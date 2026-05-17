<?php
/*
 * モデルタイプを表す列挙型
 * カテゴリがどのモデル（Image, Postなど）に属するかを管理するための列挙型
 * 'Image' => '画像', 'Post' => 'ブログ記事'
 */
namespace App\Enums;

use App\Concerns\EnumHasOptions;

enum ModelType: string
{
  use EnumHasOptions; // options() メソッドを共通化

  case IMAGE  = 'Image';
  case POST = 'Post';

  /**
   * label
   * 管理画面などで表示する日本語ラベルを返す
   *
   * @return string
   */
  public function label(): string
  {
    return match($this) {
      ModelType::IMAGE  => '画像',
      ModelType::POST => 'ブログ記事',
    };
  }
}
