<?php
/**
 * Language judgment (言語判定)
 *
 * @copyright   Copyright &copy; 2005-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: lang.php,v 0.24 2006/06/24 23:05:00 upk Exp $
 *
 */

// CORRESPONDENCE LANGUAGE : 対応言語
// == CASE SENSITIVE ==    : 大文字小文字を区別
$language_prepared = array('ja_JP', 'zh_TW', 'zh_CN',  'en_US', 'ko_KR');
$language = '';

/*
 * set_language
 *
 */
function set_language()
{
	global $language_considering_setting_level;
	global $language;
	global $public_holiday_guest_view;
	global $script;

	$language = get_language($language_considering_setting_level);

	// LANG - Internal content encoding ('en', 'ja', or ...)
	define('LANG', $language);

	// Set COOKIE['lang']
	$parsed_url = parse_url($script);
	$path = $parsed_url['path'];
	if (($pos = strrpos($path, '/')) !== FALSE) {
		$path = substr($path, 0, $pos + 1);
	}
	setcookie('lang', $language, 0, $path);
	$_COOKIE['lang'] = $language;

	// PUBLIC HOLIDAY
	// Installation person's calendar is adopted.
	if ( $public_holiday_guest_view ) {
		$_c = split('_', $language);
	} else {
		// 設置者のカレンダーを採用
		$_c = split('_', DEFAULT_LANG);
	}
	define('COUNTRY', $_c[1]);
	unset($_c);

	// FIXME:
	// UI_LANG - Content Language for buttons, menus,  etc
	define('UI_LANG', LANG); // 'en' for Internationalized wikisite
	// LANG_ENCODING - content encoding ('', 'UTF-8', or ...)
	define('LANG_ENCODING', 'UTF-8');

	// I18N

	// LOCALE Name specified by GETTEXT().
	define('DOMAIN', 'pukiwiki');
	// LOCALE Name specified by SETLOCALE().
	if (! defined('PO_LANG')) {
		define('PO_LANG', $language); // 'en_US', 'ja_JP'
	}

	// PHP mbstring process.
	set_mbstring($language);
}

/*
 * set_mbstring
 *
 */
function set_mbstring($lang)
{
       	// Internal content encoding = Output content charset (for skin)
	define('CONTENT_CHARSET', get_content_charset($lang) ); // 'UTF-8', 'iso-8859-1', 'EUC-JP' or ...
	// Internal content encoding (for mbstring extension)
	define('SOURCE_ENCODING', get_source_encoding($lang) );  // 'UTF-8', 'ASCII', or 'EUC-JP'

	mb_language( get_mb_language($lang) );

	mb_internal_encoding(SOURCE_ENCODING);
	ini_set('mbstring.http_input', 'pass');
	mb_http_output('pass');
	mb_detect_order('auto');
}

/*
 * get_language
 *
 */
function get_language($level = 0)
{
	global $language_prepared;

	if ($level == 0) return DEFAULT_LANG;

	$lng_func = array(
		'get_cookie_lang',		// 1 return ja,ja_JP
		'get_accept_language',		// 2 return ja,ko
		'get_user_agent_mozilla',	// 3 return ja,ja_JP
		'get_accept_charset',		// 4 return ja_JP
		'get_remote_addr',		// 5 return ja
	);

	$obj_lng = new accept_language();
	$level = ($level > count($lng_func)) ? count($lng_func) : $level;
	$obj_l2c = new lang2country();

	for($i=0; $i < $level; $i++){
		if ($i == $level) return DEFAULT_LANG;
		// 指定関数の実行
		$_x = $obj_lng->$lng_func[$i]();
		if (! is_array($_x)) continue;

		foreach($_x as $_lang) {
			// 完全一致の場合 (ex. ja_JP)
			if (in_array($_lang[0], $language_prepared)) return $_lang[0];
			// 言語のみの場合の対応
			$_x1 = split('_', $_lang[0]);
			if ( count($_x1) == 2) continue;
			$c = $obj_l2c->get_lang2country($_x1[0]);
			if (empty($c)) continue;
			$str = $_x1[0].'_'.$c;
			if (in_array($str, $language_prepared)) return $str;
		}
	}
	return DEFAULT_LANG;
}

/*
 * get_language_header_vary
 *
 */
function get_language_header_vary()
{
	global $language_considering_setting_level;

	if ($language_considering_setting_level < 1) return '';

	$rc = 'Negotiate';
	$vary = array(1=>'Cookie',2=>'Accept-Language',3=>'User-Agent',4=>'Accept-Charset');

	for($i=1;$i<=$language_considering_setting_level;$i++) {
		if (empty($vary[$i])) break;
		if ($rc != '') {
			$rc .= ',';
		}
		$rc .= $vary[$i];
	}
	return $rc;
}

/*
 * get_content_charset
 * @return      string
 */
function get_content_charset($lang)
{
	return 'UTF-8';
/*
	$content_charset = array(
		'en'	=> 'iso-8859-1',
		'de'	=> 'iso-8859-15',
		'ja'	=> 'UTF-8',
		'ko'	=> 'UTF-8',
		'ru'	=> 'koi8r',
		'zh_CN'	=> 'UTF-8',
		'zh_TW' => 'UTF-8',
		'default' => 'UTF-8', // default
	);
	return _lang_keyset($lang,$content_charset);
*/
}

/*
 * get_source_encoding
 * @return      string
 */
function get_source_encoding($lang)
{
	return 'UTF-8';
/*
	$source_encoding = array(
		'en'	=> 'ASCII',
		'default' => 'UTF-8', // default
	);
	return _lang_keyset($lang,$source_encoding);
*/
}

/*
 * get_mb_language
 * @return      string
 */
function get_mb_language($lang)
{
	$mb_language = array(
		'en'	=> 'English',
		'ja'	=> 'Japanese',
		'ko'	=> 'Korean',
		'zh_TW'	=> 'Traditional Chinese',
		'zh_CN'	=> 'Simplified Chinese',
		'de'	=> 'German', // 'Deutsch'
		'ru'	=> 'Russian',
		'default' => 'uni',
	);
	return _lang_keyset($lang,$mb_language);
}

/*
 * _lang_keyset
 * @return      string
 */
function _lang_keyset($lang,$key)
{
	if ( array_key_exists($lang, $key) ) return $key[ $lang ];	// ja_JP 指定のキーは存在するか？
	$x = split('_', $lang);						// ja か ja_JP かの判定
	if ( count($x) == 1) return $key['default'];			// ja のみなら処理を終了
	if ( array_key_exists($x[0], $key) ) return $key[ $x[0] ];	// ja_JP を ja にして再検索
	return $key['default'];
}

/**
 * accept_language
 * @abstract
 *
 * 1) COOKIE['lang']
 * 2) HTTP_ACCEPT_LANGUAGE
 * 3) HTTP_USER_AGENT
 * 4) HTTP_ACCEPT_CHARSET
 * 5) REMOTE_ADDR
 */
class accept_language
{
	// LANGUAGE_COUNTRY is guessed from CHARSET.
	// CHARSET から言語_国を推測する
	var $charset = array(
		'shift_jis'	=> 'ja_JP', // 392
		'sjis'		=> 'ja_JP',
		'ujis'		=> 'ja_JP',
		'euc_jp'	=> 'ja_JP',
		'x-euc'		=> 'ja_JP',
		'x-sjis'	=> 'ja_JP',
		'ms_kanji'	=> 'ja_JP',
		'euc-kr'	=> 'ko_KR', // 410
		'johab'		=> 'ko_KR',
		'uhc'		=> 'ko_KR',
		'gbk'		=> 'zh_CN', // 156 China, People's Republic of
		'cp936'		=> 'zh_CN',
		'ms936'		=> 'zh_CN',
		'gb18030'	=> 'zh_CN',
		'gb2312'	=> 'zh_CN',
		'hz'		=> 'zh_CN',
		'big5-hkscs'	=> 'zh_HK', // 344 Hong Kong, Special Administrative Region of China
		'big5'		=> 'zh_TW', // 158 Taiwan, Province of China
		'euc-tw'	=> 'zh_TW',
		'tis-620'	=> 'th_TH',
		'windows-874'	=> 'th_TH',
		'iso-8859-11'	=> 'th_TH',
		'tcvn'		=> 'vi_VN',
		'vps'		=> 'vi_VN',
		'koi8-u'	=> 'uk_UA',
	);

	// The LANGUAGE used is guessed from the COUNTRY.
	// 国から使用言語を推測する
	var $flag = array(
		'jp' => 'ja',
		'kr' => 'ko',
		'tw' => 'zh',
		'de' => 'de',
		'fr' => 'fr',
		'uk' => 'en',
		'co' => 'es',
		'es' => 'es,ca,gl,eu',
		'it' => 'it',
		'se' => 'sv',
		'ch' => 'de,en,fr,it',
		'ca' => 'en,fr',
		'mx' => 'es',
		'il' => 'iw',
		'nl' => 'nl',
		'be' => 'nl,fr,de,en',
		'cl' => 'es',
		'au' => 'en',
		'id' => 'id,en,nl,jw',
		'ar' => 'es',
		'pa' => 'es,en',
		'at' => 'de',
		'pl' => 'pl',
		'dk' => 'da,fo',
		'ru' => 'ru',
		'br' => 'pt_BR',
		'nz' => 'en',
		'fi' => 'fi,sv',
		'in' => 'en,hi,bn,te,mr,ta',
		'th' => 'th,en',
		'ph' => 'tl,en',
		'pt' => 'pt_PT',
		'no' => 'no,nn',
		'lt' => 'lt',
		'ua' => 'uk,ru',
		'lu' => 'de,fr',
		'za' => 'en,af,st,zu,xh',
		'pk' => 'en,ur,pa',
		'do' => 'es',
		'cr' => 'es,en',
		'lv' => 'lv,lt,ru',
		'vn' => 'vi,en,fr,zh_TW',
		'ie' => 'en,ga',
		'my' => 'en,ms',
		'ae' => 'ar,ur,en,hi,fa',
		'gr' => 'el',
		'sk' => 'sk,hu',
		'sa' => 'ar',
		'ec' => 'es',
		'gt' => 'es',
		'sg' => 'en,zh_CN,ms,ta',
		've' => 'es',
		'pe' => 'es',
		'ro' => 'ro,hu,de',
		'hk' => 'en,zh_TW',
		'tr' => 'tr',
		'hu' => 'hu',
		'pr' => 'es,en',
		'bz' => 'en,es',
		'sv' => 'es',
		'mt' => 'mt,en',
		'tt' => 'en,hi,fr,es,zh_TW',
		'uy' => 'es',
		'bo' => 'es',
		'li' => 'de',
		'np' => 'ne,en',
		'cu' => 'es',
		'hn' => 'es',
		'ni' => 'es,en',
		'py' => 'es',
		'ci' => 'fr',
		'ly' => 'ar,it,en',
		'gl' => 'da,en',
		'az' => 'az,ru',
		'kz' => 'ru',
		'ke' => 'en,sw',
		'ug' => 'en',
		'fj' => 'en',
		'jm' => 'en',
		'mn' => 'mn',
		'na' => 'en,af',
		'am' => 'hy,ru',
		'ag' => 'en',
		'vi' => 'en',
		'vg' => 'en',
		'sm' => 'it',
		'mu' => 'en,fr',
		'bi' => 'fr',
		'as' => 'en',
		'uz' => 'uz,ru',
		'kg' => 'ky,ru',
		'rw' => 'en,fr,sw',
		'gi' => 'en,es,it,pt_PT',
		'ls' => 'en,zu',
		'tm' => 'tk,ru,uz',
		'ai' => 'en',
		'vc' => 'en',
		'sc' => 'en,fr',
		'mw' => 'en',
		'fm' => 'en',
		'ms' => 'en',
		'nf' => 'en',
		'sh' => 'en',
		'cd' => 'fr',
		'gg' => 'en,fr',
		'to' => 'en',
		'je' => 'en,fr',
		'gm' => 'en',
		'cg' => 'fr',
		// 'td' => '',
		'dj' => 'fr,ar',
		'pn' => 'en',
		'ck' => 'en',
	);

	/*
	 * get_cookie_lang
	 * @static
	 * @return	array
	 */
	function get_cookie_lang()
	{
		if (isset($HTTP_COOKIE_VARS['lang'])) {
			$cookie['lang'] = $HTTP_COOKIE_VARS['lang'];
		} elseif (isset($_COOKIE['lang']) ) {
			$cookie['lang'] = $_COOKIE['lang'];
		} else {
			return '';
		}

                if ($cookie['lang'] == 'none') return '';
		$l = accept_language::split_locale_str($cookie['lang']);
		return array(array($l[0],1));
	}

	/*
	 * get_accept_language
	 *
	 * HTTP_ACCEPT_LANGUAGE の文字列を分解する。
	 * @static
	 * @return	array
	 */
	function get_accept_language()
	{
		if ( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) return '';
		$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		// TEST:
		//$accept_language = 'ko,en,ja,fr;q=0.7,DE;q=0.3';
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
		if ( !isset($_SERVER['HTTP_USER_AGENT']) ) return '';
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		// TEST:
		// $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.7.5) Gecko/20041119 Firefox/1.0';
		// $user_agent = 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; ja-jp) AppleWebKit/125.2 (KHTML, like Gecko) Safari/125.8';
		$rc = array();
		preg_match("'Mozilla.*? \((.*?)\) .*?'si",$user_agent,$regs);
		if ( count($regs) < 2) return '';
		foreach(split(';',$regs[1]) as $x) {
			$str = trim($x);
			$i = strlen($str);
			if ($i == 5 || $i == 2) {
				$l = accept_language::split_locale_str($str);
				$rc[] = array($l[0],1);
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
		if ( !isset($_SERVER['HTTP_ACCEPT_CHARSET']) ) return '';
		$accept_charset = $_SERVER['HTTP_ACCEPT_CHARSET'];
		// TEST:
		// $accept_charset = 'Shift_JIS,utf-8;q=0.7,*;q=0.7';
		// 取り扱い文字が、CHARSET列なので言語_国変換を行わない(第2引数)
		$tmp = accept_language::split_str($accept_charset,FALSE);
		$rc = array();
		foreach($tmp as $x) {
			$chr = strtolower( $x[0] ); // Shift_JIS などは shift_jis に変換
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
		if ( !isset($_SERVER['REMOTE_ADDR']) ) return '';
		$ip = $_SERVER['REMOTE_ADDR'];
		$host = gethostbyaddr($ip);
		if ($ip == $host) return '';
		$x = substr($host,strrpos($host, '.')+1);
                $x = strtolower($x);
                if (isset($this->flag[$x]))
			return accept_language::split_str($this->flag[$x], FALSE, FALSE);
                return '';
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
	function split_str($env, $conv=TRUE, $sort=TRUE)
	{
		$rc = array();
		foreach( split(',',$env) as $x ) {
			$x1 = split(';', $x);
			// '',1 の '' は、DUMMY
			$q = (count($x1) == 1) ? array('',1) : split('=',$x1[1]);
			if ($conv) {
				$l = accept_language::split_locale_str($x1[0]);
				$rc[] = array( $l[0], $q[1]);
			} else {
				$rc[] = array( $x1[0], $q[1]);
			}
		}
		if ($sort) {
			usort($rc,create_function('$a,$b','return ($a[1] == $b[1]) ? 0 : (($a[1] > $b[1]) ? -1 : 1);'));
		}
		return $rc;
	}

	/*
	 * split_locale_str
	 *
	 * 言語-国(省略可)の文字列を一律、言語(小文字)、国(大文字)に変換
	 * 言語と国の接続文字は、ハイフンまたはアンダースコアとする
	 * @static
	 * @return string
	 */
	function split_locale_str($str)
	{
		$x = split('[-_]', $str);
		$lang    = strtolower( $x[0] );
		if ( count($x) == 2) {
			$country = strtoupper( $x[1] );
			$join = '_';
		} else {
			$country = '';
			$join = '';
		}
		return array( $lang.$join.$country, $lang, $country );
	}

}

/**
 * lang2country
 * @abstract
 *
 */
class lang2country
{
	// The COUNTRY is guessed from the LANGUAGE.
	// 言語から国を推測する
	var $lang = array(
		'ja' => 'JP',
		'ko' => 'KR',
		'zh' => 'TW',
		'de' => 'DE',
		'fr' => 'FR',
		'en' => 'US',
		'it' => 'IT',
		'lt' => 'LT',
		'pt' => 'PT',
	);

	/*
	 * get_lang2country
	 *
	 * 言語から国を推測する
	 * @return string
	 */
	function get_lang2country($x)
	{
		if (isset($this->lang[$x])) return $this->lang[$x];
		return '';
	}

}
?>
