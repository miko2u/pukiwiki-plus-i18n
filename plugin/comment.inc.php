<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: comment.inc.php,v 1.36.8 2006/04/27 23:24:00 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
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

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');

	// Petit SPAM Check (Client(Browser)-Server Ticket Check)
	if (!isset($post['encode_hint']) && PKWK_ENCODING_HINT == '') {
		return plugin_comment_write();
	} elseif (isset($post['encode_hint']) && $post['encode_hint'] == PKWK_ENCODING_HINT) {
		return plugin_comment_write();
	}

	// If SPAM, goto jail.
	return plugin_comment_honeypot();
}

function plugin_comment_write()
{
	global $script, $vars, $now;
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
		header("Location: $script?".rawurlencode($vars['refpage']));
		exit;
	}

	$vars['page'] = $vars['refer'];

	return $retvars;
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
	global $vars, $digest;	//, $_btn_comment, $_btn_name, $_msg_comment;
	static $numbers = array();
	static $comment_cols = PLUGIN_COMMENT_SIZE_MSG;

	$_btn_name    = _("Name: ");
	$_btn_comment = _("Post Comment");
	$_msg_comment = _("Comment: ");

	// if (PKWK_READONLY) return ''; // Show nothing
	if (auth::check_role('readonly')) return ''; // Show nothing
	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$comment_no = $numbers[$vars['page']]++;

	$options = func_num_args() ? func_get_args() : array();
	if (in_array('noname', $options)) {
		$nametags = '<label for="_p_comment_comment_' . $comment_no . '">' .
			$_msg_comment . '</label>';
	} else {
		$nametags = '<label for="_p_comment_name_' . $comment_no . '">' .
			$_btn_name . '</label>' .
			'<input type="text" name="name" id="_p_comment_name_' .
			$comment_no .  '" size="' . PLUGIN_COMMENT_SIZE_NAME .
			'" />' . "\n";
	}

	$helptags = edit_form_assistant();
	$nodate = in_array('nodate', $options) ? '1' : '0';
	$above  = in_array('above',  $options) ? '1' :
		(in_array('below', $options) ? '0' : PLUGIN_COMMENT_DIRECTION_DEFAULT);
	$refpage = '';

	$script = get_script_uri();
	$s_page = htmlspecialchars($vars['page']);
	$string = <<<EOD
<br />
<form action="$script" method="post">
 <div class="commentform" onmouseup="pukiwiki_pos()" onkeyup="pukiwiki_pos()">
  <input type="hidden" name="refpage" value="$refpage" />
  <input type="hidden" name="plugin" value="comment" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above"  value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $nametags
  <input type="text"   name="msg" id="_p_comment_comment_{$comment_no}" size="$comment_cols" />
  <input type="submit" name="comment" value="$_btn_comment" />
  $helptags
 </div>
</form>
EOD;

	return $string;
}
?>
