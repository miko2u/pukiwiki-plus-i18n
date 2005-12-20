<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: convert_cache.php,v 1.12.1 2005/12/20 13:41:03 miko Exp $
//
// Copyright (C)
//   2005      Customized/Patched by Miko.Hoshina
//   2005      Originally written by ryu1k<ryu1_ATMARK_cafe_DOT_email_DOT_ne_DOT_jp>
// License: GPL v2 or (at your option) any later version

function bodycache_default_process($page, $source)
{
	global $head_tags, $foot_explain;

    $body = convert_html($source);

	$fp = fopen(get_cachename($page, '.head'), 'ab');
	@flock($fp, LOCK_EX);
	$last = ignore_user_abort(1);
	ftruncate($fp, 0);
	fwrite($fp, join("\x08", $head_tags));
	fflush($fp);
	ignore_user_abort($last);
	@flock($fp, LOCK_UN);
	fclose($fp);
	if (connection_status()) exit;

	$fp = fopen(get_cachename($page, '.note'), 'ab');
	@flock($fp, LOCK_EX);
	$last = ignore_user_abort(1);
	ftruncate($fp, 0);
	fwrite($fp, join("\x08", $foot_explain));
	fflush($fp);
	ignore_user_abort($last);
	@flock($fp, LOCK_UN);
	fclose($fp);
	if (connection_status()) exit;

	$fp = fopen(get_cachename($page, '.body'), 'ab');
	@flock($fp, LOCK_EX);
	$last = ignore_user_abort(1);
	ftruncate($fp, 0);
	fwrite($fp, $body);
	fflush($fp);
	ignore_user_abort($last);
	@flock($fp, LOCK_UN);
	fclose($fp);
	if (connection_status()) exit;

    return $body;
}

// Is cached page?
function is_cache($page, $clearcache = FALSE)
{
	if ($clearcache) clearstatcache();
	return file_exists(get_cachename($page));
}

// Get last-modified filetime of the cache
function get_cachetime($page)
{
	return is_cache($page) ? filemtime(get_cachename($page)) - LOCALZONE : 0;
}

// Get physical file name of the cache
function get_cachename($page, $ext = '.body')
{
	return CACHE_DIR . encode($page) . $ext;
}

// 
function touch_sitecache()
{
	$sitefile = CACHE_DIR . 'site.body';
	pkwk_touch_file($sitefile);
}

// Get last-modified sitetime of the cache
function get_sitecache($page)
{
	$sitefile = CACHE_DIR . 'site.body';
	return file_exists($sitefile) ? filemtime($sitefile) - LOCALZONE : 0;
}

// Get cache file
function get_cache($page, $source)
{
	if ( ! is_page($page) ) { // page not exists.
		return convert_html($source);
	}
	if ( ! is_cache($page) ) { // cache not exists.
		return bodycache_default_process($page, $source);
	}
	if ( get_sitecache($page) > get_cachetime($page) ) { // cache is obsolete.
        return bodycache_default_process($page, $source);
    }

	global $head_tags, $foot_explain;

	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		$head_tags = split("\x08", file_get_contents(get_cachename($page, '.head')));
		$foot_explain = split("\x08", file_get_contents(get_cachename($page, '.note')));
		$body = file_get_contents(get_cachename($page, '.body'));
	} else {
		$body = '';
		$fp = @fopen(get_cachename($page, '.head'), 'rb');
		flock($fp, LOCK_SH);
		do {
			$tmp = fread($fp, 8192);
			$body .= $tmp;
		} while (strlen($tmp) != 0);
		flock($fp, LOCK_UN);
		@fclose($fp);
		$head_tags = split("\x08", $body);

		$body = '';
		$fp = @fopen(get_cachename($page, '.note'), 'rb');
		flock($fp, LOCK_SH);
		do {
			$tmp = fread($fp, 8192);
			$body .= $tmp;
		} while (strlen($tmp) != 0);
		flock($fp, LOCK_UN);
		@fclose($fp);
		$foot_explain = split("\x08", $body);

		$body = '';
		$fp = @fopen(get_cachename($page, '.body'), 'rb');
		flock($fp, LOCK_SH);
		do {
			$tmp = fread($fp, 8192);
			$body .= $tmp;
		} while (strlen($tmp) != 0);
		flock($fp, LOCK_UN);
		@fclose($fp);
	}

	return $body;
}

// Convert HTML with cache
function convert_html_cache($page, $source)
{
    $body = get_cache($page, $source);
    return $body;
}
