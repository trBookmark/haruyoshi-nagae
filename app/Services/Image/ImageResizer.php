<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * ImageResizer
 * 画像をリサイズ、保存
 * Intervention Image v3（GD ドライバ）を使用
 *
 * リサイズ方式：縦横比維持・長辺基準・拡大なし
 * 保存先：public ディスク（storage/app/public/images/{size}/{filename}）
 * GIF アニメ：リサイズなし・Storage::copy() でコピーのみ
 */
class ImageResizer
{
  private ImageManager $manager;

  public function __construct()
  {
    $this->manager = new ImageManager(new Driver());
  }

  /**
   * resize
   * 画像を各サイズにリサイズして public ディスクに保存
   * GIF アニメは Storage::copy() のみ（Intervention Image で読むとアニメが壊れる）
   *
   * @param  string $sourcePath      処理元ファイルのフルパス（EXIF 除去済み or オリジナル）
   * @param  string $storageFileName 保存ファイル名（UUID + 拡張子）
   * @param  bool   $isGif           GIF アニメーションかどうか
   * @return void
   */
  public function resize(string $sourcePath, string $storageFileName, bool $isGif): void
  {
    $sizes = config('image.resize'); // ['large' => 1920, 'medium' => 600, 'thumb' => 400]

    foreach ($sizes as $size => $pixels) {
      $destPath = 'images/' . $size . '/' . $storageFileName;

      if ($isGif) {
        // GIF アニメ：ファイルコピーのみ（Intervention Image で読むとアニメが壊れる）
        Storage::disk('public')->put($destPath, file_get_contents($sourcePath));
        continue;
      }

      $this->resizeAndSave($sourcePath, $destPath, $pixels);
    }
  }

  /**
   * saveOriginal
   * オリジナルファイルを非公開の local ディスクに保存
   *
   * @param  string $sourcePath      処理元ファイルのフルパス
   * @param  string $storageFileName 保存ファイル名（UUID + 拡張子）
   * @return void
   */
  public function saveOriginal(string $sourcePath, string $storageFileName): void
  {
    $destPath = 'images/original/' . $storageFileName;
    Storage::disk('local')->put($destPath, file_get_contents($sourcePath));
  }

  /**
   * resizeAndSave
   * 単一サイズのリサイズ・エンコード・保存
   * 長辺基準・縦横比維持・拡大なし
   *
   * @param  string $sourcePath 処理元ファイルのフルパス
   * @param  string $destPath   保存先パス（ディスク相対）
   * @param  int    $pixels     長辺の最大ピクセル数
   * @return void
   */
  private function resizeAndSave(string $sourcePath, string $destPath, int $pixels): void
  {
    $image = $this->manager->read($sourcePath);

    // 長辺が $pixels を超える場合のみリサイズ（拡大なし）
    if ($image->width() > $pixels || $image->height() > $pixels) {
      $image->scaleDown(width: $pixels, height: $pixels);
    }

    $encoded = $this->encode($image, $destPath);

    Storage::disk('public')->put($destPath, $encoded);
  }

  /**
   * encode
   * 拡張子に応じた品質パラメータでエンコード
   *
   * @param  \Intervention\Image\Image $image    Intervention Image インスタンス
   * @param  string                    $destPath 保存先パス（拡張子の判定に使用）
   * @return string                              エンコード済みバイナリ
   */
  private function encode(\Intervention\Image\Image $image, string $destPath): string
  {
    $extension = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));

    return match ($extension) {
      'jpg', 'jpeg' => $image->toJpeg(quality: config('image.quality.jpeg', 90))->toString(),
      'png'         => $image->toPng(indexed: false)->toString(),
      default       => $image->toJpeg(quality: config('image.quality.jpeg', 90))->toString(),
    };
  }
}
