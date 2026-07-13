<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
  /**
   * index
   * カテゴリ一覧（Gallery トップ）を表示する
   * 表示対象：__system 以外かつ有効なカテゴリ（表示順）
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function index(): View
  {
    $categories = Category::with('coverImage')
      ->excludingSystem()
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get();

    return view('gallery.index', [
      'categories' => $categories,
    ]);
  }
}
