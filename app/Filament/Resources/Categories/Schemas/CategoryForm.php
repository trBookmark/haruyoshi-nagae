<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\ModelType;
use App\Models\Category;
use App\Models\Image;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
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
          ->label('カテゴリ名（スラッグ）')
          ->required()
          ->maxLength(190)
          ->unique(ignoreRecord: true)
          // 編集者：編集不可（URL・パス生成に使用するため管理者のみに限定）
          ->disabled(fn () => ! auth()->user()?->isAdmin()),

        TextInput::make('name_ja')
          ->label('カテゴリ表示名')
          ->required()
          ->maxLength(255),

        // model_type：管理者のみ表示
        Select::make('model_type')
          ->label('使用モデル')
          ->options([
            ''                   => 'プレイリスト親（サブカテゴリあり）',
            ModelType::IMAGE->value => ModelType::IMAGE->label(),
            ModelType::POST->value  => ModelType::POST->label(),
          ])
          ->placeholder('選択してください')
          ->visible(fn () => auth()->user()?->isAdmin()),

        // image_id：__system カテゴリの画像から選択（暫定実装）
        // TODO: ImageUploadService 完成後、CategoryForm 上で直接アップロードできるよう差し替える
        Select::make('image_id')
          ->label('カバー画像')
          ->options(fn () => Image::whereHas('category', fn ($q) => $q->where('name', Category::SYSTEM_NAME))
            ->get()
            ->mapWithKeys(fn (Image $image) => [$image->id => $image->title ?? $image->storage_file_name])
            ->toArray()
          )
          ->searchable()
          ->placeholder('未設定')
          ->helperText('カバー画像はカテゴリ用にアップロードされた画像から選択できます'),

        Toggle::make('is_active')
          ->label('使用可否')
          ->default(true),

        Textarea::make('description')
          ->label('概要')
          ->rows(3)
          ->maxLength(255)
          ->helperText('カテゴリページの説明文などに使用'),

      ]);
  }
}
