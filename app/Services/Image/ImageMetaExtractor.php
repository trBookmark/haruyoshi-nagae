<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;

/**
 * ImageMetaExtractor
 * アップロードされた画像ファイルのメタ情報を取得
 * チェックサムは EXIF 除去前のオリジナルファイルから計算
 */
class ImageMetaExtractor
{
  /**
   * extract
   * 画像ファイルからメタ情報を取得
   *
   * @param  \Illuminate\Http\UploadedFile $file アップロードファイル
   * @return array{
   *   width: int|null,
   *   height: int|null,
   *   file_size: int,
   *   mime_type: string,
   *   extension: string,
   *   checksum: string,
   * }
   */
  public function extract(UploadedFile $file): array
  {
    // getMimeType() はサーバー環境によって null を返すことがあるため、getClientMimeType() をフォールバックとして使用
    // getClientMimeType() はクライアント申告値なので、
    // MIMEバリデーションは FormRequest 側で mimes: ルールによりサーバー側判定済みであることが前提
    return [
      'checksum'  => $this->checksum($file),
      'file_size' => $file->getSize(),
      'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
      'extension' => strtolower($file->getClientOriginalExtension()),
      ...$this->dimensions($file),
    ];
  }

  /**
   * checksum
   * SHA-256 チェックサムを EXIF 除去前のオリジナルファイルから計算（重複検出の基準）
   *
   * @param  \Illuminate\Http\UploadedFile $file
   * @return string SHA-256 ハッシュ文字列（64文字）
   */
  public function checksum(UploadedFile $file): string
  {
    return hash_file('sha256', $file->getRealPath());
  }

  /**
   * dimensions
   * 画像の幅・高さを取得
   * GIF アニメ・破損ファイルなど getimagesize() が失敗する場合は null を返す
   *
   * @param  \Illuminate\Http\UploadedFile $file
   * @return array{ width: int|null, height: int|null }
   */
  private function dimensions(UploadedFile $file): array
  {
    // GIFアニメや一部のPNGで getimagesize() が警告を出すケースがあるため @ で抑制
    $size = @getimagesize($file->getRealPath());

    return [
      'width'  => $size ? (int) $size[0] : null,
      'height' => $size ? (int) $size[1] : null,
    ];
  }
}
