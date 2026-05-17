<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ModelType;

return new class extends Migration
{
  public function up(): void
  {
    // seeder で初期データを投入
    Schema::create('categories', function (Blueprint $table) {
      $table->comment('カテゴリーテーブル');
      $table->id();
      $table->string('name', 190)->unique()->comment('カテゴリ名');
      $table->string('name_ja')->comment('カテゴリ表示名'); // 旧名: j_name
      $table->unsignedBigInteger('image_id')
        ->nullable()
        ->comment('カテゴリ画像'); // images テーブル作成後に FK を追加（循環依存回避）
      $table->string('model_type', 10)->nullable()->comment('使用モデル'); // 旧名: model
      $table->unsignedSmallInteger('sort_order')->default(0)->comment('表示順');
      $table->boolean('is_active')->default(true)->comment('使用可否'); // 旧名: use
      $table->string('description')->nullable()->comment('概要'); // og:description などに使用（旧名: memo）
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('categories');
    Schema::enableForeignKeyConstraints();
  }
};
