<?php

namespace App\Services\Post;

use App\Enums\ModelType;
use App\Models\Category;
use App\Models\Image;
use App\Services\Image\ImageUploadService;
use Illuminate\Http\UploadedFile;

/**
 * EyecatchService
 * ブログ記事のアイキャッチ画像管理
 *
 * 責務：
 * - アイキャッチ用 Image レコード作成（ファイル処理は ImageUploadService へ委譲）
 * - 不要になったアイキャッチ画像の後始末
 * CreatePost / EditPost から参照
 */
class EyecatchService
{
  public function __construct(
    private readonly ImageUploadService $uploadService,
  ) {}

  /**
   * create
   * アイキャッチ画像をアップロードして Image レコードを作成
   *
   * @param  \Illuminate\Http\UploadedFile $file
   * @return \App\Models\Image
   *
   * @throws \Illuminate\Validation\ValidationException ファイル不正・カテゴリ内重複の場合
   */
  public function create(UploadedFile $file): Image
  {
    $categoryId = $this->categoryId();

    $result = $this->uploadService->upload($file, $categoryId);

    return Image::create(array_merge(
      $result->toArray(),
      ['category_id' => $categoryId],
    ));
  }

  /**
   * deleteIfUnused
   * 画像がどこからも参照されていなければレコードとファイルを削除
   * 差し替え・削除で不要になった旧アイキャッチの後始末に使用
   *
   * @param  \App\Models\Image|null $image
   * @return void
   */
  public function deleteIfUnused(?Image $image): void
  {
    if ($image && ! $image->isInUse()) {
      $image->delete(); // Image::booted() の deleting イベントで Storage ファイルも削除
    }
  }

  /**
   * categoryId
   * アイキャッチの保存先カテゴリ（ブログ用カテゴリ）の ID を返す
   * メモ：model_type = POST のカテゴリは book のみ、複数になった場合は見直すこと
   *
   * @return int
   */
  private function categoryId(): int
  {
    return Category::where('model_type', ModelType::POST->value)
      ->orderBy('sort_order')
      ->firstOrFail()
      ->id;
  }
}
