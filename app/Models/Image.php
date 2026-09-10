<?php

namespace App\Models;

use App\Enums\ThumbnailAlign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
  use HasFactory;

  /**
   * booted
   * Modelイベント登録
   * deleting: DB レコード削除前に Storage ファイルを削除
   *
   * @return void
   */
  protected static function booted(): void
  {
    static::deleting(function (Image $image): void {
      // original（非公開 local ディスク）
      Storage::disk('local')->delete('images/original/' . $image->storage_file_name);

      // large / medium / thumb（公開 public ディスク）
      foreach (array_keys(config('image.resize', [])) as $size) {
        Storage::disk('public')->delete('images/' . $size . '/' . $image->storage_file_name);
      }
    });
  }

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
    return rtrim(config('image.cdn_url') ?: config('app.url'), '/');
  }

  /**
   * imageUrl
   * サイズに応じた公開 URL を返す
   * original:  非公開ディスクのため例外をthrow
   * GIF アニメ: large/medium/thumb にそのままコピー、$size をそのまま使用
   *
   * @param  string $size large|medium|thumb
   * @return string       指定サイズの公開 URL
   *
   * @throws \InvalidArgumentException 無効なサイズ（originalなど）が渡された場合
   */
  public function imageUrl(string $size = 'large'): string
  {
    if ($size === 'original') {
      throw new \InvalidArgumentException(
        'imageUrl() の引数が無効です。large / medium / thumb のいずれかを指定してください。'
      );
    }

    return self::baseUrl() . '/storage/images/' . $size . '/' . $this->storage_file_name;
  }

  /**
   * noImageUrl
   * no image（デフォルト画像）の公開 URL を返す
   * image_id が null の場合などのフォールバック用途
   * ファイル名は config('image.no_image_filename') で管理
   *
   * @param  string $size large|medium|thumb
   * @return string       指定サイズのノーイメージ公開 URL
   */
  public static function noImageUrl(string $size = 'large'): string
  {
    $filename = config('image.no_image_filename', 'no-image.png');

    return self::baseUrl() . '/storage/images/' . $size . '/' . $filename;
  }

  /**
   * variantWidth
   * 指定サイズの実ファイル幅を返す
   * リサイズは長辺基準・縦横比維持・拡大なし
   * 長辺が基準値以下の画像は原寸のまま
   * GIF アニメはリサイズしない（常に原寸）
   *
   * @param  string   $size large|medium|thumb
   * @return int|null       幅（px）。width/height が未記録の場合は null
   */
  public function variantWidth(string $size): ?int
  {
    if ($this->width === null || $this->height === null) {
      return null;
    }

    $base = config('image.resize.' . $size);

    if ($this->isAnimatedGif() || $base === null) {
      return $this->width;
    }

    $longest = max($this->width, $this->height);

    return $longest <= $base
      ? $this->width
      : (int) round($this->width * $base / $longest);
  }

  /**
   * srcset
   * 実ファイル幅にもとづく srcset 属性値を組み立てる
   * config の基準値をそのまま記述子にすると、
   * 拡大なし方針により基準値より小さいまま保存された画像で記述子が実態とずれ、
   * ブラウザの密度補正によって表示サイズとリソース選択が狂う
   * 同じ幅になったサイズは重複するため1件にまとめる
   *
   * @param  string[] $sizes large|medium|thumb を小さい順に指定
   * @return string          "url 400w, url 600w" 形式。幅が不明な場合は空文字
   */
  public function srcset(array $sizes = ['medium', 'large']): string
  {
    $entries = [];

    foreach ($sizes as $size) {
      $width = $this->variantWidth($size);

      if ($width !== null && ! isset($entries[$width])) {
        $entries[$width] = $this->imageUrl($size) . ' ' . $width . 'w';
      }
    }

    return implode(', ', $entries);
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

  // ──────────── Scope ────────────

  /**
   * scopeActive
   * フロントに公開する画像（is_active=true）に絞り込む
   * 公開条件をここに集約し、呼び出し側から生の where を排除する
   *
   * @param  \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', true);
  }

  /**
   * scopeOrderedForGallery
   * フロントの表示順（sort_order 昇順、同値は id 昇順）を適用
   * sort_order は default 0 で重複しうるため、id を第2キーにして順序を一意に確定する
   * 一覧ページと個別ページの前後ナビで並びを一致させるため、定義をここに集約
   *
   * @param  \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeOrderedForGallery(Builder $query): Builder
  {
    return $query->orderBy('sort_order')->orderBy('id');
  }

  // ──────────── Helper ────────────

  /**
   * isInUse
   * 画像が他のレコードから参照されているかどうかを判定
   * FK 制約による削除禁止の判定に使用
   *
   * @return bool
   */
  public function isInUse(): bool
  {
    return $this->usedByCategory()->exists()
      || $this->usedBySiteSetting()->exists()
      || $this->usedByPlaylist()->exists()
      || $this->usedByPost()->exists();
  }

  /**
   * inUseDescription
   * 使用中の場合に管理画面に表示する説明文を返す
   * 使用されていない場合は null を返す
   *
   * @return string|null
   */
  public function inUseDescription(): ?string
  {
    if ($category = $this->usedByCategory()->first()) {
      return "カテゴリ「{$category->name}」のカバー画像として使用中";
    }

    if ($setting = $this->usedBySiteSetting()->first()) {
      return "サイト設定「{$setting->label}」の画像として使用中";
    }

    if ($playlist = $this->usedByPlaylist()->first()) {
      return "プレイリスト「{$playlist->name_ja}」のカバー画像として使用中";
    }

    if ($post = $this->usedByPost()->first()) {
      return "ブログ記事「{$post->title}」のアイキャッチとして使用中";
    }

    return null;
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

  // ──────────── 使用箇所リレーション（削除制御・説明文生成用） ────────────

  /**
   * usedByCategory
   * この画像をカバー画像として使用している Category
   * HasMany：将来的に複数カテゴリで同一画像を使用するケースに備えて HasMany で定義
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function usedByCategory(): HasMany
  {
    return $this->hasMany(Category::class, 'image_id');
  }

  /**
   * usedBySiteSetting
   * この画像を使用している SiteSetting
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function usedBySiteSetting(): HasMany
  {
    return $this->hasMany(SiteSetting::class, 'image_id');
  }

  /**
   * usedByPlaylist
   * この画像をサムネイルとして使用している Playlist
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function usedByPlaylist(): HasMany
  {
    return $this->hasMany(Playlist::class, 'image_id');
  }

  /**
   * usedByPost
   * この画像をアイキャッチとして使用している Post
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function usedByPost(): HasMany
  {
    return $this->hasMany(Post::class, 'image_id');
  }
}
