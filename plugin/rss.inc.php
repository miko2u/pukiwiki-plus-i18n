<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: rss.inc.php,v 1.21.3 2008/01/06 01:24:00 upk Exp $
//
// RSS plugin: Publishing RSS of RecentChanges
//
// Usage: plugin=rss[&ver=[0.91|1.0|2.0]] (Default: 0.91)
//
// NOTE for acronyms
//   RSS 0.9,  1.0  : RSS means 'RDF Site Summary'
//   RSS 0.91, 0.92 : RSS means 'Rich Site Summary'
//   RSS 2.0        : RSS means 'Really Simple Syndication' (born from RSS 0.92)
//
// Plus!NOTE:(policy)not merge official cvs(1.20->1.21) See BugTrack2/62

function plugin_rss_action()
{
	global $vars, $rss_max, $rss_description, $page_title, $whatsnew, $trackback;

	$version = isset($vars['ver']) ? $vars['ver'] : '';
	switch($version){
	case '':  $version = '0.91'; break; // Default
	case '1': $version = '1.0';  break; // Sugar
	case '2': $version = '2.0';  break; // Sugar
	case '0.91': /* FALLTHROUGH */
	case '1.0' : /* FALLTHROUGH */
	case '2.0' : break;
	default: die('Invalid RSS version!!');
	}

	$recent = CACHE_DIR . PKWK_MAXSHOW_CACHE;
	if (! file_exists($recent)) die('PKWK_MAXSHOW_CACHE is not found');

	$lang = LANG;
	$page_title_utf8 = mb_convert_encoding($page_title, 'UTF-8', SOURCE_ENCODING);
	$self = get_script_absuri();
	$rss_description_utf8 = mb_convert_encoding(htmlspecialchars($rss_description), 'UTF-8', SOURCE_ENCODING);

	// Creating <item>
	$items = $rdf_li = '';

	foreach (file_head($recent, $rss_max) as $line) {
		list($time, $page) = explode("\t", rtrim($line));
		$r_page = rawurlencode($page);
		$url    = get_page_absuri($page);
		$title  = mb_convert_encoding($page, 'UTF-8', SOURCE_ENCODING);

		switch ($version) {
		case '0.91': /* FALLTHROUGH */
		case '2.0':
			$date = get_date('D, d M Y H:i:s T', $time);
			$date = ($version == '0.91') ?
				' <description>' . $date . '</description>' :
				' <pubDate>'     . $date . '</pubDate>';
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
			$rdf_li .= '    <rdf:li rdf:resource="' . $url . '" />' . "\n";

			$date = substr_replace(get_date('Y-m-d\TH:i:sO', $time), ':', -2, 0);
			$trackback_ping = '';
			if ($trackback) {
				$tb_id = md5($r_page);
				$trackback_ping = ' <trackback:ping rdf:resource="' . "$self?tb_id=$tb_id" . '"/>';
			}
			$items .= <<<EOD
<item rdf:about="$url">
 <title>$title</title>
 <link>$url</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$url</dc:identifier>
$trackback_ping
</item>

EOD;
			break;
		}
	}

	// Feeding start
	pkwk_common_headers();
	header('Content-type: application/xml');
	print '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";

	$url_whatsnew = get_page_absuri($whatsnew);
	switch ($version) {
	case '0.91':
		print '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' .
		' "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
		 /* FALLTHROUGH */

	case '2.0':
		print <<<EOD
<rss version="$version">
 <channel>
  <title><![CDATA[$page_title_utf8]]></title>
  <link>$url_whatsnew</link>
  <description><![CDATA[$rss_description_utf8]]></description>
  <language>$lang</language>

$items
 </channel>
</rss>
EOD;
		break;

	case '1.0':
		$xmlns_trackback = $trackback ?
			'  xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/"' : '';
		print <<<EOD
<rdf:RDF
  xmlns:dc="http://purl.org/dc/elements/1.1/"
$xmlns_trackback
  xmlns="http://purl.org/rss/1.0/"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xml:lang="$lang">
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
	exit;
}
?>
