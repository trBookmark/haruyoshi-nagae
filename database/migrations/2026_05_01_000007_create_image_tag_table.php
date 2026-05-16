<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('image_tag', function (Blueprint $table) {
      $table->comment('画像とタグの中間テーブル');
      $table->unsignedBigInteger('image_id');
      $table->unsignedBigInteger('tag_id');

      $table->primary(['image_id', 'tag_id']);

      $table->foreign('image_id')
        ->references('id')
        ->on('images')
        ->cascadeOnDelete();

      $table->foreign('tag_id')
        ->references('id')
        ->on('tags')
        ->restrictOnDelete();

      $table->index('tag_id'); // whereHas(tags) 用パフォーマンス対策

    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('image_tag');
    Schema::enableForeignKeyConstraints();
  }
};
