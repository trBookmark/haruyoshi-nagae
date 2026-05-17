<?php

namespace Database\Seeders;

use App\Enums\PageType;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
  /**
   * サイト設定の初期データを投入する
   * 全6件固定・削除不可
   * image_id は画像未登録のため null（管理画面から設定）
   *
   * @return void
   */
  public function run(): void
  {
    $settings = [
      [
        'page_type'      => PageType::TOP_MSG->value,
        'label'          => 'トップページ文面',
        'description'    => "長江春芳の個人サイトです。\n作品のアーカイブを掲載しています。\nぜひ見ていってください。",
        'image_required' => true, // 旧 imgsettings: top_img
      ],
      [
        'page_type'      => PageType::ABOUT_MSG->value,
        'label'          => 'aboutページ文面',
        'description'    => "写真・絵画・文章など\nいろんな創作活動をします。\n主にtwitterで発信しています。",
        'image_required' => true, // 旧 imgsettings: about_img
      ],
      [
        'page_type'      => PageType::CONTACT_MSG->value,
        'label'          => 'お問い合わせページ文面',
        'description'    => "作品についてのお問い合わせ\n感想やご意見など、気軽に\nお寄せください。",
        'image_required' => false,
      ],
      [
        'page_type'      => PageType::CONFIRM_MSG->value,
        'label'          => 'お問い合わせ確認画面文面',
        'description'    => "こちらの内容で\n送信してよいか\nご確認ください。",
        'image_required' => false,
      ],
      [
        'page_type'      => PageType::SENT_MSG->value,
        'label'          => 'お問い合わせ送信完了文面',
        'description'    => 'お問い合わせありがとうございました。',
        'image_required' => false,
      ],
      [
        'page_type'      => PageType::ABOUT_COPYRIGHT->value,
        'label'          => '著作権表示文面',
        'description'    => "本サイト掲載データの著作権は\n長江春芳に帰属します。\n複製・改変・転載等はご遠慮ください。",
        'image_required' => false,
      ],
    ];

    foreach ($settings as $setting) {
      SiteSetting::create($setting);
    }
  }
}
