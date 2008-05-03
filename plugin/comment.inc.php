<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: comment.inc.php,v 1.36.21 2008/05/03 23:25:00 upk Exp $
// Copyright (C)
//   2005-2008 PukiWiki Plus! Team
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Comment plugin

// ----
defined('PLUGIN_COMMENT_DIRECTION_DEFAULT') or define('PLUGIN_COMMENT_DIRECTION_DEFAULT', '1'); // 1: above 0: below
defined('PLUGIN_COMMENT_SIZE_MSG') or define('PLUGIN_COMMENT_SIZE_MSG',  68);
defined('PLUGIN_COMMENT_SIZE_NAME') or define('PLUGIN_COMMENT_SIZE_NAME', 15);

// ----
define('PLUGIN_COMMENT_FORMAT_MSG',  '$msg');
define('PLUGIN_COMMENT_FORMAT_NAME', '[[$name]]');
define('PLUGIN_COMMENT_FORMAT_NOW',  '&new{$now};');
define('PLUGIN_COMMENT_FORMAT_STRING', "\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");

function plugin_comment_action()
{
	global $vars, $post;

	// Petit SPAM Check (Client(Browser)-Server Ticket Check)
	$spam = FALSE;
	if (isset($post['encode_hint']) && $post['encode_hint'] != '') {
		if (PKWK_ENCODING_HINT != $post['encode_hint']) $spam = TRUE;
	} else {
		if (PKWK_ENCODING_HINT != '') $spam = TRUE;
	}

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');

	if (!is_page($vars['refer']) && auth::is_check_role(PKWK_CREATE_PAGE)) {
		die_message( _('PKWK_CREATE_PAGE prohibits editing') );
	}

	// If SPAM, goto jail.
	if ($spam) return plugin_comment_honeypot();
	return plugin_comment_write();
}

function plugin_comment_write()
{
	global $vars, $now;
	global $_no_name;
//	global $_msg_comment_collided, $_title_comment_collided, $_title_updated;
	$_title_updated = _("$1 was updated");
	$_title_comment_collided = _("On updating  $1, a collision has occurred.");
	$_msg_comment_collided   = _("It seems that someone has already updated the page you were editing.<br />") .
	                           _("The comment was added, alhough it may be inserted in the wrong position.<br />");

	if (! isset($vars['msg'])) return array('msg'=>'', 'body'=>''); // Do nothing

	// Validate
	if (is_spampost(array('msg')))
		return plugin_comment_honeypot();

	$vars['msg'] = str_replace("\n", '', $vars['msg']); // Cut LFs
	$head = '';
	$match = array();
	if (preg_match('/^(-{1,2})-*\s*(.*)/', $vars['msg'], $match)) {
		$head        = & $match[1];
		$vars['msg'] = & $match[2];
	}
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing

	$comment  = str_replace('$msg', $vars['msg'], PLUGIN_COMMENT_FORMAT_MSG);

	list($nick, $vars['name'], $disabled) = plugin_comment_get_nick();

	if(isset($vars['name']) || ($vars['nodate'] != '1')) {
		$_name = (! isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
		$_name = ($_name == '') ? '' : str_replace('$name', $_name, PLUGIN_COMMENT_FORMAT_NAME);
		$_now  = ($vars['nodate'] == '1') ? '' :
			str_replace('$now', $now, PLUGIN_COMMENT_FORMAT_NOW);
		$comment = str_replace("\x08MSG\x08",  $comment, PLUGIN_COMMENT_FORMAT_STRING);
		$comment = str_replace("\x08NAME\x08", $_name, $comment);
		$comment = str_replace("\x08NOW\x08",  $_now,  $comment);
	}
	$comment = '-' . $head . ' ' . $comment;

	$postdata    = '';
	$comment_no  = 0;
	$above       = (isset($vars['above']) && $vars['above'] == '1');
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#comment/i', $line) && $comment_no++ == $vars['comment_no']) {
			if ($above) {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n" .
					"\n";  // Insert one blank line above #commment, to avoid indentation
			} else {
				$postdata = rtrim($postdata) . "\n" .
					$comment . "\n"; // Insert one blank line below #commment
			}
		}
		if ($above) $postdata .= $line;
	}

	$title = $_title_updated;
	$body = '';
	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $_title_comment_collided;
		$body  = $_msg_comment_collided . make_pagelink($vars['refer']);
	}

	page_write($vars['refer'], $postdata);

	$retvars['msg']  = $title;
	$retvars['body'] = $body;

	if ($vars['refpage']) {
		header('Location: ' . get_page_location_uri($vars['refpage']));
		exit;
	}

	$vars['page'] = $vars['refer'];

	return $retvars;
}

function plugin_comment_get_nick()
{
	global $vars, $_no_name;

	$name = (empty($vars['name'])) ? $_no_name : $vars['name'];
	if (PKWK_READONLY != ROLE_AUTH) return array($name,$name,'');

	$auth_key = auth::get_user_name();
	if (empty($auth_key['nick'])) return array($name,$name,'');
	if (auth::get_role_level() < ROLE_AUTH) return array($auth_key['nick'],$name,'');
	$link = (empty($auth_key['profile'])) ? $auth_key['nick'] : $auth_key['nick'].'>'.$auth_key['profile'];
	return array($auth_key['nick'], $link, "disabled=\"disabled\"");
}

// Cancel (Back to the page / Escape edit page)
function plugin_comment_honeypot()
{
	// Logging for SPAM Report
	honeypot_write();

	// Same as "Cancel" action
	return array('msg'=>'', 'body'=>''); // Do nothing
}

function plugin_comment_convert()
{
	global $vars, $digest, $script;	//, $_btn_comment, $_btn_name, $_msg_comment;
	static $numbers = array();
	static $all_numbers = 0;
	static $comment_cols = PLUGIN_COMMENT_SIZE_MSG;

	$_btn_name    = _("Name: ");
	$_btn_comment = _("Post Comment");
	$_msg_comment = _("Comment: ");

	$auth_guide = '';
	if (PKWK_READONLY == ROLE_AUTH) {
		exist_plugin('login');
		$auth_guide = do_plugin_inline('login');
	}

	// if (PKWK_READONLY) return ''; // Show nothing
	if (auth::check_role('readonly')) return $auth_guide;
	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$comment_no = $numbers[$vars['page']]++;
	$comment_all_no = $all_numbers++;

	$options = func_num_args() ? func_get_args() : array();

	list($user, $link, $disabled) = plugin_comment_get_nick();

	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_comment_comment_' . $comment_all_no . '">' .
			$_msg_comment . '</label>';
	} else {
		$nametags = '<label for="_p_comment_name_' . $comment_all_no . '">' .
			$_btn_name . '</label>' .
			'<input type="text" name="name" id="_p_comment_name_' .
			$comment_all_no .  '" size="' . PLUGIN_COMMENT_SIZE_NAME .
			'" value="'.$user.'"'.$disabled.' />' . "\n";
	}

	$helptags = edit_form_assistant();
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' :
		(in_array('below', $options) ? '0' : PLUGIN_COMMENT_DIRECTION_DEFAULT);
	$refpage = '';

	$s_page = htmlspecialchars($vars['page']);

	$ticket = md5(MUTIME);
	if (function_exists('pkwk_session_start') && pkwk_session_start() != 0) {
		$keyword = $ticket;
		$_SESSION[$keyword] = md5(get_ticket() . $digest);
	}

	$string = <<<EOD
<br />
$auth_guide
<form action="$script" method="post">
 <div class="commentform" onmouseup="pukiwiki_pos()" onkeyup="pukiwiki_pos()">
  <input type="hidden" name="refpage" value="$refpage" />
  <input type="hidden" name="plugin" value="comment" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="ticket" value="$ticket" />
  $nametags
  <input type="text"   name="msg" id="_p_comment_comment_{$comment_all_no}" size="$comment_cols" />
  <input type="submit" name="comment" value="$_btn_comment" />
  $helptags
 </div>
</form>
EOD;

	return $string;
}
?>
