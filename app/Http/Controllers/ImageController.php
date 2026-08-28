<?php

namespace App\Http\Controllers;

use App\Enums\ModelType;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Contracts\View\View;

class ImageController extends Controller
{
  /**
   * show
   * 画像個別ページを表示
   * 対象: __system 以外・is_active・model_type=IMAGE の Category に属する is_active な Image
   * Category と Image の対応はルートの scopeBindings() で保証（不一致は 404）
   *
   * @param  \App\Models\Category $category
   * @param  \App\Models\Image    $image
   * @return \Illuminate\Contracts\View\View
   */
  public function show(Category $category, Image $image): View
  {
    abort_if(Category::isSystemName($category->name), 404);
    abort_unless($category->is_active, 404);
    abort_unless($category->model_type === ModelType::IMAGE, 404);
    abort_unless($image->is_active, 404);

    $image->load('tags');

    // 同カテゴリの公開画像 ID を表示順に取得し、配列上で前後と現在位置を解決する
    // 一覧がページネーションなし＝全件を1画面に出す規模のため、この1クエリで賄う
    // $image は scopeBindings で同カテゴリ所属、かつ上で is_active を確認済みであり、
    // search() が false を返すことはない
    $ids   = $category->images()->active()->orderedForGallery()->pluck('id');
    $index = $ids->search($image->id);

    return view('images.show', [
      'category'   => $category,
      'image'      => $image,
      'previousId' => $index > 0 ? $ids->get($index - 1) : null,
      'nextId'     => $ids->get($index + 1),
      'position'   => $index + 1,
      'total'      => $ids->count(),
    ]);
  }
}
