<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\ModelType;
use App\Models\Category;
use App\Models\Image;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
  /**
   * configure
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function configure(Table $table): Table
  {
    return $table
      // システムカテゴリ（__system 等）は一覧から除外
      // 除外条件は Category::scopeExcludingSystem() に一元化（ハードコード禁止）
      ->modifyQueryUsing(fn ($query) => $query->excludingSystem())
      ->reorderable('sort_order')
      ->defaultSort('sort_order', 'asc')
      ->description('並べ替えボタン "⇅" をクリックするとドラッグ＆ドロップで順番を変更できます（チェックマーク "✓" をクリックで並べ替えを保存）')
      ->columns([

        ImageColumn::make('cover_image_url')
          ->label('カバー画像')
          ->getStateUsing(fn (Category $record): string =>
            $record->coverImage
              ? $record->coverImage->imageUrl('thumb')
              : Image::noImageUrl('thumb')
          )
          ->circular(false)
          ->width(80)
          ->height(60),

        TextColumn::make('name')
          ->label('カテゴリ名（スラッグ）')
          ->searchable()
          ->sortable(),

        TextColumn::make('name_ja')
          ->label('カテゴリ表示名')
          ->searchable()
          ->sortable(),

        TextColumn::make('model_type')
          ->label('使用モデル')
          ->formatStateUsing(fn ($state) => $state instanceof ModelType ? $state->label() : 'プレイリスト親')
          ->badge()
          ->color(fn ($state) => match(true) {
            $state instanceof ModelType && $state === ModelType::IMAGE => 'info',
            $state instanceof ModelType && $state === ModelType::POST  => 'success',
            default                                                    => 'gray',
          })
          // 管理者のみ表示
          ->visible(fn () => auth()->user()?->isAdmin()),

        IconColumn::make('is_active')
          ->label('使用可否')
          ->boolean()
          ->sortable(),

      ])
      ->filters([

        TernaryFilter::make('is_active')
          ->label('使用可否')
          ->trueLabel('使用')
          ->falseLabel('無効'),

      ])
      ->recordActions([
        EditAction::make(),
        // 削除ボタンなし（カテゴリは削除禁止）
      ])
      ->toolbarActions([
        // 一括削除なし（カテゴリは削除禁止）
      ]);
  }
}
