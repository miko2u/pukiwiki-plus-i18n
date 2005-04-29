<?php
/*
 * PukiWiki minicalendar_viewer�ץ饰����
 *
 *
 *$Id: minicalendar_viewer.inc.php,v 1.9.12 2004/12/15 17:18:50 miko Exp $
  calendarrecent�ץ饰����򸵤˺���
 */
/**
 *����
  calendar�ץ饰�����calendar2�ץ饰����Ǻ��������ڡ��������ɽ�����뤿��Υץ饰����Ǥ���
 *��������
  -2002-11-13
  --����ؤΥ�󥯤�ǯ���ּ���n��פ�ɽ������褦�ˤ�����
 *�Ȥ���
  /// #minicalendar_viewer(pagename,(yyyy-mm|n|this),[mode],[separater])
 **pagename
  calendar or calendar2�ץ饰����򵭽Ҥ��Ƥ�ڡ���̾
 **(yyyy-mm|n|this)
  -yyyy-mm
  --yyyy-mm�ǻ��ꤷ��ǯ��Υڡ��������ɽ��
  -n
  --n��ΰ���ɽ��
  -this
  --����Υڡ��������ɽ��
 **[mode]
  ��ά��ǽ�Ǥ�����ά���Υǥե���Ȥ�past
  -past(ex)
  --���������Υڡ����ΰ���ɽ���⡼�ɡ������������������
  -future(ex)
  --�����ʹߤΥڡ����ΰ���ɽ���⡼�ɡ����٥��ͽ��䥹�����塼�����
  -view(ex)
  --����̤��ؤΰ���ɽ���⡼�ɡ�ɽ���޻ߤ���ڡ����Ϥ���ޤ���
  -[separater]
  ��ά��ǽ���ǥե���Ȥ�-����calendar2�ʤ��ά��OK��
  --ǯ��������ڤ륻�ѥ졼������ꡣ

 *todo
  past or future �Ƿ�ñ��ɽ������Ȥ��ˡ����줾�������ΰ����ؤΥ�󥯤�ɽ�����ʤ��褦�ˤ���

 */
define('MINICALENDAR_VIEWER_HOLIDAYVIEW',TRUE);
define('MINICALENDAR_VIEWER_COMMENT',FALSE);
define('MINICALENDAR_VIEWER_TRACKBACK',TRUE);

function plugin_minicalendar_viewer_convert()
{
  global $WikiName,$BracketName,$vars,$get,$post,$hr,$script;
  global $_symbol_paraedit, $trackback;
//global $_err_calendar_viewer_param,$_err_calendar_viewer_param2;
//global $_msg_calendar_viewer_right,$_msg_calendar_viewer_left;
//global $_msg_calendar_viewer_restrict;

  $_err_calendar_viewer_param    = _('Wrong parameter.');
  $_err_calendar_viewer_param2   = _('Wrong second parameter.');
  $_msg_calendar_viewer_right    = _('Next %d&gt;&gt;');
  $_msg_calendar_viewer_left     = _('&lt;&lt; Prev %d');
  $_msg_calendar_viewer_restrict = _('Due to the blocking, the minicalendar_viewer cannot refer to $1.');

  //*�ǥե�����ͤ򥻥å�
  //���Ȥʤ�ڡ���̾
  $pagename = "";
  //ɽ������������
  $limit_page = 7;
  //����ɽ������ǯ��
  $date_YM = "";
  //ư��⡼��
  $mode = "past";
  //���դΥ��ѥ졼�� calendar2�ʤ�"-" calendar�ʤ�""
  $date_sep = "-";


  //*�����γ�ǧ
  if (func_num_args()>=2){
    $func_vars_array = func_get_args();

    $pagename = $func_vars_array[0];

    if (isset($func_vars_array[3])){
      $date_sep = $func_vars_array[3];
    }
    if (preg_match("/[0-9]{4}".$date_sep."[0-9]{2}/",$func_vars_array[1])){
      //����ǯ��ΰ���ɽ��
      $page_YM = $func_vars_array[1];
      $limit_base = 0;
      $limit_page = 31;	//��ȴ����31��ʬ���ߥåȤȤ��롣
    }else if (preg_match("/this/si",$func_vars_array[1])){
      //����ΰ���ɽ��
      $page_YM = get_date("Y".$date_sep."m");
      $limit_base = 0;
      $limit_page = 31;
    }else if (preg_match("/^[0-9]+$/",$func_vars_array[1])){
      //n��ʬɽ��
      $limit_pitch = $func_vars_array[1];
      $limit_page = $limit_pitch;
      $limit_base = 0;
      $page_YM = "";
    }else if (preg_match("/([0-9]+)\*([0-9]+)/",$func_vars_array[1],$reg_array)){
      $limit_pitch = $reg_array[2];
      $limit_page = $reg_array[1] + $limit_pitch;
      $limit_base = $reg_array[1];
      $page_YM = "";
    }else{
      return $_err_calendar_viewer_param2;
    }
    if (isset($func_vars_array[2])&&preg_match("/^(past|view|viewex|future|pastex|futureex)$/si",$func_vars_array[2])){
      //�⡼�ɻ���
      $mode = $func_vars_array[2];
    }


  }else{
    return $_err_calendar_viewer_param;
  }

  //*����ɽ������ڡ���̾�ȥե�����̾�Υѥ����󡡥ե�����̾�ˤ�ǯ���ޤ�
  if ($pagename == ""){
    //pagename̵����yyyy-mm-dd���б����뤿��ν���
    $pagepattern = "";
    $pagepattern_len = 0;
    $filepattern = encode($page_YM);
    $filepattern_len = strlen($filepattern);
  }else{
    $pagepattern = strip_bracket($pagename) .'/';
    $pagepattern_len = strlen($pagepattern);
    $filepattern = encode($pagepattern.$page_YM);
    $filepattern_len = strlen($filepattern);
  }

  //echo "$pagename:$page_YM:$mode:$date_sep:$limit_base:$limit_page";
  //*�ڡ����ꥹ�Ȥμ���
  //echo $pagepattern;
  //echo $filepattern;

  $pagelist = array();
  if ($dir = @opendir(DATA_DIR))
    {
      $_date = get_date("Y".$date_sep."m".$date_sep."d");
      while($file = readdir($dir))
        {
          if ($file == ".." || $file == ".") continue;
          if (substr($file,0,$filepattern_len)!=$filepattern) continue;
          //echo "OK";
          $page = decode(trim(preg_replace("/\.txt$/"," ",$file)));
          //$page���������������ʤΤ������å� �ǥե���ȤǤϡ� yyyy-mm-dd
          $page = strip_bracket($page);
          if (plugin_minicalendar_viewer_isValidDate(substr($page,$pagepattern_len),$date_sep) == false) continue;

          //*mode����̾��ǤϤ���
          //past mode�ǤϺ�����ޤ�̤��Υڡ�����NG
          if (((substr($page,$pagepattern_len)) >= $_date)&&($mode=="pastex") )continue;
          //future mode�ǤϺ�����ޤ���Υڡ�����NG
          if (((substr($page,$pagepattern_len)) <= $_date)&&($mode=="futureex") )continue;
          //past mode�Ǥ�̤��Υڡ�����NG
          if (((substr($page,$pagepattern_len)) > $_date)&&($mode=="past") )continue;
          //future mode�Ǥϲ��Υڡ�����NG
          if (((substr($page,$pagepattern_len)) < $_date)&&($mode=="future") )continue;
          //view mode�ʤ�all OK
          $pagelist[] = $page;
        }
    }
  closedir($dir);
  //echo count($pagelist);
  //*�������饤�󥯥롼�ɳ���

  $tmppage = $vars["page"];
  $return_body = "";
  //�ޤ�������
  if ($mode == 'past' || $mode == 'pastex' || $mode =='viewex'){
    //past mode�ǤϿ�����
    rsort ($pagelist);
  }else {
    //view mode �� future mode �Ǥϡ��좪��
    sort ($pagelist);
  }

  //$limit_page�η���ޤǥ��󥯥롼��
  $tmp = $limit_base;
  while ($tmp < $limit_page){
    if (empty($pagelist[$tmp])) break;
    $page = $pagelist[$tmp];

    $get["page"] = $page;
    $post["page"] = $page;
    $vars["page"] = $page;
	
	// �����Ǳ������Ĥ����������ɽ������
	if (check_readable($page,false,false)) {
		if (function_exists('convert_filter')) {
			$body = convert_html(convert_filter(get_source($page)));
		} else {
			$body = convert_html(get_source($page));
		}
	} else {
		$body = str_replace('$1',$page,$_msg_calendar_viewer_restrict);
	}
	
    $r_page = rawurlencode($page);
    $s_page = htmlspecialchars($page);
    if(mb_ereg("(.*)/(.*)", $page, $regs)) {
        $t = $regs[2]; //�ڡ��������ȥ�
        $p = $regs[1]; //�����ȥ��������ڡ����Υѥ�
        $e = rawurlencode($t);
//	$s_page_title = "$t <span class=\"size1\">($p)</span>";
	$s_page_title = "$t";
    } else {
        $s_page_title = "$s_page";
    }
    $refpage = rawurlencode($tmppage);
    $link = "<a class=\"anchor_super\" href=\"$script?cmd=edit&amp;page=$r_page&amp;refpage=$refpage\">$_symbol_paraedit</a>";
    $head = "<h3 class=\"minicalendar\">$s_page_title $link</h3>\n";
    $tail = '';
//miko
    if (MINICALENDAR_VIEWER_HOLIDAYVIEW === TRUE) {
      $monthlabel = array(
	1 =>'January','Feburary','March','April','May','June',
	'July','August','September','Octover','November','December'
      );
      $yy = substr($s_page_title,0,4);
      $mm = intval(substr($s_page_title,5,2));
      $dd = substr($s_page_title,8,2);
      $mmstr = $monthlabel[$mm];
      $h_today = public_holiday($yy,$mm,$dd); 
      $hday = $h_today['rc'];
      $f_today = getdate(mktime(0,0,0,$mm,$dd,$yy) - LOCALZONE + ZONETIME);
      $wday = $f_today['wday'];
      if($hday != 0) { $classname = 'date_holiday'; }
      else if ($wday == 0) { $classname = 'date_holiday'; }
      else if ($wday == 6) { $classname = 'date_weekend'; }
      else { $classname = 'date_weekday'; }
      $head = '<h3 class="'. $classname . '"><span class="day">' . "$dd</span> <br /><b>$mmstr</b>, <b>$yy</b> $link </h3>";
    }
//miko
    if (MINICALENDAR_VIEWER_COMMENT === TRUE) {
      if (is_page(':config/plugin/addline/comment') && exist_plugin_inline('addline')) {
	$comm = convert_html(array("&addline(comment,above){comment};"));
	$comm = str_replace('<p>','',$comm);
	$comm = str_replace('</p>','',$comm);
	$tail .= str_replace('>comment','><img src="'.IMAGE_URI.'plus/comment.png" width="15" height="15" alt="Comment" title="Comment" />Comment',$comm);
      }
    }
    if (MINICALENDAR_VIEWER_TRACKBACK === TRUE) {
      if ($trackback) {
        $tb_id = tb_get_id($page);
        $tail .= "<a href=\"$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id\"><img src=\"".IMAGE_URI."plus/trackback.png\" width=\"15\" height=\"15\" alt=\"\" title=\"\" />Trackback(".tb_count($page).")</a>\n";
      }
    }
    if ($tail != '') { $tail = '<div class="trackback">'. $tail . '</div>'; };
    $return_body .= $head . "<div class=\"minicalendar_viewer\">" . $body . "</div>" . $tail;

    $tmp++;
  }

  //�����ǡ�����Υ�󥯤�ɽ��
  //?plugin=minicalendar_viewer&file=�ڡ���̾&date=yyyy-mm
  $enc_pagename = rawurlencode(substr($pagepattern,0,$pagepattern_len -1));

  if ($page_YM != ""){
    //ǯ��ɽ����
    $date_sep_len = strlen($date_sep);
    $this_year = substr($page_YM,0,4);
    $this_month = substr($page_YM,4+$date_sep_len,2);
    //����
    $next_year = $this_year;
    $next_month = $this_month + 1;
    if ($next_month >12){
      $next_year ++;
      $next_month = 1;
    }
    $next_YM = sprintf("%04d%s%02d",$next_year,$date_sep,$next_month);
    $next_YMX = sprintf("%04d%02d",$next_year,$next_month);

    //����
    $prev_year = $this_year;
    $prev_month = $this_month -1;
    if ($prev_month < 1){
      $prev_year --;
      $prev_month = 12;
    }
    $prev_YM = sprintf("%04d%s%02d",$prev_year,$date_sep,$prev_month);
    $prev_YMX = sprintf("%04d%02d",$prev_year,$prev_month);

    if ($mode == "past" || $mode == "pastex"){
      $right_YM = $prev_YM;
      $right_YMX = $prev_YMX;
      $right_text = $prev_YM."&gt;&gt;";
      $left_YM = $next_YM;
      $left_YMX = $next_YMX;
      $left_text = "&lt;&lt;".$next_YM;
    }else{
      $left_YM = $prev_YM;
      $left_YMX = $prev_YMX;
      $left_text = "&lt;&lt;".$prev_YM;
      $right_YM = $next_YM;
      $right_YMX = $next_YMX;
      $right_text = $next_YM."&gt;&gt;";
    }
  }else{
    //n��ɽ����
    if ($limit_base >= count($pagelist)){
      $right_YM = "";
    }else{
      $right_base = $limit_base + $limit_pitch;
      $right_YM = $right_base ."*".$limit_pitch;
      $right_text = sprintf($_msg_calendar_viewer_right,$limit_pitch);
    }
    $left_base  = $limit_base - $limit_pitch;
    if ($left_base >= 0) {
      $left_YM = $left_base . "*" . $limit_pitch;
      $left_text = sprintf($_msg_calendar_viewer_left,$limit_pitch);
      
    }else{
      $left_YM = "";
    }
    $prev_YMX = '';
    $next_YMX = '';
  }
  //��󥯺���
  $s_date_sep = htmlspecialchars($date_sep);
  if ($left_YM != ""){
    if ($left_YMX != '') {
      $left_link = "<a href=\"$script?plugin=minicalendar&amp;file=$enc_pagename&amp;date=$left_YMX\">$left_text</a>";
    } else {
      $left_link = "<a href=\"$script?plugin=minicalendar_viewer&amp;file=$enc_pagename&amp;date=$left_YM&amp;date_sep=$s_date_sep&amp;mode=$mode\">$left_text</a>";
    }
  }else{
    $left_link = "";
  }
  if ($right_YM != ""){
    if ($right_YMX != '') {
      $right_link = "<a href=\"$script?plugin=minicalendar&amp;file=$enc_pagename&amp;date=$right_YMX\">$right_text</a>";
    } else {
      $right_link = "<a href=\"$script?plugin=minicalendar_viewer&amp;file=$enc_pagename&amp;date=$right_YM&amp;date_sep=$s_date_sep&amp;mode=$mode\">$right_text</a>";
    }
  }else {
    $right_link = "";
  }

  //past mode��<<�� ��>> ¾��<<�� ��>>
//$return_body .= "<br /><table width=\"90%\" align=\"center\"><tr><td align=\"left\">";
//$return_body .= $left_link;
//$return_body .= "</td><td align=\"right\">";
//$return_body .= $right_link;
//$return_body .= "</td></tr></table>";
  $return_body .= "<div class=\"prevnext\">";
  $return_body .= "<div class=\"prevnext_r\">";
  $return_body .= $right_link;
  $return_body .= "</div>";
  $return_body .= "<div class=\"prevnext_l\">";
  $return_body .= $left_link;
  $return_body .= "</div>";
  $return_body .= "</div><br style=\"display:block;\" />";

  $get["page"] = $tmppage;
  $post["page"] = $tmppage;
  $vars["page"] = $tmppage;


  return $return_body;
}

function plugin_minicalendar_viewer_action()
{
  global $WikiName,$BracketName,$vars,$get,$post,$hr,$script;
  $date_sep = "-";

  $return_vars_array = array();

  $page = strip_bracket($vars['page']);
  $vars['page'] = '*';
  if (isset($vars['file'])) $vars['page'] = $vars['file'];

  $date_sep = $vars['date_sep'];

  $page_YM = $vars['date'];
  if ($page_YM == ""){
    $page_YM = get_date("Y".$date_sep."m");
  }
  $mode = $vars['mode'];

  $args_array = array($vars['page'], $page_YM, $mode, $date_sep);
  $return_vars_array["body"] = call_user_func_array("plugin_minicalendar_viewer_convert",$args_array);

  //$return_vars_array["msg"] = "minicalendar_viewer ".$vars["page"]."/".$page_YM;
  $return_vars_array["msg"] = "minicalendar_viewer ".htmlspecialchars($vars["page"]);
  if ($vars["page"] != ""){
    $return_vars_array["msg"] .= "/";
  }
  if (preg_match("/\*/",$page_YM)){
    //������n��ɽ���λ��Ϥʤ�ƥڡ���̾�ˤ����餤����
  }else{
    $return_vars_array["msg"] .= htmlspecialchars($page_YM);
  }

  //Patched By miko
  $vars['cmd'] = 'read';
  $vars['page'] = $page;
  return $return_vars_array;
}

function plugin_minicalendar_viewer_isValidDate($aStr, $aSepList="-/ .") {
  //$aSepList=""�ʤ顢yyyymmdd�Ȥ��ƥ����å��ʼ�ȴ��(^^;��
  if ($aSepList == "") {
    //yyyymmdd�Ȥ��ƥ����å�
    return checkdate(substr($aStr,4,2),substr($aStr,6,2),substr($aStr,0,4));
  }
  if ( ereg("^([0-9]{2,4})[$aSepList]([0-9]{1,2})[$aSepList]([0-9]{1,2})$", $aStr, $m) ) {
    return checkdate($m[2], $m[3], $m[1]);
  }
  return false;
}

?>
