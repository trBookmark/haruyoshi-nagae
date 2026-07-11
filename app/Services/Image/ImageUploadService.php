<?php

namespace App\Services\Image;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * ImageUploadService
 * 画像アップロード処理全体を統括
 *
 * 責務：
 * - UploadedFile の妥当性確認
 * - チェックサム計算（EXIF 除去前）
 * - カテゴリ内チェックサム重複チェック
 * - ImageProcessor への処理委譲
 * - ImageUploadResult を返す（Image::create() は呼び出さない）
 *
 * DB への書き込みは呼び出し元で実施
 */
class ImageUploadService
{
  public function __construct(
    private readonly ImageFileNameGenerator $fileNameGenerator,
    private readonly ImageMetaExtractor     $metaExtractor,
    private readonly ImageProcessor         $processor,
  ) {}

  /**
   * upload
   * 画像をアップロードして ImageUploadResult を返す
   *
   * @param  \Illuminate\Http\UploadedFile $file       アップロードファイル
   * @param  int                           $categoryId アップロード先 CategoryID
   * @return \App\Services\Image\ImageUploadResult
   *
   * @throws \Illuminate\Validation\ValidationException ファイル不正・チェックサム重複の場合
   * @throws \RuntimeException                          画像処理失敗の場合
   */
  public function upload(UploadedFile $file, int $categoryId): ImageUploadResult
  {
    $this->validateFile($file);

    $meta = $this->metaExtractor->extract($file);

    $this->checkDuplicate($meta['checksum'], $categoryId);

    $storageFileName = $this->fileNameGenerator->generate($meta['extension']);

    $this->processor->process(
      file:            $file,
      storageFileName: $storageFileName,
      extension:       $meta['extension'],
      mimeType:        $meta['mime_type'],
    );

    return new ImageUploadResult(
      storageFileName: $storageFileName,
      clientFileName:  $file->getClientOriginalName(),
      checksum:        $meta['checksum'],
      width:           $meta['width'],
      height:          $meta['height'],
      fileSize:        $meta['file_size'],
      extension:       $meta['extension'],
      mimeType:        $meta['mime_type'],
    );
  }

  /**
   * validateFile
   * UploadedFile の妥当性を確認
   * FormRequest 通過後の二重チェック
   *
   * @param  \Illuminate\Http\UploadedFile $file
   * @return void
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  private function validateFile(UploadedFile $file): void
  {
    if (! $file->isValid()) {
      throw ValidationException::withMessages([
        'image' => 'ファイルのアップロードに失敗しました。再度お試しください。',
      ]);
    }

    $allowedMimes = config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']);

    if (! in_array($file->getMimeType(), $allowedMimes, strict: true)) {
      // MIME タイプから拡張子表記を生成（例: image/jpeg → JPEG）
      $allowedLabels = array_map(
        fn (string $mime) => strtoupper(explode('/', $mime)[1]),
        $allowedMimes,
      );

      throw ValidationException::withMessages([
        'image' => '対応していないファイル形式です。' . implode('・', $allowedLabels) . ' のみアップロードできます。',
      ]);
    }
  }

  /**
   * checkDuplicate
   * カテゴリ内のチェックサム重複を確認
   * 同一カテゴリ内に同じチェックサムの画像が存在する場合は例外をスロー
   *
   * @param  string $checksum   SHA-256 チェックサム
   * @param  int    $categoryId カテゴリ ID
   * @return void
   *
   * @throws \Illuminate\Validation\ValidationException 重複が検出された場合
   */
  private function checkDuplicate(string $checksum, int $categoryId): void
  {
    $exists = Image::where('category_id', $categoryId)
      ->where('checksum', $checksum)
      ->exists();

    if ($exists) {
      throw ValidationException::withMessages([
        'image' => 'このカテゴリには、すでに同じ画像が登録されています。',
      ]);
    }
  }
}
