<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php,v 1.40.22 2006/12/25 14:59:24 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
//   2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Edit plugin (cmd=edit)

// Remove #freeze written by hand
define('PLUGIN_EDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');

function plugin_edit_action()
{
	global $vars, $_title_edit, $load_template_func;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = isset($vars['page']) ? $vars['page'] : '';

	check_editable($page, true, true);

	if (isset($vars['preview']) || ($load_template_func && isset($vars['template']))) {
		return plugin_edit_preview();
	} else if (isset($vars['write'])) {
		return plugin_edit_write();
	} else if (isset($vars['cancel'])) {
		return plugin_edit_cancel();
	}

	$source = get_source($page);
	$postdata = $vars['original'] = @join('', $source);
	if (isset($vars['id']) && $vars['id'] != '') {
		$postdata = plugin_edit_parts($vars['id'], $source);
		if ($postdata === FALSE) {
			unset($vars['id']);
			$postdata = $vars['original'];
		}
	}
	if ($postdata == '') $postdata = auto_template($page);

	return array('msg'=>$_title_edit, 'body'=>edit_form($page, $postdata));
}

// Preview
function plugin_edit_preview()
{
	global $vars;
	global $_title_preview, $_msg_preview, $_msg_preview_delete;

	$page = isset($vars['page']) ? $vars['page'] : '';

	// Loading template
	if (isset($vars['template_page']) && is_page($vars['template_page'])) {

		$vars['msg'] = join('', get_source($vars['template_page']));

		// Cut fixed anchors
		$vars['msg'] = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);
	}

	// Delete "#freeze" command
	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '' ,$vars['msg']);
	$postdata = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = $_msg_preview . '<br />' . "\n";
	if ($postdata == '')
		$body .= '<strong>' . $_msg_preview_delete . '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	$body .= edit_form($page, $vars['msg'], $vars['digest'], FALSE);

	return array('msg'=>$_title_preview, 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
// NOTE: Plus! is not compatible for 1.4.4+ style(compatible for 1.4.3 style)
function plugin_edit_inline()
{
	static $usage = '&edit(pagename,anchor);';

	global $script, $vars, $fixed_heading_edited;
	global $_symbol_paraedit;

	if (!$fixed_heading_edited || is_freeze($vars['page'])) {
		return '';
	}

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);
	if ($s_label == '') {
		$s_label = $_symbol_paraedit;
	}

	list($page, $id) = array_pad($args, 2, '');
	if (!is_page($page)) {
		$page = $vars['page'];
	}
	if ($id != '') {
		$id = '&amp;id=' . rawurlencode($id);
	}
	$r_page = rawurlencode($page);
	return "<a class=\"anchor_super\" href=\"$script?cmd=edit&amp;page=$r_page$id\">$s_label</a>";
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $vars, $trackback;
	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;
	global $notimeupdate, $_msg_invalidpass, $do_update_diff_table;

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';
	$partid = isset($vars['id'])     ? $vars['id']     : '';
	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';

	// Check Validate
	if ($notimestamp && !is_page($page)) {
		honeypot_write();
		return plugin_edit_cancel();
	}

	// Paragraph edit mode
	if ($partid) {
		$source = preg_split('/([^\n]*\n)/', $vars['original'], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		if (plugin_edit_parts($partid, $source, $vars['msg']) !== FALSE) {
			$vars['msg'] = @join('', $source);
		} else {
			$vars['msg'] = rtrim($vars['original']) . "\n\n" . $vars['msg'];
		}
	}

	// Delete "#freeze" command
	$vars['msg'] = preg_replace('/^#freeze\s*$/im', '', $vars['msg']);
	$msg = & $vars['msg']; // Reference

	$retvars = array();

	// Collision Detection
	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);
	if ($digest != $oldpagemd5) {
		$vars['digest'] = $oldpagemd5; // Reset

		$original = isset($vars['original']) ? $vars['original'] : '';
		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $msg, $original);

		$retvars['msg'] = $_title_collided;
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
			$postdata = $msg . "\n\n" . @join('', get_source($page));
		} else {
			$postdata = @join('', get_source($page)) . "\n\n" . $msg;
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
	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		$retvars['body'] .= edit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimestamp);
	pkwk_headers_sent();
	if ($vars['refpage'] != '') {
		if ($partid != '') {
			header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['refpage'])) . '#' . rawurlencode($partid);
		} else {
			header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['refpage']));
		}
	} else {
		if ($partid != '') {
			header('Location: ' . get_script_uri() . '?' . rawurlencode($page)) . '#' . rawurlencode($partid);
		} else {
			header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
		}
	}
	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $vars;
	pkwk_headers_sent();
	header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['page']));
	exit;
}

// Replace/Pickup a part of source
function plugin_edit_parts($id, &$source, $postdata='')
{
	$postdata = rtrim($postdata) . "\n";
	$heads = preg_grep('/^\*{1,3}.+$/', $source);
	$heads[count($source)] = ''; // sentinel
	while (list($start, $line) = each($heads)) {
		if (preg_match("/\[#$id\]/", $line)) {
			list($end, $line) = each($heads);
			return join('', array_splice($source, $start, $end - $start, $postdata));
		}
	}
	return FALSE;
}
?>