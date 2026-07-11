<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Services\Post\EyecatchService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePost extends CreateRecord
{
  protected static string $resource = PostResource::class;

  /**
   * handleRecordCreation
   * アイキャッチ指定がある場合は EyecatchService 経由で Image レコードを作成してから、
   * Post レコードを作成する
   * タグは Filament の relationship 機能で作成後に自動保存
   *
   * ValidationException（不正ファイル・重複等）はキャッチして通知を表示し、
   * halt() でフォームの遷移を止める
   *
   * @param  array<string, mixed> $data フォームデータ（eyecatch_upload キーに UploadedFile が入る）
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordCreation(array $data): Model
  {
    $imageId = null;

    if (! empty($data['eyecatch_upload'])) {
      /** @var \App\Services\Post\EyecatchService $service */
      $service = app(EyecatchService::class);

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
    }

    return Post::create([
      'status'       => $data['status'],
      'image_id'     => $imageId,
      'title'        => $data['title'],
      'content'      => $data['content'],
      'excerpt'      => $data['excerpt'] ?? null,
      'memo'         => $data['memo'] ?? null,
      'published_at' => $data['published_at'] ?? null,
    ]);
  }

  /**
   * getCreatedNotification
   * 作成成功時の通知
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getCreatedNotification(): ?Notification
  {
    return Notification::make()
      ->title('記事を登録しました')
      ->success();
  }

  /**
   * getRedirectUrl
   * 「作成」ボタン押下後のリダイレクト先
   * 一覧画面へ戻る
   *
   * @return string
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
