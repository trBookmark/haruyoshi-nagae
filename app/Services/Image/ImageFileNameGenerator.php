<?php

namespace App\Services\Image;

use Illuminate\Support\Str;

/**
 * ImageFileNameGenerator
 * アップロード画像の保存ファイル名を生成
 * UUID + 元の拡張子（小文字）で一意なファイル名を返す
 */
class ImageFileNameGenerator
{
  /**
   * generate
   * UUID + 拡張子（小文字）で保存ファイル名を生成
   *
   * @param  string $extension ファイル拡張子（ドットなし、例: jpg / png / gif）
   * @return string            生成したファイル名（例: 550e8400-e29b-41d4-a716-446655440000.jpg）
   */
  public function generate(string $extension): string
  {
    return Str::uuid() . '.' . strtolower($extension);
  }
}
