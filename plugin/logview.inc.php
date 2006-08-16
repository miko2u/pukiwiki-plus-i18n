<?php
/**
 * PukiWiki Plus! ログ閲覧プラグイン
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: logview.php,v 0.8 2006/08/16 21:21:00 upk Exp $
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

	$body = <<<EOD
<table class="style_table" cellspacing="1" border="0">
<thead>
<tr>

EOD;
	// タイトルの処理
	foreach ($view as $_view) { 
		$body .= '<td class="style_td">'.$_logview_msg[$_view].'</td>'."\n";
	}

	$body .= <<<EOD
</tr>
</thead>
<tbody>

EOD;

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

		$body .= "<tr>\n";

		foreach ($view as $field) {
			switch ($field) {
			case 'ts': // タイムスタンプ (UTIME)
				$body .= ' <td class="style_td">' .
					get_date('Y-m-d H:i:s', $data['ts']) .
					' '.get_passage($data['ts']) . "</td>\n";
				break;
			case '@diff': // 差分内容
				// FIXME: バックアップ/差分 なしの新規の場合
				// バックアップデータの確定
				$body .= ' <td class="style_td">';
				$age = log::get_backup_age($page,$data['ts']);
				switch($age) {
				case -1: // データなし
					$body .= '<a class="ext" href="'.$script.'?'.rawurlencode($page).
						'" rel="nofollow">none</a>';
					break;
				case 0:  // diff
					if (log::diff_exist($page)) {
						$body .= '<a class="ext" href="'.$script.'?cmd=diff&amp;page='.rawurlencode($page).
							'" rel="nofollow">now</a>';
					}
					break;
				default: // あり
					$body .= '<a class="ext" href="'.$script.'?cmd=backup&amp;page='.rawurlencode($page).'&amp;age='.$age.'&amp;action=diff"'.
						' rel="nofollow">'.$age.'</a>';
					break;
				}
				$body .= "</td>\n";
				break;

			case 'host': // ホスト名 (FQDN)
				$body .= ' <td class="style_td">';
				if ($data['ip'] != $data['host']) {
					// 国名取得
					list($flag_icon,$flag_name) = $obj_ua->get_icon_flag($data['host']);
					if (!empty($flag_icon) && $flag_icon != 'jp') {
						$body .= '<img src="'.$path_flag.$flag_icon.'.png"'.
							' alt="'.$flag_name.'" title="'.$flag_name.'" />';
					}
					// ドメイン取得
					$domain = $obj_ua->get_icon_domain($data['host']);
					if (!empty($domain)) {
						$body .= '<img src="'.$path_domain.$domain.'.png"'.
                                                        ' alt="'.$data['host'].'" title="'.$data['host'].'" />';
					}
				}
				$body .= $data['host']."</td>\n";
				break;

			case '@guess': // 推測
				$body .= ' <td class="style_td">'.htmlspecialchars(logview_guess_user($data, $guess), ENT_QUOTES)."</td>\n";
				break;

			case 'ua': // ブラウザ情報 (USER-AGENT)
				$body .= ' <td class="style_td">';
				$os = $obj_ua->get_icon_os($data['ua']);
				if (!empty($os)) {
					$body .= '<img src="'.$path_os.$os.'.png"'.
						' alt="'.$os.'" title="'.$os.'" />';
				}
				$browser = $obj_ua->get_icon_broeswes($data['ua']);
				if (!empty($browser)) {
					$body .= '<img src="'.$path_browser.$browser.'.png"'.
						' alt="'.htmlspecialchars($data['ua'], ENT_QUOTES).
						'" title="'.htmlspecialchars($data['ua'], ENT_QUOTES).
						'" />';
				}
				$body .= "</td>\n";
				break;
			default:
				$body .= ' <td class="style_td">'.htmlspecialchars($data[$field], ENT_QUOTES)."</td>\n";
			}
		}

		$body .= "</tr>\n";
		$ctr++;
	}

	unset($obj_ua);

	if ($ctr == 0) {
		return array(
			'msg'  => $title,
			'body' => 'no data',
		);
	}

	$body .= <<<EOD
</tbody>
</table>

EOD;

	return array(
		'msg'  => $title,
		'body' => $body,
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
