<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: backup.inc.php,v 1.27.19 2008/01/05 18:07:00 upk Exp $
// Copyright (C)
//   2005-2008 PukiWiki Plus! Team
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Backup plugin

// Prohibit rendering old wiki texts (suppresses load, transfer rate, and security risk)
// define('PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING', PKWK_SAFE_MODE || PKWK_OPTIMISE);
define('PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING', auth::check_role('safemode') || PKWK_OPTIMISE);

function plugin_backup_action()
{
	global $vars, $do_backup, $hr, $script;
//	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_backup;
//	global $_msg_view, $_msg_goto, $_msg_deleted;
//	global $_msg_visualdiff;
//	global $_title_backupdiff, $_title_backupnowdiff, $_title_backupsource;
//	global $_title_backup, $_title_pagebackuplist, $_title_backuplist;

$_msg_backuplist       = _('Backup list');
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
	if ($page == '') return array('msg'=>$_title_backuplist, 'body'=>plugin_backup_get_list_all());

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
	if ($s_age <= 0) return array( 'msg'=>$_title_pagebackuplist, 'body'=>plugin_backup_get_list($page));

	$body  = '<ul>' . "\n";
	$body .= ' <li><a href="' . $script . '?cmd=backup">' . $_msg_backuplist . '</a></li>' ."\n";

	$href    = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=' . $s_age;
	$is_page = is_page($page);

	if ($is_page && $action != 'diff')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=diff">' . $_msg_diff . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($is_page && $action != 'nowdiff')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=nowdiff">' . $_msg_nowdiff . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($is_page && $action != 'visualdiff')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=visualdiff">' . $_msg_visualdiff . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($action != 'source')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=source">' . $_msg_source . '</a>',
			$_msg_view) . '</li>' . "\n";

	if (! PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING && $action)
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'">' . $_msg_backup . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($is_page) {
		$body .= ' <li>' . str_replace('$1',
			'<a href="' . get_page_uri($page) . '">' . $s_page . '</a>',
			$_msg_goto) . "\n";
	} else {
		$body .= ' <li>' . str_replace('$1', $s_page, $_msg_deleted) . "\n";
	}

	$backups = get_backup($page);
	$backups_count = count($backups);
	if ($s_age > $backups_count) $s_age = $backups_count;

	if ($backups_count > 0 && $action != 'visualdiff') {
		$body .= '  <ul>' . "\n";
		foreach($backups as $age => $val) {
			$time = (isset($val['real'])) ? $val['real'] : $val['time'];
			$date = format_date($time, TRUE);
			$body .= ($age == $s_age) ?
				'   <li><em>' . $age . ' ' . $date . '</em></li>' . "\n" :
				'   <li><a href="' . $script . '?cmd=backup&amp;action=' .
				$r_action . '&amp;page=' . $r_page . '&amp;age=' . $age .
				'">' . $age . ' ' . $date . '</a></li>' . "\n";
		}
		$body .= '  </ul>' . "\n";
	}
	$body .= ' </li>' . "\n";
	$body .= '</ul>'  . "\n";

	if ($action == 'diff') {
		if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );
		$title = & $_title_backupdiff;
		$old = ($s_age > 1) ? join('', $backups[$s_age - 1]['data']) : '';
		$cur = join('', $backups[$s_age]['data']);
		auth::is_role_page($old);
		auth::is_role_page($cur);
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'nowdiff') {
		if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );
		$title = & $_title_backupnowdiff;
		$old = join('', $backups[$s_age]['data']);
		$cur = join('', get_source($page));
		auth::is_role_page($old);
                auth::is_role_page($cur);
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'visualdiff') {
		$old = join('', $backups[$s_age]['data']);
		$cur = join('', get_source($page));
		auth::is_role_page($old);
                auth::is_role_page($cur);
		$source = do_diff($old,$cur);
		$source = plugin_backup_visualdiff($source);
		$body .= "$hr\n" . drop_submit(convert_html($source));
		$body = preg_replace('#<p>\#spandel(.*?)(</p>)#si', '<span class="remove_word">$1', $body);
		$body = preg_replace('#<p>\#spanadd(.*?)(</p>)#si', '<span class="add_word">$1', $body);
		$body = preg_replace('#<p>\#spanend(.*?)(</p>)#si', '$1</span>', $body);
		$body = preg_replace('#&amp;spandel;#i', '<span class="remove_word">', $body);
		$body = preg_replace('#&amp;spanadd;#i', '<span class="add_word">', $body);
		$body = preg_replace('#&amp;spanend;#i', '</span>', $body);
		$title = & $_title_backupnowdiff;
	} else if ($s_action == 'source') {
		if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );
		$title = & $_title_backupsource;
		auth::is_role_page($backups[$s_age]['data']);
		$body .= '<pre>' . htmlspecialchars(join('', $backups[$s_age]['data'])) .
			'</pre>' . "\n";
	} else {
		if (PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			die_message( _('This feature is prohibited') );
		} else {
			$title = & $_title_backup;
			auth::is_role_page($backups[$s_age]['data']);
			$body .= $hr . "\n" .
				drop_submit(convert_html($backups[$s_age]['data']));
		}
	}

	return array('msg'=>str_replace('$2', $s_age, $title), 'body'=>$body);
}

// Delete backup
function plugin_backup_delete($page)
{
	global $vars,$script;
//	global $_title_backup_delete, $_title_pagebackuplist, $_msg_backup_deleted;
//	global $_msg_backup_adminpass, $_btn_delete, $_msg_invalidpass;

$_title_backup_delete  = _('Deleting backup of $1');
$_title_pagebackuplist = _('Backup list of $1');
$_title_backuplist     = _('Backup list');
$_msg_backup_deleted   = _('Backup of $1 has been deleted.');
$_msg_backup_adminpass = _('Please input the password for deleting.');
$_btn_delete      = _('Delete');
$_msg_invalidpass = _('Invalid password.');

	if (! _backup_file_exists($page))
		return array('msg'=>$_title_pagebackuplist, 'body'=>plugin_backup_get_list($page)); // Say "is not found"

	$body = '';

	if (! auth::check_role('role_adm_contents')) {
		_backup_delete($page);
		return array(
			'msg'  => $_title_backup_delete,
			'body' => str_replace('$1', make_pagelink($page), $_msg_backup_deleted)
		);
        }

	if (isset($vars['pass'])) {
		if (pkwk_login($vars['pass'])) {
			_backup_delete($page);
			return array(
				'msg'  => $_title_backup_delete,
				'body' => str_replace('$1', make_pagelink($page), $_msg_backup_deleted)
			);
		} else {
			$body = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
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

function plugin_backup_diff($str)
{
//	global $_msg_addline, $_msg_delline;
	global $hr;
	$_msg_addline = _('The added line is <span class="diff_added">THIS COLOR</span>.');
	$_msg_delline = _('The deleted line is <span class="diff_removed">THIS COLOR</span>.');

	$ul = <<<EOD
$hr
<ul>
 <li>$_msg_addline</li>
 <li>$_msg_delline</li>
</ul>
EOD;

	return $ul . '<pre>' . diff_style_to_css(htmlspecialchars($str)) . '</pre>' . "\n";
}

function plugin_backup_get_list($page)
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
		$retval[1] .= '   <li>' . $msg . '</li>' . "\n";
		return join('', $retval);
	}

	// if (! PKWK_READONLY) {
	if (! auth::check_role('readonly')) {
		$retval[1] .= '   <li><a href="' . $script . '?cmd=backup&amp;action=delete&amp;page=' . $r_page . '">';
		$retval[1] .= str_replace('$1', $s_page, $_title_backup_delete);
		$retval[1] .= '</a></li>' . "\n";
	}

	$href = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=';
	$_anchor_from = $_anchor_to   = '';
	$safemode = auth::check_role('safemode');
	foreach ($backups as $age=>$data) {
		if (! PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			$_anchor_from = '<a href="' . $href . $age . '">';
			$_anchor_to   = '</a>';
		}
		$time = (isset($data['real'])) ? $data['real'] : $data['time'];
		$date = format_date($time, TRUE);
		$retval[1] .= <<<EOD
   <li>$_anchor_from$age $date$_anchor_to
EOD;

		if (! $safemode) {
			$retval[1] .= <<<EOD
     [ <a href="$href$age&amp;action=diff">$_msg_diff</a>
     | <a href="$href$age&amp;action=nowdiff">$_msg_nowdiff</a>
     | <a href="$href$age&amp;action=source">$_msg_source</a>
     ]
EOD;
		}

		$retval[1] .= <<<EOD
   </li>
EOD;
	}

	return join('', $retval);
}

// List for all pages
function plugin_backup_get_list_all($withfilename = FALSE)
{
	global $cantedit;

	if (auth::check_role('safemode')) die_message( _('PKWK_SAFE_MODE prohibits this') );

	$pages = array_diff(auth::get_existpages(BACKUP_DIR, BACKUP_EXT), $cantedit);

	if (empty($pages)) {
		return '';
	} else {
		return page_list($pages, 'backup', $withfilename);
	}
}

// Plus! Extend - Diff
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

// Plus! Extend - Create Combobox for Backup
function plugin_backup_convert()
{
	global $vars, $script;
//	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_nobackup;
//	global $_title_backup_delete;

$_msg_backuplist       = _('List of Backups');
$_msg_diff             = _('diff');
$_msg_nowdiff          = _('diff current');
$_msg_source           = _('source');
$_msg_nobackup         = _('There are no backup(s) of $1.');
$_title_backup_delete  = _('Deleting backup of $1');

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
		$retval[1] .= '<option value="' . get_page_uri($page) . '" selected="selected">' . _('->') . " $date(No.1)</option>\n";
		return join('',$retval);
	}
	$maxcnt = count($backups) + 1;
	$retval[1] .= '<option value="' . get_page_uri($page) . '" selected="selected">' . _('->') . " $date(No.$maxcnt)</option>\n";
	$backups = array_reverse($backups, True);
	foreach ($backups as $age=>$data) {
		$time = (isset($data['real'])) ? $data['real'] : $data['time'];
		$date = get_date('m/d', $time);
		$href = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=' . $age;

		$retval[1] .= '<option value="'.$href;
		switch($diff_mode) {
		case 2:
			$retval[1] .= '&amp;action=visualdiff';
			break;
		case 1:
			$retval[1] .= '&amp;action=nowdiff';
			break;
		}
		$retval[1] .= '">'.$date.' (No.'.$age.')</option>'."\n";

	}
	return join('',$retval);
}
?>
