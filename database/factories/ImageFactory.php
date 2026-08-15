<?php

namespace Database\Factories;

use App\Enums\ThumbnailAlign;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
  protected $model = Image::class;

  /**
   * definition
   * Image Model のデフォルトのテストデータを定義
   * Storage 上の実ファイルは作成しない（URL 出力のみのテスト用）
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'category_id'       => Category::factory(),
      'is_active'         => true,
      'sort_order'        => 0,
      'year'              => fake()->numberBetween(2000, 2026),
      'storage_file_name' => fake()->unique()->lexify('????????????????') . '.jpg',
      'client_file_name'  => 'test.jpg',
      'checksum'          => fake()->sha256(),
      'width'             => 800,
      'height'            => 600,
      'file_size'         => 123456,
      'extension'         => 'jpg',
      'mime_type'         => 'image/jpeg',
      'thumbnail_align'   => ThumbnailAlign::CENTER->value,
      'title'             => fake()->words(2, true),
      'caption'           => null,
      'memo'              => null,
    ];
  }

  /**
   * animatedGif
   * GIF アニメとしての状態（isAnimatedGif() が真になる）
   *
   * @return static
   */
  public function animatedGif(): static
  {
    return $this->state(fn (): array => [
      'storage_file_name' => fake()->unique()->lexify('????????????????') . '.gif',
      'client_file_name'  => 'test.gif',
      'extension'         => 'gif',
      'mime_type'         => 'image/gif',
    ]);
  }
}
