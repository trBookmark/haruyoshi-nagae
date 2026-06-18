<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
  protected static string $resource = CategoryResource::class;

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
      ->title('カテゴリを作成しました')
      ->success()
      ->persistent();
  }
}
