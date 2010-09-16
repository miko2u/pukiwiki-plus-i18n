<?php
/**
 * GETTEXT EMULATION FUNCTION
 *
 * @copyright   Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: gettext.php,v 0.8 2005/04/10 18:41:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link        http://jo1upk.blogdns.net/saito/
 *
 * == USE THIS FUNCTION ==
 * if (! extension_loaded('gettext')) {
 * 	require('gettext.php');
 * } else {
 * 	function N_($message) { return $message; }
 * 	if (! function_exists('bind_textdomain_codeset')) {
 * 		function bind_textdomain_codeset($domain, $codeset) { return; }
 * 	}
 * }
 *
 *
 * == Emulation function ==
 * function bindtextdomain($domain, $directory)
 * function textdomain($text_domain)
 * function bind_textdomain_codeset($domain, $codeset)
 * function _($message)
 * function gettext($message)
 * function N_($message)
 * function gettext_noop($message)
 * function dgettext($domain, $message)
 * function ngettext($msgid1,$msgid2,$n)
 * function dngettext($domain, $msgid1, $msgid2, $n)
 * function dcgettext($domain, $message, $category)
 * fcuntion dcngettext($domain, $msgid1, $msgid2, $n, $category)
 *
 * == Add-On function ==
 * function msgunfmt()
 *
 * ./translations_path/ja_JP/LC_MESSAGES/your_text_domain.mo
 * ------------------- ----------- ----------- -------------------
 * $directory          $locale  FIXED    $domain
 * function setlocale($category, $locale)
 * function bindtextdomain($domain, $directory)
 *
 */

if (! defined('_MAGIC')) {
	define('_MAGIC', 0x950412de);		// The magic number of the GNU message catalog format.
}
if (! defined('SEGMENTS_END')) {
	// define('SEGMENTS_END', 0xFFFFFFFF);	// Marker for the end of the segments[] array.
	define('SEGMENTS_END', ~0);		// Marker for the end of the segments[] array.
}

if (! defined('MO_LITTLE_ENDIAN')) {
	define('MO_LITTLE_ENDIAN', 0);		// Intel system
}
if (! defined('MO_BIG_ENDIAN')) {
	define('MO_BIG_ENDIAN', 1);		// POWER system
}

/*
 * if (! defined('PO_LANG')) {
 *	define('PO_LANG', 'ja_JP');		// locale name specified by setlocale().
 * }
 * if (! defined('SOURCE_ENCODING')) {
 * 	define('SOURCE_ENCODING', 'EUC-JP');	// A character code to output. Change by bind_textdomain_codeset() is possible.
 * }
 */

/*
 * setlocale
 * string setlocale ( mixed category, string locale)
 */

/*
 * bindtextdomain
 * @param	string $domain
 * @param	string $directory
 * @return	string
 */
function bindtextdomain($domain, $directory)
{
	global $po_filename, $po_category;

	if (! isset($po_category)) $po_category = 'LC_MESSAGES';

	if (isset($po_filename[$domain][$po_category])) return;

	$locale = (defined('PO_LANG')) ? PO_LANG : 'ja_JP';	// locale name specified by setlocale().
	$filename_pref = $directory . '/';
	$filename_suff = '/' . $po_category . '/' . $domain . '.mo';

	$po_filename[$domain][$po_category] = $filename_pref . $locale . $filename_suff;
	if (file_exists($po_filename[$domain][$po_category])) return realpath($directory);

	// ex. ja_JP.eucJP => ja_JP
	list($lang1) = explode('.',$locale);
	if ($locale != $lang1) {
		$po_filename[$domain][$po_category] = $filename_pref . $lang1 . $filename_suff;
		if (file_exists($po_filename[$domain][$po_category])) return realpath($directory);
	}

	// ex. ja_JP => ja
	list($lang2) = explode('_',$lang1);
	if ($lang1 == $lang2) return realpath($directory);
	$po_filename[$domain][$po_category] = $filename_pref . $lang2 . $filename_suff;

	return realpath($directory);
}

/*
 * textdomain
 * @param 	string $text_domain Text Domain Name
 * @return	string
 */
function textdomain($text_domain)
{
	global $po_msg, $po_filename, $po_category;
	global $po_charset;
	global $po_domain; // Current Domain

	$po_domain = $text_domain; // The change of a domain

	if (isset($po_msg[$po_domain][$po_category])) return $text_domain;
	// A check of finishing [ execution of bindtextdomain ]
	if (!isset($po_filename[$po_domain][$po_category])) return $text_domain;

	$obj = new read_mo();
	$po_msg[$po_domain][$po_category] = $obj->read_mo_file($po_filename[$po_domain][$po_category]);
	$po_charset[$po_domain][$po_category] = (! empty($po_msg[$po_domain][$po_category])) ? $obj->property_charset() : '';
	$obj->destruct();
	unset($obj);
	return $text_domain;
}

/*
 * bind_textdomain_codeset
 * @param	string $domain
 * @param	string $codeset
 * @return	string
 */
function bind_textdomain_codeset($domain, $codeset)
{
	global $po_codeset;
	$po_codeset[$domain] = strtoupper($codeset);
	return $po_codeset[$domain];
}

/*
 * gettext , _
 * @param	string $message
 * @return	string
 */
function _($message)
{
	global $po_msg, $po_charset, $po_codeset, $po_domain, $po_category;

	$msg = $message;
	$msg = str_replace('\"','__DUMMY__', $msg);
	$msg = str_replace('"','\"', $msg);
	$msg = str_replace('__DUMMY__', '\"', $msg);

	if (!isset($po_msg[$po_domain][$po_category][$msg])) return $message;
	if (!isset($po_codeset[$po_domain])) {
		$_enc = (defined('SOURCE_ENCODING')) ? SOURCE_ENCODING : 'EUC-JP';
		$po_codeset[$po_domain] = strtoupper($_enc);
	}

	$charset = (empty($po_charset[$po_domain][$po_category])) ? 'auto' : $po_charset[$po_domain][$po_category];

	if ($po_charset[$po_domain][$po_category] != $po_codeset[$po_domain]) {
		// return mb_convert_encoding($po_msg[$po_domain][$po_category][$msg], $po_codeset[$po_domain], $charset);
		return mb_convert_encoding( str_replace('\"','"', $po_msg[$po_domain][$po_category][$msg]), $po_codeset[$po_domain], $charset);
	}

	// Since a character code is coincidence, it is conversion needlessness.
	// return $po_msg[$po_domain][$po_category][$msg]; 
	return str_replace('\"','"', $po_msg[$po_domain][$po_category][$msg]);
}
function gettext($message) { return _($message); }	// Alias
function N_($message) { return $message; }		// gettext_noop($message)
function gettext_noop($message) { return N_($message); }

/*
 * dgettext
 * @param	string $domain
 * @param	string $message
 * @return	string
 */
function dgettext($domain, $message)
{
	global $po_domain; // Current Domain
	$bkup_domain = $po_domain;

	textdomain($domain);
	$rc = _($message);
	textdomain($bkup_domain);

	unset($bkup_domain);
	return $rc;
}

/*
 * ngettext
 * @param	string	$msgid1
 * @param	string	$msgid2
 * @param	int	$n
 * @return	string
 */
function ngettext($msgid1, $msgid2, $n)
{
	return ($n % 2) ? sprintf(_($msgid1), $n) : sprintf(_($msgid2), $n);
}

/*
 * dngettext
 * @param	string $domain
 * @param	string $msgid1
 * @param	string $msgid2
 * @param	int    $n
 * @return	string
 */
function dngettext($domain, $msgid1, $msgid2, $n)
{
	global $po_domain; // Current Domain
	$bk_domain = $po_domain;

	textdomain($domain);
	$rc = ngettext($msgid1, $msgid2, $n);
	textdomain($bk_domain);

	unset($bk_domain);
	return $rc;
}

/*
 * dcgettext
 * @param	string $domain
 * @param	string $message
 * @param	int    $category
 * @return	string
 */
function dcgettext($domain, $message, $category)
{
	global $po_msg, $po_filename, $po_category;
	global $po_charset;
	global $po_domain; // Current Domain

	$str_category = _po_set_category_file($domain, $category);
	if (! $str_category) return $message;

	$bk_category = $po_category;
	$po_category = $str_category;

	$rc = dgettext($domain, $message);

	$po_category = $bk_category;

	unset($bk_category);
	return $rc;
}

/*
 * dcngettext
 * @param	string $domain
 * @param	string $msgid1
 * @param	string $msgid2
 * @param	int    $n
 * @param	int    $category
 * @return	string
 */
function dcngettext($domain, $msgid1, $msgid2, $n, $category)
{
	global $po_msg, $po_filename, $po_category;
	global $po_charset;
	global $po_domain; // Current Domain

	$str_category = _po_set_category_file($domain, $category);
	if (! $str_category) return ($n % 2) ? sprintf($msgid1, $n) : sprintf($msgid2, $n);

	// locale backup
	$bk_category = $po_category;
	$po_category = $str_category;

	$rc = dngettext($domain, $msgid1, $msgid2, $n);

	$po_category = $bk_category;
	unset($bk_category);

	return $rc;
}

function _po_set_category_file($domain, $category)
{
	global $po_filename, $po_domain;

	if (! isset($po_filename[$domain]['LC_MESSAGES'])) return FALSE;

	$str_category = read_mo::int2category($category);
	if (empty($str_category)) return FALSE;
	if ($str_category == 'LC_ALL')  return FALSE;

	$tmp_filename = str_replace('/LC_MESSAGES/', '/' . $str_category . '/', $po_filename[$domain]['LC_MESSAGES']);

	if (! file_exists($tmp_filename)) return FALSE;
	$po_filename[$domain][$str_category] = $tmp_filename;

	return $str_category;
}

/*
 * msgunfmt
 * @return	string
 */
function msgunfmt()
{
	global $po_msg, $po_domain, $po_category;
	if (isset($po_msg[$po_domain][$po_category])) return '';
	return read_mo::msgunfmt($po_msg[$po_domain][$po_category]);
}

/**
 * GMO reading processing
 * @abstract
 */
class read_mo
{
	var $filename;
	var $data;
	var $size;
	var $fd;
	var $endian;
	var $charset;

	// function read_mo() { __construct(); }
	// function __construct() { }

	// function __destruct()
	function destruct()
	{
		  unset($this->filename, $this->data, $this->size,
			$this->fd, $this->endian, $this->charset);
	}

	function read_mo_file($filename)
	{
		if (! $this->read_binary_mo_file($filename)) return array();
		$this->get_endian();
		
		$revision = read_mo::GET_HEADER_FIELD('revision');
		$revision = read_mo::hex2dec($revision);

		if (($revision >> 16) != 0) return array();

		// Fill the header parts that apply to major revision 0.
		$header = array();
		foreach(  array('nstrings',
				'orig_tab_offset',
				'trans_tab_offset',
				'hash_tab_size',
				'hash_tab_offset') as $_key) {
			$header[$_key] = read_mo::GET_HEADER_FIELD($_key);
			$header[$_key] = read_mo::hex2dec($header[$_key]);
		}

		$po = array();
		for ($i = 0; $i < $header['nstrings']; $i++) {
			// Read the msgid.
			$msgid  = read_mo::get_string($header['orig_tab_offset']  + $i * 8);
			// Read the msgstr.
			$msgstr = read_mo::get_string($header['trans_tab_offset'] + $i * 8);

			// It changes into the character sequence for coding.
			$msgid  = read_mo::conversion_special_character($msgid);
			$msgstr = read_mo::conversion_special_character($msgstr);

			$po[$msgid] = $msgstr;
		}

		// FIXME: read_mo::get_sysdep_string
		// if ($revision & 0xffff) != 1) return $po;
		if (~$revision != 1) return $po;

		/* Fill the header parts that apply to minor revision >= 1.  */
		foreach(  array('n_sysdep_segments',
				'sysdep_segments_offset',
				'n_sysdep_strings',
				'orig_sysdep_tab_offset',
				'trans_sysdep_tab_offset') as $_key) {
			$header[$_key] = read_mo::GET_HEADER_FIELD($_key);
			$header[$_key] = read_mo::hex2dec($header[$_key]);
			//print $_key." =".$header[$_key]."\n";
		}

		for ($i = 0; $i < $header['n_sysdep_strings']; $i++) {
			/* Read the msgid.  */
			$offset = read_mo::get_uint32($header['orig_sysdep_tab_offset']  + $i * 4);
			$offset = read_mo::hex2dec($offset);
			$msgid  = read_mo::get_sysdep_string($offset, $header);
			
			/* Read the msgstr.  */
			$offset = read_mo::get_uint32($header['trans_sysdep_tab_offset'] + $i * 4);
			$offset = read_mo::hex2dec($offset);
			$msgstr = read_mo::get_sysdep_string($offset, $header);
			
			if (empty($msgid)) continue;
			$po[$msgid] = $msgstr;
		}

		read_mo::get_charset($po);
		return $po;
	}

	/*
	 * A charset is obtained.
	 * @static
	 */
	function get_charset($po)
	{
		if (! isset($po[''])) return;
		// "Content-Type: text/plain; charset=EUC-JP\n"
		$this->charset = (preg_match('#charset=(.+)$#mi', $po[''], $regs)) ? $regs[1] : 'auto';
		$this->charset = strtoupper($this->charset);
	}
	function property_charset() { return $this->charset; }

	/* Get a system dependent string from the file, at the given file position.  */
	function get_sysdep_string($offset,$header)
	{
		/* Compute the length.  */
		$length = 0;
		for ($i = 4; ; $i += 8) {
			$segsize   = read_mo::get_uint32($offset + $i);
			$segsize   = read_mo::hex2dec($segsize);
			$sysdepref = read_mo::get_uint32($offset + $i + 4);
			$sysdepref = read_mo::hex2dec($sysdepref);

			$length += $segsize;

			if ($sysdepref == SEGMENTS_END) break;

			// Invalid.
			// file is not in GNU .mo format
			if ($sysdepref >= $header['n_sysdep_segments']) return '';

			// See 'struct sysdep_segment'.
			$sysdep_segment_offset = $header['sysdep_segments_offset'] + $sysdepref * 8;
			$ss_length = read_mo::get_uint32($sysdep_segment_offset);
			$ss_length = read_mo::hex2dec($ss_length);
			$ss_offset = read_mo::get_uint32($sysdep_segment_offset + 4);
			$ss_offset = read_mo::hex2dec($ss_offset);

			// File is truncated.
			if ($ss_offset + $ss_length > $this->size) return '';

			// File contains a not NUL terminated string, at sysdep_segment[ $sysdepref ]
			if ($ss_length = 0 || $this->data[$ss_offset + $ss_length - 1] != '\0')
				return '';
			$length += 1 + strlen($this->data + $ss_offset) + 1;
		}

		/* Allocate and fill the string.  */
		$p = '';
		$s_offset = read_mo::get_uint32($offset);
		$s_offset = read_mo::hex2dec($s_offset);

		for ($i = 4; ; $i += 8) {
			$segsize   = read_mo::get_uint32($offset + $i);
			$segsize   = read_mo::hex2dec($segsize);
			$sysdepref = read_mo::get_uint32($offset + $i + 4);
			$sysdepref = read_mo::hex2dec($sysdepref);

			// error (EXIT_FAILURE, 0, _('file "%s" is truncated'), $this->filename);
			if ($s_offset + $segsize > $this->size) return '';
			//memcpy(p, $this->data + $s_offset, $segsize);
			//$p += $segsize;
			$s_offset += $segsize;

			if ($sysdepref == SEGMENTS_END) break;
			if ($sysdepref >= $header['n_sysdep_segments']) return '';
			/* See 'struct sysdep_segment'.  */
			$sysdep_segment_offset = $header['sysdep_segments_offset'] + $sysdepref * 8;
			$ss_length = read_mo::get_uint32($sysdep_segment_offset);
			$ss_length = read_mo::hex2dec($ss_length);
			$ss_offset = read_mo::get_uint32($sysdep_segment_offset + 4);
			$ss_offset = read_mo::hex2dec($ss_offset);
			if ($ss_offset + $ss_length > $this->size) return '';
			if ($ss_length = 0 || $this->data[$ss_offset + $ss_length - 1] != '\0') return '';
			$n = strlen($this->data + $ss_offset);
			$p .= '<';
			$p .= substr($this->data, $ss_offset, $n);
			$p .= '>';
		}
		return $p;
	}

	function bin2hex($c) { return sprintf('%x', $c); }
	function hex2dec($c) { return ('0x' . $c) * 1; }

	/*
	 * Read the contents of the given input stream.
	 * @return	bool
	 */
	function read_binary_mo_file($filename)
	{
		if (! file_exists($filename)) return FALSE;

		$fd = @fopen($filename, 'r');
		if($fd === FALSE) return FALSE;

		@flock($fd, LOCK_SH);
		$this->size = filesize($filename);
		$this->data = fread($fd, $this->size);
		@flock($fd, LOCK_UN);
		fclose($fd);

		$this->filename = $filename;
		return TRUE;
	}

	/*
	 * The judgment of an endian.
	 */
	function get_endian()
	{
		$_magic = read_mo::bin2hex(_MAGIC);

		$this->endian = MO_LITTLE_ENDIAN; // Set before GET_HEADER_FIELD
		$magic = read_mo::GET_HEADER_FIELD('magic');
		// settype($x, "double");
		if ($magic == $_magic) return TRUE; // Little

		$this->endian = MO_BIG_ENDIAN; // Set before GET_HEADER_FIELD
		$magic = read_mo::GET_HEADER_FIELD('magic');
		if ($magic == $_magic) return TRUE; // Big

		return FALSE;
	}

	/*
	 * Get a 32-bit number from the file, at the given file position.
	 */
	function get_uint32($offset)
	{
		if ($offset + 4 > $this->size) return 0; // ERROR

		$b0 = ord( substr($this->data, $offset + 0, 1) );
		$b1 = ord( substr($this->data, $offset + 1, 1) );
		$b2 = ord( substr($this->data, $offset + 2, 1) );
		$b3 = ord( substr($this->data, $offset + 3, 1) );

		if ($this->endian == MO_LITTLE_ENDIAN) {
			$rc = $b0 | ($b1 << 8) | ($b2 << 16) | ($b3 << 24);
		} else {
			$rc = ($b0 << 24) | ($b1 << 16) | ($b2 << 8) | $b3;
		}

		// return $rc;
		return read_mo::bin2hex($rc);
	}

	/*
	 * Get a 32-bit number from the file header.
	 * @static
	 */
	function GET_HEADER_FIELD($field) {
		return read_mo::get_uint32(read_mo::offsetof($field));
	}

	/*
	 * Offset of a header is returned.
	 * @static
	 */
	function offsetof($field)
	{
		static $mo_file_header = array();

		// gettext-0.13/gettext-runtime/intl/gmo.h
		// nsl_unit32
		// unsigned short	2	0<=X<=65,535
		// unsigned long	4	0<=X<=4,294,967,295
		// FIXME:
		static $base = 4;

		if(empty($mo_file_header)) $mo_file_header = array(
			'magic'			=> $base * 0,	// The magic number.
			'revision'		=> $base * 1,	// The revision number of the file format.
			
			// The following are only used in .mo files with major revision 0.
			'nstrings'		=> $base * 2,	// The number of strings pairs.
			'orig_tab_offset'	=> $base * 3,	// Offset of table with start offsets of original strings.
			'trans_tab_offset'	=> $base * 4,	// Offset of table with start offsets of translated strings.
			'hash_tab_size'		=> $base * 5,	// Size of hash table.
			'hash_tab_offset'	=> $base * 6,	// Offset of first hash table entry.
			
			// The following are only used in .mo files with minor revision >= 1.
			'n_sysdep_segments'	=> $base *  8,	// The number of system dependent segments.
			'sysdep_segments_offset'=> $base * 10,	// ffset of table describing system dependent segments.
			'n_sysdep_strings'	=> $base * 12,	// The number of system dependent strings pairs.
			'orig_sysdep_tab_offset'=> $base * 14,	// Offset of table with start offsets of original sysdep strings.
			'trans_sysdep_tab_offset'=>$base * 16,	// Offset of table with start offsets of translated sysdep strings.
			// FIXME:
			//'n_sysdep_segments'	=> $base * 2,	// The number of system dependent segments.
			//'sysdep_segments_offset'=> $base * 3,	// ffset of table describing system dependent segments.
			//'n_sysdep_strings'	=> $base * 4,	// The number of system dependent strings pairs.
			//'orig_sysdep_tab_offset'=> $base * 5,	// Offset of table with start offsets of original sysdep strings.
			//'trans_sysdep_tab_offset'=>$base * 6,	// Offset of table with start offsets of translated sysdep strings.

		);

		return (isset($mo_file_header[$field])) ? $mo_file_header[$field] : 0;
	}

	/*
	 * Get a static string from the file, at the given file position.
	 * @return	string
	 */
	function get_string($offset)
	{
		/* See 'struct string_desc'.  */
		$s_length = read_mo::get_uint32($offset);
		$s_length = read_mo::hex2dec($s_length);
		$s_offset = read_mo::get_uint32($offset + 4);
		$s_offset = read_mo::hex2dec($s_offset);

		return substr($this->data, $s_offset, $s_length);
	}

	/*
	 * msgunfmt
	 * @static
	 */
	function msgunfmt($po)
	{
		$rc = '';
		foreach($po as $msgid => $msgstr) {
			$rc .= read_mo::message_list('msgid',  $msgid);
			$rc .= read_mo::message_list('msgstr', $msgstr);
		}
		return $rc;
	}

	/*
	 * A special character is returned.
	 * @return	string
	 */
	function conversion_special_character($x)
	{
		static $table = array();
		if(empty($table)) $table = array(
			"\t" => "\\t",	// TAB
			"\\" => "\\\\",	// \
			"\"" => "\\\"",	// "
		);

		foreach($table as $org => $new) { $x = str_replace($org, $new, $x); }
		return $x;
	}

	/*
	 * Processing in case there is a new-line.
	 * @return	string
	 */
	function message_list($hed, $str)
	{
		$x = explode("\n", $str);
		if (count($x) == 1) return $hed . ' "' . $str . "\"\n";

		$rc = $hed . " \"\"\n";
		foreach($x as $_x) {
			if (empty($_x)) {
				$rc .= "\n";
				continue;
			}
			$rc .= '"' . $_x . "\\n\"\n";
		}
		return $rc;
	}

	/*
	 * int2category
	 * @param	int $category
	 * @static
	 * @return	array
	 */
	function int2category($category)
	{
		$cat = array();
		$cat['LC_CTYPE']	= ( defined('LC_CTYPE') )	? LC_CTYPE	: -1;
		$cat['LC_NUMERIC']	= ( defined('LC_NUMERIC') )	? LC_NUMERIC	: -1;
		$cat['LC_TIME']		= ( defined('LC_TIME') )	? LC_TIME	: -1;
		$cat['LC_COLLATE']	= ( defined('LC_COLLATE') )	? LC_COLLATE	: -1;
		$cat['LC_MONETARY']	= ( defined('LC_MONETARY') )	? LC_MONETARY	: -1;
		$cat['LC_MESSAGES']	= ( defined('LC_MESSAGES') )	? LC_MESSAGES	: -1;
		$cat['LC_PAPER']	= ( defined('LC_PAPER') )	? LC_PAPER	: -1;
		$cat['LC_NAME']		= ( defined('LC_NAME') )	? LC_NAME	: -1;
		$cat['LC_ADDRESS']	= ( defined('LC_ADDRESS') )	? LC_ADDRESS	: -1;
		$cat['LC_TELEPHONE']	= ( defined('LC_TELEPHONE') )	? LC_TELEPHONE	: -1;
		$cat['LC_MEASUREMENT']	= ( defined('LC_MEASUREMENT') )	? LC_MEASUREMENT: -1;
		$cat['LC_IDENTIFICATION'] = ( defined('LC_IDENTIFICATION') ) ? LC_IDENTIFICATION : -1;
		$cat['LC_ALL']		= ( defined('LC_ALL') )		? LC_ALL : -1;

		foreach($cat as $key => $val) {
			// if (!strstr($key,$val)) {
			if ($val > 0) {
				if ($category == $val) return $key;
			}
		}
		return '';
	}

}
?>
