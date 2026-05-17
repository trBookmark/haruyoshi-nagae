<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Playlist;
use Illuminate\Database\Seeder;

class PlaylistSeeder extends Seeder
{
  /**
   * サブカテゴリ（YouTubeプレイリスト）の初期データを投入する
   *
   * ⚠️ 注意！: yt_playlist_id はダミー値のため、本番環境では管理画面から実IDに更新する必要がある
   * 管理画面 > サブカテゴリ > 各レコードを編集
   *
   * @return void
   */
  public function run(): void
  {
    $animationId = Category::where('name', 'animation')->value('id');
    $musicId     = Category::where('name', 'music')->value('id');

    $playlists = [
      [
        'category_id'    => $animationId,
        'name'           => 'ミュージックビデオ',
        'yt_playlist_id' => 'DUMMY_ANIMATION_MV', // ⚠️ 要更新
        'sort_order'     => 1,
      ],
      [
        'category_id'    => $animationId,
        'name'           => '共同制作',
        'yt_playlist_id' => 'DUMMY_ANIMATION_CO', // ⚠️ 要更新
        'sort_order'     => 2,
      ],
      [
        'category_id'    => $animationId,
        'name'           => '自主制作',
        'yt_playlist_id' => 'DUMMY_ANIMATION_AN', // ⚠️ 要更新
        'sort_order'     => 3,
      ],
      [
        'category_id'    => $musicId,
        'name'           => '雨にまつわる音楽',
        'yt_playlist_id' => 'DUMMY_MUSIC_RAIN', // ⚠️ 要更新
        'sort_order'     => 1,
      ],
    ];

    foreach ($playlists as $playlist) {
      Playlist::create($playlist);
    }
  }
}
