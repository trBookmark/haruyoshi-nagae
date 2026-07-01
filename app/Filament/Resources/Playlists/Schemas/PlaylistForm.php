<?php

namespace App\Filament\Resources\Playlists\Schemas;

use App\Models\Category;
use App\Models\Playlist;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PlaylistForm
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

        Section::make('サブカテゴリ設定')
          ->schema([
          Select::make('category_id')
            ->label('親カテゴリ')
            ->options(fn () => Category::query()
              ->whereNull('model_type')
              ->where('is_active', true)
              ->orderBy('sort_order')
              ->pluck('name_ja', 'id')
            )
            ->required()
            ->searchable()
            ->placeholder('選択してください'),

          TextInput::make('name')
            ->label('サブカテゴリ名')
            ->required()
            ->maxLength(255),

          TextInput::make('yt_playlist_id')
            ->label('YouTube プレイリスト ID')
            ->required()
            ->maxLength(64)
            ->unique(table: 'playlists', column: 'yt_playlist_id', ignoreRecord: true)
            ->helperText('YouTube プレイリストページの URL に含まれる ID（例: PLxxxxxxxxxxxxxx）'),

          Toggle::make('is_active')
            ->label('使用可否')
            ->default(true),

          Textarea::make('description')
            ->label('概要')
            ->rows(3)
            ->maxLength(255)
            ->helperText('プレイリストページの説明文などに使用'),
        ]),

        Section::make('カバー画像設定')
          ->schema([
          // 編集画面のみ：現在のカバー画像プレビューと上書き警告を表示
          Placeholder::make('thumbnail_preview')
            ->label('現在のカバー画像')
            ->content(function (?Playlist $record): HtmlString {
              if (! $record) {
                return new HtmlString('');
              }

              $warning = \App\Filament\Support\OverwriteWarning::html(); // 上書き警告
              if (! $record->thumbnail) {
                return new HtmlString(
                  '<div style="display:flex; flex-direction:column; gap:12px;">'
                  . '<span style="color:#6b7280; font-size:0.875rem;">未設定</span>'
                  . '</div>'
                );
              }

              $url = $record->thumbnail->imageUrl('thumb');

              return new HtmlString(
                '<div style="display:flex; flex-direction:column; gap:12px;">'
                . '<img src="' . e($url) . '" alt="現在のカバー画像"'
                . ' style="max-width:160px; max-height:120px; object-fit:contain;'
                . ' border:1px solid #e5e7eb; border-radius:6px;">'
                . $warning
                . '</div>'
              );
            }),

          // カバー画像アップロード
          // storeFiles(false)：Filament の自動保存を使わず
          // CreatePlaylist / EditPlaylist::handleRecord*() で ImageUploadService 経由で処理
          // アップロード先は __system カテゴリに固定（Pages 側で設定）
          FileUpload::make('thumbnail')
            ->label('カバー画像')
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
