<?php

namespace App\Filament\Resources\Playlists\Pages;

use App\Filament\Resources\Playlists\PlaylistResource;
use App\Models\Category;
use App\Models\Image;
use App\Models\Playlist;
use App\Services\Image\ImageUploadService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class EditPlaylist extends EditRecord
{
  protected static string $resource = PlaylistResource::class;

  /**
   * getHeaderActions
   * 削除ボタンなし（プレイリストは削除禁止・is_active で管理）
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [];
  }

  /**
   * handleRecordUpdate
   * カバー画像がアップロードされた場合のみ ImageUploadService 経由で処理
   * アップロード先は __system カテゴリに固定
   * 差し替え時：旧 Image レコードを削除（booted の deleting でファイルも削除）
   *
   * @param  \Illuminate\Database\Eloquent\Model $record
   * @param  array<string, mixed>                $data
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    /** @var \App\Models\Playlist $record */

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

      // 旧カバー画像レコードを退避（FK 付け替え後に削除）
      $previousImage = $record->thumbnail;

      // 新 Image レコードを作成
      $newImage = Image::create(array_merge(
        $result->toArray(),
        ['category_id' => $systemCategory->id],
      ));

      // image_id を新画像に付け替え（旧画像の FK 制約を解除してから削除）
      $record->update(array_merge(
        $this->metaFields($data),
        ['image_id' => $newImage->id],
      ));

      // 旧カバー画像を削除（booted の deleting でストレージファイルも削除）
      $previousImage?->delete();

      return $record;
    }

    // カバー画像なし：メタ情報のみ更新
    $record->update($this->metaFields($data));

    return $record;
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
   * 保存完了通知
   *
   * @return \Filament\Notifications\Notification|null
   */
  protected function getSavedNotification(): ?Notification
  {
    return Notification::make()
      ->title('サブカテゴリを保存しました')
      ->success()
      ->persistent();
  }

  /**
   * metaFields
   * フォームデータからメタ情報カラムのみを抽出して返す
   *
   * @param  array<string, mixed> $data
   * @return array<string, mixed>
   */
  private function metaFields(array $data): array
  {
    return [
      'category_id'    => $data['category_id'],
      'name'           => $data['name'],
      'yt_playlist_id' => $data['yt_playlist_id'],
      'is_active'      => $data['is_active'] ?? true,
      'description'    => $data['description'] ?? null,
    ];
  }
}
