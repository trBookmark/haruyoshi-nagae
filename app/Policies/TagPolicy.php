<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
  /**
   * viewAny
   * タグ一覧の表示権限
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
   * タグ詳細の表示権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Tag $tag
   * @return bool
   */
  public function view(User $user, Tag $tag): bool
  {
    return true;
  }

  /**
   * create
   * タグ新規作成の権限
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
   * タグ編集の権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Tag $tag
   * @return bool
   */
  public function update(User $user, Tag $tag): bool
  {
    return true;
  }

  /**
   * delete
   * タグ削除の権限
   * 使用中（isInUse）のタグ：全員不可（データ不整合防止）
   * UI 層（DeleteAction の hidden）・DB 層（pivot の restrictOnDelete）と
   * 独立した認可層として多重防御を構成する
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\Tag $tag
   * @return bool
   */
  public function delete(User $user, Tag $tag): bool
  {
    return ! $tag->isInUse();
  }
}
