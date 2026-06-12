<?php

namespace App\Filament\Resources\Images\Tables;

use App\Models\Category;
use App\Models\Image;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;

class ImagesTable
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
      ->modifyQueryUsing(function ($query) {
        // 編集者には __system カテゴリの画像を非表示
        if (! auth()->user()?->isAdmin()) {
          $query->whereHas('category', fn ($q) => $q->where('name', 'not like', '\\_\\_%'));
        }
      })
      ->columns([

        ImageColumn::make('thumb_url')
          ->label('サムネイル')
          ->getStateUsing(fn (Image $record): string => $record->imageUrl('thumb'))
          ->circular(false)
          ->width(80)
          ->height(60),

        TextColumn::make('title')
          ->label('タイトル')
          ->searchable()
          ->placeholder('―')
          ->sortable(),

        TextColumn::make('category.name_ja')
          ->label('カテゴリ')
          ->sortable()
          // カテゴリ名クリックで画像一覧をカテゴリ絞り込み
          ->url(fn (Image $record): string =>
            route('filament.admin.resources.images.index', [
              'category_id' => $record->category_id,
            ])
          ),

        TextColumn::make('year')
          ->label('制作年')
          ->sortable()
          ->placeholder('―'),

        TextColumn::make('tags.name')
          ->label('タグ')
          ->badge()
          ->separator(',')
          ->placeholder('―'),

        IconColumn::make('is_active')
          ->label('公開')
          ->boolean()
          ->sortable(),

        TextColumn::make('mime_type')
          ->label('形式')
          ->formatStateUsing(fn (?string $state): string =>
            $state ? strtoupper(explode('/', $state)[1] ?? $state) : '―'
          )
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('width')
          ->label('幅')
          ->suffix('px')
          ->placeholder('―')
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('height')
          ->label('高さ')
          ->suffix('px')
          ->placeholder('―')
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('created_at')
          ->label('登録日時')
          ->dateTime('Y/m/d H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),

      ])
      ->filters([

        SelectFilter::make('category_id')
          ->label('カテゴリ')
          ->options(fn () => Category::query()
            // 編集者には __system カテゴリを非表示
            ->when(
              ! auth()->user()?->isAdmin(),
              fn ($q) => $q->where('name', 'not like', '\\_\\_%')
            )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name_ja', 'id')
          ),

        TernaryFilter::make('is_active')
          ->label('公開')
          ->trueLabel('公開')
          ->falseLabel('非公開'),

      ])
      ->recordActions([

        EditAction::make(),

        DeleteAction::make()
          ->hidden(fn (Image $record) => $record->isInUse())
          ->using(function (Image $record): void {
            try {
              $record->delete();
            } catch (QueryException $e) {
              Notification::make()
                ->title('削除できません')
                ->body($record->inUseDescription() ?? 'この画像は他のレコードで使用されているため削除できません。')
                ->danger()
                ->send();
            }
          }),

      ])
      ->toolbarActions([
        // カテゴリがさほど多くないことと、使用中画像の誤削除防止の観点から
        // 一括削除は提供しない
      ])
      ->defaultSort('created_at', 'desc');
  }
}
