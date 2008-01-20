<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: bluebox.skin.php,v 1.18.13 2008/01/20 13:26:00 upk Exp $
// Original is ari-

// Prohibit direct access
if (! defined('UI_LANG')) exit;

// Set skin-specific images
$_IMAGE['skin']['logo']     = 'pukiwiki.png';
$_IMAGE['skin']['reload']   = 'reload.png';
$_IMAGE['skin']['new']      = 'new.png';
$_IMAGE['skin']['newsub']   = 'new_sub.png';
$_IMAGE['skin']['edit']     = 'edit.png';
$_IMAGE['skin']['freeze']   = 'freeze.png';
$_IMAGE['skin']['unfreeze'] = 'unfreeze.png';
$_IMAGE['skin']['diff']     = 'diff.png';
$_IMAGE['skin']['upload']   = 'file.png';
$_IMAGE['skin']['copy']     = 'copy.png';
$_IMAGE['skin']['rename']   = 'rename.png';
$_IMAGE['skin']['top']      = 'top.png';
$_IMAGE['skin']['list']     = 'list.png';
$_IMAGE['skin']['search']   = 'search.png';
$_IMAGE['skin']['recent']   = 'recentchanges.png';
$_IMAGE['skin']['backup']   = 'backup.png';
$_IMAGE['skin']['help']     = 'help.png';
$_IMAGE['skin']['rss']      = 'rss.png';
$_IMAGE['skin']['top']      = 'plus/home.png';
$_IMAGE['skin']['trackback']= 'plus/trackback.png';
$_IMAGE['skin']['refer']    = 'plus/referer.png';
$_IMAGE['skin']['skeylist'] = 'plus/skeylist.png';
$_IMAGE['skin']['linklist'] = 'plus/linklist.png';

$lang  = & $_LANG['skin'];
$link  = & $_LINK;
$image = & $_IMAGE['skin'];

// Decide charset for CSS
// $css_charset = 'iso-8859-1';
$css_charset = 'utf-8';
switch(UI_LANG){
        case 'ja_JP': $css_charset = 'Shift_JIS'; break;
}

// Output header
pkwk_common_headers();
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=' . CONTENT_CHARSET);
header('ETag: ' . md5(MUTIME));

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
<?php if ($nofollow || ! $is_read)  { ?> <meta name="robots" content="NOINDEX,NOFOLLOW" /><?php } ?>
<?php if ($title == $defaultpage) { ?>
 <title><?php echo $page_title ?></title>
<?php } elseif ($newtitle != '' && $is_read) { ?>
 <title><?php echo $newtitle.' - '.$page_title ?></title>
<?php } else { ?>
 <title><?php echo $title.' - '.$page_title ?></title>
<?php } ?>
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>bluebox.css" type="text/css" media="screen,print" charset="<?php echo $css_charset ?>" />
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>greybox/greybox.css" type="text/css" media="all" charset="<?php echo $css_charset ?>" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $link['rss'] ?>" />
 <script type="text/javascript">
 <!--
<?php if (exist_plugin_convert('js_init')) echo do_plugin_convert('js_init'); ?>
 // -->
 </script>
 <script type="text/javascript" src="<?php echo SKIN_URI.'lang/'.$language ?>.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>default.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>kanzaki.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>ajax/textloader.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>ajax/glossary.js"></script>
<?php if (! $use_local_time) { ?>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>tzCalculation_LocalTimeZone.js"></script>
<?php } ?>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>greybox/AmiJS.js"></script>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>greybox/greybox.js"></script>
<?php echo $head_tag ?>
</head>
<body>

<div id="base">
<div id="header">
<div id="logo"><a href="<?php echo $link_top ?>"><?php echo $page_title ?></a></div>
</div>

<?php
 if (exist_plugin('navibar2')) {
  echo do_plugin_convert('navibar2');
 } else if (exist_plugin('navibar')) {
  echo do_plugin_convert('navibar','top,list,search,recent,help,|,new,edit,upload,|,trackback');
  echo $hr;
 }
?>

<div id="main">
<div id="center_bar">
<div id="content">
<h1 class="title"><?php echo(($newtitle!='' && $is_read)?$newtitle:$page) ?></h1>
<?php if ($lastmodified) { ?>
<div id="lastmodified"><?php echo $lastmodified ?></div>
<?php } ?>
<div id="body"><?php echo $body ?></div>
<div id="summary">
<?php if ($notes) { ?>
<div id="note"><?php echo $notes ?></div>
<?php } ?>
<div id="trackback">
<?php if ($trackback) {
?>
<a href="<?php echo $link_trackback ?>"><?php echo $lang['trackback'].'('.tb_count($_page).')' ?></a> | 
<?php } ?>
<?php if ($referer) { ?>
<a href="<?php echo $link_refer ?>"><?php echo $lang['refer'] ?></a>
<?php } ?>
</div>

<?php if ($related) { ?>
<div id="related"> Link: <?php echo $related ?></div>
<?php } ?>

<?php if ($attaches) { ?>
<div id="attach"><?php echo $hr ?><?php echo $attaches ?></div>
<?php } ?>

</div>
</div>
</div>

<div id="right_bar">
<div id="rightbar1" class="side_bar">
<h2><?php echo $lang['search'] ?></h2>
<form action="<?php echo $script ?>" method="post">
<div><input name="encode_hint" value="<?php echo $_LANG['encode_hint'] ?>" type="hidden" /></div>
<div>
<input name="plugin" value="lookup" type="hidden" />
<input name="refer" value="<?php echo $title ?>" type="hidden" />
<input name="page" size="20" value="" type="text" accesskey="s" title="serch box"/>
<input value="Go!" type="submit" accesskey="g"/><br/>
<input name="inter" value="<?php $lang['search'] ?>" type="radio" checked="checked" id="serch_site" /><label for="serch_site">This Site</label>
<input name="inter" value="Google.jp" type="radio" accesskey="w" id="serch_web"/><label for="serch_web">Web</label>
</div>
</form></div>
<div id="rightbar2" class="side_bar">
<h2><?php echo $lang['edit'] ?></h2>
<ul>
<?php if ($is_page) { ?>
	<li><a href="<?php echo $link_edit ?>"><img src="<?php echo IMAGE_URI ?>edit.png" width="20" height="20" alt="<?php echo $lang['edit'] ?>" title="<?php echo $lang['edit'] ?>" /><?php echo $lang['edit'] ?></a></li>
<?php   if ((bool)ini_get('file_uploads')) { ?>
        <li><a href="<?php echo $link_upload ?>"><img src="<?php echo IMAGE_URI ?>file.png" width="20" height="20" alt="<?php echo $lang['upload'] ?>" title="<?php echo $lang['upload'] ?>" /><?php echo $lang['upload'] ?></a></li>
<?php   } ?>
	<li><a href="<?php echo $link_diff ?>"><img src="<?php echo IMAGE_URI ?>diff.png" width="20" height="20" alt="<?php echo $lang['diff'] ?>" title="<?php echo $lang['diff'] ?>" /><?php echo $lang['diff'] ?></a></li>
<?php } ?>
<?php if ($do_backup) { ?>
	<li><a href="<?php echo $link_backup ?>"><img src="<?php echo IMAGE_URI ?>backup.png" width="20" height="20" alt="<?php echo $lang['backup'] ?>" title="<?php echo $lang['backup'] ?>" /><?php echo $lang['backup'] ?></a></li>
<?php } ?>
</ul>
</div>

<?php global $always_menu_displayed; if (arg_check('read')) $always_menu_displayed = 1; ?>
<?php if ($always_menu_displayed && exist_plugin_convert('side') && do_plugin_convert('side') != '') { ?>
<div id="rightbar3" class="side_bar">
<?php echo do_plugin_convert('side') ?></div>
<?php } ?>
</div>
</div>

<div id="left_bar">
<?php if ($always_menu_displayed && exist_plugin_convert('menu') && do_plugin_convert('menu') != '') { ?>
<div id="menubar" class="side_bar"><?php echo do_plugin_convert('menu') ?></div>
<?php } ?>
</div>

<div id="footer">
<div id="copyright">
	Founded by <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a><br />
	<?php echo S_COPYRIGHT ?>
</div>
</div>

</div>
<?php if (exist_plugin_convert('tz')) echo do_plugin_convert('tz'); ?>
<?php echo $foot_tag ?>
</body>
</html>
