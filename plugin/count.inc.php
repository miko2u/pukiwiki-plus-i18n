<?php
/**
 * Count Plugin
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: count.inc.php,v 0.2 2007/05/16 22:27:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */
function plugin_count_inline()
{
	$argv = func_get_args();
	$argc = func_num_args();

	$data = $argv[ --$argc ];

        $field = array('no','pref');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($no)) $no = 0;
	if ($no < 0 || $no > 5) {
		return 'Please specify it within the range from 1 to 5.';
	}

	return count_files($no, $pref);
}

function count_files($no=0, $pref='')
{
	// 0:DATA, 1:TB, 2:Referer, 3: DIFF, 4:BKUP, 5:CTR
	static $dir = array(DATA_DIR,TRACKBACK_DIR,REFERER_DIR,DIFF_DIR,BACKUP_DIR,COUNTER_DIR);
	static $ext = array('.txt'  ,'.txt'       ,'.ref'     ,'.txt'  ,BACKUP_EXT,'.count');

	// コンテンツ管理者以上は、全てのファイルを対象にする
	if (! auth::check_role('role_adm_contents')) {
		$pages = get_existpages($dir[$no], $ext[$no]);
	} else {
		// 自分が閲覧できるページ数のみ戻す
		$pages = auth::get_existpages($dir[$no], $ext[$no]);
	}

	// 条件なし
	if (empty($pref)) {
		return count($pages);
	}

	// 指定文書のカウント
	$i = 0;
	foreach($pages as $page) {
		if (strpos($page,$pref) === 0) {
			$i++;
		}
	}
	return $i;
}

?>
