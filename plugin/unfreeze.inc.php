<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: unfreeze.inc.php,v 1.13.5 2007/07/28 17:13:05 miko Exp $
// Copyright (C)
//   2004-2007 PukiWiki Plus! Team
//   2003-2004, 2007 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Unfreeze(Unlock) plugin

// Show edit form when unfreezed
defined('PLUGIN_UNFREEZE_EDIT') or define('PLUGIN_UNFREEZE_EDIT', TRUE);

function plugin_unfreeze_action()
{
	global $script, $vars, $function_freeze;

	$_title_isunfreezed = _(' $1 is not frozen');
	$_title_unfreezed   = _(' $1 has been unfrozen.');
	$_title_unfreeze    = _('Unfreeze  $1');
	$_msg_invalidpass   = _('Invalid password.');
	$_msg_unfreezing    = _('Please input the password for unfreezing.');
	$_btn_unfreeze      = _('Unfreeze');

	$page = isset($vars['page']) ? $vars['page'] : '';
	if (! $function_freeze || is_cantedit($page) || ! is_page($page))
		return array('msg' => '', 'body' => '');

	$pass = isset($vars['pass']) ? $vars['pass'] : NULL;
	$msg = $body = '';
	if (! is_freeze($page)) {
		// Unfreezed already
		$msg  = $_title_isunfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)),
			$_title_isunfreezed);

	} else
	if ( (! auth::check_role('role_adm_contents') ) ||
	     ($pass !== NULL && pkwk_login($pass)) )
	{
		// BugTrack2/255
		check_readable($page, true, true);
		// Unfreeze
		$postdata = get_source($page);
		array_shift($postdata);
		$postdata = join('', $postdata);
		file_write(DATA_DIR, $page, $postdata, TRUE);

		// Update 
		is_freeze($page, TRUE);
		if (PLUGIN_UNFREEZE_EDIT) {
			// BugTrack2/255
			check_editable($page, true, true);
//			$vars['cmd'] = 'read'; // To show 'Freeze' link
			$vars['cmd'] = 'edit';
			$msg  = $_title_unfreezed;
			$body = edit_form($page, $postdata);
		} else {
			$vars['cmd'] = 'read';
			$msg  = $_title_unfreezed;
			$body = '';
		}

	} else {
		// Show unfreeze form
		$msg    = $_title_unfreeze;
		$s_page = htmlspecialchars($page);
		$body   = ($pass === NULL) ? '' : "<p><strong>$_msg_invalidpass</strong></p>\n";
		$body  .= <<<EOD
<p>$_msg_unfreezing</p>
<form action="$script" method="post">
 <div>
  <input type="hidden"   name="cmd"  value="unfreeze" />
  <input type="hidden"   name="page" value="$s_page" />
  <input type="password" name="pass" size="12" />
  <input type="submit"   name="ok"   value="$_btn_unfreeze" />
 </div>
</form>
EOD;
	}

	return array('msg'=>$msg, 'body'=>$body);
}
?>
