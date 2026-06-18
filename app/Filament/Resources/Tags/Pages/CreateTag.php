<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
  protected static string $resource = TagResource::class;

  /**
   * getRedirectUrl
   * 作成後：一覧画面へリダイレクト
   *
   * @return string
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  /**
   * getCreatedNotification
   * 作成完了通知（persistent で強調）
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getCreatedNotification(): ?Notification
  {
    return Notification::make()
      ->title('タグを作成しました')
      ->success()
      ->persistent();
  }
}
