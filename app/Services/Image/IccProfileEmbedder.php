<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * IccProfileEmbedder
 * ExifTool を使って ICC プロファイルを抽出・再適用
 *
 * GD ドライバ（Intervention Image v3）はリサイズ時に ICC プロファイルを破棄するため、
 * リサイズ後のファイルに元ファイルの ICC プロファイルを再適用することで色精度を維持する
 *
 * 処理対象：JPEG・PNG
 * スキップ条件：
 * - 元ファイルに ICC プロファイルが存在しない場合（ExifTool が 0 バイト出力）
 *
 * 一時ファイルの管理：
 * - ICC プロファイル一時ファイルは finally で削除（成功・失敗に関わらず）
 *
 * exec() の注意：
 * - ICC 抽出：proc_open() に引数配列を渡してシェルを介さず直接プロセスを起動し、
 *   stdout を直接ファイルディスクリプタに書き出す。
 *   文字列コマンドでは escapeshellarg() のクォートが ExifTool に渡ってしまうため引数配列を使用。
 * - ICC 適用：'-icc_profile<=' を exec() がリダイレクトとして解釈するため sh -c 経由で実行
 */
class IccProfileEmbedder
{
  /**
   * embed
   * 元ファイルから ICC プロファイルを抽出し、リサイズ済みファイルに再適用する
   * ICC プロファイルが存在しない場合はスキップして正常終了
   *
   * @param  string $sourcePath      ICC プロファイル抽出元のフルパス（ExifCleaner 出力の一時ファイル）
   * @param  string $storageFileName 保存ファイル名（UUID + 拡張子）
   * @return void
   *
   * @throws \RuntimeException ExifTool バイナリ未設定・実行失敗の場合
   */
  public function embed(string $sourcePath, string $storageFileName): void
  {
    $binary   = config('image.exiftool.binary');
    $perl5lib = config('image.exiftool.perl5lib');

    if (empty($binary)) {
      throw new RuntimeException(
        'ExifTool のパスが設定されていません。.env に EXIFTOOL_BINARY を設定してください。'
      );
    }

    $iccTempPath = storage_path('app/temp/' . Str::uuid() . '.icc');

    $tempDir = dirname($iccTempPath);
    if (! is_dir($tempDir)) {
      mkdir($tempDir, 0755, true);
    }

    try {
      $extracted = $this->extractIccProfile($binary, $perl5lib, $sourcePath, $iccTempPath);

      if (! $extracted) {
        return;
      }

      $this->applyToResizedFiles($binary, $perl5lib, $iccTempPath, $storageFileName);

    } finally {
      if (file_exists($iccTempPath)) {
        @unlink($iccTempPath);
      }
    }
  }

  /**
   * extractIccProfile
   * ExifTool で ICC プロファイルをバイナリ抽出して一時ファイルに保存
   * ICC プロファイルが存在しない場合（0 バイト出力）は false を返す
   *
   * proc_open() に引数配列を渡してシェルを介さず直接プロセスを起動
   * 文字列コマンドでは escapeshellarg() のクォートが ExifTool にそのまま渡り
   * ファイル名として誤解釈されるため、引数配列方式でエスケープなしで渡す
   * stdout はファイルディスクリプタで直接 $iccTempPath に書き出す
   *
   * 実行コマンド（相当）：
   *   exiftool -icc_profile -b {sourcePath}  → stdout → $iccTempPath
   *     -icc_profile : ICC プロファイルタグを対象
   *     -b           : バイナリ出力（stdout に書き出す）
   *
   * @param  string $binary      ExifTool バイナリのフルパス
   * @param  string $perl5lib    PERL5LIB パス（空の場合はスキップ）
   * @param  string $sourcePath  抽出元ファイルのフルパス
   * @param  string $iccTempPath ICC プロファイル一時ファイルの保存先
   * @return bool                ICC プロファイルが存在して保存できた場合 true
   *
   * @throws \RuntimeException ExifTool の実行に失敗した場合
   */
  private function extractIccProfile(
    string $binary,
    string $perl5lib,
    string $sourcePath,
    string $iccTempPath,
  ): bool {
    if (! empty($perl5lib)) {
      putenv('PERL5LIB=' . $perl5lib);
    }

    // 引数配列でシェルを介さず直接プロセスを起動（クォート問題を回避）
    $cmdArray = [$binary, '-icc_profile', '-b', $sourcePath];

    $descriptors = [
      0 => ['pipe', 'r'],               // stdin
      1 => ['file', $iccTempPath, 'w'], // stdout → ICC 一時ファイルに直接書き出し
      2 => ['pipe', 'w'],               // stderr
    ];

    $process = proc_open($cmdArray, $descriptors, $pipes);

    if (! is_resource($process)) {
      throw new RuntimeException(
        'ExifTool プロセスの起動に失敗しました。'
      );
    }

    fclose($pipes[0]);
    $stderr   = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    // ICC プロファイルが存在しない場合、ファイルが生成されないか 0 バイトになる
    if (! file_exists($iccTempPath) || filesize($iccTempPath) === 0) {
      return false;
    }

    // ファイルは生成されたが exitCode !== 0 の場合は実行エラー
    if ($exitCode !== 0) {
      throw new RuntimeException(
        'ExifTool による ICC プロファイル抽出に失敗しました。終了コード: ' . $exitCode
        . ' stderr: ' . $stderr
      );
    }

    return true;
  }

  /**
   * applyToResizedFiles
   * 各サイズのリサイズ済みファイルに ICC プロファイルを再適用
   * config('image.resize') で定義されたサイズ（large / medium / thumb）に対して実行
   *
   * 実行コマンド：
   *   exiftool -icc_profile<={iccTempPath} -overwrite_original {targetPath}
   *     -icc_profile<= : ファイルから ICC プロファイルを読み込んで埋め込む
   *     -overwrite_original : バックアップファイルを生成せずに上書き
   *
   * @param  string $binary          ExifTool バイナリのフルパス
   * @param  string $perl5lib        PERL5LIB パス（空の場合はスキップ）
   * @param  string $iccTempPath     ICC プロファイル一時ファイルのフルパス
   * @param  string $storageFileName 保存ファイル名（UUID + 拡張子）
   * @return void
   *
   * @throws \RuntimeException ExifTool の実行に失敗した場合
   */
  private function applyToResizedFiles(
    string $binary,
    string $perl5lib,
    string $iccTempPath,
    string $storageFileName,
  ): void {
    $sizes = array_keys(config('image.resize', []));

    foreach ($sizes as $size) {
      $diskRelativePath = 'images/' . $size . '/' . $storageFileName;
      $physicalPath     = Storage::disk('public')->path($diskRelativePath);

      if (! file_exists($physicalPath)) {
        continue;
      }

      $this->runApplyCommand($binary, $perl5lib, $iccTempPath, $physicalPath);
    }
  }

  /**
   * runApplyCommand
   * ExifTool で ICC プロファイルを単一ファイルに適用
   *
   * '-icc_profile<=' は exec() がシェルのリダイレクトとして解釈するため、
   * sh -c 経由で実行してオプションとして正しく解釈させる
   *
   * 実行コマンド：
   *   exiftool "-icc_profile<={iccTempPath}" -overwrite_original {targetPath}
   *     -icc_profile<= : ファイルから ICC プロファイルを読み込んで埋め込む
   *     -overwrite_original : バックアップファイルを生成せずに上書き
   *
   * @param  string $binary      ExifTool バイナリのフルパス
   * @param  string $perl5lib    PERL5LIB パス
   * @param  string $iccTempPath ICC プロファイル一時ファイルのフルパス
   * @param  string $targetPath  ICC 適用対象ファイルの物理フルパス
   * @return void
   *
   * @throws \RuntimeException ExifTool の実行に失敗した場合
   */
  private function runApplyCommand(
    string $binary,
    string $perl5lib,
    string $iccTempPath,
    string $targetPath,
  ): void {
    if (! empty($perl5lib)) {
      putenv('PERL5LIB=' . $perl5lib);
    }

    // '-icc_profile<=パス' を1引数として escapeshellarg で保護する
    // sh -c 経由のため単引用符がシェルに解釈され、リダイレクト誤解釈・
    // パス内の空白や特殊文字の問題を同時に防げる
    $innerCmd = sprintf(
      '%s %s -overwrite_original %s 2>&1',
      escapeshellcmd($binary),
      escapeshellarg('-icc_profile<=' . $iccTempPath),
      escapeshellarg($targetPath),
    );

    exec('sh -c ' . escapeshellarg($innerCmd), $output, $exitCode);

    if ($exitCode !== 0) {
      throw new RuntimeException(
        'ExifTool による ICC プロファイル適用に失敗しました。'
        . 'ファイル: ' . basename($targetPath)
        . ' 終了コード: ' . $exitCode
        . ' 出力: ' . implode(' ', $output)
      );
    }
  }
}
