<?php
/**
 * PukiWiki Plus! 推定ユーザプラグイン
 *
 * @copyright	Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: log_whois.php,v 0.2 2005/06/11 21:56:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * 初期処理
 */
function plugin_log_whois_init()
{
	$messages = array(
	'_log_whois_msg' => array(
		'msg_whois'	=> _('<div>Mr/Ms %s ?</div>'),
		)
	);
	set_plugin_messages($messages);
}

/**
 * アクションプラグイン処理
 */
function plugin_log_whois_convert()
{
	global $log;
	global $log_ua;
	global $_log_whois_msg;

	if (!$log['guess_user']['use']) return '';	// 推定ユーザ処理が無効の場合
	$filename = log::set_filename('guess_user','');	// ログファイル名

	// ログの読み込み
	if (!file_exists($filename)) return '';
	$src = @file( $filename );
	$guess = array();
	foreach($src as $_src) {
		$data = log::table2array($_src);
		// 0:ua 1:host 2:user
		$guess[$data[0]][$data[1]][$data[2]] = '';
	}

	$host = log::ip2host();
	if (!isset($guess[$log_ua][$host])) return '';

	$uname = '';
	foreach ($guess[$log_ua][$host] as $user => $val) {
		$uname .= (!empty($uname)) ? ','.$user : $user;
	}
	return sprintf($_log_whois_msg['msg_whois'],$uname);

}

?>
