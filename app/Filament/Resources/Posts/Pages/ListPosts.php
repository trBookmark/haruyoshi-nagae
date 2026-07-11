<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
  protected static string $resource = PostResource::class;

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
}
