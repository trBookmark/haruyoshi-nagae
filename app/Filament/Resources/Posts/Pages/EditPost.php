<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Services\Post\EyecatchService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPost extends EditRecord
{
  protected static string $resource = PostResource::class;

  /**
   * getHeaderActions
   * 削除はアイキャッチの後始末を含む deleteWithEyecatch() で実行
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()
        ->using(function (Post $record): void {
          $record->deleteWithEyecatch();

          // ->using() を使うと ->successRedirectUrl() が発火しないため自前でリダイレクト
          redirect(PostResource::getUrl('index'));
        }),
    ];
  }

  /**
   * handleRecordUpdate
   * アイキャッチの差し替え・削除を処理して Post を更新する
   * 優先順位：新規ファイル指定 ＞ 削除トグル ＞ 現状維持
   * 不要になった旧アイキャッチは更新後に後始末する
   *
   * ValidationException（不正ファイル・重複等）はキャッチして通知を表示し、
   * halt() でフォームの遷移を止める
   *
   * @param  \Illuminate\Database\Eloquent\Model  $record
   * @param  array<string, mixed>                 $data
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    /** @var \App\Models\Post $record */

    /** @var \App\Services\Post\EyecatchService $service */
    $service = app(EyecatchService::class);

    $oldEyecatch = $record->eyecatch;
    $imageId     = $record->image_id;

    if (! empty($data['eyecatch_upload'])) {
      try {
        $imageId = $service->create($data['eyecatch_upload'])->id;
      } catch (ValidationException $e) {
        $messages = collect($e->errors())->flatten()->implode(' ');

        Notification::make()
          ->title('アイキャッチをアップロードできませんでした')
          ->body($messages)
          ->danger()
          ->persistent()
          ->send();

        $this->halt();
      }
    } elseif (! empty($data['remove_eyecatch'])) {
      $imageId = null;
    }

    $record->update([
      'status'       => $data['status'],
      'image_id'     => $imageId,
      'title'        => $data['title'],
      'content'      => $data['content'],
      'excerpt'      => $data['excerpt'] ?? null,
      'memo'         => $data['memo'] ?? null,
      'published_at' => $data['published_at'] ?? null,
    ]);

    // 差し替え・削除で不要になった旧アイキャッチの後始末
    if ($oldEyecatch && $oldEyecatch->id !== $imageId) {
      $service->deleteIfUnused($oldEyecatch);
    }

    // キャッシュ済みの旧リレーションを破棄し、保存後の再描画で新しいアイキャッチを表示させる
    $record->unsetRelation('eyecatch');

    return $record;
  }
}
