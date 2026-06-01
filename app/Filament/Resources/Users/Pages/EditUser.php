<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  /**
   * avatar 差し替え前の保存パス
   * afterSave() で旧ファイルを削除するために保持する
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
   * mutateFormDataBeforeSave
   * 保存前に avatar の旧パスを退避する
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
   * avatar が差し替えられた場合、旧ファイルをストレージから削除する
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
