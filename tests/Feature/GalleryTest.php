<?php

use App\Models\Category;
use App\Models\Image;
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

// ──────────── gallery.show（カテゴリ別画像一覧） ────────────

/**
 * カテゴリ別画像一覧が 200 を返し、画像タイトルが表示されることを確認
 */
test('カテゴリ別画像一覧が表示され画像タイトルが出力される', function () {
  $category = Category::factory()->create();
  Image::factory()->for($category)->create(['title' => 'テスト作品']);

  $response = $this->get(route('gallery.show', $category));

  $response->assertOk();
  $response->assertSeeText('テスト作品');
});

/**
 * タイトル未入力の画像は「No. {id}」で表示されることを確認（旧サイト踏襲）
 */
test('タイトル未入力の画像は No 表記で表示される', function () {
  $category = Category::factory()->create();
  $image = Image::factory()->for($category)->create(['title' => null]);

  $this->get(route('gallery.show', $category))
    ->assertSeeText('No. ' . $image->id);
});

/**
 * 非公開（is_active=false）の画像が表示されないことを確認
 */
test('非公開の画像は表示されない', function () {
  $category = Category::factory()->create();
  Image::factory()->for($category)->create(['title' => '公開作品']);
  Image::factory()->for($category)->create(['title' => '非公開作品', 'is_active' => false]);

  $response = $this->get(route('gallery.show', $category));

  $response->assertSeeText('公開作品');
  $response->assertDontSeeText('非公開作品');
});

/**
 * 公開画像が 0 件のカテゴリは 404 を返すことを確認（旧サイト踏襲）
 */
test('公開画像が 0 件のカテゴリは 404 を返す', function () {
  $category = Category::factory()->create();

  $this->get(route('gallery.show', $category))->assertNotFound();
});

/**
 * 無効（is_active=false）カテゴリは 404 を返すことを確認
 */
test('無効カテゴリは 404 を返す', function () {
  $category = Category::factory()->create(['is_active' => false]);
  Image::factory()->for($category)->create();

  $this->get(route('gallery.show', $category))->assertNotFound();
});

/**
 * __system カテゴリは 404 を返すことを確認
 */
test('__system カテゴリの画像一覧は 404 を返す', function () {
  $category = Category::factory()->system()->create();

  $this->get(route('gallery.show', $category))->assertNotFound();
});

/**
 * model_type が IMAGE 以外（プレイリスト専用 null）は 404 を返すことを確認
 * POST（ブログ）も同じガード（model_type === IMAGE）で弾かれる
 */
test('プレイリスト専用カテゴリの画像一覧は 404 を返す', function () {
  $category = Category::factory()->create(['model_type' => null]);

  $this->get(route('gallery.show', $category))->assertNotFound();
});
