<?php

namespace App\Services\Image;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * ExifCleaner
 * ExifTool を使って画像から EXIF を除去、ICC プロファイルは保持する
 *
 * 一時ファイルの管理：
 * - 成功時：一時ファイルのパスを返す（削除は呼び出し元 ImageProcessor で実施）
 * - 失敗時：finally で一時ファイルを削除して例外を再スロー
 * - 入力側に作業用コピーを作った場合は、成否に関わらずこのクラス内で削除する
 */
class ExifCleaner
{
  /**
   * clean
   * EXIF を除去した一時ファイルを生成してパスを返す
   * ICC プロファイル（ColorSpaceTags）は保持する
   *
   * @param  string $sourcePath 処理元ファイルのフルパス
   * @param  string $extension  ファイル拡張子（小文字・ドットなし）
   * @return string             EXIF 除去済み一時ファイルのフルパス
   *
   * @throws \RuntimeException ExifTool バイナリ未設定・実行失敗の場合
   */
  public function clean(string $sourcePath, string $extension): string
  {
    $binary   = config('image.exiftool.binary');
    $perl5lib = config('image.exiftool.perl5lib');

    if (empty($binary)) {
      throw new RuntimeException(
        'ExifTool のパスが設定されていません。.env に EXIFTOOL_BINARY を設定してください。'
      );
    }

    $tempDir = storage_path('app/temp');

    // temp ディレクトリが存在しない場合は作成
    if (! is_dir($tempDir)) {
      mkdir($tempDir, 0755, true);
    }

    $tempPath   = $tempDir . '/' . Str::uuid() . '.' . $extension;
    $sourceCopy = $this->sourceWithExtension($sourcePath, $extension, $tempDir);

    try {
      $this->runExifTool($binary, $perl5lib, $sourceCopy ?? $sourcePath, $tempPath);
    } catch (\Throwable $e) {
      // 失敗時は出力先の一時ファイルを削除して例外を再スロー
      if (file_exists($tempPath)) {
        @unlink($tempPath);
      }
      throw $e;
    } finally {
      // 作業用コピーを作った場合は、成否に関わらず削除する
      if ($sourceCopy !== null && file_exists($sourceCopy)) {
        @unlink($sourceCopy);
      }
    }

    return $tempPath;
  }

  /**
   * sourceWithExtension
   * ExifTool に渡す入力ファイルとして、拡張子が中身と一致するパスを用意する
   *
   * ExifTool は入力ファイルの種別を拡張子から判別する。拡張子がない、または中身と
   * 食い違うパスを渡すとコピー元なしと解釈し、
   * "Can't create JPEG files from scratch" で失敗する。
   *
   * PHP のアップロード一時ファイル（/tmp/phpXXXXXX）は拡張子を持たないため、
   * Livewire 以外の経路では素のパスを渡すと必ず失敗する。
   * $extension は MIME から導出した値であり、中身と食い違うファイル名で
   * 送られてきた場合の是正も兼ねる。
   *
   * 不要なコピーを避けるため、拡張子が一致している場合は null を返す。
   *
   * @param  string      $sourcePath 処理元ファイルのフルパス
   * @param  string      $extension  MIME から導出した拡張子（小文字・ドットなし）
   * @param  string      $tempDir    作業用コピーの作成先ディレクトリ
   * @return string|null             作業用コピーのフルパス。コピー不要の場合は null
   *
   * @throws \RuntimeException 作業用コピーの作成に失敗した場合
   */
  private function sourceWithExtension(string $sourcePath, string $extension, string $tempDir): ?string
  {
    if (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) === $extension) {
      return null;
    }

    $copyPath = $tempDir . '/' . Str::uuid() . '.' . $extension;

    if (! copy($sourcePath, $copyPath)) {
      throw new RuntimeException(
        'EXIF 除去用の作業ファイルを作成できませんでした: ' . $copyPath
      );
    }

    return $copyPath;
  }

  /**
   * runExifTool
   * ExifTool を実行して EXIF 除去・ICC プロファイル保持
   *
   * 実行コマンド：
   *   exiftool -all= -tagsfromfile @ -icc_profile -o {tempPath} {sourcePath}
   *     -all=            : 全メタデータを削除
   *     -tagsfromfile @  : 同ファイル（@）からタグをコピー
   *     -icc_profile     : ICC プロファイルのみ元ファイルから再コピー
   *     -o {tempPath}    : 出力先を指定（元ファイルを上書きしない）
   *
   * ※ --ColorSpaceTags では環境・バージョンによって ICC が削除される場合がある
   *   （ExifTool が "ICC_Profile deleted" を出力して実際に除去される）ため、
   *   削除後に再コピーする方式に変更
   *
   * @param  string $binary     ExifTool バイナリのフルパス
   * @param  string $perl5lib   PERL5LIB パス（空の場合は putenv をスキップ）
   * @param  string $sourcePath 処理元ファイルのフルパス
   * @param  string $tempPath   出力先一時ファイルのフルパス
   * @return void
   *
   * @throws \RuntimeException ExifTool の実行に失敗した場合
   */
  private function runExifTool(
    string $binary,
    string $perl5lib,
    string $sourcePath,
    string $tempPath,
  ): void {
    if (! empty($perl5lib)) {
      putenv('PERL5LIB=' . $perl5lib);
    }

    // シェルインジェクション対策：各引数を escapeshellarg() でエスケープ
    // -tagsfromfile @ -icc_profile で ICC プロファイルを元ファイルから再コピー
    $cmd = sprintf(
      '%s -all= -tagsfromfile @ -icc_profile -o %s %s 2>&1',
      escapeshellcmd($binary),
      escapeshellarg($tempPath),
      escapeshellarg($sourcePath),
    );

    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0) {
      throw new RuntimeException(
        'ExifTool の実行に失敗しました。終了コード: ' . $exitCode
        . ' 出力: ' . implode(' ', $output)
      );
    }

    if (! file_exists($tempPath)) {
      throw new RuntimeException(
        'ExifTool の実行後に出力ファイルが見つかりません: ' . $tempPath
      );
    }
  }
}
