<?php
/**
 * PukiWiki Plus! Proxy判定クラス
 *
 * @copyright	Copyright &copy; 2004-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: proxy.cls.php,v 0.8 2007/12/20 04:34:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once(LIB_DIR . 'spamplus.php');

/**
 * Proxy関連クラス
 * @abstract
 */
class check_proxy
{
	var $proxy = array(
		// 取得値は、上から下へ上書きする。下ほど有用。
		// 0:KEY, 1:Prox判定利用, 2:IP取得利用
		// ***** IP アドレス取得 *****
		array('HTTP_X_FORWARDED_FOR',   1,1), // プロキシサーバ経由の生IP
		array('HTTP_SP_HOST',		1,1), // ホスト情報
		array('HTTP_CLIENT_IP',		1,1),
		array('HTTP_FORWARDED',		1,1), // プロキシサーバの情報や生IP
		array('HTTP_PC_REMOTE_ADDR',	1,1),
		array('REMOTE_ADDR',            0,1),
		// ***** PROXY 判定専用 *****
		array('HTTP_CACHE_INFO',	1,0), // プロキシサーバのキャッシュ情報
		array('HTTP_IF_MODIFIED_SINCE', 1,0), // プロキシサーバに接続した時間の情報
		array('HTTP_PROXY_CONNECTION',	1,0), // プロキシ関係の情報
		array('HTTP_VIA',		1,0), // プロキシの種類・バージョン等
		array('HTTP_XONNECTION',	1,0),
		array('HTTP_XROXY_CONNECTION',	1,0),
		array('HTTP_X_LOCKING',		1,0), // IPアドレス・REFERERなどの情報
		array('HTTP_X_TE',		1,0),
		// array('HTTP_HOST',		1,0), // ホスト情報 (仮に追加 09/28)
		// ***** 未使用 *****
		//array('HTTP_CACHE_CONTROL',	0,0), // プロキシサーバへのコントロール情報
		//array('HTTP_PRAGMA',		0,0),
	);

	/**
	 * Proxy経由かのチェック
	 */
	function is_proxy()
	{
		foreach ($this->proxy as $x) {
			if (!$x[1]) continue; // Proxy判定利用
			if (isset($_SERVER[$x[0]])) return 1;
		}
		return 0;
	}

	/**
	 * Real IPアドレスを戻す
	 * プライベートアドレスの場合もある
	 */
	function get_realip()
	{
		foreach ($this->proxy as $x) {
			if (!$x[2]) continue; // IP取得利用
			$rc = '';
			if (isset($_SERVER[$x[0]])) {
				$rc = trim($_SERVER[$x[0]]);
			}
			if (empty($rc)) continue;
			if (! is_ipaddr($rc)) continue;		// IPアドレス体系か？
			if (! is_localIP($rc)) return $rc;	// プライベートな生IPを取得してもあまり意味がない
		}
		return '';
	}

	/**
	 * Proxy経由かのチェック
	 */
	function get_proxy_info()
	{
		$rc = '';
		foreach ($this->proxy as $x) {
			if (!$x[1]) continue; // Proxy判定利用
			if (isset($_SERVER[$x[0]])) {
				$rc .= '('.$x[0].':'.$_SERVER[$x[0]].')';
			}
		}
		return $rc;
	}
}

function proxy_get_real_ip()
{
	$obj = new check_proxy();
	return $obj->get_realip();
}

function is_proxy()
{
	$obj = new check_proxy();
	$ip = $obj->get_realip();
	if (!empty($ip) && MyNetCheck($ip)) return false;
	return $obj->is_proxy();
}

function MyNetCheck($ip)
{
	global $log_common, $log_ua;

	$config = new Config(CONFIG_SPAM_WL_PRIVATE_NET);
	$config->read();
	$private_ip = $config->get('IP');
	$dynm_host = $config->get('DYNAMIC_HOST');
	// $hosts = $config->get('HOST');
	unset($config);

	$dynm_ip = array();
	foreach($dynm_host as $host){
		$tmp = gethostbyname($host);
		if ($host == $tmp) continue; // IPが求まらない
		$dynm_ip[] = $tmp;
	}
	unset($tmp);

        $obj = new IPBL();

	if (! empty($log_common['nolog_ip'])) {
		$obj->setMyNetList( array(array_merge($private_ip, $log_common['nolog_ip'], $dynm_ip)) );
	} else {
		$obj->setMyNetList( array(array_merge($private_ip, $dynm_ip)) );
	}

	$hosts = (! is_array($ip)) ? array($ip) : $ip;

	foreach($hosts as $host) {
		$obj->setName($host);
		if ($obj->isMyNet()) return true;;
	}
	return false;
}

?>
