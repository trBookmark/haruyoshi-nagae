<?php

namespace App\Http\Controllers;

use App\Enums\ModelType;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
  /**
   * index
   * Category一覧（Gallery トップ）を表示
   * 表示対象: __system 以外・is_active（sort_order順）
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

  /**
   * show
   * Category別 画像一覧を表示
   * 対象: __system 以外・is_active・model_type=IMAGE （それ以外は 404）
   * コンテンツが 0 件の場合: 404
   * TODO: プレイリスト専用Category（model_type=null、サブカテゴリ）の分岐は実装時に追加
   *
   * @param  \App\Models\Category $category
   * @return \Illuminate\Contracts\View\View
   */
  public function show(Category $category): View
  {
    abort_if(Category::isSystemName($category->name), 404);
    abort_unless($category->is_active, 404);
    abort_unless($category->model_type === ModelType::IMAGE, 404);

    $images = $category->images()
      ->with('tags')
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get();

    abort_if($images->isEmpty(), 404);

    return view('gallery.show', [
      'category' => $category,
      'images'   => $images,
    ]);
  }
}
