<?php
/*
 * $Id$
 * 
 * License:  GNU General Public License
 *
 * Copyright (c) 2005 in3c.org
 * Portions Copyright (c) 2004 ようか
 *   http://noldor.info/
 *   http://kinowiki.net/
 *
 * MODIFICATION BY:
 * (C) 2006,2008 PukiWiki Plus! Developers Team
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 * 
 */

// 全体のテンプレート
define("BLIKIFOOTER_TEMPLATE","<div class='blikiFooter'>%s</div>"); 

function plugin_blikifooter_init()
{
	global $_blikifooter_msg;

	$msg = array(
		'_blikifooter_msg' => array(
			'msg_author'		=> _('Author'),
			'msg_permalink'		=> _('Permalink'),
			'msg_trackback'		=> _('Trackback'),
			'msg_comment'		=> _('Comments(%s)'),
			'seplater'		=> _(' | '),		// 区切り文字
		)
	);
	set_plugin_messages($msg);
}

/**
 * 書式
 * 
 *      #blikifooter([著者名])
 *
 * 種別 
 *      ブロック型プラグイン
 *  
 * 概要
 *
 *      「投稿者 志田|パーマリンク|trackback(10)|comment(5)」を表示する
 *       blog2プラグインのblog2trackbackを元に作成させていただきました。
 *       ようかさんありがとうございます。
 * 
 * 
 * @author Yuki SHIDA <shida@in3c.org>
 * @author ようか
 */
function plugin_blikifooter_convert()
{
	global $script, $vars, $trackback;
	global $_blikifooter_msg;

	$args   = func_get_args();
	$retval = '';

	if (! empty($args[0]) ) {
		$retval .= $_blikifooter_msg['msg_author'] . 
			'[[' . $args[0] . ']]' . $_blikifooter_msg['seplater'];
	}

	$retval .= '[[' . $_blikifooter_msg['msg_permalink'] . '>' . $vars['page'] . ']]';

	if ($trackback && isset($args[1]) && $args[1] != '0') {
		$retval .= $_blikifooter_msg['seplater'] . '[[' .
			$_blikifooter_msg['msg_trackback'] . '(' . tb_count($vars['page']) . '):' .
			$script.'?plugin=tb&__mode=view&tb_id=' . tb_get_id($vars['page']) . ']]';
	}

	$comment_count = count_comment($vars['page']);
	if (! is_null($comment_count)) {
		$retval .= $_blikifooter_msg['seplater'] .
			sprintf($_blikifooter_msg['msg_comment'], $comment_count);
	}

	return sprintf(BLIKIFOOTER_TEMPLATE, convert_html($retval));
}

function count_comment($page)
{
	$source = join("\n", get_source($page));

	if (! preg_match("/^#comment$/m", $source)) return NULL;

	require_once(PLUGIN_DIR . 'comment.inc.php');

	$comment_format = PLUGIN_COMMENT_FORMAT_STRING;

	$comment_format = preg_replace("/\x08MSG\x08/", ".*",  $comment_format);
	$comment_format = preg_replace("/\x08NAME\x08/", "\[\[.*\]\]", $comment_format);
	$comment_format = preg_replace("/\x08NOW\x08/", "&new.*",  $comment_format);

	return preg_match_all("/^-$comment_format/m", $source, $dumy);
}
?>
