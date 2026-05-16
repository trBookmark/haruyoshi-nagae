<?php
/*
 * 固定ページのタイプを表す列挙型
 * トップメッセージ、aboutメッセージ、お問い合わせメッセージ、確認メッセージ、送信完了メッセージの5種類を定義
 */
namespace App\Enums;

use App\Concerns\EnumHasOptions;

enum PageType: string
{
  use EnumHasOptions; // options() メソッドを共通化

  case TOP_MSG     = 'top_msg';
  case ABOUT_MSG   = 'about_msg';
  case CONTACT_MSG = 'contact_msg';
  case CONFIRM_MSG = 'confirm_msg';
  case SENT_MSG    = 'sent_msg';

  /**
   * 管理画面などで表示する日本語ラベルを返す
   */
  public function label(): string
  {
    return match($this) {
      PageType::TOP_MSG     => 'トップメッセージ',
      PageType::ABOUT_MSG   => 'aboutメッセージ',
      PageType::CONTACT_MSG => 'お問い合わせメッセージ',
      PageType::CONFIRM_MSG => '確認メッセージ',
      PageType::SENT_MSG    => '送信完了メッセージ',
    };
  }
}
