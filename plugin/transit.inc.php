<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: transit.inc.php,v 0.1 2003/10/08 15:47:38 miko Exp $
//

function plugin_transit_inline()
{
	if (func_num_args() != 1)
	{
		return FALSE;
	}
	
	list($station) = func_get_args();
	
	if ($station == '')
	{
		return FALSE;
	}

//	if (!preg_match('/^\d+$/',$size))
//	{
//		return $body;
//	}

	$s_station = htmlspecialchars($station);
	$result = "<FORM action=\"http://transit.yahoo.co.jp/bin/exp\" target=\"transit\" method=\"POST\">"
		    . "¡ÚºÇ´ó¤ê±Ø¡Û$s_station ¡Á "
		    . "<input type=text name=\"val_from\" size=20>"
		    . "<input type=\"hidden\" name=\"val_htmb\" value=\"horizon2\">"
		    . "<input type=\"hidden\" name=\"val_feeling\" value=\"2221122\">"
		    . "<input type=\"hidden\" name=\"val_to\" value=\"$s_station\">"
		    . "<input type=submit name=\"¸¡º÷\" value=\"¸¡º÷\">"
		    . "</FORM>";

	return $result;
}
?>
