<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Tag;

// ──────────── 表示 ────────────

/**
 * 画像個別ページが 200 を返し、タイトルが表示されることを確認
 */
test('画像個別ページが表示されタイトルが出力される', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->create(['title' => 'テスト作品']);

  $response = $this->get(route('images.show', [$category, $image]));

  $response->assertOk();
  $response->assertSeeText('テスト作品');
});

/**
 * タイトル未入力の画像は「No. {id}」で表示されることを確認（一覧ページと同じ規則）
 */
test('タイトル未入力の画像は No 表記で表示される', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->create(['title' => null]);

  $this->get(route('images.show', [$category, $image]))
    ->assertSeeText('No. ' . $image->id);
});

/**
 * 紐づくタグが表示されることを確認
 * tags.show ルートは未実装のため、リンクではなくテキストとして出力される
 */
test('紐づくタグが表示される', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->create();
  $tag      = Tag::create(['name' => 'テストタグ']);

  $image->tags()->attach($tag);

  $this->get(route('images.show', [$category, $image]))
    ->assertSeeText('テストタグ');
});

/**
 * 非GIF 画像は medium を src に、medium/large を srcset に出力することを確認
 * （Blade の isAnimatedGif() 分岐のうち、非GIF 側の経路）
 */
test('非GIF 画像は medium と large の srcset を出力する', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->create();

  $response = $this->get(route('images.show', [$category, $image]));

  $response->assertSee($image->imageUrl('medium'));
  $response->assertSee($image->srcset(['medium', 'large']), escape: false);
});

/**
 * GIF アニメは srcset を使わず large をそのまま表示することを確認
 * （リサイズなしで全サイズ同一ファイルのため、medium の URL は出力されない）
 */
test('GIF アニメは srcset を出力せず large を表示する', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->animatedGif()->create();

  $response = $this->get(route('images.show', [$category, $image]));

  $response->assertSee($image->imageUrl('large'));
  $response->assertDontSee($image->imageUrl('medium'));
});

// ──────────── 前後ナビゲーション ────────────

/**
 * 中間の画像では前後どちらのリンクも出力され、位置が正しく表示されることを確認
 * sort_order はすべて既定値 0 のため、orderedForGallery() では id 昇順に並ぶ
 */
test('中間の画像は前後のリンクと位置が表示される', function () {
  $category = Category::factory()->create();
  $images   = Image::factory()->for($category)->count(3)->create();

  $response = $this->get(route('images.show', [$category, $images[1]]));

  $response->assertSee('rel="prev"', escape: false);
  $response->assertSee('rel="next"', escape: false);
  $response->assertSee(route('images.show', [$category, $images[0]->id]));
  $response->assertSee(route('images.show', [$category, $images[2]->id]));
  $response->assertSeeText('2 / 3');
});

/**
 * 先頭の画像には前へのリンクが出力されないことを確認
 */
test('先頭の画像は前へのリンクが出力されない', function () {
  $category = Category::factory()->create();
  $images   = Image::factory()->for($category)->count(3)->create();

  $response = $this->get(route('images.show', [$category, $images[0]]));

  $response->assertDontSee('rel="prev"', escape: false);
  $response->assertSee('rel="next"', escape: false);
  $response->assertSeeText('1 / 3');
});

/**
 * 末尾の画像には次へのリンクが出力されないことを確認
 */
test('末尾の画像は次へのリンクが出力されない', function () {
  $category = Category::factory()->create();
  $images   = Image::factory()->for($category)->count(3)->create();

  $response = $this->get(route('images.show', [$category, $images[2]]));

  $response->assertSee('rel="prev"', escape: false);
  $response->assertDontSee('rel="next"', escape: false);
  $response->assertSeeText('3 / 3');
});

/**
 * 非公開の画像が前後ナビの対象から除外されることを確認
 * 2番目を非公開にすると、1番目の「次へ」は3番目を指し、総数は 2 になる
 */
test('非公開の画像は前後ナビから除外される', function () {
  $category = Category::factory()->create();
  $first    = Image::factory()->for($category)->create();
  $hidden   = Image::factory()->for($category)->create(['is_active' => false]);
  $last     = Image::factory()->for($category)->create();

  $response = $this->get(route('images.show', [$category, $first]));

  $response->assertSeeText('1 / 2');
  $response->assertSee(route('images.show', [$category, $last->id]));
  $response->assertDontSee(route('images.show', [$category, $hidden->id]));
});

// ──────────── 404 ガード ────────────
// ImageController::show() のガード 4 行とルートの scopeBindings() に 1 本ずつ対応させる
// 各テストは対象のガード以外をすべて通過する状態を作り、その 1 行だけで 404 になることを確かめる

/**
 * __system カテゴリの画像は 404 を返すことを確認
 * is_active を true に上書きするのは、後続の is_active ガードではなく
 * __system 判定のみで 404 になることを確かめるため
 */
test('__system カテゴリの画像個別ページは 404 を返す', function () {
  $category = Category::factory()->system()->create(['is_active' => true]);
  $image    = Image::factory()->for($category)->create();

  $this->get(route('images.show', [$category, $image]))->assertNotFound();
});

/**
 * 無効（is_active=false）カテゴリの画像は 404 を返すことを確認
 */
test('無効カテゴリの画像個別ページは 404 を返す', function () {
  $category = Category::factory()->create(['is_active' => false]);
  $image    = Image::factory()->for($category)->create();

  $this->get(route('images.show', [$category, $image]))->assertNotFound();
});

/**
 * model_type が IMAGE 以外（プレイリスト専用の null）のカテゴリは 404 を返すことを確認
 */
test('プレイリスト専用カテゴリの画像個別ページは 404 を返す', function () {
  $category = Category::factory()->create(['model_type' => null]);
  $image    = Image::factory()->for($category)->create();

  $this->get(route('images.show', [$category, $image]))->assertNotFound();
});

/**
 * 非公開（is_active=false）の画像は 404 を返すことを確認
 */
test('非公開の画像個別ページは 404 を返す', function () {
  $category = Category::factory()->create();
  $image    = Image::factory()->for($category)->create(['is_active' => false]);

  $this->get(route('images.show', [$category, $image]))->assertNotFound();
});

/**
 * URL のカテゴリに属さない画像 ID は 404 を返すことを確認
 * ルートの scopeBindings() により、同一画像が複数 URL で見えることを防いでいる
 */
test('カテゴリに属さない画像 ID は 404 を返す', function () {
  $category = Category::factory()->create();
  $other    = Category::factory()->create();
  $image    = Image::factory()->for($other)->create();

  $this->get(route('images.show', [$category, $image->id]))->assertNotFound();
});
