<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: auth.php,v 1.7.5 2005/03/10 19:32:38 miko Exp $
//
// Basic authentication related functions

// �Խ��Բ�ǽ�ʥڡ������Խ����褦�Ȥ����Ȥ�
function check_editable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $script;
	$_title_cannotedit = _('$1 is not editable');
	$_msg_unfreeze = _('Unfreeze');

	if (edit_auth($page, $auth_flag, $exit_flag) && is_editable($page))
		return TRUE;

	if (! $exit_flag) return FALSE;

	$body = $title = str_replace('$1', htmlspecialchars(strip_bracket($page)), $_title_cannotedit);

	if (is_freeze($page))
		$body .= '(<a href="' . $script . '?cmd=unfreeze&amp;page=' .
			rawurlencode($page) . '">' . $_msg_unfreeze . '</a>)';

	$page = str_replace('$1', make_search($page), $_title_cannotedit);

	catbody($title, $page, $body);
	exit;
}

// �����Բ�ǽ�ʥڡ�����������褦�Ȥ����Ȥ� (��)
function check_readable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	return read_auth($page, $auth_flag, $exit_flag);
}

// �Խ�ǧ��
function edit_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $edit_auth, $edit_auth_pages; //, $_title_cannotedit;

	return $edit_auth ? basic_auth($page, $auth_flag, $exit_flag, $edit_auth_pages, _('$1 is not editable')) : TRUE;
}

// ����ǧ��
function read_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $read_auth, $read_auth_pages; //, $_title_cannotread;

	return $read_auth ? basic_auth($page, $auth_flag, $exit_flag, $read_auth_pages, _('$1 is not readable')) : TRUE;
}

// Basic authentication
function basic_auth($page, $auth_flag, $exit_flag, $auth_pages, $title_cannot)
{
	global $auth_users, $auth_method_type;
//	global $_msg_auth

	$msg_auth = _('PukiWikiAuth');

	// ǧ������Ƚ���о�
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		// �ڡ���̾�ǥ����å�����
		$target_str = $page;
	} else if ($auth_method_type == 'contents') {
		// �ڡ������ʸ����ǥ����å�����
		$target_str = join('', get_source($page));
	}

	// ���פ����ѥ������������줿�桼���Υꥹ��
	$user_list = array();
	foreach($auth_pages as $key=>$val) {
		if (preg_match($key, $target_str))
			$user_list = array_merge($user_list, explode(',', $val));
	}

	if (empty($user_list)) return TRUE; // No limit

	// PHP_AUTH* �ѿ���̤����ξ��
	$matches = array();
	if (!isset($_SERVER['PHP_AUTH_USER'])
		&& ! isset($_SERVER ['PHP_AUTH_PW'])
		&& isset($_SERVER['HTTP_AUTHORIZATION'])
		&& preg_match('/^Basic (.*)$/', $_SERVER['HTTP_AUTHORIZATION'], $matches))
	{
		// HTTP_AUTHORIZATION �ѿ�����Ѥ��� Basic ǧ��
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
			explode(':', base64_decode($matches[1]));
	}

	// �桼���ꥹ�Ȥ˴ޤޤ�뤤���줫�Υ桼����ǧ�ڤ�����OK
	if (PKWK_READONLY
		|| ! isset($_SERVER['PHP_AUTH_USER'])
		|| ! in_array($_SERVER['PHP_AUTH_USER'], $user_list)
		|| ! isset($auth_users[$_SERVER['PHP_AUTH_USER']])
		|| $auth_users[$_SERVER['PHP_AUTH_USER']] != md5($_SERVER['PHP_AUTH_PW']))
	{
		pkwk_common_headers();
		if ($auth_flag) {
			header('WWW-Authenticate: Basic realm="'.$_msg_auth.'"');
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
	}
	return TRUE;
}
?>
