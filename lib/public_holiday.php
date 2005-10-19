<?php
// $Id: public_holiday.php,v 1.7 2005/10/19 22:58:00 upk Exp $
/*
 * public_holiday.php
 * License: GPL
 * Author: Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * Last-Update: 2005-10-19
 *
 * A SETUP OF A PUBLIC HOLIDAY
 *
 * Function Name: public_holiday(Year,Month,Day)
 * Return Value : array
 * ["name"] Public holiday name
 * ["rc"]   0:Weekday            (平日)
 *          1:Public holiday     (祝日)
 *          2:Substitute holiday (振替休日)
 *          3:National holiday   (国民の休日)
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

if (!defined("COUNTRY")) {
  define("COUNTRY", "JP");
}

/*
if (!defined("COUNTRY")) {
  define("COUNTRY", "US");
}
if (!defined("STATE")) {
  define("STATE", "GU"); // Guam
  define("STATE", "HI"); // Hawaii
}
*/

/*
 * Function Name                    Description
 *                    == FOR MAINTENANCE ==
 * tbl_SpecificDay()               Fixed Public Holiday
 * tbl_SpecificDay_State($tbl)     ONLY USA
 * tbl_HappyMonday()               Move Public Holiday
 * tbl_HappyMonday_State($tbl)     ONLY USA
 * tbl_SubstituteHoliday()         Substitute holiday
 * tbl_Calculation()               Calculation of a public holiday
 * tbl_NationalHoliday()           NationalHoliday (ONLY JAPAN)
 *                  == FUNCTION DEFINITION ==
 * public_holiday($y,$m,$d)        A SETUP OF A PUBLIC HOLIDAY
 * ph_SpecificDay($y,$m,$d)        Fixed Public Holiday
 * ph_HappyMonday($y,$m,$d)        Move Public Holiday
 * ph_Calculation($y,$m,$d)        Calculation of a public holiday
 * ph_NationalHoliday($y,$m,$d)    NationalHoliday (ONLY JAPAN)
 * ph_SubstituteHoliday($y,$m,$d)  Substitute holiday (振替休日)
 * jp_NationalHoliday($y,$m,$d)    A national holiday (国民の休日)
 * chk_from_to($y,$m,$d,$from,$to) Applicable period check
 * is_LastDayOfWeek($y,$m,$d)      The last day of the week
 * zeller($y,$m,$d)                Calculation of a day of the week
 * mkdate($y,$m,$d,$offset)        The date of the specified displacement is computed
 * date2jd()                       Calculation of the Julius day
 * jd2date($jd)                    A date is set up from the Julius day
 * VernalEquinox($y)               Vernal Equinox Day   (春分の日: 正式は官報)
 * AutumnalEquinox($y)             Autumnal Equinox Day (秋分の日: 正式は官報)
 * lastday($y,$m)                  End-of-the-month calculation
 * LeapYear($y)                    Leap year judging
 * iso8601($y,$m,$d,$format)       The standard strings for ISO-8601
 * date2WeekDate($y,$m,$d)         Weekdate is calculated
 */

//  $a = public_holiday(2002,5,4);
//  print "RC=".$a['rc']." ".$a['name']."\n";

//  Fixed Public Holiday (TABLE)
function tbl_SpecificDay() {

  switch (COUNTRY) {
    case "JP":
      $tbl = array(
        //     M, D, StartYMD, EndYMD  ,Public Holiday Name
        array( 1, 1, 19480725, 99999999,'元日'),
        array( 1,15, 19480725, 19991231,'成人の日'),
        array( 2,11, 19661209, 99999999,'建国記念の日'),
        array( 2,24, 19890224, 19890224,'昭和天皇の大喪の礼'),
	array( 4,10, 19590410, 19590410,'皇太子明仁親王の結婚の儀'),
        array( 4,29, 19480725, 19890216,'天皇誕生日'),
        array( 4,29, 19890217, 20061231,'みどりの日'),
	array( 4,29, 20070101, 99999999,'昭和の日'),
        array( 5, 3, 19480725, 99999999,'憲法記念日'),
	array( 5, 4, 20070101, 99999999,'みどりの日'),
        array( 5, 5, 19480725, 99999999,'こどもの日'),
        array( 6, 9, 19930609, 19930609,'皇太子徳仁親王の結婚の儀'),
        array( 7,20, 19960101, 20021231,'海の日'),
        array( 9,15, 19660625, 20021231,'敬老の日'),
        array(10,10, 19660625, 19991231,'体育の日'),
        array(11, 3, 19480725, 99999999,'文化の日'),
        array(11,12, 19901112, 19901112,'即位礼正殿の儀'),
        array(11,23, 19480725, 99999999,'勤労感謝の日'),
        array(12,23, 19890217, 99999999,'天皇誕生日'),
      );
      return $tbl;
    case "US":
      $tbl = array(
        //     M, D, StartYMD, EndYMD  ,Public Holiday Name
        array( 1, 1, 00000000, 99999999,'New Year\'s Day'),
        array( 7, 4, 17760704, 99999999,'Independence Day'),
        array(11,11, 19181111, 19531231,'Veterans Day'),
        array(11,11, 19540101, 99999999,'Veterans Day'),
        array(12,25, 00000000, 99999999,'Christmas Day'),
      );
      return tbl_SpecificDay_State($tbl);
  }

  return 0;
}

function tbl_SpecificDay_State($tbl) {

  switch (STATE) {
    case "GU": // Guam
      $tmp = array(
        array( 7,21, 00000000, 99999999,"Liberation Day"),
        array(11, 1, 00000000, 99999999,"All Souls Day"),
        array(12, 8, 00000000, 99999999,"Immaculate Conception"),
      );
      return array_merge_recursive($tbl, $tmp);
    case "HI": // Hawaii
      $tmp = array(
        array( 3,26, 18710326, 99999999,"Prince Kuhio day"),
        array( 6,11, 18720611, 99999999,"King Kamehameha Day"),
      );
      return array_merge_recursive($tbl, $tmp);
  }

  return $tbl;
}

// Move Public Holiday (TABLE)
function tbl_HappyMonday() {

  switch (COUNTRY) {
    case "JP":
      $tbl = array(
        //     M,No,WEEK, StartYMD, EndYMD  ,Public Holiday Name
        array( 1, 2,   1, 20000101, 99999999,'成人の日'),
        array( 7, 3,   1, 20030101, 99999999,'海の日'),
        array( 9, 3,   1, 20030101, 99999999,'敬老の日'),
        array(10, 2,   1, 20000101, 99999999,'体育の日'),
      );
      return $tbl;
    case "US":
      $tbl = array(
        array( 1, 3, 1, 00000000, 99999999,'Birthday of Martin Luther King'),
        array( 2, 3, 1, 00000000, 99999999,'President\'s Day'),
        array( 5, 9, 1, 00000000, 99999999,'Memorial Day'), // Last Monday
        array( 9, 1, 1, 00000000, 99999999,'Labor Day'),
        array(10, 2, 1, 00000000, 99999999,'Columbus Day'),
        array(11, 9, 4, 18631101, 19401231,'Thanksgiving Day'),// Last Thursday
        array(11, 4, 4, 19410101, 99999999,'Thanksgiving Day'),
      );
      return tbl_HappyMonday_State($tbl);
  };

  return 0;
}

function tbl_HappyMonday_State($tbl) {

  switch (STATE) {
    case "GU": // Guam
      $tmp = array(
        //     M,No,WEEK,  Start,      End, Public Holiday Name
        array( 3, 1, 1, 00000000, 99999999,"Discovery Day"),
      );
      return array_merge_recursive($tbl, $tmp);
    case "HI": // Hawaii
      $tmp = array(
        array( 8, 3, 5, 19590821, 99999999,'Admission Day'),
      );
      return array_merge_recursive($tbl, $tmp);
  }

  return $tbl;
}

// Substitute holiday (TABLE)
function tbl_SubstituteHoliday() {

  // A day of the week defines the day of the week of a day to judge.
  // That is, it judges whether it is the day when Monday may turn into
  // a substitute holiday.
  $tbl = array(
    // COUNTRY,WEEK,Offset
    array("JP",   1, -1), // Is Sunday a public holiday if it is Monday?
    array("US",   1, -1),
    array("US",   5,  1), // Is Saturday a public holiday if it is Friday?
  );
  return $tbl;
}

// Calculation of a public holiday (TABLE)
function tbl_Calculation() {

  switch (COUNTRY) {
    case "JP":
      $tbl = array(
        //    月,rc,開始年月日, 終了年月日,関数名            ,祝日名称
        array( 3, 1,19480725, 99999999,'VernalEquinox'       ,'春分の日'),
        array( 9, 1,19480725, 99999999,'AutumnalEquinox'     ,'秋分の日'),
        array( 0, 2,19730421, 99999999,'ph_SubstituteHoliday','振替休日'),
        // array( 0, 3,19851227, 99999999,'jp_NationalHoliday'  ,'国民の休日'),
      );
      return $tbl;
    case "US":
      $tbl = array(
        array( 0, 2,19680000, 99999999,'ph_SubstituteHoliday',
               'Substitute holiday'),
      );
      return $tbl;
  };

  return 0;
}

function tbl_NationalHoliday() {

  switch (COUNTRY) {
    case "JP":
      $tbl = array(
        //    月,rc,開始年月日, 終了年月日,関数名            ,祝日名称
        array( 0, 3,19851227, 99999999,'jp_NationalHoliday'  ,'国民の休日'),
      );
      return $tbl;
    case "US":
      $tbl = array(
      );
      return $tbl;
  };

  return 0;
}

// 休日判定
function public_holiday($y,$m,$d) {

  $ret = ph_SpecificDay($y,$m,$d);
  if ($ret['rc'] != 0) return $ret;
  $ret = ph_HappyMonday($y,$m,$d);
  if ($ret['rc'] != 0) return $ret;

  $ret = ph_Calculation($y,$m,$d);
  if ($ret['rc'] != 0) return $ret;

  $ret = ph_NationalHoliday($y,$m,$d);
  return $ret;
}

// Fixed Public Holiday
function ph_SpecificDay($y,$m,$d) {

  $tbl = tbl_SpecificDay();
  if (!is_array($tbl)) {
    $ret['name'] = '';
    $ret['rc']   = 0;
    return $ret;
  }

  $ret = array();

  foreach($tbl as $x) {
    if ($x[0] == $m) {
      // In the case of the applicable Month.
      if ($x[1] == $d) {
        // In the case of applicable Days and Months.
        if (chk_from_to($y,$m,$d,$x[2],$x[3])) {
          // In the case of within an object period.
          $ret['name'] = $x[4];
          $ret['rc']   = 1;
          return $ret;
        }
      }
    } elseif ($x[0] > $m) {
      break;
    }
  }

  $ret['name'] = '';
  $ret['rc']   = 0;
  return $ret;
}

// Move Public Holiday
function ph_HappyMonday($y,$m,$d) {

  $tbl = tbl_HappyMonday();
  if (!is_array($tbl)) {
    $ret['name'] = '';
    $ret['rc']   = 0;
    return $ret;
  }

  $ret = array();

  $w = zeller($y,$m,$d);  // Calculation of a day of the week
  $n = (int)(($d-1)/7)+1; // Is it equivalent to the n-th time?

  foreach($tbl as $x) {
    if ($x[0] == $m) {
      // In the case of the applicable Month.
      if (chk_from_to($y,$m,$d,$x[3],$x[4])) {
        // In the case of within an object period.
        if ($w == $x[2]) {
          if ($n == $x[1]) {
            $ret['name'] = $x[5];
            $ret['rc'] = 1;
            return $ret;
          } elseif ($x[1] == 9) {
            if (is_LastDayOfWeek($y,$m,$d)) {
              $ret['name'] = $x[5];
              $ret['rc'] = 1;
              return $ret;
            }
          }
        }
      }
    } elseif ($x[0] > $m) {
      break;
    }
  }

  $ret['name'] = '';
  $ret['rc']   = 0;
  return $ret;
}

// Calculation of a public holiday
function ph_Calculation($y,$m,$d) {

  $tbl = tbl_Calculation();
  if (!is_array($tbl)) {
    $ret['name'] = '';
    $ret['rc']   = 0;
    return $ret;
  }

  $ret = array();

  foreach($tbl as $x) {
    if ($x[0] != $m && $x[0] != 0) continue;
    if (chk_from_to($y,$m,$d,$x[2],$x[3]) == 0) continue;
    // In the case of within an object period.
    if (function_exists($x[4])) {
      // The function is entrusted when the applicable function is defined.
      if ($d == $x[4]($y,$m,$d)) {
        $ret['name'] = $x[5];
        $ret['rc']   = $x[1];
        return $ret;
      }
    }
  }

  $ret['name'] = '';
  $ret['rc']   = 0;
  return $ret;
}

function ph_NationalHoliday($y,$m,$d) {

  $tbl = tbl_NationalHoliday();
  if (!is_array($tbl)) {
    $ret['name'] = '';
    $ret['rc']   = 0;
    return $ret;
  }

  $ret = array();

  foreach($tbl as $x) {
    if ($x[0] != $m && $x[0] != 0) continue;
    if (chk_from_to($y,$m,$d,$x[2],$x[3]) == 0) continue;
    // In the case of within an object period.
    if (function_exists($x[4])) {
      // The function is entrusted when the applicable function is defined.
      if ($d == $x[4]($y,$m,$d)) {
        $ret['name'] = $x[5];
        $ret['rc']   = $x[1];
        return $ret;
      }
    }
  }

  $ret['name'] = '';
  $ret['rc']   = 0;
  return $ret;
}

// Substitute holiday
function ph_SubstituteHoliday($y,$m,$d) {

  $w = zeller($y,$m,$d); // Calculation of a day of the week

  $tbl = tbl_SubstituteHoliday();

  $offset = 0;
  foreach($tbl as $x) {
    if ($x[0] == COUNTRY && $x[1] == $w) {
      $offset = $x[2];
      break;
    }
  }

  if ($offset == 0) return 0;

  $x = mkdate($y,$m,$d,$offset);
  $rc1 = ph_SpecificDay($x['y'],$x['m'],$x['d']);
  $rc3 = ph_Calculation($x['y'],$x['m'],$x['d']);
  if ($rc1['rc'] == 0 and $rc3['rc'] == 0) return 0; // It ends, if it is a weekday.

  return $d;

}

// ONLY JAPAN.
// A national holiday
// (The weekday inserted into the public holiday)
function jp_NationalHoliday($y,$m,$d) {

  if (COUNTRY != "JP") return 0; // ONLY JPN
  if (zeller($y,$m,$d) == 0) return 0; // It is on the day except Sunday.

  // Is the previous day a public holiday?
  $x = mkdate($y,$m,$d,-1);
  $rc1 = ph_SpecificDay($x['y'],$x['m'],$x['d']);
  $rc2 = ph_HappyMonday($x['y'],$x['m'],$x['d']);
  $rc3 = ph_Calculation($x['y'],$x['m'],$x['d']);
  if ($rc1['rc'] == 0 and $rc2['rc'] == 0 and $rc3['rc'] == 0) return 0; // It ends, if it is a weekday.

  // Is the next day a public holiday?
  $x = mkdate($y,$m,$d,1);
  $rc1 = ph_SpecificDay($x['y'],$x['m'],$x['d']);
  $rc2 = ph_HappyMonday($x['y'],$x['m'],$x['d']);
  $rc3 = ph_Calculation($x['y'],$x['m'],$x['d']);
  if ($rc1['rc'] == 0 and $rc2['rc'] == 0 and $rc3['rc'] == 0) return 0; // It ends, if it is a weekday.

  return $d;
}

// Applicable period check
function chk_from_to($y,$m,$d,$from,$to) {
  $chk  = ( $y*10000) + ( $m*100) +  $d;
  if ($from <= $chk && $chk <= $to)
    return 1;
  else
    return 0;
}

// The last day of the week
function is_LastDayOfWeek($y,$m,$d) {
  // 翌週の同曜日が翌月ならば最終と判断
  $x = mkdate($y,$m,$d,7);
  if ($m == $x['m']) return 0;
  return 1;
}

// Calculation of a day of the week
function zeller($y,$m,$d) {
  // It corresponds till 1583-3999 year.
  // January and February are the previous year.
  // It processes as 13 or 14 months.
  if ($m < 3) {
    $y--; $m += 12;
  }
  $d = $y+floor($y/4)-floor($y/100)+floor($y/400)+floor(2.6*$m+1.6)+$d;
  return ($d%7);
}

// The date of the specified displacement is computed.
function mkdate($y,$m,$d,$offset) {
  $rc = array();
  $jd = date2jd($y,$m,$d) + $offset;
  @list($rc['y'],$rc['m'],$rc['d']) = jd2date($jd);
  return $rc;
}

// Calculation of the Julius day
function date2jd() {
  @list($y,$m,$d,$h,$i,$s) = func_get_args();

  if( $m < 3.0 ){
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
function jd2date($jd) {

  $x0 = (int)( $jd+68570.0);
  $x1 = (int)( $x0/36524.25 );
  $x2 = $x0 - (int)( 36524.25*$x1 + 0.75 );
  $x3 = (int)( ( $x2+1 )/365.2425 );
  $x4 = $x2 - (int)( 365.25*$x3 )+31.0;
  $x5 = (int)( (int)($x4) / 30.59 );
  $x6 = (int)( (int)($x5) / 11.0 );

  $TIME[2] = $x4 - (int)( 30.59*$x5 );
  $TIME[1] = $x5 - 12*$x6 + 2;
  $TIME[0] = 100*( $x1-49 ) + $x3 + $x6;

  // Compensation on February 30
  if($TIME[1] == 2 && $TIME[2] > 28){
    if($TIME[0] % 100 == 0 && $TIME[0] % 400 == 0){
        $TIME[2] = 29;
    }elseif($TIME[0] % 4 ==0){
        $TIME[2] = 29;
    }else{
        $TIME[2] = 28;
    }
  }

  $tm = 86400.0*( $jd - (int)( $jd ) );
  $TIME[3] = (int)( $tm/3600.0 );
  $TIME[4] = (int)( ($tm - 3600.0*$TIME[3])/60.0 );
  $TIME[5] = (int)( $tm - 3600.0*$TIME[3] - 60*$TIME[4] );

  return($TIME);
}

// Vernal Equinox Day
function VernalEquinox() {
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
function AutumnalEquinox() {
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
function lastday($y,$m) {
  $last = array(31,28,31,30,31,30,31,31,30,31,30,31);
  if (LeapYear($y)) $last[1] = 29;
  return $last[$m-1];
}

// 閏年判定
// Leap year judging
function LeapYear($y) {
  if (($y%400) == 0) return 1;
  if (($y%100) == 0) return 0;
  if (($y%4)   == 0) return 1;
  return 0;
}

function iso8601($y,$m,$d,$format="%d-W%02d-%d")
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
			if ($seq < $offset) return array($y-1,"01", $cz);
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
	return array($y,str_pad($week ,2, "0", STR_PAD_LEFT), $cz);
        // return sprintf("%02d", $week);
}

?>
