<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: edit.inc.php,v 1.19.6 2004/10/11 12:05:12 miko Exp $
//

// Edit plugin
// cmd=edit
function plugin_edit_action()
{
	global $vars, $_title_edit, $load_template_func;

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
			unset($vars['id']); // なかったことに :)
			$postdata = $vars['original'];
		}
	}
	if ($postdata == '') {
		$postdata = auto_template($page);
	}

	return array('msg'=>$_title_edit, 'body'=>edit_form($page, $postdata));
}

function plugin_edit_inline()
{
	static $usage = '&edit(pagename#anchor[[,noicon],nolabel])[{label}];';

	global $script, $vars;
	global $_symbol_paraedit, $fixed_heading_edited;

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

// Preview
function plugin_edit_preview()
{
	global $vars;
	global $_title_preview, $_msg_preview, $_msg_preview_delete;

	$page = isset($vars['page']) ? $vars['page'] : '';

	// Loading template
	if (isset($vars['template_page']) && is_page($vars['template_page'])) {

		$vars['msg'] = join('', get_source($vars['template_page']));

		// 見出しの固有ID部を削除
		$vars['msg'] = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);
	}

	// 手書きの#freezeを削除
	$vars['msg'] = preg_replace('/^#freeze\s*$/im', '' ,$vars['msg']);
	$postdata = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$body = "$_msg_preview<br />\n";
	if ($postdata == '')
		$body .= "<strong>$_msg_preview_delete</strong>";
	$body .= "<br />\n";

	if ($postdata) {
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= '<div id="preview">' . $postdata . '</div>' . "\n";
	}
	$body .= edit_form($page, $vars['msg'], $vars['digest'], FALSE);

	return array('msg'=>$_title_preview, 'body'=>$body);
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $vars;
	global $_title_collided, $_msg_collided_auto, $_msg_collided, $_title_deleted;

	$page = isset($vars['page']) ? $vars['page'] : '';
	$retvars = array();

	// 手書きの#freezeを削除
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
				// $post['msg']だけがページに書き込まれてしまうのを防ぐ。
				$postdata = $postdata_input = rtrim($vars['original'])."\n\n".$vars['msg'];
			}
		}
	}

	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);

	if (! isset($vars['digest']) || $vars['digest'] != $oldpagemd5) {
		$vars['digest'] = $oldpagemd5;

		$retvars['msg'] = $_title_collided;
		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $postdata_input, $vars['original']);

		$retvars['body'] = ($auto ? $_msg_collided_auto : $_msg_collided)."\n";

		if (TRUE) {
			global $do_update_diff_table;
			$retvars['body'] .= $do_update_diff_table;
		}

		unset($vars['id']);	// 更新が衝突したら全文編集に切り替え
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

// ソースの一部を抽出/置換する
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
