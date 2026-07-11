<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteSettings extends ListRecords
{
  protected static string $resource = SiteSettingResource::class;

  /**
   * getHeaderActions
   * 新規作成ボタンなし（サイト設定は Seeder 固定・追加禁止）
   *
   * @return array
   */
  protected function getHeaderActions(): array
  {
    return [];
  }
}
