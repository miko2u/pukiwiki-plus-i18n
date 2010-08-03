<?php
//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2007 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2007 Frederico Caldeira Knabben
//      PukiWiki Plus! : Copyright (C) 2009-2010 Katsumi Saito
//
//
//	File:
//	  editorarea.css.php
//	  FCKeditor の編集エリアに適用させる CSS
//


header('Content-Type: text/css');

//	PukiWiki の CSS の読み込み
// require_once('../theme/pukiwiki/pukiwiki.css.php');
require_once('plus.css.php');

?>


body {
	font-size: 80%;
}

pre {
	font-family: "MS Gothic", monospace;
}

div.plugin, div.ref {
	background-color: #ffffcc;
	margin-top: 5px;
	margin-bottom: 5px;
	white-space: pre;
}

span.plugin, span.ref {
	background-color: #ccffcc;
}
