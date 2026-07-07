<?php

use App\Filament\Support\SystemCategoryGuard;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

/**
 * 通常カテゴリの category_id を渡した場合、例外がスローされないことを確認
 */
test('通常カテゴリの場合は例外がスローされない', function () {
  $category = Category::factory()->create();

  SystemCategoryGuard::assertNotSystemCategory($category->id);
})->throwsNoExceptions();

/**
 * __system カテゴリの category_id を渡した場合、ValidationException がスローされることを確認
 */
test('__system カテゴリの場合は ValidationException がスローされる', function () {
  $category = Category::factory()->system()->create();

  SystemCategoryGuard::assertNotSystemCategory($category->id);
})->throws(ValidationException::class);

/**
 * DB に存在しない category_id を渡した場合、ValidationException がスローされることを確認
 */
test('存在しない category_id の場合は ValidationException がスローされる', function () {
  SystemCategoryGuard::assertNotSystemCategory(99999);
})->throws(ValidationException::class);

/**
 * __system カテゴリの場合、エラーメッセージが category_id キーに紐づくことを確認
 */
test('__system カテゴリの場合エラーメッセージが category_id キーに設定される', function () {
  $category = Category::factory()->system()->create();

  try {
    SystemCategoryGuard::assertNotSystemCategory($category->id);
  } catch (ValidationException $e) {
    expect($e->errors())->toHaveKey('category_id');
  }
});
