<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\Schemas\TagForm;
use App\Filament\Resources\Tags\Tables\TagsTable;
use App\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TagResource extends Resource
{
  protected static ?string $model = Tag::class;

  protected static string | BackedEnum | null $navigationIcon  = Heroicon::OutlinedTag;
  protected static ?string $navigationLabel                    = 'タグ';
  protected static ?string $modelLabel                         = 'タグ';
  protected static string | UnitEnum | null $navigationGroup   = '作品管理';
  protected static ?int    $navigationSort                     = 20;

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return TagForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return TagsTable::configure($table);
  }

  /**
   * getPages
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index'  => ListTags::route('/'),
      'create' => CreateTag::route('/create'),
      'edit'   => EditTag::route('/{record}/edit'),
    ];
  }
}
