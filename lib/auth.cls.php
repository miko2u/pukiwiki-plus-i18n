<?php
/**
 * PukiWiki Plus! 認証処理
 *
 * @author	Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth.cls.php,v 0.20 2006/08/18 22:32:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * 認証クラス
 * @abstract
 */
class auth
{
	/*
	 *	== IIS ==
	 *	AUTH_USER		- 認証ユーザ名
	 *	AUTH_TYPE		- 認証タイプ
	 *	HTTP_AUTHORIZATION	- パスワードのダイジェスト
	 *	LOGON_USER		- サーバへのログオンユーザ名
	*/

	/*
	 * 認証者名を取得
	 * @static
	 */
	function check_auth()
	{
		foreach (array('PHP_AUTH_USER', 'AUTH_USER', 'REMOTE_USER', 'LOGON_USER') as $x) {
			if (isset($_SERVER[$x]) && ! empty($_SERVER[$x])) {
				if (! empty($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'Digest') return $_SERVER[$x];
				$ms = explode('\\', $_SERVER[$x]);
				if (count($ms) == 3) return $ms[2]; // DOMAIN\\USERID
				foreach (array('PHP_AUTH_PW', 'AUTH_PASSWORD', 'HTTP_AUTHORIZATION') as $pw) {
					if (! empty($_SERVER[$pw])) return $_SERVER[$x];
				}
			}
		}

		// PHP Digest認証対応
		if (($data = auth::http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) {
			if (! empty($data['username'])) return $data['username'];
		}

		// NTLM対応
		list($domain, $login, $host, $pass) = auth::ntlm_decode();
		return $login;
	}

	/**
	 * ユーザのROLEを取得
	 * @static
	 */
	function get_role_level()
	{
		global $realm, $auth_type, $auth_users, $adminpass;

		$login = auth::check_auth();
		if (empty($login)) return 0; // 未認証者

		// 管理者パスワードなのかどうか？
		$temp_admin = ( pkwk_hash_compute($_SERVER['PHP_AUTH_PW'], $adminpass) !== $adminpass) ? FALSE : TRUE;

		if (! isset($auth_users[$login])) {
			// 未登録者の場合
			// 管理者パスワードと偶然一致した場合でも見做し認証者(4.1)
			return ($login == 'admin' && $temp_admin) ? 3.1 : 4.1;
		}

		// 設定されている役割を取得
		$role = (empty($auth_users[$login][1])) ? 4 : $auth_users[$login][1];
		switch ($role) {
		case 2: // サイト管理者
		case 3: // コンテンツ管理者
			// パスワードまで一致していること
			if ($auth_type == 2) {
				return (auth::auth_digest($realm,$auth_users)) ? $role : 4;
			} else {
				return (auth::auth_pw($auth_users)) ? $role : 4;
			}
		case 4: // 認証者(pukiwiki)
			return ($temp_admin) ? 3.1 : 4;
		}
		return 4;
	}

	/*
	 * 指定されるROLEに属するユーザを列挙
	 * @static
	 */
	function get_user_list($role)
	{
		global $auth_users;
		$rc = array();
		foreach($auth_users as $user=>$val)
		{
			$def_role = (empty($val[1])) ? 4 : $val[1];
			if ($def_role > $role) continue;
			$rc[] = $user;
		}

		$now_role = auth::get_role_level();
		if (($now_role == 4.1 && $role == 4) || ($now_role == 3.1 && $role == 3))
		{
			$rc[] = auth::check_auth();
		}

		return $rc;
	}

	/*
	 * ROLEに応じた挙動の確認
	 * @return bool
	 * @static
	 */
	function check_role($func='')
	{
		global $adminpass;

		switch($func) {
		case 'readonly':
			$chk_role = (defined('PKWK_READONLY')) ? PKWK_READONLY : 0;
			break;
		case 'safemode':
			$chk_role = (defined('PKWK_SAFE_MODE')) ? PKWK_SAFE_MODE : 0;
			break;
		case 'su':
			$now_role = auth::get_role_level();
			if ($now_role == 2 || (int)$now_role == 3) return FALSE; // 既に権限有
			$chk_role = 3;
			switch ($now_role) {
			case 4.1:
				// FIXME:
				return TRUE;
			case 0:
				// 未認証者は、単に管理者パスワードを要求
				$user = 'admin';
				break;
			case 4:
				// 認証済ユーザは、ユーザ名を維持しつつ管理者パスワードを要求
				$user = auth::check_auth();
				break;
			}
			$auth_temp = array($user => array($adminpass) );

			while(1) {
				if (!auth::auth_pw($auth_temp))
				{
					unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
					header( 'WWW-Authenticate: Basic realm="USER NAME is '.$user.'"' );
					header( 'HTTP/1.0 401 Unauthorized' );
					break;
				}
				// ESC : 認証失敗
				return TRUE;
			}
			break;
		case 'role_adm':
			$chk_role = 2;
			break;
		case 'role_adm_contents':
			$chk_role = 3;
			break;
		case 'role_auth':
			$chk_role = 4;
			break;
		default:
			$chk_role = 0;
		}

		if ($chk_role == 0) return FALSE;	// 機能無効
		if ($chk_role == 1) return TRUE;	// 強制

		// 役割に応じた挙動の設定
		$now_role = (int)auth::get_role_level();
		if ($now_role == 0) return TRUE;
		return ($now_role <= $chk_role) ? FALSE : TRUE;
	}

	/**
	 * NTLM, Negotiate 認証 (IIS 4.0/5.0)
	 * @static
	 */
	function auth_ntlm()
	{
		if($_SERVER['HTTP_AUTHORIZATION'] == NULL){
			header( "HTTP/1.0 401 Unauthorized" );
			header( "WWW-Authenticate: NTLM" );
			exit;
		};

		if(!isset($_SERVER['HTTP_AUTHORIZATION'])) return 0;

		list($auth_type,$digest64) = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
		switch( strtoupper($auth_type) ) {
		case 'NTLM':      // IIS 4.0
			return 1;
		case 'NEGOTIATE': // IIS 5.0 ('Negotiate')
			return 2;

		// IIS用 phpMyAdmin-2.6.2-pl1/libraries/auth/http.auth.lib.php
		case 'BASIC':     // 'Basic'
			if (!function_exists('base64_decode')) return array('','');
			return explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}

		$digest = 'NTL' . base64_decode( substr($digest64 ,4) );

		if (ord($digest{8})  != 1  ) return 0;
		if (ord($digest[13]) != 178) return 0;

		$strAuth = 'NTLMSSP'
			 . chr(0) . chr(2) . chr(0) . chr(0) . chr(0)
			 . chr(0) . chr(0) . chr(0) . chr(0) . chr(40)
			 . chr(0) . chr(0) . chr(0) . chr(1) . chr(130)
			 . chr(0) . chr(0) . chr(0) . chr(2) . chr(2)
			 . chr(2) . chr(0) . chr(0) . chr(0) . chr(0)
			 . chr(0) . chr(0) . chr(0) . chr(0) . chr(0)
			 . chr(0) . chr(0) . chr(0);
		
		$strAuth64 = base64_encode($strAuth);
		$strAuth64 = trim($strAuth64);
		header( 'HTTP/1.0 401 Unauthorized' );
		header( "WWW-Authenticate: NTLM $strAuth64" );
		exit;

		return 0;
	}

	/**
	 * HTTP_AUTHORIZATION の解読
	 * @static
	 */
	function ntlm_decode()
	{
		$rc = array('','','','');
		if (!function_exists('base64_decode')) return $rc;
		if (!isset($_SERVER['HTTP_AUTHORIZATION'])) return $rc;
		// if (substr($_SERVER['HTTP_AUTHORIZATION'],0,4) != 'MSSP') return $rc;

		list($auth_type,$x) = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

		switch( strtoupper($auth_type) ) {
		// IIS用 (http://homepage1.nifty.com/yito/namazu/gbook/20021127.1530.html)
		case 'BASIC':     // 'Basic'
			list($login, $pass) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			return array('',$login,'', $pass);
		case 'NTLM':      // IIS 4.0
			break;
		case 'NEGOTIATE': // IIS 5.0 ('Negotiate')
			break;
		default:
			return $rc;
		}

		$x = 'NTL' . base64_decode( substr($x,4) );

		if(ord($x{8}) != 3) return $rc;

		$rc = array();
		for ($i=30; $i<=46; $i += 8)
		{
									// domain login host
			$len    = (ord($x[$i+1])*256 + ord($x[$i  ]));	// 31,30  39,38 47,46
			$offset = (ord($x[$i+3])*256 + ord($x[$i+2]));	// 33,32  41,40 49,48
			$rc[] = substr($x, $offset, $len);
		}
		$rc[] = ''; // pass
		return $rc; // domain, login, hostname, pass
	}

	/**
	 * 認証 (PukiWikiの設定に準ずる)
	 * @static
	 */
	function auth_pw($auth_users)
	{
		$user = '';
		foreach (array('PHP_AUTH_USER', 'AUTH_USER') as $x) {
			if (isset($_SERVER[$x])) {
				$ms = explode('\\', $_SERVER[$x]);
				if (count($ms) == 3) {
					$user = $ms[2]; // DOMAIN\\USERID
				} else {
					$user = $_SERVER[$x];
				}
				break;
			}
		}

		$pass = '';
		foreach (array('PHP_AUTH_PW', 'AUTH_PASSWORD', 'HTTP_AUTHORIZATION') as $x) {
			if (! empty($_SERVER[$x])) {
				if ($x == 'HTTP_AUTHORIZATION') {
					// NTLM対応 (domain, login, host, pass)
					$tmp_ntlm = auth::ntlm_decode();
					if ($tmp_ntlm[3] == '') continue;
					if (empty($user)) $user = $tmp_ntlm[1];
					$pass = $tmp_ntlm[3];
					unset($tmp_ntml);
					break;
				}
				$pass = $_SERVER[$x];
				break;
			}
		}

		if (empty($user) && empty($pass)) return 0;
		if (empty($auth_users[$user][0])) return 0;
		if ( pkwk_hash_compute($pass, $auth_users[$user][0]) !== $auth_users[$user][0]) return 0;
		return 1;
	}

	/**
	 * Digest認証本体
	 * @static
	 */
	function auth_digest($realm,$auth_users)
	{
		// FIXME: なんかかっこ悪いロジックだぁ

		if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$realm.
				'", qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
			// キャンセルボタンを押下
			unset($_SERVER['PHP_AUTH_DIGEST']);
			return FALSE;
		}

		// analyze the PHP_AUTH_DIGEST variable
		if (!($data = auth::http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) {
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$realm.
				'", qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
			// キャンセルボタンを押下
			unset($_SERVER['PHP_AUTH_DIGEST']);
			return FALSE;
		}

		list($scheme, $salt, $role) = auth::get_data($data['username'], $auth_users);
		if ($scheme != '{x-digest-md5}') {
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$realm.
				'", qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
                        // キャンセルボタンを押下
			unset($_SERVER['PHP_AUTH_DIGEST']);
			return FALSE;
		}

		// $A1 = md5($data['username'] . ':' . $realm . ':' . $auth_users[$data['username']]);
		$A1 = $salt;
		$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
		$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

		if ($data['response'] != $valid_response) {
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="'.$realm.
				'", qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
                        // キャンセルボタンを押下
			unset($_SERVER['PHP_AUTH_DIGEST']);
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * PHP_AUTH_DIGEST 変数をパースする関数
	 * function to parse the http auth header
	 * @static
	 */
	function http_digest_parse($txt)
	{
		// protect against missing data
		$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
		$data = array();

                // url に含まれる文字列を含む必要がある
                // preg_match_all('@(\w+)=([\'"]?)([a-zA-Z0-9=./\_-]+)\2@', $txt, $matches, PREG_SET_ORDER); 
		// preg_match_all('@(\w+)=([\'"]?)([a-zA-Z0-9=./%&\?\_-_+]+)\2@', $txt, $matches, PREG_SET_ORDER);
                preg_match_all('@(\w+)=([\'"]?)([a-zA-Z0-9=./%&\?\_-]+)\2@', $txt, $matches, PREG_SET_ORDER);

		foreach ($matches as $m) {
			$data[$m[1]] = $m[3];
			unset($needed_parts[$m[1]]);
		}

		return $needed_parts ? FALSE : $data;
	}

	/**
	 * データの分解
	 * @static
	 */
	function get_data($user,$auth_users)
	{
		if (!isset($auth_users[$user])) {
			// scheme, salt, role
			return array('','','');
		}

		$role = (empty($auth_users[$user][1])) ? '' : $auth_users[$user][1];
		list($scheme,$salt) = auth::passwd_parse($auth_users[$user][0]);
		return array($scheme,$salt,$role);
	}

	/**
	 * PukiWiki Passwd の分解
	 * @static
	 */
	function passwd_parse($passwd)
	{
		$regs = array();
		if (preg_match('/^(\{.+\})(.*)$/', $passwd, $regs)) {
			return array($regs[1], $regs[2]);
		}
		return array('',$passwd);
	}

	/**
	 * ユーザ名の取得
	 * @static
	 */
	function get_username_digest()
	{
		global $realm,$auth_users;

		if (auth::auth_digest($realm,$auth_users)) {
			return auth::get_username_digest();
		}
		return '';
	}

	/**
	 * 署名の抽出
	 * @static
	 */
	function get_signature($lines)
	{
		$patterns = array(
			"'.*? -- \[\[(.*?)\]\] &new{.*?};'si",	// -- [[xxx]] &new{xxx};
			"'.*? -- (.*?) &new{.*?};'si",		// -- xxx &new{xxx};
			"'.*? - \[\[(.*?)\]\] &new{.*?}'si",	// - [[xxx]] &new{xxx};
			"'.*? - (.*?) &new{.*?}'si",		// - xxx &new{xxx};
			"'.*? -- \[\[(.*?)\]\]'si",		// -- [[xxx]]
			"'.*? -- \[(.*?)\]'si",			// -- [xxx]
			"'.*? -- (.*?)'si",			// -- xxx
		);

		foreach ($lines as $_line) {
			foreach ($patterns as $pat) {
				if (preg_match($pat,$_line,$regs)) {
					return $regs[1];
				}
			}
		}
		return '';
	}

	function is_auth_digest() { return version_compare(phpversion(), '5.1', '>='); }

	function is_page_readable($uname, $page, $auth_flag = TRUE)
	{
		global $read_auth, $read_auth_pages;
		global $auth_method_type;

		if (! $read_auth) return TRUE;

		// Checked by:
		$target_str = '';
		if ($auth_method_type == 'pagename') {
			$target_str = $page; // Page name
		} else if ($auth_method_type == 'contents') {
			$target_str = get_source($page, TRUE, TRUE); // Its contents
		}

		$user_list = array();
		foreach($read_auth_pages as $key=>$val)
			if (preg_match($key, $target_str))
				$user_list = array_merge($user_list, explode(',', $val));

		if (empty($user_list)) return TRUE; // No limit

		// 未認証者
		if (empty($uname)) return FALSE;

		if (in_array($uname, $user_list)) return TRUE;
		return FALSE;
	}

	function get_existpages($dir = DATA_DIR, $ext = '.txt')
	{
		static $pages;

		$rc = array();

		// ページ名の取得
		if (!isset($pages)) $pages = get_existpages($dir, $ext);
		// ユーザ名取得
		$uname = auth::check_auth();
		// コンテンツ管理者以上は、: のページも閲覧可能
		$is_colon = auth::check_role('role_adm_contents');

		// 役割の取得
		// $now_role = auth::get_role_level();

		foreach($pages as $file=>$page) {
			if (! auth::is_page_readable($uname, $page, TRUE)) continue;
			if (substr($page,0,1) != ':') {
				$rc[$file] = $page;
				continue;
			}

			// colon page
			if ($is_colon) continue;
			$rc[$file] = $page;
		}

		return $rc;
	}
}

?>
