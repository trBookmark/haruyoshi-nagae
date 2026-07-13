<?php

namespace App\Http\Controllers;

use App\Enums\PageType;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
  /**
   * __invoke
   * トップページを表示
   * TOP_MSG のサイト設定（文面・画像・代替テキスト）をビューへ渡す
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function __invoke(): View
  {
    $setting = SiteSetting::with('image')
      ->where('page_type', PageType::TOP_MSG)
      ->firstOrFail();

    return view('home', [
      'setting' => $setting,
    ]);
  }
}
