<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: bluebox.skin.php,v 1.18.2 2004/11/02 14:02:10 miko Exp $
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
header('Cache-control: no-cache');
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
 <meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo CONTENT_CHARSET ?>" />
 <meta http-equiv="content-style-type" content="text/css" />
<?php if (! $is_read)  { ?> <meta name="robots" content="NOINDEX,NOFOLLOW" /><?php } ?>
<?php if (PKWK_JAVASCRIPT && isset($javascript)) { ?> <meta http-equiv="Content-Script-Type" content="text/javascript" /><?php } ?>

<?php global $newtitle, $newbase; ?>
<?php if ($title == $defaultpage) { ?>
 <title><?php echo "$page_title" ?></title>
<?php } elseif ($newtitle != '' && $is_read) { ?>
 <title><?php echo "$newtitle - $page_title" ?></title>
<?php } else { ?>
 <title><?php echo "$title - $page_title" ?></title>
<?php } ?>
 <link rel="stylesheet" href="skin/bluebox.css" type="text/css" media="screen,print" charset="Shift_JIS" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $link['rss'] ?>" />

<?php if (PKWK_JAVASCRIPT && $trackback_javascript) { ?> <script type="text/javascript" src="skin/trackback.js"></script><?php } ?>

<?php echo $head_tag ?>
</head>
<body>

<div id="base">
<div id="header">
<div id="logo"><a href="<?php echo $link_top ?>"><?php echo $page_title ?></a></div>
</div>
<div id="navigator">
 <?php echo convert_html(get_source('SiteNavigator')) ?>
</div>
<div id="page_navigator">
 <?php echo convert_html(get_source('PageNavigator')) ?>
</div>

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
<a href="<?php echo "$script?plugin=referer&amp;page=$r_page" ?>">������󥯸�</a>
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
<h2>����</h2>
<form action="<?php echo $script ?>" method="post">
<div><input name="encode_hint" value="��" type="hidden" /></div>
<div>
<input name="plugin" value="lookup" type="hidden" />
<input name="refer" value="<?php echo $title ?>" type="hidden" />
<input name="page" size="20" value="" type="text" accesskey="s" title="serch box"/>
<input value="Go!" type="submit" accesskey="g"/><br/>
<input name="inter" value="����" type="radio" checked="checked" id="serch_site" /><label for="serch_site">��������</label>
<input name="inter" value="Google.jp" type="radio" accesskey="w" id="serch_web"/><label for="serch_web">Web</label>
</div>
</form></div>
<div id="rightbar2" class="side_bar">
<h2>�Խ����</h2>
<ul>
<?php if ($is_page) { ?>
	<li><a href="<?php echo $link_edit ?>"><img src="<?php echo IMAGE_DIR ?>edit.png" width="20" height="20" alt="�Խ�" title="�Խ�" />�Խ�</a></li>
<?php   if ((bool)ini_get('file_uploads')) { ?>
	<li><a href="<?php echo $link_upload ?>"><img src="<?php echo IMAGE_DIR ?>file.png" width="20" height="20" alt="ź��" title="ź��" />ź��</a></li>
<?php   } ?>
	<li><a href="<?php echo $link_diff ?>"><img src="<?php echo IMAGE_DIR ?>diff.png" width="20" height="20" alt="��ʬ" title="��ʬ" />��ʬ</a></li>
<?php } ?>
<?php if ($do_backup) { ?>
	<li><a href="<?php echo $link_backup ?>"><img src="<?php echo IMAGE_DIR ?>backup.png" width="20" height="20" alt="�Хå����å�" title="�Хå����å�" />�Хå����å�</a></li>
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
</body>
</html>