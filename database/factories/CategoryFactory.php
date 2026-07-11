<?php

namespace Database\Factories;

use App\Enums\ModelType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
  protected $model = Category::class;

  /**
   * definition
   * Category モデルのデフォルトのテストデータを定義
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name'        => fake()->unique()->slug(2),
      'name_ja'     => fake()->words(2, true),
      'image_id'    => null,
      'model_type'  => ModelType::IMAGE->value,
      'sort_order'  => 0,
      'is_active'   => true,
      'description' => null,
    ];
  }

  /**
   * system
   * __system カテゴリ（内部専用カテゴリ）としての状態を定義
   * SystemCategoryGuard 等のテストで使用
   *
   * @return static
   */
  public function system(): static
  {
    return $this->state(fn (): array => [
      'name'      => Category::SYSTEM_NAME,
      'name_ja'   => 'システム',
      'is_active' => false,
    ]);
  }
}
