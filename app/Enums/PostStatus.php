<?php
/*
 * 投稿の公開状態を表す列挙型
 * 'draft' => '下書き', 'published' => '公開', 'private' => '非公開'
 * 将来的には 'archived' => 'アーカイブ', 'scheduled' => '予約投稿' なども追加するかも
 */
namespace App\Enums;

use App\Concerns\EnumHasOptions;

enum PostStatus: string
{
  use EnumHasOptions; // options() メソッドを共通化

  case DRAFT     = 'draft';
  case PUBLISHED = 'published';
  case PRIVATE   = 'private';
  // case ARCHIVED = 'archived';
  // case SCHEDULED = 'scheduled'; // 将来対応するかも？

  /**
   * label
   * 管理画面などで表示する日本語ラベルを返す
   *
   * @return string
   */
  public function label(): string
  {
    return match($this) {
      PostStatus::DRAFT     => '下書き',
      PostStatus::PUBLISHED => '公開',
      PostStatus::PRIVATE   => '非公開',
      // PostStatus::ARCHIVED => 'アーカイブ',
      // PostStatus::SCHEDULED => '予約投稿',
    };
  }
}
