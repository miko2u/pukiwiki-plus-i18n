<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: auth.php,v 1.19.14 2007/04/13 01:26:00 upk Exp $
// Copyright (C)
//   2005-2007 PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Authentication related functions

define('PKWK_PASSPHRASE_LIMIT_LENGTH', 512);

// Passwd-auth related ----

function pkwk_login($pass = '')
{
	global $adminpass;

	// if (! PKWK_READONLY && isset($adminpass) &&
	if (! auth::check_role('readonly') && isset($adminpass) &&
		pkwk_hash_compute($pass, $adminpass) === $adminpass) {
		return TRUE;
	} else {
		sleep(2);       // Blocking brute force attack
		return FALSE;
	}
}

// Compute RFC2307 'userPassword' value, like slappasswd (OpenLDAP)
// $phrase : Pass-phrase
// $scheme : Specify '{scheme}' or '{scheme}salt'
// $prefix : Output with a scheme-prefix or not
// $canonical : Correct or Preserve $scheme prefix
function pkwk_hash_compute($phrase = '', $scheme = '{php_md5}', $prefix = TRUE, $canonical = FALSE)
{
	if (! is_string($phrase) || ! is_string($scheme)) return FALSE;

	if (strlen($phrase) > PKWK_PASSPHRASE_LIMIT_LENGTH)
		die('pkwk_hash_compute(): malicious message length');

	// With a {scheme}salt or not
	$matches = array();
	if (preg_match('/^(\{.+\})(.*)$/', $scheme, $matches)) {
		$scheme = & $matches[1];
		$salt   = & $matches[2];
	} else if ($scheme != '') {
		$scheme  = ''; // Cleartext
		$salt    = '';
	}

	// Compute and add a scheme-prefix
	switch (strtolower($scheme)) {

	// PHP crypt()
	case '{x-php-crypt}' :
		$hash = ($prefix ? ($canonical ? '{x-php-crypt}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// PHP md5()
	case '{x-php-md5}'   :
		$hash = ($prefix ? ($canonical ? '{x-php-md5}' : $scheme) : '') .
			md5($phrase);
		break;

	// PHP sha1()
	case '{x-php-sha1}'  :
		$hash = ($prefix ? ($canonical ? '{x-php-sha1}' : $scheme) : '') .
			sha1($phrase);
		break;

	// LDAP CRYPT
	case '{crypt}'       :
		$hash = ($prefix ? ($canonical ? '{CRYPT}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// LDAP MD5
	case '{md5}'         :
		$hash = ($prefix ? ($canonical ? '{MD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase)));
		break;

	// LDAP SMD5
	case '{smd5}'        :
		// MD5 Key length = 128bits = 16bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 16) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SMD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase . $salt)) . $salt);
		break;

	// LDAP SHA
	case '{sha}'         :
		$hash = ($prefix ? ($canonical ? '{SHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase)));
		break;

	// LDAP SSHA
	case '{ssha}'        :
		// SHA-1 Key length = 160bits = 20bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 20) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SSHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase . $salt)) . $salt);
		break;

	// LDAP CLEARTEXT and just cleartext
	case '{cleartext}'   : /* FALLTHROUGH */
	case ''              :
		$hash = ($prefix ? ($canonical ? '{CLEARTEXT}' : $scheme) : '') . $phrase;
		break;

	// Invalid scheme
	default:
		$hash = FALSE;
		break;
	}

	return $hash;
}


// Basic-auth related ----

// Check edit-permission
function check_editable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $script;
	global $_title, $_string;

	if (edit_auth($page, $auth_flag, $exit_flag) && is_editable($page)) {
		// Editable
		return TRUE;
	} else {
		// Not editable
		if ($exit_flag === FALSE) {
			return FALSE; // Without exit
		} else {
			// With exit
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $_title['cannotedit']);
			if (is_freeze($page))
				$body .= '(<a href="' . $script . '?cmd=unfreeze&amp;page=' .
					rawurlencode($page) . '">' . $_string['unfreeze'] . '</a>)';
			$page = str_replace('$1', make_search($page), $_title['cannotedit']);
			catbody($title, $page, $body);
			exit;
		}
	}
}

// Check read-permission
function check_readable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	return read_auth($page, $auth_flag, $exit_flag);
}

function edit_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $edit_auth, $edit_auth_pages;
	global $_title;

	if (auth::check_role('readonly')) return FALSE;
	return $edit_auth ? basic_auth($page, $auth_flag, $exit_flag, $edit_auth_pages, $_title['cannotedit']) : TRUE;
}

function read_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $read_auth, $read_auth_pages;
	global $_title;

	return $read_auth ? basic_auth($page, $auth_flag, $exit_flag, $read_auth_pages, $_title['cannotread']) : TRUE;
}

// Basic authentication
function basic_auth($page, $auth_flag, $exit_flag, $auth_pages, $title_cannot)
{
	global $auth_users, $auth_method_type, $auth_type;
	global $realm;

	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = get_source($page, TRUE, TRUE); // Its contents
	}

	$user_list = array();
	foreach($auth_pages as $key=>$val)
		if (preg_match($key, $target_str))
			$user_list = array_merge($user_list, explode(',', $val));

	if (empty($user_list)) return TRUE; // No limit

	if (! auth::check_role('role_adm_contents')) return TRUE; // 既にコンテンツ管理者

	// Digest
	if ($auth_type == 2) {
		if (auth::auth_digest($realm,$auth_users)) return TRUE;
		// Auth failed
		if ($auth_flag || $exit_flag) {
			pkwk_common_headers();
		}
		if ($exit_flag) {
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $title_cannot);
			$page = str_replace('$1', make_search($page), $title_cannot);
			catbody($title, $page, $body);
			exit;
		}
		return FALSE;
	}

	$matches = array();
	if (! isset($_SERVER['PHP_AUTH_USER']) &&
		! isset($_SERVER ['PHP_AUTH_PW']) &&
		isset($_SERVER['HTTP_AUTHORIZATION']) &&
		preg_match('/^Basic (.*)$/', $_SERVER['HTTP_AUTHORIZATION'], $matches))
	{

		// Basic-auth with $_SERVER['HTTP_AUTHORIZATION']
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
			explode(':', base64_decode($matches[1]));
	}

	// if (PKWK_READONLY ||
	// if (auth::check_role('readonly') ||
	//	! isset($_SERVER['PHP_AUTH_USER']) ||
	if (! isset($_SERVER['PHP_AUTH_USER']) ||
		! in_array($_SERVER['PHP_AUTH_USER'], $user_list) ||
		! isset($auth_users[$_SERVER['PHP_AUTH_USER']]) ||
		pkwk_hash_compute(
			$_SERVER['PHP_AUTH_PW'],
			$auth_users[$_SERVER['PHP_AUTH_USER']][0]
			) !== $auth_users[$_SERVER['PHP_AUTH_USER']][0])
	{
		// Auth failed
		if ($auth_flag || $exit_flag) {
			pkwk_common_headers();
		}
		if ($auth_flag) {
			header('WWW-Authenticate: Basic realm="'.$realm.'"');
			header('HTTP/1.0 401 Unauthorized');
		}
		if ($exit_flag) {
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $title_cannot);
			$page = str_replace('$1', make_search($page), $title_cannot);
			catbody($title, $page, $body);
			exit;
		}
		return FALSE;
	} else {
		return TRUE;
	}
}
?>
