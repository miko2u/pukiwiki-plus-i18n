<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: transit.inc.php,v 0.2 2005/03/10 15:47:38 miko Exp $
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

	$s_closet = _('From: ');
	$s_search = _('Search');
	$s_to = _(' to ');

	$s_station = htmlspecialchars($station);
	$result = '<form action="http://transit.yahoo.co.jp/bin/exp" target="transit" method="POST" accept-charset="euc-jp" onsubmit="' . "document.charset='euc-jp'; return true;" . '">'
		    . $s_closet . $s_station . $s_to
		    . '<input type="text" name="val_from" size="20">'
		    . '<input type="hidden" name="val_htmb" value="horizon2">'
		    . '<input type="hidden" name="val_feeling" value="2221122">'
		    . '<input type="hidden" name="val_to" value="' .$s_station .'">'
		    . '<input type="submit" name="' . $s_search . '" value="' . $s_search . '">'
		    . '</form>';

	return $result;
}
?>
