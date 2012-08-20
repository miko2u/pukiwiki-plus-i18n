<?php
/**
 * コードハイライト機能をPukiWikiに追加する
 * @author sky
 * Time-stamp: <05/07/30 20:00:55 sasaki>
 * 
 * GPL
 *
 * Ver. 0.5.0.1
 */

define('PLUGIN_CODE_LANGUAGE', 'pre');  // 標準言語 全て小文字で指定
// 標準設定
define('PLUGIN_CODE_NUMBER',    true);  // 行番号
define('PLUGIN_CODE_OUTLINE',   true);  // アウトライン;
define('PLUGIN_CODE_COMMENT',   false); // コメント表示/非表示 // 0.5.0 では非推奨
define('PLUGIN_CODE_MENU',      true);  // メニューの表示/非表示;
define('PLUGIN_CODE_FILE_ICON', true);  // 添付ファイルにダウンロードアイコンを付ける
define('PLUGIN_CODE_LINK',      true);  // オートリンク
define('PLUGIN_CODE_CACHE',    false);  // キャッシュを使う


// URLで指定したファイルを読み込むか否か
define('PLUGIN_CODE_READ_URL',  false);

// テーブルを使うか否か(falseはCSSのdivによる分割)
define('PLUGIN_CODE_TABLE',     true);

// TAB幅
define('PLUGIN_CODE_WIDTHOFTAB', '    ');
// 画像ファイルの設定
define('PLUGIN_CODE_IMAGE_FILE', IMAGE_URI.'code_dot.png');

define('PLUGIN_CODE_OUTLINE_OPEN_FILE',  IMAGE_URI.'code_outline_open.png');
define('PLUGIN_CODE_OUTLINE_CLOSE_FILE', IMAGE_URI.'code_outline_close.png');

if (! defined('FILE_ICON')) {
	define('FILE_ICON',
	'<img src="' . IMAGE_URI . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');
}


define('PLUGIN_CODE_USAGE', 
	   '<p class="error">Plugin code: Usage:<br />#code[(Lang)]{{<br />src<br />}}</p>');


function plugin_code_init()
{
	global $javascript;
	$javascript = true;
}

function plugin_code_action()
{
	global $vars;
	global $_source_messages;
	
	// if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');
	if (auth::check_role('safemode')) die_message('PKWK_SAFE_MODE prohibits this');

	$vars['refer'] = $vars['page'];

	if (! is_page($vars['page']) || ! check_readable($vars['page'],false,false)) {
		return array( 'msg' => $_source_messages['msg_notfound'],
					  'body' => $_source_messages['err_notfound'] );
	}
	return array( 'msg' => $_source_messages['msg_title'],
				  'body' => plugin_code_convert('pukiwiki',
												join('',get_source($vars['page']))."\n"));
}

function plugin_code_convert()
{
	global $head_tags, $foot_tags;
	static $plugin_code_jscript_flag = true;
/*
	if (file_exists(PLUGIN_DIR.'code/codehighlight.php'))
		require_once(PLUGIN_DIR.'code/codehighlight.php');
	else
		die_message('file '.PLUGIN_DIR.'code/codehighlight.php not exist or not readable.');
*/
	
	$lang = null;
	$option = array(
					'number'      => false,  // 行番号を表示する
					'nonumber'    => false,  // 行番号を表示しない
					'outline'     => false,  // アウトライン モード
					'nooutline'   => false,  // アウトライン 無効
					'comment'     => false,  // コメント開閉する
					'nocomment'   => false,  // コメント開閉しない
					'menu'        => false,  // メニューを表示する
					'nomenu'      => false,  // メニューを表示しない
					'icon'        => false,  // アイコンを表示する
					'noicon'      => false,  // アイコンを表示しない
					'link'        => false,  // オートリンク 有効
					'nolink'      => false,  // オートリンク 無効
					);
	
	$num_of_arg = func_num_args();
	$args = func_get_args();
	if ($num_of_arg < 1) {
		return PLUGIN_CODE_USAGE;
	}

	$arg = $args[$num_of_arg-1];
	if (strlen($arg) == 0) {
		return PLUGIN_CODE_USAGE;
	}

	if ($num_of_arg != 1 && ! _plugin_code_check_argment($args[0], $option)) {
		$is_setlang = true;
		$lang = htmlspecialchars(strtolower($args[0])); // 言語名かオプションの判定
	} else {
		$lang = PLUGIN_CODE_LANGUAGE; // default
	}

	$begin = 0;
	$end = null;
	// オプションを調べる
	for ($i = 1;$i < $num_of_arg-1; ++$i) {
	if (! _plugin_code_check_argment($args[$i], $option))
		_plugin_code_get_region($args[$i], $begin, $end);
	}
	$multiline = _plugin_code_multiline_argment($arg, $data, $option, $begin, $end);
	
	if (PLUGIN_CODE_CACHE && ! $multiline) { 
		$html = _plugin_code_read_cache($arg);
		if ($html != '' or $html != null)
			return $html;
	}		
	
	if (isset($data['_error']) && $data['_error'] != '') {
		return $data['_error'];
	}

	$lines = $data['data'];
	$title = (isset($data['title'])) ? $data['title'] : '';
	
//	$highlight = new CodeHighlight;
//	$lines = $highlight->highlight($lang, $lines, $option);
//	$lines = '<div class="'.$lang.'">'.$lines.'</div>';
	$lines = '<pre class="prettyprint linenums">'."\n".htmlspecialchars($lines).'</pre>';

	if ($plugin_code_jscript_flag) { // && ($option['outline'] || $option['comment'])) {
		$plugin_code_jscript_flag = false;
		$head_tags[] = '<link href="assets/js/google-code-prettify/prettify.css" rel="stylesheet">';
		$foot_tags[] = '<script type="text/javascript" src="assets/js/google-code-prettify/prettify.js"></script>';
//		$title .= '<script type="text/javascript" src="'.SKIN_URI.'code.js"></script>'."\n";
	}

	$html = $title.$lines;
	if (PLUGIN_CODE_CACHE && ! $multiline) {
		_plugin_code_write_cache($arg, $html);
	}

	return $html;
}

/**
 * キャッシュに書き込む
 * 引数は添付ファイル名, HTML変換後のファイル
 */
function _plugin_code_write_cache($fname, $html)
{
	global $vars;
	// 添付ファイルのあるページ: defaultは現在のページ名
	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// ファイル名にページ名(ページ参照パス)が合成されているか
	//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
	if (preg_match('#^(.+)/([^/]+)$#', $fname, $matches)) {
		if ($matches[1] == '.' || $matches[1] == '..')
			$matches[1] .= '/'; // Restore relative paths
			$fname = $matches[2];
			$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
			$file = encode($page) . '_' . encode($fname);
	} else {
		// Simple single argument
		$file =  encode($page) . '_' . encode($fname);
	}
	$fp = fopen(CACHE_DIR.'code/'.$file.'.html', 'w') or
		die_message('Cannot write cache file ' .
					CACHE_DIR.'code/'. $file .'.html'.
					'<br />Maybe permission is not writable or filename is too long');
	
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	fputs($fp, $html);
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * キャッシュを読み出す
 * 引数は添付ファイル名
 * 変換されたファイルデータを返す
 */
function _plugin_code_read_cache($fname)
{
	global $vars;
	// 添付ファイルのあるページ: defaultは現在のページ名
	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// ファイル名にページ名(ページ参照パス)が合成されているか
	//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
	if (preg_match('#^(.+)/([^/]+)$#', $fname, $matches)) {
		if ($matches[1] == '.' || $matches[1] == '..')
			$matches[1] .= '/'; // Restore relative paths
		$fname = $matches[2];
		$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
		$file = encode($page) . '_' . encode($fname);
	} else {
		// Simple single argument
		$file =  encode($page) . '_' . encode($fname);
	}
	
	/* Read file data */
	$fdata = '';
	$filelines = file(CACHE_DIR.'code/'.$file.'.html');
	
	foreach ($filelines as $line)
		$fdata .= $line;
	
	return $fdata;
}

/**
 * オプション解析
 * 引数に対応するキーをOnにする
 * キーをセットしたら"true"を返す
 */
function _plugin_code_check_argment(& $arg, & $option) {
    $arg = strtolower($arg);
    if (isset($option[$arg])) {
        $option[$arg] = true;
        return true;
    }
    return false;
}

/**
 * 範囲指定を解析
 * 呼び出し側の変数を設定する
 * 範囲を設定したら"true"を返す
 */
function _plugin_code_get_region(& $option, & $begin, & $end)
{
    if (false !== strpos($option, '-')) {
        $array = explode('-', $option);
    } else if (false !== strpos($option, '...')) {
        $array = explode('...', $option);
    } else {
        return false;
    }
    if (is_numeric ($array[0]))
        $begin = $array[0];
    else
        $begin = 1;
    if (is_numeric ($array[1]))
        $end = $array[1];
    else
        $end = null;

    return true;
}

 /**
 * 最終引数を解析する
 * 引数はプラグインへの最後の引数の内容
 * 複数行引数の場合に真を返す
 */
function _plugin_code_multiline_argment(& $arg, & $data, & $option, $begin = 0, $end = null)
{
    // 改行コード変換
    $arg = str_replace("\r\n", "\n", $arg);
    $arg = strtr($arg,"\r", "\n");

    // 最後の文字が改行でない場合は外部ファイル
    if ($arg[strlen($arg)-1] != "\n") {
        $params = _plugin_code_read_file_data($arg, $begin, $end);
        if (isset($params['_error']) && $params['_error'] != '') {
            $data['_error'] = '<p class="error">'.$params['_error'].';</p>';
            return false;
        }
        $data['data'] = $params['data'];
        if ($data['data'] == "\n" || $data['data'] == '' || $data['data'] == null) {
            $data['_error'] ='<p class="error">file '.htmlspecialchars($params['title']).' is empty.</p>';
            return false;
        }
        if (PLUGIN_CODE_FILE_ICON && !$option['noicon'] || $option['icon']) $icon = FILE_ICON;
        else                                                       $icon = '';

        $data['title'] .= '<h5 class="'.PLUGIN_CODE_HEADER.'title">'.'<a href="'.$params['url'].'" title="'.$params['info'].'">'
            .$icon.$params['title'].'</a></h5>'."\n";
    }
    else {
        $data['data'] = $arg;
        return true;
    }
    return false;
}

/**
 * 引数に与えられたファイルの内容を文字列に変換して返す
 * 文字コードは PukiWikiと同一, 改行は \n である
 */
function _plugin_code_read_file_data(& $name, $begin = 0, $end = null) {
    global $vars;
    // 添付ファイルのあるページ: defaultは現在のページ名
    $page = isset($vars['page']) ? $vars['page'] : '';

    // 添付ファイルまでのパスおよび(実際の)ファイル名
    $file = '';
    $fname = $name;

    $is_url = is_url($fname);

    /* Chech file location */
    if ($is_url) { // URL
        if (! PLUGIN_CODE_READ_URL) {
            $params['_error'] = 'Cannot assign URL';
            return $params;
        }
        $url = htmlspecialchars($fname);
        $params['title'] = htmlspecialchars(preg_match('/([^\/]+)$/', $fname, $matches) ? $matches[1] : $url);
    } else {  // 添付ファイル
        if (! is_dir(UPLOAD_DIR)) {
            $params['_error'] = 'No UPLOAD_DIR';
            return $params;
        }

        $matches = array();
        // ファイル名にページ名(ページ参照パス)が合成されているか
        //   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
        if (preg_match('#^(.+)/([^/]+)$#', $fname, $matches)) {
            if ($matches[1] == '.' || $matches[1] == '..')
                $matches[1] .= '/'; // Restore relative paths
            $fname = $matches[2];
            $page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
            $file = UPLOAD_DIR . encode($page) . '_' . encode($fname);
            $is_file = is_file($file);
        } else {
            // Simple single argument
            $file = UPLOAD_DIR . encode($page) . '_' . encode($fname);
            $is_file = is_file($file);
        }

        if (! $is_file) {
            $params['_error'] = htmlspecialchars('File not found: "' .$fname . '" at page "' . $page . '"');
            return $params;
        }
        $params['title'] = htmlspecialchars($fname);
        $fname = $file;

        $url = $script . '?plugin=attach' . '&amp;refer=' . rawurlencode($page) .
            '&amp;openfile=' . rawurlencode($name); // Show its filename at the last
    }

	$params['url'] = $url;
    $params['info'] = get_date('Y/m/d H:i:s', filemtime($file) - LOCALZONE)
        . ' ' . sprintf('%01.1f', round(filesize($file)/1024, 1)) . 'KB';

    /* Read file data */
    $fdata = '';
    $filelines = file($fname);
    if ($end === null)
        $end = count($filelines);

    for ($i=$begin; $i<=$end; ++$i)
        $fdata .= str_replace("\r\n", "\n", $filelines[$i]);

    $fdata = strtr($fdata, "\r", "\n");
    $fdata = mb_convert_encoding($fdata, SOURCE_ENCODING, "auto");

    // ファイルの最後を改行にする
    if($fdata[strlen($fdata)-1] != "\n")
        $fdata .= "\n";

    $params['data'] = $fdata;

    return $params;
}
