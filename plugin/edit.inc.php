<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php,v 1.41.39 2008/03/30 23:19:00 upk Exp $
// Copyright (C)
//   2005-2008 PukiWiki Plus! Team
//   2001-2007 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Edit plugin (cmd=edit)
// Plus!NOTE:(policy)not merge official cvs(1.40->1.41) See Question/181

// Remove #freeze written by hand
define('PLUGIN_EDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');

// Define part-edit area - 'compat':1.4.4compat, 'level':level
defined('PLUGIN_EDIT_PARTAREA') or define('PLUGIN_EDIT_PARTAREA', 'compat');

function plugin_edit_action()
{
	// global $vars, $_title_edit, $load_template_func;
	global $vars, $load_template_func;

	// if (PKWK_READONLY) die_message( _('PKWK_READONLY prohibits editing') );
	if (auth::check_role('readonly')) die_message( _('PKWK_READONLY prohibits editing') );

	if (PKWK_READONLY == ROLE_AUTH && auth::get_role_level() > ROLE_AUTH) {
		die_message( _('PKWK_READONLY prohibits editing') );
	}

	if (isset($vars['realview'])) {
		return plugin_edit_realview();
	}

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page, true, true);

	if (!is_page($page) && auth::is_check_role(PKWK_CREATE_PAGE)) {
		die_message( _('PKWK_CREATE_PAGE prohibits editing') );
	}

	if (isset($vars['preview']) || ($load_template_func && isset($vars['template']))) {
		return plugin_edit_preview();
	} else if (isset($vars['write'])) {
		return plugin_edit_write();
	} else if (isset($vars['cancel'])) {
		return plugin_edit_cancel();
	}

	$source = get_source($page);
	auth::is_role_page($source);

	$postdata = $vars['original'] = join('', $source);
	if (!empty($vars['id']))
	{
		$postdata = plugin_edit_parts($vars['id'],$source);
		if ($postdata === FALSE)
		{
			unset($vars['id']); // なかったことに :)
			$postdata = $vars['original'];
		}
	}
	if ($postdata == '') $postdata = auto_template($page);

	return array('msg'=> _('Edit of  $1'), 'body'=>edit_form($page, $postdata));
}

// Preview by Ajax
function plugin_edit_realview()
{
	global $vars;

	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '' ,$vars['msg']);
	$postdata = $vars['msg'];

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
	}
	// Feeding start
	pkwk_common_headers();
	header('Content-type: text/xml; charset=UTF-8');
	print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	print $postdata;

	$longtaketime = getmicrotime() - MUTIME;
	$taketime     = sprintf('%01.03f', $longtaketime);
	print '<span class="small1">(Time:' . $taketime . ')</span>';
	exit;
}

// Preview
function plugin_edit_preview()
{
	global $vars;
	// global $_title_preview, $_msg_preview, $_msg_preview_delete;

	$page = isset($vars['page']) ? $vars['page'] : '';

	// Loading template
	if (isset($vars['template_page']) && is_page($vars['template_page'])) {

		$vars['msg'] = join('', get_source($vars['template_page']));

		// Cut fixed anchors
		$vars['msg'] = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);
	}

	// Delete "#freeze" command for form edit.
	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '' ,$vars['msg']);
	$postdata = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = _('To confirm the changes, click the button at the bottom of the page') . '<br />' . "\n";
	if ($postdata == '')
		$body .= '<strong>' .
			 _('(The contents of the page are empty. Updating deletes this page.)') .
			 '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	$body .= edit_form($page, $vars['msg'], $vars['digest'], FALSE);

	return array('msg'=> _('Preview of  $1'), 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
// NOTE: Plus! is not compatible for 1.4.4+ style(compatible for 1.4.3 style)
function plugin_edit_inline()
{
	static $usage = '&edit(pagename,anchor);';

	global $script, $vars, $fixed_heading_edited;
	global $_symbol_paraedit;

	if (!$fixed_heading_edited || is_freeze($vars['page']) || auth::check_role('readonly')) {
		return '';
	}

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);
	if ($s_label == '')	{
		$s_label = $_symbol_paraedit;
	}

	list($page,$id) = array_pad($args,2,'');
	if (!is_page($page)) {
		$page = $vars['page'];
	}
	if ($id != '') {
		$id = '&amp;id='.rawurlencode($id);
	}
	$r_page = rawurlencode($page);
	return '<a class="anchor_super" href="' . $script . '?cmd=edit&amp;page=' . $r_page . $id . '">' . $s_label . '</a>';
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $post, $vars, $trackback;
	global $notimeupdate, $do_update_diff_table;
	global $use_trans_sid_address;
//	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
//	global $_msg_invalidpass;
	$_title_deleted = _(' $1 was deleted');
	$_msg_invalidpass = _('Invalid password.');

	$page   = isset($vars['page']) ? $vars['page']     : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';
	$partid = isset($vars['id'])     ? $vars['id']     : '';
	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';

	// Check Validate and Ticket
	if ($notimestamp && !is_page($page)) {
		return plugin_edit_honeypot();
	}

	// SPAM Check (Client(Browser)-Server Ticket Check)
	if (isset($post['encode_hint']) && $post['encode_hint'] != PKWK_ENCODING_HINT)
		return plugin_edit_honeypot();
	if (!isset($post['encode_hint']) && PKWK_ENCODING_HINT != '')
		return plugin_edit_honeypot();

	// Validate
	if (is_spampost(array('msg')))
		return plugin_edit_honeypot();

	// Paragraph edit mode
	if ($partid) {
		$source = preg_split('/([^\n]*\n)/', $vars['original'], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		if (plugin_edit_parts($partid, $source, $vars['msg']) !== FALSE) {
			$vars['msg'] = join('', $source);
		} else {
			$vars['msg'] = rtrim($vars['original']) . "\n\n" . $vars['msg'];
		}
	}

	// Delete "#freeze" command for form edit.
	$vars['msg'] = preg_replace('/^#freeze\s*$/im', '', $vars['msg']);
	$msg = & $vars['msg']; // Reference

	$retvars = array();

	// Collision Detection
	$oldpagesrc = get_source($page, TRUE, TRUE);
	$oldpagemd5 = md5($oldpagesrc);

	if ($digest != $oldpagemd5) {
		$vars['digest'] = $oldpagemd5; // Reset
		$original = isset($vars['original']) ? $vars['original'] : '';
		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $msg, $original);

		$_msg_collided_auto =
		_('It seems that someone has already updated this page while you were editing it.<br />') .
		_('The collision has been corrected automatically, but there may still be some problems with the page.<br />') .
		_('To confirm the changes to the page, press [Update].<br />');

		$_msg_collided =
		_('It seems that someone has already updated this page while you were editing it.<br />') .
		_(' + is placed at the beginning of a line that was newly added.<br />') .
		_(' ! is placed at the beginning of a line that has possibly been updated.<br />') .
		_(' Edit those lines, and submit again.');

		$retvars['msg'] = _('On updating  $1, a collision has occurred.');
		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided)."\n";
		$retvars['body'] .= $do_update_diff_table;

		unset($vars['id']);	// Change edit all-text of pages(from para-edit)
		$retvars['body'] .= edit_form($page, $postdata_input, $oldpagemd5, FALSE);
		return $retvars;
	}

	// Action?
	if ($add) {
		// Add
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $msg . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $msg;
		}
	} else {
		// Edit or Remove
		$postdata = & $msg; // Reference
	}

	// NULL POSTING, OR removing existing page
	if ($postdata == '') {
		page_write($page, $postdata);
		$retvars['msg'] = $_title_deleted;
		$retvars['body'] = str_replace('$1', htmlspecialchars($page), $_title_deleted);
		if ($trackback) tb_delete($page);
		return $retvars;
	}

	// $notimeupdate: Checkbox 'Do not change timestamp'
//	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
//	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
	if ($notimeupdate > 1 && $notimestamp && auth::check_role('role_adm_contents') && !pkwk_login($vars['pass'])) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		$retvars['body'] .= edit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimestamp);
	pkwk_headers_sent();
	if (isset($vars['refpage']) && $vars['refpage'] != '') {
		if ($partid) {
			header('Location: ' . get_page_location_uri($vars['refpage'],'',rawurlencode($partid)));
		} else {
			header('Location: ' . get_page_location_uri($vars['refpage']));
		}
	} else {
		if ($partid) {
			header('Location: ' . get_page_location_uri($page,'',rawurlencode($partid)));
		} else {
			header('Location: ' . get_page_location_uri($page));
		}
	}
	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $vars;
	pkwk_headers_sent();
	header('Location: ' . get_page_location_uri($vars['page']));
	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_honeypot()
{
	// SPAM Logging
	honeypot_write();

	// Same as "Cancel" action
	return plugin_edit_cancel();
}

// Replace/Pickup a part of source
function plugin_edit_parts($id, &$source, $postdata='')
{
	$postdata = rtrim($postdata) . "\n";
	$id = preg_quote($id);
	if (PLUGIN_EDIT_PARTAREA == 'level') {
		$start = -1;
	        $final = count($source);
	        $multiline = 0;
	        $matches = array();
	        foreach ($source as $i => $line) {
			// multiline plugin. refer lib/convert_html
			if(defined('PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK') && PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK === 0) {
				if ($multiline < 2) {
					if (preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $line, $matches)) {
						$multiline  = strlen($matches[3]);
					}
				} else {
					if (preg_match('/^\}{' . $multiline . '}/', $line, $matches)) {
						$multiline = 0;
					}
					continue;
				}
			}

			if ($start === -1) {
				if (preg_match('/^(\*{1,3})(.*?)\[#(' . $id . ')\](.*?)$/m', $line, $matches)) {
					$start = $i;
					$hlen = strlen($matches[1]);
				}
			} else {
				if (preg_match('/^(\*{1,3})/m', $line, $matches) && strlen($matches[1]) <= $hlen) {
					$final = $i;
					break;
				}
			}
		}
		if ($start !== -1) {
			return join('', array_splice($source, $start, $final - $start, $postdata));
		}
	} else {
		$heads = preg_grep('/^\*{1,3}.+$/', $source);
		$heads[count($source)] = ''; // sentinel
		while (list($start, $line) = each($heads)) {
			if (preg_match("/\[#$id\]/", $line)) {
				list($final, $line) = each($heads);
				return join('', array_splice($source, $start, $final - $start, $postdata));
			}
		}
	}
	return FALSE;
}
?>
