<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.skin.php,v 1.34.24 2005/11/01 11:23:43 miko Exp $
//
if (!defined('DATA_DIR')) { exit; }

// Decide charset for CSS
$css_charset = 'iso-8859-1';
switch(UI_LANG){
	case 'ja_JP': $css_charset = 'Shift_JIS'; break;
	case 'ja':    $css_charset = 'Shift_JIS'; break;
}
// Output header
pkwk_common_headers();
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=' . CONTENT_CHARSET);

// Output HTML DTD, <html>, and receive content-type
if (isset($pkwk_dtd)) {
	$meta_content_type = pkwk_output_dtd($pkwk_dtd);
} else {
	$meta_content_type = pkwk_output_dtd();
}
// Plus! not use $meta_content_type. because meta-content-type is most browser not used. umm...
?>
<head>
 <meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo(CONTENT_CHARSET); ?>" />
 <meta http-equiv="content-style-type" content="text/css" />
 <meta http-equiv="content-script-type" content="text/javascript" />

<?php if (!$is_read) { ?>
 <meta name="robots" content="NOINDEX,NOFOLLOW" />
<?php } ?>

<?php global $newtitle, $newbase; ?>
<?php if ($title == $defaultpage) { ?>
 <title><?php echo "$page_title" ?></title>
<?php } elseif ($newtitle != '' && $is_read) { ?>
 <title><?php echo "$newtitle - $page_title" ?></title>
<?php } else { ?>
 <title><?php echo "$title - $page_title" ?></title>
<?php } ?>
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>default.css" type="text/css" media="screen" charset="Shift_JIS" />
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>print.css" type="text/css" media="print" charset="Shift_JIS" />
 <link rel="alternate" href="<?php echo $_LINK['mixirss'] ?>" type="application/rss+xml" title="RSS" />
 <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
 <script type="text/javascript" src="<?php echo SKIN_URI ?>default.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>kanzaki.js"></script>
<?php
  global $trackback, $referer;
  if ($trackback) {
?>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>trackback.js"></script>
<?php } ?>
<?php echo $head_tag ?>
</head>
<body>

<?php if (exist_plugin_convert('headarea') && do_plugin_convert('headarea') != '') { ?>
<div id="header">
<h1 style="display:none;"><?php echo(($newtitle!='' && $is_read)?$newtitle:$page) ?></h1>
<?php echo do_plugin_convert('headarea') ?>
</div>
<?php } else { ?>
<div id="header">
 <a href="<?php echo $modifierlink ?>"><img id="logo" src="<?php echo IMAGE_URI; ?>pukiwiki.png" width="80" height="80" alt="[PukiWiki]" title="[PukiWiki]" /></a>
 <h1 class="title"><?php echo(($newtitle!='' && $is_read)?$newtitle:$page) ?></h1>

<?php if ($is_page) { ?>
 <a href="<?php echo $_LINK['reload'] ?>"><span class="small"><?php echo $_LINK['reload'] ?></span></a>
<?php } ?>

</div>
<?php
 if (exist_plugin('navibar2')) {
  echo do_plugin_convert('navibar2');
 } else if (exist_plugin('navibar')) {
  echo do_plugin_convert('navibar','top,list,search,recent,help,|,new,edit,upload,|,trackback');
  echo $hr;
 }
?>

<?php } ?>

<div id="contents">
<table class="contents" width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr>
<?php if (arg_check('read') && exist_plugin_convert('menu') && do_plugin_convert('menu') != '') { ?>
  <td class="ltable" valign="top"><div id="menubar"><?php echo do_plugin_convert('menu') ?></div></td>
<?php } ?>
  <td class="ctable" valign="top">
   <?php if ($is_page and exist_plugin_convert('topicpath')) { echo do_plugin_convert('topicpath'); } ?>
   <div id="body"><?php echo $body ?></div>
  </td>
<?php if (arg_check('read') && exist_plugin_convert('side') && do_plugin_convert('side') != '') { ?>
  <td class="rtable" valign="top"><div id="sidebar"><?php echo do_plugin_convert('side') ?></div></td>
<?php } ?>
 </tr>
</table>
</div>

<?php if ($notes) { ?>
<div id="note">
<?php echo $notes ?>
</div>
<?php } ?>

<?php if ($attaches) { ?>
<div id="attach">
<?php echo $hr ?>
<?php echo $attaches ?>
</div>
<?php } ?>


<?php echo $hr ?>
<?php if (exist_plugin_convert('footarea') && do_plugin_convert('footarea') != '') { ?>
<div id="footer">
<?php echo do_plugin_convert('footarea') ?>
</div>
<?php } else { ?>
<?php if (exist_plugin('toolbar')) {
 echo do_plugin_convert('toolbar','reload,|,new,edit,freeze,diff,upload,copy,rename,|,top,list,search,recent,backup,refer,|,help,|,mixirss');
} ?>
<?php if ($lastmodified) { ?>
<div id="lastmodified">
 Last-modified: <?php echo $lastmodified ?>
</div>
<?php } ?>


<?php if ($related) { ?>
<div id="related">
 Link: <?php echo $related ?>
</div>
<?php } ?>


<div id="footer">
<table id="footertable" border="0" cellspacing="0" cellpadding="0">
<tr>
 <td id="footerltable">
  <?php if (exist_plugin_inline('qrcode')) { ?>
  <?php
   $a_script = $script;
   $a_script = str_replace("\\", "\\\\", $a_script);
   $a_script = str_replace(':', '\:', $a_script);
   $a_script = str_replace(';', '\;', $a_script);
   $a_script = str_replace(',', '\,', $a_script);
   $a_page = str_replace('%', '%25', $r_page);
   echo plugin_qrcode_inline(1,"$script?$a_page");
  ?>
  <?php } ?>
 </td>
 <td id="footerctable"><div id="sigunature">
  Modified by <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a>.
  <br />
  Powered by PukiWiki Plus! <?php echo S_VERSION ?>.
  HTML convert time to <?php echo $taketime ?> sec.
 </div></td>
 <td id="footerrtable"><div id="validxhtml">
<?php if (! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1) { ?>
  <a href="http://validator.w3.org/check/referer"><img src="image/valid-xhtml11.png" width="88" height="31" alt="Valid XHTML 1.1" title="Valid XHTML 1.1" /></a>
<?php } else if ($pkwk_dtd >= PKWK_DTD_XHTML_1_0_FRAMESET) {  ?>
  <a href="http://validator.w3.org/check/referer"><img src="image/valid-xhtml10.png" width="88" height="31" alt="Valid XHTML 1.0" title="Valid XHTML 1.0" /></a>
<?php } else if ($pkwk_dtd >= PKWK_DTD_HTML_4_01_FRAMESET) {  ?>
  <a href="http://validator.w3.org/check/referer"><img src="image/valid-html40.png" width="88" height="31" alt="Valid HTML 4.0" title="Valid HTML 4.0" /></a>
<?php } ?>
 </div></td>
</tr>
</table>
</div>
<?php } ?>

</body>
</html>
