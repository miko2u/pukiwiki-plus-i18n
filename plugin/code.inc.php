<?php
/**
 * コードハイライト機能をPukiWikiに追加する
 * Time-stamp: <04/12/15 12:47:15 sasaki>
 *
 * GPL
 *
 * Ver. 0.4.3_1
 */

define("PLUGIN_CODE_LANGUAGE", 'pre');  // 標準言語
// 標準設定
define("PLUGIN_CODE_NUMBER",    TRUE);  // 行番号
define("PLUGIN_CODE_OUTLINE",   TRUE);  // アウトライン;
define("PLUGIN_CODE_COMMENT",   FALSE); // コメント表示/非表示 // 0.4.0 では非推奨
define("PLUGIN_CODE_MENU",      TRUE);  // メニューの表示/非表示;
define("PLUGIN_CODE_FILE_ICON", TRUE);  // 添付ファイルにダウンロードアイコンを付ける
// URLで指定したファイルを読み込むか否か
define("PLUGIN_CODE_READ_URL",  TRUE);  // 標準では添付ファイル以外読み込まない

// テーブルを使うか否か(FALSEはCSSのdivによる分割)
define("PLUGIN_CODE_TABLE",     TRUE);

// TAB幅
define("WIDTHOFTAB", "    ");

define("PLUGIN_CODE_USAGE", 
       //'<p class="error">Plugin code: Usage:<br />#code(Lang){{<br />src<br />}}</p>');
	   '<p class="error">Plugin code: Usage:<br />#code(Lang)<< EOF<br />src<br />EOF</p>');

// for PukiWiki 1.4.5 or later
global $javascript; $javascript = TRUE;

define("CODE_HEADER", "code_");
// 画像ファイルの設定
define("CODE_IMAGE_FILE", IMAGE_DIR.'code_dot.png');
define("CODE_OUTLINE_OPEN_FILE",  IMAGE_DIR.'plus/outline_open.png');
define("CODE_OUTLINE_CLOSE_FILE", IMAGE_DIR.'plus/outline_close.png');
if (! defined('FILE_ICON')) {
	define('FILE_ICON',
	'<img src="' . IMAGE_DIR . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');
}


function plugin_code_action() {
	global $vars;
	global $_source_messages;

	$vars['refer'] = $vars['page'];

	if (!is_page($vars['page']) || !check_readable($vars['page'],false,false)) {
		return array( 'msg'=>$_source_messages['msg_notfound'],
					  'body'=>$_source_messages['err_notfound'] );
	}
	return array( 'msg'=>$_source_messages['msg_title'],
				  'body' => plugin_code_convert('pukiwiki',
												join('',get_source($vars['page']))."\n"));
}

function plugin_code_convert() {
	static $plugin_code_jscript_flag = TRUE;

    $title = "";
	$lang = null;
    $option = array(
                  "number"      => FALSE,  // 行番号を表示する
                  "nonumber"    => FALSE,  // 行番号を表示しない
                  "outline"     => FALSE,  // アウトライン モード
                  "nooutline"   => FALSE,  // アウトライン 無効
				  "comment"     => FALSE,  // コメント
				  "nocomment"   => FALSE,  // define("PLUGIN_CODE_", TRUE);
				  "menu"        => FALSE,  // メニューを表示する
				  "nomenu"      => FALSE,  // メニューを表示しない
				  "icon"        => FALSE,  // アイコンを表示する
				  "noicon"      => FALSE,  // アイコンを表示しない

                  //"link"        => FALSE,  // オートリンク 有効
                  //"nolink"      => FALSE,  // オートリンク 無効
              );
    $num_of_arg = func_num_args();
    $args = func_get_args();
    if ($num_of_arg < 1) {
        return PLUGIN_CODE_USAGE;
    }

	$data = $args[$num_of_arg-1];
    if (strlen($data) == 0) {
        return PLUGIN_CODE_USAGE;
    }

	if ($num_of_arg != 1 && !code_check_argment($args[0], $option)) {
		$is_setlang = TRUE;
		$lang = $args[0]; // 言語名かオプションの判定
	}
	else
		$lang = PLUGIN_CODE_LANGUAGE; // default
		
    // オプションを調べる
    for ($i = 1;$i < $num_of_arg-1; $i++) {
        code_check_argment($args[$i], $option);
    }
	

    // 改行コード変換
	$data = str_replace("\r\n", "\n", $data);
    $data = strtr($data,"\r", "\n");

    // 最後の文字が改行でない場合は外部ファイル
    if ($data[strlen($data)-1] != "\n") {
        $params = code_read_file_data($data);
        if (isset($params['_error']) && $params['_error'] != '') {
            return '<p class="error">#code(): ' . $params['_error'] . ';</p>';
        }
        $data = $params['data'];
        if ($data == "\n" || $data == "" || $data == null) {
            return '<p class="error">file '.htmlspecialchars($params['title']). ' is empty.</p>';
        }
		$url = $params['url'];
		$info = $params['info'];
		if (PLUGIN_CODE_FILE_ICON && !$option['noicon'] || $option['icon']) $icon = FILE_ICON;
		else                                                       $icon = '';

        $title .= '<h5 class="'.CODE_HEADER.'title">'."<a href=\"$url\" title=\"$info\">$icon"
			.$params['title']."</a></h5>\n";
    }

    $highlight = new CodeHighlight;
    $lines = $highlight->highlight($lang, $data, $option);
	if ($plugin_code_jscript_flag && ($option["outline"] || $option["comment"])) {
		$plugin_code_jscript_flag = FALSE;
		$title .= "<script type=\"text/javascript\" src=\"".SKIN_DIR."code.js\"></script>\n";
	}
    return $title.$lines;
}

class CodeHighlight {

	function CodeHighlight() {
		// common
        define("CODE_CANCEL",          0); // 指定を無効化する
        define("IDENTIFIRE",           2); 
        define("SPECIAL_IDENTIFIRE",   3); 
        define("ESCAPE_IDENTIFIRE",    4); 
        define("STRING_LITERAL",       5); 
        define("NONESCAPE_LITERAL",    6); 
        define("PAIR_LITERAL",         7); 
        define("ESCAPE",              10);
        define("COMMENT",             11);
        define("FORMULA",             12); 
		// outline
        define("BLOCK_START",         20);
        define("BLOCK_END",           21);
		// 行指向用
        define("COMMENT_CHAR",        50); // 1文字でコメントと決定できるもの
        define("COMMENT_WORD",        51); // コメントが文字列で始まるもの
        define("HEAD_COMMENT",        52); // コメントが行頭だけのもの (1文字)  // fortran
        define("HEADW_COMMENT",       53); // コメントが行頭だけのもの   // pukiwiki
        define("CHAR_COMMENT",        54); // コメントが行頭だけかつ英字であるのもの (1文字) // fortran
        define("IDENTIFIRE_CHAR",     60); // 1文字で命令が決定するもの
        define("IDENTIFIRE_WORD",     61); // 命令が文字列で決定するもの
        define("MULTILINE",           62); // 複数文字列への命令

        define("CARRIAGERETURN",      70); // 空行
		define("POST_IDENTIFIRE",     71); // 文末の語よって決まるルール		
	}

    function highlight($lang, $src, &$option) {
		static $id_number = 0; // プラグインが呼ばれた回数(IDに利用)
        $id_number++;

		if (strlen($lang) > 16)
            return null;
		
		$option["number"]  = (PLUGIN_CODE_NUMBER  && !$option["nonumber"]  || $option["number"]);
		$option["outline"] = (PLUGIN_CODE_OUTLINE && !$option["nooutline"] || $option["outline"]);
		$option["comment"] = (PLUGIN_CODE_COMMENT && !$option["nocomment"] || $option["comment"]);

        // mozillaの空白行対策
        if($option["number"] || $option["outline"]) {
            // ライン表示用補正
            $src = preg_replace("/^$/m"," ",$src);
        }
		
        $lang = strtolower($lang);

        $keywordfile = sprintf("code/keyword.%s.php", $lang);
        $linekeywordfile = sprintf("code/line.%s.php", $lang); // 行指向解析用設定ファイル
		if (file_exists(PLUGIN_DIR.$keywordfile)) {
			// 言語定義ファイルが有る言語
			$data = $this->srcToHTML($src, $keywordfile, $id_number, $option);
			$src = "<pre class=\"code\"><code class=\"" .$lang. "\">".$data['src']."</code></pre>";
		} else if (file_exists(PLUGIN_DIR.$linekeywordfile)) {
			// 行指向解析用設定ファイルが有る言語
			$data = $this->lineToHTML($src, $linekeywordfile, $id_number, $option);
			$src = "<pre class=\"code\"><code class=\"" .$lang. "\">".$data['src']."</code></pre>";
		} else {
			// PHP と 未定義言語
			$option["outline"] = false;
			$option["comment"] = false;

			// 最後の余分な改行を削除
			if ($src[strlen($src)-2] == ' ')
				$src = substr($src, 0, -2);
			else
				$src= substr($src, 0, -1);

			if ($option["number"]) {
				// 行数を得る
				$num_of_line = substr_count($src, "\n");
 				if($src[strlen($src)-1]=="\n")
 					$src=substr($src,0,-1);
				$data = array('number' => '');	
				$data['number'] = $this->makeNumber($num_of_line-1);
			}
			if ('php' == $lang) 
				// PHPは標準機能を使う
				$src =  "<pre class=\"code\">".$this->highlightPHP($src). "</pre>";
			else
				// 未定義言語
				$src =  "<pre class=\"code\"><code class=\"unknown\">" .htmlspecialchars($src). "</code></pre>";
		}

		$option["menu"]  = (PLUGIN_CODE_MENU  && !$option["nomenu"]  || $option["menu"]);
		$option["menu"]  = ($option["menu"] && ($option["outline"] || $option["comment"]));

		$menu = '';
		if ($option["menu"]) {
			// アイコンの設定
			$menu .= '<div class="'.CODE_HEADER.'menu">';
			if ($option["outline"]) {
				// アウトラインのメニュー
				$menu .= "<img src=\"".CODE_OUTLINE_OPEN_FILE."\" style=\"cursor: hand\" alt=\"すべてを展開\" title=\"すべてを展開\" "
					."onclick=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'','".IMAGE_DIR."')\" "
					."onkeypress=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'','".IMAGE_DIR."')\" />";
				$menu .= "<img src=\"".CODE_OUTLINE_CLOSE_FILE."\" style=\"cursor: hand\" alt=\"すべてを収束\" title=\"すべてを収束\" "
					."onclick=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'none','".IMAGE_DIR."')\" "
					."onkeypress=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'none','".IMAGE_DIR."')\" />\n";
			}
			if ($option["comment"]){
				// コメントの開閉ボタン
				$menu .= "<input type=\"button\" value=\"comment open\" "
					."onclick=\"javascript:code_comment('".CODE_HEADER.$id_number."',".$data['commentnum'].",'')\" "
					."onkeypress=\"javascript:code_comment('".CODE_HEADER.$id_number."',".$data['commentnum'].",'')\" />";
				$menu .= "<input type=\"button\" value=\"comment close\" "
					." onclick=\"javascript:code_comment('".CODE_HEADER.$id_number."',".$data['commentnum'].",'none')\" "
					." onkeypress=\"javascript:code_comment('".CODE_HEADER.$id_number."',".$data['commentnum'].",'none')\" />";
			}
			$menu .= '</div>';
		}

		if ($option["outline"] || $option["number"] || $option["comment"]) {
			if (PLUGIN_CODE_TABLE) {
				// テーブルによる段組
				$html .= "<div id=\"".CODE_HEADER.$id_number."\" class=\"".CODE_HEADER."table\">";
				$html .= $menu;
				$html .= "<table id=\"".CODE_HEADER.$id_number."\" class=\"".CODE_HEADER.
					"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr>\n";
				if ($option["number"])
					$html .= "<td><pre class=\"".CODE_HEADER."number\">".$data["number"]."</pre></td>\n";
				if ($option["outline"])
					$html .= "<td><pre class=\"".CODE_HEADER."outline\">".$data["outline"]."</pre></td>\n";
				$html .= "<td class=\"".CODE_HEADER."src\">".$src."</td>\n".
					"</tr></table></div>\n";
			} else {
				// CSSのdivによる段組
				$html .= '<div  id="'.CODE_HEADER.$id_number.'" class="'.CODE_HEADER.'table">';
				$html .= $menu;
				if ($option["number"])
					$html .= '<div class="'.CODE_HEADER.'number"><pre class="'.CODE_HEADER.'number"><code>'
						.$data['number']. '</code></pre></div>';
				if ($option["outline"])
					$html .= '<div class="'.CODE_HEADER.'outline"><pre class="'.CODE_HEADER.'outline"><code>'
						.$data['outline']. '</code></pre></div>';
				$html .= '<div class="'.CODE_HEADER.'src">'.$src
					.'</div><div style="clear:both;"><br style="display:none;" /></div></div>';
			}
			return $html;
		}
		return $src;
	}

	/**
	 * この関数は1行切り出す
	 * 定型フォーマットを持つ言語用
	 */
	function getline(&$string){
		$line = '';
		if(!$string[0]) return false;
		$pos = strpos($string, "\n"); // 改行まで切り出す
		if ($pos === false) { // 見つからないときは終わりまで
			$line = $string;
			$string = '';
		} else {
			$line = substr($string, 0, $pos+1);
			$string = substr($string, $pos+1);
		}
		return $line;
	}


	/**
	 * この関数は行頭の文字を判定して解析・変換する
	 * 定型フォーマットを持つ言語用
	 */
	function lineToHTML($string, $keywordfile, $id_number, &$option) {

        // テーブルジャンプ用ハッシュ
        $switchHash = Array();
        $capital = FALSE; // 大文字小文字を区別しない

		$option["outline"] = false; // outlineを使わない
		$mknumber  = $option["number"];

		// 改行
		$switchHash["\n"] = CARRIAGERETURN;
		// エスケープ文字
        $switchHash['\\'] = ESCAPE;
        // 識別子開始文字
        for ($i = ord("a"); $i <= ord("z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        for ($i = ord("A"); $i <= ord("Z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        $switchHash["_"] = IDENTIFIRE;

        // 文字列開始文字
        $switchHash["\""] = STRING_LITERAL;
		$linemode = false; // 行内を解析するか否か

        // 言語定義ファイル読み込み
        include $keywordfile;


        $str_len = strlen($string);
        // 文字->html変換用ハッシュ
        $htmlHash = Array("\"" => "&quot;", "'" => "&#039;", "<" => "&lt;", ">" => "&gt;", 
						  "&" => "&amp;", "\t" => WIDTHOFTAB);
 
        $html = "";   // 出力されるHTMLコード付きソース
        $num_of_line = 0;  // 行数をカウント
		$commentnum = 0;  // コメントのID番号

		$line = $this->getline($string);
		while($line !== false) {
			$num_of_line++;
			while ($line[strlen($line)-2] == "\\") {
				// 行末がエスケープ文字なら次の行も切り出す
				$num_of_line++;
				$line .= $this->getline($string);
			}
			// 行頭文字の判定
            switch ($switchHash[$line[0]]) {

			case CHAR_COMMENT:
			case HEAD_COMMENT:
			case COMMENT_CHAR:
				// 行頭の1文字でコメントと判断できるもの

				// htmlに追加
				$commentnum++;
				$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
				$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);
				$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
					.$line."</span>\n";

				$line = $this->getline($string); // next line
				continue 2;

			case HEADW_COMMENT:
			case COMMENT_WORD:
				// 2文字以上のパターンから始まるコメント
				if (strncmp($line, $commentpattern, strlen($commentpattern)) == 0) {
					// htmlに追加
					$commentnum++;
					$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
					$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);
					$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line."</span>\n";
					
					$line = $this->getline($string); // next line
					continue 2;
				}
				// コメントではない
				break;

			case IDENTIFIRE_CHAR:
				// 行頭の1文字が意味を持つもの
				$index = $code_keyword[$line[0]];
				$line = htmlspecialchars($line, ENT_QUOTES);
				$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
				else
					$html .= $line;

				$line = $this->getline($string); // next line
				continue 2;


			case IDENTIFIRE_WORD:
				if (strlen($line) < 2 && $line[0] == ' ') break; // 空行判定
				// 行頭のパターンを調べる
				foreach ($code_identifire[$line[0]] as $pattern) {
					if (strncmp($line, $pattern, strlen($pattern)) == 0) {
						$index = $code_keyword[$pattern];
						// htmlに追加
						$line = htmlspecialchars($line, ENT_QUOTES);
						$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);
						if ($index != "")
							$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
						else
							$html .= $line;
						
						$line = $this->getline($string); // next line
						continue 3;
					}
				}
				// 行頭の1文字が意味を持つものか判定
				$index = $code_keyword[$line[0]];
				if ($index != "") {
					$line = htmlspecialchars($line, ENT_QUOTES);
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
					$line = $this->getline($string); // next line
					continue 2;
				}
				else
					// IDENTIFIREではない
					break;

			case MULTILINE:
				// 複数行に渡って効果を持つ指定
				$index = $code_keyword[$line[0]];
				$src = $line;
				$line = $this->getline($string);
				while (in_array($line[0], $multilineEOL) === false && $line !== false) {
					// 効果の範囲内を取得する
					$src .= $line;
					$num_of_line++;
					$line = $this->getline($string);
				}
				$src = htmlspecialchars($src, ENT_QUOTES);
				$src = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$src);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$src.'</span>';
				else
					$html .= $src;
				continue 2;

			case POST_IDENTIFIRE:
				// 行中の特定のパターンを検索する
				// makeのターゲット用 識別子(アルファベットから始まっている)
				$str_pos = strpos($line, $post_identifire);
				if ($str_pos !== FALSE) {
					$result  = htmlspecialchars(substr($line, 0, $str_pos), ENT_QUOTES);
					$result2 = htmlspecialchars(substr($line, $str_pos+1), ENT_QUOTES);
					$html .= '<span class="'.CODE_HEADER.'target">'.$result.$post_identifire.'</span>'
						.'<span class="'.CODE_HEADER.'src">'.$result2.'</span>';
					$line = $this->getline($string); // next line
					continue 2;
				}
				else
					// 該当しない
					break;

			default:
				// 行内を解析せずにHTMLに追加する (diff)
				if($linemode) {
					$line = htmlspecialchars($line, ENT_QUOTES);
					$html .= preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);

					$line = $this->getline($string); // next line
					continue 2;
				}
			} //switch
				
			// 行内の解析 1文字ずつ解析する
			$str_len = strlen($line);
			$str_pos = 0;
			if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++];// getc
			while($code !== false) {
				switch ($switchHash[$code]) {
					
				case CHAR_COMMENT: // 行頭以外ではコメントにはならない (fortran)
				case IDENTIFIRE:
					// 識別子(アルファベットから始まっている)
					
					// 出来る限り長く識別子を得る
					$str_pos--;// エラー処理したくないからpreg_matchで必ず見つかるようにする
					$result = substr($line, $str_pos); 
					preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
					
					// htmlに追加
					if($capital)
						$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index != "")
						$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;
					
				case SPECIAL_IDENTIFIRE:
					// 特殊文字から始まる識別子
					// 次の文字が英字か判定
					if (!ctype_alpha($line[$str_pos])) break;
					$result = substr($line, $str_pos);
					preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $code.$matches[0];
					// htmlに追加
					if($capital)
						$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index!="")
						$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case STRING_LITERAL:
				case NONESCAPE_LITERAL:
					// 文字列リテラルを得る //現在エスケープする必要が無い
					$pos = $str_pos;
					$result = substr($line, $str_pos);
					$pos1 = strpos($result, $code); // 文字列終了文字検索
					if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
						$str_pos = $str_len;
					} else {
						$str_pos += $pos1 + 1;
					}
					$result = $code.substr($line, $pos, $str_pos - $pos);
					
					// htmlに追加
					$result = htmlspecialchars($result, ENT_QUOTES);
					$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
					$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case COMMENT_CHAR: // 1文字で決まるコメント
					$line = substr($line, $str_pos-1, $str_len-$str_pos);
					$commentnum++;
					$line = htmlspecialchars($line, ENT_QUOTES);
					$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$line);
					$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line."</span>\n";
					
					$line = $this->getline($string); // next line
					continue 3;

				} //switch
				// その他の文字
				$result = $htmlHash[$code];
				if ($result) 
					$html .= $result;
				else
					$html .= $code;
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc

			}// while
			
			$line = $this->getline($string); // next line
		} // while
		
		// 最後の余分な改行を削除
		if ($html[strlen($html)-2] == ' ')
			$html = substr($html, 0, -2);
		else
			$html = substr($html, 0, -1);
		
		$html = array( 'src' => $html,  'number' => '', 'outline' => '', 'commentnum' => $commentnum,);
		if($mknumber) $html['number'] = $this->makeNumber($num_of_line-2); // 最後に改行を削除したため -2
		return $html;
	}

    /**
      * ソースからHTML生成
      */
    function srcToHTML($string, $keywordfile, $id_number, &$option) {

        // テーブルジャンプ用ハッシュ
        $switchHash = Array();
        $capital = FALSE; // 大文字小文字を区別しない
		$mkoutline = $option["outline"];
		$mknumber  = $option["number"];

		// 改行
        $switchHash["\n"] = CARRIAGERETURN;

        $switchHash['\\'] = ESCAPE;
        // 識別子開始文字
        for ($i = ord("a"); $i <= ord("z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        for ($i = ord("A"); $i <= ord("Z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        $switchHash["_"] = IDENTIFIRE;

        // 文字列開始文字
        $switchHash["\""] = STRING_LITERAL;

        // 言語定義ファイル読み込み
        include $keywordfile;
		
        // 文字->html変換用ハッシュ
        $htmlHash = Array("\"" => "&quot;", "'" => "&#039;", "<" => "&lt;", ">" => "&gt;", 
						  "&" => "&amp;", "\t" => WIDTHOFTAB);

        $html = "";
        $str_len = strlen($string);
        $str_pos = 0;
        $line = 0;  // 行数をカウント
        // for outline
        $outline = Array();// $outline[lineno][nest] $outline[lineno][blockno]がある。
        $nest = 1;// ネスト
        $blockno = 0;// 何番目のブロックか？IDをユニークにするために用いる
        $last_start = false;// 最後にブロック開始だったか、、、。
		$commentno = 0;

        // 最初の検索用に読み込み
        if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++];// getc
        while ($code !== false) {

            switch ($switchHash[$code]) {

			case CARRIAGERETURN: // 改行
				$line++;
				$html .="\n";
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case ESCAPE:
				// escape charactor
				$start = $code;
				// 判定用にもう1文字読み込む
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				if (ctype_alnum($code)) {
					// 文字(変数)なら終端まで見付ける
					$str_pos--; // エラー処理したくないからpreg_matchで必ず見つかるようにする
					$result = substr($string, $str_pos);
					preg_match("/[A-Za-z0-9_]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
				} else {
					// 記号なら1文字だけ切り出す
					$result = $code;
					if ($code == "\n") $line++;
				}
				
				// htmlに追加
				$html .= htmlspecialchars($start.$result, ENT_QUOTES);
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case COMMENT:
				// コメント
				
				// 出来る限り長く識別子を得る
				$str_pos--;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if(preg_match($pattern, $result, $matches)==1) {
						$str_pos += strlen($matches[0]);
						$result = $matches[0];
						
						// ライン数カウント
						$line+=substr_count($result,"\n");
						$commentno++;
						
						// htmlに追加
						$result = str_replace("\t", WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
						$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// 次の検索用に読み込み
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				// コメントではない
				$str_pos++;
				break;
				
			case COMMENT_WORD:
				// 文字列から始まるコメント
				
				// 出来る限り長く識別子を得る
				$str_pos--;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if(preg_match($pattern, $result, $matches)==1) {
						$str_pos += strlen($matches[0]);
						$result = $matches[0];
						
						// ライン数カウント
						$line+=substr_count($result,"\n");
						$commentno++;
						
						// htmlに追加
						$result = str_replace("\t", WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
						$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// 次の検索用に読み込み
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				$str_pos++;
				// コメントでなければ文字列 break を使わない
			case IDENTIFIRE:
				// 識別子(アルファベットから始まっている)
				
				// 出来る限り長く識別子を得る
				$str_pos--;// エラー処理したくないからpreg_matchで必ず見つかるようにする
				$result = substr($string, $str_pos);
				preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
				/* //マークアップ言語モードが出来るまで利用停止
                          if(preg_match("/^(s?https?:\/\/|ftp:\/\/|mailto:)[-_.!~*()a-zA-Z0-9;\/:@?&=+$,%#]+/",$result,$matches2)){
                            $matches=$matches2;
                            $start="<a href=\"".$matches[0]."\">";
                            $end = "</a>";
                          }else{
                            $start="";$end="";
                          }
				*/
				$str_pos += strlen($matches[0]);
				$result = $matches[0];
				
				// htmlに追加
				if($capital)
					$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				//else              $html .= $start.$result.$end;
				else
					$html .= $result;
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case SPECIAL_IDENTIFIRE:
				// 特殊文字から始まる識別子
				// 次の文字が英字か判定
				if (!ctype_alpha($string[$str_pos])) break;
				$result = substr($string, $str_pos);
				preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $code.$matches[0];
				// htmlに追加
				if($capital)
					$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index!="")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				else
					$html .= $result;
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case ESCAPE_IDENTIFIRE:
				// エスケープする必要がある特殊文字を記号として利用し且この文字から始まる識別子 TeX
				if($string[$str_pos] == "\\" && $string[$str_pos+1] == "\n") {
					$html .=  "<span>\\\\\n</span>";
					$str_pos += 2;

					$line++; // ライン数カウント

					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
					continue 2;					
				}
				// 次の文字が英字か判定
				if (!ctype_alpha($string[$str_pos])) break;
				// 出来る限り長く識別子を得る
				$result = substr($string, $str_pos);
				preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $code.$matches[0];
				
				// htmlに追加
				if($capital)
					$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index!="")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				else
					$html .= $result;
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				

			case STRING_LITERAL:
				// 文字列
				
				// 文字列リテラルを得る
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $code); // 文字列終了文字検索
					if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
						$str_pos = $str_len;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == "\\"); // 前の文字がエスケープ文字なら続ける
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// ライン数カウント
				$line+=substr_count($result,"\n");
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case NONESCAPE_LITERAL:
				// エスケープ文字と式展開を無視した文字列
				// 文字列リテラルを得る

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // 文字列終了文字検索
				if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
					$str_pos = $str_len;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PAIR_LITERAL:
				// 対記号で囲まれた文字列リテラルを得る PostScript
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $literal_delimiter); // 文字列終了文字検索
					if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
						$str_pos = $str_len;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == "\\"); // 前の文字がエスケープ文字なら続ける
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// ライン数カウント
				$line+=substr_count($result,"\n");
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case FORMULA:
				// TeXの数式に使用 将来的には汎用性を持たせる 

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // 文字列終了文字検索
				if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
					$str_pos = $str_len;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/","<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'formula">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case BLOCK_START:
				// outline 表示用開始文字 {, (
				
				$blockno++;
				$nest++;
				if(!array_key_exists($line,$outline)) {
					$outline[$line]=Array();
				}
				array_push($outline[$line],Array("nest"=>$nest, "blockno"=>$blockno));
				// アウトラインが閉じた時に表示する画像を埋め込む場所
				$html .= $code.'<span id="'.CODE_HEADER.$id_number._.$blockno.'_img" display="none"></span>'
					.'<span id="'.CODE_HEADER.$id_number._.$blockno.'">';
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				continue 2;
				
			case BLOCK_END:
				// outline 表示終了文字 }, )
				
				$nest--;
				if(!array_key_exists($line,$outline)) {
					$outline[$line]=Array();
					array_push($outline[$line],Array("nest"=>$nest,"blockno"=>0));
				} else {
					$old = array_pop($outline[$line]);
					if($old["blockno"]!=0 && ($nest+1) == $old["nest"]) {
					} else {
						if(!is_null($old))
							array_push($outline[$line],$old);
						array_push($outline[$line],Array("nest"=>$nest,"blockno"=>0));
					}
				}
				$last_start=false;
				$html .= "</span>".$code;
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				continue 2;
				
            }// switch
			
            // その他の文字
            $result = $htmlHash[$code];
            if ($result) 
				$html .= $result;
            else
                $html .= $code;

            // 次の検索用に読み込み
            if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc

        }// while

		// 最後の余分な改行を削除
		if ($html[strlen($html)-2] == ' ')
			$html = substr($html, 0, -2);
		else
			$html = substr($html, 0, -1);

		$html = array( 'src' => $html,  'number' => '', 'outline' => '', 
					   'blocknum' => $blockno, 'commentnum' => $commentno, );

        if($mkoutline) 
			return $this->makeOutline($html,$line-1,$nest,$mknumber,$outline,$blockno,$id_number);
		if($mknumber) $html['number'] = $this->makeNumber($line-1);
		return $html;
	}

	// outline の形成
	function makeOutline(&$html,$line,$nest,$mknumber,$tree,$blockno,$id_number) {
		while($nest>1) {// ネストがちゃんとしてなかった場合の対策
			$html['src'] .= "</span>";
			$nest--;
		}
		$outline="";
		$number="";
		$nest=1;

		$linelen=$line+1;
		$str_len=max(3,strlen("".$linelen));

		for($i=0;$i<$linelen;$i++) {
			$plus="";
			$plus1="";
			$plus2="";
			$minus="";
			if(array_key_exists($i,$tree)) {
				while(true) {
					$array = array_shift($tree[$i]);
					if (is_null($array))
						break;
					if ($nest<=$array["nest"]) {
						$id=$id_number."_".$array["blockno"];
						if($plus=="")
							$plus = '<a class="'.CODE_HEADER.'outline" href="javascript:code_outline(\''
								.CODE_HEADER.$id.'\',\''.IMAGE_DIR.'\')" id="'.CODE_HEADER.$id.'a">-</a>';
						$plus1 .= '<span id="'.CODE_HEADER.$id.'o">';
						$plus2 .= '<span id="'.CODE_HEADER.$id.'n">';
						$nest=$array["nest"];
					} else {
						$nest=$array["nest"];
						$minus .= '</span>';
					}
				}
			}
			if($mknumber) {
				$number.= sprintf("%".$str_len."d",($i+1)).$minus.$plus2."\n";
			}
			if($plus=="" && $minus == "") {
				if($nest==1)
					$outline.=" ";
				else
					$outline.="|";
			} else if($plus!="" && $minus == "") {
				$outline.= $plus.$plus1;
			} else if($plus=="" && $minus != "") {
				$outline.= "!".$minus;
			} else if($plus!="" && $minus != "") {
				$outline.= $plus.$minus.$plus1;
			}
			$outline.="\n";
		}
		while ($nest>1) {// ネストがちゃんとしてなかった場合の対策
			$number .= "</span>";
			$outline .= "</span>";
			$nest--;
		}
		$html['number'] = $number;
		$html['outline'] = $outline;
		return $html;
	}
	
	
	// number の形成
	function makeNumber($num_of_line){
		$number="";
		$linelen=$num_of_line+1;
		$str_len=max(3,strlen("".$linelen));
		for($i=1;$i<$linelen+1;$i++) {
			$number.= sprintf("%".$str_len."d",($i))."\n";
		}
		return $number;
	}

	/**
	 * PHPを標準関数でハイライトする
	 */
	function highlightPHP($src) {
		// phpタグが存在するか？
		$phptagf = false;
		if(!strstr($src,"<?php")) {
			$phptagf = TRUE;
			$src="<"."?php ".$src." ?".">";
		}
		ob_start(); //出力のバッファリングを有効に
		highlight_string($src); //phpは標準関数でハイライト
		$html = ob_get_contents(); //バッファの内容を得る
		ob_end_clean(); //バッファクリア?
		// phpタグを取り除く。
		if ($phptagf) {
			$html = preg_replace("/&lt;\?php (.*)?(<font[^>]*>\?&gt;<\/font>|\?&gt;)/m","$1",$html);
		}
		$html = str_replace('&nbsp;', ' ', $html);
		$html = str_replace("\n", '', $html); //$html内の"\n"を''で置き換える
		$html = str_replace('<br />', "\n", $html);
		//Vaild XHTML 1.1 Patch (thanks miko)
		$html = str_replace('<font color="', '<span style="color:', $html);
		$html = str_replace('</font>', '</span>', $html);
		return $html;
	}


}

/**
 * この関数は引数に与えられたファイルの内容を文字列に変換して返す
 * 文字コードは PukiWikiと同一, 改行は \n である
 */
function code_read_file_data($name) {
    global $vars;
    $filedata = '';
    $arraydata = array();
    // 添付ファイルのあるページ: defaultは現在のページ名
    $page = isset($vars['page']) ? $vars['page'] : '';

    // 添付ファイルまでのパスおよび(実際の)ファイル名
    $file = '';
    $fname = $name;

    $is_url = is_url($fname);

    /* Chech file location */
    if ($is_url) { // URL
		if (!PLUGIN_CODE_READ_URL) {
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

        if (!$is_file) {
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

    // PHP 4.0 Compatible
    $fdata = '';
    $filelines = file($fname);
    foreach ($filelines as $line) {
        $fdata .= str_replace("\r\n", "\n", $line);
    }
    $fdata = strtr($fdata, "\r", "\n");
    $fdata = mb_convert_encoding($fdata, SOURCE_ENCODING, "auto");

    /* // for PHP 4.3 or later
    $fdata = file_get_contents($fname);
    $fdata .= str_replace("\r\n", "\n", $fdata);
       $fdata = strtr($fdata, "\r", "\n");
    $fdata = mb_convert_encoding($fdata, SOURCE_ENCODING, "auto");
    */

	// ファイルの最後を改行にする
	if($fdata[strlen($fdata)-1] != "\n")
		$fdata .= "\n";

	$params['data'] = $fdata;

    return $params;
}

/**
 * オプション解析
 * 引数に対応するキーをOnにする
 */
function code_check_argment($arg, &$option) {
    $arg = strtolower($arg);
    if (array_key_exists($arg, $option)) {
        $option[$arg] = TRUE;
		return TRUE;
	}
	return FALSE;
}


?>
