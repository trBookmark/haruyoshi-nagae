<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use App\Filament\Support\SystemCategoryGuard;
use App\Models\Image;
use App\Services\Image\ImageUploadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateImage extends CreateRecord
{
  protected static string $resource = ImageResource::class;

  /**
   * handleRecordCreation
   * ImageUploadService 経由でファイルを処理し、Image レコードを作成する
   * Filament デフォルトの Model::create() を置き換える
   *
   * ValidationException（__system 指定・重複・不正ファイル等）はキャッチして通知を表示し、
   * halt() でフォームの遷移を止める
   *
   * @param  array<string, mixed> $data フォームデータ（image キーに UploadedFile が入る）
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordCreation(array $data): Model
  {
    $file = $data['image'];

    /** @var \App\Services\Image\ImageUploadService $service */
    $service = app(ImageUploadService::class);

    try {
      // 多重防御：ImageForm::category_id の ->rule() を
      // 直接リクエスト送信で回避された場合の保険
      SystemCategoryGuard::assertNotSystemCategory((int) $data['category_id']);

      $result = $service->upload($file, (int) $data['category_id']);
    } catch (ValidationException $e) {
      $messages = collect($e->errors())->flatten()->implode(' ');

      Notification::make()
        ->title('アップロードできませんでした')
        ->body($messages)
        ->danger()
        ->persistent()
        ->send();

      $this->halt();
    }

    return Image::create(array_merge(
      $result->toArray(),
      [
        'category_id'     => $data['category_id'],
        'is_active'       => $data['is_active'] ?? true,
        'sort_order'      => $data['sort_order'] ?? 0,
        'year'            => $data['year'] ?? now()->year,
        'thumbnail_align' => $data['thumbnail_align'],
        'title'           => $data['title'] ?? null,
        'caption'         => $data['caption'] ?? null,
        'memo'            => $data['memo'] ?? null,
      ],
    ));
  }

  /**
   * getCreatedNotification
   * 作成成功時の通知
   * 「作成」「保存して続けて作成」どちらのボタンでも表示される
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getCreatedNotification(): ?Notification
  {
    return Notification::make()
      ->title('画像を登録しました')
      ->success();
  }

  /**
   * getRedirectUrl
   * 「作成」ボタン押下後のリダイレクト先
   * 一覧画面へ戻る（誤って上書き登録を防ぐため）
   *
   * @return string
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  /**
   * getCreateAnotherRedirectUrl
   * 「保存して続けて作成」ボタン押下後のリダイレクト先
   * 新規作成画面へ遷移
   *
   * @return string
   */
  protected function getCreateAnotherRedirectUrl(): string
  {
    return $this->getResource()::getUrl('create');
  }
}
