<?php
// $Id: newpage.inc.php,v 1.14.2 2005/03/10 05:05:38 miko Exp $
//
// Newpage plugin

function plugin_newpage_convert()
{
	global $script, $vars, $BracketName;
	$_btn_edit = _('Edit');

	if (PKWK_READONLY) return ''; // Show nothing

	$newpage = '';
	if (func_num_args()) list($newpage) = func_get_args();
	if (! preg_match('/^' . $BracketName . '$/', $newpage)) $newpage = '';

	$s_page    = htmlspecialchars(isset($vars['refer']) ? $vars['refer'] : $vars['page']);
	$s_newpage = htmlspecialchars($newpage);
	$m_newpage = _('New page');
	$ret = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="newpage" />
  <input type="hidden" name="refer"  value="$s_page" />
  $m_newpage:
  <input type="text"   name="page"   value="$s_newpage" size="30" />
  <input type="submit" value="$_btn_edit" />
 </div>
</form>
EOD;

	return $ret;
}

function plugin_newpage_action()
{
	global $vars; //, $_btn_edit;
	$_btn_edit = _('Edit');

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if ($vars['page'] == '') {
		$retvars['msg']  = _('New page');
		$retvars['body'] = plugin_newpage_convert();
		return $retvars;
	} else {
		$page    = strip_bracket($vars['page']);
		$r_page  = rawurlencode(isset($vars['refer']) ?
			get_fullname($page, $vars['refer']) : $page);
		$r_refer = rawurlencode($vars['refer']);

		pkwk_headers_sent();
		header('Location: ' . get_script_uri() .
			'?cmd=read&page=' . $r_page . '&refer=' . $r_refer);
		exit;
	}
}
?>
