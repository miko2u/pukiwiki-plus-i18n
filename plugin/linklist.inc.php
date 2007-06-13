<?php
/*
 * PukiWiki 自動相互リンク作成プラグイン
 *
 * @copyright   Copyright &copy; 2004-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: linklist.inc.php,v 0.6 2007/06/13 19:12:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

// 構成定義ファイル
define('CONFIG_REFERER','plugin/referer/config');
define('CONFIG_LINKLIST','plugin/linklist/config');

function plugin_linklist_init()
{
	$msg = array(
		'_linklist_msg' => array(
			'h5_title'	=> _('Auto Mutual link'),
			'title'		=> _('Auto Mutual link of %s'),
			'not_effective'	=> _('The function of Referer is not effective.'),
			'no_data'	=> _('no data.'),
		)
	);
	set_plugin_messages($msg);
}

function plugin_linklist_action()
{
	global $vars;
	global $_linklist_msg;
	global $referer;

	$page = (empty($vars['page'])) ? '' : htmlspecialchars($vars['page'], ENT_QUOTES);
	$retval['msg']  = sprintf($_linklist_msg['title'],$page);
	if (! $referer) {
		$retval['body'] = '<div>'.$_linklist_msg['not_effective']."</div>\n";
		return $retval;
	}

	$max  = (empty($vars['max'])) ? -1 : htmlspecialchars($vars['max'], ENT_QUOTES);
	$data = ref_get_data($page);

	//  データ無し
	if (count($data) == 0)
	{
		$retval['body'] = '<div>'.$_linklist_msg['no_data']."</div>\n";
		return $retval;
	}

	$data = linklist_analysis($data);
	// 0:検索キー 1:参照カウンタ
	usort($data,create_function('$a,$b','return $b[1] - $a[1];'));
	$data = linklist_print($data,$max,0);

	$retval['body']  = '<div>';
	$retval['body'] .= (empty($data)) ? $_linklist_msg['no_data'] : $data;
	$retval['body'] .= "</div>\n";
	return $retval;
}

function plugin_linklist_convert()
{
	global $vars;
	global $_linklist_msg;
	global $referer;

	if (! $referer) return;

	list($page,$max) = func_get_args();
	if (empty($page)) $page = $vars['page'];
	$max  = (empty($max)) ? -1 : htmlspecialchars($max, ENT_QUOTES);

	$data = ref_get_data($page);
	if (count($data) == 0) return; //  データ無し
	$data = linklist_analysis($data);
	// 0:検索キー 1:参照カウンタ
	usort($data,create_function('$a,$b','return $b[1] - $a[1];'));
	$data = linklist_print($data,$max,1);
	return '<div>'.$data."</div>\n";
}

// 構成定義ファイル読込
function linklist_config_load()
{
	global $config_linklist;

	// config.php
	if (!isset($config_linklist))
	{
		$config = new Config(CONFIG_LINKLIST);
		$config->read();
		$config_linklist['spam'] = $config->get('SPAM');
		$config_linklist['misc'] = $config->get('MISC');
		$config_linklist['key']  = $config->get('KEY');
		unset($config);
	}

}

// データを解析
function linklist_analysis($data)
{
	global $script;
	global $config_linklist;

	// 構成定義ファイル読込
	linklist_config_load();
	$IgnoreHost = array_merge($config_linklist['spam'], $config_linklist['misc']);

	$rc = array();
	$i = 0;

	// 自サイトの特定
	$my = parse_url($script);
	$my = $my['host'];

	// 0:最終更新日時 1:初回登録日時 2:参照カウンタ 3:Referer ヘッダ 4:利用可否フラグ(1は有効)
	foreach ($data as $x)
	{
		if ($x[4] != 1) continue;
		// 'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'
		$url = parse_url($x[3]);
		if (empty($url['host'])) continue;
		if (strpos($url['host'],'.') == '') continue; // ホスト名にピリオドが１つもない
		if (linklist_ignore_check($url['host'])) continue;

		$sw = 0;
		foreach ($IgnoreHost as $y) {
			if (strpos($url['host'],$y) !== FALSE) {
				$sw = 1;
				continue;
			}
		}
		if ($sw) continue;

		if (strpos($url['host'],$my) !== FALSE) continue;

		$sw = 0;
		// queryストリングの解析
		$tok = strtok($url['query'],'&');
		while($tok) {
			list($key,$parm)= split ('=', $tok); // キーと値に分割
			$tok = strtok('&'); // 次の処理の準備

			// 検索キーかの判定
			$skey = '';

			foreach ($config_linklist['key'] as $y)
			{
				if ( (strpos($key,$y) === 0 )) {
					$skey = $y;
					continue;
				}
			}
			if ($skey !== $key) continue;
			if (empty($parm)) continue; // 値が入っていない場合

			// 検索エンジンからきたもの
			$sw = 1;
			break;
		}

		// 検索エンジン以外 かつ 架空ホスト以外 の場合は蓄積
		// if (!$sw and linklist_testipaddress($url['host']) )
		// 検索エンジン以外の場合は蓄積
		if (!$sw)
		{
			$rc[$i][0] = $x[3];	// 3:Referer ヘッダ
			$rc[$i][1] = $x[2];	// 参照カウンタ
			$i++;
		}
	}
	return $rc;
}

// データを加工
function linklist_print($data,$max,$title)
{
	global $_linklist_msg;

	// 無制限は、-1 のために判断
	if ($max > 0)
	{
		$data = array_splice($data,0,$max);
	}
	$i = count($data);
	if ($i == 0) return;

	$rc = '';
	if ($title)
	{
		$rc .= '<h5>'.$_linklist_msg['h5_title'].' ';
		$rc .= ($max > 0) ? $max : $i;
		$rc .= "</h5>\n";
	}

	$rc .= "<ul>\n";
	foreach ($data as $x)
	{
		$str = rawurldecode($x[0]);
		$str = mb_convert_encoding($str,SOURCE_ENCODING,'auto');
		$tmp = '<a href="'.$x[0].'">'.$str.'</a>('.$x[1].')';
		$rc .= '<li>'.$tmp."</li>\n";
	}
	$rc .= "</ul>\n";
	return $rc;
}

function linklist_ignore_check($url)
{
	static $ignore_url;

	// config.php
	if (!isset($ignore_url))
	{
		$config = new Config(CONFIG_REFERER);
		$config->read();
		$ignore_url = $config->get('IGNORE');
		unset($config);
	}

	foreach ($ignore_url as $x)
	{
		if (strpos($url,$x) !== FALSE)
		{
			return 1;
		}
	}
	return 0;
}

// ホスト名からIPアドレスに変換して評価する
function linklist_testipaddress ($host)
{
	$ip = gethostbyname($host); // ホスト名からIPアドレスを得る
	if ($ip == $host)
	{
		// そもそも IPアドレスが指定されている場合の考慮
		$name = @gethostbyaddr($host);	// IPアドレスからホスト名を得る
		if (!empty($name)) return 1;	// lookup できた
		return 0; // 変換不能
	}
	return 1; // IP アドレス変換できた
}

?>
