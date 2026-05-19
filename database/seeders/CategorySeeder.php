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
   * animation / music カテゴリは playlists の親コンテナのため model_type は null
   * __system カテゴリはカバー画像・サイト設定画像を格納する内部専用カテゴリ
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
      // ──────────── 内部専用Category ────────────
      // Categoryカバー画像・サイト設定画像など、フロントエンドに公開しない画像の格納先として使用
      // is_active=false でフロントから除外する
      // SYSTEM_NAME 定数を規約として使用
      [
        'name'       => Category::SYSTEM_NAME,
        'name_ja'    => 'システム',
        'model_type' => ModelType::IMAGE->value,
        'sort_order' => 0,
        'is_active'  => false, // デフォルト(true)を明示的に上書き
      ],
    ];

    foreach ($categories as $category) {
      Category::create($category);
    }
  }
}
