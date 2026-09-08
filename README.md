# omatsu393.com

WordPressから刷新する `omatsu393.com` のWebアプリケーションです。最初のメイン機能として、高速道路と下道それぞれの高速料金・燃料代・所要時間を比較し、移動方法を判断しやすくするツールを開発します。

## 技術構成

- Laravel 13 / PHP 8.3
- MySQL 8.4
- Vite / Tailwind CSS
- Docker Compose
- GitHub Actions

## 必要なもの

- Git
- Docker Desktop
- Visual Studio Code（推奨）

PHP、Composer、Node.js、MySQLをPCへ個別にインストールする必要はありません。

## セットアップ

PowerShellで次のコマンドを実行します。

```powershell
Copy-Item .env.example .env
docker compose up -d --build
docker compose exec app php artisan migrate
```

起動後は以下へアクセスします。

- アプリ: http://localhost:8000
- Vite開発サーバー: http://localhost:5173
- MySQL: `127.0.0.1:3307`

初回起動時は、アプリコンテナがComposerパッケージを自動でインストールし、`APP_KEY` を生成します。

## よく使う操作

```powershell
# 状態を確認
docker compose ps

# ログを確認
docker compose logs -f app

# テストを実行
docker compose exec app php artisan test

# コードを整形
docker compose exec app ./vendor/bin/pint

# マイグレーションを実行
docker compose exec app php artisan migrate

# 停止
docker compose down
```

データベースも含めて初期化する場合のみ、`docker compose down -v` を使います。この操作ではローカルのMySQLデータが削除されます。

## 機密情報

- 実際のパスワードやAPIキーは `.env` に保存します。
- `.env` と `.env.*` はGit管理されません。
- `.env.example` には開発用のサンプル値だけを記載します。
- 本番環境の値はGitHub Secretsまたはデプロイ先の環境変数で管理します。

## 開発の進め方

1. Issueを作成する
2. `feature/<issue番号>-<概要>` ブランチを作成する
3. 実装とテストを行う
4. Pull Requestを作成する
5. CI成功後に `main` へマージする

最初のIssue候補は [`docs/initial-issues.md`](docs/initial-issues.md) にまとめています。

## 初期アーキテクチャ方針

- 料金計算ロジックは画面や外部APIから分離し、単体テスト可能にする
- 外部の経路・高速料金APIはインターフェース越しに利用する
- 金額は整数の円、距離はメートル、時間は秒を内部表現の基準とする
- 入力値とAPI応答を検証し、計算根拠を結果画面に表示する

## ライセンス

Private / All rights reserved.
