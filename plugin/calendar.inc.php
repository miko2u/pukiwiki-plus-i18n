<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: calendar.inc.php,v 2.1.5 2008/02/24 18:47:00 upk Exp $
//
// Calendar plugin - renewal

defined('PLUGIN_CALENDAR_ACTION') or define('PLUGIN_CALENDAR_ACTION', 'minicalendar');

function plugin_calendar_convert()
{
	global $vars, $weeklabels;

	$read_only = 0;
	$around = 0;
	$align = '';
	$mode = 'viewex';
	$summary = 'calendar body';

	$date_str = get_date('Ym');
	$base = strip_bracket($vars['page']);

	// First Arguments are "PAGE"
	$attr = func_get_args();
	if (func_num_args()) {
		$argv = array_shift($attr);
		if ($argv) { $base = strip_bracket($argv); }
	}

	// Vaildate argument(s)
	foreach($attr as $argv) {
		if (is_numeric($argv) && strlen($argv) == 6) {
			$date_str = $argv;
		} else if ($argv == 'noedit') {
			$read_only = 1;
		} else if ($argv == 'around') {
			$around = 1;
		} else if ($argv == 'left' || $argv == 'center' || $argv == 'right') {
			$align = $argv;
		} else {
			$summary = htmlspecialchars($argv);
		}
	}

	if ($base == '*') {
		$prefix = $base = '';
	} else {
		$prefix = $base . '/';
	}

	$r_base = rawurlencode($base);
	$s_base = htmlspecialchars($base);
	$r_prefix = rawurlencode($prefix);
	$s_prefix = htmlspecialchars($prefix);

	$yy = substr($date_str,0,4);
	$mm = substr($date_str,4,2);

	if ($yy != get_date('Y') || $mm != get_date('m')) {
		$other_month = 1;
		$now_day = 1;
	} else {
		$other_month = 0;
		$now_day = get_date('d');
	}

	$today = getdate(mktime(0,0,0,$mm,$now_day,$yy));
	$m_num = $today['mon'];
	$d_num = $today['mday'];
	$y_num = $today['year'];

	$f_today = getdate(mktime(0,0,0,$m_num,1,$y_num));
	$wday = $f_today['wday'];
	$day = 1;

	$m_name = $y_num . '/' . $m_num;

	$y = substr($date_str,0,4)+0;
	$m = substr($date_str,4,2)+0;

	$prev_date_str = ($m ==  1) ? sprintf('%04d%02d',$y - 1,12) : sprintf('%04d%02d',$y,$m - 1);
	$next_date_str = ($m == 12) ? sprintf('%04d%02d',$y + 1, 1) : sprintf('%04d%02d',$y,$m + 1);
	$this_date_str = sprintf('%04d%02d',$y,$m);

	$page_YM = sprintf('%04d-%02d',$y,$m);

	$calendar_head = $calendar_week = $calendar_body = '';

	// create header
	$calendar_head .=
		'   <a href="'.get_cmd_uri(PLUGIN_CALENDAR_ACTION,'','','file='.$r_base.'&date='.$prev_date_str.'&mode='.$mode).'">&lt;&lt;</a>' . "\n" .
		'   <strong>'.$m_name.'</strong>' . "\n" .
		'   <a href="'.get_cmd_uri(PLUGIN_CALENDAR_ACTION,'','','file='.$r_base.'&date='.$next_date_str.'&mode='.$mode).'">&gt;&gt;</a>';

	if ($prefix) {
		$calendar_head .= 
			"\n" . '   <br />' . "\n" .
			'[<a href="'.get_cmd_uri(PLUGIN_CALENDAR_ACTION,'','','file='.$r_base.'&date='.$this_date_str.'&mode='.$mode).'">'.$s_base.'</a>]';
	}

	// create week label
	foreach($weeklabels as $label) {
		$calendar_week .= '  <td class="calendar_td_week">'.$label.'</td>' . "\n";
	}

	// Blank 
	for ($i=0; $i<$wday; $i++) {
		$calendar_body .= '  <td class="calendar_td_blank">&nbsp;</td>' . "\n";
	}

	while (checkdate($m_num, $day, $y_num)) {
		$dt = sprintf('%4d-%02d-%02d', $y_num, $m_num, $day);
		$page = $prefix . $dt;
		$s_page = htmlspecialchars($page);

		$h_today = public_holiday($y_num, $m_num, $day);
		$hday = $h_today['rc'];
		
		if ($wday == 0 and $day > 1) {
			$calendar_body .= " </tr>\n <tr>\n";
		}

		$style = 'calendar_td_day'; // Weekday
		if (!$other_month && ($day == $today['mday']) && ($m_num == $today['mon']) && ($y_num == $today['year'])) {
			$style = 'calendar_td_today'; // Today
		} else if ($hday != 0) {
			$style = 'calendar_td_sun';   // Holiday
		} else if ($wday == 0) {
			$style = 'calendar_td_sun';   // Sunday 
		} else if ($wday == 6) {
			$style = 'calendar_td_sat';   // Saturday
		}

		if (is_page($page)) {
			$link = '<a href="'.get_page_uri($page).'" title="'.$s_page.'"><strong>'.$day.'</strong></a>';
		} elseif ($read_only) {
			$link = $day;
		} else {
			$link = '<a href="'.get_cmd_uri('edit',$page,'','refer='.$base).'" title="'.$s_page.'">'.$day.'</a>';
		}
		$calendar_body .= '  <td class="'.$style.'">'.$link.'</td>' . "\n";
		$day++;
		$wday = ++$wday % 7;
	}
	if ($wday > 0) {
		while ($wday++ < 7) {
			$calendar_body .= '  <td class="calendar_td_blank">&nbsp;</td>'. "\n";
		}
	}

	$calstyle = '';
	if ($align != '') {
		if ($around && $align != 'center') { $calstyle = 'float:' . $align . ';'; }
		$ex = $around ? '1ex':'auto';
		if ($align == 'left')  { $calstyle .= "margin:auto $ex auto 0px;"; }
		if ($align == 'right') { $calstyle .= "margin:auto 0px auto $ex;"; }
	}
	if ($calstyle != '') { $calstyle = ' style="' . $calstyle . '"'; }

	$output .= <<<EOD
<div class="ie5">
<table class="calendar"{$calstyle} border="0" cellspacing="1" summary="{$summary}">
 <tr>
  <td class="calendar_td_caltop" colspan="7">
{$calendar_head}
  </td>
 </tr>
 <tr>
{$calendar_week}
 </tr>
 <tr>
{$calendar_body}
 </tr>
</table>
</div><!--/ie5/-->
EOD;

	return $output;
}
?>
