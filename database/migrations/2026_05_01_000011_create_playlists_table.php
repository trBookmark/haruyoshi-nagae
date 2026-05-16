<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  // サブカテゴリ（YouTubeプレイリスト）テーブルの作成
  public function up(): void
  {
    Schema::create('playlists', function (Blueprint $table) {
      $table->comment('サブカテゴリ（YouTubeプレイリスト）テーブル');
      $table->id();
      $table->foreignId('category_id')
        ->constrained('categories')
        ->restrictOnDelete() // 削除を制限する（該当カテゴリは削除できない）
        ->comment('親カテゴリID');
      $table->string('name')->comment('サブカテゴリ名');
      $table->string('yt_playlist_id', 64)->index()->comment('YouTubeプレイリストID');
      $table->foreignId('image_id') // images テーブルで管理（nullable）
        ->nullable()
        ->constrained('images') // 規約を使用して参照されるテーブルとカラムの名前を決定
        ->restrictOnDelete() // 削除を制限する（該当画像は削除できない）
        ->comment('サムネイル画像');
      $table->unsignedSmallInteger('sort_order')->default(0)->comment('表示順');
      $table->boolean('is_active')->default(true)->comment('使用可否'); // 旧名: use
      $table->string('description')->nullable()->comment('概要'); // og:description などに使用（旧名: memo）
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('playlists');
    Schema::enableForeignKeyConstraints();
  }
};
