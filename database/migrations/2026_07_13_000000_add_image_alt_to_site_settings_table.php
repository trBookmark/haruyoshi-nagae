<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * up
   * site_settings に画像の代替テキスト用カラムを追加
   * 旧: imgsettings.memo（alt 用途）
   *
   * @return void
   */
  public function up(): void
  {
    Schema::table('site_settings', function (Blueprint $table) {
      $table->string('image_alt')->nullable()->after('image_id')->comment('画像の代替テキスト');
    });
  }

  /**
   * down
   * image_alt カラムを削除
   *
   * @return void
   */
  public function down(): void
  {
    Schema::table('site_settings', function (Blueprint $table) {
      $table->dropColumn('image_alt');
    });
  }
};
