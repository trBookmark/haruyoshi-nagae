# 長江春芳 ポートフォリオ

アーティスト [長江春芳](https://twitter.com/N_haruyoshi) のためのポートフォリオサイト。\
Laravel 10 ベースの既存サイトを Laravel 13 へ移行し、構成を整理しながらフルリニューアル。

---

## 概要

| 項目 | 内容 |
|---|---|
| フレームワーク | Laravel 13 / PHP 8.3 |
| データベース | MariaDB |
| 管理画面 | Filament v5 |
| 画像処理 | Intervention Image v3 |
| Exif処理 | exiftool |
| ローカル開発 | Docker（Laravel Sail） |
| 本番環境 | 共用レンタルサーバー（SSH 使用） |

---

## 主な機能

### 管理画面

Filament v5 を利用した画像管理 CMS

- **ロールベースのアクセス制御**: 管理者と編集者（アーティスト）を分離
- **画像管理**: アップロード・表示切替・タイトル/タグ編集・並べ替え・削除
- **複数サイズ自動生成**: アップロード時に original / large / medium / thumb を自動生成
- **ブログ管理**: 下書き・公開・非公開の 3 ステータス管理
- **タグ管理**: 画像とブログで共用。使用中タグは削除不可
- **カテゴリ管理**: 表示名・画像・並び順の編集（管理者：新規作成・削除）
- **サブカテゴリ管理**: YouTube プレイリストと連携（1 階層）
- **サイト設定**: 特定ページのテキスト・画像を管理画面から編集

### フロントエンド

CMSに不慣れなアーティストでも扱いやすいシンプル構成を重視

- カテゴリ/タグ別ギャラリー（ページネーションなし、一覧で全表示）
- GIF アニメーションはそのまま動く状態で表示
- YouTube 動画・プレイリスト埋め込み
- Retina ディスプレイ対応
- ブログ一覧・記事表示

---

## 設計方針

### シンプルさの維持

- 余分な機能は加えない。
- アーティストの作品が主役になるよう、サイト自体は白基調でミニマルに設計。

### 1人保守を前提とした構造

- **Laravel 標準に近い実装**: 独自の抽象化を最小限に抑え、Laravel のドキュメントが常に参照できる状態を維持
- **各クラスは小さく**: 機能を詰め込まず、単一責任に近い形で切り分ける
- **Filament の大型化への対応:** Resource・Page・Schema を分離して管理する構成を採用

### 将来の拡張に備えた分離

- 画像処理など重くなりがちな処理はサービスクラスに分離
- 将来的に Queue 化しやすい構造（現在は共用レンタルサーバーで運用しているため Queue 化なし）
- Cloudflare CDN への移行を想定し、`CDN_URL` を書き換えるだけで対応できるよう URL 生成を集約

---

<details>
<summary>画像処理仕様</summary>

| サイズ種別 | 概要 |
|---|---|
| original | アップロード元画像（EXIF 除去・カラープロファイル保持） |
| large | 長辺基準リサイズ（縦横比維持・拡大なし） |
| medium | 同上 |
| thumb | 同上 |

**判断の背景:**

- WebP・mp4 への変換なし（色味・表現の変化を防ぐ）
- GIF アニメーションはリサイズなし（フレーム破損リスクを防ぐ）
- EXIF 除去で位置情報等のプライバシー情報を保護しつつ、カラープロファイルは保持して色味を維持
- 各サイズのピクセル数は `config/image.php` で変更可能（コード修正不要）

画像は `Storage` クラス経由で `public` ディスクに保存。DB には original のファイル名のみ保存し、パス生成はアプリ側で実施。

**チェックサム（SHA-256）:**

アップロード時に SHA-256 ハッシュを生成し `images.checksum` に保存。

- **破損検知**: 保存済みファイルを再ハッシュして DB の値と比較することで、ストレージ上の破損を検出
- **重複検出**: アップロード時に同一ハッシュの既存レコードを照合し、同一ファイルの二重登録を検出（アプリ側バリデーションで制御）

同一ファイルを複数カテゴリに掲載するケースが想定されるため `unique` 制約は付与しない。重複検出はアプリ側のバリデーションで行い、DB には `index` のみ付与。

</details>

---

<details>
<summary>DB 設計の主な判断</summary>

- 旧 `image_details` テーブル廃止、`images` テーブルに統合（不要な JOIN を削減）
- `categories.model_type` でモデルを動的に切り替え（`Image` / `Post`）、カテゴリを複数モデルで共用
- `posts.image_id` でアイキャッチ画像を `images` テーブルと同方式で管理（nullable）
- 中間テーブルの `tag_id` に単独インデックスを付与（`whereHas` のパフォーマンス対策）
- `categories` ↔ `images` の循環依存は、外部キー追加を別マイグレーションに分離して解決

</details>

---

<details>
<summary>セキュリティ方針</summary>

- ユーザー入力はバリデーション・サニタイズ
- SQL は Eloquent またはクエリビルダを使用し、文字列結合による生 SQL を禁止
- アップロード画像: MIME / 拡張子両方を検証
- SVG: アップロード禁止
- 管理画面ルート: 認可ミドルウェアで保護
- Storage 配下のみ保存許可

</details>

---

## セットアップ

**必要なもの:**

- Docker Desktop

**手順:**

### ローカル （Laravel Sail）

```bash
git clone https://github.com/trBookmark/haruyoshi-nagae.git
cd haruyoshi-nagae

# ローカルPHPがある場合
composer install
# ローカルPHPがない場合（Dockerのみ）
# docker run --rm -v $(pwd):/app composer install

./vendor/bin/sail up -d
cp .env.example .env
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan filament:assets # Filament アセットの公開
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan make:admin-user # 管理者ユーザーを対話式で作成
./vendor/bin/sail artisan db:seed --class=PlaylistSeeder
```
> `sail` をエイリアス登録済みの場合は `./vendor/bin/sail` の代わりに `sail` と入力できます。

### 本番 （共用レンタルサーバー）

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan filament:assets # Filament アセットの公開
php artisan storage:link
php artisan make:filament-user # 管理者ユーザーを対話式で作成
php artisan db:seed --class=PlaylistSeeder
```

**Cron の設定（本番のみ）:**

Laravel のスケジューラを動かすため、cPanel の「Cron ジョブ」に以下を登録する。

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

スケジューラが実行するタスク：

| タスク | 頻度 | 目的 |
|---|---|---|
| `livewire:purge-temp-uploads` | 毎日 01:00 | 画像アップロードの一時ファイル（`storage/app/private/livewire-tmp/`）を削除 |

---

## セットアップ後の追加作業

- 管理画面 > サブカテゴリ から YouTube プレイリスト ID を実値に更新
- （必須）no image ファイルの配置
  - 以下のパスに `no-image.png` を配置
  - 配置を忘れると、カテゴリのカバー画像未設定箇所で壊れた画像が表示される
  - storage/app/public/images/large/no-image.png
  - storage/app/public/images/medium/no-image.png
  - storage/app/public/images/thumb/no-image.png

---

## 担当範囲

- DB設計
- Filament管理画面設計
- 画像処理実装
- Docker開発環境構築
- フロント実装
- デプロイ

---

## ライセンス

- **コード**: [MIT License](LICENSE)
- **アートワーク・画像・コンテンツ**: [© 長江春芳 All rights reserved.](https://twitter.com/N_haruyoshi)
