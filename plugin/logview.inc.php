<?php
/**
 * PukiWiki Plus! ログ閲覧プラグイン
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: logview.php,v 0.7 2006/06/26 2:22:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

if (!defined('MAX_LINE')) {
	define('MAX_LINE', 200);
}
if (!defined('VIEW_ROBOTS')) {
	define('VIEW_ROBOTS', '0'); // robots は表示しない
}
if (!defined('USE_UA_OPTION')) {
	define('USE_UA_OPTION', '0'); // オプション
}

/**
 * 初期処理
 */
function plugin_logview_init()
{
	$messages = array(
	'_logview_msg' => array(
		'msg_title'	=> _('LogView (%s)'),
		'msg_not_auth'	=> _('Login is required in order to refer to.'),
		'ts'		=> _('Date'),
		'ip'		=> _('IP Address'),
		'host'		=> _('Host Name'),
		'user'		=> _('User Name'),
		'ntlm'		=> _('NTLM Auth Name'),
		'proxy'		=> _('Proxy Infomation'),
		'ua'		=> _('Browse Infomation'),
		'del'		=> _('Delete'),
		'sig'		=> _('Signature'),
		'file'		=> _('Faile Name'),
		'page'		=> _('Page'),
		'cmd'		=> _('CMD'),
		'@diff'		=> _('Contents'),
		'@guess'	=> _('Provisional User Name'),	// Guess
		)
	);
	set_plugin_messages($messages);
}

/**
 * アクションプラグイン処理
 */
function plugin_logview_action()
{
	global $script, $vars, $_logview_msg;
	global $log;

	$kind = (isset($vars['kind'])) ? $vars['kind'] : 'update';
	$title = sprintf($_logview_msg['msg_title'],$kind); // タイトルを設定
	$page = (isset($vars['page'])) ? $vars['page'] : '';

	// ゲスト表示ができない場合は、認証を要求する
	if ($log[$kind]['guest'] == '') {
		$obj = new auth();
		$user = $obj->check_auth();
		if (empty($user)) {
			if (exist_plugin('login')) {
				do_plugin_action('login');
			}
			unset($obj);
			return array(
				'msg'  => $title,
				'body' => $_logview_msg['msg_not_auth'],
			);
		}
	}
	unset($obj);

	// 保存データの項目名を取得
	$name = log::get_log_field($kind);
	$view = log::get_view_field($kind); // 表示したい項目設定
	$body = '';

	// タイトルの処理
	foreach ($view as $_view) { $body .= '|'.$_logview_msg[$_view]; }
	$body .= "|h\n";

	// データを取得
	$fld = logview_get_data(log::set_filename($kind,$page), $name);

	if (empty($fld)) {
		return array(
			'msg'  => $title,
			'body' => 'no data',
		);
	}

	// USER-AGENT クラス
	$obj_ua = new user_agent(USE_UA_OPTION);

	$path_flag    = IMAGE_DIR .'icon/flags/';
	$path_browser = IMAGE_DIR .'icon/browser/';
	$path_os      = IMAGE_DIR .'icon/os/';
	$path_domain  = IMAGE_DIR .'icon/option/domain/';

	$guess = ($log['guess_user']['use']) ? log::read_guess() : log::summary_signature();

	$ctr = 0;
	// データの編集
	foreach($fld as $data) {
		if (!VIEW_ROBOTS && $obj_ua->is_robots($data['ua'])) continue;	// ロボットは対象外
		foreach ($view as $field) {

			$body .= '|';

			switch ($field) {
			case 'ts': // タイムスタンプ (UTIME)
				$body .= get_date('Y-m-d H:i:s', $data['ts']);
				$body .= ' '.get_passage($data['ts']);
				break;
			case '@diff': // 差分内容
				// FIXME: バックアップ/差分 なしの新規の場合
				// バックアップデータの確定
				$age = log::get_backup_age($page,$data['ts']);
				switch($age) {
				case -1: // データなし
					$body .= '[[none>'.$page.']]';
					break;
				case 0:  // diff
					if (log::diff_exist($page)) $body .= '['.$script.'?cmd=diff&page='.rawurlencode($page).' now]';
					break;
				default: // あり
					$body .= '['.$script.'?cmd=backup&page='.rawurlencode($page).'&age='.$age.'&action=diff '.$age.']';
					break;
				}
				break;

			case 'host': // ホスト名 (FQDN)
				if ($data['ip'] != $data['host']) {
					// 国名取得
					list($flag_icon,$flag_name) = $obj_ua->get_icon_flag($data['host']);
					if (!empty($flag_icon) && $flag_icon != 'jp') $body .= '&img3('.$path_flag.$flag_icon.'.png,'.$flag_name.');';
					// ドメイン取得
					$domain = $obj_ua->get_icon_domain($data['host']);
					if (!empty($domain)) $body .= '&img3('.$path_domain.$domain.'.png,'.$data['host'].');';
				}
				$body .= $data['host'];
				break;

			case '@guess': // 推測
				$body .= htmlspecialchars(logview_guess_user($data, $guess), ENT_QUOTES);
				break;

			case 'ua': // ブラウザ情報 (USER-AGENT)
				$os = $obj_ua->get_icon_os($data['ua']);
				if (!empty($os))      $body .= '&img3('.$path_os.$os.'.png){'.$os.'};';
				$browser = $obj_ua->get_icon_broeswes($data['ua']);
				if (!empty($browser))
					$body .= '&img3('.$path_browser.$browser.'.png){'.htmlspecialchars($data['ua'], ENT_QUOTES).'};';
				break;
			default:
				$body .= htmlspecialchars($data[$field], ENT_QUOTES);
			}
		}
		$body .= "|\n";
		$ctr++;
	}

	unset($obj_ua);

	if ($ctr == 0) {
		return array(
			'msg'  => $title,
			'body' => 'no data',
		);
	}

	return array(
		'msg'  => $title,
		'body' => convert_html($body),
	);
}

function logview_get_data($filename,$name)
{
	if (! file_exists($filename)) {
		return array();
	}

	$rc = array();
	$fp = @fopen($filename, 'r');
	if ($fp == FALSE) return $rc;
	@flock($fp, LOCK_SH);

	$count = 0;
	while (! feof($fp)) {
		$line = fgets($fp, 512);
		if ($line === FALSE) continue;
		$rc[] = log::line2field($line,$name);
                ++$count;
		if ($count > MAX_LINE) {
			// 古いデータを捨てる
			array_shift($rc);
		}
	}

	@flock($fp, LOCK_UN);
	if(! fclose($fp)) return array();
	rsort($rc); // 逆順にソート(最新順になる)
	return $rc;
}

/**
 * ユーザ名推測
 */
function logview_guess_user($data,$guess)
{
	// 確定的な情報
	$user  = (isset($data['user'])) ? $data['user'] : '';
	$ntml  = (isset($data['ntml'])) ? $data['ntml'] : '';
	$sig   = (isset($data['sig']))  ? $data['sig']  : '';
	$now_user = log::guess_user($user,$ntlm,$sig);
	if (!empty($now_user)) return $now_user;

	// 見做し
	if (!isset($data['ua']))   return '';
	if (!isset($guess[$data['ua']])) return ''; // USER-AGENT が一致したデータがあるか
	if (!isset($data['host'])) return '';

	$user = '';
	$level = 0; // とりあえずホスト名は完全一致

	foreach($guess[$data['ua']] as $_host => $val1) {
		list($sw,$lvl) = log::check_host($data['host'],$_host,$level); // ホスト名の一致確認
		if (!$sw) continue; // ホスト名が一致しない

		// UA が等しく、同じIPなものの、複数ユーザまたは改変した場合は、複数人分出力
		foreach($val1 as $_user => $val2) {
			if (!empty($user)) $user .= ' / ';
			$user .= $_user;
		}
	}
	return $user;
}

?>
