<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PageType;

return new class extends Migration
{
  public function up(): void
  {
    // 旧：settings ＋ imgsettings
    // データ削除不可（サイト全体の設定を管理する重要なテーブルのため）
    // seeder で初期データを投入
    Schema::create('site_settings', function (Blueprint $table) {
      $table->comment('サイト設定テーブル');
      $table->id();
      $table->string('page_type', 20)->default(PageType::TOP_MSG->value)->unique()->comment('使用ページタイプ'); // 旧名: type
      $table->string('label')->comment('タイプ説明'); // 旧名: memo
      $table->string('description')->nullable()->comment('使用ページ説明文'); // 旧名: data
      $table->boolean('image_required')->default(false)->comment('画像必須フラグ');
      $table->foreignId('image_id')
        ->nullable()
        ->constrained('images') // 規約を使用して参照されるテーブルとカラムの名前を決定
        ->restrictOnDelete(); // 削除を制限する（該当画像は削除できない）
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('site_settings');
    Schema::enableForeignKeyConstraints();
  }
};
