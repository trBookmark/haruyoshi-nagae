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

  /**
   * SYSTEM_NAME
   * Category カバー画像/サイト設定画像などフロントエンドに公開しない内部専用 Category name
   * is_active=false で管理
   * フロントの where('is_active', true) クエリから除外
   *
   * Resource のフィルタリングや ImageUploadService でこの定数を使用
   * フロントエンドに公開しない Category を一元管理する
   */
  public const SYSTEM_NAME = '__system';

  /**
   * isSystemName
   * 内部専用 Category かどうかを判定
   * '__' prefix を規約とする
   * 将来 Category が増えても定数を追加するだけで管理画面の除外フィルタが適用される
   *
   * @param  string $name  Category 名
   * @return bool
   */
  public static function isSystemName(string $name): bool
  {
    return str_starts_with($name, '__');
  }

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
   * Category のカバー画像
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
