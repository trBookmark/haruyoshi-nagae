<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PostStatus;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('posts', function (Blueprint $table) {
      $table->comment('ブログテーブル');
      $table->id();
      $table->unsignedSmallInteger('sort_order')->default(0)->comment('表示順'); // 旧名: order アーティストが任意の順番で並べ替えるための列
      $table->string('status', 10)->default(PostStatus::DRAFT->value)->comment('投稿状態'); // 旧名: post_status:boolean から string に変更して列挙型で管理
      $table->foreignId('image_id') // 旧名: post_img_id images テーブルで管理（nullable: アイキャッチなし可）
        ->nullable()
        ->constrained('images')
        ->restrictOnDelete() // 削除を制限する（該当画像は削除できない）
        ->comment('サムネイル画像');
      $table->string('title')->comment('タイトル'); // 旧名: post_title
      $table->longText('content')->comment('本文'); // 旧名: post_content
      $table->text('excerpt')->nullable()->comment('投稿抜粋'); // 旧名: post_excerpt
      $table->text('memo')->nullable()->comment('自分用メモ');
      $table->timestamp('published_at')->nullable()->comment('公開日時');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('posts');
    Schema::enableForeignKeyConstraints();
  }
};
