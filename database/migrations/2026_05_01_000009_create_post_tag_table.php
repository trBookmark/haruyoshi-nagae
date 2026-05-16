<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('post_tag', function (Blueprint $table) {
      $table->comment('ブログとタグの中間テーブル');
      $table->unsignedBigInteger('post_id');
      $table->unsignedBigInteger('tag_id');

      $table->primary(['post_id', 'tag_id']);

      $table->foreign('post_id')
        ->references('id')
        ->on('posts')
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
    Schema::dropIfExists('post_tag');
    Schema::enableForeignKeyConstraints();
  }
};
