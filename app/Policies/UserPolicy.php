<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
  /**
   * viewAny
   * ユーザー一覧の表示権限
   * 管理者のみ許可
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function viewAny(User $user): bool
  {
    return $user->isAdmin();
  }

  /**
   * view
   * ユーザー詳細の表示権限
   * 管理者のみ許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\User $model
   * @return bool
   */
  public function view(User $user, User $model): bool
  {
    return $user->isAdmin();
  }

  /**
   * create
   * ユーザー新規作成の権限
   * 管理者のみ許可
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
   * ユーザー編集の権限
   * 管理者のみ許可（編集者自身のプロフィール編集は別途プロフィールページで対応）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\User $model
   * @return bool
   */
  public function update(User $user, User $model): bool
  {
    return $user->isAdmin();
  }

  /**
   * delete
   * ユーザー削除の権限
   * 管理者のみ許可、かつ自分自身は削除不可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\User $model
   * @return bool
   */
  public function delete(User $user, User $model): bool
  {
    return $user->isAdmin() && $user->id !== $model->id;
  }
}
