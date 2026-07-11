<?php

namespace App\Filament\Resources\Playlists\Pages;

use App\Filament\Resources\Playlists\PlaylistResource;
use App\Models\Category;
use App\Models\Image;
use App\Models\Playlist;
use App\Services\Image\ImageUploadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CreatePlaylist extends CreateRecord
{
  protected static string $resource = PlaylistResource::class;

  /**
   * handleRecordCreation
   * カバー画像がアップロードされた場合は ImageUploadService 経由で処理
   * Image レコードを作成してから Playlist レコードを作成
   * カバー画像なしの場合は image_id を null で作成
   *
   * @param  array<string, mixed> $data フォームデータ
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordCreation(array $data): Model
  {
    $imageId = null;

    if (! empty($data['thumbnail'])) {
      $file = $data['thumbnail'];

      // storeFiles(false) 時は TemporaryUploadedFile で渡されるため UploadedFile に変換
      if (! $file instanceof UploadedFile) {
        $file = new UploadedFile(
          path:         $file->getRealPath(),
          originalName: $file->getClientOriginalName(),
          mimeType:     null, // PHP に自動判定させる
          test:         true, // is_uploaded_file() チェックをスキップ
        );
      }

      $systemCategory = Category::where('name', Category::SYSTEM_NAME)->firstOrFail();

      /** @var \App\Services\Image\ImageUploadService $service */
      $service = app(ImageUploadService::class);

      try {
        $result = $service->upload($file, $systemCategory->id);
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

      $image   = Image::create(array_merge(
        $result->toArray(),
        ['category_id' => $systemCategory->id],
      ));
      $imageId = $image->id;
    }

    return Playlist::create([
      'category_id'    => $data['category_id'],
      'name'           => $data['name'],
      'yt_playlist_id' => $data['yt_playlist_id'],
      'image_id'       => $imageId,
      'is_active'      => $data['is_active'] ?? true,
      'description'    => $data['description'] ?? null,
    ]);
  }

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
   * 作成完了通知
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getCreatedNotification(): ?Notification
  {
    return Notification::make()
      ->title('サブカテゴリを作成しました')
      ->success()
      ->persistent();
  }
}
