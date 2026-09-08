# 初期Issue一覧

GitHubリポジトリ作成後、以下をIssueとして登録します。上から順に進める想定です。

## 1. 開発環境の起動確認

**Labels:** `setup`, `priority:high`

- Docker Composeで `app`、`node`、`db` が起動する
- 初回マイグレーションが完了する
- `http://localhost:8000` を表示できる
- テストとPintを実行できる

## 2. 比較条件入力画面を作成

**Labels:** `feature`, `frontend`, `priority:high`

- 出発地と目的地を入力できる
- 車の燃費、燃料単価を入力できる
- 有料道路の利用条件を選択できる
- 入力エラーを日本語で表示する

## 3. 移動費計算のドメインモデルを実装

**Labels:** `feature`, `backend`, `priority:high`

- 高速料金と燃料代を合算できる
- 距離、燃費、燃料単価から燃料代を計算できる
- 金額の丸め規則を定義する
- 正常系・境界値の単体テストを追加する

## 4. 経路検索サービスのインターフェースを定義

**Labels:** `architecture`, `backend`, `priority:high`

- 高速ルートと下道ルートに共通するデータ形式を定義する
- 外部API固有の値をアプリ内部へ漏らさない
- テスト用のFake実装を用意する

## 5. 地図・経路APIを選定して接続

**Labels:** `research`, `backend`, `priority:high`

- 候補APIの料金、利用規約、国内経路精度を比較する
- APIキーを `.env` で管理する
- タイムアウト、失敗時、利用上限到達時の処理を実装する
- API応答の契約テストを追加する

## 6. 比較結果画面を作成

**Labels:** `feature`, `frontend`, `priority:high`

- 高速と下道の総費用を並べて表示する
- 高速料金、燃料代、距離、所要時間の内訳を表示する
- 差額と短縮時間を強調表示する
- スマートフォンで読みやすくする

## 7. 時間価値を含む比較を追加

**Labels:** `feature`, `backend`, `priority:medium`

- 1時間あたりの時間価値を任意入力できる
- 移動費と時間コストを分けて表示する
- 入力しない場合は単純な実費比較を維持する

## 8. 比較履歴の保存を実装

**Labels:** `feature`, `database`, `priority:medium`

- 比較条件と結果をMySQLへ保存する
- 個人情報を保存しない設計にする
- 保存期間と削除方針を決める

## 9. SEO・OGP・基本ページを整備

**Labels:** `seo`, `content`, `priority:medium`

- title、description、canonical、OGPを設定する
- プライバシーポリシーと免責事項を作成する
- sitemap.xmlとrobots.txtを用意する

## 10. 本番デプロイ手順を構築

**Labels:** `deployment`, `security`, `priority:medium`

- 本番サーバーの要件を確定する
- GitHub Actionsまたは手動デプロイ手順を決める
- 本番用環境変数を安全に設定する
- HTTPS、バックアップ、監視、ロールバックを確認する
