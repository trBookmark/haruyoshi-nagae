<?php

namespace App\Models;

use App\Enums\ThumbnailAlign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Image extends Model
{
  use HasFactory;

  protected $fillable = [
    'category_id',
    'is_active',
    'sort_order',
    'year',
    'storage_file_name',
    'client_file_name',
    'checksum',
    'width',
    'height',
    'file_size',
    'extension',
    'mime_type',
    'thumbnail_align',
    'title',
    'caption',
    'memo',
  ];

  protected $attributes = [
    'is_active'        => true,
    'sort_order'       => 0,
    'thumbnail_align'  => ThumbnailAlign::CENTER->value,
  ];

  protected function casts(): array
  {
    return [
      'is_active'       => 'boolean',
      'sort_order'      => 'integer',
      'year'            => 'integer',
      'width'           => 'integer',
      'height'          => 'integer',
      'file_size'       => 'integer',
      'thumbnail_align' => ThumbnailAlign::class,
    ];
  }

  // ──────────── URL Accessor ────────────
  // CDN 移行時は .env: CDN_URL を書き換え、再キャッシュするだけで全 URL が切り替わる

  /**
   * baseUrl
   * Storage のベース URL を返す
   * CDN_URL が設定されていればそちらを優先
   * static 化により noImageUrl() からも参照可能
   *
   * @return string Storage のベース URL
   */
  private static function baseUrl(): string
  {
    return rtrim(config('image.cdn_url') ?? config('app.url'), '/');
  }

  /**
   * imageUrl
   * サイズに応じた公開 URL を返す
   * GIF アニメ: リサイズなし -> 常に original を返す
   *
   * @param string $size original|large|medium|thumb
   * @return string      指定サイズの公開 URL
   */
  public function imageUrl(string $size = 'original'): string
  {
    $resolvedSize = $this->isAnimatedGif() ? 'original' : $size;

    return self::baseUrl() . '/storage/images/' . $resolvedSize . '/' . $this->storage_file_name;
  }

  /**
   * noImageUrl
   * no image（デフォルト画像）の公開 URL を返す
   * image_id が null の場合などのフォールバック用途
   * ファイル名は config('image.no_image_filename') で管理
   *
   * @param string $size original|large|medium|thumb
   * @return string      指定サイズのノーイメージ公開 URL
   */
  public static function noImageUrl(string $size = 'original'): string
  {
    $filename = config('image.no_image_filename', 'no-image.png');

    return self::baseUrl() . '/storage/images/' . $size . '/' . $filename;
  }

  /**
   * isAnimatedGif
   * GIF アニメ判定
   *
   * @return boolean
   */
  public function isAnimatedGif(): bool
  {
    return $this->mime_type === 'image/gif';
  }

  // ──────────── Relations ────────────

  /**
   * category
   * Image が属する Category
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  /**
   * tags
   * Image に属する Tag
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function tags(): BelongsToMany
  {
    return $this->belongsToMany(Tag::class, 'image_tag');
  }
}
