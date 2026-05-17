<?php

namespace App\Models;

use App\Enums\ModelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'name_ja',
    'image_id',
    'model_type',
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
      'model_type' => ModelType::class,
      'is_active'  => 'boolean',
      'sort_order' => 'integer',
    ];
  }

  // ──────────── Relations ────────────

  /**
   * coverImage
   * Category のサムネイル画像
   * images より後に定義（循環依存はマイグレーション側で解決済み）
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function coverImage(): BelongsTo
  {
    return $this->belongsTo(Image::class, 'image_id');
  }

  /**
   * images
   * Category に属する Image
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function images(): HasMany
  {
    return $this->hasMany(Image::class);
  }

  /**
   * playlists
   * Category に属する Playlist
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function playlists(): HasMany
  {
    return $this->hasMany(Playlist::class);
  }
}
