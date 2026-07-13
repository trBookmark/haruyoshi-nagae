<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Filament\Support\OverwriteWarning;
use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SiteSettingForm
{
  /**
   * configure
   * Edit ページ専用（Create ページなし）
   * $record は常に存在する前提
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([

        Section::make('基本情報')
          ->schema([

            // タイプ説明は Seeder 管理のため表示のみ（編集不可）
            Placeholder::make('label_display')
              ->label('設定項目')
              ->content(fn (?SiteSetting $record): string => $record?->label ?? ''),

            Textarea::make('description')
              ->label('文面')
              ->helperText('（必須）該当ページに表示される短い文章（255文字まで、改行も反映されます）')
              ->required()
              ->rows(4)
              ->maxLength(255),

        ]),

        // 画像使用（image_required=true）の設定項目にのみ表示
        Section::make('画像')
          ->schema([

            // 現在の画像のプレビューと差し替え警告を表示
            Placeholder::make('current_image_preview')
              ->label('現在の画像')
              ->content(function (?SiteSetting $record): HtmlString {
                if (! $record || ! $record->image) {
                  return new HtmlString('未設定');
                }

                $url = $record->image->imageUrl('medium');
                $warning = OverwriteWarning::html(); // 上書き警告

                return new HtmlString(
                  '<div style="display:flex; flex-direction:column; gap:12px;">'
                  . '<img src="' . e($url) . '" alt="現在の画像" style="max-width:320px; max-height:240px; object-fit:contain; border:1px solid #e5e7eb; border-radius:6px;">'
                  . $warning
                  . '</div>'
                );
              }),

            FileUpload::make('image_upload')
              ->label('画像（差し替え時のみ指定）')
              ->image()
              ->acceptedFileTypes(config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']))
              ->maxSize(config('image.upload_max_size_kb', 2097152))
              ->helperText(function (): string {
                $allowedMimes = config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']);
                $labels = array_map(
                  fn (string $mime) => strtoupper(explode('/', $mime)[1]),
                  $allowedMimes,
                );

                return implode('・', $labels) . ' をアップロードできます';
              })
              // Filament の自動保存を使わず handleRecordUpdate で処理するため
              // storeFiles を無効化してフォームデータとして UploadedFile を受け取る
              ->storeFiles(false),

            TextInput::make('image_alt')
              ->label('画像の代替テキスト')
              ->helperText('（空欄可）画像の内容を短く説明する文。スクリーンリーダーの読み上げや、画像を表示できない環境で使われます（255文字まで）')
              ->maxLength(255),

        ])
          ->visible(fn (?SiteSetting $record): bool => (bool) $record?->image_required),

        ]);
  }
}
