<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsTable
{
  /**
   * configure
   * Seeder 固定のみ
   * フィルタ・並び替え・削除・一括操作なし
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([

        TextColumn::make('label')
          ->label('設定項目'),

        TextColumn::make('description')
          ->label('文面')
          ->limit(40)
          ->placeholder('―'),

        ImageColumn::make('image_url')
          ->label('画像')
          ->getStateUsing(fn (SiteSetting $record): ?string =>
            $record->image?->imageUrl('thumb')
          )
          ->circular(false)
          ->width(80)
          ->height(60),

        TextColumn::make('updated_at')
          ->label('更新日時')
          ->dateTime('Y/m/d H:i')
          ->sortable(),

      ])
      ->recordActions([
        EditAction::make(),
        // 削除ボタンなし（サイト設定は削除禁止・Seeder 固定）
      ])
      ->toolbarActions([
        // 一括操作なし
      ])
      ->defaultSort('id', 'asc');
  }
}
