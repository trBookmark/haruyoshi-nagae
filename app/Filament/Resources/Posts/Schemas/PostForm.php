<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Support\OverwriteWarning;
use App\Models\Post;
use App\Models\Tag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PostForm
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

        Section::make('基本情報')
          ->schema([

            TextInput::make('title')
              ->label('タイトル')
              ->required()
              ->maxLength(255),

            Select::make('status')
              ->label('公開状態')
              ->options(PostStatus::options())
              ->default(PostStatus::DRAFT->value)
              ->required(),

            DateTimePicker::make('published_at')
              ->label('公開日時')
              ->helperText('未設定のまま「公開」で保存すると現在日時が自動設定されます。過去の日時も指定可能')
              ->seconds(false),

            Select::make('tags')
              ->required()
              ->helperText('（必須）複数設定可、右の＋ボタンからタグの新規作成が可能')
              ->label('タグ')
              ->multiple()
              ->relationship('tags', 'name')
              ->options(fn () => Tag::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
              )
              ->placeholder('タグを選択')
              ->createOptionForm([
                TextInput::make('name')
                  ->label('タグ名')
                  ->required()
                  ->maxLength(190)
                  ->unique('tags', 'name'),

                Toggle::make('is_active')
                  ->label('使用可否')
                  ->default(true),
              ])
              ->createOptionUsing(function (array $data): int {
                return Tag::create([
                  'name'      => $data['name'],
                  'is_active' => $data['is_active'] ?? true,
                ])->getKey();
              }),

            Textarea::make('excerpt')
              ->label('抜粋')
              ->helperText('一覧表示用の短い抜粋（空欄可）')
              ->rows(3)
              ->maxLength(1000),

        ]),

        Section::make('本文')
          ->schema([

            RichEditor::make('content')
              ->label('本文')
              ->required()
              ->toolbarButtons([
                  ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                  ['h2', 'h3'],
                  ['alignStart', 'alignCenter', 'alignEnd'],
                  ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                  // attachFiles は無効化
                  // 画像パイプラインをバイパスする：EXIF 除去、リサイズ・Retina対応、重複チェックなし、images テーブル管理外
                  // URL が本文 HTML に直書きされるため、 CDN 移行や ドメイン変更時に全記事の一括置換が必要になる
                  // 記事を削除しても本文内画像のファイルが残り続ける
                  // ['table', 'attachFiles'],
                  ['table'],
                  ['undo', 'redo'],
              ]),

        ]),

        Section::make('アイキャッチ画像')
          ->schema([

            // 編集画面のみ：現在のアイキャッチのプレビューと差し替え警告を表示
            Placeholder::make('current_eyecatch_preview')
              ->label('現在のアイキャッチ')
              ->content(function (?Post $record): HtmlString {
                if (! $record || ! $record->eyecatch) {
                  return new HtmlString('未設定');
                }

                $url = $record->eyecatch->imageUrl('medium');
                $warning = OverwriteWarning::html(); // 上書き警告

                return new HtmlString(
                  '<div style="display:flex; flex-direction:column; gap:12px;">'
                  . '<img src="' . e($url) . '" alt="現在のアイキャッチ" style="max-width:320px; max-height:240px; object-fit:contain; border:1px solid #e5e7eb; border-radius:6px;">'
                  . $warning
                  . '</div>'
                );
              })
              ->hidden(fn ($livewire) => $livewire instanceof CreatePost),

            FileUpload::make('eyecatch_upload')
              ->label('アイキャッチ画像（空欄可）')
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
              // Filament の自動保存を使わず handleRecordCreation/Update で処理するため
              // storeFiles を無効化してフォームデータとして UploadedFile を受け取る
              ->storeFiles(false),

            Toggle::make('remove_eyecatch')
              ->label('アイキャッチを削除する')
              ->helperText('ON で保存するとアイキャッチを削除します。新しいファイルを指定した場合は差し替えが優先されます')
              ->default(false)
              ->visible(fn (?Post $record) => (bool) $record?->image_id),

        ]),

        Section::make('メモ')
          ->schema([

            Textarea::make('memo')
              ->helperText('管理用メモ（サイトには表示されません）')
              ->label('自分用メモ')
              ->rows(3)
              ->maxLength(1000),

        ]),

      ]);
  }
}
