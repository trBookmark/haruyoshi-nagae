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
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()
        ->hidden(fn (Tag $record) => $record->isInUse()) // 使用中タグは削除ボタン自体を非表示
        ->using(function (Tag $record) {
          try {
            $record->delete();
          } catch (QueryException $e) {
            // 万が一 isInUse() をすり抜けた場合の FK 制約違反をユーザーへ通知
            Notification::make()
              ->title('削除できません')
              ->body('このタグは画像またはブログ記事に使用されているため削除できません。')
              ->danger()
              ->send();
          }
        }),
    ];
  }
}
