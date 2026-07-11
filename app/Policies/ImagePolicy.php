<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Image;
use App\Models\User;

class ImagePolicy
{
  /**
   * viewAny
   * 画像一覧の表示権限
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
   * 画像詳細の表示権限
   * 管理者・編集者：許可
   * __system カテゴリの画像：管理者のみ許可（編集者の直接URLアクセスを禁止）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Image $image
   * @return bool
   */
  public function view(User $user, Image $image): bool
  {
    if (Category::isSystemName($image->category->name)) {
      return $user->isAdmin();
    }

    return true;
  }

  /**
   * create
   * 画像新規作成の権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function create(User $user): bool
  {
    return true;
  }

  /**
   * update
   * 画像編集の権限
   * 管理者・編集者：許可
   * __system カテゴリの画像：管理者のみ許可（編集者の直接URLアクセスを禁止）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Image $image
   * @return bool
   */
  public function update(User $user, Image $image): bool
  {
    if (Category::isSystemName($image->category->name)) {
      return $user->isAdmin();
    }

    return true;
  }

  /**
   * delete
   * 画像削除の権限
   * 使用中（isInUse）の画像：全員不可（データ不整合防止）
   * __system カテゴリの画像：管理者のみ許可（isInUse とは独立した認可層として多重防御）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Image $image
   * @return bool
   */
  public function delete(User $user, Image $image): bool
  {
    if ($image->isInUse()) {
      return false;
    }

    if (Category::isSystemName($image->category->name)) {
      return $user->isAdmin();
    }

    return true;
  }
}
