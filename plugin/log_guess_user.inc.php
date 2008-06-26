<?php
/**
 * PukiWiki Plus! 推定ユーザリスト更新プラグイン
 *
 * @copyright	Copyright &copy; 2004-2005,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: log_guess_user.php,v 0.4 2008/06/27 00:33:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * 初期処理
 */
function plugin_log_guess_user_init()
{
	$messages = array(
	'_log_guess_user_msg' => array(
		'msg_put'	=> _('<div>PUT: %s</div>'),
		)
	);
	set_plugin_messages($messages);
}

/**
 * アクションプラグイン処理
 */
function plugin_log_guess_user_convert()
{
	global $_log_guess_user_msg;
	global $log;

	// ユーザを推測する
	// $user = log::guess_user( $data['user'], $data['ntlm'], $data['sig'] );

	$filename = log::set_filename('guess_user','');	// ログファイル名

	$src = array();
	$master = array();
	if (file_exists($filename)) $src = @file( $filename );	// ログの読み込み
	foreach($src as $_src) {
		$data = log::table2array($_src);
		// 0:ua 1:host 2:user
		$master[$data[0]][$data[1]][$data[2]] = '';
	}

	// 更新ログから署名情報の収集
	// $guess[ USER-AGENT ][ ホスト名 ][ ユーザ名 ][任意欄] の配列を戻す
	$guess = log::summary_signature();

	$i = 0;
	foreach($guess as $ua => $val1) {
	foreach($val1 as $host => $val2) {
	foreach($val2 as $user => $val3) {
		if (isset($master[$ua][$host][$user])) continue;
	 	log_put( $filename, '|'.$ua.'|'.$host.'|'.$user.'||');
		$i++;
	}}}

	$msg = sprintf($_log_guess_user_msg['msg_put'],$i);
	return $msg;

}

?>
