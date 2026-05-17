<?php
/*
 * サムネイルの配置を表す列挙型
 * CSSにて画像のサムネイル表示位置を管理するための列挙型
 * 'c' => '中央', 't' => '上', 'r' => '右', 'b' => '下', 'l' => '左'
 */

namespace App\Enums;

use App\Concerns\EnumHasOptions;

enum ThumbnailAlign: string
{
  use EnumHasOptions; // options() メソッドを共通化

  case CENTER = 'c';
  case TOP    = 't';
  case RIGHT  = 'r';
  case BOTTOM = 'b';
  case LEFT   = 'l';

  /**
   * label
   * 管理画面などで表示する日本語ラベルを返す
   *
   * @return string
   */
  public function label(): string
  {
    return match($this) {
      ThumbnailAlign::CENTER => '中央',
      ThumbnailAlign::TOP    => '上',
      ThumbnailAlign::RIGHT  => '右',
      ThumbnailAlign::BOTTOM => '下',
      ThumbnailAlign::LEFT   => '左',
    };
  }
}
