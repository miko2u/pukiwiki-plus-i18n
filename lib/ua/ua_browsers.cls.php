<?php
/*
 * Browser
 *
 * @copyright   Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: ua_browsers.cls.php,v 0.6 2006/07/09 16:12:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * o 参考にしたコード(AWStats)
 *   http://awstats.sourceforge.net/
 *   Copyright (C) 2000-2006 - Laurent Destailleur - eldy@users.sourceforge.net
 *   awstats-6.6/wwwroot/cgi-bin/awstats.pl
 *   awstats-6.6/wwwroot/cgi-bin/lib/browsers.pm
 */

class ua_browsers
{
	//sub UnCompileRegex {
	//	shift =~ /\(\?[-\w]*:(.*)\)/;
	//	return $1;
	//}

	// ブラウザ分類用
	var $browsers_id = array(

		'ia_archiver',
		'crazy.\bbrowser',
		'feedreader',
		'funwebproducts',
		'hotbar',
		'kddi',
		'lunascape',
		'msnbot',
		'turnitinbot',
		'Yahoo!.\bSlurp',

		# 有名なブラウザは、最初に定義しておけばヒット率があがる
		'firebird',
		'firefox',
		'go!zilla',
		'icab',
		'konqueror',
		'links',
		'lynx',
		'omniweb',
		'opera',	# 携帯に実装された Opera があるんだよなぁ
		
		# Other standard web browsers
		'22acidownload',
		'aol\-iweng',
		'amaya',
		'amigavoyager',
		'aweb',
		'bpftp',
		'camino',
		'chimera',
		'cyberdog',
		'dillo',
		'doris',
		'dreamcast',
		'xbox',
		'downloadagent',
		'ecatch',
		'emailsiphon',
		'encompass',
		'epiphany',
		'friendlyspider',
		'fresco',
		'galeon',
		'flashget',
		'freshdownload',
		'getright',
		'leechget',
		'netants',
		'headdump',
		'hotjava',
		'ibrowse',
		'intergo',
		'javaws',
		'k\-meleon',
		'linemodebrowser',
		'lotus\-notes',
		'macweb',
		'multizilla',
		'ncsa\smosaic',
		'netcaptor',
		'netpositive',
		'nutscrape',
		'msfrontpageexpress',
		'phoenix',
		'shiira',		# Must be before safari
		'safari',
		'tzgeturl',
		'viking',
		'webfetcher',
		'webexplorer',
		'webmirror',
		'webvcr',
		
		# Site grabbers
		'teleport',
		'webcapture',
		'webcopier',
		
		# Music only browsers
		'real',
		'winamp',			# これは、winampmpeg および winamp3httprdr に左右する
		'windows\-media\-player',
		'audion',
		'freeamp',
		'itunes',
		'jetaudio',
		'mint\saudio',
		'mpg123',
		'mplayer',
		'nsplayer',
		'qts',
		'sonique',
		'uplayer',
		'xaudio',
		'xine',
		'xmms',
		
		# RSS Readers
		'abilon',
		'aggrevator',
		'akregator',
		'betanews\sreader',
		'Bookmark\sRenewal\sCheck\sAgent', # add upk
		'feeddemon',
		'feedreader',
		'jetbrains\somea\sreader',
		'jetbrains\somea\spro',
		'livedoorCheckers',		# add upk
		'netnewswire',
		'newsfire',
		'newsgator',
		'newzcrawler',
		'pluck',
		'pulpfiction',
		'rssbandit',
		'rssreader',
		'rssowl',
		'rss\sxpress',
		'rssxpress',
		'sage',
		'sharpreader',
		'shrook',
		'straw',
		'syndirella',
		'webpatrol',			# add upk
		'wizz\srss\snews\sreader',

		# PDA/携帯電話 ブラウザ
		'alcatel',                      # Alcatel
		'lg\-',                         # LG
		'mot\-',                        # Motorola
		'nokia',                        # Nokia
		'panasonic',                    # Panasonic
		'philips',                      # Philips
		'sagem',                        # Sagem
		'samsung',                      # Samsung
		'sie\-',                        # SIE
		'sec\-',                        # SonyEricsson
		'sonyericsson',                 # SonyEricsson
		'ericsson',                     # Ericsson (sonyericsson の後に定義すること)
		'mmef',
		'mspie',
		'wapalizer',
		'wapsilon',
		'webcollage',
		'up\.',                         # これは、UP.Browser および UP.Link に左右する

		# PDA/携帯電話 I-Mode ブラウザ
		'docomo',
		'portalmmm',
		'j\-phone',                     # add upk
		'ddipocket',                    # Opera 対応 で前出 // add upk

		# Others (TV)
		'webtv',

		# Other kind of browsers
		'gnome\-vfs',
		'neon',
		'apt',
		'analogx\sproxy',
		'curl',
		'csscheck',
		'httrack',
		'webreaper',
		'wget',
		'chilkat',
		'webdownloader\sfor\sx',
		'w3m',
		'wdg\svalidator',
		'webzip',
		'staroffice',
		'gnus',
		'liferea',
		'nikto',
		'microsoft\-webdav\-miniredir',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\scache\smanager',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sdav',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sprotocol\sdiscovery',
		'POE\-Component\-Client\-HTTP',

		# UPK
		'harbot.\bgatestation',
		'sleipnir.\b',
		'wwwc\/',
		
		# 一番最後に定義すべきもの
		'mozilla',			# 大多数のブラウザは、mozila 文字列を含んでいる
		'libwww',			# libwww を利用するブラウザは、ブラウザ識別子と libwww の両方を含むため
		'lwp',
	);

	// ブラウザ分類後のアイコン設定用
	var $browsers_icon = array(
		# Standard web browsers
		'msie'				=> 'msie',
		'netscape'			=> 'netscape',
		
		'firebird'			=> 'phoenix',
		'firefox'			=> 'firefox',
		'go!zilla'			=> 'gozilla',
		'icab'				=> 'icab',
		'konqueror'			=> 'konqueror',
		'lynx'				=> 'lynx',
		'omniweb'			=> 'omniweb',
		'opera'				=> 'opera',
		
		# Other standard web browsers
		'amaya'				=> 'amaya',
		'amigavoyager'			=> 'amigavoyager',
		'avantbrowser'			=> 'avant',
		'aweb'				=> 'aweb',
		'bpftp'				=> 'bpftp',
		'camino'			=> 'chimera',
		'chimera'			=> 'chimera',
		'cyberdog'			=> 'cyberdog',
		'dillo'				=> 'dillo',
		'doris'				=> 'doris',
		'dreamcast'			=> 'dreamcast',
		'xbox'				=> 'winxbox',
		'ecatch'			=> 'ecatch',
		'encompass'			=> 'encompass',
		'epiphany'			=> 'epiphany',
		'fresco'			=> 'fresco',
		'galeon'			=> 'galeon',
		'flashget'			=> 'flashget',
		'freshdownload'			=> 'freshdownload',
		'getright'			=> 'getright',
		'leechget'			=> 'leechget',
		'hotjava'			=> 'hotjava',
		'ibrowse'			=> 'ibrowse',
		'k\-meleon'			=> 'kmeleon',
		'lotus\-notes'			=> 'lotusnotes',
		'macweb'			=> 'macweb',
		'multizilla'			=> 'multizilla',
		'msfrontpageexpress'		=> 'fpexpress',
		'ncsa\smosaic'			=> 'ncsa\smosaic',
		'netpositive'			=> 'netpositive',
		'phoenix'			=> 'phoenix',
		'safari'			=> 'safari',
		
		# Site grabbers
		'teleport'			=> 'teleport',
		'webcapture'			=> 'adobe',
		'webcopier'			=> 'webcopier',
		
		# Music only browsers
		'real'				=> 'mediaplayer',
		'winamp'			=> 'mediaplayer',
		'windows\-media\-player'	=> 'mediaplayer',
		'audion'			=> 'mediaplayer',
		'freeamp'			=> 'mediaplayer',
		'itunes'			=> 'mediaplayer',
		'jetaudio'			=> 'mediaplayer',
		'mint\saudio'			=> 'mediaplayer',
		'mpg123'			=> 'mediaplayer',
		'mplayer'			=> 'mediaplayer',
		'nsplayer'			=> 'mediaplayer',
		'qts'				=> 'mediaplayer',
		'sonique'			=> 'mediaplayer',
		'uplayer'			=> 'mediaplayer',
		'xaudio'			=> 'mediaplayer',
		'xine'				=> 'mediaplayer',
		'xmms'				=> 'mediaplayer',

		# PDA/Phonecell browsers
		'alcatel'			=> 'pdaphone',
		'lg\-'				=> 'pdaphone',
		'ericsson'			=> 'pdaphone', # Ericsson
		'mot\-'				=> 'pdaphone',
		'nokia'				=> 'pdaphone',
		'panasonic'			=> 'pdaphone',
		'philips'			=> 'pdaphone',
		'sagem'				=> 'pdaphone',
		'samsung'			=> 'pdaphone',
		'sie\-'				=> 'pdaphone',
		'sec\-'				=> 'pdaphone',
		'sonyericsson'			=> 'pdaphone',
		'ericsson'			=> 'pdaphone',	
		'mmef'				=> 'pdaphone',
		'mspie'				=> 'pdaphone',
		'wapalizer'			=> 'pdaphone',
		'wapsilon'			=> 'pdaphone',
		'webcollage'			=> 'pdaphone',
		'up\.'				=> 'pdaphone',
		
		# PDA/Phonecell I-Mode browsers
		'docomo'			=> 'pdaphone',
		'portalmmm'			=> 'pdaphone',
		'j\-phone'			=> 'pdaphone',	# upk
		'ddipocket'			=> 'pdaphone',	# upk
		
		# Others (TV)
		'webtv'				=> 'webtv',

		# RSS Readers
		'aggrevator'			=> 'rss',
		'akregator'			=> 'akregator',
		'abilon'			=> 'abilon',
		'betanews\sreader'		=> 'rss',
		'Bookmark\sRenewal\sCheck\sAgent'=> 'rss',
		'feeddemon'			=> 'rss',
		'feedreader'			=> 'rss',
		'jetbrains\somea\sreader'	=> 'rss',
		'jetbrains\somea\spro'		=> 'rss',
		'livedoorCheckers'		=> 'rss',
		'netnewswire'			=> 'rss',
		'newsfire'			=> 'rss',
		'newsgator'			=> 'rss',
		'newzcrawler'			=> 'rss',
		'pluck'				=> 'rss',
		'pulpfiction'			=> 'rss',
		'rssbandit'			=> 'rss',
		'rssreader'			=> 'rss',
		'rssowl'			=> 'rss',
		'rss\sxpress'			=> 'rss',
		'rssxpress'			=> 'rss',
		'sage'				=> 'rss',
		'sharpreader'			=> 'rss',
		'shrook'			=> 'rss',
		'straw'				=> 'rss',
		'syndirella'			=> 'rss',
		'webpatrol'			=> 'rss',
		'wizz\srss\snews\sreader'	=> 'wizz',

		# Other kind of browsers
		'apt'				=> 'apt',
		'analogx\sproxy'		=> 'analogx',
		'svn'				=> 'subversion',
		'microsoft\-webdav\-miniredir'	=> 'frontpage',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\scache\smanager'	=> 'frontpage',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sdav'			=> 'frontpage',
		'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sprotocol\sdiscovery'	=> 'frontpage',
		'gnome\-vfs'			=> 'gnome',
		'neon'				=> 'neon',
		'javaws'			=> 'java',
		'webzip'			=> 'webzip',
		'webreaper'			=> 'webreaper',
		'httrack'			=> 'httrack',
		'staroffice'			=> 'staroffice',
		'gnus'				=> 'gnus',
		'mozilla'			=> 'mozilla'
	);

	//  $regvermsie     =qr/msie([+_ ]|)([\d\.]*)/i; // ex
	var $regvermsie     = "'msie([+_ ]|)([\d\.]*)'si";
	var $regverfirefox  = "'firefox\/([\d\.]*)'si";
	var $regversvn      = "'svn\/([\d\.]*)'si";
	var $regnotie       = "'webtv|omniweb|opera'si";
	var $regvernetscape = "'netscape.?\/([\d\.]*)'si";
	var $regvermozilla  = "'mozilla(\/|)([\d\.]*)'si";
	var $regnotnetscape = "'gecko|compatible|opera|galeon|safari'si";
	var $id;

	// ブラウザを識別
	function get_id($ua) {
		$x = $this->set_browsers_id($ua);
		// 以下は除去して戻す
		foreach(array('.\b','\/','\-') as $_pat) {
			$x = str_replace($_pat,'',$x);
		}
		return $x;
	}
	// ブラウザのアイコンを設定
	function get_icon($ua)
	{
		return $this->set_browsers_icon($ua);
	}

	// ブラウザ識別
	function set_browsers_id($ua)
	{
		foreach ($this->browsers_id as $x) {
			$pat = "'".$x."'si";
			if (preg_match($pat,$ua,$regs)) return $x;
		}
		return '';
	}

	function set_browsers_icon($ua)
	{
		$this->id = $this->set_browsers_id($ua);

		if ($this->id == 'ddipocket')
			return $this->browsers_icon[$this->id];

		# IE ?
		if (preg_match($this->regvermsie,$ua,$regs)
		&& !preg_match($this->regnotie,$ua,$tmp)) {
			return $this->browsers_icon['msie'];
		}

		# Firefox ?
		if (preg_match($this->regverfirefox,$ua,$regs)) {
			return $this->browsers_icon['firefox'];
		}

		# Subversion ?
		if (preg_match($this->regversvn,$ua,$regs)) {
			return $this->browsers_icon['svn'];
		}

		# Netscape 6.x, 7.x ... ?
		if (preg_match($this->regvernetscape,$ua,$regs)) {
			return $this->browsers_icon['netscape'];
		}

		# Netscape 3.x, 4.x ... ?
		if (preg_match($this->regvermozilla,$ua,$regs)
		&& !preg_match($this->regnotnetscape,$ua,$tmp)) {
			return $this->browsers_icon['netscape'];
		}

		// ブラウザ識別のアイコンがある場合は、それを設定
		if (isset($this->browsers_icon[$this->id]))
			return $this->browsers_icon[$this->id];
		return 'unknown';
	}

}

?>
