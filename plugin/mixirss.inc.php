<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: mixirss.inc.php,v 1.14.14 2008/01/16 00:41:00 upk Exp $
//
// Publishing RSS feed of RecentChanges
// Usage: mixirss.inc.php?ver=[0.91|1.0(default)|2.0]

// View Description Letters
define('MIXIRSS_DESCRIPTION_LENGTH', 256);
define('MIXIRSS_LANG', 'ja_JP');

// upk 2006-03-22
// define('MIXIRSS_IGNORE_REGEX', 'Navigation|RecentDeleted|MenuBar|SideBar');

// XSLT extra parameters
//define('LOGO', 'http://pukiwiki.cafelounge.net/image/plus.gif');
//define('FOAF', 'http://pukiwiki.cafelounge.net/elements/foaf.rdf');

function plugin_mixirss_action()
{
	global $vars, $get, $post, $rss_max, $rss_description, $page_title, $whatsnew, $trackback;
	global $modifier;
	global $exclude_plugin;

	$version = isset($vars['ver']) ? $vars['ver'] : '';
	switch($version){
	case '':  $version = '1.0';  break; // mixi Default
	case '1': $version = '1.0';  break;
	case '2': $version = '2.0';  break;
	case '0.91': /* FALLTHROUGH */
	case '1.0' : /* FALLTHROUGH */
	case '2.0' : break;
	default: die('Invalid RSS version!!');
	}

	$recent = CACHE_DIR . 'recent.dat';
	if (! file_exists($recent)) die('recent.dat is not found');
	$time_recent = filemtime($recent);

	$rsscache = CACHE_DIR . 'rsscache' . $version . '.dat';
	if (file_exists($rsscache)) {
		$time_rsscache = filemtime($rsscache);
	} else {
		$time_rsscache = 0;
	}

	// if caching rss file, return cache.
	if ($time_recent <= $time_rsscache) {
		pkwk_common_headers();
		header('Content-type: application/xml');
		print '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";
		print implode('', file($rsscache));
		exit;
	}

	// Official Main routine ...
	$page_title_utf8 = mb_convert_encoding($page_title, 'UTF-8', SOURCE_ENCODING);
	$rss_description_utf8 = mb_convert_encoding(htmlspecialchars($rss_description), 'UTF-8', SOURCE_ENCODING);

	// Disable plugin
	$exclude_plugin[] = 'include';

	$self  = get_script_absuri();
	change_uri('',1); // Force absoluteURI.

	// Creating <item>
	$items = $rdf_li = '';
	$source = file($recent);
	foreach (array_splice($source, 0, $rss_max) as $line) {
		list($time, $page) = explode("\t", rtrim($line));
		$r_page = rawurlencode($page);
		$url    = get_page_uri($page);
		$title  = mb_convert_encoding($page, 'UTF-8', SOURCE_ENCODING);

		switch ($version) {
		case '0.91': /* FALLTHROUGH */
		case '2.0':
			$date = get_date('D, d M Y H:i:s T', $time);
			$date = ($version == '0.91') ?
				' <description>' . $date . '</description>' :
				' <pubDate>' . $date . '</pubDate>';
			$items .= <<<EOD
<item>
 <title>$title</title>
 <link>$url</link>
$date
</item>

EOD;
			break;

		case '1.0':
			// Add <item> into <items>
//			$rdf_li .= '    <rdf:li rdf:resource="' . $url . '" />' . "\n";

			$date = substr_replace(get_date('Y-m-d\TH:i:sO', $time), ':', -2, 0);
			$trackback_ping = '';
			if ($trackback) {
				$tb_id = md5($r_page);
				$trackback_ping = ' <trackback:ping rdf:resource="' . $self . '?tb_id=' . $tb_id . '"/>';
			}
			if (plugin_mixirss_isValidDate(substr($page,-10)) && check_readable($page,false,false)) {
				// for Calendar/MiniCalendar
				$get['page'] = $post['page'] = $vars['page'] = $page;
				$source = get_source($page);
				$rdf_hx = '';
				$rdf_lx = '';
				$itemhx = '';
				$itemlx = '';
				while(!empty($source)) {
					$line = array_shift($source);
					if (preg_match('/^(\*{1,3})(.*)\[#([A-Za-z][\w-]+)\](.*)$/m', $line, $matches)) {
						$anchortitle = strip_htmltag(convert_html($matches[2]));
						$anchortitle = preg_replace("/[\r\n]/",' ',$anchortitle);
						$anchortitle = '<![CDATA[' . mb_convert_encoding($anchortitle, 'UTF-8', SOURCE_ENCODING) . '(' . $title . ')' . ']]>';
						$sharp = '#';
						$rdf_hx .= '    <rdf:li rdf:resource="' . $url . $sharp . $matches[3] . '" />' . "\n";
						$itemhx .= <<<EOD
<item rdf:about="$url{$sharp}{$matches[3]}">
 <title>{$anchortitle}</title>
 <link>$url{$sharp}{$matches[3]}</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$url{$sharp}{$matches[3]}</dc:identifier>
$trackback_ping
</item>

EOD;
					} else if (preg_match('/^(\-{1,3})(.*)$/m', $line, $matches)) {
						$anchortitle = strip_htmltag(convert_html($matches[2]));
						$anchortitle = preg_replace("/[\r\n]/",' ',$anchortitle);
						$anchortitle = '<![CDATA[' . mb_convert_encoding($anchortitle, 'UTF-8', SOURCE_ENCODING) . '(' . $title . ')' . ']]>';
						$sharp = '#';
						$rdf_lx .= '    <rdf:li rdf:resource="' . $url . '" />' . "\n";
						$itemlx .= <<<EOD
<item rdf:about="$url">
 <title>{$anchortitle}</title>
 <link>$url</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$url</dc:identifier>
$trackback_ping
</item>

EOD;
					}
				}
				if ($itemhx != '') {
					$rdf_li .= $rdf_hx;
					$items .= $itemhx;
				} else if ($itemlx != '') {
					$rdf_li .= $rdf_lx;
					$items .= $itemlx;
				} else {
					// default
					$rdf_li .= '    <rdf:li rdf:resource="' . $url . '" />' . "\n";
					$items .= <<<EOD
<item rdf:about="$url">
 <title>$title</title>
 <link>$url</link>
$description
 <dc:date>$date</dc:date>
 <dc:identifier>$url</dc:identifier>
$trackback_ping
</item>

EOD;
				}
			// upk 2006-03-22
			// } else if (check_readable($page,false,false) && !ereg(MIXIRSS_IGNORE_REGEX, $page)) {
			} else if (check_readable($page,false,false) && !is_ignore_page($page)) {
				$get['page'] = $post['page'] = $vars['page'] = $page;
//miko added
				$description = strip_htmltag(convert_html(get_source($page)));
				$description = mb_strimwidth(preg_replace("/[\r\n]/",' ',$description),0,MIXIRSS_DESCRIPTION_LENGTH,'...');
				$description = ' <description><![CDATA[' . mb_convert_encoding($description,'UTF-8',SOURCE_ENCODING) . ']]></description>';
//miko added
				$rdf_li .= '    <rdf:li rdf:resource="' . $url . '" />' . "\n";
				global $newtitle, $newbase;
				if (isset($newbase) && $newbase != '') {
					$anchortitle = $newtitle . ' (' . $title . ')';
					$newtitle = $newbase = '';
				} else {
					$anchortitle = $title;
				}
				$items .= <<<EOD
<item rdf:about="$url">
 <title>$anchortitle</title>
 <link>$url</link>
$description
 <dc:date>$date</dc:date>
 <dc:identifier>$url</dc:identifier>
$trackback_ping
</item>

EOD;
			}
			break;
		}
	}

	// Feeding start
	pkwk_common_headers();
	header('Content-type: application/xml');
	print '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";

	$url_whatsnew = get_page_uri($whatsnew);
	$html = '';
	switch ($version) {
	case '0.91':
		$html .= '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' .
		' "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
		 /* FALLTHROUGH */

	case '2.0':
		$html .= <<<EOD
<rss version="$version">
 <channel>
  <title><![CDATA[$page_title_utf8]]></title>
  <link>$url_whatsnew</link>
  <description><![CDATA[$rss_description_utf8]]></description>
  <language>ja</language>

$items
 </channel>
</rss>
EOD;
		break;

	case '1.0':
		$xmlns_trackback = $trackback ?
			'  xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/"' : '';
		$html .= <<<EOD
<rdf:RDF
  xmlns:dc="http://purl.org/dc/elements/1.1/"
$xmlns_trackback
  xmlns="http://purl.org/rss/1.0/"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xml:lang="ja">
 <channel rdf:about="$url_whatsnew">
  <title><![CDATA[$page_title_utf8]]></title>
  <link>$url_whatsnew</link>
  <description><![CDATA[$rss_description_utf8]]></description>
  <items>
   <rdf:Seq>
$rdf_li
   </rdf:Seq>
  </items>
 </channel>

$items
</rdf:RDF>
EOD;
		break;
	}
	print $html;

	// Write Cache-file
	$fp = fopen($rsscache, 'w');
	flock($fp, LOCK_EX);
	rewind($fp);
	fputs($fp, $html);
	flock($fp, LOCK_UN);
	fclose($fp);

	log_write('cmd','mixirss');
	exit;
}

function plugin_mixirss_isValidDate($aStr, $aSepList="-/ .")
{
	if ($aSepList == '') {
		return checkdate(substr($aStr,4,2),substr($aStr,6,2),substr($aStr,0,4));
	}
	if ( ereg("^([0-9]{2,4})[$aSepList]([0-9]{1,2})[$aSepList]([0-9]{1,2})$", $aStr, $m) ) {
		return checkdate($m[2], $m[3], $m[1]);
	}
	return false;
}
?>
