<?php
/*
 * ユーザーロールを表す列挙型
 * 管理者（admin）と編集者（editor）の2種類を定義
 */

namespace App\Enums;

use App\Concerns\EnumHasOptions;

enum UserRole: string
{
  use EnumHasOptions; // options() メソッドを共通化

  case ADMIN  = 'admin';
  case EDITOR = 'editor';

  /**
   * label
   * 管理画面などで表示する日本語ラベルを返す
   *
   * @return string
   */
  public function label(): string
  {
    return match($this) {
      UserRole::ADMIN  => '管理者',
      UserRole::EDITOR => '編集者',
    };
  }
}
