<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| スケジュールタスク
|--------------------------------------------------------------------------
*/

// Livewire の一時アップロードファイルを毎日深夜1時に削除
// storeFiles(false) を使用しているため Filament が自動クリーンアップしない
// 対象: storage/app/private/livewire-tmp/ 以下の 24 時間以上経過したファイル
Schedule::command('livewire:purge-temp-uploads')->dailyAt('01:00');
