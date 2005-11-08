<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: pukiwiki.css.php,v 1.12 2005/10/12 13:06:27 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Default CSS

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

// Media
$media   = isset($_GET['media'])   ? $_GET['media']    : '';
if ($media != 'print') $media = 'screen';

// Output CSS ----
?>
@charset "<?php echo $charset ?>";

pre, dl, ol, p, blockquote { line-height:130%; }

blockquote { margin-left:32px; }

body,td {
	color:black;
	background-color:white;
	margin-left:2%;
	margin-right:2%;
	font-size:90%;
	font-family:verdana, arial, helvetica, Sans-Serif;
}

a:link {
<?php	if ($media == 'print') { ?>
	text-decoration: underline;
<?php	} else { ?>
	color:#215dc6;
	background-color:inherit;
	text-decoration:none;
<?php	} ?>
}

a:active {
	color:#215dc6;
	background-color:#CCDDEE;
	text-decoration:none;
}

a:visited {
<?php	if ($media == 'print') { ?>
	text-decoration: underline;
<?php	} else { ?>
	color:#a63d21;
	background-color:inherit;
	text-decoration:none;
<?php	} ?>
}

a:hover {
	color:#215dc6;
	background-color:#CCDDEE;
	text-decoration:underline;
}

h1, h2 {
	font-family:verdana, arial, helvetica, Sans-Serif;
	color:inherit;
	background-color:#DDEEFF;
	padding:.3em;
	border:0px;
	margin:0px 0px .5em 0px;
}
h3 {
	font-family:verdana, arial, helvetica, Sans-Serif;
	border-bottom:  3px solid #DDEEFF;
	border-top:     1px solid #DDEEFF;
	border-left:   10px solid #DDEEFF;
	border-right:   5px solid #DDEEFF;

	color:inherit;
	background-color:#FFFFFF;
	padding:.3em;
	margin:0px 0px .5em 0px;
}
h4 {
	font-family:verdana, arial, helvetica, Sans-Serif;
	border-left:   18px solid #DDEEFF;

	color:inherit;
	background-color:#FFFFFF;
	padding:.3em;
	margin:0px 0px .5em 0px;
}
h5, h6 {
	font-family:verdana, arial, helvetica, Sans-Serif;
	color:inherit;
	background-color:#DDEEFF;
 	padding:.3em;
 	border:0px;
 	margin:0px 0px .5em 0px;
}

h1.title {
	font-size: 30px;
	font-weight:bold;
	background-color:transparent;
	padding: 12px 0px 0px 0px;
	border: 0px;
	margin: 12px 0px 0px 0px;
}

dt {
	font-weight:bold;
	margin-top:1em;
	margin-left:1em;
}

pre {
	border-top:#DDDDEE 1px solid;
	border-bottom:#888899 1px solid;
	border-left:#DDDDEE 1px solid;
	border-right:#888899 1px solid;
	padding:.5em;
	margin-left:1em;
	margin-right:2em;
	white-space:pre;
	color:black;
	background-color:#F0F8FF;
}

img {
	border:none;
	vertical-align:middle;
}

ul {
	margin-top:.5em;
	margin-bottom:.5em;
	line-height:130%;
}

em { font-style:italic; }

strong { font-weight:bold; }

thead td.style_td,
tfoot td.style_td {
	color:inherit;
	background-color:#D0D8E0;
}
thead th.style_th,
tfoot th.style_th {
	color:inherit;
	background-color:#E0E8F0;
}
.style_table {
	padding:0px;
	border:0px;
	margin:auto;
	text-align:left;
	color:inherit;
	background-color:#ccd5dd;
}
.style_th {
	padding:5px;
	margin:1px;
	text-align:center;
	color:inherit;
	background-color:#EEEEEE;
}
.style_td {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#EEF5FF;
}

ul.list1 { list-style-type:disc; }
ul.list2 { list-style-type:circle; }
ul.list3 { list-style-type:square; }
ol.list1 { list-style-type:decimal; }
ol.list2 { list-style-type:lower-roman; }
ol.list3 { list-style-type:lower-alpha; }

div.ie5 { text-align:center; }

span.noexists {
	color:inherit;
	background-color:#FFFACC;
}

.small { font-size:80%; }

.super_index {
	color:#DD3333;
	background-color:inherit;
	font-weight:bold;
	font-size:60%;
	vertical-align:super;
}

a.note_super {
	color:#DD3333;
	background-color:inherit;
	font-weight:bold;
	font-size:60%;
	vertical-align:super;
}

div.jumpmenu {
	font-size:60%;
	text-align:right;
}

hr.full_hr {
	border-style:ridge;
	border-color:#333333;
	border-width:1px 0px;
}
hr.note_hr {
	width:90%;
	border-style:ridge;
	border-color:#333333;
	border-width:1px 0px;
	text-align:center;
	margin:1em auto 0em auto;
}

span.size1 {
	font-size:xx-small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size2 {
	font-size:x-small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size3 {
	font-size:small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size4 {
	font-size:medium;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size5 {
	font-size:large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size6 {
	font-size:x-large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size7 {
	font-size:xx-large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}

/* html.php/catbody() */
strong.word0 {
	background-color:#FFFF66;
	color:black;
}
strong.word1 {
	background-color:#A0FFFF;
	color:black;
}
strong.word2 {
	background-color:#99FF99;
	color:black;
}
strong.word3 {
	background-color:#FF9999;
	color:black;
}
strong.word4 {
	background-color:#FF66FF;
	color:black;
}
strong.word5 {
	background-color:#880000;
	color:white;
}
strong.word6 {
	background-color:#00AA00;
	color:white;
}
strong.word7 {
	background-color:#886800;
	color:white;
}
strong.word8 {
	background-color:#004699;
	color:white;
}
strong.word9 {
	background-color:#990099;
	color:white;
}

/* html.php/edit_form() */
.edit_form { clear:both; }

/* pukiwiki.skin.php */
div#header {
	padding:0px;
	margin:0px;
}

div#navigator {
<?php   if ($media == 'print') { ?>
	display:none;
<?php   } else { ?>
	clear:both;
	padding:4px 0px 0px 0px;
	margin:0px;
<?php   } ?>
}

td.menubar {
<?php   if ($media == 'print') { ?>
	display:none;
<?php   } else { ?>
	width:9em;
	vertical-align:top;
<?php   } ?>
}

div#menubar {
<?php   if ($media == 'print') { ?>
	display:none;
<?php   } else { ?>
	width:9em;
	padding:0px;
	margin:4px;
	word-break:break-all;
	font-size:90%;
	overflow:hidden;
<?php   } ?>
}

div#menubar ul {
	margin:0px 0px 0px .5em;
	padding:0px 0px 0px .5em;
}

div#menubar ul li { line-height:110%; }

div#menubar h4 { font-size:110%; }

div#body {
	padding:0px;
	margin:0px 0px 0px .5em;
}

div#note {
	clear:both;
	padding:0px;
	margin:0px;
}

div#attach {
<?php   if ($media == 'print') { ?>
	display:none;
<?php   } else { ?>
	clear:both;
	padding:0px;
	margin:0px;
<?php   } ?>
}

div#toolbar {
<?php   if ($media == 'print') { ?>
        display:none;
<?php   } else { ?>
	clear:both;
	padding:0px;
	margin:0px;
	text-align:right;
<?php   } ?>
}

div#lastmodified {
	font-size:80%;
	padding:0px;
	margin:0px;
}

div#related {
<?php   if ($media == 'print') { ?>
        display:none;
<?php   } else { ?>
	font-size:80%;
	padding:0px;
	margin:16px 0px 0px 0px;
<?php   } ?>
}

div#footer {
	font-size:70%;
	padding:0px;
	margin:16px 0px 0px 0px;
}

div#banner {
	float:right;
	margin-top:24px;
}

div#preview {
	color:inherit;
	background-color:#F5F8FF;
}

img#logo {
<?php   if ($media == 'print') { ?>
	display:none;
<?php   } else { ?>
	float:left;
	margin-right:20px;
<?php   } ?>
}

/* aname.inc.php */
.anchor {}
.anchor_super {
	font-size:xx-small;
	vertical-align:super;
}

/* br.inc.php */
br.spacer {}

/* calendar*.inc.php */
.style_calendar {
	padding:0px;
	border:0px;
	margin:3px;
	color:inherit;
	background-color:#CCD5DD;
	text-align:center;
}
.style_td_caltop {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#EEF5FF;
	font-size:80%;
	text-align:center;
}
.style_td_today {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#FFFFDD;
	text-align:center;
}
.style_td_sat {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#DDE5FF;
	text-align:center;
}
.style_td_sun {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#FFEEEE;
	text-align:center;
}
.style_td_blank {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#EEF5FF;
	text-align:center;
}
.style_td_day {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#EEF5FF;
	text-align:center;
}
.style_td_week {
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:#DDE5EE;
	font-size:80%;
	font-weight:bold;
	text-align:center;
}

/* calendar_viewer.inc.php */
div.calendar_viewer {
	color:inherit;
	background-color:inherit;
	margin-top:20px;
	margin-bottom:10px;
	padding-bottom:10px;
}
span.calendar_viewer_left {
	color:inherit;
	background-color:inherit;
	float:left;
}
span.calendar_viewer_right {
	color:inherit;
	background-color:inherit;
	float:right;
}

/* clear.inc.php */
.clear {
	margin:0px;
	clear:both;
}

/* counter.inc.php */
div.counter { font-size:70%; }

/* diff.inc.php */
span.diff_added {
	color:blue;
	background-color:inherit;
}

span.diff_removed {
	color:red;
	background-color:inherit;
}

/* hr.inc.php */
hr.short_line {
	text-align:center;
	width:80%;
	border-style:solid;
	border-color:#333333;
	border-width:1px 0px;
}

/* include.inc.php */
h5.side_label { text-align:center; }

/* navi.inc.php */
ul.navi {
	margin:0px;
	padding:0px;
	text-align:center;
}
li.navi_none {
	display:inline;
	float:none;
}
li.navi_left {
	display:inline;
	float:left;
	text-align:left;
}
li.navi_right {
	display:inline;
	float:right;
	text-align:right;
}

/* new.inc.php */
span.comment_date { font-size:x-small; }
span.new1 {
	color:red;
	background-color:transparent;
	font-size:x-small;
}
span.new5 {
	color:green;
	background-color:transparent;
	font-size:xx-small;
}

/* popular.inc.php */
span.counter { font-size:70%; }
ul.popular_list {
<?php
/*
	padding:0px;
	border:0px;
	margin:0px 0px 0px 1em;
	word-wrap:break-word;
	word-break:break-all;
*/
?>
}

/* recent.inc.php,showrss.inc.php */
ul.recent_list {
<?php
/*
	padding:0px;
	border:0px;
	margin:0px 0px 0px 1em;
	word-wrap:break-word;
	word-break:break-all;
*/
?>
}

/* ref.inc.php */
div.img_margin {
	margin-left:32px;
	margin-right:32px;
}

/* vote.inc.php */
td.vote_label {
	color:inherit;
	background-color:#FFCCCC;
}
td.vote_td1 {
	color:inherit;
	background-color:#DDE5FF;
}
td.vote_td2 {
	color:inherit;
	background-color:#EEF5FF;
}
