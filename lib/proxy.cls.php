<?php
/**
 * PukiWiki Plus! Proxy判定クラス
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: proxy.cls.php,v 0.5 2006/05/28 19:00:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Proxy関連クラス
 * @abstract
 */
class check_proxy
{
	var $proxy = array(
		// 取得値は、上から下へ上書きする。下ほど有用。
		// KEY,Prox判定利用,IP取得利用,専用関数
		// ***** IP アドレス取得 *****
		array('REMOTE_ADDR',		0,1,''),
		array('HTTP_SP_HOST',		1,1,''), // ホスト情報
		array('HTTP_CLIENT_IP',		1,1,''),
		array('HTTP_FROM',		1,1,'http_from'), // 本来はクライアントのメールアドレスを設定
		array('HTTP_FORWARDED',		1,1,''), // プロキシサーバの情報や生IP
		array('HTTP_X_FORWARDED_FOR',	1,1,''), // プロキシサーバ経由の生IP
		array('HTTP_PC_REMOTE_ADDR',	1,1,''),
		// ***** PROXY 判定専用 *****
		array('HTTP_CACHE_INFO',	1,0,''), // プロキシサーバのキャッシュ情報
		array('HTTP_IF_MODIFIED_SINCE', 1,0,''), // プロキシサーバに接続した時間の情報
		array('HTTP_PROXY_CONNECTION',	1,0,''), // プロキシ関係の情報
		array('HTTP_VIA',		1,0,''), // プロキシの種類・バージョン等
		array('HTTP_XONNECTION',	1,0,''),
		array('HTTP_XROXY_CONNECTION',	1,0,''),
		array('HTTP_X_LOCKING',		1,0,''), // IPアドレス・REFERERなどの情報
		array('HTTP_X_TE',		1,0,''),
		// array('HTTP_HOST',		1,0,''), // ホスト情報 (仮に追加 09/28)
		// ***** 未使用 *****
		//array('HTTP_CACHE_CONTROL',	0,0,''), // プロキシサーバへのコントロール情報
		//array('HTTP_PRAGMA',		0,0,''),
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
			// 専用関数処理
			if (empty($x[3])) continue;
			if (function_exists($this->$x[3])) {
				$rc = $this->$x[3]($x[0]);
				if (!empty($rc)) return $rc;
			} else {
				if (isset($_SERVER[$x[0]])) return $_SERVER[$x[0]];
			}
		}
		return '';
	}

	function http_from($key)
	{
		switch ($_SERVER[$key]) {
		case 'msnbot(at)microsoft.com':
		case 'googlebot(at)googlebot.com':
		case 'webmaster@pita.stanford.edu': // WebVac (webmaster@pita.stanford.edu WebVac.org )
		case 'search-beheer@uci.kun.nl': // AVSearch-3.0(SURFnet Search Engine)
		case 'slurp@inktomi.com': // Yahoo!
			return '';
		default:
			return $_SERVER[$key];
		}
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

?>
