<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * データベースの初期データを投入する
   *
   * @return void
   */
  public function run(): void
  {
    $this->call([
      CategorySeeder::class,
      SiteSettingSeeder::class,
    ]);
  }
}
