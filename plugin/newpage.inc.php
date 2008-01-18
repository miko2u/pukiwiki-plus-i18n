<?php
// $Id: newpage.inc.php,v 1.15.5 2008/01/18 21:40:00 upk Exp $
// Copyright (C)
//   2007-2008 PukiWiki Plus! Team
//
// Newpage plugin

function plugin_newpage_convert()
{
	global $script, $vars, $BracketName;
	static $id = 0;
	$_btn_edit = _('Edit');
	$_msg_newpage = _('New page');

	// if (PKWK_READONLY) return ''; // Show nothing
	if (auth::check_role('readonly')) return ''; // Show nothing
        if (auth::is_check_role(PKWK_CREATE_PAGE)) return '';

	$newpage = '';
	if (func_num_args()) list($newpage) = func_get_args();
	if (! preg_match('/^' . $BracketName . '$/', $newpage)) $newpage = '';

	$s_page    = htmlspecialchars(isset($vars['refer']) ? $vars['refer'] : $vars['page']);
	$s_newpage = htmlspecialchars($newpage);
	++$id;

	$ret = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="newpage" />
  <input type="hidden" name="refer"  value="$s_page" />
  <label for="_p_newpage_$id">$_msg_newpage:</label>
  <input type="text"   name="page" id="_p_newpage_$id" value="$s_newpage" size="30" />
  <input type="submit" value="$_btn_edit" />
 </div>
</form>
EOD;

	return $ret;
}

function plugin_newpage_action()
{
	global $vars;
	$_btn_edit = _('Edit');
	$_msg_newpage = _('New page');

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (auth::check_role('readonly')) die_message( _('PKWK_READONLY prohibits editing') );
	if (auth::is_check_role(PKWK_CREATE_PAGE)) die_message( _('PKWK_CREATE_PAGE prohibits editing') );

	if ($vars['page'] == '') {
		$retvars['msg']  = $_msg_newpage;
		$retvars['body'] = plugin_newpage_convert();
		return $retvars;
	} else {
		$page    = strip_bracket($vars['page']);
		if (isset($vars['refer'])) {
			$r_page = get_fullname($page, $vars['refer']);
			$r_refer = 'refer=' .$vars['refer'];
		} else {
			$r_page = $page;
			$r_refer = '';
		}

		pkwk_headers_sent();
		header('Location: ' . get_page_location_uri($r_page,$r_refer));
		exit;
	}
}
?>
