<?php
/**
 * 整形済出力
 * @author sky
 * Time-stamp: <05/07/30 20:01:01 sasaki>
 * 
 * GPL
 *
 * Code.inc.php Ver. 0.5
 */

// 標準設定
define('PLUGIN_PRE_NUMBER',   false);  // 行番号
define('PLUGIN_PRE_VERVATIM',  true);  // インライン展開をしない
define('PLUGIN_PRE_FILE_ICON', true);  // 添付ファイルにダウンロードアイコンを付ける

// define('PLUGIN_PRE_READ_URL',  false); // URLで指定したファイルを読み込むか否か

define('PLUGIN_PRE_HEADER', 'pre_');
if (! defined('FILE_ICON')) {
	define('FILE_ICON',
	'<img src="' . IMAGE_URI . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');
}
define('PLUGIN_PRE_COLOR_REGEX', '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i');

function plugin_pre_convert()
{
	if (file_exists(PLUGIN_DIR.'code/codehighlight.php'))
		require_once(PLUGIN_DIR.'code/codehighlight.php');
	else
		die_message('file '.PLUGIN_DIR.'code/codehighlight.php not exist or not readable.');

	static $id_number = 0; // プラグインが呼ばれた回数(IDに利用)
	$id_number++;

    $option = array(
                  'number'      => false,  // 行番号を表示する
                  'nonumber'    => false,  // 行番号を表示しない
				  'vervatim'    => false,  // インライン展開しない
				  'novervatim'  => false,  // インライン展開する
				  'icon'        => false,  // アイコンを表示する
				  'noicon'      => false,  // アイコンを表示しない
                  'link'        => false,  // オートリンク 有効
                  'nolink'      => false,  // オートリンク 無効
              );
    $num_of_arg = func_num_args();
	$args = func_get_args();

	$text = '';
	$number = '';

	$style = '';
	$stylecnt = 0;

	$begin = 1;
	$end = null;

	$a = array();
	
    // オプションを調べる
    for ($i = 0;$i < $num_of_arg-1; ++$i) {
        if (! _plugin_code_check_argment($args[$i], $option)) {
			if (! _plugin_code_get_region($args[$i], $begin, $end)) {
				// style
				if ($stylecnt == 0) {
					$color   = $args[$i];
					++$stylecnt;
				} else {
					$bgcolor = $args[$i];
				}
			}
		}
    }
	if ($stylecnt) {
		// Invalid color
		foreach(array($color, $bgcolor) as $col){
			if ($col != '' && ! preg_match(PLUGIN_PRE_COLOR_REGEX, $col))
				return '<p class="error">#pre():Invalid color: '.htmlspecialchars($col).';</p>';
		}
		if ($color != '' ) {
			$style   = ' style="color:'.$color;
			if ($bgcolor != '') 
				$style .= ';background-color:'.$bgcolor.'"';
			else
				$style .= '"';
		} else {
			if ($bgcolor != '') 
				$style .= ' style="background-color:'.$bgcolor.'"';
			else 
				$style = '';
		}
	}

	_plugin_code_multiline_argment($args[$num_of_arg-1], $data, $option, $begin, $end);
	if (isset($data['_error']) && $data['_error'] != '') {
		return $data['_error'];
	}
	$text = $data['data'];
	$title = $data['title'];

	if ($end === null)
		$end = substr_count($text, "\n") + $begin -1;

	if (PLUGIN_PRE_VERVATIM  && ! $option['novervatim']  || $option['vervatim']) {
		$text = htmlspecialchars($text);
	} else {
		$text = make_link($text);
	}
	$html = '<pre class="'.PLUGIN_PRE_HEADER.'body" '.$style.'>'.$text.'</pre>';

	if (PLUGIN_PRE_NUMBER  && ! $option['nonumber']  || $option['number']) {
		$number = '<pre class="'.PLUGIN_PRE_HEADER.'number">'
			._plugin_code_makeNumber($end, $begin).'</pre>';
		$html = '<div id="'.PLUGIN_PRE_HEADER.$id_number.'" class="'.PLUGIN_PRE_HEADER.'table">'
			._plugin_code_column($html, $number, null). '</div>';
	}
	
	return $title.$html;
}


?>
