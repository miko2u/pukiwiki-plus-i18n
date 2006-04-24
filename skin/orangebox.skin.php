<?php
/////////////////////////////////////////////////
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
//
// $Id: orangebox.skin.php,v 1.18.5 2006/04/25 01:53:00 upk Exp $
// Original is ari-

// Prohibit direct access
if (! defined('UI_LANG')) exit;

// Set skin-specific images
$_IMAGE['skin']['logo']     = 'pukiwiki.png';
$_IMAGE['skin']['reload']   = 'reload.png';
$_IMAGE['skin']['new']      = 'new.png';
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

$lang  = $_LANG['skin'];
$link  = $_LINK;
$image = $_IMAGE['skin'];

// Decide charset for CSS
$css_charset = 'iso-8859-1';
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
<?php global $newtitle, $newbase; ?>
<?php if ($title == $defaultpage) { ?>
 <title><?php echo "$page_title" ?></title>
<?php } elseif ($newtitle != '' && $is_read) { ?>
 <title><?php echo "$newtitle - $page_title" ?></title>
<?php } else { ?>
 <title><?php echo "$title - $page_title" ?></title>
<?php } ?>
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>orangebox.css" type="text/css" media="screen,print" charset="<?php echo $css_charset ?>" />
 <link rel="stylesheet" href="<?php echo SKIN_URI ?>greybox/greybox.css" type="text/css" media="all" charset="<?php echo $css_charset ?>" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $link['rss'] ?>" />
 <script type="text/javascript">
 <!--
<?php if (exist_plugin_convert('js_init')) echo do_plugin_convert('js_init'); ?>
 // -->
 </script>
<?php global $language,$use_local_time; ?>
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
<?php
  global $trackback, $referer;
  if ($trackback) {
?>
 <script type="text/javascript" src="<?php echo SKIN_URI ?>trackback.js"></script>
<?php } ?>
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
 } else {
  echo '<div id="navigator">';
  echo convert_html(get_source('SiteNavigator'));
  echo '</div>';
 }
?>
<div id="main">
<div id="center_bar">
<div id="content">
<h1 class="title"><?php echo $page ?></h1>
<?php if ($lastmodified) { ?>
<div id="lastmodified"><?php echo $lastmodified ?></div>
<?php } ?>
<div id="body"><?php echo $body ?></div>
<div id="summary">
<?php if ($notes) { ?>
<div id="note"><?php echo $notes ?></div>
<?php } ?>
<div id="trackback">
<?php
  if ($trackback) {
    $tb_id = tb_get_id($_page);
?>
<a href="<?php echo "$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id" ?>">TrackBack(<?php echo tb_count($_page) ?>)</a> | 
<?php } ?>
<?php
  if ($referer) {
?>
<a href="<?php echo "$script?plugin=referer&amp;page=$r_page" ?>">外部リンク元</a>
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
<h2>検索</h2>
<form action="<?php echo $script ?>" method="post">
<div><input name="encode_hint" value="ぷ" type="hidden" /></div>
<div>
<input name="plugin" value="lookup" type="hidden" />
<input name="refer" value="<?php echo $title ?>" type="hidden" />
<input name="page" size="20" value="" type="text" accesskey="s" title="serch box"/>
<input value="Go!" type="submit" accesskey="g"/><br/>
<input name="inter" value="検索" type="radio" checked="checked" id="serch_site" /><label for="serch_site">サイト内</label>
<input name="inter" value="Google.jp" type="radio" accesskey="w" id="serch_web"/><label for="serch_web">Web</label>
</div>
</form></div>
<div id="rightbar2" class="side_bar">
<h2>編集操作</h2>
<ul>
<?php if ($is_page) { ?>
	<li><a href="<?php echo $link_edit ?>"><img src="<?php echo IMAGE_DIR ?>edit.png" width="20" height="20" alt="編集" title="編集" />編集</a></li>
<?php   if ((bool)ini_get('file_uploads')) { ?>
	<li><a href="<?php echo $link_upload ?>"><img src="<?php echo IMAGE_DIR ?>file.png" width="20" height="20" alt="添付" title="添付" />添付</a></li>
<?php   } ?>
	<li><a href="<?php echo $link_diff ?>"><img src="<?php echo IMAGE_DIR ?>diff.png" width="20" height="20" alt="差分" title="差分" />差分</a></li>
<?php } ?>
<?php if ($do_backup) { ?>
	<li><a href="<?php echo $link_backup ?>"><img src="<?php echo IMAGE_DIR ?>backup.png" width="20" height="20" alt="バックアップ" title="バックアップ" />バックアップ</a></li>
<?php } ?>
</ul>
</div>
<?php if (get_source('RightBar')) { ?>
<div id="rightbar3" class="side_bar">
	<?php echo convert_html(get_source('RightBar')) ?>
</div>
<?php } ?>
</div>
</div>

<div id="left_bar">
<div id="menubar" class="side_bar"><?php echo convert_html(get_source('MenuBar')) ?></div>
</div>

<div id="footer">
<div id="copyright">
	Modified by <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a><br />
	<?php echo S_COPYRIGHT ?>
</div>
</div>

</div>

<?php if (exist_plugin_convert('tz')) echo do_plugin_convert('tz'); ?>
<?php echo $foot_tag ?>
</body>
</html>
