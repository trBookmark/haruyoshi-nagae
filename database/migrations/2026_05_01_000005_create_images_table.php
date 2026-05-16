<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ThumbnailAlign;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('images', function (Blueprint $table) {
      $table->comment('画像テーブル');
      $table->id();
      $table->foreignId('category_id')
        ->constrained('categories')
        ->restrictOnDelete(); // 削除を制限する（該当画像は削除できない）
      $table->boolean('is_active')->default(true)->comment('使用可否'); // 旧名: use
      $table->unsignedSmallInteger('sort_order')->default(0)->comment('表示順'); // 旧名: order
      $table->unsignedSmallInteger('year')->nullable()->comment('制作年'); // Model の $attributes でデフォルト値を設定する
      $table->string('storage_file_name', 190)->unique()->comment('保存ファイル名'); // 旧名: original
      $table->string('client_file_name', 190)
        ->nullable()
        ->comment('アップロードファイル名'); // ユーザーの利便性のために保存
      $table->string('checksum', 64)->nullable()->index()->comment('チェックサム（SHA-256）');
      $table->unsignedSmallInteger('width')->nullable()->comment('幅');
      $table->unsignedSmallInteger('height')->nullable()->comment('高さ');
      $table->unsignedInteger('file_size')->nullable()->comment('ファイルサイズ');
      $table->string('extension', 10)->nullable()->comment('ファイル拡張子');
      $table->string('mime_type', 100)->nullable()->comment('MIMEタイプ');
      $table->string('thumbnail_align', 1)->default(ThumbnailAlign::CENTER->value)->comment('サムネイル配置');
      $table->string('title')->nullable()->comment('タイトル');
      $table->string('caption')->nullable()->comment('表示コメント');
      $table->text('memo')->nullable()->comment('自分用メモ');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('images');
    Schema::enableForeignKeyConstraints();
  }
};
