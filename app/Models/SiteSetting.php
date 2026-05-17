<?php

namespace App\Models;

use App\Enums\PageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
  use HasFactory;

  protected $fillable = [
    'page_type',
    'label',
    'description',
    'image_required',
    'image_id',
  ];

  protected $attributes = [
    'image_required' => false,
    // page_type は unique であり Seeder で流し込むため default値なし
  ];

  protected function casts(): array
  {
    return [
      'page_type'      => PageType::class,
      'image_required' => 'boolean',
    ];
  }

  // ──────────── Relations ────────────

  /**
   * image
   * SiteSetting に紐づく Image
   * image_id は nullable のため withDefault() は使用しない
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function image(): BelongsTo
  {
    return $this->belongsTo(Image::class, 'image_id');
  }
}
