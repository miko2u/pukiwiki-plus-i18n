<?php
/**
 * TimeZone
 *
 * @copyright   Copyright &copy; 2005-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: timezone.php,v 0.11 2006/03/24 01:12:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/*
 * set_time
 *
 */
function set_time()
{
	global $language, $use_local_time;

	define('UTIME', time());
	define('MUTIME', getmicrotime());

	if ($use_local_time) {
		list($zone, $zonetime) = set_timezone( DEFAULT_LANG );
	} else {
		list($zone, $zonetime) = set_timezone( $language );
		list($l_zone, $l_zonetime) = get_localtimezone();
		if ($l_zonetime != '' && $zonetime != $l_zonetime) {
			$zone = $l_zone;
			$zonetime = $l_zonetime;
		}
	}

	define('ZONE', $zone);
	define('ZONETIME', $zonetime);
}

/*
 * set_timezone
 *
 */
function set_timezone($lang='')
{
	if (empty($lang)) {
		return array('UTC', 0);
	}
	$l = accept_language::split_locale_str( $lang );

	// When the name of a country is uncertain (国名が不明な場合)
	if (empty($l[2])) {
		$obj_l2c = new lang2country();
		$l[2] = $obj_l2c->get_lang2country($l[1]);
		if (empty($l[2])) {
			return array('UTC', 0);
		}
	}

	$obj = new timezone();
	$obj->set_datetime(UTIME); // Setting at judgment time. (判定時刻の設定)
	$obj->set_country($l[2]); // The acquisition country is specified. (取得国を指定)

	// With the installation country in case of the same
	// 設置者の国と同一の場合
	if ($lang == DEFAULT_LANG) {
		if (defined('DEFAULT_TZ_NAME')) {
			$obj->set_tz_name(DEFAULT_TZ_NAME);
		}
	}

	list($zone, $zonetime) = $obj->get_zonetime();

	if ($zonetime == 0 || empty($zone)) {
		return array('UTC', 0);
	}

	return array($zone, $zonetime);
}

function get_localtimezone()
{
	if (isset($_COOKIE['timezone'])) {
		$tz = $_COOKIE['timezone'];
	} else {
		return array('','');
	}

	$tz = trim($tz);

	$offset = substr($tz,0,1);
	switch ($offset) {
	case '-':
	case '+':
		$tz = substr($tz,1);
		break;
	default:
		$offset = '+';
	}

	$h = substr($tz,0,2);
	$i = substr($tz,2,2);

	$zonetime = ($h * 3600) + ($i * 60);
	$zonetime = ($offset == '-') ? $zonetime * -1 : $zonetime;

	return array($offset.$tz, $zonetime);
}

/**
 * timezone
 * @abstract
 *
 */
class timezone
{
  var $country;
  var $tz_country;
  var $tz_name;
  var $utime, $y, $m;
  var $offset;
  var $tz = array(
    // Key - TimeZone => 0: OFFSET, 1:HOUR, 2:MINUTE, 3:ISO3166
    //                   4: ABBREV, 5:DAYLIGHT, 6:RULE(DST)
    'Africa/Abidjan'                   => array( 1,  0,  0, 'CI', 'GMT',  '',     ''),
    'Africa/Accra'                     => array( 1,  0,  0, 'GH', 'GMT',  '',     ''),
    'Africa/Addis_Ababa'               => array( 1,  3,  0, 'ET', 'EAT',  '',     ''),
    'Africa/Algiers'                   => array( 1,  1,  0, 'DZ', 'CET',  '',     ''),
    'Africa/Asmera'                    => array( 1,  3,  0, 'ER', 'EAT',  '',     ''),
    'Africa/Bamako'                    => array( 1,  0,  0, 'ML', 'GMT',  '',     ''),
    'Africa/Bangui'                    => array( 1,  1,  0, 'CF', 'WAT',  '',     ''),
    'Africa/Banjul'                    => array( 1,  0,  0, 'GM', 'GMT',  '',     ''),
    'Africa/Bissau'                    => array( 1,  0,  0, 'GW', 'GMT',  '',     ''),
    'Africa/Blantyre'                  => array( 1,  2,  0, 'MW', 'CAT',  '',     ''),
    'Africa/Brazzaville'               => array( 1,  1,  0, 'CG', 'WAT',  '',     ''),
    'Africa/Bujumbura'                 => array( 1,  2,  0, 'BI', 'CAT',  '',     ''),
    'Africa/Cairo'                     => array( 1,  2,  0, 'EG', 'EET',  'EEST', 'Egypt'),
    'Africa/Casablanca'                => array( 1,  0,  0, 'MA', 'WET',  '',     ''),
    'Africa/Ceuta'                     => array( 1,  1,  0, 'ES', 'CET',  'CEST', 'EU'),
    'Africa/Conakry'                   => array( 1,  0,  0, 'GN', 'GMT',  '',     ''),
    'Africa/Dakar'                     => array( 1,  0,  0, 'SN', 'GMT',  '',     ''),
    'Africa/Dar_es_Salaam'             => array( 1,  3,  0, 'TZ', 'EAT',  '',     ''),
    'Africa/Djibouti'                  => array( 1,  3,  0, 'DJ', 'EAT',  '',     ''),
    'Africa/Douala'                    => array( 1,  1,  0, 'CM', 'WAT',  '',     ''),
    'Africa/El_Aaiun'                  => array( 1,  0,  0, 'EH', 'WET',  '',     ''),
    'Africa/Freetown'                  => array( 1,  0,  0, 'SL', 'GMT',  '',     'SL'),
    'Africa/Gaborone'                  => array( 1,  2,  0, 'BW', 'CAT',  '',     ''),
    'Africa/Harare'                    => array( 1,  2,  0, 'ZW', 'CAT',  '',     ''),
    'Africa/Johannesburg'              => array( 1,  2,  0, 'ZA', 'SAST', '',     'SA'),
    'Africa/Kampala'                   => array( 1,  3,  0, 'UG', 'EAT',  '',     ''),
    'Africa/Khartoum'                  => array( 1,  3,  0, 'SD', 'EAT',  '',     ''),
    'Africa/Kigali'                    => array( 1,  2,  0, 'RW', 'CAT',  '',     ''),
    'Africa/Kinshasa'                  => array( 1,  1,  0, 'CD', 'WAT',  '',     ''),
    'Africa/Lagos'                     => array( 1,  1,  0, 'NG', 'WAT',  '',     ''),
    'Africa/Libreville'                => array( 1,  1,  0, 'GA', 'WAT',  '',     ''),
    'Africa/Lome'                      => array( 1,  0,  0, 'TG', 'GMT',  '',     ''),
    'Africa/Luanda'                    => array( 1,  1,  0, 'AO', 'WAT',  '',     ''),
    'Africa/Lubumbashi'                => array( 1,  2,  0, 'CD', 'CAT',  '',     ''),
    'Africa/Lusaka'                    => array( 1,  2,  0, 'ZM', 'CAT',  '',     ''),
    'Africa/Malabo'                    => array( 1,  1,  0, 'GQ', 'WAT',  '',     ''),
    'Africa/Maputo'                    => array( 1,  2,  0, 'MZ', 'CAT',  '',     ''),
    'Africa/Maseru'                    => array( 1,  2,  0, 'LS', 'SAST', '',     ''),
    'Africa/Mbabane'                   => array( 1,  2,  0, 'SZ', 'SAST', '',     ''),
    'Africa/Mogadishu'                 => array( 1,  3,  0, 'SO', 'EAT',  '',     ''),
    'Africa/Monrovia'                  => array( 1,  0,  0, 'LR', 'GMT',  '',     ''),
    'Africa/Nairobi'                   => array( 1,  3,  0, 'KE', 'EAT',  '',     ''),
    'Africa/Ndjamena'                  => array( 1,  1,  0, 'TD', 'WAT',  '',     ''),
    'Africa/Niamey'                    => array( 1,  1,  0, 'NE', 'WAT',  '',     ''),
    'Africa/Nouakchott'	               => array( 1,  0,  0, 'MR', 'GMT',  '',     ''),
    'Africa/Ouagadougou'               => array( 1,  0,  0, 'BF', 'GMT',  '',     ''),
    'Africa/Porto-Novo'                => array( 1,  1,  0, 'BJ', 'WAT',  '',     ''),
    'Africa/Sao_Tome'                  => array( 1,  0,  0, 'ST', 'GMT',  '',     ''),
    'Africa/Timbuktu'                  => array( 1,  0,  0, 'ML', 'GMT',  '',     ''),
    'Africa/Tripoli'                   => array( 1,  2,  0, 'LY', 'EET',  '',     ''),
    'Africa/Tunis'                     => array( 1,  1,  0, 'TN', 'CET',  '',     'Tunisia'),
    'Africa/Windhoek'                  => array( 1,  1,  0, 'NA', 'WAT',  'WAST', 'Namibia'),
    'America/Adak'                     => array(-1, 10,  0, 'US', 'HAST', 'HADT', 'US'),
    'America/Anchorage'                => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'US'),
    'America/Anguilla'                 => array(-1,  4,  0, 'AI', 'AST',  '',     ''),
    'America/Antigua'                  => array(-1,  4,  0, 'AG', 'AST',  '',     ''),
    'America/Araguaina'                => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Argentina/Buenos_Aires'   => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Catamarca'      => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/ComodRivadavia' => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Cordoba'        => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Jujuy'          => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/La_Rioja'       => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Mendoza'        => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Rio_Gallegos'   => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/San_Juan'       => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Tucuman'        => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Argentina/Ushuaia'        => array(-1,  3,  0, 'AR', 'ART',  '',     ''),
    'America/Aruba'                    => array(-1,  4,  0, 'AW', 'AST',  '',     ''),
    'America/Asuncion'                 => array(-1,  4,  0, 'PY', 'PYT',  'PYST', 'Para'),
    'America/Atka'                     => array(-1, 10,  0, 'US', 'HAST', 'HADT', 'US'), // America/Adak
    'America/Bahia'                    => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Barbados'                 => array(-1,  4,  0, 'BB', 'AST',  '',     'Barb'),
    'America/Belem'                    => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Belize'                   => array(-1,  6,  0, 'BZ', 'CST',  '',     'Belize'),
    'America/Boa_Vista'                => array(-1,  4,  0, 'BR', 'AMT',  '',     ''),
    'America/Bogota'                   => array(-1,  5,  0, 'CO', 'COT',  '',     'CO'),
    'America/Boise'                    => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),
    'America/Buenos_Aires'             => array(-1,  3,  0, 'AR', 'ART',  '',     ''), // America/Argentina/Buenos_Aires
    'America/Cambridge_Bay'            => array(-1,  7,  0, 'CA', 'MST',  'MDT',  'Canada'),
    'America/Campo_Grande'             => array(-1,  4,  0, 'BR', 'AMT',  'AMST', 'Brazil'),
    'America/Cancun'                   => array(-1,  6,  0, 'MX', 'CST',  'CDT',  'Mexico'),
    'America/Caracas'                  => array(-1,  4,  0, 'VE', 'VET',  '',     ''),
    'America/Catamarca'                => array(-1,  3,  0, 'AR', 'ART',  '',     ''), // America/Argentina/Catamarca
    'America/Cayenne'                  => array(-1,  3,  0, 'GF', 'GFT',  '',     ''),
    'America/Cayman'                   => array(-1,  5,  0, 'KY', 'EST',  '',     ''),
    'America/Chicago'                  => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),
    'America/Chihuahua'                => array(-1,  7,  0, 'MX', 'MST',  'MDT',  'Mexico'),
    'America/Cordoba'                  => array(-1,  3,  0, 'AR', 'ART',  '',     ''), // America/Argentina/Cordoba
    'America/Costa_Rica'               => array(-1,  6,  0, 'CR', 'CST',  '',     'CR'),
    'America/Cuiaba'                   => array(-1,  4,  0, 'BR', 'AMT',  'AMST', 'Brazil'),
    'America/Curacao'                  => array(-1,  4,  0, 'AN', 'AST',  '',     ''),
    'America/Danmarkshavn'             => array( 1,  0,  0, 'GL', 'GMT',  '',     ''),
    'America/Dawson'                   => array(-1,  8,  0, 'CA', 'PST',  'PDT',  'NT_YK'),
    'America/Dawson_Creek'             => array(-1,  7,  0, 'CA', 'MST',  '',     ''),
    'America/Denver'                   => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),
    'America/Detroit'                  => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),
    'America/Dominica'                 => array(-1,  4,  0, 'DM', 'AST',  '',     ''),
    'America/Edmonton'                 => array(-1,  7,  0, 'CA', 'MST',  'MDT',  'Edm'),
    'America/Eirunepe'                 => array(-1,  5,  0, 'BR', 'ACT',  '',     ''),
    'America/El_Salvador'              => array(-1,  6,  0, 'SV', 'CST',  '',     'Salv'),
    'America/Ensenada'                 => array(-1,  8,  0, 'MX', 'PST',  'PDT',  'Mexico'), // America/Tijuana
    'America/Fort_Wayne'               => array(-1,  5,  0, 'US', 'EST',  '',     ''),       // America/Indianapolis
    'America/Fortaleza'                => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Glace_Bay'                => array(-1,  4,  0, 'CA', 'AST',  'ADT',  'Canada'),
    'America/Godthab'                  => array(-1,  3,  0, 'GL', 'WGT',  'WGST', 'EU'),
    'America/Goose_Bay'                => array(-1,  4,  0, 'CA', 'AST',  'ADT',  'StJohns'),
    'America/Grand_Turk'               => array(-1,  5,  0, 'TC', 'EST',  'EDT',  'TC'),
    'America/Grenada'                  => array(-1,  4,  0, 'GD', 'AST',  '',     ''),
    'America/Guadeloupe'               => array(-1,  4,  0, 'GP', 'AST',  '',     ''),
    'America/Guatemala'                => array(-1,  6,  0, 'GT', 'CST',  '',     'Guat'),
    'America/Guayaquil'                => array(-1,  5,  0, 'EC', 'ECT',  '',     ''),
    'America/Guyana'                   => array(-1,  4,  0, 'GY', 'GYT',  '',     ''),
    'America/Halifax'                  => array(-1,  4,  0, 'CA', 'AST',  'ADT',  'Canada'),
    'America/Havana'                   => array(-1,  5,  0, 'CU', 'CST',  'CDT',  'Cuba'),
    'America/Hermosillo'               => array(-1,  7,  0, 'MX', 'MST',  '',     ''),
    'America/Indiana/Indianapolis'     => array(-1,  5,  0, 'US', 'EST',  '',     ''), // America/Indianapolis
    'America/Indiana/Knox'             => array(-1,  5,  0, 'US', 'EST',  '',     ''),
    'America/Indiana/Marengo'          => array(-1,  5,  0, 'US', 'EST',  '',     ''),
    'America/Indiana/Vevay'            => array(-1,  5,  0, 'US', 'EST',  '',     ''),
    'America/Indianapolis'             => array(-1,  5,  0, 'US', 'EST',  '',     ''),
    'America/Inuvik'                   => array(-1,  7,  0, 'CA', 'MST',  'MDT',  'NT_YK'),
    'America/Iqaluit'                  => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Jamaica'                  => array(-1,  5,  0, 'JM', 'EST',  '',     ''),
    'America/Jujuy'                    => array(-1,  3,  0, 'AR', 'ART',  '',     ''),   // America/Argentina/Jujuy
    'America/Juneau'                   => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'US'),
    'America/Kentucky/Louisville'      => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'), // America/Louisville
    'America/Kentucky/Monticello'      => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),
    'America/Knox_IN'                  => array(-1,  5,  0, 'US', 'EST',  '',     ''),   // America/Indiana/Knox
    'America/La_Paz'                   => array(-1,  4,  0, 'BO', 'BOT',  '',     ''),
    'America/Lima'                     => array(-1,  5,  0, 'PE', 'PET',  '',     'Peru'),
    'America/Los_Angeles'              => array(-1,  8,  0, 'US', 'PST',  'PDT',  'US'),
    'America/Louisville'               => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),
    'America/Maceio'                   => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Managua'                  => array(-1,  6,  0, 'NI', 'CST',  '',     ''),
    'America/Manaus'                   => array(-1,  4,  0, 'BR', 'AMT',  '',     ''),
    'America/Martinique'               => array(-1,  4,  0, 'MQ', 'AST',  '',     ''),
    'America/Mazatlan'                 => array(-1,  7,  0, 'MX', 'MST',  'MDT',  'Mexico'),
    'America/Mendoza'                  => array(-1,  3,  0, 'AR', 'ART',  '',     ''), // America/Argentina/Mendoza
    'America/Menominee'                => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),
    'America/Merida'                   => array(-1,  6,  0, 'MX', 'CST',  'CDT',  'Mexico'),
    'America/Mexico_City'              => array(-1,  6,  0, 'MX', 'CST',  'CDT',  'Mexico'),
    'America/Miquelon'                 => array(-1,  3,  0, 'PM', 'PMST', 'PMDT', 'Canada'),
    'America/Monterrey'                => array(-1,  6,  0, 'MX', 'CST',  'CDT',  'Mexico'),
    'America/Montevideo'               => array(-1,  3,  0, 'UY', 'UYT',  'UYST', 'Uruguay'),
    'America/Montreal'                 => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Montserrat'               => array(-1,  4,  0, 'MS', 'AST',  '',     ''),
    'America/Nassau'                   => array(-1,  5,  0, 'BS', 'EST',  'EDT',  'Bahamas'),
    'America/New_York'                 => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),
    'America/Nipigon'                  => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Nome'                     => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'Canada'),
    'America/Noronha'                  => array(-1,  2,  0, 'BR', 'FNT',  '',     ''),
    'America/North_Dakota/Center'      => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),
    'America/Panama'                   => array(-1,  5,  0, 'PA', 'EST',  '',     ''),
    'America/Pangnirtung'              => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Paramaribo'               => array(-1,  3,  0, 'SR', 'SRT',  '',     ''),
    'America/Phoenix'                  => array(-1,  7,  0, 'US', 'MST',  '',     ''),
    'America/Port-au-Prince'           => array(-1,  5,  0, 'HT', 'EST',  'EDT',  'Haiti'),
    'America/Port_of_Spain'            => array(-1,  4,  0, 'TT', 'AST',  '',     ''),
    'America/Porto_Acre'               => array(-1,  5,  0, 'BR', 'ACT',  '',     ''), // America/Rio_Branco
    'America/Porto_Velho'              => array(-1,  4,  0, 'BR', 'AMT',  '',     ''),
    'America/Puerto_Rico'              => array(-1,  4,  0, 'PR', 'AST',  '',     ''),
    'America/Rainy_River'              => array(-1,  6,  0, 'CA', 'CST',  'CDT',  'Canada'),
    'America/Rankin_Inlet'             => array(-1,  6,  0, 'CA', 'CST',  'CDT',  'Canada'),
    'America/Recife'                   => array(-1,  3,  0, 'BR', 'BRT',  '',     ''),
    'America/Regina'                   => array(-1,  6,  0, 'CA', 'CST',  '',     ''),
    'America/Rio_Branco'               => array(-1,  5,  0, 'BR', 'ACT',  '',     ''),
    'America/Rosario'                  => array(-1,  3,  0, 'AR', 'ART',  '',     ''), // America/Cordoba, FIXME: AR
    'America/Santiago'                 => array(-1,  4,  0, 'CL', 'CLT',  'CLST', 'Chile'),
    'America/Santo_Domingo'            => array(-1,  4,  0, 'DO', 'AST',  '',     ''),
    'America/Sao_Paulo'                => array(-1,  3,  0, 'BR', 'BRT',  'BRST', 'Brazil'),
    'America/Scoresbysund'             => array(-1,  1,  0, 'GL', 'EGT',  'EGST', 'EU'),
    'America/Shiprock'                 => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'), // America/Denver
    'America/St_Johns'                 => array(-1,  3, 30, 'CA', 'NST',  'NDT',  'StJohns'),
    'America/St_Kitts'                 => array(-1,  4,  0, 'KN', 'AST',  '',     ''),
    'America/St_Lucia'                 => array(-1,  4,  0, 'LC', 'AST',  '',     ''),
    'America/St_Thomas'                => array(-1,  4,  0, 'VI', 'AST',  '',     ''), // Virgin Is
    'America/St_Vincent'               => array(-1,  4,  0, 'VC', 'AST',  '',     ''),
    'America/Swift_Current'            => array(-1,  6,  0, 'CA', 'CST',  '',     ''),
    'America/Tegucigalpa'              => array(-1,  6,  0, 'HN', 'CST',  '',     'Salv'),
    'America/Thule'                    => array(-1,  4,  0, 'GL', 'AST',  'ADT',  'Thule'),
    'America/Thunder_Bay'              => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Tijuana'                  => array(-1,  8,  0, 'MX', 'PST',  'PDT',  'Mexico'),
    'America/Toronto'                  => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),
    'America/Tortola'                  => array(-1,  4,  0, 'VG', 'AST',  '',     ''),
    'America/Vancouver'                => array(-1,  8,  0, 'CA', 'PST',  'PDT',  'Vanc'),
    'America/Virgin'                   => array(-1,  4,  0, 'VI', 'AST',  '',     ''), // America/St_Thomas
    'America/Whitehorse'               => array(-1,  8,  0, 'CA', 'PST',  'PDT',  'NT_YK'),
    'America/Winnipeg'                 => array(-1,  6,  0, 'CA', 'CST',  'CDT',  'Winn'),
    'America/Yakutat'                  => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'US'),
    'America/Yellowknife'              => array(-1,  7,  0, 'CA', 'MST',  'MDT',  'NT_YK'),
    'Antarctica/Casey'                 => array( 1,  8,  0, 'AQ', 'WST',  '',     ''),
    'Antarctica/Davis'                 => array( 1,  7,  0, 'AQ', 'DAVT', '',     ''),
    'Antarctica/DumontDUrville'        => array( 1, 10,  0, 'AQ', 'DDUT', '',     ''),
    'Antarctica/Mawson'                => array( 1,  6,  0, 'AQ', 'MAWT', '',     ''),
    'Antarctica/McMurdo'               => array( 1, 12,  0, 'AQ', 'NZST', 'NZDT', 'NZAQ'),
    'Antarctica/Palmer'                => array(-1,  4,  0, 'AQ', 'CLT',  'CLST', 'ChileAQ'),
    'Antarctica/Rothera'               => array(-1,  3,  0, 'AQ', 'ROTT', '',     ''),
    'Antarctica/South_Pole'            => array( 1, 12,  0, 'AQ', 'NZST', 'NZDT', 'NZAQ'), // Antarctica/McMurdo
    'Antarctica/Syowa'                 => array( 1,  3,  0, 'AQ', 'SYOT', '',     ''),
    'Antarctica/Vostok'                => array( 1,  6,  0, 'AQ', 'VOST', '',     ''),
    'Arctic/Longyearbyen'              => array( 1,  1,  0, 'SJ', 'CET',  'CEST', 'EU'), // Europe/Oslo
    'Asia/Aden'                        => array( 1,  3,  0, 'YE', 'AST',  '',     ''),
    'Asia/Almaty'                      => array( 1,  6,  0, 'KZ', 'ALMT', 'ALMST','RussiaAsia'),
    'Asia/Amman'                       => array( 1,  2,  0, 'JO', 'EET',  'EEST', 'Jordan'),
    'Asia/Anadyr'                      => array( 1, 12,  0, 'RU', 'ANAT', 'ANAST','Russia'),
    'Asia/Aqtau'                       => array( 1,  4,  0, 'KZ', 'AQTT', 'AQTST','RussiaAsia'),
    'Asia/Aqtobe'                      => array( 1,  5,  0, 'KZ', 'AQTT', 'AQTST','RussiaAsia'),
    'Asia/Ashgabat'                    => array( 1,  5,  0, 'TM', 'TMT',  '',     ''),
    'Asia/Ashkhabad'                   => array( 1,  5,  0, 'TM', 'TMT',  '',     ''), // Asia/Ashgabat
    'Asia/Baghdad'                     => array( 1,  3,  0, 'IQ', 'AST',  'ADT',  'Iraq'),
    'Asia/Bahrain'                     => array( 1,  3,  0, 'BH', 'AST',  '',     ''),
    'Asia/Baku'                        => array( 1,  4,  0, 'AZ', 'AZT',  'AZST', 'Azer'),
    'Asia/Bangkok'                     => array( 1,  7,  0, 'TH', 'ICT',  '',     ''),
    'Asia/Beirut'                      => array( 1,  2,  0, 'LB', 'EET',  'EEST', 'Lebanon'),
    'Asia/Bishkek'                     => array( 1,  5,  0, 'KG', 'KGT',  'KGST', 'Kirgiz'),
    'Asia/Brunei'                      => array( 1,  8,  0, 'BN', 'BNT',  '',     ''),
    'Asia/Calcutta'                    => array( 1,  5, 30, 'IN', 'IST',  '',     ''),
    'Asia/Choibalsan'                  => array( 1,  9,  0, 'MN', 'CHOT', 'CHOST','Mongol'),
    'Asia/Chongqing'                   => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),
    'Asia/Chungking'                   => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'), // Asia/Chongqing
    'Asia/Colombo'                     => array( 1,  6,  0, 'LK', 'LKT',  '',     ''),
    'Asia/Dacca'                       => array( 1,  6,  0, 'BD', 'BDT',  '',     ''), // Asia/Dhaka
    'Asia/Damascus'                    => array( 1,  2,  0, 'SY', 'EET',  'EEST', 'Syria'),
    'Asia/Dhaka'                       => array( 1,  6,  0, 'BD', 'BDT',  '',     ''),
    'Asia/Dili'                        => array( 1,  9,  0, 'TL', 'TPT',  '',     ''),
    'Asia/Dubai'                       => array( 1,  4,  0, 'AE', 'GST',  '',     ''),
    'Asia/Dushanbe'                    => array( 1,  5,  0, 'TJ', 'TJT',  '',     ''),
    'Asia/Gaza'                        => array( 1,  2,  0, 'PS', 'EET',  'EEST', 'Palestine'),
    'Asia/Harbin'                      => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),
    'Asia/Hong_Kong'                   => array( 1,  8,  0, 'HK', 'HKT',  '',     'HK'),
    'Asia/Hovd'                        => array( 1,  7,  0, 'MN', 'HOVT', 'HOVST','Mongol'),
    'Asia/Irkutsk'                     => array( 1,  8,  0, 'RU', 'IRKT', 'IRKST','Russia'),
    'Asia/Istanbul'                    => array( 1,  2,  0, 'TR', 'EET',  'EEST', 'EU'), // Europe/Istanbul
    'Asia/Jakarta'                     => array( 1,  7,  0, 'ID', 'WIT',  '',     ''),
    'Asia/Jayapura'                    => array( 1,  9,  0, 'ID', 'EIT',  '',     ''),
    'Asia/Jerusalem'                   => array( 1,  2,  0, 'IL', 'IST',  'IDT',  'Zion'),
    'Asia/Kabul'                       => array( 1,  4, 30, 'AF', 'AFT',  '',     ''),
    'Asia/Kamchatka'                   => array( 1, 12,  0, 'RU', 'PETT', 'PETST','Russia'),
    'Asia/Karachi'                     => array( 1,  5,  0, 'PK', 'PKT',  '',     'Pakistan'),
    'Asia/Kashgar'                     => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),
    'Asia/Katmandu'                    => array( 1,  5, 45, 'NP', 'NPT',  '',     ''),
    'Asia/Krasnoyarsk'                 => array( 1,  7,  0, 'RU', 'KRAT', 'KRAST','Russia'),
    'Asia/Kuala_Lumpur'                => array( 1,  8,  0, 'MY', 'MYT',  '',     ''),
    'Asia/Kuching'                     => array( 1,  8,  0, 'MY', 'MYT',  '',     ''),
    'Asia/Kuwait'                      => array( 1,  3,  0, 'KW', 'AST',  '',     ''),
    'Asia/Macao'                       => array( 1,  8,  0, 'MO', 'CST',  '',     'PRC'), // Asia/Macau
    'Asia/Macau'                       => array( 1,  8,  0, 'MO', 'CST',  '',     'PRC'),
    'Asia/Magadan'                     => array( 1, 11,  0, 'RU', 'MAGT', 'MAGST','Russia'),
    'Asia/Makassar'                    => array( 1,  8,  0, 'ID', 'CIT',  '',     ''),
    'Asia/Manila'                      => array( 1,  8,  0, 'PH', 'PHT',  '',     'Phil'),
    'Asia/Muscat'                      => array( 1,  4,  0, 'OM', 'GST',  '',     ''),
    'Asia/Nicosia'                     => array( 1,  2,  0, 'CY', 'EET',  'EEST', 'EUAsia'),
    'Asia/Novosibirsk'                 => array( 1,  6,  0, 'RU', 'NOVT', 'NOVST','Russia'),
    'Asia/Omsk'                        => array( 1,  6,  0, 'RU', 'OMST', 'OMSST','Russia'),
    'Asia/Oral'                        => array( 1,  4,  0, 'KZ', 'ORAT', 'ORAST','RussiaAsia'),
    'Asia/Phnom_Penh'                  => array( 1,  7,  0, 'KH', 'ICT',  '',     ''),
    'Asia/Pontianak'                   => array( 1,  7,  0, 'ID', 'WIT',  '',     ''),
    'Asia/Pyongyang'                   => array( 1,  9,  0, 'KP', 'KST',  '',     ''),
    'Asia/Qatar'                       => array( 1,  3,  0, 'QA', 'AST',  '',     ''),
    'Asia/Qyzylorda'                   => array( 1,  6,  0, 'KZ', 'QYZT', 'QYZST','RussiaAsia'),
    'Asia/Rangoon'                     => array( 1,  6, 30, 'MM', 'MMT',  '',     ''),
    'Asia/Riyadh'                      => array( 1,  3,  0, 'SA', 'AST',  '',     ''),
    'Asia/Riyadh87'                    => array( 1,  3,  7, '',   '',     '',     ''),       // 3:07:04
    'Asia/Riyadh88'                    => array( 1,  3,  7, '',   '',     '',     ''),       // 3:07:04
    'Asia/Riyadh89'                    => array( 1,  3,  7, '',   '',     '',     ''),       // 3:07:04
    'Asia/Saigon'                      => array( 1,  7,  0, 'VN', 'ICT',  '',     ''),
    'Asia/Sakhalin'                    => array( 1, 10,  0, 'RU', 'SAKT', 'SAKST','Russia'),
    'Asia/Samarkand'                   => array( 1,  5,  0, 'UZ', 'UZT',  '',     ''),
    'Asia/Seoul'                       => array( 1,  9,  0, 'KR', 'KST',  '',     'ROK'),
    'Asia/Shanghai'                    => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),
    'Asia/Singapore'                   => array( 1,  8,  0, 'SG', 'SGT',  '',     ''),
    'Asia/Taipei'                      => array( 1,  8,  0, 'TW', 'CST',  '',     'Taiwan'),
    'Asia/Tashkent'                    => array( 1,  5,  0, 'UZ', 'UZT',  '',     ''),
    'Asia/Tbilisi'                     => array( 1,  3,  0, 'GE', 'GET',  'GEST', 'RussiaAsia'),
    'Asia/Tehran'                      => array( 1,  3, 30, 'IR', 'IRST', 'IRDT', 'Iran'),
    'Asia/Tel_Aviv'                    => array( 1,  2,  0, 'IL', 'IST',  'IDT',  'Zion'),    // Asia/Jerusalem
    'Asia/Thimbu'                      => array( 1,  6,  0, 'BT', 'BTT',  '',     ''),        // Asia/Thimphu
    'Asia/Thimphu'                     => array( 1,  6,  0, 'BT', 'BTT',  '',     ''),
    'Asia/Tokyo'                       => array( 1,  9,  0, 'JP', 'JST',  '',     ''),
    'Asia/Ujung_Pandang'               => array( 1,  8,  0, 'ID', 'CIT',  '',     ''),        // Asia/Makassar
    'Asia/Ulaanbaatar'                 => array( 1,  8,  0, 'MN', 'ULAT', 'ULAST','Mongol'),
    'Asia/Ulan_Bator'                  => array( 1,  8,  0, 'MN', 'ULAT', 'ULAST','Mongol'),  // Asia/Ulaanbaatar
    'Asia/Urumqi'                      => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),
    'Asia/Vientiane'                   => array( 1,  7,  0, 'LA', 'ICT',  '',     ''),
    'Asia/Vladivostok'                 => array( 1, 10,  0, 'RU', 'VLAT', 'VLAST','Russia'),
    'Asia/Yakutsk'                     => array( 1,  9,  0, 'RU', 'YAKT', 'YAKST','Russia'),
    'Asia/Yekaterinburg'               => array( 1,  5,  0, 'RU', 'YEKT', 'YEKST','Russia'),
    'Asia/Yerevan'                     => array( 1,  4,  0, 'AM', 'AMT',  'AMST', 'RussiaAsia'),
    'Atlantic/Azores'                  => array(-1,  1,  0, 'PT', 'AZOT', 'AZOST','EU'),
    'Atlantic/Bermuda'                 => array(-1,  4,  0, 'BM', 'AST',  'ADT',  'Bahamas'),
    'Atlantic/Canary'                  => array( 1,  0,  0, 'ES', 'WET',  'WEST', 'EU'),
    'Atlantic/Cape_Verde'              => array(-1,  1,  0, 'CV', 'CVT',  '',     ''),
    'Atlantic/Faeroe'                  => array( 1,  0,  0, 'FO', 'WET',  'WEST', 'EU'),
    'Atlantic/Jan_Mayen'               => array( 1,  1,  0, 'SJ', 'CET',  'CEST', 'EU'),      // Europe/Oslo
    'Atlantic/Madeira'                 => array( 1,  0,  0, 'PT', 'WET',  'WEST', 'EU'),
    'Atlantic/Reykjavik'               => array( 1,  0,  0, 'IS', 'GMT',  '',     ''),
    'Atlantic/South_Georgia'           => array(-1,  2,  0, 'GS', 'GST',  '',     ''),
    'Atlantic/St_Helena'               => array( 1,  0,  0, 'SH', 'GMT',  '',     ''),
    'Atlantic/Stanley'                 => array(-1,  4,  0, 'FK', 'FKT',  'FKST', 'Falk'),
    'Australia/ACT'                    => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AN'),      // Australia/Sydney
    'Australia/Adelaide'               => array( 1,  9, 30, 'AU', 'CST',  'CST',  'AS'),
    'Australia/Brisbane'               => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AQ'),
    'Australia/Broken_Hill'            => array( 1,  9, 30, 'AU', 'CST',  'CST',  'AS'),
    'Australia/Canberra'               => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AN'),      // Australia/Sydney
    'Australia/Darwin'                 => array( 1,  9, 30, 'AU', 'CST',  'CST',  'Aus'),
    'Australia/Hobart'                 => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AT'),
    'Australia/LHI'                    => array( 1, 10, 30, 'AU', 'LHST', 'LHST', 'LH'),      // Australia/Lord_Howe
    'Australia/Lindeman'               => array( 1, 10,  0, 'AU', 'EST',  'EST',  'Holiday'),
    'Australia/Lord_Howe'              => array( 1, 10, 30, 'AU', 'LHST', 'LHST', 'LH'),
    'Australia/Melbourne'              => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AV'),
    'Australia/NSW'                    => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AN'),      // Australia/Sydney
    'Australia/North'                  => array( 1,  9, 30, 'AU', 'CST',  'CST',  'Aus'),     // Australia/Darwin
    'Australia/Perth'                  => array( 1,  8,  0, 'AU', 'WST',  '',     ''),
    'Australia/Queensland'             => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AQ'),      // Australia/Brisbane
    'Australia/South'                  => array( 1,  9, 30, 'AU', 'CST',  'CST',  'AS'),      // Australia/Adelaide
    'Australia/Sydney'                 => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AN'),
    'Australia/Tasmania'               => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AT'),      // Australia/Hobart
    'Australia/Victoria'               => array( 1, 10,  0, 'AU', 'EST',  'EST',  'AV'),      // Australia/Melbourne
    'Australia/West'                   => array( 1,  8,  0, 'AU', 'WST',  '',     ''),        // Australia/Perth
    'Australia/Yancowinna'             => array( 1,  9, 30, 'AU', 'CST',  'CST',  'AS'),      // Australia/Broken_Hill
    'Brazil/Acre'                      => array(-1,  5,  0, 'BR', 'ACT',  '',     ''),        // America/Porto_Acre
    'Brazil/DeNoronha'                 => array(-1,  2,  0, 'BR', 'FNT',  '',     ''),        // America/Noronha
    'Brazil/East'                      => array(-1,  3,  0, 'BR', 'BRT', 'BRST',  'Brazil'),  // America/Sao_Paulo
    'Brazil/West'                      => array(-1,  4,  0, 'BR', 'AMT',  '',     ''),        // America/Manaus
    'CET'                              => array( 1,  1,  0, '',   '',     '',     ''),        // Central European Time (CET)
    'CST6CDT'                          => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),      // America/Chicago
    'Canada/Atlantic'                  => array(-1,  4,  0, 'CA', 'AST',  'ADT',  'Canada'),  // America/Halifax
    'Canada/Central'                   => array(-1,  6,  0, 'CA', 'CST',  'CDT',  'Winn'),    // America/Winnipeg
    'Canada/East-Saskatchewan'         => array(-1,  6,  0, 'CA', 'CST',  '',     ''),        // America/Regina
    'Canada/Eastern'                   => array(-1,  5,  0, 'CA', 'EST',  'EDT',  'Canada'),  // America/Toronto
    'Canada/Mountain'                  => array(-1,  7,  0, 'CA', 'MST',  'MDT',  'Edm'),     // America/Edmonton
    'Canada/Newfoundland'              => array(-1,  3, 30, 'CA', 'NST',  'NDT',  'StJohns'), // America/St_Johns
    'Canada/Pacific'                   => array(-1,  8,  0, 'CA', 'PST',  'PDT',  'Vanc'),    // America/Vancouver
    'Canada/Saskatchewan'              => array(-1,  6,  0, 'CA', 'CST',  '',     ''),        // America/Regina
    'Canada/Yukon'                     => array(-1,  8,  0, 'CA', 'PST',  'PDT',  'NT_YK'),   // America/Whitehorse
    'Chile/Continental'                => array(-1,  4,  0, 'CL', 'CLT',  'CLST', 'Chile'),   // America/Santiago
    'Chile/EasterIsland'               => array(-1,  6,  0, 'CL', 'EAST', 'EASST','Chile'),   // Pacific/Easter
    'Cuba'                             => array(-1,  5,  0, 'CU', 'CST',  'CDT',  'Cuba'),    // America/Havana
    'EET'                              => array( 1,  2,  0, '',   'EET',  '',     ''),        // Eastern Europe Time (EET)
    'EST'                              => array(-1,  5,  0, '',   'EST',  '',     ''),
    'EST5EDT'                          => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),      // America/New_York
    'Egypt'                            => array( 1,  2,  0, 'EG', 'EET',  'EEST', 'Egypt'),   // Africa/Cairo
    'Eire'                             => array( 1,  0,  0, 'IE', 'GMT',  'IST',  ''),        // Europe/Dublin
    'Etc/GMT'                          => array( 1,  0,  0, '',   'GMT',  '',     ''),
    'Etc/GMT+0'                        => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT
    'Etc/GMT+1'                        => array(-1,  1,  0, '',   'GMT+0','',     ''),
    'Etc/GMT+10'                       => array(-1, 10,  0, '',   'GMT+10','',    ''),
    'Etc/GMT+11'                       => array(-1, 11,  0, '',   'GMT+11','',    ''),
    'Etc/GMT+12'                       => array(-1, 12,  0, '',   'GMT+12','',    ''),
    'Etc/GMT+2'                        => array(-1,  2,  0, '',   'GMT+2','',     ''),
    'Etc/GMT+3'                        => array(-1,  3,  0, '',   'GMT+3','',     ''),
    'Etc/GMT+4'                        => array(-1,  4,  0, '',   'GMT+4','',     ''),
    'Etc/GMT+5'                        => array(-1,  5,  0, '',   'GMT+5','',     ''),
    'Etc/GMT+6'                        => array(-1,  6,  0, '',   'GMT+6','',     ''),
    'Etc/GMT+7'                        => array(-1,  7,  0, '',   'GMT+7','',     ''),
    'Etc/GMT+8'                        => array(-1,  8,  0, '',   'GMT+8','',     ''),
    'Etc/GMT+9'                        => array(-1,  9,  0, '',   'GMT+9','',     ''),
    'Etc/GMT-0'                        => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT
    'Etc/GMT-1'                        => array( 1,  1,  0, '',   'GMT-1','',     ''),
    'Etc/GMT-10'                       => array( 1, 10,  0, '',   'GMT-10','',    ''),
    'Etc/GMT-11'                       => array( 1, 11,  0, '',   'GMT-11','',    ''),
    'Etc/GMT-12'                       => array( 1, 12,  0, '',   'GMT-12','',    ''),
    'Etc/GMT-13'                       => array( 1, 13,  0, '',   'GMT-13','',    ''),
    'Etc/GMT-14'                       => array( 1, 14,  0, '',   'GMT-14','',    ''),
    'Etc/GMT-2'                        => array( 1,  2,  0, '',   'GMT-2','',     ''),
    'Etc/GMT-3'                        => array( 1,  3,  0, '',   'GMT-3','',     ''),
    'Etc/GMT-4'                        => array( 1,  4,  0, '',   'GMT-4','',     ''),
    'Etc/GMT-5'                        => array( 1,  5,  0, '',   'GMT-5','',     ''),
    'Etc/GMT-6'                        => array( 1,  6,  0, '',   'GMT-6','',     ''),
    'Etc/GMT-7'                        => array( 1,  7,  0, '',   'GMT-7','',     ''),
    'Etc/GMT-8'                        => array( 1,  8,  0, '',   'GMT-8','',     ''),
    'Etc/GMT-9'                        => array( 1,  9,  0, '',   'GMT-9','',     ''),
    'Etc/GMT0'                         => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT
    'Etc/Greenwich'                    => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT
    'Etc/UCT'                          => array( 1,  0,  0, '',   'GMT',  '',     ''),
    'Etc/UTC'                          => array( 1,  0,  0, '',   'UTC',  '',     ''),
    'Etc/Universal'                    => array( 1,  0,  0, '',   'UTC',  '',     ''),        // Etc/UTC
    'Etc/Zulu'                         => array( 1,  0,  0, '',   'UTC',  '',     ''),        // Etc/UTC
    'Europe/Amsterdam'                 => array( 1,  1,  0, 'NL', 'CET',  'CEST', 'EU'),
    'Europe/Andorra'                   => array( 1,  1,  0, 'AD', 'CET',  'CEST', 'EU'),
    'Europe/Athens'                    => array( 1,  2,  0, 'GR', 'EET',  'EEST', 'EU'),
    'Europe/Belfast'                   => array( 1,  0,  0, 'GB', 'GMT',  'IST',  'EU'),
    'Europe/Belgrade'                  => array( 1,  1,  0, 'CS', 'CET',  'CEST', 'EU'),
    'Europe/Berlin'                    => array( 1,  1,  0, 'DE', 'CET',  'CEST', 'EU'),
    'Europe/Bratislava'                => array( 1,  1,  0, 'SK', 'CET',  'CEST', 'EU'),      // Europe/Prague
    'Europe/Brussels'                  => array( 1,  1,  0, 'BE', 'CET',  'CEST', 'EU'),
    'Europe/Bucharest'                 => array( 1,  2,  0, 'RO', 'EET',  'EEST', 'EU'),
    'Europe/Budapest'                  => array( 1,  1,  0, 'HU', 'CET',  'CEST', 'EU'),
    'Europe/Chisinau'                  => array( 1,  2,  0, 'MD', 'EET',  'EEST', 'EU'),
    'Europe/Copenhagen'                => array( 1,  1,  0, 'DK', 'WET',  'WEST', 'EU'),
    'Europe/Dublin'                    => array( 1,  0,  0, 'IE', 'GMT',  'IST',  ''),
    'Europe/Gibraltar'                 => array( 1,  1,  0, 'GI', 'CET',  'CEST', 'EU'),
    'Europe/Helsinki'                  => array( 1,  2,  0, 'FI', 'EET',  'EEST', 'EU'),
    'Europe/Istanbul'                  => array( 1,  2,  0, 'TR', 'EET',  'EEST', 'EU'),
    'Europe/Kaliningrad'               => array( 1,  2,  0, 'RU', 'EET',  'EEST', 'Russia'),
    'Europe/Kiev'                      => array( 1,  2,  0, 'UA', 'EET',  'EEST', 'EU'),
    'Europe/Lisbon'                    => array( 1,  0,  0, 'PT', 'WET',  'WEST', 'EU'),
    'Europe/Ljubljana'                 => array( 1,  1,  0, 'SI', 'CET',  'CEST', 'EU'),      // Europe/Belgrade
    'Europe/London'                    => array( 1,  0,  0, 'GB', 'GMT',  'BST',  'EU'),
    'Europe/Luxembourg'                => array( 1,  1,  0, 'LU', 'CET',  'CEST', 'EU'),
    'Europe/Madrid'                    => array( 1,  1,  0, 'ES', 'CET',  'CEST', 'EU'),
    'Europe/Malta'                     => array( 1,  1,  0, 'MT', 'CET',  'CEST', 'EU'),
    'Europe/Mariehamn'                 => array( 1,  2,  0, 'TR', 'EET',  'EEST', 'EU'),      // Europe/Istanbul
    'Europe/Minsk'                     => array( 1,  2,  0, 'BY', 'EET',  'EEST', 'Russia'),
    'Europe/Monaco'                    => array( 1,  1,  0, 'MC', 'CET',  'CEST', 'EU'),
    'Europe/Moscow'                    => array( 1,  3,  0, 'RU', 'MSK',  'MSD',  'Russia'),
    'Europe/Nicosia'                   => array( 1,  2,  0, 'CY', 'EET',  'EEST', 'EUAsia'),  // Asia/Nicosia
    'Europe/Oslo'                      => array( 1,  1,  0, 'NO', 'CET',  'CEST', 'EU'),
    'Europe/Paris'                     => array( 1,  1,  0, 'FR', 'CET',  'CEST', 'EU'),
    'Europe/Prague'                    => array( 1,  1,  0, 'CZ', 'CET',  'CEST', 'EU'),
    'Europe/Riga'                      => array( 1,  2,  0, 'LV', 'EET',  'EEST', 'EU'),
    'Europe/Rome'                      => array( 1,  1,  0, 'IT', 'CET',  'CEST', 'EU'),
    'Europe/Samara'                    => array( 1,  4,  0, 'RU', 'SAMT', 'SAMST','Russia'),
    'Europe/San_Marino'                => array( 1,  1,  0, 'SM', 'CET',  'CEST', 'EU'),      // Europe/Rome
    'Europe/Sarajevo'                  => array( 1,  1,  0, 'BA', 'CET',  'CEST', 'EU'),      // Europe/Belgrade
    'Europe/Simferopol'                => array( 1,  2,  0, 'UA', 'EET',  'EEST', 'EU'),
    'Europe/Skopje'                    => array( 1,  1,  0, 'MK', 'CET',  'CEST', 'EU'),      // Europe/Belgrade
    'Europe/Sofia'                     => array( 1,  2,  0, 'BG', 'EET',  'EEST', 'EU'),
    'Europe/Stockholm'                 => array( 1,  1,  0, 'SE', 'CET',  'CEST', 'EU'),
    'Europe/Tallinn'                   => array( 1,  2,  0, 'EE', 'EET',  'EEST', 'EU'),
    'Europe/Tirane'                    => array( 1,  1,  0, 'AL', 'CET',  'CEST', 'EU'),
    'Europe/Tiraspol'                  => array( 1,  2,  0, 'MD', 'EET',  'EEST', 'EU'),      // Europe/Chisinau
    'Europe/Uzhgorod'                  => array( 1,  2,  0, 'UA', 'EET',  'EEST', 'EU'),
    'Europe/Vaduz'                     => array( 1,  1,  0, 'LI', 'CET',  'CEST', 'EU'),
    'Europe/Vatican'                   => array( 1,  1,  0, 'VA', 'CET',  'CEST', 'EU'),      // Europe/Rome
    'Europe/Vienna'                    => array( 1,  1,  0, 'AT', 'CET',  'CEST', 'EU'),
    'Europe/Vilnius'                   => array( 1,  2,  0, 'LT', 'EET',  'EEST', 'EU'),
    'Europe/Warsaw'                    => array( 1,  1,  0, 'PL', 'CET',  'CEST', 'EU'),
    'Europe/Zagreb'                    => array( 1,  1,  0, 'HR', 'CET',  'CEST', 'EU'),      // Europe/Belgrade
    'Europe/Zaporozhye'                => array( 1,  2,  0, 'UA', 'EET',  'EEST', 'EU'),
    'Europe/Zurich'                    => array( 1,  1,  0, 'CH', 'CET',  'CEST', 'EU'),
    'Factory'                          => array( 1,  0,  0, '',   '',     '',     ''),
    'GB'                               => array( 1,  0,  0, 'GB', 'GMT',  'BST',  'EU'),      // Europe/London
    'GB-Eire'                          => array( 1,  0,  0, 'GB', 'GMT',  'BST',  'EU'),      // Europe/London
    'GMT'                              => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT
    'GMT+0'                            => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT+0
    'GMT-0'                            => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT-0
    'GMT0'                             => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/GMT0
    'Greenwich'                        => array( 1,  0,  0, '',   'GMT',  '',     ''),        // Etc/Greenwich
    'HST'                              => array(-1, 10,  0, 'US', 'HST',  '',     ''),
    'Hongkong'                         => array( 1,  8,  0, 'HK', 'HKT',  '',     'HK'),      // Asia/Hong_Kong
    'Iceland'                          => array( 1,  0,  0, 'IS', 'GMT',  '',     ''),        // Atlantic/Reykjavik
    'Indian/Antananarivo'              => array( 1,  3,  0, 'MG', 'EAT',  '',     ''),
    'Indian/Chagos'                    => array( 1,  6,  0, 'IO', 'IOT',  '',     ''),
    'Indian/Christmas'                 => array( 1,  7,  0, 'CX', 'CXT',  '',     ''),
    'Indian/Cocos'                     => array( 1,  6, 30, 'CC', 'CCT',  '',     ''),
    'Indian/Comoro'                    => array( 1,  3,  0, 'KM', 'EAT',  '',     ''),
    'Indian/Kerguelen'                 => array( 1,  5,  0, 'TF', 'TFT',  '',     ''),
    'Indian/Mahe'                      => array( 1,  4,  0, 'SC', 'SCT',  '',     ''),
    'Indian/Maldives'                  => array( 1,  5,  0, 'MV', 'MVT',  '',     ''),
    'Indian/Mauritius'                 => array( 1,  4,  0, 'MU', 'MUT',  '',     ''),
    'Indian/Mayotte'                   => array( 1,  3,  0, 'YT', 'EAT',  '',     ''),
    'Indian/Reunion'                   => array( 1,  4,  0, 'RE', 'RET',  '',     ''),
    'Iran'                             => array( 1,  3, 30, 'IR', 'IRST', 'IRDT', 'Iran'),    // Asia/Tehran
    'Israel'                           => array( 1,  2,  0, 'IL', 'IST',  'IDT',  'Zion'),    // Asia/Jerusalem
    'Jamaica'                          => array(-1,  5,  0, 'JM', 'EST',  '',     ''),        // America/Jamaica
    'Japan'                            => array( 1,  9,  0, 'JP', 'JST',  '',     ''),        // Asia/Tokyo
    'Kwajalein'                        => array( 1, 12,  0, 'MH', 'MHT',  '',     ''),        // Pacific/Kwajalein
    'Libya'                            => array( 1,  2,  0, 'LY', 'EET',  '',     ''),        // Africa/Tripoli
    'MET'                              => array( 1,  1,  0, '',   'MET',  'MEST', 'C-Eur'),
    'MST'                              => array(-1,  7,  0, 'US', 'MST',  '',     ''),        // America/Phoenix
    'MST7MDT'                          => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),      // America/Denver
    'Mexico/BajaNorte'                 => array(-1,  8,  0, 'MX', 'PST',  'PDT',  'Mexico'),  // America/Tijuana
    'Mexico/BajaSur'                   => array(-1,  7,  0, 'MX', 'MST',  'MDT',  'Mexico'),  // America/Mazatlan
    'Mexico/General'                   => array(-1,  6,  0, 'MX', 'CST',  'CDT',  'Mexico'),  // America/Mexico_City
    'Mideast/Riyadh87'                 => array( 1,  3,  7, '',   '',     '',     ''),        // 3:07:04
    'Mideast/Riyadh88'                 => array( 1,  3,  7, '',   '',     '',     ''),        // 3:07:04
    'Mideast/Riyadh89'                 => array( 1,  3,  7, '',   '',     '',     ''),        // 3:07:04
    'NZ'                               => array( 1, 12,  0, 'NZ', 'NZST', 'NZDT', 'NZ'),      // Pacific/Auckland
    'NZ-CHAT'                          => array( 1, 12, 45, 'NZ', 'CHAST','CHADT','Chatham'), // Pacific/Chatham
    'Navajo'                           => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),      // America/Shiprock
    'PRC'                              => array( 1,  8,  0, 'CN', 'CST',  '',     'PRC'),     // Asia/Shanghai
    'PST8PDT'                          => array(-1,  8,  0, 'US', 'PST',  'PDT',  'US'),      // America/Los_Angeles
    'Pacific/Apia'                     => array(-1, 11,  0, 'WS', 'WST',  '',     ''),
    'Pacific/Auckland'                 => array( 1, 12,  0, 'NZ', 'NZST', 'NZDT', 'NZ'),
    'Pacific/Chatham'                  => array( 1, 12, 45, 'NZ', 'CHAST','CHADT','Chatham'),
    'Pacific/Easter'                   => array(-1,  6,  0, 'CL', 'EAST', 'EASST','Chile'),
    'Pacific/Efate'                    => array( 1, 11,  0, 'VU', 'VUT',  '',     'Vanuatu'),
    'Pacific/Enderbury'                => array( 1, 13,  0, 'KI', 'PHOT', '',     ''),
    'Pacific/Fakaofo'                  => array(-1, 10,  0, 'TK', 'TKT',  '',     ''),
    'Pacific/Fiji'                     => array( 1, 12,  0, 'FJ', 'FJT',  '',     'Fiji'),
    'Pacific/Funafuti'                 => array( 1, 12,  0, 'TV', 'TVT',  '',     ''),
    'Pacific/Galapagos'                => array(-1,  6,  0, 'EC', 'GALT', '',     ''),
    'Pacific/Gambier'                  => array(-1,  9,  0, 'PF', 'GAMT', '',     ''),
    'Pacific/Guadalcanal'              => array( 1, 11,  0, 'SB', 'SBT',  '',     ''),
    'Pacific/Guam'                     => array( 1, 10,  0, 'GU', 'ChST', '',     ''),
    'Pacific/Honolulu'                 => array(-1, 10,  0, 'US', 'HST',  '',     ''),
    'Pacific/Johnston'                 => array(-1, 10,  0, 'UM', 'HST',  '',     ''),
    'Pacific/Kiritimati'               => array( 1, 14,  0, 'KI', 'LINT', '',     ''),
    'Pacific/Kosrae'                   => array( 1, 11,  0, 'FM', 'KOST', '',     ''),
    'Pacific/Kwajalein'                => array( 1, 12,  0, 'MH', 'MHT',  '',     ''),
    'Pacific/Majuro'                   => array( 1, 12,  0, 'MH', 'MHT',  '',     ''),
    'Pacific/Marquesas'                => array(-1,  9, 30, 'PF', 'MART', '',     ''),
    'Pacific/Midway'                   => array(-1, 11,  0, 'UM', 'SST',  '',     ''),
    'Pacific/Nauru'                    => array( 1, 12,  0, 'NR', 'NRT',  '',     ''),
    'Pacific/Niue'                     => array(-1, 11,  0, 'NU', 'NUT',  '',     ''),
    'Pacific/Norfolk'                  => array( 1, 11, 30, 'NF', 'NFT',  '',     ''),
    'Pacific/Noumea'                   => array( 1, 11,  0, 'NC', 'NCT',  '',     'NC'),
    'Pacific/Pago_Pago'                => array(-1, 11,  0, 'AS', 'SST',  '',     ''),
    'Pacific/Palau'                    => array( 1,  9,  0, 'PW', 'PWT',  '',     ''),
    'Pacific/Pitcairn'                 => array(-1,  8,  0, 'PN', 'PST',  '',     ''),
    'Pacific/Ponape'                   => array( 1, 11,  0, 'FM', 'PONT', '',     ''),
    'Pacific/Port_Moresby'             => array( 1, 10,  0, 'PG', 'PGT',  '',     ''),
    'Pacific/Rarotonga'                => array(-1, 10,  0, 'CK', 'CKT',  '',     'Cook'),
    'Pacific/Saipan'                   => array( 1, 10,  0, 'MP', 'ChST', '',     ''),
    'Pacific/Samoa'                    => array(-1, 11,  0, 'AS', 'SST',  '',     ''),       // Pacific/Pago_Pago
    'Pacific/Tahiti'                   => array(-1, 10,  0, 'PF', 'TAHT', '',     ''),
    'Pacific/Tarawa'                   => array( 1, 12,  0, 'KI', 'GILT', '',     ''),
    'Pacific/Tongatapu'                => array( 1, 13,  0, 'TO', 'TOT',  '',     'Tonga'),
    'Pacific/Truk'                     => array( 1, 10,  0, 'FM', 'TRUT', '',     ''),
    'Pacific/Wake'                     => array( 1, 12,  0, 'UM', 'WAKT', '',     ''),
    'Pacific/Wallis'                   => array( 1, 12,  0, 'WF', 'WFT',  '',     ''),
    'Pacific/Yap'                      => array( 1, 10,  0, 'FM', 'YAPT', '',     ''),
    'Poland'                           => array( 1,  1,  0, 'PL', 'CET',  'CEST', 'EU'),     // Europe/Warsaw
    'Portugal'                         => array( 1,  0,  0, 'PT', 'WET',  'WEST', 'EU'),     // Europe/Lisbon
    'ROC'                              => array( 1,  8,  0, 'TW', 'CST',  '',     'Taiwan'), // Asia/Taipei
    'ROK'                              => array( 1,  9,  0, 'KR', 'KST',  '',     'ROK'),    // Asia/Seoul
    'Singapore'                        => array( 1,  8,  0, 'SG', 'SGT',  '',     ''),       // Asia/Singapore
    'SystemV/AST4'                     => array(-1,  4,  0, 'PR', 'AST',  '',     ''),       // America/Puerto_Rico
    'SystemV/AST4ADT'                  => array(-1,  4,  0, 'CA', 'AST',  'ADT',  'Canada'), // America/Halifax
    'SystemV/CST6'                     => array(-1,  6,  0, 'CA', 'CST',  '',     ''),       // America/Regina
    'SystemV/CST6CDT'                  => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),     // America/Chicago
    'SystemV/EST5'                     => array(-1,  5,  0, 'US', 'EST',  '',     ''),       // America/Indianapolis
    'SystemV/EST5EDT'                  => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),     // America/New_York
    'SystemV/HST10'                    => array(-1, 10,  0, 'US', 'HST',  '',     ''),       // Pacific/Honolulu
    'SystemV/MST7'                     => array(-1,  7,  0, 'US', 'MST',  '',     ''),       // America/Phoenix
    'SystemV/MST7MDT'                  => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),     // America/Denver
    'SystemV/PST8'                     => array(-1,  8,  0, 'PN', 'PST',  '',     ''),       // Pacific/Pitcairn
    'SystemV/PST8PDT'                  => array(-1,  8,  0, 'US', 'PST',  'PDT',  'US'),     // America/Los_Angeles
    'SystemV/YST9'                     => array(-1,  9,  0, 'PF', 'GAMT', '',     ''),       // Pacific/Gambier
    'SystemV/YST9YDT'                  => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'US'),     // America/Anchorage
    'Turkey'                           => array( 1,  2,  0, 'TR', 'EET',  'EEST', 'EU'),     // Europe/Istanbul
    'UCT'                              => array( 1,  0,  0, '',   '',     '',     ''),       // Etc/UCT
    'US/Alaska'                        => array(-1,  9,  0, 'US', 'AKST', 'AKDT', 'US'),     // America/Anchorage
    'US/Aleutian'                      => array(-1, 10,  0, 'US', 'HAST', 'HADT', 'US'),     // America/Adak
    'US/Arizona'                       => array(-1,  7,  0, 'US', 'MST',  '',     ''),       // America/Phoenix
    'US/Central'                       => array(-1,  6,  0, 'US', 'CST',  'CDT',  'US'),     // America/Chicago
    'US/East-Indiana'                  => array(-1,  5,  0, 'US', 'EST',  '',     ''),       // America/Indianapolis
    'US/Eastern'                       => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),     // America/New_York
    'US/Hawaii'                        => array(-1, 10,  0, 'US', 'HST',  '',     ''),       // Pacific/Honolulu
    'US/Indiana-Starke'                => array(-1,  5,  0, 'US', 'EST',  '',     ''),       // America/Indiana/Knox
    'US/Michigan'                      => array(-1,  5,  0, 'US', 'EST',  'EDT',  'US'),     // America/Detroit
    'US/Mountain'                      => array(-1,  7,  0, 'US', 'MST',  'MDT',  'US'),     // America/Denver
    'US/Pacific'                       => array(-1,  8,  0, 'US', 'PST',  'PDT',  'US'),     // America/Los_Angeles
    'US/Pacific-New'                   => array(-1,  8,  0, 'US', 'PST',  'PDT',  'US'),     // America/Los_Angeles
    'US/Samoa'                         => array(-1, 11,  0, 'AS', 'SST',  '',     ''),       // Pacific/Pago_Pago
    'UTC'                              => array( 1,  0,  0, '',   'UTC',  '',     ''),       // Etc/UTC
    'Universal'                        => array( 1,  0,  0, '',   'UTC',  '',     ''),       // Etc/Universal
    'W-SU'                             => array( 1,  3,  0, 'RU', 'MSK',  'MSD',  'Russia'), // Europe/Moscow
    'WET'                              => array( 1,  0,  0, '',   'WET',  '',     ''),       // WET:Western Europe
    'Zulu'                             => array( 1,  0,  0, '',   'UTC',  '',     ''),       // Etc/Zulu
  );

  var $dst = array(
    //    0            1      2    3   4           5  6   7   8
    //    Rule         Start  End  MM, Week,       H, i  +M, S/D
    array('AN',        1996, 9999,  5, 'lastSun',  2, 0,  0, ''),
    array('AN',        2001, 9999, 10, 'lastSun',  2, 0, 60, ''),
    array('AQ',        1990, 1992,  5,  'Sun>=1',  2, 0,  0, ''), // s
    array('AQ',        1989, 1991, 10, 'lastSun',  2, 0, 60, ''), // s
    array('AS',        1995, 9999,  5, 'lastSun',  2, 0,  0, ''), // s
    array('AS',        1987, 9999, 10, 'lastSun',  2, 0, 60, ''), // s
    array('AT',        1991, 9999,  5, 'lastSun',  2, 0,  0, ''), // s
    array('AT',        2001, 9999, 10,  'Sun>=1',  2, 0, 60, ''), // s
    array('Aus',       1943, 1944,  5, 'lastSun',  2, 0,  0, ''),
    array('Aus',       1943, 1943, 10,         3,  2, 0, 60, ''),
    array('AV',        1995, 9999,  5, 'lastSun',  2, 0,  0, ''), // s
    array('AV',        2001, 9999, 10, 'lastSun',  2, 0, 60, ''), // s
    array('Azer',      1997, 9999,  3, 'lastSun',  1, 0, 60, 'S'),
    array('Azer',      1997, 9999, 10, 'lastSun',  1, 0,  0, ''),
    array('Barb',      1978, 1980,  4, 'Sun>=15',  2, 0, 60, 'D'),
    array('Barb',      1980, 1980,  9,        25,  2, 0,  0, 'S'),
    array('Bahamas',   1964, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Bahamas',   1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Belize',    1982, 1982, 12,        18,  0, 0, 60, 'D'),
    array('Belize',    1983, 1983,  2,        12,  0, 0,  0, 'S'),
    array('Brazil',    2001, 9999,  2, 'Sun>=15',  0, 0,  0, ''),
    array('Brazil',    2005, 9999, 10, 'Sun>=15',  0, 0, 60, 'S'),
    array('Canada',    1974, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Canada',    1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Chatham',   1990, 9999,  3, 'Sun>=15',  2,45,  0, 'S'), // s
    array('Chatham',   1990, 9999, 10,  'Sun>=1',  2,45, 60, 'D'), // s
    array('ChileAQ',   1999, 9999, 10,  'Sun>=9',  0, 0, 60, 'S'),
    array('ChileAQ',   2000, 9999,  3,  'Sun>=9',  0, 0,  0, ''),
    array('Chile',     1999, 9999, 10,  'Sun>=9',  4, 0, 60, 'S'),
    array('Chile',     2000, 9999,  3,  'Sun>=9',  3, 0,  0, ''),
    array('CR',        1991, 1992,  1, 'Sat>=15',  0, 0, 60, 'D'),
    array('CR',        1992, 1992,  3,        15,  0, 0,  0, 'S'),
    array('CO',        1992, 1992,  5,         2,  0, 0, 60, 'S'),
    array('CO',        1992, 1992, 12,        31,  0, 0,  0, ''),
    array('Cook',      1979, 1991,  5,  'Sun>=1',  0, 0,  0, ''),
    array('Cook',      1979, 1990, 10, 'lastSun',  0, 0, 30, 'HS'),
    array('Cuba',      1998, 9999, 10, 'lastSun',  0, 0,  0, 'S'), // s
    array('Cuba',      2000, 9999,  4,  'Sun>=1',  0, 0, 60, 'D'), // s
    array('C-Eur',     1981, 9999,  3, 'lastSun',  2, 0, 60, 'S'), // s
    array('C-Eur',     1996, 9999, 10, 'lastSun',  2, 0,  0, ''), // s
    array('Egypt',     1995, 9999,  4, 'lastFri',  0, 0, 60, 'S'), // s
    array('Egypt',     1995, 9999,  9, 'lastThu', 23,0, 0, ''), // s
    array('EU',        1981, 9999,  3, 'lastSun',  1, 0, 60, 'S'), // u
    array('EU',        1996, 9999, 10, 'lastSun',  1, 0,  0, ''), // u
    array('Edm',       1972, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Edm',       1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('EUAsia',    1981, 9999,  3, 'lastSun',  1, 0, 60, 'S'), // u
    array('EUAsia',    1996, 9999, 10, 'lastSun',  1, 0,  0, ''),  // u
    array('Falk',      2001, 9999,  4, 'Sun>=15',  2, 0,  0, ''),
    array('Falk',      2001, 9999,  9,  'Sun>=1',  2, 0, 60, 'S'),
    array('Fiji',      1998, 1999, 11,  'Sun>=1',  2, 0, 60, 'S'),
    array('Fiji',      1999, 2000,  2, 'lastSun',  3, 0,  0, ''),
    array('Guat',      1991, 1991,  3,        23,  0, 0, 60, 'D'),
    array('Guat',      1991, 1991,  9,         7,  0, 0,  0, 'S'),
    array('Haiti',     1988, 1997,  4,  'Sun>=1',  1, 0, 60, 'D'), // s
    array('Haiti',     1988, 1997, 10, 'lastSun',  1, 0,  0, 'S'), // s
    array('HK',        1979, 1980,  5,  'Sun>=8',  3,30, 60, 'S'),
    array('HK',        1979, 1980, 10, 'Sun>=16',  3,30,  0, ''),
    array('Holiday',   1992, 1993, 10, 'lastSun',  2, 0, 60, ''),
    array('Holiday',   1993, 1994,  5,  'Sun>=1',  2, 0,  0, ''),
    array('Iraq',      1991, 9999,  4,         1,  3, 0, 60, 'D'), // s
    array('Iraq',      1991, 9999, 10,         1,  3, 0,  0, 'S'), // s
    array('Iran',      2005, 2007,  3,        22,  0, 0, 60, 'D'),
    array('Iran',      2005, 2007,  9,        22,  0, 0,  0, 'S'),
    array('Iran',      2008, 2008,  3,        21,  0, 0, 60, 'D'),
    array('Iran',      2008, 2008,  9,        21,  0, 0,  0, 'S'),
    array('Iran',      2009, 2011,  3,        22,  0, 0, 60, 'D'),
    array('Iran',      2009, 2011,  9,        22,  0, 0,  0, 'S'),
    array('Iran',      2012, 2012,  3,        21,  0, 0, 60, 'D'),
    array('Iran',      2012, 2012,  9,        21,  0, 0,  0, 'S'),
    array('Iran',      2013, 2015,  3,        22,  0, 0, 60, 'D'),
    array('Iran',      2013, 2015,  9,        22,  0, 0,  0, 'S'),
    array('Iran',      2016, 2016,  3,        21,  0, 0, 60, 'D'),
    array('Iran',      2016, 2016,  9,        21,  0, 0,  0, 'S'),
    array('Iran',      2017, 2019,  3,        22,  0, 0, 60, 'D'),
    array('Iran',      2017, 2019,  9,        22,  0, 0,  0, 'S'),
    array('Iran',      2020, 2020,  3,        21,  0, 0, 60, 'D'),
    array('Iran',      2020, 2020,  9,        21,  0, 0,  0, 'S'),
    array('Jordan',    1999, 9999,  9, 'lastThu',  0, 0,  0, ''),  // s
    array('Jordan',    2000, 9999,  3, 'lastThu',  0, 0, 60, 'S'), // s
    array('Kirgiz',    1997, 9999,  3, 'lastSun',  2,30, 60, 'S'),
    array('Kirgiz',    1997, 9999, 10, 'lastSun',  2,30,  0, ''),
    array('Lebanon',   1993, 9999,  3, 'lastSun',  0, 0, 60, 'S'),
    array('Lebanon',   1999, 9999, 10, 'lastSun',  0, 0,  0, ''),
    array('LH',        1996, 9999,  5, 'lastSun',  2, 0,  0, ''),
    array('LH',        2001, 9999, 10, 'lastSun',  2, 0, 30, ''),
    array('Mexico',    2002, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Mexico',    2002, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Mongol',    2001, 9999,  9, 'lastSat',  2, 0,  0, ''),
    array('Mongol',    2002, 9999,  3, 'lastSat',  2, 0, 60, 'S'),
    array('Namibia',   1994, 9999,  9,  'Sun>=1',  2, 0, 60, 'S'),
    array('Namibia',   1995, 9999,  4,  'Sun>=1',  2, 0,  0, ''),
    array('NC',        1996, 1996, 12,         1,  2, 0, 60, 'S'),
    array('NC',        1997, 1997,  3,         2,  2, 0,  0, ''),
    array('NT_YK',     1980, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('NT_YK',     1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('NZAQ',      1990, 9999, 10,  'Sun>=1',  2, 0, 60, 'D'),
    array('NZAQ',      1990, 9999,  3, 'Sun>=15',  2, 0,  0, 'S'),
    array('NZ',        1990, 9999,  3, 'Sun>=15',  2, 0,  0, 'S'), // s
    array('NZ',        1990, 9999, 10,  'Sun>=1',  2, 0, 60, 'D'), // s
    array('Pakistan',  2002, 2002,  4,  'Sun>=2',  0, 1, 60, 'S'),
    array('Pakistan',  2002, 2002, 10,  'Sun>=2',  0, 1,  0, ''),
    array('Para',      2002, 9999,  4,  'Sun>=1',  0, 0,  0, ''),
    array('Para',      2002, 9999,  9,  'Sun>=1',  0, 0, 60, 'S'),
    array('Peru',      1994, 1994,  1,         1,  0, 0, 60, 'S'),
    array('Peru',      1994, 1994,  4,         1,  0, 0,  0, ''),
    array('PRC',       1986, 1991,  9, 'Sun>=11',  0, 0,  0, 'S'),
    array('PRC',       1987, 1991,  4, 'Sun>=10',  0, 0, 60, 'D'),
    array('Palestine', 1999, 9999,  4, 'Fri>=15',  0, 0, 60, 'S'),
    array('Palestine', 1999, 9999, 10, 'Fri>=15',  0, 0,  0, ''),
    array('Phil',      1978, 1978,  3,        22,  0, 0, 60, 'S'),
    array('Phil',      1978, 1978,  9,        21,  0, 0,  0, ''),
    array('RussiaAsia',1993, 9999,  3, 'lastSun',  2, 0, 60, 'S'), // s
    array('RussiaAsia',1996, 9999, 10, 'lastSun',  2, 0,  0, ''),  // s
    array('Russia',    1993, 9999,  3, 'lastSun',  2, 0, 60, 'S'), // s
    array('Russia',    1996, 9999, 10, 'lastSun',  2, 0,  0, ''),  // s
    array('ROK',       1987, 1988,  5, 'Sun<=14',  0, 0, 60, 'D'),
    array('ROK',       1987, 1988, 10, 'Sun<=14',  0, 0,  0, 'S'),
    array('Salv',      1987, 1988,  5,  'Sun>=1',  0, 0, 60, 'D'),
    array('Salv',      1987, 1988,  9, 'lastSun',  0, 0,  0, 'S'),
    array('SA',        1942, 1943,  9, 'Sun>=15',  2, 0, 60, ''),
    array('SA',        1943, 1944,  3, 'Sun>=15',  2, 0,  0, ''),
    array('SL',        1957, 1962,  6,         1,  0, 0, 60, 'SLST'),
    array('SL',        1957, 1962,  9,         1,  0, 0,  0, 'GMT'),
    array('StJohns',   1987, 9999, 10, 'lastSun',  0, 1,  0, 'S'),
    array('StJohns',   1989, 9999,  4,  'Sun>=1',  0, 1, 60, 'D'),
    array('Syria',     1994, 9999, 10,         1,  0, 0,  0, ''),
    array('Syria',     1999, 9999,  4,         1,  0, 0, 60, 'S'),
    array('TC',        1979, 9999, 10, 'lastSun',  0, 0,  0, 'S'),
    array('TC',        1987, 9999,  4,  'Sun>=1',  0, 0, 60, 'D'),
    array('Tunisia',   1988, 1990,  9, 'lastSun',  0, 0,  0, ''),  // s
    array('Tunisia',   1990, 1990,  5,         1,  0, 0, 60, 'S'), // s
    array('Thule',     1993, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Thule',     1993, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Taiwan',    1980, 1980,  6,        30,  0, 0, 60, 'D'),
    array('Taiwan',    1980, 1980,  9,        30,  0, 0,  0, 'S'),
    array('Tonga',     2001, 2002,  1, 'lastSun',  2, 0,  0, ''),
    array('Tonga',     2000, 2001, 11,  'Sun>=1',  2, 0, 60, 'S'),
    array('US',        1967, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('US',        1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Uruguay',   2004, 2004,  9, 'Sun>=15',  0, 0, 60, 'S'),
    array('Uruguay',   2005, 2005,  3,  'Sun>=8',  0, 0,  0, ''),
    array('Vanc',      1962, 9999, 10, 'lastSun',  2, 0,  0, 'S'),
    array('Vanc',      1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Vanuatu',   1992, 1993,  1, 'Sun>=23',  0, 0,  0, ''),
    array('Vanuatu',   1992, 1992, 10, 'Sun>=23',  0, 0, 60, 'S'),
    array('Winn',      1987, 9999,  4,  'Sun>=1',  2, 0, 60, 'D'),
    array('Winn',      1987, 9999, 10, 'lastSun',  2, 0,  0, 'S'), // s
    array('Zion',      2005, 9999,  4,         1,  1, 0, 60, 'D'),
    array('Zion',      2005, 9999, 10,         1,  1, 0,  0, 'S'),
  );

	// set_country または set_tz_name を予め実行し、タイムゾーン情報を抽出しておく

	/*
	 * set_country
	 * The TimeZone Information of the Country that specified it is extracted.
	 * 指定した国のタイムゾーン情報を抽出
	 */
	function set_country($x='')
	{
		$this->country = strtoupper( $x );
		$this->tz_country = array();
		foreach($this->tz as $_key => $_tz) {
			if ($_tz[3] == $this->country) $this->tz_country[$_key] = $_tz;
		}
	}

	/*
	 * set_tz_name
	 * The TimeZone Information is extracted.
	 * 指定したタイムゾーン情報を抽出
	 */
	function set_tz_name($tz_name)
	{
		if (! is_array($this->tz[$tz_name])) return;
		$this->tz_name = $tz_name;
		$this->tz_country = array();
		$this->tz_country[$tz_name] = $this->tz[$tz_name];
	}

	/*
	 * set_datetime
	 * Time is set.
	 * 時間を設定する
	 */
	function set_datetime($utime=0)
	{
		$this->utime = ($utime == 0) ? time() - date('Z') : $utime;
		$this->y = date('Y',$this->utime);
		$this->m = date('j',$this->utime);
	}

	/*
	 * get_zonetime
	 * ゾーン情報の取得
	 * @return	integer
	 */
	function get_zonetime()
	{
		$zonetime = $this->get_offsettime();
		$zone = $this->get_zonename($this->offset);
		return array($zone, $zonetime);
	}

	/*
	 * get_offsettime
	 * GMTに対するオフセット秒の取得
	 * @return	integer
	 */
	function get_offsettime()
	{
		// Execute the processing of set_country or set_tz_name beforehand.
		$this->offset = 0;
		foreach($this->tz_country as $_key => $_tz) {
			// Key - TimeZone Name => 0: OFFSET, 1:HOUR, 2:MINUTE, 3:ISO3166
			//                        4: ABBREV, 5:DAYLIGHT, 6:RULE(DST)
			// 'Mexico/General' => array(-1,  6,  0, 'MX', 'CST','CDT','Mexico'),
			// $_dst = ($dst) ? (3600 * $_tz[0]) : 0;
			$this->offset = (empty($_tz[6])) ? 0: ($this->calc_dst($_tz[6]) * 60 * $_tz[0]);
			$h = $_tz[1] * 3600;
			$i = $_tz[2] * 60;
			return ($h + $i + $this->offset) * $_tz[0];
		}
		return 0;
	}

	/*
	 * get_zonename
	 * ゾーン名の取得
	 * @return	string
	 */
	function get_zonename($dst=0)
	{
		// Execute the processing of set_country or set_tz_name beforehand.
		$idx = ($dst == 0) ? 4 : 5;
		foreach($this->tz_country as $_key => $_tz) {
			if (! empty($_tz[$idx])) return $_tz[$idx];
		}
		return '';
	}

	/*
	 * calc_dst
	 * Calculation at summer time correction time.
	 * 夏時間補正時間算出
	 * @return	integer (Min)
	 */
	function calc_dst($rule_name)
	{
		if (! isset($this->y)) $this->set_datetime(); // 日時未設定の場合
		$tbl = array();
		$str_dst = '';
		foreach($this->dst as $x) {
			if ($x[0] != $rule_name) continue;	// 他のルールは除く
			if ($x[1] > $this->y) continue;		// 先日付は除く
			if ($x[7] == 0) {
				$idx_dst = 0;			// 通常
				$str_dst = $x[8];		// 通常時のDST文字列(過去定義のみの場合を考慮)
			} else {
				$idx_dst = 1;			// DST
			}
			if ($x[2] < $this->y) continue;		// 過去分は除く

			if (! empty($tbl[$idx_dst]) && $tbl[$idx_dst][1] != 9999) continue; // 日付固定分のため優先
			$_day = $this->calc_week2day($this->y,$this->m,$x[4]);
				// mktime(h,i,s,   m,d, y,dst);
			$_udate = mktime($x[5],$x[6],0,$x[3],$_day,$this->y);
			$tbl[$idx_dst] = array('s' => $x[1], 'e' => $x[2],
					   'm' => $x[3], 'd' => $_day, 'h' => $x[5], 'i' => $x[6],
					   'offset' => $x[7], 'str' => $x[8], 'utime' => $_udate );
		}

		// print_r($tbl);

		if (! isset($tbl[0]) || ! isset($tbl[1])) return 0; // 開始終了がない

		$idx_s  = ($tbl[0]['m'] < $tbl[1]['m']) ? 0 : 1; // 月を比較
		$idx_e  = ($idx_s == 1) ? 0 : 1;

		$idx = ($tbl[$idx_s]['utime'] <= $this->utime && $this->utime < $tbl[$idx_e]['utime']) ? $idx_s : $idx_e;

		return $tbl[$idx]['offset'];
	}

	/*
	 * calc_week2day
	 * The date of a specified day of the week is calculated.
	 * 指定曜日の日付を算出する
	 * - 最後の指定曜日を求める
	 * - 指定日の直近(前後)の指定曜日を求める
	 *
	 * @return	integer
	 */
	function calc_week2day($y,$m,$key)
	{
		if (strlen($key) < 3) return $key;

		$wday = array('Sun'=>0,'Mon'=>1,'Tue'=>2,'Wed'=>3,'Thu'=>4,'Fri'=>5,'Sat'=>6);
		$gl = '';

		if (substr($key,0,4) == 'last') {
			// 最後の指定曜日を算出
			$week = $wday[substr($key,4)]; // 求めたい曜日
			// mktime(h,i,s,   m,d, y,dst);
			$calc_week = date('w', mktime(0,0,0,$m+1,0,$y) ); // 当月末
			$calc_day  = date('j', mktime(0,0,0,$m+1,0,$y) );
		} else {
			// 指定日の直近(前後)の指定曜日を算出
			$week = $wday[substr($key,0,3)]; // 求めたい曜日
			$gl = substr($key,3,1);
			$calc_day = substr($key,5);
			$calc_week = date('w', mktime(0,0,0,$m,$calc_day,$y) );
		}

		// 指定日以降
		if ($gl == '>') {
			$offset = $calc_week + $week - 1;
			$offset = ($offset >= 7) ? $offset - 7 : $offset;
			return ($calc_day + $offset);
		}
		// 指定日以前
		$offset = $calc_week - $week;
		$offset = ($offset < 0) ? $offset + 7: $offset;
		return ($calc_day - $offset);
	}

}

?>
