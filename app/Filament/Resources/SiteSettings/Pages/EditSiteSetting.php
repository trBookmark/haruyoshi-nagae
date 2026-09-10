<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\Category;
use App\Models\Image;
use App\Services\Image\ImageUploadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditSiteSetting extends EditRecord
{
  protected static string $resource = SiteSettingResource::class;

  /**
   * getHeaderActions
   * 削除ボタンなし（サイト設定は削除禁止）
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [];
  }

  /**
   * handleRecordUpdate
   * 画像がアップロードされた場合のみ ImageUploadService 経由で処理
   * 保存先カテゴリ：__system 固定（カテゴリカバー画像と同じ）
   * 差し替え時：旧 Image が他から参照されていなければレコード＋ファイルを削除
   *
   * ValidationException（不正ファイル・重複等）はキャッチして通知を表示し、
   * halt() でフォームの遷移を止める
   *
   * @param  \Illuminate\Database\Eloquent\Model $record
   * @param  array<string, mixed>                $data
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    /** @var \App\Models\SiteSetting $record */

    if (! empty($data['image_upload'])) {
      $systemCategory = Category::where('name', Category::SYSTEM_NAME)->firstOrFail();

      /** @var \App\Services\Image\ImageUploadService $service */
      $service = app(ImageUploadService::class);

      $result = null;
      try {
        $result = $service->upload($data['image_upload'], $systemCategory->id);
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

      // 旧画像レコードを退避（FK 付け替え後に後始末）
      $previousImage = $record->image;

      $newImage = Image::create(array_merge(
        $result->toArray(),
        ['category_id' => $systemCategory->id],
      ));

      $record->update([
        'description' => $data['description'],
        'image_alt'   => $data['image_alt'] ?? null,
        'image_id'    => $newImage->id,
      ]);

      // 旧画像の後始末（他から参照されていない場合のみ削除）
      if ($previousImage && ! $previousImage->isInUse()) {
        $previousImage->delete(); // Image::booted() の deleting イベントで Storage ファイルも削除
      }

      return $record;
    }

    // 画像なし：文面と代替テキストのみ更新
    $record->update([
      'description' => $data['description'],
      'image_alt'   => $data['image_alt'] ?? null,
    ]);

    return $record;
  }

  /**
   * getRedirectUrl
   * 保存後は一覧画面へリダイレクト（マスタ系の既存方針と同様）
   *
   * @return string
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  /**
   * getSavedNotification
   * 保存完了通知
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getSavedNotification(): ?Notification
  {
    return Notification::make()
      ->title('サイト設定を保存しました')
      ->success();
  }
}
