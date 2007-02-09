<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: vote.inc.php,v 1.24.5 2007/01/21 14:15:30 miko Exp $
// Copyright (C)
//	 2004-2007 PukiWiki Plus! Team
//   2002-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Vote box plugin

// expired 3days
define(PLUGIN_VOTE_COOKIE_EXPIRED,60*60*24*3);

function plugin_vote_action()
{
	global $vars, $script, $cols, $rows;
//	global $_title_collided, $_msg_collided, $_title_updated;
	$s_votes  = _('Vote');
$_title_collided   = _('On updating $1, a collision has occurred.');
$_title_updated    = _('$1 was updated');
$_msg_collided = _('It seems that someone has already updated this page while you were editing it.<br />
 + is placed at the beginning of a line that was newly added.<br />
 ! is placed at the beginning of a line that has possibly been updated.<br />
 Edit those lines, and submit again.');

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (auth::check_role('readonly')) die_message('PKWK_READONLY prohibits editing');

	$postdata_old  = get_source($vars['refer']);

	$vote_no = 0;
	$title = $body = $postdata = $postdata_input = $vote_str = '';
	$matches = array();

	// added by miko
	$votedkey = 'vote_'.$vars['refer'].'_'.$vars['vote_no'];
	if (isset($_COOKIE[$votedkey])) {
		return array(
			'msg'  => _('Error in vote'),
			'body' => _('Continuation vote cannot be performed.'),
		);
	}
	$_COOKIE[$votedkey] = 1;
	preg_match('!(.*/)!', $_SERVER['REQUEST_URI'], $matches);
	setcookie($votedkey, 1, time()+PLUGIN_VOTE_COOKIE_EXPIRED, $matches[0]);
	// added by miko

	foreach($postdata_old as $line) {

		if (! preg_match('/^#vote(?:\((.*)\)(.*))?$/i', $line, $matches) ||
		    $vote_no++ != $vars['vote_no']) {
			$postdata .= $line;
			continue;
		}
		$args  = explode(',', $matches[1]);
		$lefts = isset($matches[2]) ? $matches[2] : '';

		foreach($args as $arg) {
			$cnt = 0;
			if (preg_match('/^(.+)\[(\d+)\]$/', $arg, $matches)) {
				$arg = $matches[1];
				$cnt = $matches[2];
			}
			$e_arg = encode($arg);
			if (! empty($vars['vote_' . $e_arg]) && $vars['vote_' . $e_arg] == $s_votes)
				++$cnt;

			$votes[] = $arg . '[' . $cnt . ']';
		}

		$vote_str       = '#vote(' . @join(',', $votes) . ')' . $lefts . "\n";
		$postdata_input = $vote_str;
		$postdata      .= $vote_str;
	}

	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $_title_collided;

		$s_refer          = htmlspecialchars($vars['refer']);
		$s_digest         = htmlspecialchars($vars['digest']);
		$s_postdata_input = htmlspecialchars($postdata_input);
		$body = <<<EOD
$_msg_collided
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>

EOD;
	} else {
		page_write($vars['refer'], $postdata);
		$title = $_title_updated;
	}

	$vars['page'] = $vars['refer'];

	return array('msg'=>$title, 'body'=>$body);
}

function plugin_vote_convert()
{
	global $script, $vars, $digest;
	static $number = array();

	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// Vote-box-id in the page
	if (! isset($number[$page])) $number[$page] = 0; // Init
	$vote_no = $number[$page]++;

	if (! func_num_args()) return '#vote(): No arguments<br />' . "\n";

	// if (PKWK_READONLY) {
	if (auth::check_role('readonly')) {
		$_script = '';
		$_submit = 'hidden';
	} else {
		$_script = $script;
		$_submit = 'submit';
	}

	$args     = func_get_args();
	$s_page   = htmlspecialchars($page);
	$s_digest = htmlspecialchars($digest);
	$s_choice = _('Selection');
	$s_votes  = _('Vote');

	$body = <<<EOD
<form action="$_script" method="post">
 <table cellspacing="0" cellpadding="2" class="style_table_vote" summary="vote">
  <tr>
   <td align="left" class="vote_label" style="padding-left:1em;padding-right:1em"><strong>$s_choice</strong>
    <input type="hidden" name="plugin"  value="vote" />
    <input type="hidden" name="refer"   value="$s_page" />
    <input type="hidden" name="vote_no" value="$vote_no" />
    <input type="hidden" name="digest"  value="$s_digest" />
   </td>
   <td align="center" class="vote_label"><strong>$s_votes</strong></td>
  </tr>

EOD;

	$tdcnt = 0;
	$matches = array();
	foreach($args as $arg) {
		$cnt = 0;

		if (preg_match('/^(.+)\[(\d+)\]$/', $arg, $matches)) {
			$arg = $matches[1];
			$cnt = $matches[2];
		}
		$e_arg = encode($arg);

		$link = make_link($arg);

		$cls = ($tdcnt++ % 2)  ? 'vote_td1' : 'vote_td2';

		$body .= <<<EOD
  <tr>
   <td align="left"  class="$cls" style="padding-left:1em;padding-right:1em;">$link</td>
   <td align="right" class="$cls">$cnt&nbsp;&nbsp;
    <input type="$_submit" name="vote_$e_arg" value="$s_votes" class="submit" />
   </td>
  </tr>

EOD;
	}

	$body .= <<<EOD
 </table>
</form>

EOD;

	return $body;
}
?>
