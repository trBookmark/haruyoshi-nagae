<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTags extends ListRecords
{
  protected static string $resource = TagResource::class;

  /**
   * getHeaderActions
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
