<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php,v 1.19.30 2005/01/23 05:22:25 miko Exp $
//

// Edit plugin
// cmd=edit

function plugin_edit_action()
{
	// global $vars, $_title_edit, $load_template_func;
	global $vars, $load_template_func;

	if (PKWK_READONLY) die_message( _('PKWK_READONLY prohibits editing') );

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
	$postdata = $vars['original'] = join('', $source);
	if (!empty($vars['id']))
	{
		$postdata = plugin_edit_parts($vars['id'],$source);
		if ($postdata === FALSE)
		{
			unset($vars['id']); // �ʤ��ä����Ȥ� :)
			$postdata = $vars['original'];
		}
	}
	if ($postdata == '') $postdata = auto_template($page);

	return array('msg'=> _('Edit of  $1'), 'body'=>edit_form($page, $postdata));
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

	// ��񤭤�#freeze����
	$vars['msg'] = preg_replace('/^#freeze\s*$/im', '' ,$vars['msg']);
	$postdata = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = _('To confirm the changes, click the button at the bottom of the page') . "<br />\n";
	if ($postdata == '')
		$body .= "<strong>" .
			 _('(The contents of the page are empty. Updating deletes this page.)') .
			 "</strong>";
	$body .= "<br />\n";

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
function plugin_edit_inline()
{
	static $usage = '&edit(pagename#anchor[[,noicon],nolabel])[{label}];';

	global $script, $vars, $fixed_heading_edited;
	global $_symbol_paraedit;

	if (!$fixed_heading_edited || is_freeze($vars['page'])) {
		return '';
	}

	$args = func_get_args();

	$s_label = array_pop($args);
	if ($s_label == '')
	{
		$s_label = $_symbol_paraedit;
	}

	list($page,$id) = array_pad($args,2,'');
	if (!is_page($page))
	{
		$page = $vars['page'];
	}
	if ($id != '')
	{
		$id = '&amp;id='.rawurlencode($id);
	}
	$r_page = rawurlencode($page);
	return "<a class=\"anchor_super\" href=\"$script?cmd=edit&amp;page=$r_page$id\">$s_label</a>";
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $vars;
	// global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;

	$page = isset($vars['page']) ? $vars['page'] : '';
	$retvars = array();

	// ��񤭤�#freeze����
	$vars['msg'] = preg_replace('/^#freeze\s*$/im','',$vars['msg']);
	$postdata = $postdata_input = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	} else {
		if (isset($vars['id']) && $vars['id']) {
			$source = preg_split('/([^\n]*\n)/',$vars['original'],-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			if (plugin_edit_parts($vars['id'],$source,$vars['msg']) !== FALSE) {
				$postdata = $postdata_input = join('',$source);
			} else {
				// $post['msg']�������ڡ����˽񤭹��ޤ�Ƥ��ޤ��Τ��ɤ���
				$postdata = $postdata_input = rtrim($vars['original'])."\n\n".$vars['msg'];
			}
		}
	}

	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);

	if (! isset($vars['digest']) || $vars['digest'] != $oldpagemd5) {
		$vars['digest'] = $oldpagemd5;

		$retvars['msg'] = _('On updating  $1, a collision has occurred.');

		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $postdata_input, $vars['original']);

		$_msg_collided_auto =
		_('It seems that someone has already updated this page while you were editing it.<br />') .
		_('The collision has been corrected automatically, but there may still be some problems with the page.<br />') .
		_('To confirm the changes to the page, press [Update].<br />');

		$_msg_collided =
		_('It seems that someone has already updated this page while you were editing it.<br />') .
		_(' + is placed at the beginning of a line that was newly added.<br />') .
		_(' ! is placed at the beginning of a line that has possibly been updated.<br />') .
		_(' Edit those lines, and submit again.');

		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided)."\n";

		if (TRUE) {
			global $do_update_diff_table;
			$retvars['body'] .= $do_update_diff_table;
		}

		unset($vars['id']);	// ���������ͤ�������ʸ�Խ����ڤ��ؤ�
		$retvars['body'] .= edit_form($page, $postdata_input, $oldpagemd5, FALSE);
	}
	else {
		$notimestamp = (isset($vars['notimestamp']) && $vars['notimestamp'] != '');
		page_write($page, $postdata, $notimestamp);

		if ($postdata) {
			pkwk_headers_sent();
			if ($vars['refpage'] != '') {
				if ($vars['id'] != '') {
					header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['refpage'])) . '#' . rawurlencode($vars['id']);
				} else {
					header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['refpage']));
				}
			} else {
				if ($vars['id'] != '') {
					header('Location: ' . get_script_uri() . '?' . rawurlencode($page)) . '#' . rawurlencode($vars['id']);
				} else {
					header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
				}
			}
			exit;
		}

		$_title_deleted = _(' $1 was deleted');

		$retvars['msg'] = $_title_deleted;
		$retvars['body'] = str_replace('$1', htmlspecialchars($page), $_title_deleted);
		tb_delete($page);
	}

	return $retvars;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $vars;
	pkwk_headers_sent();
	header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['page']));
	exit;
}

// �������ΰ��������/�ִ�����
function plugin_edit_parts($id,&$source,$postdata='')
{
	$postdata = rtrim($postdata)."\n";
	$heads = preg_grep('/^\*{1,3}.+$/',$source);
	$heads[count($source)] = ''; // sentinel
	while (list($start,$line) = each($heads))
	{
		if (preg_match("/\[#$id\]/",$line))
		{
			list($end,$line) = each($heads);
			return join('',array_splice($source,$start,$end - $start,$postdata));
		}
	}
	return FALSE;
}

?>