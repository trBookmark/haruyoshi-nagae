<?php

use App\Filament\Resources\Images\ImageResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Livewire\Livewire;

// ──────────── 未認証 ────────────

/**
 * 未ログインで管理画面にアクセスするとログイン画面へリダイレクトされることを確認
 * （AdminPanelProvider の authMiddleware に指定した Authenticate の挙動）
 */
test('未ログインで管理画面にアクセスするとログイン画面へリダイレクトされる', function () {
  $this->get(Dashboard::getUrl())
    ->assertRedirect(Filament::getLoginUrl());
});

/**
 * ログイン画面自体は未ログインでも表示できることを確認
 * （リダイレクト先が保護されていてループしないことの確認を兼ねる）
 */
test('ログイン画面は未ログインでも表示できる', function () {
  $this->get(Filament::getLoginUrl())->assertOk();
});

// ──────────── ログイン処理 ────────────

/**
 * 正しい認証情報でログインでき、認証済みになることを確認
 * パスワードは UserFactory が設定する 'password'
 */
test('正しい認証情報でログインできる', function () {
  $user = User::factory()->admin()->create();

  Livewire::test(Login::class)
    ->fillForm([
      'email'    => $user->email,
      'password' => 'password',
    ])
    ->call('authenticate')
    ->assertHasNoFormErrors();

  $this->assertAuthenticatedAs($user);
});

/**
 * パスワードが誤っている場合はログインできず、未認証のままであることを確認
 */
test('パスワードが誤っている場合はログインできない', function () {
  $user = User::factory()->admin()->create();

  Livewire::test(Login::class)
    ->fillForm([
      'email'    => $user->email,
      'password' => 'wrong-password',
    ])
    ->call('authenticate')
    ->assertHasFormErrors(['email']);

  $this->assertGuest();
});

// ──────────── パネルへのアクセス ────────────
// User::canAccessPanel() は admin / editor の両方を許可する
// role は UserRole にキャストされるため、これ以外の値を持つ User は作成できず、false の経路はテストできない

/**
 * 管理者が管理画面にアクセスできることを確認
 */
test('管理者は管理画面にアクセスできる', function () {
  $this->actingAs(User::factory()->admin()->create())
    ->get(Dashboard::getUrl())
    ->assertOk();
});

/**
 * 編集者も管理画面にアクセスできることを確認
 */
test('編集者は管理画面にアクセスできる', function () {
  $this->actingAs(User::factory()->editor()->create())
    ->get(Dashboard::getUrl())
    ->assertOk();
});

// ──────────── ロール別の認可 ────────────

/**
 * 編集者は User 一覧にアクセスできないことを確認
 * （UserPolicy::viewAny が管理者のみを許可）
 */
test('編集者は User 一覧にアクセスできない', function () {
  $this->actingAs(User::factory()->editor()->create())
    ->get(UserResource::getUrl('index'))
    ->assertForbidden();
});

/**
 * 管理者は User 一覧にアクセスできることを確認
 */
test('管理者は User 一覧にアクセスできる', function () {
  $this->actingAs(User::factory()->admin()->create())
    ->get(UserResource::getUrl('index'))
    ->assertOk();
});

/**
 * 編集者が Image 一覧にアクセスできることを確認
 * User 一覧の制限が、編集者本来の作業画面まで巻き込んで塞いでいないことの確認
 */
test('編集者は Image 一覧にアクセスできる', function () {
  $this->actingAs(User::factory()->editor()->create())
    ->get(ImageResource::getUrl('index'))
    ->assertOk();
});
