<?php
//
// $Id: minicalendar.inc.php,v 1.20.4 2008/06/23 19:45:00 upk Exp $
// *引数にoffと書くことで今日の日記を表示しないようにした。
// *携帯電話用に拡張
//
function plugin_minicalendar_convert()
{
	global $script,$vars,$post,$get,$weeklabels,$WikiName,$BracketName;
//	global $_minicalendar_plugin_edit, $_minicalendar_plugin_empty;

	$_minicalendar_plugin_edit  = _('[edit]');
	$_minicalendar_plugin_empty = _('%s is empty.');

	$today_view = TRUE;
	$today_args = 'viewex';
	$editable = 1;

	$date_str = get_date('Ym');
	$base = strip_bracket($vars['page']);

	if (func_num_args() > 0) {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (is_numeric($arg) && strlen($arg) == 6) {
				$date_str = $arg;
			}
			else if ($arg == 'locked') {
				$editable = 0;
			}
			else if ($arg == 'off') {
				$today_view = FALSE;
			}
			else if ($arg == 'past' || $arg == 'pastex' || $arg == 'future' || $arg == 'futureex' || $arg == 'view' ||$arg == 'viewex') {
				$today_args = $arg;
			}
			else {
				$base = strip_bracket($arg);
			}
		}
	}
	if ($base == '*') {
		$base = '';
		$prefix = '';
	}
	else {
		$prefix = $base.'/';
	}
	$r_base = rawurlencode($base);
	$s_base = htmlspecialchars($base);
	$r_prefix = rawurlencode($prefix);
	$s_prefix = htmlspecialchars($prefix);
	
	$yr = substr($date_str,0,4);
	$mon = substr($date_str,4,2);
	
	if ($yr != get_date('Y') || $mon != get_date('m')) {
		$now_day = 1;
		$other_month = 1;
	}
	else {
		$now_day = get_date('d');
		$other_month = 0;
	}
	
	$today = getdate(mktime(0,0,0,$mon,$now_day,$yr));
	
	$m_num = $today['mon'];
	$d_num = $today['mday'];
	$year = $today['year'];
	
	$f_today = getdate(mktime(0,0,0,$m_num,1,$year));
	$wday = $f_today['wday'];
	$day = 1;
	
	$m_name = $year.'-'.sprintf("%02d", $m_num);
	
	$y = substr($date_str,0,4)+0;
	$m = substr($date_str,4,2)+0;
	
	$prev_date_str = ($m == 1) ?
		sprintf('%04d%02d',$y - 1,12) : sprintf('%04d%02d',$y,$m - 1);
	
	$next_date_str = ($m == 12) ?
		sprintf('%04d%02d',$y + 1,1) : sprintf('%04d%02d',$y,$m + 1);
	
	$this_date_str = sprintf('%04d%02d',$y,$m);
	$page_YM = sprintf('%04d-%02d',$y,$m);
	
	$ret = '';

if (!defined('UA_MOBILE') || UA_MOBILE == 0) {
	if ($today_view) {
		if (exist_plugin('topicpath')) {
			$ret = "<div class=\"breadcrumb\"><a href=\"".$script."\">".PLUGIN_TOPICPATH_TOP_LABEL."</a> &raquo; calendar - ".$s_base."</div>\n";
		}
		$ret .= "<h2>".sprintf(_('%04d/%02d %s'),$y,$m,$s_base)."</h2>\n";
		$ret .= '</div></div><div class="row-fluid"><div class="span3">'."\n";
	}
	$ret .= <<<EOD
<div class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" style="display:block">
<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all">
	<a class="ui-datepicker-prev ui-corner-all" href="$script?plugin=minicalendar&amp;file=$r_base&amp;date=$prev_date_str&amp;mode=$today_args&amp;editable=$editable"><span class="ui-icon ui-icon-circle-triangle-w">&laquo;</span></a>
	<a class="ui-datepicker-next ui-corner-all" href="$script?plugin=minicalendar&amp;file=$r_base&amp;date=$next_date_str&amp;mode=$today_args&amp;editable=$editable"><span class="ui-icon ui-icon-circle-triangle-e">&raquo;</span></a>
	<div class="ui-datepicker-title">
EOD;

	if ($prefix) {
		$ret .= "<a href=\"$script?plugin=minicalendar&amp;file=$r_base&amp;date=$this_date_str&amp;mode=$today_args\"><strong class=\"tooltips\" title=\"$s_base\">$m_name</strong></a>";
	} else {
		$ret .= "<strong>$m_name</strong>";
	}

	$ret .= <<<EOD
	</div>
</div>
<table class="ui-datepicker-calendar" summary="calendar body">
  <thead>
    <tr>
     <td class="ui-datepicker-title" colspan="7">
EOD;
	
	$ret .= "\n     </td>\n    </tr>\n    <tr>\n";
	
	foreach($weeklabels as $label) {
		$ret .= "     <th>$label</th>\n";
	}
	
	$ret .= "    </tr>\n</thead>\n<tbody>    <tr>\n";
	// Blank 
	for ($i = 0; $i < $wday; $i++) {
		$ret .= "     <td class=\"ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled\">&nbsp;</td>\n";
	}
	
	while (checkdate($m_num,$day,$year)) {
		$dt = sprintf('%4d-%02d-%02d', $year, $m_num, $day);
		$page = $prefix.$dt;
		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);

		$h_today = public_holiday($year,$m_num,$day);
		$hday = $h_today['rc'];
		
		if ($wday == 0 and $day > 1) {
			$ret .= "    </tr>\n    <tr>\n";
		}
		
		$style = ''; // Weekday
		if (!$other_month && ($day == $today['mday']) && ($m_num == $today['mon']) && ($year == $today['year'])) { // Today
			$style = 'ui-datepicker-days-cell-over ui-datepicker-current-day ui-datepicker-today';
		}
		else if ($hday != 0) { // Holiday
			$style = 'ui-datepicker-week-end ui-datepicker-sunday';
		}
		else if ($wday == 0) { // Sunday 
			$style = 'ui-datepicker-week-end ui-datepicker-sunday';
		}
		else if ($wday == 6) { //  Saturday 
			$style = 'ui-datepicker-week-end ui-datepicker-saturday';
		}
		
		if (is_page($page))
		{
			$link = "<a class=\"ui-state-default ui-state-active\" href=\"" . get_page_uri($page) . "\" title=\"$s_page\"><strong>$day</strong></a>";
		} else if ($editable) {
			$link = "<a class=\"ui-state-default ui-state-active\" href=\"$script?cmd=edit&amp;page=$r_page&amp;refer=$r_base\" title=\"$s_page\">$day</a>";
		} else {
			$link = '<span class="ui-state-default">'.$day.'</a>';
		}
		
		$ret .= '<td class="'.$style.'">'.$link.'</td>'."\n";
		$day++;
		$wday = ++$wday % 7;
	}
	if ($wday > 0) {
		while ($wday++ < 7) { // Blank 
			$ret .= '<td class="ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled">&nbsp;</td>'."\n";
		}
	}
	
	$ret .= "    </tr>\n   </table>\n</div>\n";
	
	if ($today_view) {
		if ($today_args == '') {
			$tpage = $prefix.sprintf("%4d-%02d-%02d", $today['year'], $today['mon'], $today['mday']);
			$r_tpage = rawurlencode($tpage);
			if (is_page($tpage)) {
				$_page = $vars['page'];
				$get['page'] = $post['page'] = $vars['page'] = $tpage;
				$str = convert_html(get_source($tpage));
				$str .= "<hr /><a class=\"small\" href=\"$script?cmd=edit&amp;page=$r_tpage\">$_minicalendar_plugin_edit</a>";
				$get['page'] = $post['page'] = $vars['page'] = $_page;
			}
			else {
				$str = sprintf($_minicalendar_plugin_empty,make_pagelink(sprintf('%s%4d-%02d-%02d',$prefix, $today['year'], $today['mon'], $today['mday'])));
			}
		} else {
			$aryargs = array(rawurldecode($r_base), $page_YM, $today_args);
			if (exist_plugin('minicalendar_viewer')) {
				bindtextdomain('minicalendar_viewer', LANG_DIR);
				bind_textdomain_codeset('minicalendar_viewer', SOURCE_ENCODING);
				textdomain('minicalendar_viewer');
				$str = call_user_func_array('plugin_minicalendar_viewer_convert',$aryargs);
				textdomain('minicalendar');
			}
		}
		$ret .= "</div><div class=\"span9\">$str</div>\n</div>\n";
	}
} else {
	//
	// for non-default profile
	//
	$ret .= <<<EOD
      <a href="$script?plugin=minicalendar&amp;file=$r_base&amp;date=$prev_date_str&amp;mode=$today_args&amp;editable=$editable">&laquo;</a>
      <strong>$m_name</strong>
      <a href="$script?plugin=minicalendar&amp;file=$r_base&amp;date=$next_date_str&amp;mode=$today_args&amp;editable=$editable">&raquo;</a>
EOD;
	
	if ($prefix) {
		$ret .= "\n      <br />[<a href=\"$script?plugin=minicalendar&amp;file=$r_base&amp;date=$this_date_str&amp;mode=$today_args&amp;editable=$editable\">$s_base</a>]";
	}
	
	$ret .= "<br />\n";
	
	foreach($weeklabels as $label) {
		$ret .= "     $label\n";
	}
	
	$ret .= "<br />\n";
	// Blank 
	for ($i = 0; $i < $wday; $i++) {
		$ret .= " &nbsp;&nbsp;\n";
	}
	
	while (checkdate($m_num,$day,$year)) {
		$dt = sprintf('%4d-%02d-%02d', $year, $m_num, $day);
		$page = $prefix.$dt;
		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);

		$h_today = public_holiday($year,$m_num,$day);
		$hday = $h_today['rc'];
		
		if ($wday == 0 and $day > 1) {
			$ret .= "    <br />\n";
		}
		
		$style = 'ministyle_td_day'; // Weekday
		if (!$other_month && ($day == $today['mday']) && ($m_num == $today['mon']) && ($year == $today['year'])) { // Today
			$style = 'ministyle_td_today';
		}
		else if ($hday != 0) { // Holiday
			$style = 'ministyle_td_sun';
		}
		else if ($wday == 0) { // Sunday 
			$style = 'ministyle_td_sun';
		}
		else if ($wday == 6) { //  Saturday 
			$style = 'ministyle_td_sat';
		}
		
		if (is_page($page)) {
			if ($day < 10) { $spc = '&nbsp;'; } else { $spc = ''; }
			$link = "$spc<a class=\"small\" href=\"" . get_page_uri($page) . "\" title=\"$s_page\"><font color=\"red\">$day</font></a>";
		}
		else {
			if ($day < 10) { $spc = '&nbsp;'; } else { $spc = ''; }
			$link = "$spc<a class=\"small\" href=\"$script?cmd=edit&amp;page=$r_page&amp;refer=$r_base\" title=\"$s_page\">$day</a>";
		}
		
		$ret .= $link."\n";
		$day++;
		$wday = ++$wday % 7;
	}
	if ($wday > 0) {
		while ($wday++ < 7) { // Blank 
			$ret .= " &nbsp;\n";
		}
	}
	
	$ret .= "<br /><br />\n";

	if ($today_view) {
		if ($today_args == '') {
			$tpage = $prefix.sprintf("%4d-%02d-%02d", $today['year'], $today['mon'], $today['mday']);
			$r_tpage = rawurlencode($tpage);
			if (is_page($tpage)) {
				$_page = $vars['page'];
				$get['page'] = $post['page'] = $vars['page'] = $tpage;
				$str = convert_html(get_source($tpage));
				$str .= "<hr /><a class=\"small\" href=\"$script?cmd=edit&amp;page=$r_tpage\">$_minicalendar_plugin_edit</a>";
				$get['page'] = $post['page'] = $vars['page'] = $_page;
			}
			else {
				$str = sprintf($_minicalendar_plugin_empty,make_pagelink(sprintf('%s%4d-%02d-%02d',$prefix, $today['year'], $today['mon'], $today['mday'])));
			}
		} else {
			$aryargs = array(rawurldecode($r_base), $page_YM, $today_args);
			if (exist_plugin('minicalendar_viewer')) {
                                bindtextdomain('minicalendar_viewer', LANG_DIR);
                                bind_textdomain_codeset('minicalendar_viewer', SOURCE_ENCODING);
                                textdomain('minicalendar_viewer');
				$str = call_user_func_array('plugin_minicalendar_viewer_convert',$aryargs);
				textdomain('minicalendar');
			}
		}
		$ret .= $str."\n";
	}
}
	return $ret;
}

function plugin_minicalendar_action()
{
	global $vars;

	$page = strip_bracket($vars['page']);
	$vars['page'] = '*';
	if ($vars['file'])
	{
		$vars['page'] = $vars['file'];
	}
	
	$date = $vars['date'];
	
	if ($date == '')
	{
		$date = get_date("Ym");
	}

	$mode = $vars['mode'];
	
	if ($mode == '')
	{
		$mode = "viewex";
	}
	
	$editable = $vars['editable'];
	if ($editable == '')
	{
		$aryargs = array($vars['page'],$date,$mode);
	}
	else
	{
		$aryargs = array($vars['page'],$date,$mode,'locked');
	}

	$yy = sprintf("%04d.%02d",substr($date,0,4),substr($date,4,2));
	$s_page = htmlspecialchars($vars['page']);
	
	$ret['msg'] = 'calendar '.$s_page.'/'.$yy;
	$ret['body'] = call_user_func_array('plugin_minicalendar_convert',$aryargs);
	
	$vars['page'] = $page;
	
	return $ret;
}
