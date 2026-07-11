<?php

namespace App\Filament\Resources\Images;

use App\Filament\Resources\Images\Pages\CreateImage;
use App\Filament\Resources\Images\Pages\EditImage;
use App\Filament\Resources\Images\Pages\ListImages;
use App\Filament\Resources\Images\Schemas\ImageForm;
use App\Filament\Resources\Images\Tables\ImagesTable;
use App\Models\Image;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ImageResource extends Resource
{
  protected static ?string $model = Image::class;

  protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPhoto;
  protected static ?string $navigationLabel                   = '画像';
  protected static ?string $modelLabel                        = '画像';
  protected static string | UnitEnum | null $navigationGroup  = '作品管理';
  protected static ?int    $navigationSort                    = 1;

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return ImageForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return ImagesTable::configure($table);
  }

  /**
   * getPages
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index'  => ListImages::route('/'),
      'create' => CreateImage::route('/create'),
      'edit'   => EditImage::route('/{record}/edit'),
    ];
  }
}
