<?php

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Image;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/**
 * 各テストは管理者としてログインした状態から始める
 * TagPolicy は管理者・編集者を同じ扱いにしているため、ロールによる差は AdminAccessTest 側で検証する
 */
beforeEach(function () {
  $this->user = User::factory()->admin()->create();

  $this->actingAs($this->user);
});

// ──────────── Create ────────────

/**
 * タグを作成すると DB に保存されることを確認
 */
test('タグを作成すると DB に保存される', function () {
  Livewire::test(CreateTag::class)
    ->fillForm([
      'name'        => 'テストタグ',
      'description' => 'テスト用の概要',
      'is_active'   => true,
    ])
    ->call('create')
    ->assertHasNoFormErrors();

  $this->assertDatabaseHas('tags', [
    'name'        => 'テストタグ',
    'description' => 'テスト用の概要',
    'is_active'   => true,
  ]);
});

/**
 * タグ名が未入力の場合は作成できないことを確認（TagForm の required）
 */
test('タグ名が未入力の場合は作成できない', function () {
  Livewire::test(CreateTag::class)
    ->fillForm(['name' => null])
    ->call('create')
    ->assertHasFormErrors(['name' => 'required']);

  $this->assertDatabaseCount('tags', 0);
});

/**
 * 既存と同名のタグは作成できないことを確認（TagForm の unique）
 */
test('既存と同名のタグは作成できない', function () {
  $existing = Tag::factory()->create();

  Livewire::test(CreateTag::class)
    ->fillForm(['name' => $existing->name])
    ->call('create')
    ->assertHasFormErrors(['name' => 'unique']);

  $this->assertDatabaseCount('tags', 1);
});

// ──────────── Update ────────────

/**
 * タグを更新すると DB に反映されることを確認
 */
test('タグを更新すると DB に反映される', function () {
  $tag = Tag::factory()->create(['name' => '変更前', 'is_active' => true]);

  Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
    ->fillForm([
      'name'      => '変更後',
      'is_active' => false,
    ])
    ->call('save')
    ->assertHasNoFormErrors();

  $this->assertDatabaseHas('tags', [
    'id'        => $tag->id,
    'name'      => '変更後',
    'is_active' => false,
  ]);
});

/**
 * 名前を変えずに保存できることを確認
 * TagForm の unique(ignoreRecord: true) が効いていないと、自身の name で重複判定される
 */
test('名前を変えずに保存できる', function () {
  $tag = Tag::factory()->create(['name' => 'そのまま']);

  Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
    ->fillForm([
      'name'        => 'そのまま',
      'description' => '概要を追加',
    ])
    ->call('save')
    ->assertHasNoFormErrors();

  $this->assertDatabaseHas('tags', [
    'id'          => $tag->id,
    'description' => '概要を追加',
  ]);
});

/**
 * 他のタグと同名には変更できないことを確認
 */
test('他のタグと同名には変更できない', function () {
  $other  = Tag::factory()->create();
  $target = Tag::factory()->create();

  Livewire::test(EditTag::class, ['record' => $target->getRouteKey()])
    ->fillForm(['name' => $other->name])
    ->call('save')
    ->assertHasFormErrors(['name' => 'unique']);

  expect($target->refresh()->name)->not->toBe($other->name);
});

// ──────────── Delete ────────────
// 使用中タグの削除は TagsTable の ->hidden() と TagPolicy::delete() の2層で防いでいる
// 片方だけを検証するともう片方を消しても気づけないため、層ごとにテストを分ける

/**
 * 未使用のタグを削除すると DB から消えることを確認
 */
test('未使用のタグを削除できる', function () {
  $tag = Tag::factory()->create();

  Livewire::test(ListTags::class)
    ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($tag));

  $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

/**
 * 画像に使用されているタグは削除アクションが表示されないことを確認（TagsTable の層）
 */
test('画像に使用されているタグは削除アクションが表示されない', function () {
  $tag = Tag::factory()->create();

  Image::factory()->create()->tags()->attach($tag);

  Livewire::test(ListTags::class)
    ->assertActionHidden(TestAction::make(DeleteAction::getDefaultName())->table($tag));
});

/**
 * 画像に使用されているタグは Policy でも削除が拒否されることを確認（TagPolicy の層）
 */
test('画像に使用されているタグは Policy でも削除できない', function () {
  $tag = Tag::factory()->create();

  Image::factory()->create()->tags()->attach($tag);

  expect($this->user->can('delete', $tag))->toBeFalse();
});

// ──────────── List ────────────

/**
 * 一覧に既存のタグが表示されることを確認
 */
test('一覧に既存のタグが表示される', function () {
  $tags = Tag::factory()->count(3)->create();

  Livewire::test(ListTags::class)
    ->assertCanSeeTableRecords($tags);
});
