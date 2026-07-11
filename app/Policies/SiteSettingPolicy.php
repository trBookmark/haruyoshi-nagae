<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
  /**
   * viewAny
   * サイト設定一覧の表示権限
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
   * サイト設定詳細の表示権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\SiteSetting $siteSetting
   * @return bool
   */
  public function view(User $user, SiteSetting $siteSetting): bool
  {
    return true;
  }

  /**
   * create
   * サイト設定新規作成の権限
   * 全員不可（Seeder 固定・追加禁止、Create ページ非設置と独立した認可層）
   *
   * @param  \App\Models\User $user
   * @return bool
   */
  public function create(User $user): bool
  {
    return false;
  }

  /**
   * update
   * サイト設定編集の権限
   * 管理者・編集者：許可
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\SiteSetting $siteSetting
   * @return bool
   */
  public function update(User $user, SiteSetting $siteSetting): bool
  {
    return true;
  }

  /**
   * delete
   * サイト設定削除の権限
   * 全員不可（サイト全体の設定を管理する重要データのため削除禁止）
   *
   * @param  \App\Models\User $user
   * @param  \App\Models\SiteSetting $siteSetting
   * @return bool
   */
  public function delete(User $user, SiteSetting $siteSetting): bool
  {
    return false;
  }
}
