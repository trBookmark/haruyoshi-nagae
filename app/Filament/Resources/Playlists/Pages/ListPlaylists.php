<?php

namespace App\Filament\Resources\Playlists\Pages;

use App\Filament\Resources\Playlists\PlaylistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlaylists extends ListRecords
{
  protected static string $resource = PlaylistResource::class;

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
