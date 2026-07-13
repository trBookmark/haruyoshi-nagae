<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * up
   * playlists の name をカテゴリと同じ規約（name = URL 用英語名 / name_ja = 表示名）に分割
   * 既存の name（日本語表示名）は name_ja へリネームし、URL 用の name を新設
   * 既存レコードの name は null のまま（管理画面でrequired）
   *
   * @return void
   */
  public function up(): void
  {
    Schema::table('playlists', function (Blueprint $table) {
      $table->renameColumn('name', 'name_ja');
    });

    Schema::table('playlists', function (Blueprint $table) {
      $table->string('name', 190)->nullable()->unique()->after('category_id')->comment('サブカテゴリ名（URL用英語名）');
    });
  }

  /**
   * down
   * name を削除し、name_ja を name へ戻す
   *
   * @return void
   */
  public function down(): void
  {
    Schema::table('playlists', function (Blueprint $table) {
      $table->dropColumn('name'); // unique インデックスもカラムと同時に削除
    });

    Schema::table('playlists', function (Blueprint $table) {
      $table->renameColumn('name_ja', 'name');
    });
  }
};
