<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class EditProfile extends BaseEditProfile
{
  /**
   * avatar 差し替え前の保存パス
   * afterSave() で旧ファイルを削除するために保持する
   */
  private ?string $previousAvatar = null;

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([
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

        FileUpload::make('avatar')
          ->label('アバター画像')
          ->avatar()  // image/* + 円形プレビュー
          ->acceptedFileTypes((array) config('image.avatar.accepted_mime_types', ['image/jpeg', 'image/png']))
          ->maxSize(config('image.avatar.max_size_kb', 3072))
          ->disk(config('image.avatar.disk', 'public'))
          ->directory(config('image.avatar.directory', 'avatars'))
          ->deletable(),

        TextInput::make('password')
          ->label('パスワード')
          ->password()
          ->revealable()
          ->minLength(8)
          ->maxLength(255)
          // 空欄のまま保存した場合は更新しない
          ->dehydrated(fn ($state) => filled($state))
          ->dehydrateStateUsing(fn ($state) => bcrypt($state))
          ->helperText('変更する場合のみ入力してください'),

        TextInput::make('password_confirmation')
          ->label('パスワード（確認）')
          ->password()
          ->revealable()
          ->same('password')
          // 確認フィールドは DB に保存しない
          ->dehydrated(false),
      ]);
  }

  /**
   * mutateFormDataBeforeSave
   * 保存前処理：
   * - avatar の旧パスを退避（afterSave() での旧ファイル削除に使用）
   * - FileUpload が配列で state を返す場合に文字列へ正規化
   *   （Filament v5 の FileUpload は内部状態を配列で管理するため、
   *    パスワード変更など他フィールドと同時保存時に配列のまま渡されることがある）
   *
   * @param  array<string, mixed> $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeSave(array $data): array
  {
    $this->previousAvatar = $this->getRecord()->avatar;

    // FileUpload の state が配列で渡された場合、先頭要素を文字列として取り出す
    // avatar は単一ファイルのため複数要素は想定しない
    if (isset($data['avatar']) && is_array($data['avatar'])) {
      $data['avatar'] = array_values($data['avatar'])[0] ?? null;
    }

    return $data;
  }

  /**
   * afterSave
   * avatar が差し替えられた場合、旧ファイルをストレージから削除
   *
   * @return void
   */
  protected function afterSave(): void
  {
    $newAvatar = $this->getRecord()->fresh()->avatar;

    if ($this->previousAvatar && $this->previousAvatar !== $newAvatar) {
      Storage::disk(config('image.avatar.disk', 'public'))->delete($this->previousAvatar);
    }
  }
}
