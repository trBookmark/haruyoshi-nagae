<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Post extends Model implements Sortable
{
  use HasFactory, SortableTrait;

  /**
   * booted
   * Model イベント登録
   * saving: ステータスが「公開」かつ published_at 未設定の場合に現在日時を自動セット
   * 下書き・非公開に戻しても published_at は保持する（クリアは手動）
   *
   * @return void
   */
  protected static function booted(): void
  {
    static::saving(function (Post $post): void {
      if ($post->status === PostStatus::PUBLISHED && $post->published_at === null) {
        $post->published_at = now();
      }
    });
  }

  public array $sortable = [
    'order_column_name'  => 'sort_order',
    'sort_when_creating' => true, // 新規記事は末尾に自動採番（一覧のドラッグで並び替え可能）
  ];

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

  // ──────────── Helper ────────────

  /**
   * deleteWithEyecatch
   * ブログ記事の削除時、アイキャッチ画像が他から参照されていなければ画像レコード＋ファイルを削除
   * posts.image_id は restrictOnDelete のため、先に記事を削除してから画像を削除する
   *
   * @return void
   */
  public function deleteWithEyecatch(): void
  {
    DB::transaction(function (): void {
      $eyecatch = $this->eyecatch;

      $this->delete();

      if ($eyecatch && ! $eyecatch->isInUse()) {
        $eyecatch->delete(); // Image::booted() の deleting イベントで Storage ファイルも削除される
      }
    });
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