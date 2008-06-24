<?php
/*
 * $Id$
 * 
 * License:  GNU General Public License
 *
 * Copyright (c) 2005 in3c.org
 * Portions Copyright (c) 2002 Y.MASUI
 *   http://masui.net/pukiwiki/ masui@masui.net
 *
 * MODIFICATION BY:
 * (C) 2006,2008 PukiWiki Plus! Developers Team
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 * 
 */

// require_once('cache.cls.php');

// Keywordモード時に使う正規表現で設定（両端の「/」は不用）
define('BLIKI_MORE', '^#blikimore');
define('BLIKI_FOOTER', '#blikifooter');

// 何ページ読みこむか？
if (!defined('BLIKI_DEFAULT_PAGE_NUM')) {
	define('BLIKI_DEFAULT_PAGE_NUM', '10');
}
// キャッシュを使うか？
if (!defined('BLIKI_CACHE_MODE')) {
	define('BLIKI_CACHE_MODE', '1');
}
// テンプレート
if (!defined('BLIKI_TEMPLATE')) {
	define('BLIKI_TEMPLATE', '<div>%s</div>');
}
// 各エントリー用テンプレート(wiki記法) 置換用引数 => (タイトル, 日付, 本文, もっとよむ, blikifooter)
if (!defined('BLIKI_PAGE_WIKI_TEMPLATE')) {
	define('BLIKI_PAGE_WIKI_TEMPLATE', "*%s\nRIGHT:%s %s\n\n%s\n\n%s\n\n%s\n");
}
// 各エントリー用テンプレート(HTML) BLIKI_PAGE_WIKI_TEMPLATEが収容される
if (!defined('BLIKI_PAGE_TEMPLATE')) {
	define('BLIKI_PAGE_TEMPLATE', "<div class='blikiEntry'>%s</div>");
}
// 子ディレクトリのドキュメントもインクルードするか
if (!defined('BLIKI_CHILD')) {
	define('BLIKI_CHILD', TRUE);
}
// blikifooterがないページをインクルードするか
if (!defined('BLIKI_FOOTER_REQUIRED')) {
	define('BLIKI_FOOTER_REQUIRED', TRUE);
}

function plugin_bliki_init()
{
	global $_bliki_msg;

	$msg = array(
		'_bliki_msg' => array(
			'msg_more'	=> _('&size(12){->[[The continuation of %s is read.:%s]]};'),	// 「もっと読む」リンクの文言
			'msg_update'	=> _('UPDATE'),
		)
	);
	set_plugin_messages($msg);
}

/**
 * 書式
 *     #bliki(pages,WikiName,child-page-control)
 * 
 * 種別 
 *     ブロック型プラグイン
 *
 * 概要
 *      blogfooterプラグインを含むページを更新順でincludeします。
 *      inlcludeされるページにblogmoreプラグインがあると、その部分までしか
 *      inlcludeされません。
 *      Olorinさんが作成されたshowcaseプラグインを元にしています。
 *      Olorinさんありがとうございます。
 * 
 * 使用例
 *      #bliki(15,hoge,1)  → hoge/?のページを更新順に15件表示
 *      #bliki(15,hoge,0)  → hoge/?のページを更新順に15件表示(子ページであるhoge/helloは含むが孫ページであるhoge/hello/worldは含まない。)
 *
 * @author Yuki SHIDA <shida@in3c.org>
 * @author Y.MASUI
 * @copyright Copyright &copy; in3c.org
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Revision$
 * 
 */
function plugin_bliki_convert()
{
    global $date_format, $_bliki_msg;
    
    list($child, $cache_name, $recent_prefix, $page_num) 
        = bliki_process_args(func_get_args());

    $pages = bliki_get_pages($recent_prefix, $child, $cache_name);
    if (! $pages) return '';

    $entries = bliki_get_cache($cache_name);
    if (! is_null($entries) && BLIKI_CACHE_MODE == 1) {
        return bliki_convert_html($entries, $page_num);
    } else {
        $entries = array();
    }

    for ($cnt = 0; $cnt < sizeof($pages) && $cnt < $page_num; ++$cnt) {
        $parse_result = bliki_parse_page($pages[$cnt]['page']);
        if (! $parse_result) continue;
        $entries[] = 
            array('page'  => $pages[$cnt]['page'], 
                  'entry' => sprintf(BLIKI_PAGE_WIKI_TEMPLATE,
                                     bliki_get_page_title($pages[$cnt]['page'],$pages[$cnt]['time']),
                                     $_bliki_msg['msg_update'],
                                     get_date($date_format, $pages[$cnt]['time']),
                                     $parse_result[0],
                                     $parse_result[1],
                                     $parse_result[2]));
    }

    bliki_write_cache($cache_name, $entries);
    return bliki_convert_html($entries, $page_num);
}


function plugin_bliki_action()
{
    global $post, $vars;
    $cache = isset($vars['cache']) ? $vars['cache'] : NULL;
    
    // this delete tracker caches. 
    if( $cache == 'DELALL' ) {
        if (! bliki_delete_caches('(.*)(.blc)$')) {
            die_message( CACHE_DIR . ' is not found or not readable.');
        }
            
        return array('result' => FALSE,
                     'msg'    => 'bliki caches are cleared.',
                     'body'   => 'bliki caches are cleared.');
    }
}


function bliki_process_args($args)
{
    global $vars, $defaultpage;

    $page_num      = BLIKI_DEFAULT_PAGE_NUM;
    $child         = BLIKI_CHILD;
    $cache_name    = $vars['page'];

    if (isset($args[0]) && ! is_null($args[0]) && is_numeric($args[0])) {
        $page_num = $args[0];
    }
    if (isset($args[1]) && ! is_null($args[1]) && $args[1] != '') {
        $cache_name = rtrim($args[1]);
    }
    if (isset($args[2]) && ! is_null($args[2]) && ($args[2] == '0') || ($args[2] == '1')) {
        $child = ($args[2] == '1') ? TRUE : FALSE;
    }

    $recent_prefix = get_fullname( strip_bracket($cache_name), $vars['page']) . '/';

    if ($recent_prefix == $defaultpage . '/') {
        $recent_prefix = '';
    }

    return array($child, $cache_name, $recent_prefix, $page_num);
}


function bliki_get_pages($recent_prefix, $child, $cache_name)
{

    if (!file_exists(CACHE_DIR . 'recent.dat')) {
        return FALSE;
    }

    $lines = file(CACHE_DIR . 'recent.dat');
    $pages = array();

    foreach ($lines as $line) {
        list($time, $page) = explode("\t",rtrim($line));

        if (($recent_prefix != '') && (strpos($page, $recent_prefix) !== 0)) continue;
        if ( (! $child) && (strpos($page , '/' , strlen($recent_prefix)) !== FALSE) ) continue;
        if (! check_readable($page, FALSE, FALSE)) continue;

        array_push($pages, array('time' => $time,
                                 'page' => $page));

        bliki_check_caches($cache_name, $time);
    }

    return $pages;
}


function bliki_check_caches($cache_name, $time)
{
    $cache_file = CACHE_DIR . encode($cache_name) .'.blc';

    if (file_exists($cache_file)) {
        // $cache_time = filemtime($cache_file); - LOCALZONE;
        $cache_time = filemtime($cache_file);
        if ($time > $cache_time) @unlink($cache_file);
    }

    return TRUE;
}


function bliki_get_cache($cache_name)
{
    $cache_file = CACHE_DIR . encode($cache_name) .'.blc';

    if (file_exists($cache_file)){
        return unserialize(join('', file($cache_file)));
    } else {
        return NULL;
    }
}

function bliki_write_cache($cache_name, $entries)
{
    $cache_file = CACHE_DIR . encode($cache_name) .'.blc';

    if (BLIKI_CACHE_MODE) {
        $fp = fopen($cache_file, 'w')
            or die_message('cannot write page file or diff file or other' . 
                           htmlspecialchars($cache_file) . 
                           '<br />maybe permission is not writable or filename is too long');
        @flock($fp,LOCK_EX);
        fwrite($fp, serialize($entries));
        @flock($fp,LOCK_UN);
        fclose($fp);
    }

    return TRUE;
}
    

function bliki_delete_caches($del_pattern)
{
    $dir = CACHE_DIR;

    if(! $dp = @opendir($dir) ) return FALSE;

    while($file = readdir($dp)) {
        if(preg_match("/$del_pattern/",$file)) {
            @unlink($dir . $file);
        }
    }
    closedir($dp);
    return TRUE;
}


function bliki_get_page_title($page,$time)
{
    return '[[' . htmlspecialchars($page) . ']] &passage(' . $time . ',1);';
}

/**
 * BLIKI_FOOTER_REQUIRED が TRUE で blikifooter が見つからない場合は失敗
 * 
 * 
 * @return 成功 = ARRAY($body, $more, $footer), 失敗 = FALSE
 */
function bliki_parse_page($page)
{
    $lines = get_source($page);

    $body = $more = $footer = '';

    foreach($lines as $row){
        //exclude #freeze & #norelated from the loop, avoid an infinite loop

        if(preg_match('/' . BLIKI_MORE . '/', $row)){
            $more = bliki_get_more($page);
            continue;
        }
        elseif(preg_match('/' . BLIKI_FOOTER . '/', $row)){
            $footer = $row;
            continue;
        }
        elseif(preg_match("/^(#freeze|#norelated|#bliki)$/m",$row)){
            continue;
        }
        elseif (empty($more)) {
            $body .= $row;
        }
    }    

    if (empty($footer) && BLIKI_FOOTER_REQUIRED) {
        return FALSE;
    } else {
        return array($body, $more, $footer);
    }
}


function bliki_convert_html($entries, $page_num)
{
    global $vars;

    $contents = '';
    for ($cnt = 0; $cnt < sizeof($entries) && $cnt < $page_num; ++$cnt) {
        $page_back    = $vars['page'];
        $vars['page'] = $entries[$cnt]['page'];
        $contents    .= sprintf(BLIKI_PAGE_TEMPLATE,
                                convert_html($entries[$cnt]['entry']));
        $vars['page'] = $page_back;
    }

    return sprintf(BLIKI_TEMPLATE, $contents);
}


function bliki_get_more($page)
{
	global $_bliki_msg;

	return sprintf($_bliki_msg['msg_more'],
		htmlspecialchars($page),
		get_page_uri($page,'','','more'));
}

?>
