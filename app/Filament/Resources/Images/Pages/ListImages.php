<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImages extends ListRecords
{
  protected static string $resource = ImageResource::class;

  /**
   * getHeaderActions
   * 新規作成ボタン：管理者・編集者ともに表示
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make(),
    ];
  }

  /**
   * getTableFilterState
   * URL パラメータ category_id をテーブルフィルターの初期値として反映
   * /admin/images?category_id=3 のようなリンクからの遷移に対応
   * TODO: Tag フィルターを同方式で追加
   *
   * @return array<string, mixed>
   */
  public function getTableFilterState(string $name): ?array
  {
    if ($name === 'category_id') {
      $categoryId = request()->query('category_id');

      if (filled($categoryId)) {
        return ['value' => (string) $categoryId];
      }
    }

    return parent::getTableFilterState($name);
  }
}
