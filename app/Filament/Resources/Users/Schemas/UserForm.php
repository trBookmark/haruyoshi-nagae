<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
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

            TextInput::make('name')
              ->label('ユーザー名')
              ->required()
              ->maxLength(190)
              ->unique(ignoreRecord: true),

            TextInput::make('email')
              ->label('メールアドレス')
              ->email()
              ->required()
              ->maxLength(190)
              ->unique(ignoreRecord: true),

            Select::make('role')
              ->label('権限')
              ->options(UserRole::options())
              ->required()
              // 自分自身の編集時は権限変更を禁止
              // disabled 時は Filament が値を保存しないため、DB は変更されない
              ->disabled(fn ($record) => $record?->id === auth()->id()),

          ]),

        Section::make('アバター')
          ->schema([

            FileUpload::make('avatar')
              ->label('アバター画像')
              ->avatar()  // image/* + 円形プレビュー
              ->acceptedFileTypes((array) config('image.avatar.accepted_mime_types', ['image/jpeg', 'image/png']))
              ->maxSize(config('image.avatar.max_size_kb', 3072))
              ->disk(config('image.avatar.disk', 'public'))
              ->directory(config('image.avatar.directory', 'avatars'))
              ->deletable(),

          ]),

        Section::make('パスワード')
          ->schema([

            TextInput::make('password')
              ->label('パスワード')
              ->password()
              ->revealable()
              ->required(fn ($livewire) => $livewire instanceof CreateUser)
              ->minLength(8)
              ->maxLength(255)
              // 空欄のまま保存した場合は更新しない
              ->dehydrated(fn ($state) => filled($state))
              ->dehydrateStateUsing(fn ($state) => bcrypt($state))
              ->helperText(
                fn ($livewire) => $livewire instanceof CreateUser
                  ? null
                  : '変更する場合のみ入力してください'
              ),

            TextInput::make('password_confirmation')
              ->label('パスワード（確認）')
              ->password()
              ->revealable()
              ->required(fn ($livewire) => $livewire instanceof CreateUser)
              ->same('password')
              // 確認フィールドは DB に保存しない
              ->dehydrated(false),

          ]),

      ]);
  }
}
