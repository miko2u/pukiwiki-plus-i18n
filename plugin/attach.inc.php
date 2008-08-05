<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: attach.inc.php,v 1.87.35 2008/08/06 02:21:00 upk Exp $
// Copyright (C)
//   2005-2008 PukiWiki Plus! Team
//   2003-2007 PukiWiki Developers Team
//   2002-2003 PANDA <panda@arino.jp> http://home.arino.jp/
//   2002      Y.MASUI <masui@hisec.co.jp> http://masui.net/pukiwiki/
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// File attach plugin

// NOTE (PHP > 4.2.3):
//    This feature is disabled at newer version of PHP.
//    Set this at php.ini if you want.
// Max file size for upload on PHP (PHP default: 2MB)

defined('PLUGIN_ATTACH_UPLOAD_MAX_FILESIZE')	or define('PLUGIN_ATTACH_UPLOAD_MAX_FILESIZE', '4M');		// default: 4MB
ini_set('upload_max_filesize', PLUGIN_ATTACH_UPLOAD_MAX_FILESIZE);

// Max file size for upload on script of PukiWikiX_FILESIZE
defined('PLUGIN_ATTACH_MAX_FILESIZE')		or define('PLUGIN_ATTACH_MAX_FILESIZE', (2048 * 1024));		// default: 1MB
// 管理者だけが添付ファイルをアップロードできるようにする
defined('PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY')	or define('PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY', FALSE);		// FALSE or TRUE
// 管理者だけが添付ファイルを削除できるようにする
defined('PLUGIN_ATTACH_DELETE_ADMIN_ONLY')	or define('PLUGIN_ATTACH_DELETE_ADMIN_ONLY', FALSE);		// FALSE or TRUE
// 管理者が添付ファイルを削除するときは、バックアップを作らない
// PLUGIN_ATTACH_DELETE_ADMIN_ONLY=TRUEのとき有効
defined('PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP')	or define('PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP', FALSE);	// FALSE or TRUE
// アップロード/削除時にパスワードを要求する(ADMIN_ONLYが優先)
defined('PLUGIN_ATTACH_PASSWORD_REQUIRE')	or define('PLUGIN_ATTACH_PASSWORD_REQUIRE', FALSE);		// FALSE or TRUE
// 添付ファイル名を変更できるようにする
defined('PLUGIN_ATTACH_RENAME_ENABLE')		or define('PLUGIN_ATTACH_RENAME_ENABLE', TRUE);			// FALSE or TRUE
// ファイルのアクセス権
defined('PLUGIN_ATTACH_FILE_MODE')		or define('PLUGIN_ATTACH_FILE_MODE', 0644);
						// define('PLUGIN_ATTACH_FILE_MODE', 0604);			// for XREA.COM

// File icon image
define('PLUGIN_ATTACH_FILE_ICON', '<img src="' . IMAGE_URI .  'file.png"' .
	' width="20" height="20" alt="file"' .
	' style="border-width:0px" />');

// mime-typeを記述したページ
define('PLUGIN_ATTACH_CONFIG_PAGE_MIME', 'plugin/attach/mime-type');

defined('PLUGIN_ATTACH_UNKNOWN_COMPRESS')	or define('PLUGIN_ATTACH_UNKNOWN_COMPRESS', 1);			// 1(compress) or 0(raw)
defined('PLUGIN_ATTACH_COMPRESS_TYPE')		or define('PLUGIN_ATTACH_COMPRESS_TYPE', 'TGZ');		// TGZ, GZ or ZIP

function plugin_attach_init()
{
	$messages = array(
		'_attach_messages' => array(
			'msg_uploaded' => _('Uploaded the file to $1'),
			'msg_deleted'  => _('Deleted the file in $1'),
			'msg_freezed'  => _('The file has been frozen.'),
			'msg_unfreezed'=> _('The file has been unfrozen'),
			'msg_upload'   => _('Upload to $1'),
			'msg_info'     => _('File information'),
			'msg_confirm'  => _('<p>Delete %s.</p>'),
			'msg_list'     => _('List of attached file(s)'),
			'msg_listpage' => _('List of attached file(s) in $1'),
			'msg_listall'  => _('Attached file list of all pages'),
			'msg_file'     => _('Attach file'),
			'msg_maxsize'  => _('Maximum file size is %s.'),
			'msg_count'    => _(' <span class="small">%s download</span>'),
			'msg_password' => _('password'),
			'msg_adminpass'=> _('Administrator password'),
			'msg_delete'   => _('Delete file.'),
			'msg_freeze'   => _('Freeze file.'),
			'msg_unfreeze' => _('Unfreeze file.'),
			'msg_renamed'  => _('The file has been renamed'),
			'msg_isfreeze' => _('File is frozen.'),
			'msg_rename'   => _('Rename'),
			'msg_newname'  => _('New file name'),
			'msg_require'  => _('(require administrator password)'),
			'msg_filesize' => _('size'),
			'msg_type'     => _('type'),
			'msg_date'     => _('date'),
			'msg_dlcount'  => _('access count'),
			'msg_md5hash'  => _('MD5 hash'),
			'msg_page'     => _('Page'),
			'msg_filename' => _('Stored filename'),
			'err_noparm'   => _('Cannot upload/delete file in $1'),
			'err_exceed'   => _('File size too large to $1'),
			'err_exists'   => _('File already exists in $1'),
			'err_notfound' => _('Could not find the file in $1'),
			'err_noexist'  => _('File does not exist.'),
			'err_delete'   => _('Cannot delete file in  $1'),
			'err_rename'   => _('Cannot rename this file'),
			'err_password' => _('Wrong password.'),
			'err_adminpass'=> _('Wrong administrator password'),
			'btn_upload'   => _('Upload'),
			'btn_info'     => _('Information'),
			'btn_submit'   => _('Submit')
		),
	);
	set_plugin_messages($messages);
}

//-------- convert
function plugin_attach_convert()
{
	global $vars;

	$page = isset($vars['page']) ? $vars['page'] : '';

	$nolist = $noform = FALSE;
	if (func_num_args() > 0) {
		foreach (func_get_args() as $arg) {
			$arg = strtolower($arg);
			$nolist |= ($arg == 'nolist');
			$noform |= ($arg == 'noform');
		}
	}

	$ret = '';
	if (! $nolist) {
		$obj  = & new AttachPages($page);
		$ret .= $obj->toString($page, TRUE);
	}
	if (! $noform) {
		$ret .= attach_form($page);
	}

	return $ret;
}

//-------- action
function plugin_attach_action()
{
	global $vars, $_attach_messages;

	// Backward compatible
	if (isset($vars['openfile'])) {
		$vars['file'] = $vars['openfile'];
		$vars['pcmd'] = 'open';
	}
	if (isset($vars['delfile'])) {
		$vars['file'] = $vars['delfile'];
		$vars['pcmd'] = 'delete';
	}

	$pcmd  = isset($vars['pcmd'])  ? $vars['pcmd']  : '';
	$refer = isset($vars['refer']) ? $vars['refer'] : '';
	$pass  = isset($vars['pass'])  ? $vars['pass']  : NULL;
	$page  = isset($vars['page'])  ? $vars['page']  : '';

	if ($refer != '' && is_pagename($refer)) {
		if(in_array($pcmd, array('info', 'open', 'list'))) {
			check_readable($refer);
		} else {
			check_editable($refer);
		}
	}

	// Dispatch
	if (isset($_FILES['attach_file'])) {
		// Upload
		return attach_upload($_FILES['attach_file'], $refer, $pass);
	} else {
		switch ($pcmd) {
		case 'delete':	/*FALLTHROUGH*/
		case 'freeze':
		case 'unfreeze':
			// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
			if (auth::check_role('readonly')) die_message( _('PKWK_READONLY prohibits editing') );
		}
		switch ($pcmd) {
		case 'info'     : return attach_info();
		case 'delete'   : return attach_delete();
		case 'open'     : return attach_open();
		case 'list'     : return attach_list();
		case 'freeze'   : return attach_freeze(TRUE);
		case 'unfreeze' : return attach_freeze(FALSE);
		case 'rename'   : return attach_rename();
		case 'upload'   : return attach_showform();
		}
		if ($page == '' || ! is_page($page)) {
			return attach_list();
		} else {
			return attach_showform();
		}
	}
}

//-------- call from skin
function attach_filelist()
{
	global $vars, $_attach_messages;

	$page = isset($vars['page']) ? $vars['page'] : '';

	$obj = & new AttachPages($page, 0);

	if (! isset($obj->pages[$page])) {
		return '';
	} else {
		return $_attach_messages['msg_file'] . ': ' .
		$obj->toString($page, TRUE) . "\n";
	}
}

//-------- 実体
// ファイルアップロード
// $pass = NULL : パスワードが指定されていない
// $pass = TRUE : アップロード許可
function attach_upload($file, $page, $pass = NULL)
{
	global $_attach_messages;

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');

	// Check query-string
	$query = 'plugin=attach&amp;pcmd=info&amp;refer=' . rawurlencode($page) .
		'&amp;file=' . rawurlencode($file['name']);

	if (PKWK_QUERY_STRING_MAX && strlen($query) > PKWK_QUERY_STRING_MAX) {
		pkwk_common_headers();
		echo(_("Query string (page name and/or file name) too long")); 
		exit;
	} else if (! is_page($page)) {
		die_message(_("No such page"));
	} else if ($file['tmp_name'] == '' || ! is_uploaded_file($file['tmp_name'])) {
		return array('result'=>FALSE);
	} else if ($file['size'] > PLUGIN_ATTACH_MAX_FILESIZE) {
		return array(
			'result'=>FALSE,
			'msg'=>$_attach_messages['err_exceed']);
	} else if (! is_pagename($page) || ($pass !== TRUE && ! is_editable($page))) {
		return array(
			'result'=>FALSE,'
			msg'=>$_attach_messages['err_noparm']);

	// } else if (PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE &&
	} else if (PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && auth::check_role('role_adm_contents') && $pass !== TRUE &&
		  ($pass === NULL || ! pkwk_login($pass))) {
		return array(
			'result'=>FALSE,
			'msg'=>$_attach_messages['err_adminpass']);
	}

	return attach_doupload($file, $page, $pass);
}

function attach_gettext($path, $lock=FALSE)
{
	$fp = @fopen($path, 'r');
	if ($fp == FALSE) return FALSE;

	if ($lock) {
		@flock($fp, LOCK_SH);
	}

	// Returns a value
	$result = fread($fp, filesize($path));

	if ($lock) {
		@flock($fp, LOCK_UN);
		@fclose($fp);
	}
	return $result;
}

function attach_doupload(&$file, $page, $pass=NULL, $temp='', $copyright=FALSE, $notouch=FALSE)
{
	global $_attach_messages;
	global $notify, $notify_subject, $notify_exclude, $spam;

	$type = get_mimeinfo($file['tmp_name']);
	$must_compress = attach_is_compress($type,PLUGIN_ATTACH_UNKNOWN_COMPRESS);

	if ($must_compress) {
		// if attach spam, filtering attach file.
		$vars['uploadname'] = $file['name'];
		$vars['uploadtext'] = attach_gettext($file['tmp_name']);
		if ($vars['uploadtext'] === '' || $vars['uploadtext'] === FALSE) return FALSE;

		//global $spam;
		if ($spam !== 0) {
			if (isset($spam['method']['attach'])) {
				$_method = & $spam['method']['attach'];
			} else if (isset($spam['method']['_default'])) {
				$_method = & $spam['method']['_default'];
			} else {
				$_method = array();
			}
			$exitmode = isset($spam['exitmode']) ? $spam['exitmode'] : '';
			pkwk_spamfilter('File Attach', $page, $vars, $_method, $exitmode);
		}
	}

	if ($must_compress && is_uploaded_file($file['tmp_name'])) {
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'TGZ' && exist_plugin('dump')) {
			$obj = & new AttachFile($page, $file['name'] . '.tgz');
			if ($obj->exist)
				return array('result'=>FALSE,
					'msg'=>$_attach_messages['err_exists']);

			$tar = new tarlib();
			$tar->create(CACHE_DIR, 'tgz') or
				die_message( _("It failed in the generation of a temporary file.") );
			$tar->add_file($file['tmp_name'], $file['name']);
			$tar->close();

			@rename($tar->filename, $obj->filename);
			chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);
			@unlink($tar->filename);
		} else
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'GZ' && extension_loaded('zlib')) {
			$obj = & new AttachFile($page, $file['name'] . '.gz');
			if ($obj->exist)
				return array('result'=>FALSE,
					'msg'=>$_attach_messages['err_exists']);

			$tp = fopen($file['tmp_name'],'rb') or
				die_message( _("The uploaded file cannot be read.") ); // アップロードされたファイルが読めません。
			$zp = gzopen($obj->filename, 'wb') or
				die_message( _("The compression file cannot be written.") );	// 圧縮ファイルが書けません。

			while (!feof($tp)) { gzwrite($zp,fread($tp, 8192)); }
			gzclose($zp);
			fclose($tp);
			chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);
			@unlink($file['tmp_name']);
		} else
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'ZIP' && class_exists('ZipArchive')) {
			$obj = & new AttachFile($page, $file['name'] . '.zip');
			if ($obj->exist)
				return array('result'=>FALSE,
					'msg'=>$_attach_messages['err_exists']);
			$zip = new ZipArchive();

			$zip->addFile($file['tmp_name'],$file['name']);
			// if ($zip->status !== ZIPARCHIVE::ER_OK)
			if ($zip->status !== 0)
				die_message( _('The error occurred('.$zip->status.').') );
			$zip->close();
			chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);
			@unlink($file['tmp_name']);
		}
	} else {
//miko
		$obj = & new AttachFile($page, $file['name']);
		if ($obj->exist)
			return array('result'=>FALSE,
				'msg'=>$_attach_messages['err_exists']);

		if (move_uploaded_file($file['tmp_name'], $obj->filename))
			chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);
	}

	if (is_page($page))
		touch(get_filename($page));

	$obj->getstatus();
	$obj->status['pass'] = ($pass !== TRUE && $pass !== NULL) ? md5($pass) : '';
	$obj->putstatus();

	if ($notify) {
		$notify_exec = TRUE;
		foreach ($notify_exclude as $exclude) {
			$exclude = preg_quote($exclude);
			if (substr($exclude, -1) == '.')
				$exclude = $exclude . '*';
			if (preg_match('/^' . $exclude . '/', $_SERVER['REMOTE_ADDR'])) {
				$notify_exec = FALSE;
				break;
			}
		}
	} else {
		$notify_exec = FALSE;
	}

	if ($notify_exec !== FALSE) {
		$footer['ACTION']   = 'File attached';
		$footer['FILENAME'] = & $file['name'];
		$footer['FILESIZE'] = & $file['size'];
		$footer['PAGE']     = & $page;

		$footer['URI']      = get_script_absuri() .
			//'?' . rawurlencode($page);

			// MD5 may heavy
			'?plugin=attach' .
				'&refer=' . rawurlencode($page) .
				'&file='  . rawurlencode($file['name']) .
				'&pcmd=info';

		$footer['USER_AGENT']  = TRUE;
		$footer['REMOTE_ADDR'] = TRUE;

		pkwk_mail_notify($notify_subject, "\n", $footer);
	}

	return array(
		'result'=>TRUE,
		'msg'=>$_attach_messages['msg_uploaded']);
}

// ファイルタイプによる圧縮添付の判定
function attach_is_compress($type,$compress=1)
{
	if (empty($type)) return $compress;
	list($discrete,$composite_tmp) = explode('/', strtolower($type));
	if (strstr($type,';') === false) {
		$composite = $composite_tmp;
		$parameter = '';
	} else {
		list($composite,$parameter) = explode(';', $composite_tmp);
		$parameter = trim($parameter);
	}
	unset($composite_tmp);

	// type
	static $composite_type = array(
		'application' => array(
			'msword'		=> 0, // doc
			'vnd.ms-excel'		=> 0, // xls
			'vnd.ms-powerpoint'	=> 0, // ppt
			'vnd.visio'		=> 0,
			'octet-stream'		=> 0, // bin dms lha lzh exe class so dll img iso
			'x-bcpio'		=> 0, // bcpio
			'x-bittorrent'		=> 0, // torrent
			'x-bzip2'		=> 0, // bz2
			'x-compress'		=> 0,
			'x-cpio'		=> 0, // cpio
			'x-dvi'			=> 0, // dvi
			'x-gtar'		=> 0, // gtar
			'x-gzip'		=> 0, // gz tgz
			'x-rpm'			=> 0, // rpm
			'x-shockwave-flash'	=> 0, // swf
			'zip'			=> 0, // zip
			'x-java-archive'	=> 0, // jar
			'x-javascript'		=> 1, // js
			'ogg'			=> 0, // ogg
			'pdf'			=> 0, // pdf
		),
	);
	if (isset($composite_type[$discrete][$composite])) {
		return $composite_type[$discrete][$composite];
	}

	// discrete-type
	static $discrete_type = array(
		'text'                          => 1,
		'image'                         => 0,
		'audio'                         => 0,
		'video'                         => 0,
	);
	if (isset($discrete_type[$discrete])) {
		return $discrete_type[$discrete];
	}

	return $compress;
}

// 詳細フォームを表示
function attach_info($err = '')
{
	global $vars, $_attach_messages;

	foreach (array('refer', 'file', 'age') as $var)
		${$var} = isset($vars[$var]) ? $vars[$var] : '';

	check_editable($refer, true, true);

	$obj = & new AttachFile($refer, $file, $age);
	return $obj->getstatus() ?
		$obj->info($err) :
		array('msg'=>$_attach_messages['err_notfound']);
}

// 削除
function attach_delete()
{
	global $vars, $_attach_messages;

	foreach (array('refer', 'file', 'age', 'pass') as $var)
		${$var} = isset($vars[$var]) ? $vars[$var] : '';

	if (is_freeze($refer) || ! is_editable($refer))
		return array('msg'=>$_attach_messages['err_noparm']);

	$obj = & new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$_attach_messages['err_notfound']);
		
	return $obj->delete($pass);
}

// 凍結
function attach_freeze($freeze)
{
	global $vars, $_attach_messages;

	foreach (array('refer', 'file', 'age', 'pass') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$_attach_messages['err_noparm']);
	} else {
		$obj = & new AttachFile($refer, $file, $age);
		return $obj->getstatus() ?
			$obj->freeze($freeze, $pass) :
			array('msg'=>$_attach_messages['err_notfound']);
	}
}

// リネーム
function attach_rename()
{
	global $vars, $_attach_messages;

	foreach (array('refer', 'file', 'age', 'pass', 'newname') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$_attach_messages['err_noparm']);
	}
	$obj = & new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$_attach_messages['err_notfound']);

	return $obj->rename($pass, $newname);
}

// ダウンロード
function attach_open()
{
	global $vars, $_attach_messages;

	foreach (array('refer', 'file', 'age') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	$obj = & new AttachFile($refer, $file, $age);
	return $obj->getstatus() ?
		$obj->open() :
		array('msg'=>$_attach_messages['err_notfound']);
}

// 一覧取得
function attach_list()
{
	global $vars, $_attach_messages;

	if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );

	$refer = isset($vars['refer']) ? $vars['refer'] : '';

	$obj = & new AttachPages($refer);

	$msg = $_attach_messages[($refer == '') ? 'msg_listall' : 'msg_listpage'];
	$body = ($refer == '' || isset($obj->pages[$refer])) ?
		$obj->toString($refer, FALSE) :
		$_attach_messages['err_noexist'];

	return array('msg'=>$msg, 'body'=>$body);
}

// アップロードフォームを表示 (action時)
function attach_showform()
{
	global $vars, $_attach_messages;

	if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page, true, true);
	$vars['refer'] = $page;
	$body = attach_form($page, TRUE);

	return array('msg'=>$_attach_messages['msg_upload'], 'body'=>$body);
}

//-------- サービス
// mime-typeの決定
function attach_mime_content_type($filename)
{
	$type = 'application/octet-stream'; // default

	if (! file_exists($filename)) return $type;

	$size = @getimagesize($filename);
	if (is_array($size)) {
		switch ($size[2]) {
			case 1: return 'image/gif';
			case 2: return 'image/jpeg';
			case 3: return 'image/png';
			case 4: return 'application/x-shockwave-flash';
		}
	}

	$matches = array();
	if (! preg_match('/_((?:[0-9A-F]{2})+)(?:\.\d+)?$/', $filename, $matches))
		return $type;

	$filename = decode($matches[1]);

	// mime-type一覧表を取得
	$config = new Config(PLUGIN_ATTACH_CONFIG_PAGE_MIME);
	$table = $config->read() ? $config->get('mime-type') : array();
	unset($config); // メモリ節約

	foreach ($table as $row) {
		$_type = trim($row[0]);
		$exts = preg_split('/\s+|,/', trim($row[1]), -1, PREG_SPLIT_NO_EMPTY);
		foreach ($exts as $ext) {
			if (preg_match("/\.$ext$/i", $filename)) return $_type;
		}
	}

	return $type;
}

// アップロードフォームの出力
function attach_form($page, $listview = FALSE)
{
	global $script, $vars, $_attach_messages;

	$refer = isset($vars['refer']) ? $vars['refer'] : '';

	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$navi = <<<EOD
  <span class="small">
   [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$_attach_messages['msg_list']}</a>]
   [<a href="$script?plugin=attach&amp;pcmd=list">{$_attach_messages['msg_listall']}</a>]
  </span><br />
EOD;

	if (! ini_get('file_uploads')) return '#attach(): file_uploads disabled<br />' . $navi;
	if (! is_page($page))          return '#attach(): No such page<br />'          . $navi;

	$maxsize = PLUGIN_ATTACH_MAX_FILESIZE;
	$msg_maxsize = sprintf($_attach_messages['msg_maxsize'], number_format($maxsize/1024) . 'KB');

	$pass = '';
	if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
		if (auth::check_role('role_adm_contents')) {
			$title = $_attach_messages[PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'msg_adminpass' : 'msg_password'];
			$pass = '<br />' . $title . ': <input type="password" name="pass" size="8" />';
		}
	}
	$html = '';
	if ($listview) {
		$html .= '<h3>' . str_replace('$1', $s_page, $_attach_messages['msg_upload']) . '</h3>';
		$navi = <<<EOD
  <span class="small">
   [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$_attach_messages['msg_list']}</a>]
   [<a href="$script?plugin=attach&amp;pcmd=list">{$_attach_messages['msg_listall']}</a>]
  </span><br />
EOD;
	}
	$html .= <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="pcmd"   value="post" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="max_file_size" value="$maxsize" />
  $navi
  <span class="small">
   $msg_maxsize
  </span><br />
  <label for="_p_attach_file">{$_attach_messages['msg_file']}:</label> <input type="file" name="attach_file" id="_p_attach_file" />
  $pass
  <input type="submit" value="{$_attach_messages['btn_upload']}" />
 </div>
</form>
EOD;
	if ($listview) {
		$obj = & new AttachPages($page);
		$body = ($refer == '' || isset($obj->pages[$page])) ?
			$obj->toRender($page, FALSE) :
			$_attach_messages['err_noexist'];
		$html .= "<h3>" . str_replace('$1', $s_page, $_attach_messages['msg_listpage']) . "</h3>" . $body;
	}
	return $html;
}

//-------- クラス
// ファイル
class AttachFile
{
	var $page, $file, $age, $basename, $filename, $logname;
	var $time = 0;
	var $size = 0;
	var $time_str = '';
	var $size_str = '';
	var $status = array('count'=>array(0), 'age'=>'', 'pass'=>'', 'freeze'=>FALSE);

	function AttachFile($page, $file, $age = 0)
	{
		$this->page = $page;
		$this->file = basepagename($file);
		$this->age  = is_numeric($age) ? $age : 0;

		$this->basename = UPLOAD_DIR . encode($page) . '_' . encode($this->file);
		$this->filename = $this->basename . ($age ? '.' . $age : '');
		$this->logname  = $this->basename . '.log';
		$this->exist    = file_exists($this->filename);
		$this->time     = $this->exist ? filemtime($this->filename) : 0;
	}

	function gethash()
	{
		return $this->exist ? md5_file($this->filename) : '';
	}

	// ファイル情報取得
	function getstatus()
	{
		if (! $this->exist) return FALSE;

		// ログファイル取得
		if (file_exists($this->logname)) {
			$data = file($this->logname);
			foreach ($this->status as $key=>$value) {
				$this->status[$key] = chop(array_shift($data));
			}
			$this->status['count'] = explode(',', $this->status['count']);
		}
		$this->time_str = get_date('Y/m/d H:i:s', $this->time);
		$this->size     = filesize($this->filename);
		$this->size_str = sprintf('%01.1f', round($this->size/1024, 1)) . 'KB';
		$this->type     = attach_mime_content_type($this->filename);

		return TRUE;
	}

	// ステータス保存
	function putstatus()
	{
		$this->status['count'] = join(',', $this->status['count']);
		$fp = fopen($this->logname, 'wb') or
			die_message('cannot write ' . $this->logname);
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		rewind($fp);
		foreach ($this->status as $key=>$value) {
			fwrite($fp, $value . "\n");
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	// 日付の比較関数
	function datecomp($a, $b) {
		return ($a->time == $b->time) ? 0 : (($a->time > $b->time) ? -1 : 1);
	}

	function toString($showicon, $showinfo)
	{
		global $script, $_attach_messages;

		$this->getstatus();
		$param  = '&amp;file=' . rawurlencode($this->file) . '&amp;refer=' . rawurlencode($this->page) .
			($this->age ? '&amp;age=' . $this->age : '');
		$title = $this->time_str . ' ' . $this->size_str;
		$label = ($showicon ? PLUGIN_ATTACH_FILE_ICON : '') . htmlspecialchars($this->file);
		if ($this->age) {
			$label .= ' (backup No.' . $this->age . ')';
		}
		$info = $count = '';
		if ($showinfo) {
			$_title = str_replace('$1', rawurlencode($this->file), $_attach_messages['msg_info']);
			$info = "\n<span class=\"small\">[<a href=\"$script?plugin=attach&amp;pcmd=info$param\" title=\"$_title\">{$_attach_messages['btn_info']}</a>]</span>\n";
			$count = ($showicon && ! empty($this->status['count'][$this->age])) ?
				sprintf($_attach_messages['msg_count'], $this->status['count'][$this->age]) : '';
		}
		return "<a href=\"$script?plugin=attach&amp;pcmd=open$param\" title=\"$title\">$label</a>$count$info";
	}

	// 情報表示
	function info($err)
	{
		global $script, $_attach_messages;

		$r_page = rawurlencode($this->page);
		$s_page = htmlspecialchars($this->page);
		$s_file = htmlspecialchars($this->file);
		$s_err = ($err == '') ? '' : '<p style="font-weight:bold">' . $_attach_messages[$err] . '</p>';

		$role_adm_contents = auth::check_role('role_adm_contents');
		$msg_require = ($role_adm_contents) ? $_attach_messages['msg_require'] : '';

		$msg_rename  = '';
		if ($this->age) {
			$msg_freezed = '';
			$msg_delete  = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
				'<label for="_p_attach_delete">' .  $_attach_messages['msg_delete'] .
				$msg_require . '</label><br />';
			$msg_freeze  = '';
		} else {
			if ($this->status['freeze']) {
				$msg_freezed = "<dd>{$_attach_messages['msg_isfreeze']}</dd>";
				$msg_delete  = '';
				$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_unfreeze" value="unfreeze" />' .
					'<label for="_p_attach_unfreeze">' .  $_attach_messages['msg_unfreeze'] .
					$msg_require . '</label><br />';
			} else {
				$msg_freezed = '';
				$msg_delete = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
					'<label for="_p_attach_delete">' . $_attach_messages['msg_delete'];
				if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age)
					$msg_delete .= $msg_require;
				$msg_delete .= '</label><br />';
				$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_freeze" value="freeze" />' .
					'<label for="_p_attach_freeze">' .  $_attach_messages['msg_freeze'] .
					$msg_require . '</label><br />';
				if (PLUGIN_ATTACH_RENAME_ENABLE) {
					$msg_rename  = '<input type="radio" name="pcmd" id="_p_attach_rename" value="rename" />' .
						'<label for="_p_attach_rename">' .  $_attach_messages['msg_rename'] .
						$msg_require . '</label><br />&nbsp;&nbsp;&nbsp;&nbsp;' .
						'<label for="_p_attach_newname">' . $_attach_messages['msg_newname'] .
						':</label> ' .
						'<input type="text" name="newname" id="_p_attach_newname" size="40" value="' .
						$this->file . '" /><br />';
				}
			}
		}
		$info = $this->toString(TRUE, FALSE);
		$hash = $this->gethash();

		$size = @getimagesize($this->filename);
		if ($size[2] > 0 && $size[2] < 3) {
			if ($size[0] < 200) { $w = $size[0]; $h = $size[1]; }
			else { $w = 200; $h = $size[1] * (200 / ($size[0]!=0?$size[0]:1) ); }
			$_attach_setimage  = '<div class="img_margin" style="float:right;"><img src="';
			$_attach_setimage .= "$script?plugin=ref&amp;src={$s_file}&amp;page={$r_page}";
			$_attach_setimage .= '" width="' . $w .'" height="' . $h . '" /></div>';
		} else {
			$_attach_setimage = '';
		}

		$msg_auth = '';
		if ($role_adm_contents) {
			$msg_auth = <<<EOD
  <label for="_p_attach_password">{$_attach_messages['msg_password']}:</label>
  <input type="password" name="pass" id="_p_attach_password" size="8" />
EOD;
		}

		$retval = array('msg'=>sprintf($_attach_messages['msg_info'], htmlspecialchars($this->file)));
		$retval['body'] = <<< EOD
<p class="small">
 [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$_attach_messages['msg_list']}</a>]
 [<a href="$script?plugin=attach&amp;pcmd=list">{$_attach_messages['msg_listall']}</a>]
</p>
<dl>
 <dt>$info (<a href="#" title="{$this->filename}">{$_attach_messages['msg_filename']}</a>)</dt>
</dl>
{$_attach_setimage}
<dl>
 <dd>{$_attach_messages['msg_page']}:$s_page</dd>
 <dd>{$_attach_messages['msg_filesize']}:{$this->size_str} ({$this->size} bytes)</dd>
 <dd>Content-type:{$this->type}</dd>
 <dd>{$_attach_messages['msg_date']}:{$this->time_str}</dd>
 <dd>{$_attach_messages['msg_dlcount']}:{$this->status['count'][$this->age]}</dd>
 <dd>{$_attach_messages['msg_md5hash']}:{$hash}</dd>
 $msg_freezed
</dl>
<div style="clear:right"><hr /></div>
$s_err
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="file" value="$s_file" />
  <input type="hidden" name="age" value="{$this->age}" />
  $msg_delete
  $msg_freeze
  $msg_rename
  $msg_auth
  <input type="submit" value="{$_attach_messages['btn_submit']}" />
 </div>
</form>
EOD;
		return $retval;
	}

	function delete($pass)
	{
		global $_attach_messages, $notify, $notify_subject;

		if ($this->status['freeze']) return attach_info('msg_isfreeze');

		// if (! pkwk_login($pass)) {
		if (auth::check_role('role_adm_contents') && ! pkwk_login($pass)) {
			if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age) {
				return attach_info('err_adminpass');
			} else if (PLUGIN_ATTACH_PASSWORD_REQUIRE &&
				md5($pass) != $this->status['pass']) {
				return attach_info('err_password');
			}
		}

		// バックアップ
		if ($this->age ||
			(PLUGIN_ATTACH_DELETE_ADMIN_ONLY && PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP)) {
			@unlink($this->filename);
		} else {
			do {
				$age = ++$this->status['age'];
			} while (file_exists($this->basename . '.' . $age));

			if (! rename($this->basename,$this->basename . '.' . $age)) {
				// 削除失敗 why?
				return array('msg'=>$_attach_messages['err_delete']);
			}

			$this->status['count'][$age] = $this->status['count'][0];
			$this->status['count'][0] = 0;
			$this->putstatus();
		}

		if (is_page($this->page))
			touch(get_filename($this->page));

		if ($notify) {
			$footer['ACTION']   = 'File deleted';
			$footer['FILENAME'] = & $this->file;
			$footer['PAGE']     = & $this->page;
			$footer['URI']      = get_page_absuri($this->page);
			$footer['USER_AGENT']  = TRUE;
			$footer['REMOTE_ADDR'] = TRUE;
			pkwk_mail_notify($notify_subject, "\n", $footer) or
				die('pkwk_mail_notify(): Failed');
		}

		return array('msg'=>$_attach_messages['msg_deleted']);
	}

	function rename($pass, $newname)
	{
		global $_attach_messages, $notify, $notify_subject;

		if ($this->status['freeze']) return attach_info('msg_isfreeze');

		if (auth::check_role('role_adm_contents') && ! pkwk_login($pass))
			return attach_info('err_adminpass');

		$newbase = UPLOAD_DIR . encode($this->page) . '_' . encode($newname);
		if (file_exists($newbase)) {
			return array('msg'=>$_attach_messages['err_exists']);
		}
		if (! PLUGIN_ATTACH_RENAME_ENABLE || ! rename($this->basename, $newbase)) {
			return array('msg'=>$_attach_messages['err_rename']);
		}

		return array('msg'=>$_attach_messages['msg_renamed']);
	}

	function freeze($freeze, $pass)
	{
		global $_attach_messages;

		// if (! pkwk_login($pass))
		if (auth::check_role('role_adm_contents') && ! pkwk_login($pass))
			return attach_info('err_adminpass');

		$this->getstatus();
		$this->status['freeze'] = $freeze;
		$this->putstatus();

		return array('msg'=>$_attach_messages[$freeze ? 'msg_freezed' : 'msg_unfreezed']);
	}

	function open()
	{
		$this->getstatus();
		$this->status['count'][$this->age]++;
		$this->putstatus();
		$filename = $this->file;

		// Care for Japanese-character-included file name
		if (LANG == 'ja_JP') {
			switch(UA_NAME . '/' . UA_PROFILE){
			case 'Opera/default':
				// Care for using _auto-encode-detecting_ function
				$filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
				break;
			case 'MSIE/default':
				$filename = mb_convert_encoding($filename, 'SJIS', 'auto');
				break;
			}
		}
		$filename = htmlspecialchars($filename);

		ini_set('default_charset', '');
		mb_http_output('pass');

		pkwk_common_headers();
		if ($this->type == 'text/html' || $this->type == 'application/octet-stream') {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Type: application/octet-stream; name="' . $filename . '"');
			header('Content-Length: ' . $this->size);
		} else {
			header('Content-Disposition: inline; filename="' . $filename . '"');
			header('Content-Type: '   . $this->type);
			header('Content-Length: ' . $this->size);
		}

		@readfile($this->filename);
		log_put_download($this->page,$this->file);
		exit;
	}
}

// ファイルコンテナ
class AttachFiles
{
	var $page;
	var $files = array();

	function AttachFiles($page)
	{
		$this->page = $page;
	}

	function add($file, $age)
	{
		$this->files[$file][$age] = & new AttachFile($this->page, $file, $age);
	}

	// ファイル一覧を取得
	function toString($flat)
	{
		global $_title;

		if (! check_readable($this->page, FALSE, FALSE)) {
			return str_replace('$1', make_pagelink($this->page), $_title['cannotread']);
		} else if ($flat) {
			return $this->to_flat();
		}

		$ret = '';
		$files = array_keys($this->files);
		sort($files, SORT_STRING);

		foreach ($files as $file) {
			$_files = array();
			foreach (array_keys($this->files[$file]) as $age) {
				$_files[$age] = $this->files[$file][$age]->toString(FALSE, TRUE);
			}
			if (! isset($_files[0])) {
				$_files[0] = htmlspecialchars($file);
			}
			ksort($_files, SORT_NUMERIC);
			$_file = $_files[0];
			unset($_files[0]);
			$ret .= " <li>$_file\n";
			if (count($_files)) {
				$ret .= "<ul>\n<li>" . join("</li>\n<li>", $_files) . "</li>\n</ul>\n";
			}
			$ret .= " </li>\n";
		}
		return make_pagelink($this->page) . "\n<ul>\n$ret</ul>\n";
	}

	// ファイル一覧を取得(inline)
	function to_flat()
	{
		$ret = '';
		$files = array();
		foreach (array_keys($this->files) as $file) {
			if (isset($this->files[$file][0])) {
				$files[$file] = & $this->files[$file][0];
			}
		}
		uasort($files, array('AttachFile', 'datecomp'));
		foreach (array_keys($files) as $file) {
			$ret .= $files[$file]->toString(TRUE, TRUE) . ' ';
		}

		return $ret;
	}

	// ファイル一覧をテーブルで取得
	function toRender($flat)
	{
		global $_attach_messages;
		global $_title;

		if (! check_readable($this->page, FALSE, FALSE)) {
			return str_replace('$1', make_pagelink($this->page), $_title['cannotread']);
		} else if ($flat) {
			return $this->to_flat();
		}

		$ret = '';
		$files = array_keys($this->files);
		sort($files, SORT_STRING);

		$cssstyle = '';
		foreach ($files as $file) {
			$_files = array();
			foreach (array_keys($this->files[$file]) as $age) {
				$_files[$age] = $this->files[$file][$age]->toString(FALSE, TRUE);
			}
			if (! isset($_files[0])) {
				$_files[0] = htmlspecialchars($file);
			}
			ksort($_files, SORT_NUMERIC);
			$_file = $_files[0];
			unset($_files[0]);
			$cssstyle = ($cssstyle == 'attach_td1') ? 'attach_td2':'attach_td1';
			$ret .= '<tr><td class="' . $cssstyle . '">' . "$_file</td>"
			     .  '<td class="' . $cssstyle . '">' . "{$this->files[$file][0]->size_str}</td>"
			     .  '<td class="' . $cssstyle . '">' . "{$this->files[$file][0]->type}</td>"
			     .  '<td class="' . $cssstyle . '">' . "{$this->files[$file][0]->time_str}</td></tr>\n";
		}
		return '<table width="100%" class="attach_table">' . "\n" .
		       '<tr><th class="attach_th">' . $_attach_messages['msg_file'] . '</th>' .
			   '<th class="attach_th">' . $_attach_messages['msg_filesize'] . '</th>' .
		       '<th class="attach_th">' . $_attach_messages['msg_type'] . '</th>' .
		       '<th class="attach_th">' . $_attach_messages['msg_date'] . '</th></tr>' . "\n$ret</table>\n";
	}
}

// ページコンテナ
class AttachPages
{
	var $pages = array();

	function AttachPages($page = '', $age = NULL)
	{

		$dir = opendir(UPLOAD_DIR) or
			die('directory ' . UPLOAD_DIR . ' is not exist or not readable.');

		$page_pattern = ($page == '') ? '(?:[0-9A-F]{2})+' : preg_quote(encode($page), '/');
		$age_pattern = ($age === NULL) ?
			'(?:\.([0-9]+))?' : ($age ?  "\.($age)" : '');
		$pattern = "/^({$page_pattern})_((?:[0-9A-F]{2})+){$age_pattern}$/";

		$matches = array();
		while ($file = readdir($dir)) {
			if (! preg_match($pattern, $file, $matches))
				continue;

			$_page = decode($matches[1]);
			if (! check_readable($_page, FALSE, FALSE)) continue;

			$_file = decode($matches[2]);
			$_age  = isset($matches[3]) ? $matches[3] : 0;
			if (! isset($this->pages[$_page])) {
				$this->pages[$_page] = & new AttachFiles($_page);
			}
			$this->pages[$_page]->add($_file, $_age);
		}
		closedir($dir);
	}

	function toString($page = '', $flat = FALSE)
	{
		if ($page != '') {
			if (! isset($this->pages[$page])) {
				return '';
			} else {
				return $this->pages[$page]->toString($flat);
			}
		}
		$ret = '';

		$pages = array_keys($this->pages);
		sort($pages, SORT_STRING);

		foreach ($pages as $page) {
			if (check_non_list($page)) continue;
			$ret .= '<li>' . $this->pages[$page]->toString($flat) . '</li>' . "\n";
		}
		return "\n" . '<ul>' . "\n" . $ret . '</ul>' . "\n";
	}

	function toRender($page = '', $pattern = 0)
	{
		if ($page != '') {
			if (! isset($this->pages[$page])) {
				return '';
			} else {
				return $this->pages[$page]->toRender($pattern);
			}
		}
		$ret = '';

		$pages = array_keys($this->pages);
		sort($pages, SORT_STRING);

		foreach ($pages as $page) {
			if (check_non_list($page)) continue;
			$ret .= '<tr><td>' . $this->pages[$page]->toRender($pattern) . '</td></tr>' . "\n";
		}
		return "\n" . '<table>' . "\n" . $ret . '</table>' . "\n";
	}
}
?>
