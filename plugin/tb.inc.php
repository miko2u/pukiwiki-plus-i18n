<?php
// $Id: tb.inc.php,v 1.19.4 2006/02/02 01:30:00 upk Exp $
/*
 * PukiWiki/TrackBack: TrackBack Ping receiver and viewer
 * (C) 2003-2004 PukiWiki Developers Team
 * (C) 2003,2005-2006 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * License: GPL
 *
 * plugin_tb_action()    action
 * plugin_tb_save($url, $tb_id) Save or update TrackBack Ping data
 * plugin_tb_return($rc, $msg)  Return TrackBack ping via HTTP/XML
 * plugin_tb_mode_rss($tb_id)   ?__mode=rss
 * plugin_tb_mode_view($tb_id)  ?__mode=view
 */


function plugin_tb_convert()
{
	global $vars;
	$tb_id = tb_get_id($vars['page']);
	return plugin_tb_mode_view_set($tb_id, $vars['page']);
}

function plugin_tb_action()
{
	global $vars, $trackback;

	if (isset($vars['url'])) {
		// Receive and save a TrackBack Ping (both GET and POST)
		$url   = $vars['url'];
		$tb_id = isset($vars['tb_id']) ? $vars['tb_id'] : '';
		plugin_tb_save($url, $tb_id); // Send a response (and exit)

	} else {
		if ($trackback && isset($vars['__mode']) && isset($vars['tb_id'])) {
			// Show TrackBacks received (and exit)
			switch ($vars['__mode']) {
			case 'rss' : plugin_tb_mode_rss($vars['tb_id']);  break;
			// case 'view': plugin_tb_mode_view($vars['tb_id']); break;
			case 'view': return plugin_tb_mode_view($vars['tb_id']);
			}
		}

		// Show List of pages that TrackBacks reached
		$pages = get_existpages(TRACKBACK_DIR, '.txt');
		if (! empty($pages)) {
			return array('msg'=>'trackback list',
				'body'=>page_list($pages, 'read', FALSE));
		} else {
			return array('msg'=>'', 'body'=>'');
		}
	}
}

// Save or update TrackBack Ping data
function plugin_tb_save($url, $tb_id)
{
	global $vars, $trackback;
	static $fields = array( /* UTIME, */ 'url', 'title', 'excerpt', 'blog_name');

	$die = '';
	if (! $trackback) $die .= 'TrackBack feature disabled. ';
	if ($url   == '') $die .= 'URL parameter is not set. ';
	if ($tb_id == '') $die .= 'TrackBack Ping ID is not set. ';
	if ($die != '') plugin_tb_return(1, $die);

	if (! file_exists(TRACKBACK_DIR)) plugin_tb_return(1, 'No such directory: TRACKBACK_DIR');
	if (! is_writable(TRACKBACK_DIR)) plugin_tb_return(1, 'Permission denied: TRACKBACK_DIR');

	$page = tb_id2page($tb_id);
	if ($page === FALSE) plugin_tb_return(1, 'TrackBack ID is invalid.');

	// URL validation (maybe worse of processing time limit)
	$result = http_request($url, 'HEAD');
	if ($result['rc'] !== 200) plugin_tb_return(1, 'URL is fictitious.');

	// Update TrackBack Ping data
	$filename = tb_get_filename($page);
	$data     = tb_get($filename);

	$items = array(UTIME);
	foreach ($fields as $key) {
		$value = isset($vars[$key]) ? $vars[$key] : '';
		if (preg_match('/[,"' . "\n\r" . ']/', $value))
			$value = '"' . str_replace('"', '""', $value) . '"';
		$items[$key] = $value;
	}
	$data[rawurldecode($items['url'])] = $items;

	$fp = fopen($filename, 'w');
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	foreach ($data as $line) {
		$line = preg_replace('/[\r\n]/s', '', $line); // One line, one ping
		fwrite($fp, join(',', $line) . "\n");
	}
	flock($fp, LOCK_UN);
	fclose($fp);

	plugin_tb_return(0); // Return OK
}

// Return TrackBack ping via HTTP/XML
function plugin_tb_return($rc, $msg = '')
{
	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="iso-8859-1"?>';
	echo '<response>';
	echo ' <error>' . $rc . '</error>';
	if ($rc !== 0) echo '<message>' . $msg . '</message>';
	echo '</response>';
	exit;
}

// ?__mode=rss
function plugin_tb_mode_rss($tb_id)
{
	global $script, $vars, $entity_pattern, $language;

	$page = tb_id2page($tb_id);
	if ($page === FALSE) return FALSE;

	$items = '';
	foreach (tb_get(tb_get_filename($page)) as $arr) {
		// _utime_, title, excerpt, _blog_name_
		array_shift($arr); // Cut utime
		list ($url, $title, $excerpt) = array_map(
			create_function('$a', 'return htmlspecialchars($a);'), $arr);
		$items .= <<<EOD

   <item>
    <title>$title</title>
    <link>$url</link>
    <description>$excerpt</description>
   </item>
EOD;
	}

	$title = htmlspecialchars($page);
	$link  = $script . '?' . rawurlencode($page);
	$vars['page'] = $page;
	$excerpt = strip_htmltag(convert_html(get_source($page)));
	$excerpt = preg_replace("/&$entity_pattern;/", '', $excerpt);
	$excerpt = mb_strimwidth(preg_replace("/[\r\n]/", ' ', $excerpt), 0, 255, '...');
	$lang    = $language;

	$rc = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<response>
 <error>0</error>
 <rss version="0.91">
  <channel>
   <title>$title</title>
   <link>$link</link>
   <description>$excerpt</description>
   <language>$lang</language>$items
  </channel>
 </rss>
</response>
EOD;

	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo mb_convert_encoding($rc, 'UTF-8', SOURCE_ENCODING);
	exit;
}

// ?__mode=view
function plugin_tb_mode_view($tb_id)
{
	global $script, $vars;

	$page = tb_id2page($tb_id);
	if ($page === FALSE) return FALSE;

	$vars['page'] = $page; // topicpath
	$retval = array();
	$retval['msg'] = sprintf( _('TrackBack: Discussion on TrackBack in %s'), $page);
	$retval['body'] = plugin_tb_mode_view_set($tb_id, $page);
	return $retval;
}

function plugin_tb_mode_view_set($tb_id, $page)
{
	global $script, $vars;

	$body  = '<h3>' . _('TrackBack URL for this entry:') . "</h3>\n";
	$body .= '<p>' . $script . '?tb_id=' . $tb_id . "</p>\n";
	$body .= '<h3>' . _('Continuing the discussion...') . "</h3>\n";
	
	$_tb_header_Excerpt = _('Summary:');
	$_tb_header_Weblog  = _('Weblog:');
	$_tb_header_Tracked = _('Tracked:');
	$_tb_date   = _('F j, Y, g:i A');

	$data = tb_get(tb_get_filename($page));

	// Sort: The first is the latest
	usort($data, create_function('$a,$b', 'return $b[0] - $a[0];'));

	foreach ($data as $x) {
		if (count($x) != 5) continue; // Ignore incorrect record

		list ($time, $url, $title, $excerpt, $blog_name) = $x;
		if ($title == '') $title = 'no title';

		$time = get_date($_tb_date, $time);

		$body .= '<h4><a class="ext" href="' . $url . '" rel="nofollow">' . $title . 
			 '<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\'' .
			 $url . '\', \'_blank\');" /></a></h4>' . "\n";

		$body .= '<p>' . $excerpt . "</p>\n";

		$body .= '<div style="text-align:right">' .
			 $_tb_header_Tracked . $time . ' ' .
			 $_tb_header_Weblog . $blog_name . "</div>\n";

	}

	$body .= '<div style="text-align:right">' .
		 '<a href="' . $script . '?plugin=tb&amp;__mode=view">' . 'Trackback List' . 
		 '<img src="'.IMAGE_URI.'plus/trackback.png" alt="" title="" />' .
		 '</a>'. "</div>\n";

	return $body;
}

?>
