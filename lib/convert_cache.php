<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: convert_cache.php,v 1.11.1 2005/10/04 13:41:03 miko Exp $
//
// Copyright (C)
//   2005      Customized/Patched by Miko.Hoshina
//   2005      Originally written by ryu1k<ryu1_ATMARK_cafe_DOT_email_DOT_ne_DOT_jp>
// License: GPL v2 or (at your option) any later version

function bodycache_default_process($page, $source)
{
    $body = convert_html($source);
    $fb = fopen(get_cachename($page), 'wb');
    fwrite($fb, $body);
    fclose($fb);
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
function get_cachename($page)
{
	return CACHE_DIR . encode($page) . '.body';
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
	if ( get_site_updatetime($page) > get_cachetime($page) ) { // cache is obsolete.
        return bodycache_default_process($page, $source);
    }

	// return file_get_contents(get_cachename($page));
    $fp = fopen(get_cachename($page), 'rb');
    do {
        $tmp = fread($fp, 8192);
        $body .= $tmp;
    } while (strlen($tmp) != 0);
    fclose($fp);

    return $body;
}

// Convert HTML with cache
function convert_html_cache($page, $source)
{
    $body = get_cache($page, $source);
    return $body;
}
