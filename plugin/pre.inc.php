<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: pre.inc.php,v 0.1.1 2004/11/08 13:17:36 miko Exp $
// Original is sakurai

function plugin_pre_convert()
{
	$args = func_get_args();
	return '<pre class="pre">' . plugin_pre_code($args) .'</pre>';
}

function plugin_pre_code($args)
{
	$field = "";
	foreach ($args as $line) {
		$field .= htmlspecialchars($line);
	}
	return $field;
}
?>
