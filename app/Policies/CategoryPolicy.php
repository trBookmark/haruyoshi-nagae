<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
  /**
   * viewAny
   * カテゴリ一覧の表示権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function viewAny(User $user): bool
  {
    return true;
  }

  /**
   * view
   * カテゴリ詳細の表示権限
   * 管理者・編集者：許可
   * __system カテゴリ：管理者のみ許可（編集者の直接URLアクセスを禁止）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Category $category
   * @return bool
   */
  public function view(User $user, Category $category): bool
  {
    if (Category::isSystemName($category->name)) {
      return $user->isAdmin();
    }

    return true;
  }

  /**
   * create
   * カテゴリ新規作成の権限
   * 管理者：許可
   * 編集者：不可
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function create(User $user): bool
  {
    return $user->isAdmin();
  }

  /**
   * update
   * カテゴリ編集の権限
   * 管理者：全て許可
   * 編集者：一部制限あり（name / model_type の編集制限はフォーム側で制御）
   * __system カテゴリ：管理者のみ許可（編集者の直接URLアクセスを禁止）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Category $category
   * @return bool
   */
  public function update(User $user, Category $category): bool
  {
    if (Category::isSystemName($category->name)) {
      return $user->isAdmin();
    }

    return true;
  }

  /**
   * delete
   * カテゴリ削除の権限
   * 全員不可（誤操作やデータ不整合を防ぐため削除禁止）
   * 使用/不使用の切り替えは is_active で管理
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Category $category
   * @return bool
   */
  public function delete(User $user, Category $category): bool
  {
    return false;
  }
}
