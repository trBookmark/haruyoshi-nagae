<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('tags', function (Blueprint $table) {
      $table->comment('タグテーブル');
      $table->id();
      $table->string('name', 190)->unique()->comment('タグ'); // 旧名: tag
      $table->text('description')->nullable()->comment('概要'); // og:description などに使用（旧名: memo）
      $table->boolean('is_active')->default(true)->comment('使用可否'); // 旧名: use
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('tags');
    Schema::enableForeignKeyConstraints();
  }
};
