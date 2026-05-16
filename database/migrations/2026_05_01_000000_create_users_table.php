<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserRole;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->comment('ユーザーテーブル');
      $table->id();
      $table->string('name', 190)->unique()->comment('ユーザー名');
      $table->string('email', 190)->unique()->comment('メールアドレス');
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password', 255)->comment('パスワードハッシュ');
      $table->string('role', 20)->default(UserRole::EDITOR->value)->comment('権限');
      $table->string('avatar')->nullable()->comment('管理画面用アバター画像パス'); // 管理画面専用アバター画像（画像処理パイプライン不要のため文字列で管理）
      $table->rememberToken();
      $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email', 190)->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->foreignId('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('sessions');
  }
};
