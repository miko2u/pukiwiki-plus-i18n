<?php
/**
 * A SETUP OF A PUBLIC HOLIDAY
 *
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: public_holiday.php,v 2.0 2007/08/05 15:19:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 * Function Name: public_holiday(Year,Month,Day)
 * Return Value : array
 * ['rc']   0:Weekday            (平日)
 *          1:Public holiday     (国民の祝日)
 *          2:Substitute holiday (振替休日)
 *          3:National holiday   (国民の休日)
 * ['name'] Public holiday name  (祝日名称)
 * ['w']    0-6: day of the
 */

/* *************************************************************************
 * COUNTRY:
 * The country code which applies a public holiday is defined.
 * This is a 2 characters (Alpha) code defined by ISO3166.
 * See: http://www.iso.ch/iso/en/prods-services/iso3166ma/
 * STATE:
 * ONLY USA.
 * FIPS (Federal Information Processing Standards) State Alpha Code
 * See: http://www.itl.nist.gov/fipspubs/fip5-2.htm
 *************************************************************************** */

defined('COUNTRY') or define('COUNTRY','JP');

/*
defined('COUNTRY') or define('COUNTRY','US');
defined('STATE')   or define('STATE','GU');	// Guam
defined('STATE')   or define('STATE','HI');	// Hawaii
*/

/*
 * CLASS: public_holiday
 * public_holiday($y,$m,$d)	constructor						コンストラクタ
 * init($y,$m,$d)		initialize
 * is_holiday()
 * get_rc()
 * set_public_holiday()
 * ph_SpecificDay()		Fixed Public Holiday					固定祝日算出
 * ph_Calculation()		Calculation of a public holiday				祝日の計算 (春分の日・秋分の日計算)
 * ph_HappyMonday()		Move Public Holiday					移動祝日算出
 * ph_SubstituteHoliday()	Substitute holiday					振替休日
 * set_SubstituteHoliday()	Substitute holiday					振替休日
 * ph_NationalHoliday()		Dummy
 * chk_from_to($from,$to)	Applicable period check					適用期間判定
 *
 * CLASS: public_holiday_jp
 * public_holiday_jp($y,$m,$d)	コンストラクタ
 * ph_SubstituteHoliday()	第３条第２項 (振替休日)
 * jp_SubstituteHoliday()	振替休日 ２００７年以降
 * ph_NationalHoliday()		第３条第３項 (国民の休日)
 * jp_NationalHoliday()		国民の休日
 *
 * CLASS: public_holiday_us
 * public_holiday_us($y,$m,$d)	constructor
 * tbl_SpecificDay_State()	Fixed Public Holiday
 * tbl_HappyMonday_State()	Move Public Holiday
 *
 *
 * Function Name		Description
 * public_holiday($y,$m,$d)	A SETUP OF A PUBLIC HOLIDAY				祝日判定(メイン)
 * is_LastDayOfWeek($y,$m,$d)	The last day of the week				最終週判定
 * zeller($y,$m,$d)		Calculation of a day of the week			zellerの公式 (曜日算出)
 * mkdate($y,$m,$d,$offset)	The date of the specified displacement is computed	日付の加減
 * date2jd()			Calculation of the Julius day				日付からジュリアンデートを算出
 * jd2date($jd)			A date is set up from the Julius day			ジュリアンデートから日付を算出
 * VernalEquinox()		Vernal Equinox Day					春分の日 (正式は官報)
 * AutumnalEquinox()		Autumnal Equinox Day					秋分の日 (正式は官報)
 * lastday($y,$m)		End-of-the-month calculation				末日算出
 * LeapYear($y)			Leap year judging					閏年判定
 * iso8601($y,$m,$d,$format='%d-W%02d-%d')
 *				The standard strings for ISO-8601			ISO 8601 標準文字列設定
 * date2WeekDate($y,$m,$d)	Weekdate is calculated					週目算出
*/

//$hol = public_holiday(2002,5,4);
//$hol = public_holiday(2009,9,22);
//print_r($hol);

/**
 * 祝日判定クラス
 * @abstract
 */
class public_holiday
{
	var $y,$m,$d,$w,$n;
	var $rc = array();
	var $my_name;

	var $tbl_SpecificDay = array();
	var $tbl_Calculation = array();
	var $tbl_HappyMonday = array();
	var $tbl_SubstituteHoliday = array();
        var $tbl_SubstituteHoliday_offset = array();
	var $tbl_NationalHoliday = array();

	function public_holiday($y,$m,$d) { $this->init($y,$m,$d); }

	function init($y,$m,$d)
	{
		$this->y = $y;
		$this->m = $m;
		$this->d = $d;
		$this->w = zeller($y,$m,$d);
		$this->n = (int)(($d-1)/7)+1; // Is it equivalent to the n-th time?
		$this->rc['rc'] = 0;
		$this->rc['name'] = '';
		$this->my_name = get_class($this);
	}

	function is_holiday() { return $this->rc['rc']; }
	function get_rc() { return array_merge_recursive($this->rc, array('w'=>$this->w)); }
	function set_public_holiday()
	{
		static $func = array('ph_SpecificDay','ph_Calculation','ph_HappyMonday','ph_SubstituteHoliday','ph_NationalHoliday');
		foreach($func as $x) {
			call_user_func( array(&$this, $x) );
			if ($this->rc['rc']) return;
		}
	}

	// 固定の祝日
	// Fixed Public Holiday
	function ph_SpecificDay()
	{
		foreach($this->tbl_SpecificDay as $x) {
			if ($x[0] > $this->m) return;
			if ($x[0] == $this->m && $x[1] == $this->d) {
				if ($this->chk_from_to($x[2],$x[3])) {
					// In the case of within an object period.
					$this->rc['rc'] = 1;
					$this->rc['name'] = $x[4];
					return;
				}
			}
		}
	}

	// 移動の祝日
	// Calculation of a public holiday
	function ph_Calculation()
	{
		foreach($this->tbl_Calculation as $x) {
			if ($x[0] != $this->m) continue;
			if (! $this->chk_from_to($x[1],$x[2])) continue;
			// In the case of within an object period.
			if (function_exists($x[3])) {
				// The function is entrusted when the applicable function is defined.
				if ($this->d == $x[3]($this->y,$this->m,$this->d)) {
					$this->rc['rc'] = 1;
					$this->rc['name'] = $x[4];
					return;
				}
			}
		}
	}

	// Move Public Holiday
	function ph_HappyMonday()
	{
		foreach($this->tbl_HappyMonday as $x) {
			if ($x[0] > $this->m) return;
			if ($x[0] != $this->m) continue;
			if (! $this->chk_from_to($x[3],$x[4])) continue;
			// In the case of within an object period.
			if ($this->w != $x[2]) continue;

			if ($this->n == $x[1]) {
				$this->rc['rc'] = 1;
				$this->rc['name'] = $x[5];
				return;
			} elseif ($x[1] == 9) {
				if (is_LastDayOfWeek($this->y,$this->m,$this->d)) {
					$this->rc['rc'] = 1;
					$this->rc['name'] = $x[5];
					return;
				}
			}
		}
	}

        // 振替休日
        // Substitute holiday
        function ph_SubstituteHoliday()
        {
                foreach($this->tbl_SubstituteHoliday as $x) {
                        if (! $this->chk_from_to($x[0],$x[1])) continue;
                        // In the case of within an object period.
			if (method_exists($this,$x[2])) {
				if (call_user_func( array(&$this, $x[2]) )) {
					$this->rc['rc'] = 2;
					$this->rc['name'] = $x[3];
					return;
				}
			}
                }
        }

	// Substitute holiday
	function set_SubstituteHoliday()
	{
		$offset = 0;
		foreach($this->tbl_SubstituteHoliday_offset as $x) {
			if ($x[0] == $this->w) {
				$offset = $x[1];
				break;
			}
		}
		if ($offset == 0) return false;

		$x = mkdate($this->y,$this->m,$this->d,$offset);
		$obj = new $this->my_name($x['y'],$x['m'],$x['d']);
		$obj->ph_SpecificDay();
		$obj->ph_Calculation();
		$rc = $obj->rc['rc'];
		unset($obj);
		return ($rc) ? true : false;
	}

	function ph_NationalHoliday() { return; }

	/* --------------------------------------------------------------------------------- */
	// Applicable period check
	// chk_from_to($y,$m,$d,$x[3],$x[4]);
	function chk_from_to($from,$to) {
		$chk  = ( $this->y*10000) + ( $this->m*100) +  $this->d;
		return ($from <= $chk && $chk <= $to) ? true : false;
	}

}

/**
 * 祝日判定クラス(日本用)
 * @abstract
 */
class public_holiday_jp extends public_holiday
{
	// 休日の変遷＠行政歴史研究会 を参考に、明治から大正までの対応を行っています
	// http://homepage1.nifty.com/gyouseinet/kyujitsu.htm

	// 第３条１項 国民の祝日
	var $tbl_SpecificDay = array(
		//     M, D, StartYMD, EndYMD  ,Public Holiday Name
		array( 1, 1, 19480720, 99999999,'元日'),
		array( 1, 3, 18731014, 19480719,'元始祭'),
		array( 1, 5, 18731014, 19480719,'新年宴會'),
		array( 1,15, 19480720, 19991231,'成人の日'),
		array( 1,30, 18731014, 19120902,'孝明天皇祭'),
		array( 2,11, 18731014, 19480719,'紀元節'),
		array( 2,11, 19661209, 99999999,'建国記念の日'),
		array( 2,24, 19890224, 19890224,'昭和天皇の大喪の礼'),		// 平成元年法律４号
		array( 4, 3, 18731014, 19480719,'神武天皇祭'),
		array( 4,10, 19590410, 19590410,'皇太子明仁親王の結婚の儀'),	// 昭和３４年法律１６号
		array( 4,29, 19270303, 19480719,'天長節'),
		array( 4,29, 19480720, 19890216,'天皇誕生日'),
		array( 4,29, 19890217, 20061231,'みどりの日'),
		array( 4,29, 20070101, 99999999,'昭和の日'),
		array( 5, 3, 19480720, 99999999,'憲法記念日'),
		array( 5, 4, 20070101, 99999999,'みどりの日'),
		array( 5, 5, 19480720, 99999999,'こどもの日'),
		array( 6, 9, 19930609, 19930609,'皇太子徳仁親王の結婚の儀'),	// 平成５年法律３２号
		array( 7,20, 19960101, 20021231,'海の日'),
		array( 7,30, 19120903, 19270302,'明治天皇祭'),
		array( 8,31, 19120903, 19270302,'天長節'),
		array( 9,15, 19660625, 20021231,'敬老の日'),
		array( 9,17, 18731014, 18790704,'神嘗祭'),
		array(10,10, 19660625, 19991231,'体育の日'),
		array(10,17, 18790705, 19480719,'新嘗祭'),
		array(10,31, 19130716, 19270302,'天長節祝日'),
		array(11, 3, 18731014, 19120902,'天長節'),
		array(11, 3, 19270303, 19480719,'明治節'),
		array(11, 3, 19480720, 99999999,'文化の日'),
		array(11,10, 19150921, 19151116,'即位ノ禮'),			// 大正４年勅令１６１号
		array(11,10, 19280908, 19281116,'即位ノ禮'),			// 昭和３年勅令２２６号
		array(11,12, 19901112, 19901112,'即位礼正殿の儀'),		// 平成２年法律２４号
		array(11,14, 19150921, 19151116,'大嘗祭'),			// 大正４年勅令１６１号
		array(11,14, 19280908, 19281116,'大嘗祭'),			// 昭和３年勅令２２６号
		array(11,23, 18731014, 19480719,'新嘗祭'),
		array(11,23, 19480720, 99999999,'勤労感謝の日'),
		array(12,23, 19890217, 99999999,'天皇誕生日'),
		array(12,25, 19270303, 19480719,'大正天皇祭'),
	);

	var $tbl_Calculation = array(
		//    月,開始YMD , 終了YMD , 関数名              ,祝日名称
		array( 3,18780605, 19480719, 'VernalEquinox'    ,'春季皇靈祭'),
		array( 3,19480720, 99999999, 'VernalEquinox'    ,'春分の日'),
		array( 9,18780605, 19480719, 'AutumnalEquinox'  ,'秋季皇靈祭'),
		array( 9,19480720, 99999999, 'AutumnalEquinox'	,'秋分の日'),
	);


	// 平成１０年法律１４１号
	// 平成１３年法律５９号
	var $tbl_HappyMonday = array(
		//    月,週,曜日, 開始YMD , 終了YMD , 祝日名称
		array( 1, 2,   1, 20000101, 99999999, '成人の日'),
		array( 7, 3,   1, 20030101, 99999999, '海の日'),
		array( 9, 3,   1, 20030101, 99999999, '敬老の日'),
		array(10, 2,   1, 20000101, 99999999, '体育の日'),
	);

	// 第３条２項 振替休日
	var $tbl_SubstituteHoliday = array(
		//    開始YMD,  終了YMD , method name            , 祝日名称
		array(19730421, 20061231, 'set_SubstituteHoliday', '振替休日'),
		array(20070101, 99999999, 'jp_SubstituteHoliday' , '振替休日'),
	);

	var $tbl_SubstituteHoliday_offset = array(
                // 曜日,Offset
                array(1, -1), // Is Sunday a public holiday if it is Monday?
	);

	// 第３条３項 国民の休日
	var $tbl_NationalHoliday = array(
		//    開始YMD , 終了YMD , 祝日名称
		array(19851227, 99999999,'国民の休日'),
	);

        function public_holiday_jp($y,$m,$d) { $this->init($y,$m,$d); }

        // ONLY JAPAN.
        // 振替休日(第３条第２項)
        // ２００７年から
        // 「国民の祝日」が日曜日に当たるときは、その日後においてその日に最も近い「国民の祝日」でない日を休日とする。
        function jp_SubstituteHoliday()
        {
                if ($this->w == 0) return false;

                $rc = false;
                $offset = 0;
                while(1) {
			$offset--;
			$x = mkdate($this->y,$this->m,$this->d,$offset);

			$obj = new $this->my_name($x['y'],$x['m'],$x['d']);
			$obj->ph_SpecificDay();
			$obj->ph_Calculation();
			if ($obj->rc['rc'] == 0) break; // 平日なら終了

			if ($obj->w == 0) {
				$rc = true; // 日曜日が祝日
				break;
			}
			unset($obj);
                }

                return $rc;
        }

	function ph_NationalHoliday()
	{
		foreach($this->tbl_NationalHoliday as $x) {
			if (! $this->chk_from_to($x[0],$x[1])) continue;
			// In the case of within an object period.
			if ($this->jp_NationalHoliday()) {
				$this->rc['name'] = $x[2];
				$this->rc['rc']   = 3;
				return;
			}
		}
	}

	// 国民の休日(第３条第３項) ２００６年まで
	// その前日及び翌日が「国民の祝日」である日（日曜日にあたる日及び前項に規定する休日にあたる日を除く。）は、休日とする。
	// 国民の休日(第３条第３項) ２００７年から
	// その前日及び翌日が「国民の祝日」である日（「国民の祝日」でない日に限る。）は、休日とする。
	function jp_NationalHoliday()
	{
		// 本当は、２００７年からは条件対象外ではあるが、他ロジックからそのまま。
		if ($this->w == 0) return false; // It is on the day except Sunday.

		// 「国民の祝日」でない日に限る。
		// この部分に関しては、この method を呼ぶ前に、
		// ph_SpecificDay, ph_Calculation, ph_HappyMonday, ph_SubstituteHoliday
		// を判定済みという前提で回避。-> set_public_holiday()

		foreach(array(-1,1) as $offset) {
			$x = mkdate($this->y,$this->m,$this->d,$offset);
			$obj = new $this->my_name($x['y'],$x['m'],$x['d']);
			$obj->ph_SpecificDay();
			$obj->ph_HappyMonday();
			$obj->ph_Calculation();
			if ($obj->rc['rc'] == 0) return false;
			unset($obj);
		}
		return true;
	}

}

/**
 * 祝日判定クラス(ＵＳ用)
 * @abstract
 */
class public_holiday_us extends public_holiday
{
	var $tbl_SpecificDay = array(
		//     M, D, StartYMD, EndYMD  , Public Holiday Name
		array( 1, 1, 00000000, 99999999, 'New Year\'s Day'),
		array( 7, 4, 17760704, 99999999, 'Independence Day'),
		array(11,11, 19181111, 19531231, 'Veterans Day'),
		array(11,11, 19540101, 99999999, 'Veterans Day'),
		array(12,25, 00000000, 99999999, 'Christmas Day'),
	);
	var $tbl_Calculation = array();
	var $tbl_HappyMonday = array(
		array( 1, 3, 1, 00000000, 99999999, 'Birthday of Martin Luther King'),
		array( 2, 3, 1, 00000000, 99999999, 'President\'s Day'),
		array( 5, 9, 1, 00000000, 99999999, 'Memorial Day'),		// Last Monday
		array( 9, 1, 1, 00000000, 99999999, 'Labor Day'),
		array(10, 2, 1, 00000000, 99999999, 'Columbus Day'),
		array(11, 9, 4, 18631101, 19401231, 'Thanksgiving Day'),	// Last Thursday
		array(11, 4, 4, 19410101, 99999999, 'Thanksgiving Day'),
	);

	var $tbl_SubstituteHoliday = array(
		//    StartYMD, EndYMD  ,  method name           ,  祝日名称
		array(19680000, 99999999, 'set_SubstituteHoliday', 'Substitute holiday'),
	);
        var $tbl_SubstituteHoliday_offset = array(
		array(1, -1),
		array(5,  1), // Is Saturday a public holiday if it is Friday?
	);

	function public_holiday_us($y,$m,$d)
	{
		$this->init($y,$m,$d);
		$this->tbl_SpecificDay_State();
		$this->tbl_HappyMonday_State();
	}

	function tbl_SpecificDay_State()
	{
		switch (STATE) {
		case 'GU': // Guam
			$tmp = array(
				array( 7,21, 00000000, 99999999,'Liberation Day'),
				array(11, 1, 00000000, 99999999,'All Souls Day'),
				array(12, 8, 00000000, 99999999,'Immaculate Conception'),
			);
			$this->tbl_SpecificDay = array_merge_recursive($this->tbl_SpecificDay, $tmp);
			break;
		case 'HI': // Hawaii
			$tmp = array(
				array( 3,26, 18710326, 99999999,'Prince Kuhio day'),
				array( 6,11, 18720611, 99999999,'King Kamehameha Day'),
			);
			$this->tbl_SpecificDay = array_merge_recursive($this->tbl_SpecificDay, $tmp);
			break;
		}
	}

	function tbl_HappyMonday_State()
	{
		switch (STATE) {
		case 'GU': // Guam
			$tmp = array(
				//     M,No,WEEK,  Start,      End, Public Holiday Name
				array( 3, 1, 1, 00000000, 99999999, 'Discovery Day'),
			);
			$this->tbl_HappyMonday = array_merge_recursive($this->tbl_HappyMonday, $tmp);
			break;
		case 'HI': // Hawaii
			$tmp = array(
				array( 8, 3, 5, 19590821, 99999999, 'Admission Day'),
			);
			$this->tbl_HappyMonday = array_merge_recursive($this->tbl_HappyMonday, $tmp);
			break;
		}
	}
}


// 休日判定
function public_holiday($y,$m,$d)
{
	switch (COUNTRY) {
	case 'JP':
		$obj = new public_holiday_jp($y,$m,$d);
		break;
	case 'US':
		$obj = new public_holiday_us($y,$m,$d);
		break;
	default:
		$obj = new public_holiday_jp($y,$m,$d);
	}

	$obj->set_public_holiday();
	return $obj->get_rc();
}

/* ------------------------------------------------------------------------------------------------------------------------------------- */
// The last day of the week
function is_LastDayOfWeek($y,$m,$d)
{
	// 翌週の同曜日が翌月ならば最終と判断
	$x = mkdate($y,$m,$d,7);
	if ($m == $x['m']) return false;
	return true;
}

// Calculation of a day of the week
function zeller($y,$m,$d)
{
	// It corresponds till 1583-3999 year.
	// January and February are the previous year.
	// It processes as 13 or 14 months.
	if ($m < 3) {
		$y--;
		$m += 12;
	}
	$d = $y + floor($y/4) - floor($y/100) + floor($y/400) + floor(2.6*$m+1.6) + $d;
	return ($d%7);
}

// The date of the specified displacement is computed.
function mkdate($y,$m,$d,$offset)
{
	$rc = array();
	$jd = date2jd($y,$m,$d) + $offset;
	@list($rc['y'],$rc['m'],$rc['d']) = jd2date($jd);
	return $rc;
}

// Calculation of the Julius day
function date2jd()
{
	@list($y,$m,$d,$h,$i,$s) = func_get_args();

	if( $m < 3.0 ) {
		$y -= 1.0;
		$m += 12.0;
	}

	$jd  = (int)( 365.25 * $y );
	$jd += (int)( $y / 400.0 );
	$jd -= (int)( $y / 100.0 );
	$jd += (int)( 30.59 * ( $m-2.0 ) );
	$jd += 1721088;
	$jd += $d;

	$t  = $s / 3600.0;
	$t += $i /60.0;
	$t += $h;
	$t  = $t / 24.0;

	$jd += $t;
	return( $jd );
}

// A date is set up from the Julius day.
function jd2date($jd)
{
	$x0 = (int)( $jd+68570.0);
	$x1 = (int)( $x0/36524.25 );
	$x2 = $x0 - (int)( 36524.25*$x1 + 0.75 );
	$x3 = (int)( ( $x2+1 )/365.2425 );
	$x4 = $x2 - (int)( 365.25*$x3 )+31.0;
	$x5 = (int)( (int)($x4) / 30.59 );
	$x6 = (int)( (int)($x5) / 11.0 );

	$time[2] = $x4 - (int)( 30.59*$x5 );
	$time[1] = $x5 - 12*$x6 + 2;
	$time[0] = 100*( $x1-49 ) + $x3 + $x6;

	// Compensation on February 30
	if ($time[1] == 2 && $time[2] > 28) {
		if ($time[0] % 100 == 0 && $time[0] % 400 == 0) {
			$time[2] = 29;
		} elseif ($time[0] % 4 == 0) {
			$time[2] = 29;
		} else {
			$time[2] = 28;
		}
	}

	$tm = 86400.0*( $jd - (int)( $jd ) );
	$time[3] = (int)( $tm/3600.0 );
	$time[4] = (int)( ($tm - 3600.0*$time[3]) / 60.0 );
	$time[5] = (int)( $tm - 3600.0*$time[3] - 60*$time[4] );

	return($time);
}

// Vernal Equinox Day
function VernalEquinox()
{
	@list($y) = func_get_args();
	if ($y < 1980)
		$a = 20.8357;
	elseif ($y < 2100)
		$a = 20.8431;
	elseif ($y < 2151)
		$a = 21.8510;
	$b = $y - 1980;
	return (int)($a+0.242194 * $b - (int)($b/4));
}

// Autumnal Equinox Day
function AutumnalEquinox()
{
	@list($y) = func_get_args();
	if ($y < 1980)
		$a = 23.2588;
	elseif ($y < 2100)
		$a = 23.2488;
	elseif ($y < 2151)
		$a = 24.2488;
	$b = $y - 1980;
	return (int)($a+0.242194 * $b - (int)($b/4));
}

// 末日の算出
// End-of-the-month calculation
function lastday($y,$m)
{
	$last = array(31,28,31,30,31,30,31,31,30,31,30,31);
	if (LeapYear($y)) $last[1] = 29;
	return $last[$m-1];
}

// 閏年判定
// Leap year judging
function LeapYear($y)
{
	if (($y%400) == 0) return true;
	if (($y%100) == 0) return false;
	if (($y%4)   == 0) return true;
	return false;
}

function iso8601($y,$m,$d,$format='%d-W%02d-%d')
{
	list($cy,$cw,$cz) = date2WeekDate($y,$m,$d);
	return sprintf($format,$cy,$cw,$cz);
}

function date2WeekDate($y,$m,$d)
{
	$jd = date2jd($y,$m,$d);
	$cz = zeller($y,$m,$d);
	if ($cz == 0) $cz = 7;

	if ($m == 12) {
		// 翌年の1/1
		$w = zeller($y+1,1,1);
		$offset = ($w == 0) ? 7 : $w;
		// 翌年の1/1が木までの場合
		// 2:火 3:水 4:木
		if ($offset < 5 && $offset > 1) {
			// 12月の最終週は、翌年に属する可能性がある
			$jd_y0101 = date2jd($y+1,1,1);
			$seq = $jd_y0101 - $jd;
			// 月火水までなら翌年
			if ($seq < $offset) return array($y-1,'01', $cz);
		}
	}

	$w = zeller($y,1,1);
	// $offset = 1:月 2:火 3:水 4:木 5:金 6:土 7:日
	$offset = ($w == 0) ? 7 : $w;
	$jd_y0101 = date2jd($y,1,1);
	$week = ceil(($jd - $jd_y0101 + $offset) / 7);

	// 1/1が 金土日の場合は、前年に属するので計算結果から1週減らす
	if ($offset > 4) $week--;
	// 計算したい日が結果ゼロ週の場合は、前年週を再算出
	if ($week == 0) return date2WeekDate($y-1,12,31);
	return array($y,str_pad($week ,2, '0', STR_PAD_LEFT), $cz);
}

?>
