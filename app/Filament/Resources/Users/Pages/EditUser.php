<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  /**
   * avatar 差し替え前の保存パス
   * afterSave() で旧ファイルを削除するために保持
   */
  private ?string $previousAvatar = null;

  /**
   * getHeaderActions
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make(),
    ];
  }

  /**
   * getRedirectUrl
   * 保存後：一覧画面へリダイレクト
   *
   * @return string
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  /**
   * getSavedNotification
   * 保存完了通知（persistent で強調）
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getSavedNotification(): ?Notification
  {
    return Notification::make()
      ->title('ユーザーを保存しました')
      ->success()
      ->persistent();
  }

  /**
   * mutateFormDataBeforeSave
   * 保存前に avatar の旧パスを退避
   *
   * @param  array $data
   * @return array
   */
  protected function mutateFormDataBeforeSave(array $data): array
  {
    $this->previousAvatar = $this->getRecord()->avatar;

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
    $newAvatar = $this->getRecord()->avatar;

    if ($this->previousAvatar && $this->previousAvatar !== $newAvatar) {
      Storage::disk(config('image.avatar.disk'))->delete($this->previousAvatar);
    }
  }
}
