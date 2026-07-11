<?php

namespace App\Filament\Resources\Tags\Tables;

use App\Models\Tag;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;

class TagsTable
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
      ->columns([

        TextColumn::make('name')
          ->label('タグ名')
          ->searchable()
          ->sortable(),

        TextColumn::make('description')
          ->label('概要')
          ->limit(50)
          ->placeholder('―'),

        TextColumn::make('images_count')
          // 使用数を表示。使用中かどうかを明示、削除ボタン使用不可の場合の理由付け
          ->label('タグ使用画像数')
          ->counts('images')
          ->sortable(),

        TextColumn::make('posts_count')
          // 使用数を表示。使用中かどうかを明示、削除ボタン使用不可の場合の理由付け
          ->label('タグ使用記事数')
          ->counts('posts')
          ->sortable(),

        IconColumn::make('is_active')
          ->label('使用可否')
          ->boolean()
          ->sortable(),

        TextColumn::make('created_at')
          ->label('作成日時')
          ->dateTime('Y/m/d H:i')
          ->sortable()
          ->toggleable(), // 最近追加したタグを探しやすいようデフォルト表示（列トグルで非表示可）

      ])
      ->filters([

        TernaryFilter::make('is_active')
          ->label('使用可否')
          ->trueLabel('使用')
          ->falseLabel('無効'),

      ])
      ->recordActions([

        EditAction::make(),

        DeleteAction::make()
          ->hidden(fn (Tag $record) => $record->isInUse())
          ->using(function (Tag $record) {
            try {
              $record->delete();
            } catch (QueryException $e) {
              // 万が一 isInUse() をすり抜けた場合の FK 制約違反をユーザーへ通知
              Notification::make()
                ->title('削除できません')
                ->body('このタグは画像またはブログ記事に使用されているため削除できません。')
                ->danger()
                ->send();
            }
          }),

      ])
      ->toolbarActions([
        // 一括削除なし（使用中タグの誤削除防止）
      ])
      // デフォルトソートをタグ名昇順に変更
      ->defaultSort('name', 'asc');
  }
}
