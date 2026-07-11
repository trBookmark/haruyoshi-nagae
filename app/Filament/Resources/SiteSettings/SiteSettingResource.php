<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Filament\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteSettingResource extends Resource
{
  protected static ?string $model = SiteSetting::class;

  protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;
  protected static ?string $navigationLabel                   = 'サイト設定';
  protected static ?string $modelLabel                        = 'サイト設定';
  protected static string | UnitEnum | null $navigationGroup  = 'システム管理';
  protected static ?int    $navigationSort                    = 50;

  // 削除確認モーダルやパンくずにタイプ説明を表示
  protected static ?string $recordTitleAttribute = 'label';

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return SiteSettingForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return SiteSettingsTable::configure($table);
  }

  /**
   * getPages
   * Seeder 固定を編集する
   * Create なし
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index' => ListSiteSettings::route('/'),
      'edit'  => EditSiteSetting::route('/{record}/edit'),
    ];
  }
}
