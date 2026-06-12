<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use App\Models\Image;
use App\Services\Image\ImageUploadService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EditImage extends EditRecord
{
  protected static string $resource = ImageResource::class;

  /**
   * getHeaderActions
   * 使用中の画像は削除ボタンを非表示にする
   * 削除成功後は一覧画面へリダイレクト
   * isInUse() をすり抜けた場合は QueryException をキャッチして通知
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()
        ->hidden(fn (Image $record) => $record->isInUse())
        ->using(function (Image $record): void {
          try {
            $record->delete();
          } catch (QueryException $e) {
            Notification::make()
              ->title('削除できません')
              ->body($record->inUseDescription() ?? 'この画像は他のページで使用されているため削除できません。')
              ->danger()
              ->send();

            // 削除失敗時はリダイレクトしない
            return;
          }

          // ->using() を使うと ->successRedirectUrl() が発火しないため自前でリダイレクト
          redirect(ImageResource::getUrl('index'));
        }),
    ];
  }

  /**
   * handleRecordUpdate
   * 画像ファイルが差し替えられた場合のみ ImageUploadService 経由で再処理
   * ファイルなし（メタ情報のみの編集）の場合はデフォルト動作に委譲
   *
   * ValidationException（重複・不正ファイル等）はキャッチして通知を表示し、
   * halt() でフォームの遷移を止める
   *
   * @param  \Illuminate\Database\Eloquent\Model  $record
   * @param  array<string, mixed>                 $data
   * @return \Illuminate\Database\Eloquent\Model
   */
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    /** @var \App\Models\Image $record */

    // image キーに UploadedFile が入っている場合のみ再アップロード処理
    if (! empty($data['image'])) {
      $file = $data['image'];

      /** @var \App\Services\Image\ImageUploadService $service */
      $service = app(ImageUploadService::class);

      $result = null;
      try {
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

      // 旧ファイルをストレージから削除
      // deleting イベントは発火しないため手動
      $this->deleteStorageFiles($record);

      $record->update(array_merge(
        $result->toArray(),
        $this->metaFields($data),
      ));

      return $record;
    }

    // ファイルなし：メタ情報のみ更新
    $record->update($this->metaFields($data));

    return $record;
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
      'category_id'     => $data['category_id'],
      'is_active'       => $data['is_active'] ?? true,
      'sort_order'      => $data['sort_order'] ?? 0,
      'year'            => $data['year'] ?? null,
      'thumbnail_align' => $data['thumbnail_align'],
      'title'           => $data['title'] ?? null,
      'caption'         => $data['caption'] ?? null,
      'memo'            => $data['memo'] ?? null,
    ];
  }

  /**
   * deleteStorageFiles
   * 旧画像ファイルをストレージから削除
   * handleRecordUpdate での差し替え時に使用
   * Image::delete() を呼ばないため deleting イベントが発火しない
   *
   * @param  \App\Models\Image $record
   * @return void
   */
  private function deleteStorageFiles(Image $record): void
  {
    \Illuminate\Support\Facades\Storage::disk('local')
      ->delete('images/original/' . $record->storage_file_name);

    foreach (array_keys(config('image.resize', [])) as $size) {
      \Illuminate\Support\Facades\Storage::disk('public')
        ->delete('images/' . $size . '/' . $record->storage_file_name);
    }
  }
}
