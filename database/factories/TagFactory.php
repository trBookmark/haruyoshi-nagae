<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
  protected $model = Tag::class;

  /**
   * definition
   * Tag Model のデフォルトのテストデータを定義
   * name は tags テーブルで unique 制約があるため、重複しない値を生成する
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name'        => fake()->unique()->slug(2),
      'description' => null,
      'is_active'   => true,
    ];
  }

  /**
   * inactive
   * 使用不可（is_active=false）の状態
   *
   * @return static
   */
  public function inactive(): static
  {
    return $this->state(fn (): array => [
      'is_active' => false,
    ]);
  }
}
