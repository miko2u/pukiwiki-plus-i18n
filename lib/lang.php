<?php
/**
 * Language judgment (言語判定)
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: lang.php,v 0.5 2005/03/08 23:19:00 upk Exp $
 *
 */

function set_ui_language()
{
	global $language_considering_setting_level;
	global $language_prepared;
	return get_language($language_considering_setting_level);
}

function get_language($level = 0)
{
	global $language_prepared;

	if ($level == 0) return DEFAULT_LANG;

	$lng_func = array(
		"get_accept_language",		// 1
		"get_user_agent_mozilla",	// 2
		"get_accept_charset",		// 3
		"get_remote_addr",		// 4
	);

	$obj_lng = new accept_language();
	$i = 0;

	// 手指定のため一応、小文字変換
	$tmp_language_prepared = array();
	foreach($language_prepared as $_language_prepared) {
		$tmp_language_prepared[] = strtolower( $_language_prepared );
	}

	foreach($lng_func as $_func) {
		$i++;
		// 指定関数の実行
		$_x = $obj_lng->$_func();
		if (! is_array($_x)) {
			// 指定レベルでも検出不能時は終了
			if ($level == $i) return DEFAULT_LANG;
		}
		$_x2 = array();
		foreach($_x as $_lang) {
			// 環境変数中の定義を一律、小文字変換
			$tmp_lang = strtolower( $_lang[0] );
			// 完全一致の場合
			if (in_array($tmp_lang, $tmp_language_prepared)) return $tmp_lang;
			// 言語_国の定義時に次フェーズ用処理
			$_x1 = split("-", $tmp_lang);
			if ( count($_x1) == 1) continue;
			// 言語名のみ格納
			$_x2[] = $_x1[0];
		}
		// 言語_国を分離し、言語が定義されているかを検査
		foreach($_x2 as $_lang) {
			if (in_array($_lang, $tmp_language_prepared)) return $_lang;
		}
	}
	return DEFAULT_LANG;
}

/**
 * accept_language
 * @abstract
 *
 * 1) HTTP_ACCEPT_LANGUAGE
 * 2) HTTP_USER_AGENT
 * 3) HTTP_ACCEPT_CHARSET
 * 4) REMOTE_ADDR
 */
class accept_language
{
	var $charset = array(
		"shift_jis"	=> "ja_JP", // 392
		"sjis"		=> "ja_JP",
		"ujis"		=> "ja_JP",
		"euc_jp"	=> "ja_JP",
		"x-euc"		=> "ja_JP",
		"x-sjis"	=> "ja_JP",
		"ms_kanji"	=> "ja_JP",
		"euc-kr"	=> "ko_KR", // 410
		"johab"		=> "ko_KR",
		"uhc"		=> "ko_KR",
		"gbk"		=> "zh_CN", // 156 China, People's Republic of
		"cp936"		=> "zh_CN",
		"ms936"		=> "zh_CN",
		"gb18030"	=> "zh_CN",
		"gb2312"	=> "zh_CN",
		"hz"		=> "zh_CN",
		"big5-hkscs"	=> "zh_HK", // 344 Hong Kong, Special Administrative Region of China
		"big5"		=> "zh_TW", // 158 Taiwan, Province of China
		"euc-tw"	=> "zh_TW",
		"tis-620"	=> "th_TH",
		"windows-874"	=> "th_TH",
		"iso-8859-11"	=> "th_TH",
		"tcvn"		=> "vi_VN",
		"vps"		=> "vi_VN",
		"koi8-u"	=> "uk_UA",
	);

	var $flag = array(
		"jp" => "ja",
		"kr" => "ko",
		"tw" => "zh-TW",
		"de" => "de",
		"fr" => "fr",
		"uk" => "en",
		"co" => "es",
		"es" => "es,ca,gl,eu",
		"it" => "it",
		"se" => "sv",
		"ch" => "de,en,fr,it",
		"ca" => "en,fr",
		"mx" => "es",
		"il" => "iw",
		"nl" => "nl",
		"be" => "nl,fr,de,en",
		"cl" => "es",
		"au" => "en",
		"id" => "id,en,nl,jw",
		"ar" => "es",
		"pa" => "es,en",
		"at" => "de",
		"pl" => "pl",
		"dk" => "da,fo",
		"ru" => "ru",
		"br" => "pt-BR",
		"nz" => "en",
		"fi" => "fi,sv",
		"in" => "en,hi,bn,te,mr,ta",
		"th" => "th,en",
		"ph" => "tl,en",
		"pt" => "pt-PT",
		"no" => "no,nn",
		"lt" => "lt",
		"ua" => "uk,ru",
		"lu" => "de,fr",
		"za" => "en,af,st,zu,xh",
		"pk" => "en,ur,pa",
		"do" => "es",
		"cr" => "es,en",
		"lv" => "lv,lt,ru",
		"vn" => "vi,en,fr,zh-TW",
		"ie" => "en,ga",
		"my" => "en,ms",
		"ae" => "ar,ur,en,hi,fa",
		"gr" => "el",
		"sk" => "sk,hu",
		"sa" => "ar",
		"ec" => "es",
		"gt" => "es",
		"sg" => "en,zh-CN,ms,ta",
		"ve" => "es",
		"pe" => "es",
		"ro" => "ro,hu,de",
		"hk" => "en,zh-TW",
		"tr" => "tr",
		"hu" => "hu",
		"pr" => "es,en",
		"bz" => "en,es",
		"sv" => "es",
		"mt" => "mt,en",
		"tt" => "en,hi,fr,es,zh-TW",
		"uy" => "es",
		"bo" => "es",
		"li" => "de",
		"np" => "ne,en",
		"cu" => "es",
		"hn" => "es",
		"ni" => "es,en",
		"py" => "es",
		"ci" => "fr",
		"ly" => "ar,it,en",
		"gl" => "da,en",
		"az" => "az,ru",
		"kz" => "ru",
		"ke" => "en,sw",
		"ug" => "en",
		"fj" => "en",
		"jm" => "en",
		"mn" => "mn",
		"na" => "en,af",
		"am" => "hy,ru",
		"ag" => "en",
		"vi" => "en",
		"vg" => "en",
		"sm" => "it",
		"mu" => "en,fr",
		"bi" => "fr",
		"as" => "en",
		"uz" => "uz,ru",
		"kg" => "ky,ru",
		"rw" => "en,fr,sw",
		"gi" => "en,es,it,pt-PT",
		"ls" => "en,zu",
		"tm" => "tk,ru,uz",
		"ai" => "en",
		"vc" => "en",
		"sc" => "en,fr",
		"mw" => "en",
		"fm" => "en",
		"ms" => "en",
		"nf" => "en",
		"sh" => "en",
		"cd" => "fr",
		"gg" => "en,fr",
		"to" => "en",
		"je" => "en,fr",
		"gm" => "en",
		"cg" => "fr",
		// "td" => "",
		"dj" => "fr,ar",
		"pn" => "en",
		"ck" => "en",
	);

	/*
	 * get_accept_language
	 *
	 * HTTP_ACCEPT_LANGUAGE の文字列を分解する。
	 * @static
	 * @return	array
	 */
	function get_accept_language()
	{
		if ( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) return "";
		$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		// $accept_language = "ja,fr;q=0.7,de;q=0.3";
		return accept_language::split_str($accept_language);
	}

	/*
	 * get_user_agent_mozilla
	 * USER-AGENT から最近の Mozilla の場合
	 * 設定されているlocale文字列を取得する
	 * @static
	 * @return	array
	 */
	function get_user_agent_mozilla()
	{
		if ( !isset($_SERVER['HTTP_USER_AGENT']) ) return "";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		// $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.7.5) Gecko/20041119 Firefox/1.0";
		// $user_agent = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; ja-jp) AppleWebKit/125.2 (KHTML, like Gecko) Safari/125.8";
		$rc = array();
		preg_match("'Mozilla.*? \((.*?)\) .*?'si",$user_agent,$regs);
		if ( count($regs) < 2) return "";
		foreach(split(";",$regs[1]) as $x) {
			$str = trim($x);
			$i = strlen($str);
			switch($i) {
			case 5:
				$x1 = split("-",$str);
				if ( count($x1) == 2) {
					$rc[] = array($str,1);
				}
				break;
			case 2:
				$rc[] = array($str,1);
				break;
			}
		}
		return $rc;
	}

	/*
	 * get_accept_charset
	 *
	 * HTTP_ACCEPT_CHARSET で設定される利用可能な
	 * 文字コードから言語を見做し判定する
	 * @return	array
	 */
	function get_accept_charset()
	{
		if ( !isset($_SERVER['HTTP_ACCEPT_CHARSET']) ) return "";
		$accept_charset = $_SERVER['HTTP_ACCEPT_CHARSET'];
		// $accept_charset = "Shift_JIS,utf-8;q=0.7,*;q=0.7";
		$tmp = accept_language::split_str($accept_charset);
		$rc = array();
		foreach($tmp as $x) {
			$chr = strtolower( $x[0] );
			if (array_key_exists($chr,$this->charset)) {
				$rc[] = array($this->charset[$chr],$x[1]);
			}
		}
		return $rc;
	}

	/*
	 * get_remote_addr
	 * IPアドレスから国を特定し、見做し言語を判定する
	 * @return	array
	 */
	function get_remote_addr()
	{
		if ( !isset($_SERVER['REMOTE_ADDR']) ) return "";
		$ip = $_SERVER['REMOTE_ADDR'];
		$host = gethostbyaddr($ip);
		if ($ip == $host) return "";
		$x = substr($host,strrpos($host, '.')+1);
                $x = strtolower($x);
                if (isset($this->flag[$x]))
			return accept_language::split_str($this->flag[$x], FALSE);
                return "";
	}

	/*
	 * split_str
	 *
	 * x1,x2;q=0.6,x3;q=0.4 のような書式を分解する
	 * @static
	 * @return array
	 * $rc[0] = (x1,1),(x2,0.6),(x3,0.4) が入る。
	 * 値順に整列して戻す。
	 */
	function split_str($env, $sort = TRUE)
	{
		$rc = array();
		foreach( split(",",$env) as $x ) {
			$x1 = split(";", $x);
			// "",1 の "" は、DUMMY
			$q = (count($x1) == 1) ? array("",1) : split("=",$x1[1]);
			$rc[] = array( $x1[0], $q[1]);
		}
		if ($sort) {
			usort($rc,create_function('$a,$b','return ($a[1] == $b[1]) ? 0 : (($a[1] > $b[1]) ? -1 : 1);'));
		}
		return $rc;
	}
}

?>
