<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;

/**
 * ImageProcessor
 * 画像処理制御
 *
 * 処理経路：
 * - GIF  : ExifCleaner をスキップ → ImageResizer でコピーのみ（ICC 再適用もスキップ）
 * - 非GIF: ExifCleaner で EXIF 除去 → ImageResizer でリサイズ・保存
 *          → IccProfileEmbedder で ICC プロファイル再適用
 *          → 一時ファイルを削除（EXIF 除去失敗時は ExifCleaner 側で削除）
 *
 * original の保存（非公開）は ImageResizer で実施（GIF・非GIF 共通）
 *
 * ICC プロファイルの再適用が必要な理由：
 * GD ドライバ（Intervention Image v3）はリサイズ時に ICC プロファイルを破棄するため、
 * ExifCleaner 出力の一時ファイル（ICC 保持済み）から再抽出して各サイズに再適用する。
 */
class ImageProcessor
{
  public function __construct(
    private readonly ExifCleaner       $exifCleaner,
    private readonly ImageResizer      $resizer,
    private readonly IccProfileEmbedder $iccEmbedder,
  ) {}

  /**
   * process
   * アップロードファイルを処理して各サイズに保存
   *
   * @param  \Illuminate\Http\UploadedFile $file            アップロードファイル
   * @param  string                        $storageFileName 保存ファイル名（UUID + 拡張子）
   * @param  string                        $extension       ファイル拡張子（小文字・ドットなし）
   * @param  string                        $mimeType        MIMEタイプ
   * @return void
   */
  public function process(
    UploadedFile $file,
    string $storageFileName,
    string $extension,
    string $mimeType,
  ): void {
    $isGif      = $mimeType === 'image/gif';
    $sourcePath = $file->getRealPath();

    // original を非公開ディスクに保存（GIF・非GIF 共通）
    $this->resizer->saveOriginal($sourcePath, $storageFileName);

    if ($isGif) {
      // GIF アニメ：EXIF 除去なし・ICC 再適用なし・コピーのみ
      $this->resizer->resize($sourcePath, $storageFileName, isGif: true);
      return;
    }

    // 非GIF：EXIF 除去 → リサイズ・保存 → ICC 再適用 → 一時ファイル削除
    $tempPath = null;

    try {
      $tempPath = $this->exifCleaner->clean($sourcePath, $extension);
      $this->resizer->resize($tempPath, $storageFileName, isGif: false);

      // リサイズ後に ICC プロファイルを再適用
      // ICC が存在しない場合は IccProfileEmbedder 内でスキップされる
      $this->iccEmbedder->embed($tempPath, $storageFileName);

    } finally {
      // 成功・失敗に関わらず一時ファイルを削除（ImageProcessor の責任）
      if ($tempPath !== null && file_exists($tempPath)) {
        @unlink($tempPath);
      }
    }
  }
}
