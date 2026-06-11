<?php

namespace App\Providers;

use App\Services\Image\ExifCleaner;
use App\Services\Image\ImageFileNameGenerator;
use App\Services\Image\ImageMetaExtractor;
use App\Services\Image\ImageProcessor;
use App\Services\Image\ImageResizer;
use App\Services\Image\ImageUploadService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * register
   * サービスコンテナへのバインディング登録
   *
   * @return void
   */
  public function register(): void
  {
    $this->app->singleton(ImageUploadService::class, function () {
      $exifCleaner  = new ExifCleaner();
      $resizer      = new ImageResizer();
      $processor    = new ImageProcessor($exifCleaner, $resizer);

      return new ImageUploadService(
        fileNameGenerator: new ImageFileNameGenerator(),
        metaExtractor:     new ImageMetaExtractor(),
        processor:         $processor,
      );
    });
  }

  /**
   * boot
   * アプリケーションサービスの起動処理
   *
   * @return void
   */
  public function boot(): void
  {
    //
  }
}
