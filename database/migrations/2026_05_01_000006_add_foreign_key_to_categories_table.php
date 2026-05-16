<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    // categories テーブルの image_id に images テーブルの id を参照する外部キー制約を追加
    Schema::table('categories', function (Blueprint $table) {
      $table->foreign('image_id')
        ->references('id')
        ->on('images')
        ->restrictOnDelete(); // 削除を制限する（該当画像は削除できない）
    });
  }

  public function down(): void
  {
    Schema::table('categories', function (Blueprint $table) {
      $table->dropForeign(['image_id']);
    });
  }
};
