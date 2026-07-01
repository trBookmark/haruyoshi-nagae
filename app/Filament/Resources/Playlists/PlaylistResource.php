<?php

namespace App\Filament\Resources\Playlists;

use App\Filament\Resources\Playlists\Pages\CreatePlaylist;
use App\Filament\Resources\Playlists\Pages\EditPlaylist;
use App\Filament\Resources\Playlists\Pages\ListPlaylists;
use App\Filament\Resources\Playlists\Schemas\PlaylistForm;
use App\Filament\Resources\Playlists\Tables\PlaylistsTable;
use App\Models\Playlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PlaylistResource extends Resource
{
  protected static ?string $model = Playlist::class;

  protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQueueList;
  protected static ?string $navigationLabel                   = 'サブカテゴリ';
  protected static ?string $modelLabel                        = 'サブカテゴリ';
  protected static string | UnitEnum | null $navigationGroup  = 'カテゴリ管理';
  protected static ?int    $navigationSort                    = 20;

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return PlaylistForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return PlaylistsTable::configure($table);
  }

  /**
   * getPages
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index'  => ListPlaylists::route('/'),
      'create' => CreatePlaylist::route('/create'),
      'edit'   => EditPlaylist::route('/{record}/edit'),
    ];
  }
}
