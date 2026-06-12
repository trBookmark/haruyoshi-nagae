<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\ThumbnailAlign;
use App\Filament\Resources\Images\Pages\CreateImage;
use App\Models\Category;
use App\Models\Image;
use App\Models\Tag;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ImageForm
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

        Section::make('画像ファイル')
          ->schema([

            // 編集画面で表示：現在登録されている画像のプレビュー＋差し替え警告
            Placeholder::make('current_image_preview')
              ->label('現在の画像')
              ->content(function (?Image $record): HtmlString {
                if (! $record) {
                  return new HtmlString('');
                }

                $url = $record->imageUrl('medium');

                return new HtmlString(
                  '<div style="display:flex; flex-direction:column; gap:12px;">'
                  . '<img src="' . e($url) . '" alt="現在の画像" style="max-width:320px; max-height:240px; object-fit:contain; border:1px solid #e5e7eb; border-radius:6px;">'
                  . '<div style="display:flex; align-items:center; gap:8px; padding:10px 14px; background:#fff7ed; border:1px solid #fed7aa; border-radius:6px; color:#9a3412; font-size:0.875rem;">'
                  . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px; height:18px; flex-shrink:0;">'
                  . '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />'
                  . '</svg>'
                  . '<span>下のアップロード欄にファイルをアップロードすると、<strong>現在の画像が上書きされます</strong>。変更しない場合は空のままにしてください。</span>'
                  . '</div>'
                  . '</div>'
                );
              })
              ->hidden(fn ($livewire) => $livewire instanceof CreateImage),

            FileUpload::make('image')
              ->label('画像ファイル')
              ->image()
              ->acceptedFileTypes(config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']))
              ->maxSize(config('image.upload_max_size_kb', 2097152))
              // 作成時のみ必須、編集時は差し替えなしを許容
              ->required(fn ($livewire) => $livewire instanceof CreateImage)
              ->helperText(function (): string {
                $allowedMimes = config('image.allowed_mime_types', ['image/jpeg', 'image/png', 'image/gif']);
                $labels = array_map(
                  fn (string $mime) => strtoupper(explode('/', $mime)[1]),
                  $allowedMimes,
                );

                return implode('・', $labels) . ' をアップロードできます（GIF アニメーション可）';
              })
              // Filament の自動保存を使わず handleRecordCreation/Update で処理するため
              // storeFiles を無効化してフォームデータとして UploadedFile を受け取る
              ->storeFiles(false),

          ]),

        Section::make('基本情報')
          ->schema([

            Select::make('category_id')
              ->label('カテゴリ')
              ->options(fn () => Category::query()
                // 編集者には __system カテゴリを非表示
                ->when(
                  ! auth()->user()?->isAdmin(),
                  fn ($q) => $q->where('name', 'not like', '\\_\\_%')
                )
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('name_ja', 'id')
              )
              ->required()
              ->searchable()
              ->placeholder('選択してください'),

            TextInput::make('title')
              ->helperText('右の＋ボタンからタグを作成できます。複数設定可能。')
              ->label('タイトル')
              ->maxLength(255)
              ->placeholder('未設定'),

            TextInput::make('year')
              ->label('制作年')
              ->numeric()
              ->minValue(1900)
              ->maxValue(now()->year + 1)
              ->default(now()->year)
              ->placeholder((string) now()->year),

            Select::make('thumbnail_align')
              ->helperText('一覧のスクエア表示で寄せる方向を指定')
              ->label('サムネイル配置')
              ->options(ThumbnailAlign::options())
              ->default(ThumbnailAlign::CENTER->value)
              ->required(),

            TextInput::make('caption')
              ->helperText('1行の解説文。空欄可。個別ページのタイトルの下に表示されます。')
              ->label('キャプション')
              ->maxLength(255)
              ->placeholder('未設定'),

            Select::make('tags')
              ->required()
              ->helperText('複数設定可能。右の＋ボタンからタグを新規作成できます。')
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

                Textarea::make('description')
                  ->label('概要')
                  ->rows(3)
                  ->maxLength(255)
                  ->helperText('タグ一覧ページの Twitterカードの説明文などに使用'),

                Toggle::make('is_active')
                  ->label('使用可否')
                  ->default(true),
              ])
              ->createOptionUsing(function (array $data): int {
                return Tag::create([
                  'name'        => $data['name'],
                  'description' => $data['description'] ?? null,
                  'is_active'   => $data['is_active'] ?? true,
                ])->getKey();
              }),

          ]),

        Section::make('公開設定・メモ')
          ->schema([

            Toggle::make('is_active')
              ->label('公開')
              ->default(true),

            Textarea::make('memo')
              ->helperText('管理用メモ（サイトには表示されません）')
              ->label('メモ（自分用）')
              ->rows(3)
              ->maxLength(1000),

          ]),

      ]);
  }
}
