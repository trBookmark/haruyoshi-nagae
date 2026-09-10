<?php

use App\Enums\ModelType;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/**
 * 既定は管理者としてログインした状態から始める
 * 編集者の挙動を見るテストでは、各テスト内で actingAs を上書きする
 */
beforeEach(function () {
  $this->admin = User::factory()->admin()->create();

  $this->actingAs($this->admin);
});

// ──────────── Create ────────────

/**
 * 管理者がカテゴリを作成すると DB に保存されることを確認
 */
test('管理者はカテゴリを作成できる', function () {
  Livewire::test(CreateCategory::class)
    ->fillForm([
      'name'       => 'new-category',
      'name_ja'    => '新カテゴリ',
      'model_type' => ModelType::IMAGE->value,
      'is_active'  => true,
    ])
    ->call('create')
    ->assertHasNoFormErrors();

  $this->assertDatabaseHas('categories', [
    'name'       => 'new-category',
    'name_ja'    => '新カテゴリ',
    'model_type' => ModelType::IMAGE->value,
  ]);
});

/**
 * 編集者はカテゴリを作成できないことを確認（CategoryPolicy::create は管理者のみ）
 */
test('編集者はカテゴリ作成ページにアクセスできない', function () {
  $this->actingAs(User::factory()->editor()->create())
    ->get(CategoryResource::getUrl('create'))
    ->assertForbidden();
});

/**
 * name / name_ja が未入力の場合は作成できないことを確認（どちらも required）
 */
test('name と name_ja が未入力の場合は作成できない', function () {
  Livewire::test(CreateCategory::class)
    ->fillForm([
      'name'    => null,
      'name_ja' => null,
    ])
    ->call('create')
    ->assertHasFormErrors([
      'name'    => 'required',
      'name_ja' => 'required',
    ]);
});

/**
 * 既存と同名のカテゴリは作成できないことを確認（unique）
 */
test('既存と同名のカテゴリは作成できない', function () {
  $existing = Category::factory()->create();

  Livewire::test(CreateCategory::class)
    ->fillForm([
      'name'    => $existing->name,
      'name_ja' => '重複カテゴリ',
    ])
    ->call('create')
    ->assertHasFormErrors(['name' => 'unique']);
});

// ──────────── Update ────────────

/**
 * 管理者がカテゴリを更新すると DB に反映されることを確認
 */
test('管理者はカテゴリを更新できる', function () {
  $category = Category::factory()->create(['name_ja' => '変更前', 'is_active' => true]);

  Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
    ->fillForm([
      'name_ja'   => '変更後',
      'is_active' => false,
    ])
    ->call('save')
    ->assertHasNoFormErrors();

  $this->assertDatabaseHas('categories', [
    'id'        => $category->id,
    'name_ja'   => '変更後',
    'is_active' => false,
  ]);
});

// 編集者には name が disabled、model_type が非表示のため、いずれも Filament の $data に含まれない
// EditCategory::metaFields() のフォールバックを外すと、編集者の保存のたびに null で上書きされる
// 実際に踏んだバグのため、行ごとに1本ずつ用意して再発時に必ず落ちるようにする

/**
 * 編集者が保存しても model_type が維持されることを確認
 */
test('編集者が保存しても model_type が維持される', function () {
  $this->actingAs(User::factory()->editor()->create());

  $category = Category::factory()->create(['model_type' => ModelType::IMAGE->value]);

  Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
    ->fillForm(['name_ja' => '表示名だけ変更'])
    ->call('save')
    ->assertHasNoFormErrors();

  expect($category->refresh()->model_type)->toBe(ModelType::IMAGE);
});

/**
 * 編集者が保存しても name が維持されることを確認
 */
test('編集者が保存しても name が維持される', function () {
  $this->actingAs(User::factory()->editor()->create());

  $category = Category::factory()->create(['name' => 'keep-this-name']);

  Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
    ->fillForm(['name_ja' => '表示名だけ変更'])
    ->call('save')
    ->assertHasNoFormErrors();

  expect($category->refresh()->name)->toBe('keep-this-name');
});

/**
 * 編集者は __system カテゴリを編集できないことを確認（CategoryPolicy::update）
 */
test('編集者は __system カテゴリを編集できない', function () {
  $system = Category::factory()->system()->create();

  $this->actingAs(User::factory()->editor()->create())
    ->get(CategoryResource::getUrl('edit', ['record' => $system]))
    ->assertForbidden();
});

/**
 * 管理者は __system カテゴリを編集できることを確認
 */
test('管理者は __system カテゴリを編集できる', function () {
  $system = Category::factory()->system()->create();

  $this->get(CategoryResource::getUrl('edit', ['record' => $system]))
    ->assertOk();
});

// ──────────── Delete（禁止されていることの確認） ────────────
// 誤操作とデータ不整合を防ぐため、カテゴリは削除できない
// 使用/不使用の切り替えは is_active で行う

/**
 * 管理者であってもカテゴリを削除できないことを確認（CategoryPolicy::delete は常に false）
 */
test('管理者でもカテゴリを削除できない', function () {
  $category = Category::factory()->create();

  expect($this->admin->can('delete', $category))->toBeFalse();
});

/**
 * 一覧に削除アクションが存在しないことを確認
 */
test('カテゴリ一覧に削除アクションが存在しない', function () {
  $category = Category::factory()->create();

  Livewire::test(ListCategories::class)
    ->assertActionDoesNotExist(TestAction::make(DeleteAction::getDefaultName())->table($category));
});

// ──────────── List ────────────

/**
 * 一覧に __system カテゴリが表示されないことを確認（CategoriesTable の excludingSystem()）
 */
test('一覧に __system カテゴリは表示されない', function () {
  $system = Category::factory()->system()->create();
  $normal = Category::factory()->create();

  Livewire::test(ListCategories::class)
    ->assertCanSeeTableRecords([$normal])
    ->assertCanNotSeeTableRecords([$system]);
});

// ──────────── 並べ替え ────────────

/**
 * 並べ替えを実行すると sort_order が更新されることを確認
 * CategoriesTable の reorderable('sort_order') と Category の SortableTrait の噛み合わせを見る
 */
test('並べ替えを実行すると sort_order が更新される', function () {
  $first  = Category::factory()->create(['sort_order' => 1]);
  $second = Category::factory()->create(['sort_order' => 2]);
  $third  = Category::factory()->create(['sort_order' => 3]);

  Livewire::test(ListCategories::class)
    ->call('reorderTable', [$third->id, $first->id, $second->id]);

  expect($third->refresh()->sort_order)->toBe(1)
    ->and($first->refresh()->sort_order)->toBe(2)
    ->and($second->refresh()->sort_order)->toBe(3);
});

/**
 * カテゴリ作成時に sort_order が自動採番されないことを確認
 * Category の $sortable で sort_when_creating を false にしているため、$attributes の既定値 0 のままになる
 * true に戻ると Seeder が明示指定した並び順が上書きされる
 */
test('カテゴリ作成時に sort_order が自動採番されない', function () {
  Category::factory()->create(['sort_order' => 5]);

  $created = Category::create([
    'name'       => 'no-auto-order',
    'name_ja'    => '自動採番なし',
    'model_type' => ModelType::IMAGE->value,
  ]);

  expect($created->sort_order)->toBe(0);
});
