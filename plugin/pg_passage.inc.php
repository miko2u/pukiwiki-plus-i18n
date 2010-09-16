<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: pg_passage.inc.php,v 0.1 2006/02/17 00:09:00 upk Exp $

function plugin_pg_passage_inline()
{
	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('page','paren',);
        for($i=0; $i<$argc; $i++) {
                $$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
        }

        if (empty($page)) return '';
	$paren = (empty($paren)) ? FALSE : TRUE;
	return get_pg_passage($page, $paren);
}

?>
