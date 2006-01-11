<?php
/**
 * PukiWiki Plus! 認証処理
 *
 * @author	Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth.cls.php,v 0.4 2006/01/11 22:21:00 upk Exp $
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
		foreach (array('PHP_AUTH_USER', 'REMOTE_USER', 'AUTH_USER') as $x) {
			if (isset($_SERVER[$x])) {
				$ms = explode('\\', $_SERVER[$x]);
				if (count($ms) == 3) return $ms[2]; // DOMAIN\\USERID
				foreach (array('PHP_AUTH_PW', 'HTTP_AUTHORIZATION') as $pw) {
					if (! empty($_SERVER[$pw])) return $_SERVER[$x];
				}
			}
		}

		// NTLM対応
		list($domain, $login, $host) = auth::ntlm_decode();
		return $login;
	}

	/**
	 * ユーザのROLEを取得
	 * @static
	 */
	function get_role_level()
	{
		global $auth_users, $adminpass;

		$login = auth::check_auth();
		if (empty($login)) return 0;			// 未認証者
		if (! isset($auth_users[$login])) {
			if ($login == 'admin') {
				// return ( pkwk_hash_compute($_SERVER['PHP_AUTH_PW'], $adminpass) !== $adminpass) ? 0 : 3.1;
				return 3.1;
			} else {
				// return ( auth::auth_pw($auth_users) ) ? 4.1 : 0;
				return 4.1;
			}
		}
		if (empty($auth_users[$login][1])) return 4;	// 認証者(pukiwiki)

		$role = $auth_users[$login][1];
		switch ($role) {
		case 2: // サイト管理者
		case 3: // コンテンツ管理者
		case 4: // 認証者(pukiwiki)
			return $role;
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
			$auth_temp = array('admin' => array($adminpass) );
			while(1) {
				if (!auth::auth_pw($auth_temp))
				{
					unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
					header( 'WWW-Authenticate: Basic realm="USER NAME is admin"' );
					header( 'HTTP/1.0 401 Unauthorized' );
					break;
				}
				// ESC
				return FALSE;
			}
			$chk_role = 3;
			break;
		default:
			$chk_role = 0;
		}

		if ($chk_role == 0) return FALSE;	// 機能無効
		if ($chk_role == 1) return TRUE;	// 強制

		// 役割に応じた挙動の設定
		$now_role = (int)auth::get_role_level();
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
		$rc = array('','','');
		if (!function_exists('base64_decode')) return $rc;
		if (!isset($_SERVER['HTTP_AUTHORIZATION'])) return $rc;
		// if (substr($_SERVER['HTTP_AUTHORIZATION'],0,4) != 'MSSP') return $rc;

		list($auth_type,$x) = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

		switch( strtoupper($auth_type) ) {
		// IIS用 (http://homepage1.nifty.com/yito/namazu/gbook/20021127.1530.html)
		case 'BASIC':     // 'Basic'
			list($login, $pass) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			return array('',$login,'');
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
		return $rc; // domain, login, hostname
	}

	/**
	 * 認証 (PukiWikiの設定に準ずる)
	 * @static
	 */
	function auth_pw($auth_users)
	{
		$user = (isset($_SERVER['PHP_AUTH_USER'])) ? $_SERVER['PHP_AUTH_USER'] : '';
		$pass = (isset($_SERVER['PHP_AUTH_PW']))   ? $_SERVER['PHP_AUTH_PW'] : '';
		if (empty($user) && empty($pass)) return 0;
		if (empty($auth_users[$user][0])) return 0;
		if ( pkwk_hash_compute($pass, $auth_users[$user][0]) !== $auth_users[$user][0]) return 0;
		return 1;
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

}

?>
