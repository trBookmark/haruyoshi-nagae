<?php

namespace App\Filament\Resources\Playlists\Tables;

use App\Models\Category;
use App\Models\Image;
use App\Models\Playlist;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PlaylistsTable
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
      ->reorderable('sort_order')
      ->defaultSort('sort_order', 'asc')
      ->description('並べ替えボタン "⇅" をクリックするとドラッグ＆ドロップで順番を変更できます（チェックマーク "✓" をクリックで並べ替えを保存）')
      ->columns([

        ImageColumn::make('thumbnail_url')
          ->label('サムネイル')
          ->getStateUsing(fn (Playlist $record): string =>
            $record->thumbnail
              ? $record->thumbnail->imageUrl('thumb')
              : Image::noImageUrl('thumb')
          )
          ->circular(false)
          ->width(80)
          ->height(60),

        TextColumn::make('name_ja')
          ->label('サブカテゴリ名')
          ->searchable()
          ->sortable(),

        TextColumn::make('name')
          ->label('URL用英語名')
          ->placeholder('未入力')
          ->searchable()
          ->sortable(),

        TextColumn::make('category.name_ja')
          ->label('親カテゴリ')
          ->sortable(),

        TextColumn::make('yt_playlist_id')
          ->label('YouTube プレイリスト ID')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),

        IconColumn::make('is_active')
          ->label('使用可否')
          ->boolean()
          ->sortable(),

        TextColumn::make('created_at')
          ->label('作成日時')
          ->dateTime('Y/m/d H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),

      ])
      ->filters([

        SelectFilter::make('category_id')
          ->label('親カテゴリ')
          ->options(fn () => Category::query()
            ->whereNull('model_type')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name_ja', 'id')
          ),

        TernaryFilter::make('is_active')
          ->label('使用可否')
          ->trueLabel('使用')
          ->falseLabel('無効'),

      ])
      ->recordActions([
        EditAction::make(),
        // 削除ボタンなし（プレイリストは削除禁止・is_active で管理）
      ])
      ->toolbarActions([
        // 一括削除なし（誤操作によるデータ損失防止）
      ]);
  }
}
