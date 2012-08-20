<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.skin.php,v 1.34.37 2008/01/18 23:59:00 upk Exp $
//
if (!defined('DATA_DIR')) { exit; }

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

global $body_menu, $body_side;
global $_LINK;
$rw = ! PKWK_READONLY;

?><!DOCTYPE html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<meta charset="<?php echo(CONTENT_CHARSET); ?>" />
<?php if (!$is_read): ?>
<meta name="robots" content="NOINDEX,NOFOLLOW" />
<?php endif; ?>
<?php if ($title == $defaultpage): ?>
<title><?php echo $page_title ?></title>
<?php elseif ($newtitle != '' && $is_read): ?>
<title><?php echo $newtitle.' - '.$page_title ?></title>
<?php else: ?>
<title><?php echo $title.' - '.$page_title ?></title>
<?php endif; ?>
<link rel="stylesheet" href="assets/css/jquery-ui-1.8.21.custom.css" type="text/css" charset="<?php echo $css_charset ?>" />
<link rel="stylesheet" href="assets/css/bootstrap.css" type="text/css" charset="<?php echo $css_charset ?>" />
<link rel="stylesheet" href="assets/css/bootstrap-responsive.css" type="text/css" charset="<?php echo $css_charset ?>" />
<link rel="stylesheet" href="assets/css/default.css" type="text/css" charset="<?php echo $css_charset ?>" />
<link rel="stylesheet" href="assets/css/print.css" type="text/css" charset="<?php echo $css_charset ?>" />
<link rel="alternate" href="<?php echo $_LINK['mixirss'] ?>" type="application/rss+xml" title="RSS" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
<?php echo $head_tag ?>
</head>
<body id="modern">
<div id="header" class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<a class="hidden-phone" href="<?php echo $modifierlink ?>"><img class="brand" id="logo" src="<?php echo IMAGE_URI; ?>pukiwiki.plus_logo.png" width="120" height="40" alt="[PukiWiki Plus!]" title="[PukiWiki Plus!]" /></a>
		<h1 class="brand" style="height:40px;line-height:40px;margin-left:0;color:white;"><?php echo(($newtitle!='' && $is_read)?$newtitle:$page) ?></h1>
		<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<div class="nav-collapse">
			<div class="row-fluid">
				<div class="span5d">
					<ul class="nav nav-list">
						<li class="nav-header">サイト</li>
						<li><a href="<?php echo $modifierlink ?>"><i class="icon-white icon-home"></i>ホーム</a></li>
<?php if ($rw): ?>
						<li><a href="<?php echo $_LINK['new'] ?>"><i class="icon-white icon-asterisk"></i>新規</a></li>
<?php endif; ?>
					</ul>
				</div>
				<div class="span5d">
					<ul class="nav nav-list">
						<li class="nav-header">ページ</li>
						<li><a href="<?php echo $_LINK['edit'] ?>"><i class="icon-white icon-edit"></i>編集</a></li>
<?php if ($is_read && $function_freeze){ ?>
<?php if (! $is_freeze){ ?>
						<li><a href="<?php echo $_LINK['freeze'] ?>"><i class="icon-white icon-lock"></i>凍結</a></li>
<?php }else{ ?>
						<li><a href="<?php echo $_LINK['unfreeze'] ?>"><i class="icon-white icon-wrench"></i>解凍</a></li>
<?php } ?>
<?php } ?>
						<li><a href="<?php echo $_LINK['diff'] ?>"><i class="icon-white icon-th-list"></i>差分</a></li>
						<li class="nav-header">ページ操作</li>
						<li><a href="<?php echo $_LINK['copy'] ?>"><i class="icon-white icon-tags"></i>コピー</a></li>
						<li><a href="<?php echo $_LINK['rename'] ?>"><i class="icon-white icon-tag"></i>名前変更</a></li>
					</ul>
				</div>
				<div class="span5d">
					<ul class="nav nav-list">
						<li class="nav-header">一覧</li>
						<li><a href="<?php echo $_LINK['list'] ?>"><i class="icon-white icon-list"></i>一覧</a></li>
						<li><a href="<?php echo $_LINK['search'] ?>"><i class="icon-white icon-search"></i>検索</a></li>
						<li><a href="<?php echo $_LINK['recent'] ?>"><i class="icon-white icon-time"></i>最終更新</a></li>
					</ul>
				</div>
				<div class="span5d">
					<ul class="nav nav-list">
						<li class="nav-header">ツール</li>
<?php if ($do_backup) { ?>
						<li><a href="<?php echo $_LINK['backup'] ?>"><i class="icon-white icon-folder-open"></i>バックアップ</a></li>
<?php } ?>
					</ul>
				</div>
				<div class="span5d">
					<ul class="nav nav-list">
						<li class="nav-header">ヘルプ</li>
						<li><a href="<?php echo $_LINK['help'] ?>"><i class="icon-white icon-question-sign"></i>ヘルプ</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row-fluid">
		<?php if (!empty($body_menu) && !empty($side_menu)): ?>
			<div class="span2"><div id="menubar" class="hidden-phone"><?php echo $body_menu; ?></div></div>
			<div class="span8">
				<?php if ($is_page and exist_plugin_convert('topicpath')) { echo do_plugin_convert('topicpath'); } ?>
				<div id="body"><?php echo $body ?></div>
			</div>
			<div class="span2"><div id="sidebar" class="hidden-phone"><?php echo $body_side; ?></div></div>
		<?php elseif (!empty($body_menu)): ?>
			<div class="span3"><div id="menubar" class="hidden-phone"><?php echo $body_menu; ?></div></div>
			<div class="span9">
				<?php if ($is_page and exist_plugin_convert('topicpath')) { echo do_plugin_convert('topicpath'); } ?>
				<div id="body"><?php echo $body ?></div>
			</div>
		<?php elseif (!empty($body_side)): ?>
			<div class="span9">
				<?php if ($is_page and exist_plugin_convert('topicpath')) { echo do_plugin_convert('topicpath'); } ?>
				<div id="body"><?php echo $body ?></div>
			</div>
			<div class="span3"><div id="sidebar" class="hidden-phone"><?php echo $body_side; ?></div></div>
		<?php else: ?>
			<div class="span12">
				<?php if ($is_page and exist_plugin_convert('topicpath')) { echo do_plugin_convert('topicpath'); } ?>
				<div id="body"><?php echo $body ?></div>
			</div>
		<?php endif; ?>
	</div>

	<div class="row-fluid">
		<?php if ($notes): ?>
		<div id="note">
			<?php echo $notes ?>
		</div>
		<?php endif; ?>

		<?php if ($attaches): ?>
		<div id="attach">
			<?php echo $hr ?>
			<?php echo $attaches ?>
		</div>
		<?php endif; ?>

		<?php echo $hr ?>
		<?php if (exist_plugin('toolbar')): ?>
			<?php echo do_plugin_convert('toolbar','reload,|,new,newsub,edit,freeze,diff,upload,copy,rename,|,top,list,search,recent,backup,refer,|,help,|,mixirss'); ?>
		<?php endif; ?>

		<?php if ($lastmodified): ?>
		<div id="lastmodified" class="row-fluid">
			<p class="pull-right">
				Last-modified: <?php echo $lastmodified ?>
			</p>
		</div>
		<?php endif; ?>

		<?php if ($related): ?>
		<div id="related" class="row-fluid">
			Link: <?php echo $related ?>
		</div>
		<?php endif; ?>
	</div>
</div>

<div id="footer" class="footer">
	<footer class="row-fluid">
		<div class="span12">
			<p class="pull-left" style="margin-right:20px;">
				<?php if (exist_plugin_inline('qrcode')) {
					echo plugin_qrcode_inline(1, get_script_absuri().'?'.$a_page);
				} ?>
			</p>
			<div id="sigunature">
				Founded by <a href="<?php echo $modifierlink ?>"><?php echo $modifier ?></a>.<br />
				Powered by PukiWiki Plus! <?php echo S_VERSION ?>. <?php echo $taketime ?> sec.
			</div>
		</div>
	</footer>
</div>

<script type="text/javascript" src="assets/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui-i18n.js"></script>
<script type="text/javascript" src="assets/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.inputcount.js"></script>
<script type="text/javascript" src="assets/js/jquery.pjax.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<?php echo $foot_tag ?>
<script type="text/javascript" src="assets/js/default.js"></script>
</body>
</html>
