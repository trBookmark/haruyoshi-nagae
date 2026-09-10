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

  /**
   * small
   * 長辺が thumb の基準値（config: image.resize.thumb）より小さい状態
   * リサイズは拡大なしのため、全サイズが原寸のまま保存される
   * variantWidth() / srcset() が原寸を返し、記述子が1件にまとまることの検証用
   *
   * @return static
   */
  public function small(): static
  {
    return $this->state(fn (): array => [
      'width'  => 300,
      'height' => 200,
    ]);
  }

  /**
   * oversized
   * 長辺が large の基準値（config: image.resize.large）より大きい状態
   * 全サイズがリサイズされる。variantWidth() の縮小計算の検証用
   *
   * @return static
   */
  public function oversized(): static
  {
    return $this->state(fn (): array => [
      'width'  => 3000,
      'height' => 2000,
    ]);
  }
}
