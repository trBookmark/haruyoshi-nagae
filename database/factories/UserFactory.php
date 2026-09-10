<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
  protected $model = User::class;

  /**
   * ファクトリ全体で使い回すハッシュ済みパスワード
   * User 1件ごとにハッシュ化すると bcrypt のコストがそのままテストの実行時間になるため、初回だけ生成する
   *
   * @var string|null
   */
  protected static ?string $password;

  /**
   * definition
   * User Model のデフォルトのテストデータを定義
   * role は指定せず、マイグレーションの既定値（editor）に委ねる
   * 平文のパスワードは 'password'
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name'              => fake()->name(),
      'email'             => fake()->unique()->safeEmail(),
      'email_verified_at' => now(),
      'password'          => static::$password ??= Hash::make('password'),
      'remember_token'    => Str::random(10),
    ];
  }

  /**
   * admin
   * 管理者（UserRole::ADMIN）としての状態
   *
   * @return static
   */
  public function admin(): static
  {
    return $this->state(fn (): array => [
      'role' => UserRole::ADMIN->value,
    ]);
  }

  /**
   * editor
   * 編集者（UserRole::EDITOR）としての状態
   * マイグレーションの既定値と同じだが、テスト側の意図を明示するために用意する
   *
   * @return static
   */
  public function editor(): static
  {
    return $this->state(fn (): array => [
      'role' => UserRole::EDITOR->value,
    ]);
  }

  /**
   * unverified
   * メールアドレス未確認の状態
   *
   * @return static
   */
  public function unverified(): static
  {
    return $this->state(fn (): array => [
      'email_verified_at' => null,
    ]);
  }
}
