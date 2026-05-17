<?php

namespace Database\Seeders;

use App\Enums\ModelType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
  /**
   * カテゴリの初期データを投入する
   * image_id は画像未登録のため null
   * animation カテゴリは playlists の親コンテナのため model_type は null
   *
   * @return void
   */
  public function run(): void
  {
    $categories = [
      [
        'name'       => 'artwork',
        'name_ja'    => 'イラスト',
        'model_type' => ModelType::IMAGE->value,
        'sort_order' => 1,
      ],
      [
        'name'       => 'drawing',
        'name_ja'    => '絵画',
        'model_type' => ModelType::IMAGE->value,
        'sort_order' => 2,
      ],
      [
        'name'       => 'production',
        'name_ja'    => '制作',
        'model_type' => ModelType::IMAGE->value,
        'sort_order' => 3,
      ],
      [
        'name'       => 'animation',
        'name_ja'    => 'アニメーション',
        'model_type' => null, // playlists の親コンテナのため null
        'sort_order' => 4,
      ],
      [
        'name'       => 'music',
        'name_ja'    => '音楽',
        'model_type' => null, // playlists の親コンテナのため null
        'sort_order' => 5,
      ],
      [
        'name'       => 'book',
        'name_ja'    => '読み物',
        'model_type' => ModelType::POST->value,
        'sort_order' => 6,
      ],
    ];

    foreach ($categories as $category) {
      Category::create($category);
    }
  }
}
