<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeLivewireTempUploads extends Command
{
  protected $signature   = 'livewire:purge-temp-uploads
                            {--hours=24 : この時間（時間単位）より古いファイルを削除する}
                            {--dry-run  : 削除せずに対象ファイルを表示する}';

  protected $description = 'Livewire の一時アップロードファイルを削除する';

  /**
   * handle
   * storage/app/private/livewire-tmp/ 以下の古い一時ファイルを削除する
   * --hours で指定した時間より古いファイルのみ対象（デフォルト 24 時間）
   *
   * @return int
   */
  public function handle(): int
  {
    $hours   = (int) $this->option('hours');
    $dryRun  = $this->option('dry-run');
    $disk    = Storage::disk('local');
    $baseDir = 'livewire-tmp';

    if (! $disk->exists($baseDir)) {
      $this->info('livewire-tmp ディレクトリが存在しません。スキップします。');
      return self::SUCCESS;
    }

    $files     = $disk->allFiles($baseDir);
    $threshold = now()->subHours($hours)->timestamp;
    $targets   = [];

    foreach ($files as $file) {
      if ($disk->lastModified($file) < $threshold) {
        $targets[] = $file;
      }
    }

    if (empty($targets)) {
      $this->info("削除対象のファイルはありません（{$hours} 時間以上経過したファイルなし）。");
      return self::SUCCESS;
    }

    if ($dryRun) {
      $this->info('[dry-run] 以下のファイルが削除対象です：');
      foreach ($targets as $file) {
        $this->line('  ' . $file);
      }
      $this->info(count($targets) . ' 件');
      return self::SUCCESS;
    }

    $disk->delete($targets);

    $this->info(count($targets) . ' 件の一時ファイルを削除しました。');

    return self::SUCCESS;
  }
}
