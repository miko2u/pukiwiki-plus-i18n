<?php
/*
 * $Id$
 * 
 * License:  GNU General Public License
 *
 * Copyright (c) 2005 in3c.org
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 *
 * MODIFICATION
 * 2006-08-21 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 *
 */

// :config/plugin/chart/
define('CHART_CONFIG_PREFIX', 'plugin/chart/');
// 設定名を省略した時の設定名
define('CHART_DEFAULT_CONFIG_NAME', 'default');
// 設定ページから読み出す見出し名
define('CHART_CONFIG_TITLE', 'chart_setting');

// ykcee 保存先
define('CHART_YKCEE_DIR', LIB_DIR.'ykcee/');
// エラー表示に使用するデフォルトフォント
define('CHART_ERROR_FONT', CHART_YKCEE_DIR.'VERDANA.TTF');

function plugin_chart_init()
{
	$msg = array(
	  '_chart_msg' => array(
		'ParameterError'	=> _("<p>parameter error</p>"),
		'GDNotSupport'		=> _("<p>GD is not supported.</p>"),
		// 日本語対応の GD でない場合には文字化けするため、このままとする
		'NoData'		=> "Sorry, There is no data",
		'CannotReadConfig'	=> "cannot read config",
		'CannotReadTable'	=> "cannot read table",
	  ),
	);
	set_plugin_messages($msg);
}

/**
 * 書式
 * 
 *     #chart(title,[page],[設定名])
 * 
 * 種別 
 *     ブロック型プラグイン
 *
 * 概要
 *      表を読み込んでグラフを表示します。
 *      表はページ名と見出し名で特定され、内部的にConfigクラスを
 *      を使用して読み込まれます。
 *      グラフの描画はykcee(http://ykcee.sourceforge.net/)を使用します。
 *      描画の際の色などは、:config/plugin/chart/設定名のファイルを使用して
 *      決定します。
 * 
 * 使用例
 *      #bliki(my_chart)          → 現在のページにあるレベル1みだし「my_chart」の中に書かれた表を
 *                                   :config/plugin/chart/defaultの設定に基づいて描画します。
 *      #bliki(my_chart,foo)      → ページfooにあるレベル1みだし「my_chart」の中に書かれた表を
 *                                   :config/plugin/chart/defaultの設定に基づいて描画します。
 *      #bliki(my_chart,foo,bar)  → ページfooにあるレベル1みだし「my_chart」の中に書かれた表を
 *                                   :config/plugin/chart/barの設定に基づいて描画します。
 *      #bliki(my_chart,,bar)     → 現在のページにあるレベル1みだし「my_chart」の中に書かれた表を
 *                                   :config/plugin/chart/barの設定に基づいて描画します。
 *
 * 設定ファイル
 *      設定を変更する場合は、:config/plugin/chart/defaultをコピーして複製してください。
 *      各パラーメータの意味については、下記ページの同名のSet関数の説明を参照して下さい。
 *
 *        http://ykcee.sourceforge.net/index.php?MAINNAV=2
 *
 * 表の書き方
 *
 *      - lib/config.phpを流用していますので、:config/plugin/
 *        配下のページと同様のルールで記述する必要があります。
 *      - レベル1の見出しの次にテーブルを定義してください。
 *      - テーブルを書いたら、その次に、別のレベル1の見出しを作るか、
 *        そのページをそこまでで終了させてください。
 *      - 1番左の列は、横軸の座標になります。
 *      - 2番目, 3番目・・・の列は、2, 3, 4・・といった系列のデータになります。
 *
 * @author Yuki SHIDA <shida@in3c.org>
 * @copyright Copyright &copy; in3c.org
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Revision$
 * 
 */

function plugin_chart_convert()
{
	global $vars, $script, $_chart_msg;

	$argv = func_get_args();
	$argc = func_num_args();

	// $data = $argv[ --$argc ];
	$data = chart::line2array($argv[ --$argc ]);
	if (count($data) > 4) {
		$title = trim(substr($data[0],1));
		array_shift($data);
		// title - 自動取得
		// page  - インラインなため外部ページはなし
		$field = array('config','pos');
	} else {
		$argc++;
		$field = array('title','page','config');
	}

	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($title)) {
		return $_chart_msg['ParameterError'];
	}

	if (empty($page)) {
		$page = $vars['page'];
	}

	if (empty($config)) {
		$config = CHART_DEFAULT_CONFIG_NAME;
	}

	// 表は表示しない
	if (empty($pos)) {
                $pos = 0;
	}

	// GD がサポートされているかどうかのみチェック
	if (! function_exists('gd_info')) {
		return $_chart_msg['GDNotSupport'];
        }

	// 図
	$img = '<img src=\''.$script.'?plugin=chart'.
		'&amp;title='.urlencode($title).'&amp;page='.urlencode($page).
		'&amp;config='.urlencode($config).'\' alt=\'\' />';

	switch ($pos) {
	case 1:
		// 図の上に表を表示する
		return convert_html($data).$img;
	case 2:
		// 図の下に表を表示する
		return $img.convert_html($data);
	}

	// 図のみ表示する
	return $img;
}

function plugin_chart_action()
{
	global $vars, $_chart_msg;

	$params = read_config($vars['config']);
	if (! $params) {
		draw_error( $_chart_msg['CannotReadConfig'] );
		exit();
	}

	$table = new ChartTable($vars['page']);

	if (! $table->read()) {
		draw_error( $_chart_msg['CannotReadTable'] );
		exit();
	}

	$array = $table->get($vars['title']);

	$graph = init_graph($params);
	$graph->SetChartTitle($vars['title']);

	$row = array_shift($array);
	$graph->SetLegend(array_splice($row, 1));

	$row = array_shift($array);
	$graph->SetBarColor(array_splice($row, 1));

	$graph->SetDataValues($array);

	$graph->DrawGraph();

	exit();
}

function read_config($config)
{

	$config = new Config(CHART_CONFIG_PREFIX . $config);

	if (!$config->read()) {
        	return false;
	}
    
	foreach ($config->get(CHART_CONFIG_TITLE) as $row) {
		list($key, $value) = $row;
		$params[$key] = $value;
	}

	return $params;
}

function init_graph($params)
{
	global $_chart_msg;

  // include_once(CHART_YKCEE_DIR.'ykcee.php');
  require_once(CHART_YKCEE_DIR.'ykcee.php');

	$graph = new ykcee;

	$graph->SetImageSize($params['ImageWidthSize'], $params['ImageHeightSize']);
	$graph->SetTitleFont(CHART_YKCEE_DIR.$params['TitleFont']);
	$graph->SetFont(CHART_YKCEE_DIR.$params['Font']);
	$graph->SetFileFormat($params['FileFormat']);
	$graph->SetBackgroundColor($params['BackgroundColor']);
	$graph->SetChartBackgroundColor($params['ChartBackgroundColor']);
	$graph->SetMaxStringSize($params['MaxStringSize']);
	$graph->SetChartBorderColor($params['ChartBorderColor']);
	$graph->SetChartType($params['ChartType']);
	$graph->SetChartTitleSize($params['ChartTitleSize']);
	$graph->SetChartTitleColor($params['ChartTitleColor']);
	$graph->SetFontColor($params['FontColor']);
	$graph->SetBarBorderColor(array('black'));
	$graph->SetLegendPosition($params['LegendPosition']);
	$graph->SetTitleAxisX($params['TitleAxisX']);
	$graph->SetTitleAxisY($params['TitleAxisY']);
	$graph->SetAxisFontSize($params['AxisFontSize']);
	$graph->SetAxisColor($params['AxisColor']);
	$graph->SetAxisTitleSize($params['AxisTitleSize']);
	$graph->SetTickLength($params['TickLength']);
	$graph->SetTickInterval($params['TickInterval']);
	$graph->SetGridX($params['GridX']);
	$graph->SetGridY($params['GridY']);
	$graph->SetGridColor($params['GridColor']);
	$graph->SetLineThickness($params['LineThickness']);
	$graph->SetPointSize($params['PointSize']); //es werden dringend gerade Zahlen empfohlen
	$graph->SetPointShape($params['PointShape']);
	$graph->SetShading($params['Shading']);
	$graph->SetNoData( $_chart_msg['NoData'] );

	return $graph;
}

function draw_error($error_message)
{
	header ('Content-type: image/png');

	$im = imagecreate(300,200);
	$white = imagecolorallocate($im, 255,255,255);
	$black = imagecolorallocate($im, 0,0,0);
  
	// Replace path by your own font path
	imagettftext($im, 12, 0, 20, 180, $black, CHART_ERROR_FONT,$error_message);
	imagepng($im);
	imagedestroy($im);

	return true;
}

class ChartTable extends Config
{
    function ChartTable($name) {
        $this->name = $name;
        $this->page = $name;
    }
}

class chart
{
	// インラインパラメータのデータを１行毎に分割する
	function line2array($x)
	{
		$x = preg_replace(
			array("[\\r\\n]","[\\r]"),
			array("\n","\n"),
			$x
		); // 行末の統一
		return explode("\n", trim($x));
	}

	function tbl2dat($data)
	{
		$x = explode('|',$data);
		if (substr($data,0,1) == '|') array_shift($x);
		if (substr($data,-1)  == '|') array_pop($x);
		return $x;
	}
}

?>
