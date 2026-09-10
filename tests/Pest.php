<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| ディレクトリごとに適用する base class と trait を指定する。
|
| Feature: HTTP request から response までを通す。DB を使うため RefreshDatabase で
|          各 test を transaction に包み、test 同士を独立させる。
|          withoutVite() で @vite を無効化する。検証したいのは controller の分岐と
|          view の出力内容であり、build 済み asset の hash 名は対象外のため。
|          これがないと public/build/manifest.json を要求され、build していない環境
|          （clone 直後・CI）で view を描画する test が全て落ちる。
| Unit:    class 単体のロジックを検証する。DB に触れないため RefreshDatabase は付けない。
|          TestCase を extend するのは config() など Laravel の application 機能を
|          使うため（素の PHPUnit だと config('image.resize.…') が解決できない）。
|
*/

pest()->extend(TestCase::class)
  ->use(RefreshDatabase::class)
  ->beforeEach(fn () => $this->withoutVite())
  ->in('Feature');

pest()->extend(TestCase::class)
  ->in('Unit');
