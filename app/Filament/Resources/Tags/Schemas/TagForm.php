<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TagForm
{
  /**
   * configure
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([

        TextInput::make('name')
          ->label('タグ名')
          ->required()
          ->maxLength(190)
          ->unique(ignoreRecord: true), // 編集時は自身のレコードを無視してユニークチェック

        Textarea::make('description')
          ->label('概要')
          ->rows(3)
          ->maxLength(255)
          ->helperText('タグ一覧ページの Twitterカードの説明文などに使用'),

        Toggle::make('is_active')
          ->label('使用可否')
          ->default(true),

      ]);
  }
}
