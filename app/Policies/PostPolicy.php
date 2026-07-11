<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
  /**
   * viewAny
   * ブログ一覧の表示権限
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
   * ブログ詳細の表示権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Post $post
   * @return bool
   */
  public function view(User $user, Post $post): bool
  {
    return true;
  }

  /**
   * create
   * ブログ新規作成の権限
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
   * ブログ編集の権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Post $post
   * @return bool
   */
  public function update(User $user, Post $post): bool
  {
    return true;
  }

  /**
   * delete
   * ブログ削除の権限
   * 管理者・編集者：許可（アイキャッチの後始末は deleteWithEyecatch() で実施）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Post $post
   * @return bool
   */
  public function delete(User $user, Post $post): bool
  {
    return true;
  }

  /**
   * reorder
   * ブログ並び替えの権限
   * 管理者・編集者：許可
   * メソッド未定義でも並び替えは動作するが、意図を明示するため定義する
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function reorder(User $user): bool
  {
    return true;
  }
}
