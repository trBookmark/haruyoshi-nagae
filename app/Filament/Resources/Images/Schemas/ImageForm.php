<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\ModelType;
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
use Illuminate\Validation\Rule;

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


        Section::make('基本情報')
          ->schema([

            Select::make('category_id')
              ->label('カテゴリ')
              ->options(fn () => Category::query()
                ->where('model_type', ModelType::IMAGE->value)
                ->excludingSystem()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('name_ja', 'id')
              )
              ->required()
              ->searchable()
              ->placeholder('選択してください')
              // 多重防御：ドロップダウン選択肢の改ざん（直接リクエスト送信）による
              // __system カテゴリへの割り当てをサーバーサイドでも禁止する
              ->rule(fn () => Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('model_type', ModelType::IMAGE->value)
                  ->excludingSystem()
                  ->where('is_active', true)
              )),

            TextInput::make('title')
              ->helperText('個別ページのタイトルとして使用（空欄可）')
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

            TextInput::make('caption')
              ->helperText('簡単な解説文、画像詳細ページのタイトルの下に表示（空欄可）')
              ->label('1行コメント')
              ->maxLength(255)
              ->placeholder('未設定'),

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
                  'name'        => $data['name'],
                  'is_active'   => $data['is_active'] ?? true,
                ])->getKey();
              }),

        ]),

        Section::make('画像ファイル')
          ->schema([

            // 編集画面のみ：現在登録されている画像のプレビューと差し替え警告を表示
            Placeholder::make('current_image_preview')
              ->label('現在の画像')
              ->content(function (?Image $record): HtmlString {
                if (! $record) {
                  return new HtmlString('');
                }

                $url = $record->imageUrl('medium');
                $warning = \App\Filament\Support\OverwriteWarning::html(); // 上書き警告

                return new HtmlString(
                  '<div style="display:flex; flex-direction:column; gap:12px;">'
                  . '<img src="' . e($url) . '" alt="現在の画像" style="max-width:320px; max-height:240px; object-fit:contain; border:1px solid #e5e7eb; border-radius:6px;">'
                  . $warning
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

        Section::make('公開設定')
          ->schema([

            Toggle::make('is_active')
              ->label('公開')
              ->default(true),

            Select::make('thumbnail_align')
              ->helperText('（必須）一覧のスクエア表示で画像を寄せる方向を指定')
              ->label('サムネイル配置')
              ->options(ThumbnailAlign::options())
              ->default(ThumbnailAlign::CENTER->value)
              ->required(),
        ]),

        Section::make('メモ')
          ->schema([

            Textarea::make('memo')
              ->helperText('管理用メモ（サイトには表示されません）')
              ->label('自分用メモ')
              ->rows(3)
              ->maxLength(1000),

            ])
          ]);

  }
}
