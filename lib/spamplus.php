<?php
/**
 * PukiWiki Plus! Blocking SPAM
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: spamplus.php,v 0.7 2007/04/28 23:26:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * Plus! - lib/file.php, lib/func.php, lib/config.php
 *
 */

defined('CONFIG_SPAM_BANLIST')        or define('CONFIG_SPAM_BANLIST',        'spam/BANList');
defined('CONFIG_SPAM_BLOCKLIST')      or define('CONFIG_SPAM_BLOCKLIST',      'spam/BlockList');
defined('CONFIG_SPAM_BL')             or define('CONFIG_SPAM_BL',             'spam/BlackList');
defined('CONFIG_SPAM_WL_PRIVATE_NET') or define('CONFIG_SPAM_WL_PRIVATE_NET', 'spam/WhiteList/praivate_network');
defined('CONFIG_SPAM_WL_SKIP_DOMAIN') or define('CONFIG_SPAM_WL_SKIP_DOMAIN', 'spam/WhiteList/skip_domain');

// Zero is Unlimited
defined('SPAM_MAX_COUNTER')           or define('SPAM_MAX_COUNTER', 2); 

function SpamCheck($link,$mode='dns')
{
	return ($mode == 'ip') ? SpamCheckIPBL('',$link) : SpamCheckDNSBL('',$link);
}

function SpamCheckBAN($ip)
{
	global $log_ua, $use_spam_check;
	if (! $use_spam_check['page_view']) return FALSE;
	$obj = new SPAMBAN();
	if ($obj->BlackCheck($ip,$log_ua)) return TRUE;
	return FALSE;
}

function SpamCheckDNSBL($bl,$link)
{
	$obj = new DNSBL();
	$obj->setWhiteList( $obj->getConfig(CONFIG_SPAM_WL_SKIP_DOMAIN) );

	if (empty($bl) || ! is_array($bl)) {
		$obj->setBlockList( $obj->getConfig(CONFIG_SPAM_BLOCKLIST, 'HOST') );
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
	global $log_common, $log_ua;

	$obj_bl = new SPAMBL();
	if ($obj_bl->BlackCheck($ip,$log_ua)) return TRUE;

	$obj = new IPBL();

	// $obj->setBlackList( $obj->getConfig(CONFIG_SPAM_BL, 'IP,HOST,UA') );

	if (empty($bl) || ! is_array($bl)) {
		$obj->setBlockList( $obj->getConfig(CONFIG_SPAM_BLOCKLIST, 'IP') );
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

class SPAMCHECK
{
	var $BlockList, $BlackList, $WhiteList, $MyNetList;

	function setBlockList($x) { $this->BlockList = $x[0]; }
	function setBlackList($x) { $this->BlackList = $x; }
	function setWhiteList($x) { $this->WhiteList = $x[0]; }
	function setMyNetList($x) { $this->MyNetList = $x[0]; }

	function getConfig($name,$target='LIST')
	{
		$config = new Config($name);
		$config->read();

		$targets = explode(',', $target);
		$data = array();
		foreach($targets as $x) {
			$data[] = $config->get($x);
		}
		unset($config);
		return $data;
	}
}

class SPAMBL extends SPAMCHECK
{
	var $target,$ua;

	function BlackCheck($target,$ua)
	{
		$this->target = $target;
		$this->ua = $ua;

		$this->setBlackList( $this->getConfig(CONFIG_SPAM_BL, 'IP,HOST,UA') );
		return $this->Check();
	}

	function Check()
	{
		if (! empty($this->target)) {
			// IP
			if (! empty($this->BlackList[0])) {
				$ips = (is_ipaddr($this->target)) ? array($this->target) : gethostbynamel($this->target);
				foreach($ips as $ip) {
					if ($this->RangeCheck($ip)) return TRUE;
				}
			}
			// HOST
			if (! empty($this->BlackList[1])) {
				$host = gethostbyaddr($this->target);
				$len_host = strlen($host);
				foreach($this->BlackList[1] as $x) {
					// 後方一致のために、長さを確認
					if (strlen($x) > $len_host) continue;
					// 部分一致(後方一致)
					if(stristr($host, $x) !== FALSE) return TRUE;
				}
			}
		}

		// UA
		if (! empty($this->ua)) {
			foreach($this->BlackList[2] as $x) {
				if ($this->ua == $x) return TRUE;
			}
		}

		return FALSE;
	}

	function RangeCheck($ip)
	{
		foreach($this->BlackList[0] as $x) {
			if (is_array($x)) {
				$from = (empty($x[0])) ? '' : $x[0];
				$to   = (empty($x[1])) ? '' : $x[1];
			} else {
				$from = $x;
				$to = '';
			}

			if (ip_range_check($ip,$from,$to)) return TRUE;
		}
		return FALSE;
	}
}

class SPAMBAN extends SPAMBL
{
	function BlackCheck($target,$ua)
	{
                $this->target = $target;
                $this->ua = $ua;

		$this->setBlackList( $this->getConfig(CONFIG_SPAM_BANLIST, 'IP,HOST,UA') );
                return $this->Check();
	}
}

class DNSBL extends SPAMCHECK
{
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

	// function DNSBL() { }

	function setName($host)
	{
		$this->host = strtolower($host);
		$this->reverse = array_reverse(explode('.', $this->host ));
	}

	function getDomain()
	{
		// 予約されたドメインを使用(不正)
		if (is_ReservedTLD($this->host)) return '';

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
		foreach($this->MyNetList as $My) {
			if (strpos($this->host,$My) === 0) return FALSE;
		}
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
	// function IPBL() { }

	function isListed()
	{
		if (is_localIP($this->host)) return FALSE;
		if (ip_scope_check($this->host,$this->MyNetList)) return FALSE;
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
}

?>
