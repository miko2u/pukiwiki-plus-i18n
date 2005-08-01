<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: init.php,v 1.38.1 2005/07/27 14:13:12 miko Exp $
// Copyright (C)
//   2005      Customized/Patched by Miko.Hoshina
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Init PukiWiki here

// PukiWiki version / Copyright / Licence

//define('S_VERSION', '1.4.6');
define('S_VERSION', '1.4.6-plus-u1');
define('S_COPYRIGHT',
	'<strong>PukiWiki ' . S_VERSION . '</strong>' .
	' Copyright &copy; 2001-2005' .
	' <a href="http://pukiwiki.org/">PukiWiki Developers Team</a>.' .
	' License is <a href="http://www.gnu.org/licenses/gpl.html">GPL</a>.<br />' .
	' Based on "PukiWiki" 1.3 by <a href="http://factage.com/yu-ji/">yu-ji</a>'
);

/////////////////////////////////////////////////
// Init server variables

foreach (array('SCRIPT_NAME', 'SERVER_ADMIN', 'SERVER_NAME',
	'SERVER_PORT', 'SERVER_SOFTWARE') as $key) {
	define($key, isset($_SERVER[$key]) ? $_SERVER[$key] : '');
	unset(${$key}, $_SERVER[$key], $HTTP_SERVER_VARS[$key]);
}

/////////////////////////////////////////////////
// Init grobal variables

$foot_explain = array();	// Footnotes
$related      = array();	// Related pages
$head_tags    = array();	// XHTML tags in <head></head>

/////////////////////////////////////////////////
// Time settings

define('LOCALZONE', date('Z'));
define('UTIME', time() - LOCALZONE);
define('MUTIME', getmicrotime());

/////////////////////////////////////////////////
// Require INI_FILE

define('INI_FILE',  DATA_HOME . 'pukiwiki.ini.php');
$die = '';
if (! file_exists(INI_FILE) || ! is_readable(INI_FILE)) {
	$die .= 'File is not found. (INI_FILE)' . "\n";
} else {
	require(INI_FILE);
}
if ($die) die_message(nl2br("\n\n" . $die));

/////////////////////////////////////////////////
// INI_FILE: LANG ､ﾋｴﾅ､ｯ･ｨ･ｳ｡ｼ･ﾇ･｣･ｰﾀﾟﾄ・
// CONTENT_CHARSET: Internal content encoding = Output content charset (for skin)
//   'UTF-8', 'iso-8859-1', 'EUC-JP' or ...

// MB_LANGUAGE: mb_language (for mbstring extension)
//   'uni'(means UTF-8), 'English', or 'Japanese'

// SOURCE_ENCODING: Internal content encoding (for mbstring extension)
//   'UTF-8', 'ASCII', or 'EUC-JP'

switch (LANG){
case 'en':
	// ASCII
	define('CONTENT_CHARSET', 'iso-8859-1');
	define('MB_LANGUAGE',     'English');
	define('SOURCE_ENCODING', 'ASCII');

	// UTF-8
	//define('CONTENT_CHARSET', 'UTF-8');
	//define('MB_LANGUAGE',     'English');
	//define('SOURCE_ENCODING', 'UTF-8');

	break;
	
case 'ja':
	// EUC-JP
	define('CONTENT_CHARSET', 'EUC-JP');
	define('MB_LANGUAGE',     'Japanese');
	define('SOURCE_ENCODING', 'EUC-JP');
	break;

case 'ko':
	// UTF-8 (See BugTrack2/13 for all hack about Korean support, and give us your report!)
	define('CONTENT_CHARSET', 'UTF-8');
	define('MB_LANGUAGE',     'Korean');
	define('SOURCE_ENCODING', 'UTF-8');
	break;

default:
	die_message('No such language "' . LANG . '"');
}

mb_language(MB_LANGUAGE);
mb_internal_encoding(SOURCE_ENCODING);
ini_set('mbstring.http_input', 'pass');
mb_http_output('pass');
mb_detect_order('auto');

// for SESSION Variables
if (!($_REQUEST['plugin'] != 'attach' && $_REQUEST['pcmd'] != 'open')) {
	if (ini_get('session.auto_start') != 1) {
		session_name('pukiwiki');
		session_start();
	}
}

/////////////////////////////////////////////////
// INI_FILE: Require LANG_FILE

define('LANG_FILE_HINT', DATA_HOME . LANG . '.lng.php');	// For encoding hint
define('LANG_FILE',      DATA_HOME . UI_LANG . '.lng.php');	// For UI resource
$die = '';
foreach (array('LANG_FILE_HINT', 'LANG_FILE') as $langfile) {
	if (! file_exists(constant($langfile)) || ! is_readable(constant($langfile))) {
		$die .= 'File is not found or not readable. (' . $langfile . ')' . "\n";
	} else {
		require_once(constant($langfile));
	}
}
if ($die) die_message(nl2br("\n\n" . $die));

/////////////////////////////////////////////////
// LANG_FILE: Init encoding hint

define('PKWK_ENCODING_HINT', isset($_LANG['encode_hint'][LANG]) ? $_LANG['encode_hint'][LANG] : '');
unset($_LANG['encode_hint']);

/////////////////////////////////////////////////
// LANG_FILE: Init severn days of the week

$weeklabels = $_msg_week;

/////////////////////////////////////////////////
// INI_FILE: Init $script

if (isset($script)) {
	get_script_uri($script); // Init manually
} else {
	$script = get_script_uri(); // Init automatically
}

/////////////////////////////////////////////////
// INI_FILE: $agents:  UserAgent､ﾎｼｱﾊﾌ

$ua = 'HTTP_USER_AGENT';
$user_agent = $matches = array();

$user_agent['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
unset(${$ua}, $_SERVER[$ua], $HTTP_SERVER_VARS[$ua], $ua);	// safety

foreach ($agents as $agent) {
	if (preg_match($agent['pattern'], $user_agent['agent'], $matches)) {
		$user_agent['profile'] = isset($agent['profile']) ? $agent['profile'] : '';
		$user_agent['name']    = isset($matches[1]) ? $matches[1] : '';	// device or browser name
		$user_agent['vers']    = isset($matches[2]) ? $matches[2] : ''; // 's version
		break;
	}
}
unset($agents, $matches);

// Profile-related init and setting
define('UA_PROFILE', isset($user_agent['profile']) ? $user_agent['profile'] : '');

define('UA_INI_FILE', DATA_HOME . UA_PROFILE . '.ini.php');
if (! file_exists(UA_INI_FILE) || ! is_readable(UA_INI_FILE)) {
	die_message('UA_INI_FILE for "' . UA_PROFILE . '" not found.');
} else {
	require(UA_INI_FILE); // Also manually
}

define('UA_NAME', isset($user_agent['name']) ? $user_agent['name'] : '');
define('UA_VERS', isset($user_agent['vers']) ? $user_agent['vers'] : '');
unset($user_agent);	// Unset after reading UA_INI_FILE

/////////////////////////////////////////////////
// ･ﾇ･｣･・ｯ･ﾈ･熙ﾎ･ﾁ･ｧ･ﾃ･ｯ

$die = '';
foreach(array('DATA_DIR', 'DIFF_DIR', 'BACKUP_DIR', 'CACHE_DIR') as $dir){
	if (! is_writable(constant($dir)))
		$die .= 'Directory is not found or not writable (' . $dir . ')' . "\n";
}

// ﾀﾟﾄ・ﾕ･｡･､･・ﾎﾊﾑｿﾁ･ｧ･ﾃ･ｯ
$temp = '';
foreach(array('rss_max', 'page_title', 'note_hr', 'related_link', 'show_passage',
	'rule_related_str', 'load_template_func') as $var){
	if (! isset(${$var})) $temp .= '$' . $var . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Variable(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

$temp = '';
foreach(array('LANG', 'PLUGIN_DIR') as $def){
	if (! defined($def)) $temp .= $def . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Define(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

if($die) die_message(nl2br("\n\n" . $die));
unset($die, $temp);

/////////////////////////////////////////////////
// ﾉｬｿﾜ､ﾎ･ﾚ｡ｼ･ｸ､ｬﾂｸｺﾟ､ｷ､ﾊ､ｱ､・ﾐ｡｢ｶﾎ･ﾕ･｡･､･・鋿ｮ､ｹ､・
foreach(array($defaultpage, $whatsnew, $interwiki) as $page){
	if (! is_page($page)) touch(get_filename($page));
}

/////////////////////////////////////////////////
// ｳｰﾉｫ､鬢ｯ､・ﾑｿﾎ･ﾁ･ｧ･ﾃ･ｯ

// Prohibit $_GET attack
foreach (array('msg', 'pass') as $key) {
	if (isset($_GET[$key])) die_message('Sorry, already reserved: ' . $key . '=');
}

// Expire risk
unset($HTTP_GET_VARS, $HTTP_POST_VARS);	//, 'SERVER', 'ENV', 'SESSION', ...
unset($_REQUEST);	// Considered harmful

// Remove null character etc.
$_GET    = input_filter($_GET);
$_POST   = input_filter($_POST);
$_COOKIE = input_filter($_COOKIE);
$_SESSION = input_filter($_SESSION);

// ﾊｸｻ妺ｳ｡ｼ･ﾉﾊﾑｴｹ ($_POST)
// <form> ､ﾇﾁｮ､ｵ､・ｿﾊｸｻ・(･ﾖ･鬣ｦ･ｶ､ｬ･ｨ･ｳ｡ｼ･ﾉ､ｷ､ｿ･ﾇ｡ｼ･ｿ) ､ﾎ･ｳ｡ｼ･ﾉ､ﾑｴｹ
// POST method ､ﾏｾ・ﾋ form ｷﾐﾍｳ､ﾊ､ﾎ､ﾇ｡｢ﾉｬ､ｺﾊﾑｴｹ､ｹ､・//
if (isset($_POST['encode_hint']) && $_POST['encode_hint'] != '') {
	// do_plugin_xxx() ､ﾎﾃ讀ﾇ｡｢<form> ､ﾋ encode_hint ､ﾅｹ
､ﾇ､､､・ﾎ､ﾇ｡｢
	// encode_hint ､ﾑ､､､ﾆ･ｳ｡ｼ･ﾉｸ｡ｽﾐ､ｹ､・｣
	// ﾁｴﾂﾎ､ｫ､ﾆ･ｳ｡ｼ･ﾉｸ｡ｽﾐ､ｹ､・ﾈ｡｢ｵ｡ｼ・ﾍﾂｸﾊｸｻ妤茖｢ﾌｯ､ﾊ･ﾐ･､･ﾊ･・	// ･ｳ｡ｼ･ﾉ､ｬｺｮﾆ
､ｷ､ｿｾ・遉ﾋ｡｢･ｳ｡ｼ･ﾉｸ｡ｽﾐ､ﾋｼｺﾇﾔ､ｹ､・ｲ､・ｬ､｢､・｣
	$encode = mb_detect_encoding($_POST['encode_hint']);
	mb_convert_variables(SOURCE_ENCODING, $encode, $_POST);

} else if (isset($_POST['charset']) && $_POST['charset'] != '') {
	// TrackBack Ping ､ﾇｻﾘﾄ熙ｵ､・ﾆ､､､・ｳ､ﾈ､ｬ､｢､・	// ､ｦ､ﾞ､ｯ､､､ｫ､ﾊ､､ｾ・遉ﾏｼｫﾆｰｸ｡ｽﾐ､ﾋﾀﾚ､・ﾘ､ｨ
	if (mb_convert_variables(SOURCE_ENCODING,
	    $_POST['charset'], $_POST) !== $_POST['charset']) {
		mb_convert_variables(SOURCE_ENCODING, 'auto', $_POST);
	}

} else if (! empty($_POST)) {
	// ﾁｴﾉﾞ､ﾈ､皃ﾆ｡｢ｼｫﾆｰｸ｡ｽﾐ｡ｿﾊﾑｴｹ
	mb_convert_variables(SOURCE_ENCODING, 'auto', $_POST);
}

// ﾊｸｻ妺ｳ｡ｼ･ﾉﾊﾑｴｹ ($_GET)
// GET method ､ﾏ form ､ｫ､鬢ﾎｾ・遉ﾈ｡｢<a href="http://script/?key=value> ､ﾎｾ・遉ｬ､｢､・// <a href...> ､ﾎｾ・遉ﾏ｡｢･ｵ｡ｼ･ﾐ｡ｼ､ｬ rawurlencode ､ｷ､ﾆ､､､・ﾎ､ﾇ｡｢･ｳ｡ｼ･ﾉﾊﾑｴｹ､ﾏﾉﾔﾍﾗ
if (isset($_GET['encode_hint']) && $_GET['encode_hint'] != '')
{
	// form ｷﾐﾍｳ､ﾎｾ・遉ﾏ｡｢･ﾖ･鬣ｦ･ｶ､ｬ･ｨ･ｳ｡ｼ･ﾉ､ｷ､ﾆ､､､・ﾎ､ﾇ｡｢･ｳ｡ｼ･ﾉｸ｡ｽﾐ｡ｦﾊﾑｴｹ､ｬﾉｬﾍﾗ｡｣
	// encode_hint ､ｬｴﾞ､ﾞ､・ﾆ､､､・ﾏ､ｺ､ﾊ､ﾎ､ﾇ｡｢､ｽ､・ｫ､ﾆ｡｢･ｳ｡ｼ･ﾉｸ｡ｽﾐ､ｷ､ｿｸ蝪｢ﾊﾑｴｹ､ｹ､・｣
	// ﾍ
ﾍｳ､ﾏ｡｢post ､ﾈﾆｱﾍﾍ
	$encode = mb_detect_encoding($_GET['encode_hint']);
	mb_convert_variables(SOURCE_ENCODING, $encode, $_GET);
}


/////////////////////////////////////////////////
// QUERY_STRING､霹ﾀ

// cmd､穡lugin､篏ﾘﾄ熙ｵ､・ﾆ､､､ﾊ､､ｾ・遉ﾏ｡｢QUERY_STRING､・// ･ﾚ｡ｼ･ｸﾌｾ､ｫInterWikiName､ﾇ､｢､・ﾈ､ﾟ､ﾊ､ｹ
$arg = '';
if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
	$arg = & $_SERVER['QUERY_STRING'];
} else if (isset($_SERVER['argv']) && ! empty($_SERVER['argv'])) {
	$arg = & $_SERVER['argv'][0];
}
if (PKWK_QUERY_STRING_MAX && strlen($arg) > PKWK_QUERY_STRING_MAX) {
	// Something nasty attack?
	pkwk_common_headers();
	sleep(1);	// Fake processing, and/or process other threads
	echo('Query string too long');
	exit;
}
$arg = input_filter($arg); // \0 ｽ・・
// unset QUERY_STRINGs
foreach (array('QUERY_STRING', 'argv', 'argc') as $key) {
	unset(${$key}, $_SERVER[$key], $HTTP_SERVER_VARS[$key]);
}
// $_SERVER['REQUEST_URI'] is used at func.php NOW
unset($REQUEST_URI, $HTTP_SERVER_VARS['REQUEST_URI']);

// mb_convert_variables､ﾎ･ﾐ･ｰ(?)ﾂﾐｺ・ ﾇﾛﾎﾇﾅﾏ､ｵ､ﾊ､､､ﾈﾍ釥ﾁ､・$arg = array($arg);
mb_convert_variables(SOURCE_ENCODING, 'auto', $arg);
$arg = $arg[0];

/////////////////////////////////////////////////
// QUERY_STRING､ｬｲｷ､ﾆ･ｳ｡ｼ･ﾉﾊﾑｴｹ､ｷ｡｢$_GET ､ﾋｾ蠖ｭ

// URI ､・urlencode ､ｻ､ｺ､ﾋﾆ
ﾎﾏ､ｷ､ｿｾ・遉ﾋﾂﾐｽ隍ｹ､・$matches = array();
foreach (explode('&', $arg) as $key_and_value) {
	if (preg_match('/^([^=]+)=(.+)/', $key_and_value, $matches) &&
	    mb_detect_encoding($matches[2]) != 'ASCII') {
		$_GET[$matches[1]] = $matches[2];
	}
}
unset($matches);

/////////////////////////////////////////////////
// GET, POST, COOKIE

$get    = & $_GET;
$post   = & $_POST;
$cookie = & $_COOKIE;
$session = & $_SESSION;

// GET + POST = $vars
if (empty($_POST)) {
	$vars = & $_GET;  // Major pattern: Read-only access via GET
} else if (empty($_GET)) {
	$vars = & $_POST; // Minor pattern: Write access via POST etc.
} else {
	$vars = array_merge($_GET, $_POST); // Considered reliable than $_REQUEST
}

// ﾆ
ﾎﾏ･ﾁ･ｧ･ﾃ･ｯ: cmd, plugin ､ﾎﾊｸｻ昀ﾏｱﾑｿ嵓ﾊｳｰ､｢､熙ｨ､ﾊ､､
foreach(array('cmd', 'plugin') as $var) {
	if (isset($vars[$var]) && ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $vars[$var]))
		unset($get[$var], $post[$var], $vars[$var]);
}

// ﾀｰｷﾁ: page, strip_bracket()
if (isset($vars['page'])) {
	$get['page'] = $post['page'] = $vars['page']  = strip_bracket($vars['page']);
} else {
	$get['page'] = $post['page'] = $vars['page'] = '';
}

// ﾀｰｷﾁ: msg, ｲ
ｹﾔ､隍・・ｯ
if (isset($vars['msg'])) {
	$get['msg'] = $post['msg'] = $vars['msg'] = str_replace("\r", '', $vars['msg']);
}

// ｸ衞
ｸﾟｴｹﾀｭ (?md5=...)
if (isset($get['md5']) && $get['md5'] != '' &&
    ! isset($vars['cmd']) && ! isset($vars['plugin'])) {
	$get['cmd'] = $post['cmd'] = $vars['cmd'] = 'md5';
}

// TrackBack Ping
if (isset($vars['tb_id']) && $vars['tb_id'] != '') {
	$get['cmd'] = $post['cmd'] = $vars['cmd'] = 'tb';
}

// cmd､穡lugin､篏ﾘﾄ熙ｵ､・ﾆ､､､ﾊ､､ｾ・遉ﾏ｡｢QUERY_STRING､ﾚ｡ｼ･ｸﾌｾ､ｫInterWikiName､ﾇ､｢､・ﾈ､ﾟ､ﾊ､ｹ
if (! isset($vars['cmd']) && ! isset($vars['plugin'])) {

	$get['cmd']  = $post['cmd']  = $vars['cmd']  = 'read';

	if ($arg == '') $arg = $defaultpage;
	$arg = rawurldecode($arg);
	$arg = strip_bracket($arg);
	$arg = input_filter($arg);
	$get['page'] = $post['page'] = $vars['page'] = $arg;
}

// ﾆ
ﾎﾏ･ﾁ･ｧ･ﾃ･ｯ: 'cmd=' prohibits nasty 'plugin='
if (isset($vars['cmd']) && isset($vars['plugin']))
	unset($get['plugin'], $post['plugin'], $vars['plugin']);


/////////////////////////////////////////////////
// ｽ魘・ﾟﾄ・$WikiName,$BracketName､ﾊ､ﾉ)
// $WikiName = '[A-Z][a-z]+(?:[A-Z][a-z]+)+';
// $WikiName = '\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b';
// $WikiName = '(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])';
// $WikiName = '(?<!\w)(?:[A-Z][a-z]+){2,}(?!\w)';

// BugTrack/304ｻﾃﾄ・ﾐｽ・$WikiName = '(?:[A-Z][a-z]+){2,}(?!\w)';

// $BracketName = ':?[^\s\]#&<>":]+:?';
$BracketName = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';

// InterWiki
$InterWikiName = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';

// ﾃ晴・$NotePattern = '/\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)/ex';

/////////////////////////////////////////////////
// ｽ魘・ﾟﾄ・･譯ｼ･ｶﾄ・ﾁ･・ｼ･・ﾉ､ﾟｹ
､ﾟ)
require(DATA_HOME . 'rules.ini.php');

/////////////////////////////////////////////////
// ｽ魘・ﾟﾄ・､ｽ､ﾎﾂｾ､ﾎ･ｰ･悅ｼ･ﾐ･・ﾑｿ・

// ｸｽｺﾟｻ
ｹ・$now = format_date(UTIME);

// ﾆ・
ﾃﾖｴｹ･・ｼ･・・line_rules､ﾋｲﾃ､ｨ､・if ($usedatetime) $line_rules += $datetime_rules;
unset($datetime_rules);

// ･ﾕ･ｧ･､･ｹ･ﾞ｡ｼ･ｯ､・line_rules､ﾋｲﾃ､ｨ､・if ($usefacemark) $line_rules += $facemark_rules;
unset($facemark_rules);

// ｼﾂﾂﾎｻｲｾﾈ･ﾑ･ｿ｡ｼ･ｪ､隍ﾓ･ｷ･ｹ･ﾆ･爨ﾇｻﾈﾍﾑ､ｹ､・ﾑ･ｿ｡ｼ･・line_rules､ﾋｲﾃ､ｨ､・//$entity_pattern = '[a-zA-Z0-9]{2,8}';
$entity_pattern = trim(join('', file(CACHE_DIR . 'entities.dat')));

$line_rules = array_merge(array(
	'&amp;(#[0-9]+|#x[0-9a-f]+|' . $entity_pattern . ');' => '&$1;',
	"\r"          => '<br />' . "\n",	/* ｹﾔﾋﾋ･ﾁ･・ﾀ､ﾏｲ
ｹﾔ */
), $line_rules);

?>
