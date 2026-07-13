<?php

use App\Models\Category;
use Database\Seeders\CategorySeeder;

/**
 * Gallery カテゴリ一覧が 200 を返し、カテゴリ名が表示されることを確認
 */
test('カテゴリ一覧が表示されカテゴリ名が出力される', function () {
  $this->seed(CategorySeeder::class);

  $category = Category::excludingSystem()->where('is_active', true)->first();

  $response = $this->get(route('gallery.index'));

  $response->assertOk();
  $response->assertSeeText($category->name_ja);
});

/**
 * __system カテゴリがカードとして表示されないことを確認
 * （カード見出しは大文字表示のため大文字化した名前で検証）
 */
test('__system カテゴリは表示されない', function () {
  $this->seed(CategorySeeder::class);

  $this->get(route('gallery.index'))
    ->assertDontSeeText(strtoupper(Category::SYSTEM_NAME));
});

/**
 * アイテム数 0 のカテゴリは「準備中」と表示されることを確認
 * （Seeder 直後は画像・記事・プレイリストが存在しないため全カテゴリが該当）
 */
test('アイテム数 0 のカテゴリは準備中と表示される', function () {
  $this->seed(CategorySeeder::class);

  $this->get(route('gallery.index'))->assertSeeText('準備中');
});
