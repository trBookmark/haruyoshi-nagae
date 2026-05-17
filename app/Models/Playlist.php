<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Playlist extends Model
{
  use HasFactory;

  protected $fillable = [
    'category_id',
    'name',
    'yt_playlist_id',
    'image_id',
    'sort_order',
    'is_active',
    'description',
  ];

  protected $attributes = [
    'sort_order' => 0,
    'is_active'  => true,
  ];

  protected function casts(): array
  {
    return [
      'sort_order' => 'integer',
      'is_active'  => 'boolean',
    ];
  }

  // ──────────── Relations ────────────

  /**
   * category
   * Playlist が属する Category
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  /**
   * thumbnail
   * Playlist のサムネイル画像
   * image_id は nullable のため withDefault() は使用しない
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function thumbnail(): BelongsTo
  {
    return $this->belongsTo(Image::class, 'image_id');
  }
}
