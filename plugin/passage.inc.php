<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: passage.inc.php,v 0.1 2006/02/17 00:05:00 upk Exp $

function plugin_passage_inline()
{
	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('time','paren',);
        for($i=0; $i<$argc; $i++) {
                $$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
        }

        if (empty($time)) return '';
	$paren = (empty($paren)) ? FALSE : TRUE;
	return get_passage($time, $paren);
}

?>
