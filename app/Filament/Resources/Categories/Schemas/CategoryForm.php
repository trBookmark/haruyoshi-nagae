<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\ModelType;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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

        Section::make('カテゴリ設定')
          ->schema([
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

          Toggle::make('is_active')
            ->label('使用可否')
            ->default(true),

          Textarea::make('description')
            ->label('概要')
            ->rows(3)
            ->maxLength(255)
            ->helperText('カテゴリページの説明文などに使用'),
        ]),

        Section::make('カバー画像設定')
          ->schema([
          // カバー画像：現在の画像プレビュー（編集画面のみ）
          Placeholder::make('cover_image_preview')
            ->label('現在のカバー画像')
            ->content(function (?Category $record): HtmlString {
              if (! $record?->coverImage) {
                return new HtmlString('<span style="color:#6b7280; font-size:0.875rem;">未設定</span>');
              }

              $url = $record->coverImage->imageUrl('thumb');
              $warning = \App\Filament\Support\OverwriteWarning::html(); // 上書き警告

              return new HtmlString(
                '<img src="' . e($url) . '" alt="現在のカバー画像"'
                . ' style="max-width:160px; max-height:120px; object-fit:contain;'
                . ' border:1px solid #e5e7eb; border-radius:6px;">'
                . $warning
              );
            }),

          // カバー画像：アップロード
          // storeFiles(false)：Filament の自動保存を使わず EditCategory::handleRecordUpdate() で処理
          // アップロード先は __system カテゴリに固定（EditCategory 側で設定）
          FileUpload::make('cover_image')
            ->label('カバー画像をアップロード')
            ->image()
            ->acceptedFileTypes(config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']))
            ->maxSize(config('image.upload_max_size_kb', 2097152))
            ->helperText(function (): string {
              $allowedMimes = config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']);
              $labels = array_map(
                fn (string $mime) => strtoupper(explode('/', $mime)[1]),
                $allowedMimes,
              );

              return implode('・', $labels) . ' をアップロードできます。アップロードすると現在の画像が置き換えられます。';
            })
            ->storeFiles(false),
        ]),

      ]);
  }
}
