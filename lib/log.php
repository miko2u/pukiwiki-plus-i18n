<?php
/**
 * PukiWiki Plus! 更新ログ処理
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: log.php,v 0.7 2006/08/19 00:00:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

// require_once('proxy.cls.php');

$log_ua = log_set_user_agent(); // 保存

/**
 * ログの存在チェック
 */
function log_exist($kind,$page)
{
	global $log;

	if (!$log[$kind]['use']) return 0;
	$filename = log::set_filename($kind,$page);
	if (!file_exists($filename)) return 0;
	if (!is_readable($filename)) return 0;
	return 1;
}

/**
 * ログ件数
 */
function log_count($kind,$page)
{
	global $log;

	if (! log_exist($kind,$page)) return 0;

	$filename = log::set_filename($kind,$page);
        if (!($fd = fopen($filename,'r'))) return 0;

        $ctr = 0;
        while ($data = @fgets($fd, 4096)) {
		$x = trim($data);
		if (log::is_table($x)) $ctr++;
	}
        fclose($fd);
        return $ctr;
}

/**
 * ログ出力(browse,update)
 */
function log_write($kind,$page)
{
	global $log;

	if (!$log[$kind]['use']) return '';

	$rc = log_common_check($kind,$page,'');
	if (empty($rc)) return '';
	$filename = log::set_filename($kind,$page); // ログファイル名
	$data = log::array2table( $rc );
 	log_put( $filename, $data);

	// 見做しユーザ
	if ($kind == 'update' && $log['guess_user']['use']) {
		log_put_guess($rc);
	}

}

/**
 * ダウンロードログ
 */
function log_put_download($page,$file)
{
	global $log;

	if (!$log['download']['use']) return '';

	$rc = log_common_check('download',$page,$file);
	if (empty($rc)) return '';
	$filename = log::set_filename('download',$page); // ログファイル名

	$data = log::array2table( $rc );
 	log_put( $filename, $data);
}

/*
 * 推測ユーザデータの出力
 */
function log_put_guess($data)
{
	// ユーザを推測する
	$user = log::guess_user( $data['user'], $data['ntlm'], $data['sig'] );
	if (empty($user)) return;

	$filename = log::set_filename('guess_user','');	// ログファイル名

	if (file_exists($filename)) {
		$src = file( $filename );			// ログの読み込み
	} else {
		// 最初の１件目
		$data = log::array2table( array( $data['ua'], $data['host'], $user,"" ) );
	 	log_put( $filename, $data);
		return;
	}

	$sw = FALSE;

	foreach($src as $_src) {
		$x = trim($_src);
		$field = log::table2array($x);		// PukiWiki 表形式データを配列データに変換
		if (count($field) == 0) continue;
		if ($field[0] != $data['ua']  ) continue;
		if ($field[1] != $data['host']) continue;
		if ($field[2] != $user        ) continue;
		$sw = TRUE;
		break;
	}
	if ($sw) return; // 既に存在
	// データの更新
	$data = log::array2table( array( $data['ua'], $data['host'], $user,'' ) );
 	log_put( $filename, $data);
}

/**
 * 共通チェック
 */
function log_common_check($kind,$page,$parm)
{
	global $log;
	global $log_ua;

	$username = auth::check_auth();

	// 認証済の場合
	if ($log['auth_nolog'] && !empty($username)) return '';

	$utime     = UTIME;
	$obj_log   = new log();
	$obj_proxy = new check_proxy();

	$ip         = $obj_log->getip();
	$hostname   = $obj_log->ip2host($ip);
	$proxy_info = ($obj_proxy->is_proxy()) ? $obj_proxy->get_proxy_info().'('.$obj_proxy->get_realip().')' : '';

	unset($obj_log);
	unset($obj_proxy);

	// ロギング対象外IP
	foreach ($log[$kind]['nolog_ip'] as $nolog_ip) {
		if ($ip == $nolog_ip) return '';
	}

	// NetBIOS でのチェックを実施
	if (netbios_scope_check($ip,$hostname)) {
                $obj_nbt = new netbios($ip);
		$ntlm_user = $obj_nbt->username;
		unset($obj_nbt);
	} else {
		$ntlm_user = '';
	}

	// 更新時は、削除されたか？
	if ($kind == 'update') {
		$delete_flag = (file_exists(get_filename($page))) ? '' : 'DELETE';
	} else {
		$delete_flag = '';
	}

	// 署名の収集
	$signature = log_set_signature($kind,$page,$utime);

	$rc = array();
	$field = log::set_fieldname($kind);

	foreach ($field as $key) {

		switch ($key) {

		case 'ts': // タイムスタンプ (UTIME)
			$rc[$key] = $utime;
			break;
		case 'ip': // IPアドレス
			$rc[$key] = $ip;
			break;
		case 'host': // ホスト名 (FQDN)
			$rc[$key] = $hostname;
			break;
		case 'user': // ユーザ名(認証済)
			$rc[$key] = $username;
			break;
		case 'ntlm': // ユーザ名(NTLM認証)
			$rc[$key] = $ntlm_user;
			break;
		case 'proxy': // Proxy情報
			$rc[$key] = $proxy_info;
			break;
		case 'ua': // ブラウザ情報
			$rc[$key] = $log_ua;
			break;
		case 'del': // 削除フラグ
			$rc[$key] = $delete_flag;
			break;
		case 'sig': // 署名(曖昧)
			$rc[$key] = $signature;
			break;
		case 'file': // ファイル名
			$rc[$key] = $parm;
			break;
		case 'page':
		case 'cmd':
			$rc[$key] = $page;
			break;
		}
	}

	return $rc;

}

/**
 * NetBIOS の適用範囲決定
 */
function netbios_scope_check($ip,$host)
{
	global $log;
	static $ip_pattern = '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?:\/(.+))?$/';

	if (!$log['auth_netbios']['use']) return FALSE;

	$l_ip = ip2long($ip);
	$valid = (is_long($l_ip) and long2ip($l_ip) == $ip); // valid ip address

	$matches = array();
	foreach ($log['auth_netbios']['scope'] as $network)
	{
		if ($valid and preg_match($ip_pattern,$network,$matches))
		{
			$l_net = ip2long($matches[1]);
			$mask = array_key_exists(2,$matches) ? $matches[2] : 32;
			$mask = is_numeric($mask) ?
				pow(2,32) - pow(2,32 - $mask) : // "10.0.0.0/8"
				ip2long($mask);                 // "10.0.0.0/255.0.0.0"
			if (($l_ip & $mask) == $l_net) return TRUE;
		} else {
			if (preg_match('/'.preg_quote($network,'/').'/',$host)) return FALSE;
                }
        }
        return FALSE;
}

/**
 * HTTP_USER_AGENT の保存 (init.php で unsetしていて $user_agent は使えない)
 */
function log_set_user_agent()
{
	if (isset($_SERVER['HTTP_USER_AGENT'])) return $_SERVER['HTTP_USER_AGENT'];
	if (isset($_SERVER['ALL_HTTP'])) {
		return (preg_match('/^HTTP_USER_AGENT:(.+)$/m',$_SERVER['ALL_HTTP'],$regs)) ? $regs[1] : '';
	}
	return '';
}

/**
 * 署名の特定
 */
function log_set_signature($kind,$page,$utime)
{
	// $utime は、今後、閲覧者の特定などの際にバックアップファイルから
	// 特定することを想定し、含めている。

	if ($kind != 'update') return '';

	$diff = log::diff_filename($page);

	$lines = array();

	if (file_exists($diff)) {
		$src = str_replace("\r", '', file($diff));
		// 今回更新行のみ抽出
		foreach($src as $_src) {
			if (substr($_src,0,1) == '+') $lines[] = substr($_src,1);
		}
	} else {
		// 新規ページの全てが対象
		$lines = get_source($page);
	}

	return auth::get_signature($lines);
}

/**
 * ファイル出力
 */
function log_put($filename,$data)
{
	// 排他制御が利かない FS に対応し @ で逃げる
	$fp = fopen($filename, 'a');
	@flock( $fp, LOCK_EX);
	fputs($fp, $data);
	@flock( $fp, LOCK_UN);
	@fclose( $fp );
}


/**
 * ログ全般の処理を取り纏めたもの
 * @abstract
 */
class log
{
	/**
	 * ページのファイル名を得る
	 * @static
	 */
	function get_filename($subdir,$page,$ext='.txt')
	{
		return LOG_DIR . $subdir . encode($page) . $ext;
	}

	/**
	 * ログファイル名を設定
	 * @static
	 */
	function set_filename($kind,$page)
	{
		global $log;

		// ファイル名必須処理
		switch ($kind) {
		case 'cmd':
		case 'guess_user':
			if (empty($log[$kind]['file'])) $log[$kind]['file'] = ':log/'.$kind;
			break;
		}

		return (isset($log[$kind]['file'])) ? get_filename($log[$kind]['file']) : log::get_filename($kind.'/',$page);
	}

	/**
	 * 設定項目名を設定
	 * @static
	 */
	function set_fieldname($kind)
	{
		global $log;

		$kind_no = array(
			// default      => 0,
			'update'	=> 1,
			'download'	=> 2,
			'cmd'		=> 3,
		);
		$idx = (isset($kind_no[$kind])) ? $kind_no[$kind] : 0;

		// 先頭 @ の項目は、ログには保存されていない項目(表示用)
		$field = array(
			// 定義順は、デフォルト(all)表示順
			//		 略,更,DL,cmd
			'ts'	=> array( 1, 1, 1, 1), // タイムスタンプ (UTIME)
			'@diff'	=> array( 0, 1, 0, 0), // 差分内容
			'ip'	=> array( 1, 1, 1, 1), // IPアドレス
			'host'	=> array( 1, 1, 1, 1), // ホスト名 (FQDN)
			'@guess'=> array( 1, 1, 1, 0), // 推測
			'user'	=> array( 1, 1, 1, 1), // ユーザ名(認証済)
			'ntlm'	=> array( 1, 1, 1, 1), // ユーザ名(NTLM認証)
			'proxy'	=> array( 1, 1, 1, 1), // Proxy情報
			'ua'	=> array( 1, 1, 1, 1), // ブラウザ情報 (USER-AGENT)
			'del'	=> array( 0, 1, 0, 0), // 削除フラグ
			'sig'	=> array( 0, 1, 0, 0), // 署名(曖昧)
			'file'	=> array( 0, 0, 1, 0), // ファイル名
			'cmd'	=> array( 0, 0, 0, 1), // コマンド名
			'page'	=> array( 1, 1, 1, 0), // ページ名
		);

		$rc = array();
		foreach($field as $_field => $sw) {
			if ($sw[$idx] == 0) continue;
			if ($_field == 'page' && !isset($log[$kind]['file'])) continue;
			$rc[] = $_field;
		}

		return $rc;
	}

	/**
	 * ログの表示指示項目の設定
	 * @static
	 */
	function get_view_field($kind)
	{
		global $log;

		$rc = log::set_fieldname($kind);

		// 認証済の判定
		$user = auth::check_auth();

		$kind_view = (empty($user)) ? 'guest' : 'view';

		if ($log[$kind][$kind_view] == 'all') return $rc;

		$tmp = explode(':', $log[$kind][$kind_view]);

		// 妥当性チェック
		$chk = array();
		foreach($tmp as $_tmp) {
			$sw = 0;
			foreach($rc as $_name) {
				if ($_name == $_tmp) {
					$sw = 1;
					break;
				}
			}
			if (!$sw) continue;
			$chk[] = $_tmp;
		}
		unset($tmp, $sw);
		return $chk;
	}

	/**
	 * ログに書き出している項目のみ抽出する
	 * @static
	 */
	function get_log_field($kind)
	{
		// 全項目名を取得
		$all = log::set_fieldname($kind);
		$rc = array();
		foreach ($all as $field) {
			if (substr($field,0,1) == '@') continue; // 表示項目は除去
			$rc[] = $field;
		}
		return $rc;
	}

	/**
	 * IPアドレスから逆引きする
	 * @static
	 */
	function ip2host($ip)
	{
		if ($ip == NULL) $ip = log::getip();
		$longisp = gethostbyaddr($ip);
		return $longisp;
	}

	/**
	 * IPアドレスの取得
	 * @static
	 */
	function getip()	{ return $_SERVER['REMOTE_ADDR']; }
	/**
	 * ホスト名からIPアドレス得る
	 * @static
	 */
	function host2ip($host) { return gethostbyname($host); }

	/**
	 * 配列データを PukiWiki 表形式データに変換
	 * @static
	 */
	function array2table($data)
	{
		$rc = '';
		foreach ($data as $x1) {
			$rc .= '|'.$x1;
		}
		$rc .= "|\n";
		return $rc;
	}

	/**
	 * PukiWiki 表形式データかの判定
	 * @static
	 */
	function is_table($line)
	{
		$x = trim($line);
		if (substr($x,0,1) != '|') return FALSE;
		if (substr($x,-1)  != '|') return FALSE;
		return TRUE;
	}

	/**
	 * PukiWiki 表形式データを配列データに変換
	 * @static
	 */
	function table2array($x)
	{
		if (!log::is_table($x)) return array();
		return explode('|', substr($x,1,-1));
	}

	/**
	 * ログの１行を配列に変換した後、項目名を付与する
	 * @static
	 */
	function line2field($line,$name)
	{
		$_fld = log::table2array($line);
		$i = 0;
		$rc = array();
		foreach($name as $_name) {
			$rc[$_name] = $_fld[$i];
			$i++;
		}
		return $rc;
	}

	/**
	 * 更新日時のバックアップデータの世代を確定する
	 * @static
	 */
	function get_backup_age($page,$update_time)
	{
		static $backup_page;

		if (!isset($backup_page)) $backup_page = get_backup($page);
		if (count($backup_page) == 0) return -1; // 存在しない

		// 初回バックアップ作成は、文書生成日時となる
		$create_date = $backup_page[1]['time'];
		if ($update_time == $create_date) return 1;

		$match = -1;
		foreach ($backup_page as $age => $val)
		{
			if ($val['real'] == $update_time) $match = $age;
		}
		$match++; // ヒットした次が書き込んだ内容(バックアップなため)
		if ($age < $match) return 0; // カレント(diffを読む)
		if ($match > 0) return $match;
		return -1; // 存在しない(一致したものが存在しない)
	}

	/**
	 * 差分ファイル名
	 * @static
	 */
	function diff_filename($page)
	{
		return DIFF_DIR . encode($page) . '.txt'; // 差分ファイル名
	}

	/**
	 * 差分ファイルの存在確認
	 * @static
	 */
	function diff_exist($page)
	{
		return file_exists( log::diff_filename($page) );
	}

	/**
	 * ログ種別毎によるファイルの集約
	 * @static
	 */
	function log_summary($kind)
	{
		global $log;
		if (!$log[$kind]['use']) return array();

		// 単ファイルの場合
		if (isset($log['update']['file'])) {
			return array(get_source(get_filename($log[$kind]['file'])) );
		}

		// ページ毎の場合
		$pages = get_existpages(LOG_DIR.'update/');
		$sum = array();
		foreach($pages as $file => $_page) {
			$sum[] = str_replace("\r", '', file(LOG_DIR.'update/'.$file));
		}
		unset($pages);
		return $sum;
	}

	/**
	 * 更新ログから署名情報の収集
	 * @return	array
	 * $rc[ USER-AGENT ][ ホスト名 ][ ユーザ名 ] の配列を戻す
	 * @static
	 */
	function summary_signature()
	{
		global $log;
		if (!$log['update']['use']) return '';

		$sum = array();
		$data = log::log_summary('update');
		$name = log::get_log_field('update');

		foreach ($data as $_data) {
			foreach($_data as $line) {
				$field = log::line2field($line,$name);
				if (empty($field['ua'])) continue;
				$user = log::guess_user($field['user'],$field['ntlm'],$field['sig']);
				if (empty($user)) continue;
				$sum[$field['ua']][$field['host']][$user] = '';
			}
		}
		return $sum;
	}

	/**
	 * 推測ユーザデータから署名情報の収集
	 * @return	array
	 * $rc[ USER-AGENT ][ ホスト名 ][ ユーザ名 ] の配列を戻す
	 * @static
	 */
	function read_guess()
	{
		global $log;
		if (!$log['guess_user']['use']) return '';
		$filename = log::set_filename('guess_user','');	// ログファイル名
		$src = @file( $filename );

		$sum = array();
		foreach($src as $_src) {
			$x = trim($_src);
			$field = log::table2array($x);		// PukiWiki 表形式データを配列データに変換
			if (count($field) == 0) continue;
			$user = (empty($field[3])) ? $field[2] : $field[3]; // 任意欄が記入されていれば、それを採用
			$sum[$field[0]][$field[1]][$user] = '';
		}
		return $sum;
	}

	/**
	 * ユーザを推測する
	 * @static
	 */
	function guess_user($user,$ntlm,$sig)
	{
		if (!empty($user)) return $user; // 署名ユーザ
		if (!empty($ntlm)) return $ntlm; // NTLM認証ユーザ
		if (!empty($sig))  return $sig;  // 本人の署名
		return '';
	}

	/**
	 * ホスト名のチェック (a と b のチェック)
	 * $level で指定された階層までで比較する
	 * @static
	 */
	function check_host($a,$b,$level)
	{
		$tbl_a = array_reverse( explode('.',$a) );
		$ctr_a = count($tbl_a);
		$tbl_b = array_reverse( explode('.',$b) );
		$ctr_b = count($tbl_b);

		$max   = max($ctr_a, $ctr_b);
		$loop  = min($ctr_a, $ctr_b);

		$sw    = TRUE;
		for ($i=0; $i<$loop; $i++) {
			if ($tbl_a[$i] != $tbl_b[$i]) {
				$sw = FALSE;
				break;
			}
		}

		if ($i != $max) $sw = FALSE; // 打ち切り対応
		if ($sw) return array(TRUE,$i);
		if ($level == 0) return array(FALSE,$i); // 完全一致
		if ($level > $max) return array(TRUE,$i);
		// 指定レベルよりも一致している場合は真
		return ($i >= $level) ? array(TRUE,$i) : array(FALSE,$i);
	}

}

?>
