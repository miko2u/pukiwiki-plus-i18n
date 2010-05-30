<?php
/**
 * WebDAV プラグイン
 *
 * Author: a-urasim@pu-toyama.ac.jp
 * Version: 0.39
 *
 * @copyright   Copyright &copy; 2010, Katsumi Saito <jo1upk@users.sourceforge.net>
 * @version     $Id: dav.inc.php,v 0.39.1 2010/05/30 20:36:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 * 現状では、DOMDocument の実装上、PHP5以降でのみ稼働。
 * CentOS の場合は、php-xml パッケージを導入する必要あり。
 *
 * == 稼働上の制限 ==
 * apache only:
 *   $_SERVER: 'HTTPS', 'HTTP_HOST', 'REQUEST_METHOD', 'PATH_INFO', 'REQUEST_URI'
 *   apache_getenv('SERVER_PORT');
 *
 * == for Windows Vista/7, check ==
 *  HKLM\SYSTEM\CurrentControlSet\Services\WebClient\Parameters\BasicAuthLevel
 *   0 - Basic authentication disabled
 *   1 - Basic authentication enabled for SSL shares only
 *   2 or greater - Basic authentication enabled for SSL shares
 *                  and for non-SSL shares
 *
*/

defined('PLUGIN_DAV_SHOWONLYEDITABLE') or define('PLUGIN_DAV_SHOWONLYEDITABLE', false);

function plugin_dav_action()
{
	global $scriptname, $zslash, $log_ua;

	if (!exist_plugin('attach')) plugin_dav_error_exit(500,'attach plugin not found.');

	$scriptname = SCRIPT_NAME;
	// 区切り文字の全角
	$zslash = mb_convert_kana('/', 'A', SOURCE_ENCODING);

	header('Expires: Sat,  1 Jan 2000 00:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');

	$req_headers = apache_request_headers();
	$path_info = (empty($_SERVER['PATH_INFO'])) ? '' : $_SERVER['PATH_INFO'];

	switch ($_SERVER['REQUEST_METHOD']) {
	case 'OPTIONS':
		header('DAV: 1');
		// OPTIONS,PROPFIND,GET,HEAD,PUT,DELETE,MOVE,COPY
		header('Allow: OPTIONS,PROPFIND,GET,PUT,MOVE,COPY');
		header('MS-Author-Via: DAV');
		break;
	case 'PROPFIND':
		// 添付する際にパスワードまたは、管理者のみの場合は、認証を要求
		if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
			// PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'admin password' : 'password';
			if(!isset($req_headers['Authorization'])) plugin_dav_error_exit(401);
			//$user = auth::check_auth_pw(); // 認証済かのチェック
			//if (empty($user)) plugin_dav_error_exit(401); // 未認証なら認証を要求
		}

		if( empty($path_info)) {
			plugin_dav_error_exit(301, NULL, plugin_dav_myurl1().'/');
		}

		$tree = plugin_dav_maketree($path_info);
		if(!isset($tree)) plugin_dav_error_exit(404);
		$ret = plugin_dav_makemultistat($tree, $_SERVER['REQUEST_URI'], $req_headers['Depth']);

		if(!isset($ret)) plugin_dav_error_exit(301, NULL, plugin_dav_myurl().'/');
		header('HTTP/1.1 207 Multi-Status');
		header('Content-Type: text/xml');
		echo $ret->saveXML();
		exit;

	case 'GET':
	case 'HEAD':
		// 通常のファイル参照時は、このメソッドでアクセスされる
		$obj = & plugin_dav_getfileobj($path_info);
		if($obj != NULL && $obj->exist) {
			$obj->open();
		}
		else if($_SERVER['REQUEST_METHOD'] == 'GET' && empty($path_info) && strpos($log_ua, 'MSIE') > 0) {
			plugin_dav_officious_message();
			exit;
		}
		else plugin_dav_error_exit(404);

		break;

	case 'PUT':
		$pass = NULL;

		if (auth::check_role('readonly')) plugin_dav_error_exit(403, 'PKWK_READONLY prohibits editing');

		// 添付する際にパスワードまたは、管理者のみの場合は、認証を要求
		if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
			if(isset($req_headers['Authorization']))
				$pass = plugin_dav_getbasicpass($req_headers['Authorization']);
			//  else
			// PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'admin password' : 'password';
			//    plugin_dav_error_exit(401);
		}
		if (PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE &&
			($pass === NULL || ! pkwk_login($pass)))
			plugin_dav_error_exit(401); // 'admin only.'
 
		$obj = & plugin_dav_getfileobj($path_info, false);

		if(!isset($obj)) plugin_dav_error_exit(403, 'no page');
		if($obj->exist){
			unlink($tmpfilename);
			plugin_dav_error_exit(403, 'already exist.');
		}

		$size = intval($req_headers['Content-Length']);
		// Windows 7のクライアントは、まず0バイト書いて、
		// それをLOCKしてから、上書きしにくる。
		// しかし、Pukiwikiは基本上書き禁止。
		// そこで0バイトの時は無視する。
		if($size > 0){
			if($size > PLUGIN_ATTACH_MAX_FILESIZE) {
				plugin_dav_error_exit(403, 'file size error');
			}
 
			$tmpfilename = tempnam('/tmp', 'dav');
			$fp = fopen($tmpfilename, 'wb');

			$size = 0;
			$putdata = fopen('php://input','rb');
			while ($data = fread($putdata,1024)){
				$size += strlen($data);
				fwrite($fp,$data);
			}
			fclose($putdata);
			fclose($fp);

			if(copy($tmpfilename, $obj->filename)) {
				chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);
			}

			if(is_page($obj->page)) touch(get_filename($obj->page));
			$obj->getstatus();
			$obj->status['pass'] = ($pass !== TRUE && $pass !== NULL) ? md5($pass) : '';
			$obj->putstatus();
			unlink($tmpfilename);
		}

		break;

	case 'DELETE':
		// FIXME
		// フォルダーは消せないくせに、消せたように処理してしまう。
		//
		$pass = NULL;

		if (auth::check_role('readonly')) plugin_dav_error_exit(403, 'PKWK_READONLY prohibits editing');

		// 添付する際にパスワードまたは、管理者のみの場合は、認証を要求
		if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
			if(isset($req_headers['Authorization']))
				$pass = plugin_dav_getbasicpass($req_headers['Authorization']);
			//  else
			// PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'admin password' : 'password';
			//    plugin_dav_error_exit(401);
		}
		if(PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE &&
			($pass === NULL || ! pkwk_login($pass)))
			plugin_dav_error_exit(401); // 'admin only.'
 
		$obj = & plugin_dav_getfileobj($path_info, false);

		if(!isset($obj)) plugin_dav_error_exit(403);
		if($obj->getstatus() == FALSE) plugin_dav_error_exit(404);

		$obj->delete($pass);
		if(file_exists($obj->filename)) {
			plugin_dav_error_exit(406, "can't delete this file");
		}

		break;

	case 'MOVE':
	case 'COPY':
		// 添付ファイルのコピーと移動のみ
		// 同じページ内での添付ファイルの移動もわざわざ消して書いている
		// ページのコピーや移動は未実装 

		$pass = NULL;

		if (auth::check_role('readonly')) plugin_dav_error_exit(403, 'PKWK_READONLY prohibits editing');

		// 添付する際にパスワードまたは、管理者のみの場合は、認証を要求
		if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
			if(isset($req_headers['Authorization']))
				$pass = plugin_dav_getbasicpass($req_headers['Authorization']);
			//  else
			// PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'admin password' : 'password';
			//    plugin_dav_error_exit(401);
		}
		if(PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE &&
			($pass === NULL || ! pkwk_login($pass)))
			plugin_dav_error_exit(401); // 'admin only.'

		// FROM (PATH_INFO)
		if($_SERVER['REQUEST_METHOD'] == 'MOVE'){
			$obj1 = & plugin_dav_getfileobj($path_info, false);
		}
		else {
			$obj1 = & plugin_dav_getfileobj($path_info, true); // readonly
		}
		if(!isset($obj1)) plugin_dav_error_exit(403, 'no src page.');
		if($obj1->getstatus() == FALSE) plugin_dav_error_exit(404);

		// TO (Destination)
		$destname = $req_headers['Destination'];
		if(strpos($destname, plugin_dav_myurl0()) === 0) {
			$destname = substr($destname, strlen(plugin_dav_myurl0()));
		}
		if(strpos($destname, $scriptname) === 0) {
			$destname = urldecode(substr($destname, strlen($scriptname)));
		} else {
			plugin_dav_error_exit(403, 'not dav directory.');
		}

		$obj2 = & plugin_dav_getfileobj($destname, false);
		if (!isset($obj2)) plugin_dav_error_exit(403, 'no dst page.');
		if ($obj2->exist) plugin_dav_error_exit(403, 'already exist');

		if(copy($obj1->filename, $obj2->filename)) {
			chmod($obj2->filename, PLUGIN_ATTACH_FILE_MODE);
		} else {
			plugin_dav_error_exit(406, "can't copy it");
		}

		// COPY
		if(is_page($obj2->page)) touch(get_filename($obj2->page));
		$obj2->getstatus();
		$obj2->status['pass'] = ($pass !== TRUE && $pass !== NULL) ? md5($pass) : '';
		$obj2->putstatus();

		// MOVE(DELETE)
		if($_SERVER['REQUEST_METHOD'] == 'MOVE') {
			$obj1->delete($pass);
			if(file_exists($obj1->filename))
				plugin_dav_error_exit(406, "can't delete this file");
		}

		break;
	/*
	case 'MKCOL':
		// ページは作成可能
		// セキュリティは未検証
		// Windowsクライアントを考えると、
		// ページのリネームを考えないと無意味

		if (auth::check_role('readonly')) plugin_dav_error_exit(403, 'PKWK_READONLY prohibits editing');

		// 添付する際にパスワードまたは、管理者のみの場合は、認証を要求
		if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
			if(isset($req_headers['Authorization']))
				$pass = plugin_dav_getbasicpass($req_headers['Authorization']);
			//  else
			// PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'admin password' : 'password';
			//    plugin_dav_error_exit(401);
		}
		$path = $path_info;
		if(!isset($path)) plugin_dav_error_exit(403);
		// $path = mb_convert_encoding($path, SOURCE_ENCODING, 'UTF-8');

		if(preg_match('/^\/(.+)\/$/', $path, $matches) != 1)
			plugin_dav_error_exit(403);

		$page = str_replace($zslash, '/', $matches[1]);
		if(is_page($page)) plugin_dav_error_exit(403);

		// ここの辺りにもう一つチェックが必要かも
		page_write($page, "dav.php"); // write initial string to the page.
		break;
	*/

	case 'PROPPATCH':
		// ダミールーチン: Windows 7のクライアントは、PROPPATCHが
		//                   失敗するとファイルを消しに来るため仕方なく。
		//
		header('HTTP/1.1 207 Multi-Status');
		header('Content-Type: text/xml');
		$doc = plugin_dav_proppatch_dummy_response($_SERVER['REQUEST_URI']);
		echo $doc->saveXML();
		exit;

	case 'MKCOL':
	case 'LOCK':
	case 'UNLOCK':
	case 'POST':
		plugin_dav_error_exit(501); // Method not Implemented
		break;
	default:
		plugin_dav_error_exit(405); // Method not Allowed
	}
}

function plugin_dav_makemultistat($tree, $path, $depth)
{
	global $zslash;

	if(!isset($tree)) return NULL;

	// preg_match('/\/$/', $path) != 1 => 1:dir/ 0:dir/filename
	// !=1 は、ファイル名の場合は、それを除外
	// dir の場合に処理する
	if(is_array($tree) && preg_match('/\/$/', $path) != 1) return NULL;

	$ret = new DOMDocument();
	$ele = $ret->createElementNS('DAV:', 'D:multistatus');
	$ret->appendChild($ele);

	plugin_dav_makemultistat_sub($ret, $ele, $path, $tree);
	if(is_array($tree) && $depth !== '0'){
		foreach($tree as $key => $value){
			//$str = mb_convert_encoding(str_replace('/', $zslash, $key), 'UTF-8');
			$str = str_replace('/', $zslash, $key);
			$str = $path.rawurlencode($str);
			if(is_array($value)) $str .= '/';
			plugin_dav_makemultistat_sub($ret, $ele, $str, $value);
		}
	}
	return $ret;
}

function plugin_dav_makemultistat_sub(&$doc, &$ele, $name, $type)
{
	$res = $doc->createElementNS('DAV:', 'D:response');
	$ele->appendChild($res);
	$href = $doc->createElementNS('DAV:', 'D:href', $name);
	$res->appendChild($href);

	$propstat = $doc->createElementNS('DAV:', 'D:propstat');
	$res->appendChild($propstat);

	$prop = $doc->createElementNS('DAV:', 'D:prop');
	$propstat->appendChild($prop);

	$resourcetype = $doc->createElementNS('DAV:', 'D:resourcetype');
	$prop->appendChild($resourcetype);
	if(is_array($type)){
		$coll = $doc->createElementNS('DAV:', 'D:collection');
		$resourcetype->appendChild($coll);
	}
	else if($type->getstatus() && isset($type->size)){
		$getcontentlength = $doc->createElementNS('DAV:', 'D:getcontentlength');
		$getcontentlength->appendChild($doc->createTextNode(''.$type->size));
		$prop->appendChild($getcontentlength);
	}

	$stat = $doc->createElementNS('DAV:', 'D:status', 'HTTP/1.1 200 OK');
	$propstat->appendChild($stat);
}

function plugin_dav_error_exit($code, $msg = NULL, $url = NULL)
{
	global $auth_type, $realm;

        $array_msg = array(
                301 => array('msg1'=>'Moved',                   'msg2'=>''),
                401 => array('msg1'=>'Authorization Required',  'msg2'=>''),
                403 => array('msg1'=>'Forbidden',               'msg2'=>'Your request is forbideen.'),
                404 => array('msg1'=>'Not Found',               'msg2'=>'The file/directory you request is not found.'),
                405 => array('msg1'=>'Method not Allowed',      'msg2'=>'Your request is not allowd.'),
                406 => array('msg1'=>'Not acceptable',          'msg2'=>'Your request is not acceptable'),
                500 => array('msg1'=>'Internal Server Error',   'msg2'=>'Internal Server Error.'),
                501 => array('msg1'=>'Method not Implemented',  'msg2'=>'The method you request is not implemented.'),
        );

        if (!array_key_exists($code,$array_msg)) $code = 500;
        $msg1 = & $array_msg[$code]['msg1'];
        $msg2 = & $array_msg[$code]['msg2'];
        header('HTTP/1.1 '.$code.' '.$msg1);

        switch ($code) {
        case 301:
                header('Location: '.$url);
                exit;
        case 401:
		switch ($auth_type) {
		case 2:
			header('WWW-Authenticate: Digest realm="'.$realm.
				'", qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
			exit;
		default:
                	header('WWW-Authenticate: Basic realm="'.$realm.'"');
                	exit;
		}
		exit;
        }

        echo '<html><head>';
        echo '<title>'.$code.' '.$msg1.'</title>';
        echo '</head><body>';
        echo '<h1>'.$code.' '.$msg1.'</h1>';
        echo '<p>'.$msg2.'</p>';
        if(isset($msg)) echo '<p>'.htmlspecialchars($msg).'</p>';
        echo '<p>This script should be used with WebDAV protocol.</p>';
        echo '</body></html>';
        exit;
}

function plugin_dav_officious_message()
{
	global $scriptname;

	$myurl1 = plugin_dav_myurl1();
	$port = apache_getenv('SERVER_PORT');
	echo '<html>';
	echo '<head><title>officious message</title></head>';
	echo '<body>';
	echo '<p>Please use this script with WebDAV protocol.</p>';
	echo '<p>If your client OS is <font size=+2>Windows XP</font>,';

	if( strrpos($scriptname, '/') != 0){
		echo ' you should place this script in document root directory';
	}
	else if($_SERVER['HTTPS'] != 'on' && (!isset($port) || $port == 80)){
		$myurl2 = preg_replace('/^http:/', 'https:', $myurl1);
		echo ' you may be able to click';
		echo ' <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl1.'?">'.$myurl1.'?';
		echo '</a> or <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl2.'/">'.$myurl2.'/';
		echo '</a>. <br>Or do <font size=+2>"Add Network Place"';
		echo ' in "My Network Place"</font>';
	}
	else {
		echo ' you may be able to click';
		echo ' <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl1.'/">'.$myurl1.'/';
		echo '</a>. <br>Or do <font size=+2>"Add Network Place"';
		echo ' in "My Network Place"</font>';
	}

	echo '</p>';
	echo '<p>If your client OS is <font size=+2>Windows 7</font>, ';

	if($_SERVER['HTTPS'] != 'on' && (!isset($port) || $port == 80)){
		$myurl2 = preg_replace('/^http:/', 'https:', $myurl1);
		echo 'you can try <font size=+2>"net use w: '.$myurl1.'"</font>';
		echo ' or <font size=+2>"net use w: '.$myurl2.'".</font>';
		echo ' ("w:" is arbitary drive letter.)';
		echo '<br>Or you are rarely(at the very first time after booting?)';
		echo ' be able to click';
		echo ' <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl1.'">'.$myurl1.'</a>';
		echo ' or <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl2.'">'.$myurl2.'</a>.';
		echo '<br>(http: can be used only when the value in the regsitry key';
		echo ' "HKLM\SYSTEM\CurrentControlSet\Services\WebClient\Parameters\BasicAuthLevel" is "2")';
	}
	else {
		echo 'you can try <font size=+2>"net use w: '.$myurl1.'"</font>';
		echo ' ("w:" is arbitary drive letter.)';
		echo '<br>Or you are rarely(at the very first time after booting?)';
		echo ' be able to click';
		echo ' <a style="behavior: url(#default#AnchorClick);"';
		echo ' Folder="'.$myurl1.'">'.$myurl1.'</a>.';
	}

	echo '</p>';
	echo '</body>';
	echo '</html>';
}

function plugin_dav_myurl0()
{
	// $_SERVER['HTTPS'] - https かどうかの判定用
	// get_script_absuri();
	// rc - http://jo1upk.blogdns.net:80
	$url = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'];
	$port = apache_getenv('SERVER_PORT');
	if(isset($port) && $port != 443 && $_SERVER['HTTPS'] == 'on')
		$url .= ':'.$port;
	else if(isset($port) && $port != 80 && $_SERVER['HTTPS'] != 'on')
		$url .= ':'.$port;
	return $url;
}
function plugin_dav_myurl1()
{
	global $scriptname;
	return plugin_dav_myurl0() . $scriptname;
}
function plugin_dav_myurl()
{
	return plugin_dav_myurl0() . $_SERVER['REQUEST_URI'];
}

// このルーチンを何とかすると、
// 全角スラッシュの件が何とかなるかも。
function plugin_dav_maketree0()
{
	global $whatsnew, $attach_link;

	$root = array();
	$pagelist = auth::get_existpages();
	foreach($pagelist as $page){
		if(check_non_list($page)) continue;
		if($page == $whatsnew) continue;
		if(PLUGIN_DAV_SHOWONLYEDITABLE && !is_editable($page)) continue;

		$root[$page] = array();
	}

	// FIXME
	// 認証時は、この制限を回避するようにする。
	// 現状は、添付ファイル非表示の場合は抑止
	if (!$attach_link) return $root;

	$attaches = & new AttachPages('');
	foreach($attaches->pages as $key => $val){
		if(check_non_list($key)) continue;
		if(!check_readable($key, false, false)) continue;
		if(PLUGIN_DAV_SHOWONLYEDITABLE && !is_editable($key)) continue;

		if(!isset($root[$key])) $root[$key] = array();
		if(is_array($root[$key])){
			foreach($val->files as $file => $arr){
				if(isset($arr[0])) $root[$key][$file] = $arr[0];
			}
		}
	}

	return $root;
}

function plugin_dav_maketree($path)
{
	global $zslash;
	$page = null;

	$tree = plugin_dav_maketree0();
	if(empty($path)) return $tree;

	// $path = mb_convert_encoding($path, SOURCE_ENCODING, 'UTF-8');

	$pieces = explode('/', $path);
	if(!empty($pieces[0])) return NULL;
	$count = count($pieces);
	for($i = 1; $i < $count; $i++){
		if(empty($pieces[$i])){
			if(is_array($tree)){
				continue;
			}
			else
				return NULL;
		}
		else if(is_array($tree)){
			$pieces[$i] = str_replace($zslash, '/', $pieces[$i]);

			if(isset($tree[$pieces[$i]]))
				$tree = $tree[$pieces[$i]]; 
			else
				return NULL;

			if(is_array($tree)){
				if($page == null) $page = $pieces[$i];
				else $page .= '/'.$pieces[$i];
			}
		}
		else
			return NULL;
	}

	if(isset($page) && !check_readable($page, false, false)) plugin_dav_error_exit(401); // 'user/password'

	return $tree;
}

function plugin_dav_getfileobj($path, $readonly=true)
{
	global $zslash;

	if(!isset($path)) return NULL;

	// $path = mb_convert_encoding($path, SOURCE_ENCODING, 'UTF-8');

	if(preg_match('/^\/(.+)\/([^\/]+)$/', $path, $matches) != 1)
		return NULL;

	$page = str_replace($zslash, '/', $matches[1]);
	if(!is_page($page)) return NULL;
	if(!$readonly && !is_editable($page)) return NULL;

	if(!check_readable($page, false, false)) plugin_dav_error_exit(401); // 'user/password'
	if(!$readonly && !check_editable($page, false, false)) plugin_dav_error_exit(401); // 'user/password'

	return new AttachFile($page, $matches[2]);
}

function plugin_dav_getbasicpass($str)
{
	if(preg_match('/^Basic (.+)$/', $str, $matches) != 1)
		return NULL;
	$str2 = base64_decode($matches[1]);
	if($str2 === false) return NULL;
	if(preg_match('/^[^:]+:(.+)$/', $str2, $matches2) != 1)
		return NULL;
	if(!isset($matches2[1])) return NULL;
	if($matches2[1] == '') return NULL;
	return $matches2[1];
}

function plugin_dav_proppatch_dummy_response($path)
{
	$doc = new DOMDocument();
	$ele = $doc->createElementNS('DAV:', 'D:multistatus');
	$doc->appendChild($ele);
	$res = $doc->createElementNS('DAV:', 'D:response');
	$ele->appendChild($res);
	$href = $doc->createElementNS('DAV:', 'D:href', $path);
	$res->appendChild($href);
	$propstat = $doc->createElementNS('DAV:', 'D:propstat');
	$res->appendChild($prospstat);
	$prop = $doc->createElementNS('DAV:', 'D:prop');
	$propstat->appendChild($prop);
	$prop->appendChild($doc->createElementNS('urn:schemas-microsoft-com:', 'Z:Win32CreationTime'));
	$prop->appendChild($doc->createElementNS('urn:schemas-microsoft-com:', 'Z:Win32LastAccessTime'));
	$prop->appendChild($doc->createElementNS('urn:schemas-microsoft-com:', 'Z:Win32LastModifiedTime'));
	$prop->appendChild($doc->createElementNS('urn:schemas-microsoft-com:', 'Z:Win32FileAttributes'));
	$stat = $doc->createElementNS('DAV:', 'D:status', 'HTTP/1.1 200 OK');
	$propstat->appendChild($stat);
	return $doc;
}

?>
