<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
  protected static string $resource = CategoryResource::class;

  /**
   * getHeaderActions
   * 新規作成ボタン：管理者のみ表示
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()
        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
    ];
  }
}
