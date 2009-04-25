<?php
// デフォルトの色に戻したい場合 = 1, 
// カスタマイズするぞー = 0, おまけの春色ｗ = 2
$defaultnimodoshitai = 1;

// :::::: タイトル :::::: //
$title_fontsize  = '20px';		// タイトルの文字の大きさ
$title_fontcolor = '#ccd5dd';		// タイトルの色
// :::::: 基本配色 :::::: //
$bgcolor_htmlbody	= '#EFEFEF';	// HTMLのbodyの色
$bgcolor_contents	= 'White';	// 中身の色
$bgcolor_aLink1		= 'inherit';	// リンクの背景色(親要素の継承色なので変更しないほうが良い)
$bgcolor_aLink2		= '#CCDDEE';	// リンクのhoverの背景色
// :::::: ナビバーの配色 :::::: //
$bgcolor_naviborder = '1px #ccd5dd solid';			// Navibar2の枠
$bgcolor_navigator  = '#EEF5FF'; 				// Navibar2の基本色
$bgcolor_naviblock	= '1px '.$bgcolor_navigator.' solid';	// Navibar2の文字ブロックの枠
$bgcolor_naviborder2 ='1px Navy solid'; 			// Navibar2の文字ブロックの枠(hover)
$bgcolor_navigator2  = '#F5F9FF'; 				// Naviber2の文字ブロックの背景色(hover)
// :::::: メニュー・サイドバーの配色 :::::: //
$bgcolor_menuborder = $bgcolor_naviborder;		// Menubar, Sidebarの見出しの枠
$bgcolor_menutitle	= $bgcolor_navigator;		// Menuber, Sidebarの見出しの背景色
$bgcolor_barcolor	= '#F5F9FF';			// Menuber, Sidebarの背景色
// :::::: 見出しの配色 :::::: //
$bgcolor_finding	= '#116EAA';			// 大見出し
$bgcolor_findline	= '#999999';			// 見出しの下のライン
$bgcolor_finding1	= '#3BB6D7';			// 中見出し
$bgcolor_findline1	= $bgcolor_findline;		// 中見出しの下のライン(個別に設定したい場合は変更)
$bgcolor_finding2	= '#AEE4E9';			// 小見出し
$bgcolor_findline2	= $bgcolor_findline;		// 小見出しの下のライン(個別に設定したい場合は変更)
// :::::: テーブルの配色 :::::: //
$bgcolor_tableline	= 'Black';
$bgcolor_tablecolor = 'White';

// :::::: 文字配色 :::::: //
$fontcolor_default	= '#333322';			// デフォルトの文字色
$fontcolor_inherit	= 'inherit';			// 親要素の継承色
$fontcolor_aLink1	= '#a63d21';			// 未訪問リンクの文字色
$fontcolor_aLink2	= '#116EAA';			// 訪問済みリンク文字色
$fontcolor_aLink3	= $fontcolor_aLink1;		// リンクのhover文字色

// :::::: フォント指定 :::::: //
$default_fontfamily = 'verdana, arial, helvetica, Sans-Serif';


// ---------- ここから下は設定ではないです ------------
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
// sagiri.css.php (2006/4/20)
//            Pukiwiki Plus!に入っている default.css の改造版です
// ------------------------------------------------------------------------- //
//            舞乃　砂霧
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
$default_fontfamily2 = $default_fontfamily;

if ( $defaultnimodoshitai == 1){
	// デフォルト
	$title_fontsize		= '30px';
	$title_fontcolor	= '#cc0000';
	$bgcolor_htmlbody	= 'Cornsilk';
	$bgcolor_contents	= 'White';
	$bgcolor_aLink1		= 'inherit';
	$bgcolor_aLink2		= '#CCDDEE';
	$bgcolor_naviborder	= '1px #ffcc99 solid';
	$bgcolor_navigator	= '#fff0dd';
	$bgcolor_naviblock	= '1px '.$bgcolor_navigator.' solid';
	$bgcolor_naviborder2	='1px #ff9933 solid';
	$bgcolor_navigator2	= '#ffeecc';
	$bgcolor_menuborder	= $bgcolor_naviborder;
	$bgcolor_menutitle	= $bgcolor_navigator;
	$bgcolor_barcolor	= '#FFFCEE';
	$bgcolor_finding	= '#ff7711';
	$bgcolor_findline	= '#999999';
	$bgcolor_finding1	= '#ff9933';
	$bgcolor_findline1	= $bgcolor_findline;
	$bgcolor_finding2	= '#ffcc66';
	$bgcolor_findline2	= $bgcolor_findline;
	$bgcolor_tableline	= '#ccd5dd';
	$bgcolor_tablecolor	= '#EEF5FF';
	$fontcolor_default	= '#333322';
	$fontcolor_inherit	= 'inherit';
	$fontcolor_aLink1	= '#215dc6';
	$fontcolor_aLink2	= '#a63d21';
	$fontcolor_aLink3	= $fontcolor_aLink1;
	$default_fontfamily	= '"Trebuchet MS", arial, helvetica, Sans-Serif';
	$default_fontfamily2	= 'verdana, arial, sans-serif';
} elseif ( $defaultnimodoshitai == 2 ){
	// 春色設定
	$title_fontsize		= '20px';
	$title_fontcolor	= '#CC0099';
	$bgcolor_htmlbody	= '#E7F8E5';
	$bgcolor_contents	= 'White';
	$bgcolor_aLink1		= 'inherit';
	$bgcolor_aLink2		= '#F0BFE0';
	$bgcolor_naviborder	= '1px #F0BFE0 solid';
	$bgcolor_navigator	= '#FFEDF7';
	$bgcolor_naviblock	= '1px '.$bgcolor_navigator.' solid';
	$bgcolor_naviborder2	='1px Navy solid';
	$bgcolor_navigator2	= '#F5F9FF';
	$bgcolor_menuborder	= $bgcolor_naviborder;
	$bgcolor_menutitle	= $bgcolor_navigator;
	$bgcolor_barcolor	= '#FEF7F9';
	$bgcolor_finding	= '#CC0099';
	$bgcolor_findline	= '#999999';
	$bgcolor_finding1	= '#F2AAF5';
	$bgcolor_findline1	= $bgcolor_findline;
	$bgcolor_finding2	= '#F2AAF5';
	$bgcolor_findline2	= $bgcolor_findline;
	$bgcolor_tableline	= 'Black';
	$bgcolor_tablecolor	= 'White';
	$fontcolor_default	= '#333322';
	$fontcolor_inherit	= 'inherit';
	$fontcolor_aLink1	= '#66CD58';
	$fontcolor_aLink2	= '#D11A56';
	$fontcolor_aLink3	= 'black';
	$default_fontfamily	= 'verdana, arial, helvetica, Sans-Serif';
}
// 整形など
$bgcolor_finding = $bgcolor_contents.' '.$bgcolor_contents.' '.$bgcolor_findline.' '.$bgcolor_finding;
$bgcolor_finding1 = $bgcolor_contents.' '.$bgcolor_contents.' '.$bgcolor_findline1.' '.$bgcolor_finding1;
$bgcolor_finding2 = $bgcolor_contents.' '.$bgcolor_contents.' '.$bgcolor_findline2.' '.$bgcolor_finding2;

// CSSとして吐き出すためのおまじない？ｗ
// Send header
header('Content-Type: text/css');
$matches = array();
if(ini_get('zlib.output_compression') && preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
	header('Content-Encoding: ' . $matches[1]); header('Vary: Accept-Encoding');
}
// Default charset
$charset = isset($_GET['charset']) ? $_GET['charset']  : '';
switch ($charset) {
	case 'Shift_JIS': break; default: $charset ='iso-8859-1';
}
?>
@charset "<?php echo $charset ?>";
/* this @charset is for mozilla's bug */
/*
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.css,v 1.32.13 2004/07/31 03:09:20 miko Exp $
//
*/

pre, dl, ol, p, blockquote
{
	line-height:140%;
}

blockquote
{
	margin-left:32px;
}

body
{
	color:<?php echo $fontcolor_default; ?>;
	background-color:<?php echo $bgcolor_htmlbody; ?>;
	margin-top:0px;
	margin-left:0px;
	margin-right:0px;
	font-family:<?php echo $default_fontfamily; ?>;
	font-size: 94%;
}

td
{
	color:<?php echo $fontcolor_default; ?>;
	background-color:<?php echo $bgcolor_contents ?>;
	margin-left:0%;
	margin-right:0%;
	font-family:<?php echo $default_fontfamily; ?>;
	font-size: 94%;
}

div#body a
{
        line-break:strict;
        word-break:break-all;
        word-wrap:break-word;
}

a:link
{
	color:<?php echo $fontcolor_aLink1 ?>;
	background-color:<?php echo $bgcolor_aLink1 ?>;
	text-decoration:none;
}

a:active
{
	color:<?php echo $fontcolor_aLink1 ?>;
	background-color:<?php echo $bgcolor_aLink2 ?>;
	text-decoration:none;
}

a:visited
{
	color:<?php echo $fontcolor_aLink2 ?>;
	background-color:<?php echo $bgcolor_aLink1 ?>;
	text-decoration:none;
}

a:hover
{
	color:<?php echo $fontcolor_aLink3 ?>;
	background-color:<?php echo $bgcolor_aLink2 ?>;
	text-decoration:underline;
}
/*
h1, h2, h3, h4, h5, h6
{
	font-family:<?php echo $default_fontfamily; ?>;
	color:<?php echo $fontcolor_inherit ?>;
	background-color:#DDEEFF;
	padding:.3em;
	border:0px;
	margin:0px 0px .5em 0px;
}
*/
h1
{
	font-family:<?php echo $default_fontfamily; ?>;
	color:<?php echo $title_fontcolor ?>;
	font-size: 20px;
	background-color:transparent;
	border:0px;
	margin:.2em 0px .2em .5em;
}

h2
{
	font-family:<?php echo $default_fontfamily; ?>;
	font-size:124%;
	color:#000000;
	background-color:transparent;
	margin:.2em -10px .2em -18px;
	padding:.3em .3em .15em .5em;
	border:0px;
	border-left:8px solid;
	border-bottom:1px solid;
	border-color:<?php echo $bgcolor_finding ?>;
}

h3
{
	font-family:<?php echo $default_fontfamily; ?>;
	font-size:112%;
	color:#000000;
	background-color:transparent;
	margin:.2em -10px .5em -14px;
	padding:.3em .3em .15em .5em;
	border:0px;
	border-left:8px solid;
	border-bottom:1px solid;
	border-color:<?php echo $bgcolor_finding1 ?>;
}

h4, h5, h6
{
	font-family:<?php echo $default_fontfamily; ?>;
        font-size:100%;
        color:#000000;
        background-color:transparent;
        margin:.2em -10px .5em -10px;
        padding:.3em .3em .15em .5em;
        border:0px;
        border-left:8px solid;
        border-bottom:1px solid;
        border-color:<?php echo $bgcolor_finding2 ?>;
}

h1.title
{
	font-size: <?php echo $title_fontsize ?>;
	font-weight:bold;
	background-color:transparent;
	padding: 12px 0px 0px 0px;
	border: 0px;
	margin: 12px 0px 0px 0px;
}

dt
{
	color: #663333;
	font-weight:bold;
	margin-top:1em;
}
dd
{
	margin-left:1em;
}

pre
{
	border-top:#DDDDEE 1px solid;
	border-bottom:#888899 1px solid;
	border-left:#DDDDEE 1px solid;
	border-right:#888899 1px solid;
	padding:.5em;
	margin-left:1em;
	margin-right:2em;
/*	white-space:pre;	*/
	font-size:80%;
	color:black;
	background-color:#F0F8FF;
/*	white-space:normal;	*/
	line-break:strict;
	word-break:break-all;
	word-wrap:break-word;
}

img
{
	border:none;
/*	vertical-align:middle;	*/
}

ul
{
	margin-top:.5em;
	margin-bottom:.5em;
	line-height:130%;
}

em
{
	font-style:italic;
}

strong
{
	font-weight:bold;
}

thead td.style_td,
tfoot td.style_td
{
	color:<?php echo $fontcolor_inherit ?>;
	background-color:#D0D8E0;
}
thead th.style_th,
tfoot th.style_th
{
	color:<?php echo $fontcolor_inherit ?>;
	background-color:#E0E8F0;
}
.style_table
{
	padding:0px;
	border:0px;
	margin:auto; 
	text-align:left;
	color:inherit;
	background-color:<?php echo $bgcolor_tableline ?>;
}
.style_th
{
	padding:5px;
	margin:1px;
	text-align:center;
	color:inherit;
	background-color:#EEEEEE;
}
.style_td
{
	padding:5px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
}

ul.list1
{
	list-style-type:disc;
}
ul.list2
{
	list-style-type:circle;
}
ul.list3
{
	list-style-type:square;
}
ol.list1
{
	list-style-type:decimal;
}
ol.list2
{
	list-style-type:lower-roman;
}
ol.list3
{
	list-style-type:lower-alpha;
}

div.ie5
{
	text-align:center;
}

span.noexists
{
	color:inherit;
	background-color:#FFFACC;
}

.small
{
	font-size:80%;
}

.small1
{
	font-size:70%;
}

.super_index
{
	color:#DD3333;
	background-color:inherit;
	font-weight:bold;
	font-size:60%;
	vertical-align:super;
}

a.note_super
{
	color:#DD3333;
	background-color:inherit;
	font-weight:bold;
	font-size:60%;
	vertical-align:super;
	margin: 0px 1%;
}

div.jumpmenu
{
	font-size:50%;
	text-align:right;
}

hr.full_hr
{
	border-style:solid;
	border-color:#333333 #FFFFFF #333333;
	border-width:1px 0px;
}
hr.note_hr
{
	width:100%;
	border-style:solid;
	border-color:#FF8822 #FFFFFF #CCCCCC;
	border-width:1px 0px 1px 0px;
	text-align:center;
	margin:1em 1% 0em 1%;
}

span.size1
{
	font-size:xx-small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size2
{
	font-size:x-small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size3
{
	font-size:small;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size4
{
	font-size:medium;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size5
{
	font-size:large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size6
{
	font-size:x-large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}
span.size7
{
	font-size:xx-large;
	line-height:130%;
	text-indent:0px;
	display:inline;
}

/* html.php/catbody() */
strong.word0
{
	background-color:#FFFF66;
	color:black;
}
strong.word1
{
	background-color:#A0FFFF;
	color:black;
}
strong.word2
{
	background-color:#99FF99;
	color:black;
}
strong.word3
{
	background-color:#FF9999;
	color:black;
}
strong.word4
{
	background-color:#FF66FF;
	color:black;
}
strong.word5
{
	background-color:#880000;
	color:white;
}
strong.word6
{
	background-color:#00AA00;
	color:white;
}
strong.word7
{
	background-color:#886800;
	color:white;
}
strong.word8
{
	background-color:#004699;
	color:white;
}
strong.word9
{
	background-color:#990099;
	color:white;
}

/* html.php/edit_form() */
.edit_form
{
	clear:both;
}

/* pukiwiki.skin.*.php */
div#header
{
	padding:0px;
	margin:0px 1%;
/*	white-space: nowrap;	*/
}

div#navigator
{
	clear:both;
	padding:4px 0px 0px 0px;
	margin:0px 1%;
	white-space: nowrap;
	font-size:93%;
}

td.menubar
{
	width:160px;
	vertical-align:top;
}

td.sidebar
{
	width:160px;
	vertical-align:top;
}

div#menubar
{
	width:160px;
	padding:0px;
	margin:0px 2px;
	word-break:break-all;
	font-size:90%;
	overflow:hidden;
}

div#menubar ul.menu
{
	list-style-type: none;
	list-style-position: outside;
	margin: 0px;
	padding: 0px;
}

div#menubar ul.menu li
{
	padding: 0px 0.3em;
	margin: 0px;
	border-bottom: 1px dotted <?php echo $bgcolor_barcolor ?>;
}

div#menubar ul.menu li a
{
	text-decoration: none;
	display:block;
	padding:0.1em 0.3em;
	margin:0px -0.3em;
}

div#menubar h2
{
	margin:0px 0px 0px 0px;
	padding:4px;
	border: <?php echo $bgcolor_menuborder ?>;
	background-color: <?php echo $bgcolor_menutitle ?>;
	text-align:center;
}

div#menubar h3
{
	margin:0px 0px 0px 0px;
	padding:4px;
	border: <?php echo $bgcolor_menuborder ?>;
	background-color: <?php echo $bgcolor_menutitle ?>;
	text-align:center;
}

div#menubar h4
{
	margin:0px 0px 0px 0px;
	padding:4px;
	border: <?php echo $bgcolor_menuborder ?>;
	background-color: <?php echo $bgcolor_menutitle ?>;
	text-align:center;
}

div#menubar h5
{
	margin:0px 0px 4px 0px;
	padding:3px;
	border: <?php echo $bgcolor_menuborder ?>;
	background-color: <?php echo $bgcolor_menutitle ?>;
	text-align:center;
}

div#sidebar
{
	width:160px;
	padding:0px;
	margin:0px 2px;
	word-break:break-all;
	font-size:90%;
	overflow:hidden;
}

div#sidebar ul
{
	margin:0px 0px 0px .5em;
	padding:0px 0px 0px .5em;
}

div#sidebar ul li
{
	line-height:110%;
}

div#sidebar h2
{
margin:0px 0px 4px 0px;
padding:4px;
border: <?php echo $bgcolor_menuborder ?>;
background-color: <?php echo $bgcolor_menutitle ?>;
text-align:center;
	font-size:110%;
}

div#sidebar h3
{
margin:0px 0px 4px 0px;
padding:4px;
border: <?php echo $bgcolor_menuborder ?>;
background-color: <?php echo $bgcolor_menutitle ?>;
text-align:center;
	font-size:110%;
}

div#sidebar h4
{
margin:0px 0px 4px 0px;
padding:4px;
border: <?php echo $bgcolor_menuborder ?>;
background-color: <?php echo $bgcolor_menutitle ?>;
text-align:center;
	font-size:110%;
}

div#body
{
	padding:0px;
	margin:0px 0px 0px 0px;
}

div#note
{
	clear:both;
	padding:0px;
}

div#attach
{
	clear:both;
	padding:0px;
	margin:0px 1%;
	font-size: 86%;
}
div#attach img
{
	vertical-align: middle;
}

div#toolbar
{
	clear:both;
	padding:0px;
	margin:0px 1%;
	text-align:right;
	white-space: nowrap;
}

div#lastmodified
{
	font-size:80%;
	padding:0px;
	margin:0px 1%;
	text-align:right;
}

div#related
{
	font-size:80%;
	padding:0px;
	margin:16px 1% 0px 1%;
}

div#footer
{
	font-size:70%;
	padding:0px;
	margin:0px 1% 0px 1%; 
}

div#preview
{
	color:inherit;
	background-color:#F5F8FF;
}

img#logo
{
	float:left;
	margin-right:20px;
}

/* aname.inc.php */
.anchor
{
}
.anchor_super
{
	font-size:xx-small;
	vertical-align:super;
}

/* br.inc.php */
br.spacer
{
}

/* calendar*.inc.php */
.style_calendar
{
	padding:0px;
	border:0px;
	margin:3px;
	color:inherit;
	background-color:<?php echo $bgcolor_tableline ?>;
	text-align:center;
}

.style_td_caltop
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
	font-size:80%;
	text-align:center;
}

.style_td_today
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:#FFFFDD;
	text-align:center;
}

.style_td_sat
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:#DDE5FF;
	text-align:center;
}

.style_td_sun
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:#FFEEEE;
	text-align:center;
}

.style_td_blank
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
	text-align:center;
}

.style_td_day
{
	padding:5px 3px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
	text-align:center;
}

.style_td_week
{
	padding:5px 5px;
	margin:1px;
	color:inherit;
	background-color:#DDE5EE;
	font-size:80%;
	font-weight:bold;
	text-align:center;
}

/* clear.inc.php */
.clear
{
	display:block;
	margin:0px;
}

/* counter.inc.php */
div.counter
{
	font-size:70%;
}

/* diff.inc.php */
span.diff_added
{
	color:blue;
	background-color:inherit;
}

span.diff_removed
{
	color:red;
	background-color:inherit;
}

/* hr.inc.php */
hr.short_line
{
	text-align:center;
	width:80%;
	border-style:solid;
	border-color:#333333;
	border-width:1px 0px;
}

/* include.inc.php */
h5.side_label
{
	text-align:center;
}

/* navi.inc.php */
ul.navi
{
	margin:0px;
	padding:0px;
	text-align:center;
}

li.navi_none
{
	display:inline;
	float:none;
}

li.navi_left
{
	display:inline;
	float:left;
	text-align:left;
}

li.navi_right
{
	display:inline;
	float:right;
	text-align:right;
}

/* new.inc.php */
span.comment_date
{
	font-size:x-small;
}
span.new1
{
	color:red;
	background-color:transparent;
	font-size:x-small;
}
span.new5
{
	color:green;
	background-color:transparent;
	font-size:xx-small;
}

/* popular.inc.php */
span.counter
{
	font-size:70%;
}
ul.popular_list
{
	padding:0px 0px 0px .5em;
	margin:0px 0px 0px .5em;
	border:0px;
	word-wrap:break-word;
	word-break:break-all;
}

/* recent.inc.php,showrss.inc.php */
ul.recent_list
{
	padding:0px 0px 0px .5em;
	margin:0px 0px 0px .5em;
	border:0px;
	word-wrap:break-word;
	word-break:break-all;
}
ul.recent_list li
{
	line-height:110%;
}

/* ref.inc.php */
div.img_margin
{
	margin-left:32px;
	margin-right:32px;
}
div.img_nomargin
{
	margin-left:-20px;
	margin-right:-20px;
}

/* vote.inc.php */
.style_table_vote
{
	padding:0px;
	border:0px;
	margin:auto auto auto 32px !important;
	text-align:left;
	color:inherit;
	background-color:<?php echo $bgcolor_tableline ?>;
}
td.vote_label
{
	color:inherit;
	background-color:#FFCCCC;
}
td.vote_td1
{
	color:inherit;
	background-color:#DDE5FF;
}
td.vote_td2
{
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
}

/* PukiWiki Plus! */
td.ltable
{
	width:160px;
	vartical-align:top;
	background-color: <?php echo $bgcolor_barcolor ?>;
}

td.rtable
{
	width:160px;
	vartical-align:top;
	background-color: <?php echo $bgcolor_barcolor ?>;
}

td.ctable
{
	vartical-align:top;
	padding-left:28px;
	padding-right:28px;
}

div#floattable
{
	min-width:620px;
}

div#ltable
{
	width:160px;
	vartical-align:top;
	float:left;
}

div#rtable
{
	width:160px;
	vartical-align:top;
	float:right;
}

div#ctablelr
{
	vartical-align:top;
	padding-left:1%;
	padding-right:1%;
	margin-left:180px;
	margin-right:180px;
}

div#ctablel
{
	vartical-align:top;
	padding-left:1%;
	padding-right:1%;
	margin-left:180px;
	margin-right:20px;
}

div#ctabler
{
	vartical-align:top;
	padding-left:1%;
	padding-right:1%;
	margin-left:20px;
	margin-right:180px;
}

div#ctable
{
	vartical-align:top;
	padding-left:1%;
	padding-right:1%;
	margin-left:20px;
	margin-right:20px;
}

div#cleartable
{
	clear:both;
}

div#topicpath
{
	color:black;
	font-size:80%;
}

div#footerltable
{
	float:left;
}

div#footerrtable
{
}

blockquote
{
//	border: 1px solid #FFCC99;
//	border-color: <?php echo $bgcolor_contents ?>;
	border: 0px
	margin-top: 0.5em;
	margin-bottom: 0.5em;
}
blockquote p
{
	margin: 0.5em 1em;
}

/* tooltip.inc.php */
abbr, .tooltip {
	border-style: none none dotted none;
	border-width: medium medium 1px medium;
	cursor: help
}

.linktip {
}

/* amazon.inc.php */
div.amazon_img {
	margin:16px 10px 8px 8px;
	text-align:center;
}

div.amazon_imgetc {
	 margin:0px 8px 8px 8px;
	 text-align:center;
}

div.amazon_sub {
	 font-size:90%;
}

div.amazon_avail {
	 margin-left:150px;
	 font-size:90%;
}

td.amazon_td {
	 font-size:90%;
	 text-align:center;
}

table.amazon_tbl {
	 border:0;
	 width:115px;
	 font-size:90%;
	 text-align:center;
}

/* calendar_viewer.inc.php, minicalendar_viewer.inc.php */
div.trackback {
	font-size:80%;
	text-align:right;
}

div.prevnext {
}

div.prevnext_l {
	float:left;
}

div.prevnext_r {
	float:right;
}

/* minicalendar.inc.php */
.ministyle_calendar
{
	width:150px;
	padding:0px;
	margin:2px;
	color:inherit;
	background-color:#E0E0E0;
	text-align:center;
}

.ministyle_td_caltop
{
	padding:2px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_menutitle ?>;
	font-size:12px;
	text-align:center;
}

.ministyle_td_today
{
	padding:1px;
	margin:1px;
	color:inherit;
	background-color:#FFFFDD;
	font-size:16px;
	text-align:center;
}

.ministyle_td_sat
{
	padding:1px;
	margin:1px;
	color:inherit;
	background-color:#DDE5FF;
	font-size:16px;
	text-align:center;
}

.ministyle_td_sun
{
	height:20px;
	padding:1px;
	margin:1px;
	color:inherit;
	background-color:#FFEEEE;
	font-size:16px;
	text-align:center;
}

.ministyle_td_blank
{
	padding:1px;
	margin:1px;
	color:inherit;
	background-color:#EEEEEE;
	font-size:16px;
	text-align:center;
}

.ministyle_td_day
{
	padding:1px;
	margin:1px;
	color:inherit;
	background-color:<?php echo $bgcolor_tablecolor ?>;
	font-size:16px;
	text-align:center;
	vertical-align:center;
}

.ministyle_td_week
{
	width:23px;
	padding:2px 0px;
	margin:2px 0px;
	color:inherit;
	background-color:#E0E0E0; /*#DDE5EE;*/
	font-size:12px;
	font-weight:bold;
	text-align:center;
}

/* minicalendar_viewer */
h3.minicalendar
{
	margin: 8px -8px 0px -6px;

        font-size:100%;
	border:0px;
	border-left:8px solid;
	border-bottom:1px solid;
	border-color:#ffffff #ffffff #999999 #ff9933;
        color:inherit;
}

h4.minicalendar
{
	margin: 8px -8px 0px -6px;

        font-size:100%;
	border:0px;
	border-left:8px solid;
	border-bottom:1px solid;
	border-color:#ffffff #ffffff #999999 #ffCC66;
        color:inherit;
}

.minicalendar_viewer
{
        margin: 0px 0px 0px 0px;
}

.minicalendar_viewer h4
{
	margin: 0.5em 0px 0px 0.5em;
}

.minicalendar_viewer p
{
	margin: 0.5em 0px 0px 1.5em;
}

/* popup toc */
#poptoc
{
	font-size:90%;
	border:gray thin outset; padding:0.5em;
	background: <?php echo $bgcolor_barcolor ?>;
	/*min-width:18em; max-width:25em;*/
	/*width:22em;*/
	margin-right:-10em;
	z-index:1;
	position:absolute;
	display:none;
}
#poptoc a:hover
{
	background:<?php echo $fontcolor_aLink2 ?>;
}
#poptoc a
{
	color:blue;
}
#poptoc h2
{
	color:navy;
	background:<?php echo $bgcolor_navigator ?>;
	font-size:small;
	font-weight:normal;
	padding:0.3em;
	margin:0;
	text-align:center;
	border:<?php echo $bgcolor_menuborder ?>;
}
#poptoc h2 a
{
	font-size:90%;
	color:navy;
	text-decoration:none;
}
#poptoc h2 img
{
	margin-bottom:-3px;
	margin-right: 2px;
}
#poptoc .nav
{
	border-top:1px gray solid;
	padding-top:0.2em;
	text-align:center;
}
#poptoc a.here
{
	color: #333333;
	background: #fff8f8;
	text-decoration: none;
	border:1px dotted gray;
/*	cursor:default;	*/
}

/* for BugTrack/1 */
.dummyblock
{
	width:77%;
	float:left;
	display:block;
}

/* for MediaPlayer */
.mediaplayerbutton
{
	margin:2px 2px;
	width:24px;
}

.playercontainer
{
	border:solid 1px #333;
	width:320px;
	text-align:center;
	vertical-align:middle;
	position:relative;
}

.videosplash
{
	position:expression('absolute');
	display:block;
}

.player
{
	display:none;
	display:expression(PlayerSupported(this)?'block':'none');
	background-color:Black;
	font-size:0px;
}

.controlstable
{
	width:320px;
	margin:0px;
	background-image:url(../../../image/player/base.gif);
/*	background-repeat:no-repeat;	*/
}

table.controlstable tr td
{
	background-color:transparent;
}

.controlstablenoscript
{
	display:expression(PlayerSupported(this)?'none':'block');
	margin:0px;
	background-image:url(../../../image/player/base.gif);
	background-repeat:no-repeat;
}

.slider
{
	background-image:url(../../../image/player/playbar.gif);
	background-repeat:no-repeat;
	background-position:center center;
}

.indicator,.downloadindicator
{
	width:0px;
	height:3px;
	margin-left:1px;
	margin-top:2px;
}

.indicatorhandle
{
	margin-top:2px;
}

.center_form
{
	text-align:center;
	padding:4px 0px 8px;
	margin:0px;
}

span.add_word
{
        background-color:#FFFF66;
}

span.remove_word
{
        background-color:#A0FFFF;
}

div#validxhtml
{
        text-align:right;
        white-space:nowrap;
}

div#sigunature
{
        margin: 0px 16px 0px 0px;
        white-space:nowrap;
        font-size: 12px;
        line-height: 122%;
}

#footerctable
{
	width:98%;
}

/* pukiwiki extend anchor */
img.ext
{
	margin-left: 2px;
	vertical-align: baseline;
}
img.inn
{
	margin-left: 2px;
	vertical-align: baseline;
}

/* headarea extend */
#header .style_table
{
	background-color: transparent;
}

#header .style_table .style_th
{
	margin: 0px;
	padding: 0px;
	background-color: transparent;
}

#header .style_table .style_td
{
	margin: 0px;
	padding: 0px;
	background-color: transparent;
}

#header .style_table .style_td h1.title
{
	font-size: <?php echo $title_fontsize ?>;
	font-weight:bold;
	background-color:transparent;
	padding: 0px;
	border: 0px;
	margin: 0px;
}

#footer .style_table
{
	background-color: transparent;
}

#footer .style_table .style_th
{
	margin: 0px;
	padding: 0px;
	background-color: transparent;
}

#footer .style_table .style_td
{
	margin: 0px;
	padding: 0px;
	background-color: transparent;
}

/* minicalendar+public_holiday view */
.date_weekday {
	font-family: <?php echo $default_fontfamily2; ?>;
	font-size: 100%;
	line-height: 110%;
	font-weight: normal;
	padding: 3px 0;
	border: none;
	border-bottom: 1px solid #666;
	margin-bottom: 10px;
}

.date_weekend {
	color: #14F;
	font-family: <?php echo $default_fontfamily2; ?>;
	font-size: 100%;
	line-height: 110%;
	font-weight: normal;
	padding: 3px 0;
	border: none;
	border-bottom: 1px solid #666;
	margin-bottom: 10px;
}

.date_holiday {
	color: #f41;
	font-family: <?php echo $default_fontfamily2; ?>;
	font-size: 100%;
	line-height: 110%;
	font-weight: normal;
	padding: 3px 0px;
	border: none;
	border-bottom: 1px solid #666;
	margin-bottom: 10px;
}

.day {
	float: left;
	font-size: 200%;
	line-height: 100%;
	font-weight: normal;
	letter-spacing: 0.02em;
	padding-left: 4pt;
	padding-right: 3pt;
	padding-top: 3pt;
	padding-bottom: 3pt;
	margin: 0px;
	margin-top:3pt;
	margin-right:4pt;
}
.date_weekday .day {
	background-color: #333;
	color: #fff;
}
.date_weekend .day {
	background-color: #14F;
	color: #fff;
}
.date_holiday .day {
	background-color: #F41;
	color: #fff;
}



/**
 * PukiWiki Plugin Code highlight
 *
 */

/* オペレータ */
span.code_operator {color: blue;}
/* 識別子 */
span.code_identifier {color: darkblue;}

/* 制御構文 */
span.code_control  {color: navy;}
/* 標準関数 */
span.code_function {color: blue;}
/* 定数 */
span.code_constant {color: teal;}

/* module, import, 将来対応する pragma */
span.code_pragma {color: #008080;}
/* __stdcall などの処理系専用の奴とか */
span.code_system {color: #5f0000;}
/* 環境変数  */
span.code_environment {color: #777777;}
/* 文字列 */
span.code_string {color: green;}
/* コメント */
span.code_comment {color: darkorange;}

/* 個々のハイライト専用 */
/* for TeX */
span.code_formula {color: teal;}
/* for diff*/
span.code_changed {color: green;}
span.code_added   {color: blue;}
span.code_removed {color: red;}
/* for make*/
span.code_execute {color: teal;}
span.code_target  {color: darkblue;}
span.code_src     {color: darkgreen;}
/* for PukiWiki */
span.code_header{color: blue;}
span.code_table {color: darkgreen;}
span.code_list  {color: navy;}
span.code_pre   {color: teal;}
span.code_quote {color: #777700;}

/* ソースコード表示部分の設定 */
/* 段組をしない場合の設定 */
pre.code {
    background: #EEFFFF;
    margin: 1em 2em 0.5em 1em;
    padding: 0.5em;
    border-top:    #DDDDEE 1px solid;
    border-right:  #888899 1px solid;
    border-bottom: #888899 1px solid;
    border-left:   #DDDDEE 1px solid;
	white-space: pre;
	overflow: visible;
    line-height: 120%;
}
/* 段組をした場合の設定 */
table.code_table pre.code,
div.code_table pre.code,
table.code_table pre.pre_body,
div.code_src pre.pre_body
{
    margin: 0;
    padding: 0;
    padding-left: 0.5em;
    border: 1px;
    line-height: 120%;
}

/* 行番号表示 */
pre.code_number,
pre.pre_number
{
    background: #FFFFFF;
    margin: 0;
    padding: 0;
    padding-right: 0.5em;
    border: 1px;
    border-right: 1px solid #CCDDDD;
    line-height: 120%;
    min-width: 3ex;
}

/* アウトライン表示 */
pre.code_outline {
    margin:  0;
    padding: 0;
    border: 1px;
    border-right: 1px solid #F5FFFF;
    width: 10px;
    text-align: center;
    background: #E0F5F5;
    line-height: 120%;
}

/* 枠組 */
div.code_table,
div.pre_table
{
    color: black;
    background-color: #F0F8FF;
    border-top:    #DDDDEE 1px solid;
    border-right:  #888899 1px solid;
    border-bottom: #888899 1px solid;
    border-left:   #DDDDEE 1px solid;
    margin: 1em 2em 0.5em 1em;
    white-space: pre;
}

/* 段組要素の設定無効化 */
table.code_table,
table.code_table td,
div.code_number pre,
div.code_outline pre,
div.code_src pre,
div.pre_number pre,
div.pre_body pre
{
    margin:  0;
    padding: 0;
    border: none;
}

/* tableによる段組 */
td.code_src,
td.pre_body 
{width:100%;}

/* divによる分割の定義  */
div.code_number,
div.code_outline,
div.code_src,
div.pre_number,
div.pre_body
{
    position: relative;
    margin: 0;
    padding: 0;
    left: 0;
    float: left;
}

/**
 * アウトライン・メニューの設定
 */

/* icon */ 
div.code_menu {
    background-color: #d4d0c8;
}

/* アウトラインが閉じた時のイメージ */
img.code_dotimage {width:20px;height:8px;margin:0;padding:0;}

/* アウトラインの設定 */
a.code_outline{
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #888888;
    text-decoration: none;
}
a.code_outline:link
{
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #888888;
    text-decoration: none;
}
a.code_outline:visited
{
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #888888;
    text-decoration: none;
}
a.code_outline:hover
{
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #888888;
    text-decoration: none;
}
a.code_outline:active
{
    background-color: #FFFFFF;
    color: black;
    border: 1px solid #888888;
    text-decoration: none;
}

/* extra attach table */
table.attach_table
{
	padding:0px;
	border:0px;
	margin:auto;
	text-align:left;
	color:inherit;
	background-color: #CCCCCC;
}
th.attach_th
{
	padding:2px;
	margin:1px;
	text-align:center;
	color:inherit;
	background-color: #FFEECC;
}
td.attach_td1
{
	background-color: #FFFFFF;
}
td.attach_td2
{
	background-color: #CCFFCC;
}

/* navibar2.inc.php */
div#navigator2 {
	margin: 4px 0px;
	padding: 0px 4px;
	left: 0px;
	width: 100%;
	background-color: <?php echo $bgcolor_navigator ?>;
	border: <?php echo $bgcolor_naviborder ?>;
	clear:both;
	white-space: nowrap;
}

td.navimenu {
	margin: 2px 5px 2px 5px;
	padding: 2px;
	font-size:84%;
	background-color: <?php echo $bgcolor_navigator ?>;
}
td.navimenu a {
	color: <?php echo $fontcolor_aLink2 ?>;
	padding: 1px 8px 1px;
	border: <?php echo $bgcolor_naviblock ?>;
	text-decoration: none;
	display:block;
}
td.navimenu a:active {
	border: 1px #ff9933 solid;
	color: <?php echo $fontcolor_aLink2 ?>;
	background-color:transparent;
	text-decoration:none;
}
td.navimenu a:visited {
	color: <?php echo $fontcolor_aLink2 ?>;
	text-decoration: none;
}
td.navimenu a:hover {
	border: <?php echo $bgcolor_naviborder2 ?>;
	color: #ff0000;
	background-color: <?php echo $bgcolor_navigator2 ?>;
	text-decoration:none;
}

div.naviblock {
	padding: 2px 5px 2px 5px;
	border: <?php echo $bgcolor_naviborder ?>;
	border-top: <?php echo $bgcolor_naviblock ?>;
	font-size:84%;
	background-color: <?php echo $bgcolor_navigator ?>;
	visibility: hidden;
	position: absolute;
	z-index:10;
}
div.menuitem {
	padding: 1px 0px;
}
div.naviblock a {
	padding: 1px 8px;
	color: <?php echo $fontcolor_aLink2 ?>;
	border: <?php echo $bgcolor_naviblock ?>;
	text-decoration: none;
	display:block;
}
div.naviblock a:active {
	border: 1px #ff9933 solid;
	color: <?php echo $fontcolor_aLink2 ?>;
	background-color:transparent;
	text-decoration: none;
}
div.naviblock a:visited {
	color: <?php echo $fontcolor_aLink2 ?>;
	text-decoration: none;
}
div.naviblock a:hover {
	border: <?php echo $bgcolor_naviborder2 ?>;
	color: #ff0000;
	background-color:<?php echo $bgcolor_navigator2 ?>;
	text-decoration: none;
}

/* 1.4.6u1 added */
span.linkwarn {
	font-size:xx-small;
	font-weight:bold;
	color:#f00;
	background-color:#ff6;
}

span.linkwarn a {
	color:#f00;
	background-color:#ff6;
}
