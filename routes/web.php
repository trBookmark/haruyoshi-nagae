<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| フロントルート
|--------------------------------------------------------------------------
|
| ルート名の骨格。未実装ページはコメントアウト
| 各機能ブランチでコントローラ作成とあわせて有効化する
|
*/

// トップページ（本実装は手順2で HomeController に差し替え）
Route::view('/', 'home')->name('home');

// Gallery（カテゴリ一覧）
// Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// カテゴリ別画像一覧
// Route::get('/gallery/{category:name}', [GalleryController::class, 'show'])->name('gallery.show');

// 画像個別ページとプレイリストページは URL が同型のため、制約で互いに排他にする：
// 画像＝数値のみ、プレイリスト＝英小文字始まり（playlists.name のバリデーションで形式を強制）

// 画像個別ページ
// Route::get('/gallery/{category:name}/{image}', [ImageController::class, 'show'])
//   ->whereNumber('image')->name('images.show');

// プレイリストページ（サブカテゴリ・YouTube 埋め込み。着手時に playlists の name 分割が必要）
// Route::get('/gallery/{category:name}/{playlist:name}', [PlaylistController::class, 'show'])
//   ->where('playlist', '[a-z][a-z0-9\-]*')->name('playlists.show');

// About
// Route::get('/about', [AboutController::class, 'show'])->name('about');

// ブログ
// Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
// Route::get('/blog/{post}', [PostController::class, 'show'])->name('blog.show');

// タグページ（画像・ブログ横断）
// Route::get('/tags/{tag:name}', [TagController::class, 'show'])->name('tags.show');

// お問い合わせ
// Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
// Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
