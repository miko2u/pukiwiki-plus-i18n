<?php
/**
 * PukiWiki Plus! ログ閲覧プラグイン
 *
 * @copyright	Copyright &copy; 2004-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: logview.php,v 0.15 2008/06/29 17:05:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

defined('MAX_LINE')      or define('MAX_LINE', 200);
defined('VIEW_ROBOTS')   or define('VIEW_ROBOTS', '0');   // robots は表示しない
defined('USE_UA_OPTION') or define('USE_UA_OPTION', '0'); // オプション
defined('PLUGIN_LOGVIEW_COLOR_AUTH_API') or define('PLUGIN_LOGVIEW_COLOR_AUTH_API','#009900');

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
		'auth_api'	=> _('Authentication API Name'),
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
		'@guess_diff'	=> _('Provisional Browse Contents'),  // Guess
		'info_unused'   => _('Unused user list'),
		'all_user'	=> _('Number of enrollees'),
		'number_unused' => _('Number of Unused'),
		'availability'  => _('Availability'),
		)
	);
	set_plugin_messages($messages);
}

/**
 * アクションプラグイン処理
 */
function plugin_logview_action()
{
	global $vars, $_logview_msg;
	global $log, $sortable_tracker;
	static $count = 0;

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

	if ($sortable_tracker && $count == 0) {
		global $head_tags;
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'sortabletable.js"></script>';
	}

	$count++;
	$body = <<<EOD
<table id="logview$count" class="style_table" cellspacing="1" border="0">
<thead>
<tr>

EOD;
	$cols = 0;
	// タイトルの処理
	foreach ($view as $_view) { 
		$body .= '<td class="style_td">'.$_logview_msg[$_view].'</td>'."\n";
		$cols++;
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

	$path_flag    = IMAGE_URI .'icon/flags/';
	$path_browser = IMAGE_URI .'icon/browser/';
	$path_os      = IMAGE_URI .'icon/os/';
	$path_domain  = IMAGE_URI .'icon/option/domain/';

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

			case '@guess_diff':
			case '@diff': // 差分内容
				$update = ($field == '@diff') ? true : false;
				// FIXME: バックアップ/差分 なしの新規の場合
				// バックアップデータの確定
				$body .= ' <td class="style_td">';
				$age = log::get_backup_age($page,$data['ts'],$update);
				switch($age) {
				case -1: // データなし
					$body .= '<a class="ext" href="'.get_page_uri($page).
						'" rel="nofollow">none</a>';
					break;
				case 0:  // diff
					$body .= '<a class="ext" href="';
					$body .= (log::diff_exist($page)) ? get_cmd_uri('diff',$page) : get_page_uri($page);
					$body .= '" rel="nofollow">now</a>';
					break;
				default: // あり
					$body .= '<a class="ext" href="'.get_cmd_uri('backup',$page,'',array('age'=>$age,'action'=>'visualdiff')).'"'.
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

	switch ($kind) {
	case 'login':
	case 'check':
		$body .= logview_user_list($fld);
		break;
	}

	if ($sortable_tracker) {
		$logviewso = join(',', array_fill(0, $cols, '"String"'));
		$body .= <<<EOD
<script type="text/javascript">
<!-- <![CDATA[
var st = new SortableTable(document.getElementById('logview{$count}'),[{$logviewso}]);
//]]>-->
</script>
EOD;
	}

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
	$ntlm  = (isset($data['ntlm'])) ? $data['ntlm'] : '';
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

function logview_user_list(& $fld)
{
	global $_logview_msg;

	$all_user = auth::user_list();
	$all_user_idx = 0;
	foreach ($all_user as $auth_api=>$val1) {
	foreach ($val1 as $user=>$val) {
		$all_user_idx++;
	}}

	$user_list = array();
	foreach($fld as $line) {
		$user_list[$line['auth_api']][$line['user']] = '';
	}

	$check_list = array();
	foreach($all_user as $auth_api=>$val1) {
	foreach($val1 as $id=>$val) {
		if (isset($user_list[$auth_api][$val['displayname']])) continue;
		$check_list[] = array('name'=>$val['displayname'],'auth_api'=>$auth_api);
	}}

	$ctr = count($check_list);
	if ($ctr == 0) return '';

	$ret = '<h4>'.$_logview_msg['info_unused'].'</h4>'."\n"; // 未確認者一覧
	$ret .= '<div><fieldset>'.$_logview_msg['all_user'].': '.$all_user_idx.' '.
			$_logview_msg['number_unused'].': '.$ctr.' '.
			$_logview_msg['availability'].': '.floor(($ctr/$all_user_idx)*100).'%</fieldset></div><div>&nbsp;</div>'."\n"; // 人数

	sort($check_list);
	$ctr = 0;
	foreach($check_list as $user) {
		$ctr++;
		// $ret .= '('.$ctr.') ['.$user['auth_api'].']'.$user['name']."\n";
		$ret .= '('.$ctr.') '.$user['name'].' <small><span style="color: '.PLUGIN_LOGVIEW_COLOR_AUTH_API.'">'.$user['auth_api']."</span></small>\n";
	}

	return $ret;
}

?>
