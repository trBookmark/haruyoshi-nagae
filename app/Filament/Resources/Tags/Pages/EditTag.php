<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;

class EditTag extends EditRecord
{
  protected static string $resource = TagResource::class;

  /**
   * getHeaderActions
   * 使われているタグは削除ボタンを非表示にする
   * 削除成功後は一覧画面へリダイレクト
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()
        ->hidden(fn (Tag $record) => $record->isInUse())
        ->using(function (Tag $record): void {
          try {
            $record->delete();
          } catch (QueryException $e) {
            Notification::make()
              ->title('削除できません')
              ->body('このタグは画像またはブログ記事に使用されているため削除できません。')
              ->danger()
              ->send();

            return;
          }

          // ->using() を使うと ->successRedirectUrl() が発火しないため自前でリダイレクト
          redirect(TagResource::getUrl('index'));
        }),
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
      ->title('タグを保存しました')
      ->success()
      ->persistent();
  }
}
