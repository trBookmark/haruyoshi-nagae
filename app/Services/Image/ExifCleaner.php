<?php

namespace App\Services\Image;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * ExifCleaner
 * ExifTool を使って画像から EXIF を除去し、ICC プロファイルを保持する
 *
 * 一時ファイルの管理：
 * - 成功時：一時ファイルのパスを返す（削除は呼び出し元 ImageProcessor で実施）
 * - 失敗時：finally で一時ファイルを削除して例外を再スロー
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

    $tempPath = storage_path('app/temp/' . Str::uuid() . '.' . $extension);

    // temp ディレクトリが存在しない場合は作成
    $tempDir = dirname($tempPath);
    if (! is_dir($tempDir)) {
      mkdir($tempDir, 0755, true);
    }

    try {
      $this->runExifTool($binary, $perl5lib, $sourcePath, $tempPath);
    } catch (\Throwable $e) {
      // 失敗時は一時ファイルを削除して例外を再スロー
      if (file_exists($tempPath)) {
        @unlink($tempPath);
      }
      throw $e;
    }

    return $tempPath;
  }

  /**
   * runExifTool
   * ExifTool を実行して EXIF 除去・ICC プロファイル保持を行う
   *
   * 実行コマンド：
   *   exiftool -all= --ColorSpaceTags -o {tempPath} {sourcePath}
   *     -all=            : 全メタデータを削除
   *     --ColorSpaceTags : ICC プロファイル関連タグを除外から除外（保持）
   *     -o {tempPath}    : 出力先を指定（元ファイルを上書きしない）
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
    $cmd = sprintf(
      '%s -all= --ColorSpaceTags -o %s %s 2>&1',
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
