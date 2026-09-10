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
| Unit:    class 単体のロジックを検証する。DB に触れないため RefreshDatabase は付けない。
|          TestCase を extend するのは config() など Laravel の application 機能を
|          使うため（素の PHPUnit だと config('image.resize.…') が解決できない）。
|
*/

pest()->extend(TestCase::class)
  ->use(RefreshDatabase::class)
  ->in('Feature');

pest()->extend(TestCase::class)
  ->in('Unit');
