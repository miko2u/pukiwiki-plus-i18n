<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: backup.inc.php,v 1.19.7 2005/03/10 12:20:34 miko Exp $
//
// バックアップ
function plugin_backup_action()
{
	global $script, $vars, $do_backup, $hr;
//	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_backup;
//	global $_msg_visualdiff, $_msg_view, $_msg_goto, $_msg_deleted;
//	global $_title_backupdiff, $_title_backupnowdiff, $_title_backupsource;
//	global $_title_backup, $_title_pagebackuplist, $_title_backuplist;

$_title_backuplist     = _('Backup list');
$_msg_diff             = _('diff');
$_msg_nowdiff          = _('diff current');
$_msg_source           = _('source');
$_msg_backup           = _('backup');
$_msg_visualdiff       = _('diff for visual');
$_msg_view             = _('View the $1.');
$_msg_goto             = _('Go to $1.');
$_msg_deleted          = _(' $1 has been deleted.');
$_title_backupdiff     = _('Backup diff of $1(No. $2)');
$_title_backupnowdiff  = _('Backup diff of $1 vs current(No. $2)');
$_title_backupsource   = _('Backup source of $1(No. $2)');
$_title_backup         = _('Backup of $1(No. $2)');
$_title_pagebackuplist = _('Backup list of $1');
$_title_backuplist     = _('Backup list');

	if (! $do_backup) return;

	$page = isset($vars['page']) ? $vars['page']  : '';
	if ($page == '') return array('msg'=>$_title_backuplist, 'body'=>get_backup_list_all());

	check_readable($page, true, true);
	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	$action = isset($vars['action']) ? $vars['action'] : '';
	if ($action == 'delete') return plugin_backup_delete($page);

	$s_action = $r_action = '';
	if ($action != '') {
		$s_action = htmlspecialchars($action);
		$r_action = rawurlencode($action);
	}

	$s_age  = (isset($vars['age']) && is_numeric($vars['age'])) ? $vars['age'] : 0;
	if ($s_age == 0) return array( 'msg'=>$_title_pagebackuplist, 'body'=>get_backup_list($page));


	$body  = "<ul>\n";
	$body .= " <li><a href=\"$script?cmd=backup\">$_msg_backuplist</a></li>\n";

	$href = "$script?cmd=backup&amp;page=$r_page&amp;age=$s_age";

	$is_page = is_page($page);
	if ($is_page) {
		if ($action != 'diff')
			$body .= ' <li>' . str_replace('$1', "<a href=\"$href&amp;action=diff\">$_msg_diff</a>", $_msg_view) . "</li>\n";
		if ($action != 'nowdiff')
			$body .= ' <li>' . str_replace('$1', "<a href=\"$href&amp;action=nowdiff\">$_msg_nowdiff</a>", $_msg_view) . "</li>\n";
		if ($action != 'visualdiff')
			$body .= ' <li>' . str_replace('$1', "<a href=\"$href&amp;action=visualdiff\">$_msg_visualdiff</a>", $_msg_view) . "</li>\n";
	}

	if ($action != 'source')
		$body .= ' <li>' . str_replace('$1', "<a href=\"$href&amp;action=source\">$_msg_source</a>", $_msg_view) . "</li>\n";

	if ($action)
		$body .= ' <li>' . str_replace('$1', "<a href=\"$href\">$_msg_backup</a>", $_msg_view) . "</li>\n";

	if ($is_page) {
		$body .= ' <li>' . str_replace('$1', "<a href=\"$script?$r_page\">$s_page</a>", $_msg_goto) . "\n";
	} else {
		$body .= ' <li>' . str_replace('$1', $s_page, $_msg_deleted) . "\n";
	}

	$backups = get_backup($page);
if ($action != 'visualdiff') {
	if (! empty($backups)) {
		$body .= "  <ul>\n";
		foreach($backups as $age => $val) {
			$date = format_date($val['time'], TRUE);
			$body .= ($age == $s_age) ?
				"   <li><em>$age $date</em></li>\n" :
				"   <li><a href=\"$script?cmd=backup&amp;action=$r_action&amp;page=$r_page&amp;age=$age\">$age $date</a></li>\n";
		}
		$body .= "  </ul>\n";
	}
	$body .= " </li>\n";
	$body .= "</ul>\n";
}
	if ($action == 'diff') {
		$old = ($s_age > 1) ? join('', $backups[$s_age - 1]['data']) : '';
		$cur = join('', $backups[$s_age]['data']);
		$body .= plugin_backup_diff(do_diff($old, $cur));
		return array('msg'=>str_replace('$2', $s_age, $_title_backupdiff), 'body'=>$body);

	} else if ($s_action == 'nowdiff') {
		$old = join('', $backups[$s_age]['data']);
		$cur = join('', get_source($page));
		$body .= plugin_backup_diff(do_diff($old, $cur));
		return array('msg'=>str_replace('$2', $s_age, $_title_backupnowdiff), 'body'=>$body);

	} else if ($s_action == 'visualdiff') {
		$old = join('', $backups[$s_age]['data']);
		$cur = join('', get_source($page));
		$source = do_diff($old,$cur);
		$source = plugin_backup_visualdiff($source);
		$body .= "$hr\n".drop_submit(convert_html($source));
		$body = preg_replace('#<p>\#spandel(.*?)(</p>)#si', '<span class="remove_word">$1', $body);
		$body = preg_replace('#<p>\#spanadd(.*?)(</p>)#si', '<span class="add_word">$1', $body);
		$body = preg_replace('#<p>\#spanend(.*?)(</p>)#si', '$1</span>', $body);
		$body = preg_replace('#&amp;spandel;#i', '<span class="remove_word">', $body);
		$body = preg_replace('#&amp;spanadd;#i', '<span class="add_word">', $body);
		$body = preg_replace('#&amp;spanend;#i', '</span>', $body);
		return array('msg'=>str_replace('$2',$s_age,$_title_backupnowdiff),'body'=>$body);
	} else if ($s_action == 'source') {
		$body .= '<pre>' . htmlspecialchars(join('', $backups[$s_age]['data'])) . "</pre>\n";
		return array('msg'=>str_replace('$2', $s_age, $_title_backupsource), 'body'=>$body);

	} else {
		$body .= "$hr\n" . drop_submit(convert_html($backups[$s_age]['data']));
		return array('msg'=>str_replace('$2', $s_age, $_title_backup), 'body'=>$body);
	}
}

// バックアップを削除
function plugin_backup_delete($page)
{
	global $script, $vars;
//	global $_title_backup_delete, $_title_pagebackuplist, $_msg_backup_deleted;
//	global $_msg_backup_adminpass, $_btn_delete, $_msg_invalidpass;

$_title_backup_delete  = _('Deleting backup of $1');
$_title_backuplist     = _('Backup list');
$_msg_backup_deleted   = _('Backup of $1 has been deleted.');
$_msg_backup_adminpass = _('Please input the password for deleting.');
$_btn_delete    = _('Delete');
$_msg_invalidpass = _('Invalid password.');

	if (! _backup_file_exists($page))
		return array('msg'=>$_title_pagebackuplist, 'body'=>get_backup_list($page)); // Say "is not found"

	$body = '';
	if (isset($vars['pass'])) {
		if (pkwk_login($vars['pass'])) {
			_backup_delete($page);
			return array(
				'msg'  => $_title_backup_delete,
				'body' => str_replace('$1',make_pagelink($page),$_msg_backup_deleted)
			);
		} else {
			$body = "<p><strong>$_msg_invalidpass</strong></p>\n";
		}
	}

	$s_page = htmlspecialchars($page);
	$body .= <<<EOD
<p>$_msg_backup_adminpass</p>
<form action="$script" method="post">
 <div>
  <input type="hidden"   name="cmd"    value="backup" />
  <input type="hidden"   name="page"   value="$s_page" />
  <input type="hidden"   name="action" value="delete" />
  <input type="password" name="pass"   size="12" />
  <input type="submit"   name="ok"     value="$_btn_delete" />
 </div>
</form>
EOD;
	return	array('msg'=>$_title_backup_delete, 'body'=>$body);
}

function plugin_backup_visualdiff($str)
{
	$str = preg_replace('/^(\x20)(.*)$/m', "\x08$2", $str);
	$str = preg_replace('/^(\-)(\x20|#\x20|\-\-\-|\-\-|\-|\+\+\+|\+\+|\+|>|>>|>>>)(.*)$/m', "\x08$2&spandel;$3&spanend;", $str);
	$str = preg_replace('/^(\+)(\x20|#\x20|\-\-\-|\-\-|\-|\+\+\+|\+\+|\+|>|>>|>>>)(.*)$/m', "\x08$2&spanadd;$3&spanend;", $str);
	$str = preg_replace('/^(\-)(.*)$/m', "#spandel\n$2\n#spanend", $str);
	$str = preg_replace('/^(\+)(.*)$/m', "#spanadd\n$2\n#spanend", $str);
	$str = preg_replace('/^(\x08)(.*)$/m', '$2', $str);
	$str = trim($str);
	return $str;
}

function plugin_backup_diff($str)
{
//	global $_msg_addline, $_msg_delline;
	global $hr;
	$_msg_addline = _('The added line is <span class="diff_added">THIS COLOR</span>.');
	$_msg_delline = _('The deleted line is <span class="diff_removed">THIS COLOR</span>.');

	$str = htmlspecialchars($str);
	$str = preg_replace('/^(\-)(.*)$/m', '<span class="diff_removed"> $2</span>', $str);
	$str = preg_replace('/^(\+)(.*)$/m', '<span class="diff_added"> $2</span>', $str);
	$str = trim($str);
	$str = <<<EOD
$hr
<ul>
 <li>$_msg_addline</li>
 <li>$_msg_delline</li>
</ul>
<pre>$str</pre>
EOD;

	return $str;
}

// バックアップ一覧を取得
function get_backup_list($page)
{
	global $script;
//	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_nobackup;
//	global $_title_backup_delete;

$_msg_backuplist       = _('List of Backups');
$_msg_nobackup         = _('There are no backup(s) of $1.');
$_msg_diff             = _('diff');
$_msg_nowdiff          = _('diff current');
$_msg_source           = _('source');
$_title_backup_delete  = _('Deleting backup of $1');

	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$retval = array();
	$retval[0] = <<<EOD
<ul>
 <li><a href="$script?cmd=backup">$_msg_backuplist</a>
  <ul>
EOD;
	$retval[1] = "\n";
	$retval[2] = <<<EOD
  </ul>
 </li>
</ul>
EOD;

	$backups = _backup_file_exists($page) ? get_backup($page) : array();
	if (empty($backups)) {
		$msg = str_replace('$1', make_pagelink($page), $_msg_nobackup);
		$retval[1] .= "   <li>$msg</li>\n";
		return join('', $retval);
	}
	$retval[1] .= "   <li><a href=\"$script?cmd=backup&amp;action=delete&amp;page=$r_page\">";
	$retval[1] .= str_replace('$1', $s_page, $_title_backup_delete);
	$retval[1] .= "</a></li>\n";
	foreach ($backups as $age=>$data) {
		$date = format_date($data['time'], TRUE);
		$href = "$script?cmd=backup&amp;page=$r_page&amp;age=$age";
		$retval[1] .= <<<EOD
   <li><a href="$href">$age $date</a>
     [ <a href="$href&amp;action=diff">$_msg_diff</a>
     | <a href="$href&amp;action=nowdiff">$_msg_nowdiff</a>
     | <a href="$href&amp;action=source">$_msg_source</a>
     ]
   </li>
EOD;
	}
	return join('', $retval);
}

// 全ページのバックアップ一覧を取得
function get_backup_list_all($withfilename = FALSE)
{
	global $cantedit;

	$pages = array_diff(get_existpages(BACKUP_DIR, BACKUP_EXT), $cantedit);

	if (count($pages) == 0)
		return '';

	return page_list($pages, 'backup', $withfilename);
}

// バックアップのドロップダウンコンボボックスを作成
function plugin_backup_convert()
{
	global $script, $vars;

        // Get arguments
	$with_label = TRUE;
	$args = func_get_args();
	while (isset($args[0])) {
		switch(array_shift($args)) {
		case 'default'    : $diff_mode = 0; break;
		case 'nowdiff'    : $diff_mode = 1; break;
		case 'visualdiff' : $diff_mode = 2; break;
		case 'label'      : $with_label = TRUE;  break;
		case 'nolabel'    : $with_label = FALSE; break;
                }
	}

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$retval = array();
	$date = get_date("m/d", get_filetime($page));
	if ($with_label) {
	$retval[0] = <<<EOD
<form class="center_form" action=""><div><label>Versions:
<select onchange="javascript:location.href=this[this.selectedIndex].value">\n
EOD;
	$retval[1] = "\n";
	$retval[2] = <<<EOD
</select></label></div>
</form>\n
EOD;
	} else {
	$retval[0] = <<<EOD
<form class="center_form" action=""><div>
<select onchange="javascript:location.href=this[this.selectedIndex].value">\n
EOD;
	$retval[1] = "\n";
	$retval[2] = <<<EOD
</select>
</div></form>\n
EOD;
	}

	$backups = _backup_file_exists($page) ? get_backup($page) : array();
	if (count($backups) == 0)
	{
		$retval[1] .= "<option value=\"$script?$r_page\" selected=\"selected\">→ $date(No.1)</option>\n";
		return join('',$retval);
	}
	$maxcnt = count($backups) + 1;
	$retval[1] .= "<option value=\"$script?$r_page\" selected=\"selected\">→ $date(No.$maxcnt)</option>\n";
	$backups = array_reverse($backups, True);
	foreach ($backups as $age=>$data) {
		$date = get_date("m/d", $data['time']);
		$href = "$script?cmd=backup&amp;page=$r_page&amp;age=$age";
		if ($diff_mode == 2) {
			$retval[1] .= "<option value=\"$href&amp;action=visualdiff\">$date (No.$age)</option>\n";
		} else if ($diff_mode == 1) {
			$retval[1] .= "<option value=\"$href&amp;action=nowdiff\">$date (No.$age)</option>\n";
		} else {
			$retval[1] .= "<option value=\"$href\">$date (No.$age)</option>\n";
		}
	}
	return join('',$retval);
}
?>
