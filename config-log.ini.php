<?php
/////////////////////////////////////////////////
// 各種ログの定義

// 事前定義
$log_common = array(
	// プライベートエリア
	'private' => array(
		'localhost',		// localhost
		'127.0.0.0/8',		// loopback
		'10.0.0.0/8',		// private class A
		'172.16.0.0/12',	// private class B
		'192.168.0.0/16',	// private class C
	),
	// ロギング不要アドレスリスト(共通定義用)
	'nolog_ip' => array(
		'127.0.0.1',
	),
);

/*

**ログの設定

***種別

|種別        |説明                                                            |h
|update      |文書の更新に関するログの指定                                    |
|download    |添付ファイルのダウンロードログに関する指定                      |
|browse      |文書の閲覧に関するログの指定                                    |
|cmd         |rss, opml, lirs などの情報コマンドの実行状況のログについての指定|
|login       |ログイン状況に関するログの指定                                  |
|check       |認証者が閲覧したであろう文書のログに関する指定                  |
|>|~その他の指定|
|auth_netbios|NetBIOS でのユーザ確認を実施するかどうか                        |
|auth_nolog  |認証済みの場合は、ロギングしない                                |
|guess_user  |見做しユーザ一覧情報データベースの作成に関する指定              |

**各ログの詳細な指定について

***use
1:ロギング 0:オフ
***view
all:全項目表示 ts:@diff:host のようにコロンで項目名を指定すると、
指定項目名のみを選択表示できる。
各項目の記述名については、''項目の記述名について'' を参照。
***guest
通常ブランク、認証していないゲストユーザにも開示する場合には、
コロンで区切って定義
***nolog_ip
ロギングしない IPアドレスリストを定義。個々のログ毎に指定が可能。
***file
ロギング情報を単一ページに作成する場合のページ名を指定。

***updtkey
通常のログは追記タイプですが、更新タイプに変更することができます。
その場合には、ここにキー項目なる項目名を指定して下さい。
***mustkey
updtkey が指定されている場合で、更新キーが入力されている場合のみ、
更新します。更新キーがブランクや、未入力の場合には更新されません。


**項目の記述名について

|項目       |説明                      |h
|ts         |タイムスタンプ (UTIME)    |
|@diff      |差分内容                  |
|@guess_diff|差分の推測(閲覧状況の確定)|
|ip         |IPアドレス                |
|host       |ホスト名 (FQDN)           |
|@guess     |推測                      |
|user       |ユーザ名(認証済)          |
|ntlm       |ユーザ名(NTLM認証)        |
|proxy      |Proxy情報                 |
|ua         |ブラウザ情報 (USER-AGENT) |
|del        |削除フラグ                |
|sig        |署名(曖昧)                |
|file       |ファイル名                |
|cmd        |コマンド名                |
|page       |ページ名                  |

***先頭が @ で開始される項目
ログには書かれていない項目。表示する際にのみ指定できる。

*/

$log = array(
	'update' => array(
		// ts:@diff:@guess_diff:ip:host:@guess:user:ntlm:proxy:ua:file:page
		'use'      => 0,
		'view'     => 'ts:@diff:host:user:sig:ua:proxy:del',
		'guest'    => 'ts:ua',
		'nolog_ip' => $log_common['nolog_ip']
		),
	'download' => array(
		// ts:ip:host:@guess:user:ntlm:proxy:ua:file:page
		'use'      => 0,
		'view'     => 'ts:host:@guess:ua:file:proxy',
		'guest'    => 'ts:ua:file',
		'nolog_ip' => $log_common['nolog_ip']
		),
	'browse' => array(
		// ts:host:@guess:user:ntlm:proxy:ua:page
		'use'      => 0,
		'view'     => 'ts:host:@guess:ua:proxy',
		'guest'    => 'ts:ua',
		'nolog_ip' => $log_common['nolog_ip']
		),
	'login' => array(
		// ts:ip:host:auth_api:user:ua
		'use'      => 0,
		'updtkey'  => 'auth_api:user',
		'view'	   => 'ts:user:auth_api',
		'guest'	   => 'ts:user',
		'file'     => ':log/login',
	),
	'check' => array(
		// ts:@guess_diff:ip:host:auth_api:user:ua
		'use'      => 0,
		'mustkey'  => 1,
		'updtkey'  => 'auth_api:user',
		'view'     => 'ts:@guess_diff:user:auth_api',
		'guest'    => 'ts:@guess_diff:user',
	),
	// cmd - rss, rdf, opml, lirs などの情報コマンドの実行状況
	'cmd' => array(
		// ts:ip:host:user:ntlm:proxy:ua:cmd
		'use'      => 0,
		'view'     => 'ts:host:cmd:ua:proxy',
		'guest'    => 'ts:cmd:ua',
		'nolog_ip' => $log_common['nolog_ip'],
		'file'     => ':log/cmd/data'
		),
	// NetBIOS認証の利用可否
	'auth_netbios' => array(
		'use'      => 0,
		'scope'    => $log_common['private']
		),
	'auth_nolog'	   => 0,
	// 見做しユーザ一覧情報
	'guess_user' => array(
		'use'      => 0,
		'file'     => ':log/signature'
		),

);

?>
