<?php
// $Id: tb.inc.php,v 1.19.23 2006/10/11 19:48:00 upk Exp $
/*
 * PukiWiki/TrackBack: TrackBack Ping receiver and viewer
 * (C) 2003,2005-2006 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * (C) 2003-2005 PukiWiki Developers Team
 * License: GPL
 *
 * plugin_tb_convert()          block plugin
 * plugin_tb_action()           action plugin
 * plugin_tb_inline()           inline plugin
 * plugin_tb_save($url, $tb_id) Save or update TrackBack Ping data
 * plugin_tb_return($rc, $msg)  Return TrackBack ping via HTTP/XML
 * plugin_tb_mode_rss($tb_id)   ?__mode=rss
 * plugin_tb_mode_view($tb_id)  ?__mode=view
 * plugin_tb_recent($line)
 */

define('PLUGIN_TB_OK',      0); 
define('PLUGIN_TB_ERROR',   1); 

function plugin_tb_convert()
{
	global $vars;

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('cmd','line');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($cmd)) $cmd = 'list';
	if (empty($line)) $line = 0;

	switch ( $cmd ) {
	case 'recent':
		return plugin_tb_recent($vars['page'],$line);
	// case 'list':
	default:
		return plugin_tb_mode_view_set($vars['page']);
	}
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
		$pages = auth::get_existpages(TRACKBACK_DIR, '.txt');
		if (! empty($pages)) {
			return array('msg'=>'Trackback list',
				'body'=>page_list($pages, 'read', FALSE));
		} else {
			return array('msg'=>'', 'body'=>'');
		}
	}
}

function plugin_tb_inline()
{
	global $vars, $trackback, $script;

	if (! $trackback) return '';

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('page');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}
	if (empty($page)) $page = $vars['page'];

	$tb_id = tb_get_id($page);

	return $script . '?tb_id=' . $tb_id;
}

// Save or update TrackBack Ping data
function plugin_tb_save($url, $tb_id)
{
	global $vars, $trackback, $use_spam_check;
	static $fields = array( /* UTIME, */ 'url', 'title', 'excerpt', 'blog_name');

	$die = '';
	if (! $trackback) $die .= 'TrackBack feature disabled. ';
	if ($url   == '') $die .= 'URL parameter is not set. ';
	if ($tb_id == '') $die .= 'TrackBack Ping ID is not set. ';
	if ($die != '') plugin_tb_return(PLUGIN_TB_ERROR, $die);

	if (! file_exists(TRACKBACK_DIR)) plugin_tb_return(PLUGIN_TB_ERROR, 'No such directory: TRACKBACK_DIR');
	if (! is_writable(TRACKBACK_DIR)) plugin_tb_return(PLUGIN_TB_ERROR, 'Permission denied: TRACKBACK_DIR');

	$page = tb_id2page($tb_id);
	if ($page === FALSE) plugin_tb_return(PLUGIN_TB_ERROR, 'TrackBack ID is invalid.');

	// URL validation (maybe worse of processing time limit)
	$result = http_request($url, 'HEAD');
	if ($result['rc'] !== 200) plugin_tb_return(PLUGIN_TB_ERROR, 'URL is fictitious.');

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

	// minimum checking from SPAM
	$matches = array();
	if (preg_match_all('/a\s+href=/i', $items['excerpt'], $matches) >= 1) {
		honeypot_write();
		plugin_tb_return(PLUGIN_TB_ERROR, 'Writing is prohibited.');
	}

	// Blocking SPAM
	if ($use_spam_check['trackback'] && SpamCheck($items['url'])) plugin_tb_return(1, 'Writing is prohibited.');

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

	plugin_tb_return(PLUGIN_TB_OK); // Return OK
}

// Show a response code of the ping via HTTP/XML (then exit)
function plugin_tb_return($rc, $msg = '')
{
	if ($rc == PLUGIN_TB_OK) {
		$rc = 0; // for PLUGIN_TB_OK
	} else {
		$rc = 1; // for PLUGIN_TB_ERROR
	}

	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="iso-8859-1"?>';
	echo '<response>';
	echo ' <error>' . $rc . '</error>';
	if ($rc) echo '<message>' . $msg . '</message>';
	echo '</response>';
	exit;
}

// Show pings for the page via RSS (?__mode=rss)
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

	// ToDo: response encoding must equal request encoding.(from trackback reference.)
	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo mb_convert_encoding($rc, 'UTF-8', SOURCE_ENCODING);
	exit;
}

// Show pings for the page via XHTML (?__mode=view)
function plugin_tb_mode_view($tb_id)
{
	global $script, $vars;

	$page = tb_id2page($tb_id);
	if ($page === FALSE) return FALSE;

	$vars['page'] = $page; // topicpath
	$retval = array();
// TrackBack list to aaaaa
// aaaa への TrackBack 一覧

	// $retval['msg'] = sprintf( _('TrackBack list to %s'), $page);
	$retval['msg'] = $page;
	$retval['body'] = plugin_tb_mode_view_set($page);
	return $retval;
}

function plugin_tb_mode_view_set($page)
{
	global $script, $vars;

	$tb_id = tb_get_id($page);

	$body = '<div><fieldset><legend>'._('TrackBack URL').'<legend>'.
		'<p>'.$script . '?tb_id=' . $tb_id.'</p>'.
		'</fieldset></div>'."\n";

	$_tb_header_Weblog  = _('Blog:');
	$_tb_header_Tracked = _('Date:');
	$_tb_date   = _('F j, Y, g:i A');

	$data = tb_get(tb_get_filename($page));

	// Sort: The first is the latest
	usort($data, create_function('$a,$b', 'return $b[0] - $a[0];'));

	foreach ($data as $x) {
		if (count($x) != 5) continue; // Ignore incorrect record

		list ($time, $url, $title, $excerpt, $blog_name) = $x;
		if ($title == '') $title = 'no title';

		$time = get_date($_tb_date, $time);

		$body .= '<div><fieldset>'.
			 '<legend><a class="ext" href="' . $url . '" rel="nofollow">' . $title . 
			 '<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\'' .
			 $url . '\', \'_blank\');" /></a></legend>' . "\n".

			 '<p>' . $excerpt . "</p>\n".

			 '<div style="text-align:right">' .
			 '<strong>'.$_tb_header_Tracked.'</strong>'.$time.'&nbsp;&nbsp;'.
			 '<strong>'.$_tb_header_Weblog.'</strong>'.$blog_name.
			 '</div>'."\n".

			 '</fieldset></div>'."\n";
	}

	$body .= '<div style="text-align:right">' .
		 '<a href="' . $script . '?plugin=tb&amp;__mode=view">' .
		 '<img src="'.IMAGE_URI.'plus/trackback.png" alt="" title="" />' .
		 'Trackback List' . 
		 '</a>'. "</div>\n";

	return $body;
}

function plugin_tb_recent($page,$line)
{
	$body = '';

	$tb_id = tb_get_id($page);
	$data = tb_get(tb_get_filename($page));
	$ctr = count($data);
	if ($ctr == 0) return '';

	if ($ctr > 1) {
		// Sort: The first is the latest
		usort($data, create_function('$a,$b', 'return $b[0] - $a[0];'));
	}

	$body .= '<h5>' . _("RECENT TRACKBACK") . "</h5>\n";
	$body .= "<div>\n<ul class=\"recent_list\">\n";
	$i = 0;
	foreach ($data as $x) {
		if (count($x) != 5) continue; // Ignore incorrect record

		list ($time, $url, $title, $excerpt, $blog_name) = $x;
		if ($title == '') $title = 'no title';

		$body .= '<li><a href="' . $url . '" title="' .
			$blog_name . ' ' . get_passage($time) .
			'" rel="nofollow">' . $title . '</a></li>'."\n";
		$i++;
		if ($line == 0) continue;
		if ($i >= $line) break;
	}

	if ($i == 0) return '';

	$body .= "</ul>\n</div>\n";

	return $body;
}

?>
