<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: legent.inc.php,v 1.0.1 2009/02/16 23:33:00 upk Exp $
//
// Legent plugin

// ----
define('PLUGIN_CODE_USAGE', 
	   '<p class="error">Plugin code: Usage:<br />#legend[(title)]{{<br />contents<br />}}</p>');

function plugin_legend_convert()
{
	$argv = func_get_args();
	$argc = func_num_args();

	if ($argc < 1) return PLUGIN_CODE_USAGE;

	$data = $argv[ --$argc ];
	array_pop($argv);
	$parm = legend_set_parm($argv);
	if (strlen($data) == 0 || empty($parm['title'])) {
		return PLUGIN_CODE_USAGE;
	}

	// FIXME:
	// class, style で指定可能であったとしても、ブラウザで正しく処理できるのは、align しかなさそう
	$align = (empty($parm['align'])) ? '' : ' align="'.$parm['align'].'"';
	return "<div>\n<fieldset>\n<legend$align>" . $parm['title'] . "</legend>\n" . convert_html(line2array($data)) . "</fieldset>\n</div>";

}

function legend_set_parm($argv)
{
	$parm = array();
	$parm['align'] = $parm['title'] = '';

	foreach($argv as $arg) {
		$val = explode('=', $arg);
		$val[1] = (empty($val[1])) ? htmlspecialchars($val[0]) : htmlspecialchars($val[1]);

		switch($val[0]) {
		case 'r':
		case 'right':
			$parm['align'] = 'right';
			break;
		case 'l':
		case 'left':
			$parm['align'] = 'left';
			break;
		case 'c':
		case 'center':
			$parm['align'] = 'center';
			break;
		/*
		case 't':
		case 'top':
			$parm['align'] = 'top';
			break;
		case 'b':
		case 'bottom':
			$parm['align'] = 'bottom';
			break;
		*/
		default:
			$parm['title'] = $val[1];
			// $parm[$val[0]] = $val[1];
                }
	}
	return $parm;
}
?>
