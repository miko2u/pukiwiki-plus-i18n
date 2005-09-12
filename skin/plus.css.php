<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: plus.css.php,v 1.9 2004/11/27 11:40:53 sky,miko Exp $
//
// Default CSS Adaptive Kit

// Send header
header('Content-Type: text/css');
$matches = array();
if(ini_get('zlib.output_compression') && preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
	header('Content-Encoding: ' . $matches[1]);
	header('Vary: Accept-Encoding');
}

// Default charset
$charset = isset($_GET['charset']) ? $_GET['charset']  : '';
switch ($charset) {
	case 'Shift_JIS': break; /* this @charset is for Mozilla's bug */
	default: $charset ='iso-8859-1';
}
echo '@charset "' . $charset . '";' . "\n";

echo file_get_contents('pukiwiki.css');

$dir = @opendir('./css/');
while ($file = @readdir($dir)) {
	$pathparts = pathinfo($file);
	if (!strcasecmp($path_parts['extension'], 'css')) {
		echo file_get_contents($file);
	}
}
echo '@media print {'."\n";
echo file_get_contents('print.css');
echo '}';

?>