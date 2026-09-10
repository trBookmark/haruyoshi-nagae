<?php

use App\Enums\PageType;
use App\Models\SiteSetting;
use Database\Seeders\SiteSettingSeeder;

/**
 * トップページが 200 を返し、TOP_MSG の文面が表示されることを確認
 * Seeder 初期状態は image_id が null のため、画像未設定でも表示できることの確認を兼ねる
 */
test('トップページが表示され TOP_MSG の文面が出力される', function () {
  $this->seed(SiteSettingSeeder::class);

  $description = SiteSetting::where('page_type', PageType::TOP_MSG)->value('description');

  $response = $this->get(route('home'));

  $response->assertOk();
  $response->assertSeeText(str($description)->before("\n")->toString());
});

/**
 * TOP_MSG のサイト設定が存在しない場合は 404 を返すことを確認
 * （HomeController の firstOrFail() の挙動）
 */
test('TOP_MSG が存在しない場合は 404 を返す', function () {
  $this->get(route('home'))->assertNotFound();
});
