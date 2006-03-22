<?php
/**
 * PukiWiki Plus! Blocking SPAM
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: log.php,v 0.2 2006/03/22 00:43:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * Plus! - lib/file.php, lib/func.php, lib/config.php
 *
 */

if (!defined('CONFIG_SPAM_BL')) {
	define('CONFIG_SPAM_BL', 'spam/BlockList');
}
if (!defined('CONFIG_SPAM_WL_PRIVATE_NET')) {
	define('CONFIG_SPAM_WL_PRIVATE_NET', 'spam/WhiteList/praivate_network');
}
if (!defined('CONFIG_SPAM_WL_SKIP_DOMAIN')) {
	define('CONFIG_SPAM_WL_SKIP_DOMAIN', 'spam/WhiteList/skip_domain');
}

// Zero is Unlimited
if (!defined('SPAM_MAX_COUNTER')) {
	define('SPAM_MAX_COUNTER', 2);
}

function SpamCheck($link,$mode='dns')
{
	return ($mode == 'ip') ? SpamCheckIPBL('',$link) : SpamCheckDNSBL('',$link);
}

function SpamCheckDNSBL($bl,$link)
{
	$obj = new DNSBL();
	$obj->setWhiteList( $obj->getConfig(CONFIG_SPAM_WL_SKIP_DOMAIN) );

	if (empty($bl) || ! is_array($bl)) {
		$obj->setBlockList( $obj->getConfig(CONFIG_SPAM_BL, 'HOST') );
	} else {
		$obj->setBlockList( $bl );
	}

	$obj->setMyNetList( $obj->getConfig(CONFIG_SPAM_WL_PRIVATE_NET, 'HOST') );

	$hosts = (! is_array($link)) ? array($link) : $link;

	$i = 0;
	foreach($hosts as $host) {
		$url = parse_url($host);
		$domain = (empty($url['host'])) ? $host : $url['host'];
		$obj->setName($domain);
		if ($obj->isListed()) return TRUE;
		if (SPAM_MAX_COUNTER == 0) continue;
		$i++;
		if ($i > SPAM_MAX_COUNTER) return FALSE;
	}
	return FALSE;
}

function SpamCheckIPBL($bl,$ip)
{
	global $log_common;

	$obj = new IPBL();

	if (empty($bl) || ! is_array($bl)) {
		$obj->setBlockList( $obj->getConfig(CONFIG_SPAM_BL, 'IP') );
	} else {
		$obj->setBlockList( $bl );
	}

	$config = new Config(CONFIG_SPAM_WL_PRIVATE_NET);
	$config->read();
	$private_ip = $config->get('IP');
	$dynm_host = $config->get('DYNAMIC_HOST');
	unset($config);
	$dynm_ip = array();
	foreach($dynm_host as $host){
		$tmp = gethostbyname($host);
		if ($host == $tmp) continue; // IPが求まらない
		$dynm_ip[] = $tmp;
	}

	if (! empty($log_common['nolog_ip'])) {
		 $obj->setMyNetList( array_merge($private_ip, $log_common['nolog_ip'], $dynm_ip) );
	} else {
		$obj->setMyNetList( array_merge($private_ip, $dynm_ip) );
	}

	$hosts = (! is_array($ip)) ? array($ip) : $ip;

	$i = 0;
	foreach($hosts as $host) {
		$obj->setName($host);
		if ($obj->isListed()) return TRUE;
		if (SPAM_MAX_COUNTER == 0) continue;
		$i++;
		if ($i > SPAM_MAX_COUNTER) return FALSE;
	}
	return FALSE;
}

class DNSBL
{
	var $BlockList, $WhiteList, $MyNetList;
	var $debug = FALSE, $debug_result = array();
	var $host, $reverse;
	var $TLD = array(
		// TLD
		'com'    => 1, 'net'    => 1, 'org'    => 1, 'edu'    => 1, 'gov'    => 1,
		'mil'    => 1, 'int'    => 1,
		'biz'    => 1, 'info'   => 1, 'name'   => 1, 'pro'    => 1, 'museum' => 1,
		'aero'   => 1, 'coop'   => 1,
		// Infrastructure TLD
		'arpa'   => 2, // e164.arpa, ip6.arpa, in-addr.arpa
		// ccTLD は、2 とする
	);

	// RFC2606
	var $ReservedTLD = array(
		'example' => '', 'invalid' => '', 'localhost' => '', 'test' => '',
	);

	// function DNSBL() { }

	function setName($host)
	{
		$this->host = strtolower($host);
		$this->reverse = array_reverse(explode('.', $this->host ));
	}
	function setBlockList($x) { $this->BlockList = $x; }
	function setWhiteList($x) { $this->WhiteList = $x; }
	function setMyNetList($x) { $this->MyNetList = $x; }

	function getConfig($name,$target='LIST')
	{
		$config = new Config($name);
		$config->read();
		$data = $config->get($target);
		unset($config);
		return $data;
	}

	// 予約されたドメインを使用(不正)
	function isReservedTLD() { return (isset($this->ReservedTLD[$this->reverse[0]])) ? TRUE : FALSE; }

	function getDomain()
	{
		// 予約されたドメインを使用(不正)
		if ($this->isReservedTLD()) return '';

		$idx = (isset($this->TLD[$this->reverse[0]])) ? $this->TLD[$this->reverse[0]] : 2;
		// 本来あるべき長さに達していない
		if (count($this->reverse) < $idx) return '';

		$rc = '';
		for($i=$idx; $i>=0; $i--) {
			$rc .= $this->reverse[$i];
			if ($i > 0) $rc .= '.';
		}
		return $rc;
	}

	function isListed()
	{
		// 指定されたホスト名でチェック
		if (isset($this->MyNetList[$this->host])) return FALSE;
		// ドメイン名のレベル合わせ
		$host = $this->getDomain();
		if (empty($host)) return FALSE;
		// White List に存在するドメインは除く
		if (isset($this->WhiteList[$host]) && $this->WhiteList[$host][0]) return FALSE;

		foreach($this->BlockList as $zone) {
			if (! $zone[1]) continue;
			$lookup = $host . '.' . $zone[0];
			$ip = gethostbyname($lookup);
			if ($this->debug) {
				$result = ($lookup != $ip) ? ' '.$ip : '';
				$this->debug_result[] = array($zone[0],$host,$result);
				continue;
			}
			if ($ip != $lookup) return TRUE;
		}
		return FALSE;
	}

	function setDebug($x) { $this->debug = $x; }
	function getDebug()
	{
		foreach($this->debug_result as $rc) {
			echo (! empty($rc[2])) ? 'HIT' : '   ';
			echo ' : '.$rc[1].'.'.$rc[0].' '.$rc[2]."\n";
		}
	}
}

class IPBL extends DNSBL
{
	var $praivateIP = array('127.0.0.0/8','10.0.0.0/8','172.16.0.0/12','192.168.0.0/16');

	// function IPBL() { }

	function isListed()
	{
		if ($this->isPraivate($this->host, array_merge($this->praivateIP,$this->MyNetList)) ) return FALSE;
		// reverse ip を生成
		$host = implode('.', $this->reverse);

		foreach($this->BlockList as $zone) {
			if (! $zone[1]) continue;
			$lookup = $host . '.' . $zone[0];
			$ip = gethostbyname($lookup);
			if ($this->debug) {
				$result = ($lookup != $ip) ? ' '.$ip : '';
				$this->debug_result[] = array($zone[0],$host,$result);
				continue;
			}
			if ($ip != $lookup) return TRUE;
		}
		return FALSE;
	}

	// Private IP の判定
	function isPraivate($ip,$networks)
	{
		// $l_ip = ip2long( $this->ip2arrangement($ip) );
		$l_ip = ip2long($ip);
		foreach($networks as $network) {
			$range = explode('/', $network);
			// $l_network = ip2long( $this->ip2arrangement($range[0]) );
			$l_network = ip2long( $range[0] );
			if (empty($range[1])) $range[1] = 0;
			$subnetmask = pow(2,32) - pow(2,32 - $range[1]);
			if (($l_ip & $subnetmask) == $l_network) return TRUE;
		}
		return FALSE;
	}

	// ex. 10 -> 10.0.0.0, 192.168 -> 192.168.0.0
	function ip2arrangement($ip)
	{
		$x = explode('.', $ip);
		if (count($x) == 4) return $ip;
		for($i=0;$i<4;$i++) { if (empty($x[$i])) $x[$i] =0; }
		return sprintf('%d.%d.%d.%d',$x[0],$x[1],$x[2],$x[3]);
	}

}

?>
