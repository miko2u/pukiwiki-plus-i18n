<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: button.inc.php,v 1.0 2003/05/10 14:42:29 reimy Exp $
//

function plugin_button_inline()
{
	if (func_num_args() != 1)
	{
		return FALSE;
	}
	
	list($body) = func_get_args();
	
	if ($body == '')
	{
		return FALSE;
	}

	return '<button type="button" class="btn">'.$body.'</button>';
}
