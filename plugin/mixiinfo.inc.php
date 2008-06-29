<?php
/**
 * PukiWiki Plus! mixi info
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: mixiinfo.inc.php,v 0.3 2008/06/21 23:46:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 * @see         http://d.hatena.ne.jp/shimooka/20070702/1183374400
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('MIXI_FOOTPRINT_URL') or define('MIXI_FOOTPRINT_URL', 'http://mixi.jp/atom/tracks/r=2/member_id=');
defined('MIXI_NOTIFY_URL') or define('MIXI_NOTIFY_URL', 'http://mixi.jp/atom/notify/r=2/member_id=');

function plugin_mixiinfo_convert()
{
	@list($kind) = func_get_args();
	$kind = (empty($kind)) ? 'fp' : $kind;

	$obj = new auth_api();
	$msg = $obj->auth_session_get();
	if ($msg['api'] != 'mixi' || empty($msg['id'])) return '<p>Please mixi login.</p>';

	switch($kind){
	case 'fp':
	case 'footprint':
		$url = MIXI_FOOTPRINT_URL.$msg['id'];
		$edit_func = 'mixiinfo_footprint_edit';
		break;
	case 'notify':
		$url = MIXI_NOTIFY_URL.$msg['id'];
		$edit_func = 'mixiinfo_notify_edit';
		break;
	default:
		$url = MIXI_FOOTPRINT_URL.$msg['id'];
		$edit_func = 'mixiinfo_footprint_edit';
		break;
	}

	$wsse_header = decode($msg['wsse']);
	$data = http_request($url,'GET',array('X-WSSE'=>$wsse_header),array());

	$xml_data = mixiinfo_xml_parser($data['data']);
	return $edit_func($xml_data);
}

function mixiinfo_xml_parser($data)
{
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $data, $val, $index);
        xml_parser_free($xml_parser);

        $rc = array();
        $i = 0;
        foreach($val as $x) {
                $tag = strtolower($x['tag']);
                if ($tag == 'entry' && $x['type'] == 'open') $i++;
                if ($x['type'] != 'complete') continue;
                if ($tag == 'content') continue;
                if ($tag == 'updated') {
                        $x['value'] = mixiinfo_get_timestamp($x['value']);
                }
                $rc[$i][$tag] = ($tag == 'link') ? $x['attributes']['HREF'] : $x['value'];
        }

        return $rc;
}

function mixiinfo_get_timestamp($str)
{
	$str = trim($str);
	if ($str == '') return UTIME;

	$matches = array();

	if (preg_match('/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(([+-])(\d{2}):(\d{2}))?/', $str, $matches)) {
		$time = gmmktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
		if (! empty($matches[7])) {
			$offset = ($matches[7] === '-') ? -1 : 1;
			$offset = $offset * (($matches[8] * 60) + $matches[9]);
		} else {
			$offset = 0;
		}
	}

	return ($time + $offset);
}

function mixiinfo_footprint_edit($data)
{
	$rc = '';

	$i=0;
	foreach($data as $line) {
		if ($line['title'] === 'mixi tracks') continue;
		$rc .= '|&ref('.$line['tracks:image'].'); [['.$line['name'].'>'.$line['link'].']]さん'.get_passage($line['updated']);
		$i++;
		if ($i == 2) {
			$rc .= "|\n";
			$i=0;
		}
	}

	if ($i > 0) $rc .= "||\n";

	return convert_html($rc);
}

function mixiinfo_notify_edit($data)
{
	$rc = '';

	foreach($data as $line) {
		if ($line['title'] === 'mixi notify') continue;
		$rc .= '-[['.$line['title'].'>'.$line['link'].']]'.get_passage($line['updated'])."\n";
	}

	return convert_html($rc);
}

?>
