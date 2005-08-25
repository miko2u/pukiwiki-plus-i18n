<?php
/**
 * コードハイライト機能
 * @author sky
 * Time-stamp: <05/07/25 00:56:53 sasaki>
 * 
 * GPL
 *
 * code.inc.php Ver. 0.5.0
 */

define('PLUGIN_CODE_HEADER', 'code_');

class CodeHighlight {
	function CodeHighlight()
	{
		// common
        define('PLUGIN_CODE_CODE_CANCEL',          0); // 指定を無効化する
        define('PLUGIN_CODE_IDENTIFIRE',           2); 
        define('PLUGIN_CODE_SPECIAL_IDENTIFIRE',   3); 
        define('PLUGIN_CODE_STRING_LITERAL',       5); 
        define('PLUGIN_CODE_NONESCAPE_LITERAL',    7); 
        define('PLUGIN_CODE_PAIR_LITERAL',         8); 
        define('PLUGIN_CODE_ESCAPE',              10);
        define('PLUGIN_CODE_COMMENT',             11);
        define('PLUGIN_CODE_COMMENT_WORD',        12); // コメントが文字列で始まるもの
        define('PLUGIN_CODE_FORMULA',             14); 
		// outline
        define('PLUGIN_CODE_BLOCK_START',         20);
        define('PLUGIN_CODE_BLOCK_END',           21);
		// 行指向用
        define('PLUGIN_CODE_COMMENT_CHAR',        50); // 1文字でコメントと決定できるもの
        define('PLUGIN_CODE_COMMENT_WORD',        51); // コメントが文字列で始まるもの
        define('PLUGIN_CODE_HEAD_COMMENT',        52); // コメントが行頭だけのもの (1文字)  // fortran
        define('PLUGIN_CODE_HEADW_COMMENT',       53); // コメントが行頭だけのもの   // pukiwiki
        define('PLUGIN_CODE_CHAR_COMMENT',        54); // コメントが行頭だけかつ英字であるのもの (1文字) // fortran
        define('PLUGIN_CODE_IDENTIFIRE_CHAR',     60); // 1文字で命令が決定するもの
        define('PLUGIN_CODE_IDENTIFIRE_WORD',     61); // 命令が文字列で決定するもの
        define('PLUGIN_CODE_MULTILINE',           62); // 複数文字列への命令

        define('PLUGIN_CODE_CARRIAGERETURN',      70); // 空行
		define('PLUGIN_CODE_POST_IDENTIFIRE',     71); // 文末の語よって決まるルール		
	}

    function highlight(& $lang, & $src, & $option) {
		static $id_number = 0; // プラグインが呼ばれた回数(IDに利用)
        ++$id_number;

		if (strlen($lang) > 16)
            $lang = '';
		
		$option['number']  = (PLUGIN_CODE_NUMBER  && ! $option['nonumber']  || $option['number']);
		$option['outline'] = (PLUGIN_CODE_OUTLINE && ! $option['nooutline'] || $option['outline']);
		$option['comment'] = (PLUGIN_CODE_COMMENT && ! $option['nocomment'] || $option['comment']);
		$option['link']    = (PLUGIN_CODE_LINK    && ! $option['nolink']    || $option['link']);

        // mozillaの空白行対策
        if($option['number'] || $option['outline']) {
            // ライン表示用補正
            $src = preg_replace('/^$/m',' ',$src);
        }
		if (file_exists(PLUGIN_DIR.'code/keyword.'.$lang.'.php')) {
			// 言語定義ファイルが有る言語
			$data = $this->srcToHTML($src, $lang, $id_number, $option);
			$src = '<pre class="code"><code class="'.$lang.'">'.$data['src'].'</code></pre>';
		} else if (file_exists(PLUGIN_DIR.'code/line.'.$lang.'.php')) {
			// 行指向解析設定ファイルが有る言語
			$data = $this->lineToHTML($src, $lang, $id_number, $option);
			$src = '<pre class="code"><code class="'.$lang.'">'.$data['src'].'</code></pre>';
		} else {
			// PHP と 未定義言語
			$option['outline'] = false;
			$option['comment'] = false;

			// 最後の余分な改行を削除
			if ($src[strlen($src)-2] == ' ')
				$src = substr($src, 0, -2);
			else
				$src= substr($src, 0, -1);

			if ($option['number']) {
				// 行数を得る
				$num_of_line = substr_count($src, "\n");
 				if($src[strlen($src)-1]=="\n")
 					$src=substr($src,0,-1);
				$data = array('number' => '');	
				$data['number'] = _plugin_code_makeNumber($num_of_line-1);
			}
			if ('php' == $lang) 
				// PHPは標準機能を使う
				$src =  '<pre class="code">'.$this->highlightPHP($src). '</pre>';
			else
				// 未定義言語
				$src =  '<pre class="code"><code class="unknown">' .htmlspecialchars($src). '</code></pre>';
		}
		$option['menu']  = (PLUGIN_CODE_MENU  && ! $option['nomenu']  || $option['menu']);
		$option['menu']  = ($option['menu'] && ($option['outline'] || $option['comment']));

		$menu = '';
		if ($option['menu']) {
			// アイコンの設定
			$menu .= '<div class="'.PLUGIN_CODE_HEADER.'menu">';
			if ($option['outline']) {
				// アウトラインのメニュー
				$_code_expand = _('Everything is expanded.');
				$_code_short = _('Everything is shortened.');
				$menu .= '<img src="'.PLUGIN_CODE_OUTLINE_OPEN_FILE.'" style="cursor: hand" alt="'.$_code_expand.'" title="'.$_code_expand.'" '
					.'onclick="javascript:code_all_outline(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['blocknum'].',\'\',\''.IMAGE_DIR.'\')" '
					.'onkeypress="javascript:code_all_outline(\''.CODE_HEADER.$id_number.'\','.$data['blocknum'].',\'\',\''.IMAGE_DIR.'\')" />';
				$menu .= '<img src="'.PLUGIN_CODE_OUTLINE_CLOSE_FILE.'" style="cursor: hand" alt="'.$_code_short.'" title="'.$_code_short.'" '
					.'onclick="javascript:code_all_outline(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['blocknum'].',\'none\',\''.IMAGE_DIR.'\')" '
					.'onkeypress="javascript:code_all_outline(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['blocknum'].",'none','".IMAGE_DIR.'\')" />'."\n";
			}
			if ($option['comment']){
				// コメントの開閉ボタン
				$menu .= '<input type="button" value="comment open" '
					.'onclick="javascript:code_comment(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['commentnum'].',\')" '
					.'onkeypress="javascript:code_comment(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['commentnum'].',\')" />';
				$menu .= '<input type="button" value="comment close" '
					.' onclick="javascript:code_comment(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['commentnum'].',\'none\')" '
					.' onkeypress="javascript:code_comment(\''.PLUGIN_CODE_HEADER.$id_number.'\','.$data['commentnum'].',\'none\')" />';
			}
			$menu .= '</div>';
		}

		if ($option['number'])
			$data['number'] = '<pre class="'.PLUGIN_CODE_HEADER.'number">'.$data['number'].'</pre>';
		else
			$data['number'] = null;

		if ($option['outline'])
			$data['outline'] = '<pre class="'.PLUGIN_CODE_HEADER.'outline">'.$data['outline'].'</pre>';

		$html .= '<div id="'.PLUGIN_CODE_HEADER.$id_number.'" class="'.PLUGIN_CODE_HEADER.'table">'
			. $menu
			. _plugin_code_column($src, $data['number'], $data['outline'])
			. '</div>';

		return $html;

	}

	/**
	 * この関数は1行切り出す
	 * 定型フォーマットを持つ言語用
	 */
	function getline(& $string){
		$line = '';
		if(! $string[0]) return false;
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
	function lineToHTML(& $string, & $lang, $id_number, & $option) {

        // テーブルジャンプ用ハッシュ
        $switchHash = Array();
        $capital = false; // 大文字小文字を区別しない

		$option['outline'] = false; // outlineを使わない
		$mknumber  = $option['number'];

		// 改行
		$switchHash["\n"] = PLUGIN_CODE_CARRIAGERETURN;
		// エスケープ文字
        $switchHash['\\'] = PLUGIN_CODE_ESCAPE;
        // 識別子開始文字
        for ($i = ord('a'); $i <= ord('z'); ++$i)
            $switchHash[chr($i)] = PLUGIN_CODE_IDENTIFIRE;
        for ($i = ord('A'); $i <= ord('Z'); ++$i)
            $switchHash[chr($i)] = PLUGIN_CODE_IDENTIFIRE;
        $switchHash['_'] = PLUGIN_CODE_IDENTIFIRE;

        // 文字列開始文字
        $switchHash['"'] = PLUGIN_CODE_STRING_LITERAL;
		$linemode = false; // 行内を解析するか否か

        $str_len = strlen($string);
        // 文字->html変換用ハッシュ
        $htmlHash = Array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;', 
						  '&' => '&amp;', "\t" => PLUGIN_CODE_WIDTHOFTAB);
 
 
        // 言語定義ファイル読み込み
        include(PLUGIN_DIR.'code/line.'.$lang.'.php');
		
		$html = '';   // 出力されるHTMLコード付きソース
        $num_of_line = 0;  // 行数をカウント
		$commentnum = 0;  // コメントのID番号

		$line = $this->getline($string);
		while($line !== false) {
			++$num_of_line;
			while ($line[strlen($line)-2] == '\\') {
				// 行末がエスケープ文字なら次の行も切り出す
				++$num_of_line;
				$line .= $this->getline($string);
			}
			// 行頭文字の判定
            switch ($switchHash[$line[0]]) {

			case PLUGIN_CODE_CHAR_COMMENT:
			case PLUGIN_CODE_HEAD_COMMENT:
			case PLUGIN_CODE_COMMENT_CHAR:
				// 行頭の1文字でコメントと判断できるもの

				// htmlに追加
				++$commentnum;
				$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
				if ($option['link']) 
					$line = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										 '<a href="$0">$0</a>',$line);
				$html .= '<span class="'.PLUGIN_CODE_HEADER.'comment" id="'.PLUGIN_CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
					.$line.'</span>'."\n";

				$line = $this->getline($string); // next line
				continue 2;

			case PLUGIN_CODE_HEADW_COMMENT:
			case PLUGIN_CODE_COMMENT_WORD:
				// 2文字以上のパターンから始まるコメント
				if (strncmp($line, $commentpattern, strlen($commentpattern)) == 0) {
					// htmlに追加
					++$commentnum;
					$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
				if ($option['link']) 
					$line = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										 '<a href="$0">$0</a>',$line);
					$html .= '<span class="'.PLUGIN_CODE_HEADER.'comment" id="'.PLUGIN_CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line.'</span>'."\n";
					
					$line = $this->getline($string); // next line
					continue 2;
				}
				// コメントではない
				break;

			case PLUGIN_CODE_IDENTIFIRE_CHAR:
				// 行頭の1文字が意味を持つもの
				$index = $code_keyword[$line[0]];
				$line = htmlspecialchars($line, ENT_QUOTES);
				if ($option['link']) 
					$line = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										 '<a href="$0">$0</a>',$line);
				if ($index != '')
					$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
				else
					$html .= $line;

				$line = $this->getline($string); // next line
				continue 2;


			case PLUGIN_CODE_IDENTIFIRE_WORD:
				if (strlen($line) < 2 && $line[0] == ' ') break; // 空行判定
				// 行頭のパターンを調べる
				foreach ($code_identifire[$line[0]] as $pattern) {
					if (strncmp($line, $pattern, strlen($pattern)) == 0) {
						$index = $code_keyword[$pattern];
						// htmlに追加
						$line = htmlspecialchars($line, ENT_QUOTES);
						if ($option['link']) 
							$line = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
												 '<a href="$0">$0</a>',$line);
						if ($index != '')
							$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
						else
							$html .= $line;
						
						$line = $this->getline($string); // next line
						continue 3;
					}
				}
				// 行頭の1文字が意味を持つものか判定
				$index = $code_keyword[$line[0]];
				if ($index != '') {
					$line = htmlspecialchars($line, ENT_QUOTES);
					$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
					$line = $this->getline($string); // next line
					continue 2;
				}
				else
					// IDENTIFIREではない
					break;

			case PLUGIN_CODE_MULTILINE:
				// 複数行に渡って効果を持つ指定
				$index = $code_keyword[$line[0]];
				$src = $line;
				$line = $this->getline($string);
				while (in_array($line[0], $multilineEOL) === false && $line !== false) {
					// 効果の範囲内を取得する
					$src .= $line;
					++$num_of_line;
					$line = $this->getline($string);
				}
				$src = htmlspecialchars($src, ENT_QUOTES);
				if ($option['link']) 
					$src = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										'<a href="$0">$0</a>',$src);
				if ($index != '')
					$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$src.'</span>';
				else
					$html .= $src;
				continue 2;

			case PLUGIN_CODE_POST_IDENTIFIRE:
				// 行中の特定のパターンを検索する
				// makeのターゲット用 識別子(アルファベットから始まっている)
				$str_pos = strpos($line, $post_identifire);
				if ($str_pos !== false) {
					$result  = htmlspecialchars(substr($line, 0, $str_pos), ENT_QUOTES);
					$result2 = htmlspecialchars(substr($line, $str_pos+1), ENT_QUOTES);
					$html .= '<span class="'.PLUGIN_CODE_HEADER.'target">'.$result.$post_identifire.'</span>'
						.'<span class="'.PLUGIN_CODE_HEADER.'src">'.$result2.'</span>';
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
				if ($option['link']) 
					$html .= preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										  '<a href="$0">$0</a>',$line);

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
					
				case PLUGIN_CODE_CHAR_COMMENT: // 行頭以外ではコメントにはならない (fortran)
				case PLUGIN_CODE_IDENTIFIRE:
					// 識別子(アルファベットから始まっている)
					
					// 出来る限り長く識別子を得る
					--$str_pos;// エラー処理したくないからpreg_matchで必ず見つかるようにする
					$result = substr($line, $str_pos); 
					preg_match('/[A-Za-z0-9_\-]+/', $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
					
					// htmlに追加
					if($capital)
						$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index != '')
						$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;
					
				case PLUGIN_CODE_SPECIAL_IDENTIFIRE:
					// 特殊文字から始まる識別子
					// 次の文字が英字か判定
					if (! ctype_alpha($line[$str_pos])) break;
					$result = substr($line, $str_pos);
					preg_match('/[A-Za-z0-9_\-]+/', $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $code.$matches[0];
					// htmlに追加
					if($capital)
						$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index != '')
						$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case PLUGIN_CODE_STRING_LITERAL:
				case PLUGIN_CODE_NONESCAPE_LITERAL:
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
					if ($option['link']) 
						$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
											   '<a href="$0">$0</a>',$result);
					$html .= '<span class="'.PLUGIN_CODE_HEADER.'string">'.$result.'</span>';
					
					// 次の検索用に読み込み
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case PLUGIN_CODE_COMMENT_CHAR: // 1文字で決まるコメント
					$line = substr($line, $str_pos-1, $str_len-$str_pos);
					++$commentnum;
					$line = htmlspecialchars($line, ENT_QUOTES);
					if ($option['link']) 
						$line = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
											 '<a href="$0">$0</a>',$line);
					$html .= '<span class="'.PLUGIN_CODE_HEADER.'comment" id="'.PLUGIN_CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line.'</span>'."\n";
					
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
		if($mknumber) $html['number'] = _plugin_code_makeNumber($num_of_line-2); // 最後に改行を削除したため -2
		return $html;
	}

    /**
      * ソースからHTML生成
      */
    function srcToHTML(& $string, & $lang, $id_number, & $option) {
        // テーブルジャンプ用ハッシュ
        $switchHash = Array();
        $capital = false; // 大文字小文字を区別しない
		$mkoutline = $option['outline'];
		$mknumber  = $option['number'];

		// 改行
        $switchHash["\n"] = PLUGIN_CODE_CARRIAGERETURN;

        $switchHash['\\'] = PLUGIN_CODE_ESCAPE;
        // 識別子開始文字
        for ($i = ord('a'); $i <= ord('z'); ++$i)
            $switchHash[chr($i)] = PLUGIN_CODE_IDENTIFIRE;
        for ($i = ord('A'); $i <= ord('Z'); ++$i)
            $switchHash[chr($i)] = PLUGIN_CODE_IDENTIFIRE;
        $switchHash['_'] = PLUGIN_CODE_IDENTIFIRE;

        // 文字列開始文字
        $switchHash['"'] = PLUGIN_CODE_STRING_LITERAL;

        // 言語定義ファイル読み込み
        include(PLUGIN_DIR.'code/keyword.'.$lang.'.php');
		
        // 文字->html変換用ハッシュ
        $htmlHash = Array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;', 
						  '&' => '&amp;', "\t" => PLUGIN_CODE_WIDTHOFTAB);

        $html = '';
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

			case PLUGIN_CODE_CARRIAGERETURN: // 改行
				++$line;
				$html .="\n";
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case PLUGIN_CODE_ESCAPE:
				// escape charactor
				$start = $code;
				// 判定用にもう1文字読み込む
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				if (ctype_alnum($code)) {
					// 文字(変数)なら終端まで見付ける
					--$str_pos; // エラー処理したくないからpreg_matchで必ず見つかるようにする
					$result = substr($string, $str_pos);
					preg_match('/[A-Za-z0-9_]+/', $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
				} else {
					// 記号なら1文字だけ切り出す
					$result = $code;
					if ($code == "\n") ++$line;
				}
				
				// htmlに追加
				$html .= htmlspecialchars($start.$result, ENT_QUOTES);
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
			case PLUGIN_CODE_COMMENT:
				// コメント
				--$str_pos;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if (preg_match($pattern[0], $result)) {
					//if (!strncmp($pattern[1], $result, $pattern[0])) {
						$pos = strpos($result, $pattern[1]);
						if ($pos === false) { // 見つからないときは終わりまで
							$str_pos = $str_len;
							//$result = $result; ってことで何もしない
						} else {
							$pos += $pattern[2];
							$str_pos += $pos;
							$result = substr($result, 0, $pos);
						}
						// ライン数カウント
						$line+=substr_count($result,"\n");
						++$commentno;
						
						// htmlに追加
						$result = str_replace('\t', PLUGIN_CODE_WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						if ($option['link']) 
							$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
												   '<a href="$0">$0</a>',$result);
						$html .= '<span class="'.PLUGIN_CODE_HEADER.'comment" id="'.PLUGIN_CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// 次の検索用に読み込み
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				// コメントではない
				++$str_pos;
				break;
				
			case PLUGIN_CODE_COMMENT_WORD:
				// 文字列から始まるコメント
				
				// 出来る限り長く識別子を得る
				--$str_pos;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if (preg_match($pattern[0], $result)) {
						$pos = strpos($result, $pattern[1]);
						if ($pos === false) { // 見つからないときは終わりまで
							$str_pos = $str_len;
							//$result = $result; ってことで何もしない
						} else {
							$pos += $pattern[2];
							$str_pos += $pos;
							$result = substr($result, 0, $pos);
						}
						
						// ライン数カウント
						$line+=substr_count($result,"\n");
						++$commentno;
						
						// htmlに追加
						$result = str_replace('\t', PLUGIN_CODE_WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						if ($option['link']) 
							$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
												   '<a href="$0">$0</a>',$result);
						$html .= '<span class="'.PLUGIN_CODE_HEADER.'comment" id="'.PLUGIN_CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// 次の検索用に読み込み
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				++$str_pos;
				// コメントでなければ文字列 break を使わない
			case PLUGIN_CODE_IDENTIFIRE:
				// 識別子(アルファベットから始まっている)
				
				// 出来る限り長く識別子を得る
				--$str_pos;// エラー処理したくないからpreg_matchで必ず見つかるようにする
				$result = substr($string, $str_pos);
				preg_match('/[A-Za-z0-9_\-]+/', $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $matches[0];
				
				// htmlに追加
				if($capital)
					$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index != '')
					$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				//else              $html .= $start.$result.$end;
				else
					$html .= $result;
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_SPECIAL_IDENTIFIRE:
				// 特殊文字から始まる識別子
				// 次の文字が英字か判定
				if (! ctype_alpha($string[$str_pos])) break;
				$result = substr($string, $str_pos);
				preg_match('/[A-Za-z0-9_\-]+/', $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $code.$matches[0];
				// htmlに追加
				if($capital)
					$index = $code_keyword[strtolower($result)];// 大文字小文字を区別しない
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index!='')
					$html .= '<span class="'.PLUGIN_CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				else
					$html .= $result;
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case PLUGIN_CODE_STRING_LITERAL:
				// 文字列
				
				// 文字列リテラルを得る
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $code); // 文字列終了文字検索
					if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
						$str_pos = $str_len-1;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == '\\'); // 前の文字がエスケープ文字なら続ける
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// ライン数カウント
				$line+=substr_count($result,"\n");
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option['link']) 
					$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										   '<a href="$0">$0</a>',$result);
				$html .= '<span class="'.PLUGIN_CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_NONESCAPE_LITERAL:
				// エスケープ文字と式展開を無視した文字列
				// 文字列リテラルを得る

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // 文字列終了文字検索
				if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
					$str_pos = $str_len-1;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				// ライン数カウント
				$line+=substr_count($result,"\n");
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option['link']) 
					$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										   '<a href="$0">$0</a>',$result);
				$html .= '<span class="'.PLUGIN_CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_PAIR_LITERAL:
				// 対記号で囲まれた文字列リテラルを得る PostScript
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $literal_delimiter); // 文字列終了文字検索
					if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
						$str_pos = $str_len-1;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == '\\'); // 前の文字がエスケープ文字なら続ける
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// ライン数カウント
				$line+=substr_count($result,"\n");
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option['link']) 
					$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										   '<a href="$0">$0</a>',$result);
				$html .= '<span class="'.PLUGIN_CODE_HEADER.'string">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_FORMULA:
				// TeXの数式に使用 将来的には汎用性を持たせる 

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // 文字列終了文字検索
				if ($pos1 === false) { // 文字列が終わらなかったので全部文字列とする
					$str_pos = $str_len-1;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// htmlに追加
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option['link']) 
					$result = preg_replace('/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/',
										   '<a href="$0">$0</a>',$result);
				$html .= '<span class="'.PLUGIN_CODE_HEADER.'formula">'.$result.'</span>';
				
				// 次の検索用に読み込み
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_BLOCK_START:
				// outline 表示用開始文字 {, (
				
				++$blockno;
				++$nest;
				//if(! array_key_exists($line,$outline)) {
				if(! isset($outline[$line])) {
					$outline[$line]=Array();
				}
				array_push($outline[$line],Array('nest'=>$nest, 'blockno'=>$blockno));
				// アウトラインが閉じた時に表示する画像を埋め込む場所
				$html .= $code.'<span id="'.PLUGIN_CODE_HEADER.$id_number._.$blockno.'_img" display="none"></span>'
					.'<span id="'.PLUGIN_CODE_HEADER.$id_number._.$blockno.'">';
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				continue 2;
				
			case PLUGIN_CODE_BLOCK_END:
				// outline 表示終了文字 }, )
				
				--$nest;
				//if(! array_key_exists($line,$outline)) {
				if(! isset($outline[$line])) {
					$outline[$line]=Array();
					array_push($outline[$line],Array('nest'=>$nest,'blockno'=>0));
				} else {
					$old = array_pop($outline[$line]);
					if($old['blockno']!=0 && ($nest+1) == $old['nest']) {
					} else {
						if(! is_null($old))
							array_push($outline[$line],$old);
						array_push($outline[$line],Array('nest'=>$nest,'blockno'=>0));
					}
				}
				$last_start = false;
				$html .= '</span>'.$code;
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
		if($mknumber) $html['number'] = _plugin_code_makeNumber($line-1);
		return $html;
	}

	/**
	 * outline の形成
	 */
	function makeOutline(& $html,$line,$nest,$mknumber,$tree,$blockno,$id_number) {
		while($nest>1) {// ネストがちゃんとしてなかった場合の対策
			$html['src'] .= '</span>';
			--$nest;
		}
		$outline='';
		$number='';
		$nest=1;

		$linelen=$line+1;
		$str_len=max(3,strlen(''.$linelen));

		for($i=0;$i<$linelen;++$i) {
			$plus='';
			$plus1='';
			$plus2='';
			$minus='';
			//if(array_key_exists($i,$tree)) {
			if(isset($tree[$i])) {
				while(true) {
					$array = array_shift($tree[$i]);
					if (is_null($array))
						break;
					if ($nest<=$array['nest']) {
						$id=$id_number.'_'.$array['blockno'];
						if($plus=='')
							$plus = '<a class="'.PLUGIN_CODE_HEADER.'outline" href="javascript:code_outline(\''
								.PLUGIN_CODE_HEADER.$id.'\',\''.IMAGE_DIR.'\')" id="'.PLUGIN_CODE_HEADER.$id.'a">-</a>';
						$plus1 .= '<span id="'.PLUGIN_CODE_HEADER.$id.'o">';
						$plus2 .= '<span id="'.PLUGIN_CODE_HEADER.$id.'n">';
						$nest=$array['nest'];
					} else {
						$nest=$array['nest'];
						$minus .= '</span>';
					}
				}
			}
			if($mknumber) {
				$number.= sprintf('%'.$str_len.'d',($i+1)).$minus.$plus2."\n";
			}
			if($plus=='' && $minus == '') {
				if($nest==1)
					$outline.=' ';
				else
					$outline.='|';
			} else if($plus!='' && $minus == '') {
				$outline.= $plus.$plus1;
			} else if($plus=='' && $minus != '') {
				$outline.= '!'.$minus;
			} else if($plus!='' && $minus != '') {
				$outline.= $plus.$minus.$plus1;
			}
			$outline.="\n";
		}
		while ($nest>1) {// ネストがちゃんとしてなかった場合の対策
			$number .= '</span>';
			$outline .= '</span>';
			--$nest;
		}
		$html['number'] = $number;
		$html['outline'] = $outline;
		return $html;
	}

	/**
	 * PHPを標準関数でハイライトする
	 */
	function highlightPHP($src) {
		// phpタグが存在するか？
		$phptagf = false;
		if(! strstr($src,'<?php')) {
			$phptagf = true;
			$src='<'.'?php '.$src.' ?'.'>';
		}
		ob_start(); //出力のバッファリングを有効に
		highlight_string($src); //phpは標準関数でハイライト
		$html = ob_get_contents(); //バッファの内容を得る
		ob_end_clean(); //バッファクリア?
		// phpタグを取り除く。
		if ($phptagf) {
			$html = preg_replace('/&lt;\?php (.*)?(<font[^>]*>\?&gt;<\/font>|\?&gt;)/m','$1',$html);
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


/* pre.inc.php と共用 */

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
 * 行番号を作成する
 * 引数は行番号の範囲
 * 整形された行番号を返す
 */
function _plugin_code_makeNumber($end, $begin=0)
{
	$number='';
	$str_len=max(3,strlen(''.$end));
	for($i=$begin; $i<=$end; ++$i) {
		$number.= sprintf('%'.$str_len.'d',($i))."\n";
	}
	return $number;
}
/**
 * 段組みして出力する
 * 
 * 整形HTMLを返す
 */
function _plugin_code_column(& $text, $number=null, $outline=null)
{
	if ($number === null && $outline === null)
		return $text;
	
	if (PLUGIN_CODE_TABLE) {
		$html .= '<table class="'.PLUGIN_CODE_HEADER
			.'table" border="0" cellpadding="0" cellspacing="0"><tr>';
		if ($number !== null)
			$html .= '<td>'.$number.'</td>';
		if ($outline !== null)
			$html .= '<td>'.$outline.'</td>';
		$html .= '<td>'.$text.'</td></tr></table>';
	} else {
		if ($number !== null)
			$html .= '<div class="'.PLUGIN_CODE_HEADER.'number">'.$number.'</div>';
		if ($outline !== null)
			$html .= '<div class="'.PLUGIN_CODE_HEADER.'outline">'.$outline.'</div>';
		$html .= '<div class="'.PLUGIN_CODE_HEADER.'src">'.$text.'</div>'
			. '<div style="clear:both;"><br style="display:none;" /></div>';
	}

	return $html;
}

?>