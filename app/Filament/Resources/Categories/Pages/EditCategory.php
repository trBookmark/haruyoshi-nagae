<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
  protected static string $resource = CategoryResource::class;

  /**
   * getHeaderActions
   * 削除ボタンなし（カテゴリは削除禁止）
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [];
  }
}
