<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
  use HasFactory;

  protected $fillable = [
    'sort_order',
    'status',
    'image_id',
    'title',
    'content',
    'excerpt',
    'memo',
    'published_at',
  ];

  protected $attributes = [
    'sort_order' => 0,
    'status'     => PostStatus::DRAFT->value, // スカラー値で設定
  ];

  protected function casts(): array
  {
    return [
      'sort_order'   => 'integer',
      'status'       => PostStatus::class,
      'published_at' => 'datetime',
    ];
  }

  // ──────────── Relations ────────────

  /**
   * eyecatch
   * Post のアイキャッチ画像
   * image_id は nullable のため withDefault() は使用しない
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function eyecatch(): BelongsTo
  {
    return $this->belongsTo(Image::class, 'image_id');
  }

  /**
   * tags
   * Post に属する Tag
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function tags(): BelongsToMany
  {
    return $this->belongsToMany(Tag::class, 'post_tag');
  }
}