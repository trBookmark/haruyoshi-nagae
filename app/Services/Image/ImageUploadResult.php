<?php

namespace App\Services\Image;

/**
 * ImageUploadResult
 * ImageUploadService が返す Value Object
 * Image::create() に渡すデータを型安全に保持
 * DB への書き込みは呼び出し元で行う
 */
readonly class ImageUploadResult
{
  /**
   * @param  string   $storageFileName 保存ファイル名（UUID + 拡張子）
   * @param  string   $clientFileName  アップロード元のファイル名
   * @param  string   $checksum        SHA-256 チェックサム（EXIF 除去前）
   * @param  int|null $width           画像の幅（px）
   * @param  int|null $height          画像の高さ（px）
   * @param  int      $fileSize        ファイルサイズ（バイト）
   * @param  string   $extension       ファイル拡張子（小文字・ドットなし）
   * @param  string   $mimeType        MIMEタイプ
   */
  public function __construct(
    public string  $storageFileName,
    public string  $clientFileName,
    public string  $checksum,
    public ?int    $width,
    public ?int    $height,
    public int     $fileSize,
    public string  $extension,
    public string  $mimeType,
  ) {}

  /**
   * toArray
   * Image::create() に渡す配列を返す
   * キー名は images テーブルのカラム名に対応
   *
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    return [
      'storage_file_name' => $this->storageFileName,
      'client_file_name'  => $this->clientFileName,
      'checksum'          => $this->checksum,
      'width'             => $this->width,
      'height'            => $this->height,
      'file_size'         => $this->fileSize,
      'extension'         => $this->extension,
      'mime_type'         => $this->mimeType,
    ];
  }
}
