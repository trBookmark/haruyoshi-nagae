<?php

use App\Models\Category;
use App\Models\Image;
use App\Services\Image\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * exiftoolAvailable
 * ExifTool が実行可能かどうかを返す
 * 非GIF 経路は ExifCleaner を通るため、未導入の環境では該当テストをスキップする
 * CI に ExifTool を導入すれば、テスト側の変更なしで自動的に実行されるようになる
 *
 * @return bool
 */
function exiftoolAvailable(): bool
{
  $binary = config('image.exiftool.binary');

  return ! empty($binary) && is_executable($binary);
}

/**
 * ExifCleaner を経由する画像形式
 * config('image.allowed_mime_types') のうち GIF 以外がここに該当する
 * config に形式を追加した場合は、末尾の「対応形式の網羅」テストが落ちて気づけるようにしてある
 */
dataset('EXIF 除去を経由する画像形式', [
  'JPEG' => ['jpg', 'image/jpeg'],
  'PNG'  => ['png', 'image/png'],
]);

beforeEach(function () {
  Storage::fake('public');
  Storage::fake('local');

  $this->service  = app(ImageUploadService::class);
  $this->category = Category::factory()->create();
});

// ──────────── GIF 経路 ────────────
// GIF は ExifCleaner と IccProfileEmbedder を通らず、Storage へのコピーのみで処理される
// テスト用の GIF は GD 生成のため静止画だが、ImageProcessor の分岐は mime_type だけで決まるため
// 実際のアニメーション GIF と同じ経路を通る（アニメが壊れないこと自体はここでは検証できない）

/**
 * GIF の original が非公開の local ディスクに保存されることを確認
 */
test('GIF の original が local ディスクに保存される', function () {
  $file = UploadedFile::fake()->image('test.gif', 400, 300);

  $result = $this->service->upload($file, $this->category->id);

  Storage::disk('local')->assertExists('images/original/' . $result->storageFileName);
});

/**
 * GIF の各サイズが public ディスクに生成されることを確認
 */
test('GIF の各サイズが public ディスクに生成される', function () {
  $file = UploadedFile::fake()->image('test.gif', 400, 300);

  $result = $this->service->upload($file, $this->category->id);

  foreach (array_keys(config('image.resize')) as $size) {
    Storage::disk('public')->assertExists('images/' . $size . '/' . $result->storageFileName);
  }
});

/**
 * GIF がリサイズされず、全サイズが元ファイルと同一であることを確認
 * Intervention Image を通すとアニメーションが壊れるため、コピーのみで処理している
 */
test('GIF はリサイズされず全サイズが元ファイルと一致する', function () {
  $file   = UploadedFile::fake()->image('test.gif', 400, 300);
  $source = file_get_contents($file->getRealPath());

  $result = $this->service->upload($file, $this->category->id);

  foreach (array_keys(config('image.resize')) as $size) {
    expect(Storage::disk('public')->get('images/' . $size . '/' . $result->storageFileName))
      ->toBe($source);
  }
});

/**
 * ImageUploadResult に必要なメタ情報が入ることを確認
 * checksum は EXIF 除去前のファイルから計算される
 */
test('ImageUploadResult にメタ情報が入る', function () {
  $file             = UploadedFile::fake()->image('sample.gif', 400, 300);
  $expectedChecksum = hash_file('sha256', $file->getRealPath());

  $result = $this->service->upload($file, $this->category->id);

  expect($result->clientFileName)->toBe('sample.gif')
    ->and($result->checksum)->toBe($expectedChecksum)
    ->and($result->width)->toBe(400)
    ->and($result->height)->toBe(300)
    ->and($result->extension)->toBe('gif')
    ->and($result->mimeType)->toBe('image/gif')
    ->and($result->storageFileName)->toEndWith('.gif');
});

// ──────────── JPEG / PNG 経路（ExifTool 必須） ────────────

/**
 * original と各サイズが生成されることを確認
 */
test('original と各サイズが生成される', function (string $extension, string $mimeType) {
  $file = UploadedFile::fake()->image('test.' . $extension, 800, 600);

  $result = $this->service->upload($file, $this->category->id);

  Storage::disk('local')->assertExists('images/original/' . $result->storageFileName);

  foreach (array_keys(config('image.resize')) as $size) {
    Storage::disk('public')->assertExists('images/' . $size . '/' . $result->storageFileName);
  }
})
  ->with('EXIF 除去を経由する画像形式')
  ->skip(fn () => ! exiftoolAvailable(), 'ExifTool が未導入のためスキップ');

/**
 * 長辺が large の基準値を超える画像が縮小されることを確認
 * リサイズは長辺基準・縦横比維持のため、幅で判定する
 */
test('長辺が large の基準値を超える画像は縮小される', function (string $extension, string $mimeType) {
  $base = config('image.resize.large');
  $file = UploadedFile::fake()->image('big.' . $extension, $base + 480, 1200);

  $result = $this->service->upload($file, $this->category->id);

  $info = getimagesizefromstring(
    Storage::disk('public')->get('images/large/' . $result->storageFileName)
  );

  expect($info[0])->toBe($base);
})
  ->with('EXIF 除去を経由する画像形式')
  ->skip(fn () => ! exiftoolAvailable(), 'ExifTool が未導入のためスキップ');

/**
 * 基準値より小さい画像が拡大されないことを確認（拡大なしの方針）
 * 300x200 は thumb の基準値（400）より小さいため、全サイズが原寸のまま保存される
 */
test('基準値より小さい画像は拡大されない', function (string $extension, string $mimeType) {
  $file = UploadedFile::fake()->image('small.' . $extension, 300, 200);

  $result = $this->service->upload($file, $this->category->id);

  foreach (array_keys(config('image.resize')) as $size) {
    $info = getimagesizefromstring(
      Storage::disk('public')->get('images/' . $size . '/' . $result->storageFileName)
    );

    expect($info[0])->toBe(300)
      ->and($info[1])->toBe(200);
  }
})
  ->with('EXIF 除去を経由する画像形式')
  ->skip(fn () => ! exiftoolAvailable(), 'ExifTool が未導入のためスキップ');

/**
 * 出力ファイルの形式が入力と同じであることを確認
 * ImageResizer::encode() の拡張子別の分岐が崩れると、PNG が JPEG として保存されても
 * ファイル名は .png のままになり、拡張子と中身が食い違う
 */
test('出力ファイルの形式が入力と同じになる', function (string $extension, string $mimeType) {
  $file = UploadedFile::fake()->image('format.' . $extension, 800, 600);

  $result = $this->service->upload($file, $this->category->id);

  $info = getimagesizefromstring(
    Storage::disk('public')->get('images/large/' . $result->storageFileName)
  );

  expect($info['mime'])->toBe($mimeType);
})
  ->with('EXIF 除去を経由する画像形式')
  ->skip(fn () => ! exiftoolAvailable(), 'ExifTool が未導入のためスキップ');

// ──────────── バリデーションと重複検出 ────────────

/**
 * 許可されていない MIME タイプはアップロードできないことを確認
 */
test('許可されていない MIME タイプはアップロードできない', function () {
  $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

  expect(fn () => $this->service->upload($file, $this->category->id))
    ->toThrow(ValidationException::class);
});

/**
 * 同一カテゴリに同じ画像を重複してアップロードできないことを確認
 */
test('同一カテゴリに同じ画像は重複してアップロードできない', function () {
  $file     = UploadedFile::fake()->image('dup.gif', 400, 300);
  $checksum = hash_file('sha256', $file->getRealPath());

  Image::factory()->for($this->category)->create(['checksum' => $checksum]);

  expect(fn () => $this->service->upload($file, $this->category->id))
    ->toThrow(ValidationException::class);
});

/**
 * 別カテゴリであれば同じ画像をアップロードできることを確認
 * 重複検出はカテゴリ内に限定されているという仕様の確認
 * このテストがないと、checkDuplicate() のカテゴリ条件を外しても気づけない
 */
test('別カテゴリであれば同じ画像をアップロードできる', function () {
  $file     = UploadedFile::fake()->image('dup.gif', 400, 300);
  $checksum = hash_file('sha256', $file->getRealPath());

  Image::factory()->for(Category::factory()->create())->create(['checksum' => $checksum]);

  $result = $this->service->upload($file, $this->category->id);

  expect($result->checksum)->toBe($checksum);
});

// ──────────── 対応形式の網羅 ────────────

/**
 * config の allowed_mime_types が、このファイルでテストしている形式と一致することを確認
 *
 * 【対応形式を追加する場合の注意】
 * config への追記だけでは動作しない。以下をすべて更新すること。
 *
 * 1. ExifCleaner            新形式で EXIF 除去と ICC 保持が意図どおり動くかを確認する
 * 2. IccProfileEmbedder     新形式に ICC プロファイルを再適用できるかを確認する
 * 3. ImageMetaExtractor::extensionFromMime()
 *                           match に新形式を追加する。未追加だとクライアント申告の拡張子に落ちる
 * 4. ImageResizer::encode() match に新形式を追加する。未追加だと JPEG として保存され、
 *                           ファイル名の拡張子と中身が食い違う
 * 5. このファイルのデータセットと、下の期待値
 *
 * データセットを config から自動生成すれば、このテストは不要にできる。
 * そうしていないのは、config に追記した時点でここを落とし、
 * 上記の更新箇所が存在すること自体に気づけるようにするため。
 */
test('allowed_mime_types にテスト未対応の形式が追加されていない', function () {
  expect(config('image.allowed_mime_types'))
    ->toEqualCanonicalizing(['image/jpeg', 'image/png', 'image/gif']);
});
