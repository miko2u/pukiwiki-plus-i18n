<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: unfreeze.inc.php,v 1.10.3 2005/03/09 06:24:21 miko Exp $
//
// Unfreeze(Unlock) plugin

// Show edit form when unfreezed
define('PLUGIN_UNFREEZE_EDIT', TRUE);

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
	if (! $function_freeze || ! is_page($page))
		return array('msg' => '', 'body' => '');

	$pass = isset($vars['pass']) ? $vars['pass'] : NULL;
	$msg = $body = '';
	if (! is_freeze($page)) {
		// Unfreezed already
		$msg  = $_title_isunfreezed;
		$body = str_replace('$1', htmlspecialchars(strip_bracket($page)),
			$_title_isunfreezed);

	} else if ($pass !== NULL && pkwk_login($pass)) {
		// Unfreeze
		$postdata = get_source($page);
		array_shift($postdata);
		$postdata = join('', $postdata);
		file_write(DATA_DIR, $page, $postdata, TRUE);

		// Update 
		is_freeze($page, TRUE);
		if (PLUGIN_UNFREEZE_EDIT) {
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
