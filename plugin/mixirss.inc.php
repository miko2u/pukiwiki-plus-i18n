<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: mixirss.inc.php,v 1.12.1 2004/12/21 01:20:02 miko Exp $
//
// Publishing RSS feed of RecentChanges
// Usage: mixirss.inc.php?ver=[0.91|1.0(default)|2.0]

// View Description Letters
define('MIXIRSS_DESCRIPTION_LENGTH', 512);
define('MIXIRSS_LANG', 'ja_JP');

// XSLT extra parameters
//define('LOGO', 'http://pukiwiki.cafelounge.net/image/plus.gif');
//define('FOAF', 'http://pukiwiki.cafelounge.net/elements/foaf.rdf');

function plugin_mixirss_action()
{
	global $vars, $rss_max, $page_title, $whatsnew, $trackback;
	global $modifier;

	$version = isset($vars['ver']) ? $vars['ver'] : '';
	switch($version){
	case '':  $version = '1.0';  break; // mixi Default
	case '1': $version = '1.0';  break;
	case '2': $version = '2.0';  break;
	case '0,91': /* FALLTHROUGH */
	case '1.0' : /* FALLTHROUGH */
	case '2.0' : break;
	default: die('Invalid RSS version!!');
	}

	$recent = CACHE_DIR . 'recent.dat';
	if (! file_exists($recent)) die('recent.dat is not found');

	$page_title_utf8 = mb_convert_encoding($page_title, 'UTF-8', SOURCE_ENCODING);
	$self  = get_script_uri();

	// Creating <item>
	$items = $rdf_li = '';
	foreach (array_splice(file($recent), 0, $rss_max) as $line) {
		list($time, $page) = explode("\t", rtrim($line));
		$r_page = rawurlencode($page);
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
 <link>$self?$r_page</link>
$date
</item>

EOD;
			break;

		case '1.0':
			// Add <item> into <items>
//			$rdf_li .= '    <rdf:li rdf:resource="' . $self .
//				'?' . $r_page . '" />' . "\n";

			$date = substr_replace(get_date('Y-m-d\TH:i:sO', $time), ':', -2, 0);
			$trackback_ping = '';
			if ($trackback) {
				$tb_id = md5($r_page);
				$trackback_ping = ' <trackback:ping>' .
					"$self?tb_id=$tb_id" . '</trackback:ping>';
			}
			if (plugin_mixirss_isValidDate(substr($page,-10)) && check_readable($page,false,false)) {
				$source = get_source($page);
				$rdf_hx = '';
				$rdf_lx = '';
				$itemhx = '';
				$itemlx = '';
				while(!empty($source)) {
					$line = array_shift($source);
					if (preg_match('/^(\*{1,3})(.*)\[#([A-Za-z][\w-]+)\](.*)$/m', $line, $matches)) {
						$anchortitle = convert_html($matches[2]);
						$anchortitle = preg_replace('#<([^>]*)>#','',$anchortitle);
						$anchortitle = '<![CDATA[' . mb_convert_encoding($anchortitle, 'UTF-8', SOURCE_ENCODING) . ']]>';
						$sharp = '#';
						$rdf_hx .= '    <rdf:li rdf:resource="' . $self . '?' . $r_page . $sharp . $matches[3] . '" />' . "\n";
						$itemhx .= <<<EOD
<item rdf:about="$self?$r_page{$sharp}{$matches[3]}">
 <title>{$anchortitle}({$title})</title>
 <link>$self?$r_page{$sharp}{$matches[3]}</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$self?$r_page{$sharp}{$matches[3]}</dc:identifier>
$trackback_ping
</item>

EOD;
					} else if (preg_match('/^(\-{1,3})(.*)$/m', $line, $matches)) {
						$anchortitle = convert_html($matches[2]);
						$anchortitle = preg_replace('#<([^>]*)>#','',$anchortitle);
						$anchortitle = '<![CDATA[' . mb_convert_encoding($anchortitle, 'UTF-8', SOURCE_ENCODING) . ']]>';
						$sharp = '#';
						$rdf_lx .= '    <rdf:li rdf:resource="' . $self . '?' . $r_page . '" />' . "\n";
						$itemlx .= <<<EOD
<item rdf:about="$self?$r_page">
 <title>{$anchortitle}({$title})</title>
 <link>$self?$r_page</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$self?$r_page</dc:identifier>
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
				}
			} else {
			$rdf_li .= '    <rdf:li rdf:resource="' . $self . '?' . $r_page . '" />' . "\n";
			$items .= <<<EOD
<item rdf:about="$self?$r_page">
 <title>$title</title>
 <link>$self?$r_page</link>
 <dc:date>$date</dc:date>
 <dc:identifier>$self?$r_page</dc:identifier>
$trackback_ping
</item>

EOD;
			}
			break;
		}
	}

	// Feeding start
	if (function_exists('pkwk_headers_sent')) { pkwk_headers_sent(); }
	header('Content-type: application/xml');
	print '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";

	$r_whatsnew = rawurlencode($whatsnew);
	switch ($version) {
	case '0.91':
		print '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' .
		' "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
		 /* FALLTHROUGH */

	case '2.0':
		print <<<EOD
<rss version="$version">
 <channel>
  <title>$page_title_utf8</title>
  <link>$self?$r_whatsnew</link>
  <description>PukiWiki RecentChanges</description>
  <language>ja</language>

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
  xml:lang="ja">
 <channel rdf:about="$self?$r_whatsnew">
  <title>$page_title_utf8</title>
  <link>$self?$r_whatsnew</link>
  <description>PukiWiki RecentChanges</description>
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

function plugin_mixirss_isValidDate($aStr, $aSepList="-/ .")
{
	if ($aSepList == "") {
		return checkdate(substr($aStr,4,2),substr($aStr,6,2),substr($aStr,0,4));
	}
	if ( ereg("^([0-9]{2,4})[$aSepList]([0-9]{1,2})[$aSepList]([0-9]{1,2})$", $aStr, $m) ) {
		return checkdate($m[2], $m[3], $m[1]);
	}
	return false;
}
?>