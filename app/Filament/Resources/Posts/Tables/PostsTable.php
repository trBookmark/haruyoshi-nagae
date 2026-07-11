<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Image;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
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

        ImageColumn::make('eyecatch_url')
          ->label('アイキャッチ')
          ->getStateUsing(fn (Post $record): string =>
            $record->eyecatch
              ? $record->eyecatch->imageUrl('thumb')
              : Image::noImageUrl('thumb')
          )
          ->circular(false)
          ->width(80)
          ->height(60),

        TextColumn::make('title')
          ->label('タイトル')
          ->searchable()
          ->sortable(),

        TextColumn::make('status')
          ->label('公開状態')
          ->badge()
          ->formatStateUsing(fn (PostStatus $state): string => $state->label())
          ->color(fn (PostStatus $state): string => match ($state) {
            PostStatus::DRAFT     => 'gray',
            PostStatus::PUBLISHED => 'success',
            PostStatus::PRIVATE   => 'warning',
          })
          ->sortable(),

        TextColumn::make('tags.name')
          ->label('タグ')
          ->badge()
          ->separator(',')
          ->placeholder('―'),

        TextColumn::make('published_at')
          ->label('公開日時')
          ->dateTime('Y/m/d H:i')
          ->placeholder('―')
          ->sortable(),

        TextColumn::make('created_at')
          ->label('作成日時')
          ->dateTime('Y/m/d H:i')
          ->sortable()
          ->toggleable(), // 最近追加した記事を探しやすいようデフォルト表示（列トグルで非表示可）

      ])
      ->filters([

        SelectFilter::make('status')
          ->label('公開状態')
          ->options(PostStatus::options()),

        SelectFilter::make('tags')
          ->label('タグ')
          ->relationship('tags', 'name'),

      ])
      ->recordActions([

        EditAction::make(),

        DeleteAction::make()
          ->using(fn (Post $record) => $record->deleteWithEyecatch()),

      ])
      ->toolbarActions([
        // 一括削除なし（誤操作によるデータ損失防止）
      ]);
  }
}
