<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'description',
    'is_active',
  ];

  protected $attributes = [
    'is_active' => true,
  ];

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
    ];
  }

  // ──────────── Helper ────────────

  /**
   * isInUse
   * タグが画像またはブログ記事に使われているかどうかを判定
   * 削除制御の判定に使用
   *
   * @return bool
   */
  public function isInUse(): bool
  {
    return $this->images()->exists() || $this->posts()->exists();
  }

  // ──────────── Relations ────────────

  /**
   * images
   * Tag に属する Image
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function images(): BelongsToMany
  {
    return $this->belongsToMany(Image::class, 'image_tag');
  }

  /**
   * posts
   * Tag に属する Post
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class, 'post_tag');
  }
}
