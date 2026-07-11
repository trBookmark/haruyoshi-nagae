<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

class PlaylistPolicy
{
  /**
   * viewAny
   * サブカテゴリ一覧の表示権限
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
   * サブカテゴリ詳細の表示権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Playlist $playlist
   * @return bool
   */
  public function view(User $user, Playlist $playlist): bool
  {
    return true;
  }

  /**
   * create
   * サブカテゴリ新規作成の権限
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
   * サブカテゴリ編集の権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Playlist $playlist
   * @return bool
   */
  public function update(User $user, Playlist $playlist): bool
  {
    return true;
  }

  /**
   * delete
   * サブカテゴリ削除の権限
   * 全員不可（誤操作やデータ不整合を防ぐため削除禁止）
   * 使用/不使用の切り替えは is_active で管理
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Playlist $playlist
   * @return bool
   */
  public function delete(User $user, Playlist $playlist): bool
  {
    return false;
  }

  /**
   * reorder
   * サブカテゴリ並び替えの権限
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
