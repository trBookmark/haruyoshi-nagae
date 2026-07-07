<?php

namespace App\Filament\Support;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

/**
 * SystemCategoryGuard
 * __system カテゴリへの直接割り当てを禁止する共通ガード
 *
 * ImageForm::category_id の ->rule()（宣言的バリデーション）を
 * 直接リクエスト送信（Livewire ペイロード改ざん等）で回避された場合の
 * 多重防御として使用する。CreateImage / EditImage 双方から呼び出す想定。
 *
 * ImageUploadService と同じ規約（ValidationException をスロー）に合わせることで、
 * 呼び出し元の既存 try/catch（Notification + halt）にそのまま乗せられる。
 */
class SystemCategoryGuard
{
  /**
   * assertNotSystemCategory
   * 指定された category_id が __system カテゴリでないことを確認する
   * 存在しない、または __system カテゴリの場合は ValidationException をスロー
   *
   * @param  int $categoryId
   * @return void
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public static function assertNotSystemCategory(int $categoryId): void
  {
    $category = Category::find($categoryId);

    if (! $category || Category::isSystemName($category->name)) {
      throw ValidationException::withMessages([
        'category_id' => '指定されたカテゴリは選択できません。',
      ]);
    }
  }
}
