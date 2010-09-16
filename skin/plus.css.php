<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: plus.css.php,v 1.9 2004/11/27 11:40:53 sky,miko Exp $
//
// Default CSS Adaptive Kit

$css = array();
$lastmodtime = 0;

$css[] = file_get_contents('pukiwiki.css');
$lastmodtime = max($lastmodtime, filemtime('pukiwiki.css'));
$css[] = file_get_contents('plus.css');
$lastmodtime = max($lastmodtime, filemtime('plus.css'));

$dir = @opendir('./css/');
while ($file = @readdir($dir)) {
	$pathparts = pathinfo($file);
	if (!strcasecmp($path_parts['extension'], 'css')) {
		$css[] = file_get_contents($file);
		$lastmodtime = max($lastmodtime, filemtime($file));
	}
}
// Default charset
$charset = isset($_GET['charset']) ? $_GET['charset']  : '';
switch ($charset) {
	case 'Shift_JIS': break; /* this @charset is for Mozilla's bug */
	default: $charset ='iso-8859-1';
}

// Output header
//$matches = array();
//if(ini_get('zlib.output_compression') && preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
//	header('Content-Encoding: ' . $matches[1]);
//	header('Vary: Accept-Encoding');
//}
header('Content-Type: text/css; charset=' . $charset);
header('Last-Modified: ' . date('r', $lastmodtime));

// Output body 
echo '@charset "' . $charset . '";' . "\n";
echo join("\n\n", $css) . "\n\n";
echo '@media print {'."\n";
echo file_get_contents('print.css');
echo '}';
?>