<?php

namespace App\Models;

use App\Enums\ModelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Category extends Model implements Sortable
{
  use HasFactory, SortableTrait;

  public array $sortable = [
    'order_column_name'  => 'sort_order',
    'sort_when_creating' => false, // Seeder で sort_order を明示指定するため自動採番を無効化
  ];

  /**
   * SYSTEM_NAME
   * フロントエンドに公開しない内部専用カテゴリ
   * カテゴリカバー画像・サイト設定画像など
   * is_active=false なので where('is_active', true) クエリから自動除外される
   *
   * Resource のフィルタリングや ImageUploadService でこの定数を使用
   * 文字列 '__system' をコード内で散在させない。
   */
  public const SYSTEM_NAME = '__system';

  /**
   * isSystemName
   * 内部専用カテゴリかどうかを判定
   * '__' プレフィックスを規約とする
   * 将来カテゴリが増えても定数を追加するだけで管理画面の除外フィルタが自動適用される
   *
   * @param  string $name カテゴリ名
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
