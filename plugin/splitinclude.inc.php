<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: splitinclude.inc.php,v 1.4 2005/06/02 09:12:17 miko Exp $
//

define('PLUGIN_SPLITINCLUDE_LAYOUT_TABLE',FALSE);

function plugin_splitinclude_convert()
{
	global $script,$vars,$get,$post;
	global $_msg_splitinclude_restrict;
	static $splitinclude_list = array();

	if (func_num_args() == 0)
	{
		return;
	}

	$splitinclude_list[$vars['page']] = TRUE;

	$func_vars_num = func_num_args();
	$func_vars_array = func_get_args();
	$incbody = "";

	foreach($func_vars_array as $page)
	{
		$page = strip_bracket($page);
		
		if (!is_page($page) or isset($splitinclude_list[$page]))
		{
			return '';
		}
		$splitinclude_list[$page] = TRUE;
		
		$_page = $vars['page'];
		$get['page'] = $post['page'] = $vars['page'] = $page;
		
		// Check read authentification(if not readable, show error message.)
		if (check_readable($page, false, false)) {
			if (function_exists('convert_filter')) {
				$body = convert_html(convert_filter(get_source($page)));
			} else {
				$body = convert_html(get_source($page));
			}
		} else {
			$body = str_replace('$1',$page,$_msg_splitinclude_restrict);
		}
		
		$get['page'] = $post['page'] = $vars['page'] = $_page;
		
		if (PLUGIN_SPLITINCLUDE_LAYOUT_TABLE) {
			$incbody .= '<td>' . $body . '</td>' . "\n";
		} else {
			$incbody .= "<div style=\"width:".intval(96/$func_vars_num)."%;margin:0px 2px;vartical-align:top;float:left;\">$body</div>\n";
		}
	}
	if (PLUGIN_SPLITINCLUDE_LAYOUT_TABLE) {
		$incbody = '<table width="100%" border="0" cellspacing="0" cellpadding="0">' . "\n"
		         . '<tr>' . "\n" . $incbody . '</tr>' . "\n"
		         . '</table>' . "\n";
	} else {
		$incbody = "<div style=\"width:100%\">\n$incbody</div>\n<div style=\"display:block;\"></div>\n";
	}
	return $incbody;
}
?>
