<?php
/**
 * �����ɥϥ��饤�ȵ�ǽ��PukiWiki���ɲä���
 * Time-stamp: <04/12/28 00:57:17 sasaki>
 *
 * GPL
 *
 * Ver. 0.4.3_2.1
 */

define("PLUGIN_CODE_LANGUAGE", 'pre');  // ɸ�����
// ɸ������
define("PLUGIN_CODE_NUMBER",    TRUE);  // ���ֹ�
define("PLUGIN_CODE_OUTLINE",   TRUE);  // �����ȥ饤��;
define("PLUGIN_CODE_COMMENT",   FALSE); // ������ɽ��/��ɽ�� // 0.4.0 �Ǥ���侩
define("PLUGIN_CODE_MENU",      TRUE);  // ��˥塼��ɽ��/��ɽ��;
define("PLUGIN_CODE_FILE_ICON", TRUE);  // ź�եե�����˥�������ɥ���������դ���
define("PLUGIN_CODE_LINK",      TRUE);  // �����ȥ��

// URL�ǻ��ꤷ���ե�������ɤ߹��फ�ݤ�
define("PLUGIN_CODE_READ_URL",  TRUE);  // ɸ��Ǥ�ź�եե�����ʳ��ɤ߹��ޤʤ�

// �ơ��֥��Ȥ����ݤ�(FALSE��CSS��div�ˤ��ʬ��)
define("PLUGIN_CODE_TABLE",     TRUE);

// TAB��
define("WIDTHOFTAB", "    ");

define("PLUGIN_CODE_USAGE", 
       //'<p class="error">Plugin code: Usage:<br />#code(Lang){{<br />src<br />}}</p>');
	   '<p class="error">Plugin code: Usage:<br />#code(Lang)<< EOF<br />src<br />EOF</p>');

// for PukiWiki 1.4.5 or later
global $javascript; $javascript = TRUE;

define("CODE_HEADER", "code_");
// �����ե����������
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
                  "number"      => FALSE,  // ���ֹ��ɽ������
                  "nonumber"    => FALSE,  // ���ֹ��ɽ�����ʤ�
                  "outline"     => FALSE,  // �����ȥ饤�� �⡼��
                  "nooutline"   => FALSE,  // �����ȥ饤�� ̵��
		  "comment"     => FALSE,  // ������
		  "nocomment"   => FALSE,  // define("PLUGIN_CODE_", TRUE);
		  "menu"        => FALSE,  // ��˥塼��ɽ������
		  "nomenu"      => FALSE,  // ��˥塼��ɽ�����ʤ�
		  "icon"        => FALSE,  // ���������ɽ������
		  "noicon"      => FALSE,  // ���������ɽ�����ʤ�
                  "link"        => FALSE,  // �����ȥ�� ͭ��
                  "nolink"      => FALSE,  // �����ȥ�� ̵��
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
		$lang = $args[0]; // ����̾�����ץ�����Ƚ��
	}
	else
		$lang = PLUGIN_CODE_LANGUAGE; // default
		
    // ���ץ�����Ĵ�٤�
    for ($i = 1;$i < $num_of_arg-1; $i++) {
        code_check_argment($args[$i], $option);
    }
	

    // ���ԥ������Ѵ�
	$data = str_replace("\r\n", "\n", $data);
    $data = strtr($data,"\r", "\n");

    // �Ǹ��ʸ�������ԤǤʤ����ϳ����ե�����
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
        define("CODE_CANCEL",          0); // �����̵��������
        define("IDENTIFIRE",           2); 
        define("SPECIAL_IDENTIFIRE",   3); 
        define("STRING_LITERAL",       5); 
        define("NONESCAPE_LITERAL",    6); 
        define("PAIR_LITERAL",         7); 
        define("ESCAPE",              10);
        define("COMMENT",             11);
        define("FORMULA",             12); 
		// outline
        define("BLOCK_START",         20);
        define("BLOCK_END",           21);
		// �Իظ���
        define("COMMENT_CHAR",        50); // 1ʸ���ǥ����Ȥȷ���Ǥ�����
        define("COMMENT_WORD",        51); // �����Ȥ�ʸ����ǻϤޤ���
        define("HEAD_COMMENT",        52); // �����Ȥ���Ƭ�����Τ�� (1ʸ��)  // fortran
        define("HEADW_COMMENT",       53); // �����Ȥ���Ƭ�����Τ��   // pukiwiki
        define("CHAR_COMMENT",        54); // �����Ȥ���Ƭ�������ıѻ��Ǥ���Τ�� (1ʸ��) // fortran
        define("IDENTIFIRE_CHAR",     60); // 1ʸ����̿�᤬���ꤹ����
        define("IDENTIFIRE_WORD",     61); // ̿�᤬ʸ����Ƿ��ꤹ����
        define("MULTILINE",           62); // ʣ��ʸ����ؤ�̿��

        define("CARRIAGERETURN",      70); // ����
		define("POST_IDENTIFIRE",     71); // ʸ���θ��äƷ�ޤ�롼��		
	}

    function highlight($lang, $src, &$option) {
		static $id_number = 0; // �ץ饰���󤬸ƤФ줿���(ID������)
        $id_number++;

		if (strlen($lang) > 16)
            return null;
		
		$option["number"]  = (PLUGIN_CODE_NUMBER  && !$option["nonumber"]  || $option["number"]);
		$option["outline"] = (PLUGIN_CODE_OUTLINE && !$option["nooutline"] || $option["outline"]);
		$option["comment"] = (PLUGIN_CODE_COMMENT && !$option["nocomment"] || $option["comment"]);
		$option["link"]    = (PLUGIN_CODE_LINK    && !$option["nolink"]    || $option["link"]);

        // mozilla�ζ�����к�
        if($option["number"] || $option["outline"]) {
            // �饤��ɽ��������
            $src = preg_replace("/^$/m"," ",$src);
        }
		
        $lang = htmlspecialchars(strtolower($lang));

		if (file_exists(PLUGIN_DIR.'code/keyword.'.$lang.'.php')) {
			// ��������ե����뤬ͭ�����
			$data = $this->srcToHTML($src, $lang, $id_number, $option);
			$src = "<pre class=\"code\"><code class=\"" .$lang. "\">".$data['src']."</code></pre>";
		} else if (file_exists(PLUGIN_DIR.'code/line.'.$lang.'.php')) {
			// �Իظ�����������ե����뤬ͭ�����
			$data = $this->lineToHTML($src, $lang, $id_number, $option);
			$src = "<pre class=\"code\"><code class=\"" .$lang. "\">".$data['src']."</code></pre>";
		} else {
			// PHP �� ̤�������
			$option["outline"] = false;
			$option["comment"] = false;

			// �Ǹ��;ʬ�ʲ��Ԥ���
			if ($src[strlen($src)-2] == ' ')
				$src = substr($src, 0, -2);
			else
				$src= substr($src, 0, -1);

			if ($option["number"]) {
				// �Կ�������
				$num_of_line = substr_count($src, "\n");
 				if($src[strlen($src)-1]=="\n")
 					$src=substr($src,0,-1);
				$data = array('number' => '');	
				$data['number'] = $this->makeNumber($num_of_line-1);
			}
			if ('php' == $lang) 
				// PHP��ɸ�ൡǽ��Ȥ�
				$src =  "<pre class=\"code\">".$this->highlightPHP($src). "</pre>";
			else
				// ̤�������
				$src =  "<pre class=\"code\"><code class=\"unknown\">" .htmlspecialchars($src). "</code></pre>";
		}

		$option["menu"]  = (PLUGIN_CODE_MENU  && !$option["nomenu"]  || $option["menu"]);
		$option["menu"]  = ($option["menu"] && ($option["outline"] || $option["comment"]));

		$menu = '';
		if ($option["menu"]) {
			// �������������
			$menu .= '<div class="'.CODE_HEADER.'menu">';
			if ($option["outline"]) {
				// �����ȥ饤��Υ�˥塼
				$_code_expand = _("Everything is expanded.");
				$_code_short = _("Everything is shortened.");
				$menu .= "<img src=\"".CODE_OUTLINE_OPEN_FILE."\" style=\"cursor: hand\" alt=\"$_code_expand\" title=\"$_code_expand\" "
					."onclick=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'','".IMAGE_DIR."')\" "
					."onkeypress=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'','".IMAGE_DIR."')\" />";
				$menu .= "<img src=\"".CODE_OUTLINE_CLOSE_FILE."\" style=\"cursor: hand\" alt=\"$_code_short\" title=\"$_code_short\" "
					."onclick=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'none','".IMAGE_DIR."')\" "
					."onkeypress=\"javascript:code_all_outline('".CODE_HEADER.$id_number."',".$data['blocknum'].",'none','".IMAGE_DIR."')\" />\n";
			}
			if ($option["comment"]){
				// �����Ȥγ��ĥܥ���
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
				// �ơ��֥�ˤ������
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
				// CSS��div�ˤ������
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
	 * ���δؿ���1���ڤ�Ф�
	 * �귿�ե����ޥåȤ���ĸ�����
	 */
	function getline(&$string){
		$line = '';
		if(!$string[0]) return false;
		$pos = strpos($string, "\n"); // ���Ԥޤ��ڤ�Ф�
		if ($pos === false) { // ���Ĥ���ʤ��Ȥ��Ͻ����ޤ�
			$line = $string;
			$string = '';
		} else {
			$line = substr($string, 0, $pos+1);
			$string = substr($string, $pos+1);
		}
		return $line;
	}


	/**
	 * ���δؿ��Ϲ�Ƭ��ʸ����Ƚ�ꤷ�Ʋ��ϡ��Ѵ�����
	 * �귿�ե����ޥåȤ���ĸ�����
	 */
	function lineToHTML($string, $lang, $id_number, &$option) {

        // �ơ��֥른�����ѥϥå���
        $switchHash = Array();
        $capital = FALSE; // ��ʸ����ʸ������̤��ʤ�

		$option["outline"] = false; // outline��Ȥ�ʤ�
		$mknumber  = $option["number"];

		// ����
		$switchHash["\n"] = CARRIAGERETURN;
		// ����������ʸ��
        $switchHash['\\'] = ESCAPE;
        // ���̻ҳ���ʸ��
        for ($i = ord("a"); $i <= ord("z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        for ($i = ord("A"); $i <= ord("Z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        $switchHash["_"] = IDENTIFIRE;

        // ʸ���󳫻�ʸ��
        $switchHash["\""] = STRING_LITERAL;
		$linemode = false; // �������Ϥ��뤫�ݤ�

        // ��������ե������ɤ߹���
        include(PLUGIN_DIR.'code/line.'.$lang.'.php');


        $str_len = strlen($string);
        // ʸ��->html�Ѵ��ѥϥå���
        $htmlHash = Array("\"" => "&quot;", "'" => "&#039;", "<" => "&lt;", ">" => "&gt;", 
						  "&" => "&amp;", "\t" => WIDTHOFTAB);
 
        $html = "";   // ���Ϥ����HTML�������դ�������
        $num_of_line = 0;  // �Կ��򥫥����
		$commentnum = 0;  // �����Ȥ�ID�ֹ�

		$line = $this->getline($string);
		while($line !== false) {
			$num_of_line++;
			while ($line[strlen($line)-2] == "\\") {
				// ����������������ʸ���ʤ鼡�ιԤ��ڤ�Ф�
				$num_of_line++;
				$line .= $this->getline($string);
			}
			// ��Ƭʸ����Ƚ��
            switch ($switchHash[$line[0]]) {

			case CHAR_COMMENT:
			case HEAD_COMMENT:
			case COMMENT_CHAR:
				// ��Ƭ��1ʸ���ǥ����Ȥ�Ƚ�ǤǤ�����

				// html���ɲ�
				$commentnum++;
				$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
				if ($option["link"]) 
					$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										 "<a href=\"$0\">$0</a>",$line);
				$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
					.$line."</span>\n";

				$line = $this->getline($string); // next line
				continue 2;

			case HEADW_COMMENT:
			case COMMENT_WORD:
				// 2ʸ���ʾ�Υѥ����󤫤�Ϥޤ륳����
				if (strncmp($line, $commentpattern, strlen($commentpattern)) == 0) {
					// html���ɲ�
					$commentnum++;
					$line = htmlspecialchars(substr($line,0,-1), ENT_QUOTES);
				if ($option["link"]) 
					$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										 "<a href=\"$0\">$0</a>",$line);
					$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line."</span>\n";
					
					$line = $this->getline($string); // next line
					continue 2;
				}
				// �����ȤǤϤʤ�
				break;

			case IDENTIFIRE_CHAR:
				// ��Ƭ��1ʸ������̣����Ĥ��
				$index = $code_keyword[$line[0]];
				$line = htmlspecialchars($line, ENT_QUOTES);
				if ($option["link"]) 
					$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										 "<a href=\"$0\">$0</a>",$line);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
				else
					$html .= $line;

				$line = $this->getline($string); // next line
				continue 2;


			case IDENTIFIRE_WORD:
				if (strlen($line) < 2 && $line[0] == ' ') break; // ����Ƚ��
				// ��Ƭ�Υѥ������Ĵ�٤�
				foreach ($code_identifire[$line[0]] as $pattern) {
					if (strncmp($line, $pattern, strlen($pattern)) == 0) {
						$index = $code_keyword[$pattern];
						// html���ɲ�
						$line = htmlspecialchars($line, ENT_QUOTES);
						if ($option["link"]) 
							$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
												 "<a href=\"$0\">$0</a>",$line);
						if ($index != "")
							$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
						else
							$html .= $line;
						
						$line = $this->getline($string); // next line
						continue 3;
					}
				}
				// ��Ƭ��1ʸ������̣����Ĥ�Τ�Ƚ��
				$index = $code_keyword[$line[0]];
				if ($index != "") {
					$line = htmlspecialchars($line, ENT_QUOTES);
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$line.'</span>';
					$line = $this->getline($string); // next line
					continue 2;
				}
				else
					// IDENTIFIRE�ǤϤʤ�
					break;

			case MULTILINE:
				// ʣ���Ԥ��ϤäƸ��̤���Ļ���
				$index = $code_keyword[$line[0]];
				$src = $line;
				$line = $this->getline($string);
				while (in_array($line[0], $multilineEOL) === false && $line !== false) {
					// ���̤��ϰ�����������
					$src .= $line;
					$num_of_line++;
					$line = $this->getline($string);
				}
				$src = htmlspecialchars($src, ENT_QUOTES);
				if ($option["link"]) 
					$src = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										"<a href=\"$0\">$0</a>",$src);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$src.'</span>';
				else
					$html .= $src;
				continue 2;

			case POST_IDENTIFIRE:
				// ���������Υѥ�����򸡺�����
				// make�Υ������å��� ���̻�(����ե��٥åȤ���ϤޤäƤ���)
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
					// �������ʤ�
					break;

			default:
				// �������Ϥ�����HTML���ɲä��� (diff)
				if($linemode) {
					$line = htmlspecialchars($line, ENT_QUOTES);
				if ($option["link"]) 
					$html .= preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										  "<a href=\"$0\">$0</a>",$line);

					$line = $this->getline($string); // next line
					continue 2;
				}
			} //switch
				
			// ����β��� 1ʸ�����Ĳ��Ϥ���
			$str_len = strlen($line);
			$str_pos = 0;
			if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++];// getc
			while($code !== false) {
				switch ($switchHash[$code]) {
					
				case CHAR_COMMENT: // ��Ƭ�ʳ��Ǥϥ����ȤˤϤʤ�ʤ� (fortran)
				case IDENTIFIRE:
					// ���̻�(����ե��٥åȤ���ϤޤäƤ���)
					
					// �����¤�Ĺ�����̻Ҥ�����
					$str_pos--;// ���顼�����������ʤ�����preg_match��ɬ�����Ĥ���褦�ˤ���
					$result = substr($line, $str_pos); 
					preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
					
					// html���ɲ�
					if($capital)
						$index = $code_keyword[strtolower($result)];// ��ʸ����ʸ������̤��ʤ�
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index != "")
						$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// ���θ����Ѥ��ɤ߹���
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;
					
				case SPECIAL_IDENTIFIRE:
					// �ü�ʸ������Ϥޤ뼱�̻�
					// ����ʸ�����ѻ���Ƚ��
					if (!ctype_alpha($line[$str_pos])) break;
					$result = substr($line, $str_pos);
					preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $code.$matches[0];
					// html���ɲ�
					if($capital)
						$index = $code_keyword[strtolower($result)];// ��ʸ����ʸ������̤��ʤ�
					else
						$index = $code_keyword[$result];
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($index!="")
						$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
					else
						$html .= $result;
					
					// ���θ����Ѥ��ɤ߹���
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case STRING_LITERAL:
				case NONESCAPE_LITERAL:
					// ʸ�����ƥ������� //���ߥ��������פ���ɬ�פ�̵��
					$pos = $str_pos;
					$result = substr($line, $str_pos);
					$pos1 = strpos($result, $code); // ʸ����λʸ������
					if ($pos1 === false) { // ʸ���󤬽����ʤ��ä��Τ�����ʸ����Ȥ���
						$str_pos = $str_len;
					} else {
						$str_pos += $pos1 + 1;
					}
					$result = $code.substr($line, $pos, $str_pos - $pos);
					
					// html���ɲ�
					$result = htmlspecialchars($result, ENT_QUOTES);
					if ($option["link"]) 
						$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
											   "<a href=\"$0\">$0</a>",$result);
					$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
					
					// ���θ����Ѥ��ɤ߹���
					if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc
					continue 2;

				case COMMENT_CHAR: // 1ʸ���Ƿ�ޤ륳����
					$line = substr($line, $str_pos-1, $str_len-$str_pos);
					$commentnum++;
					$line = htmlspecialchars($line, ENT_QUOTES);
					if ($option["link"]) 
						$line = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
											 "<a href=\"$0\">$0</a>",$line);
					$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentnum.'">'
						.$line."</span>\n";
					
					$line = $this->getline($string); // next line
					continue 3;

				} //switch
				// ����¾��ʸ��
				$result = $htmlHash[$code];
				if ($result) 
					$html .= $result;
				else
					$html .= $code;
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $line[$str_pos++]; // getc

			}// while
			
			$line = $this->getline($string); // next line
		} // while
		
		// �Ǹ��;ʬ�ʲ��Ԥ���
		if ($html[strlen($html)-2] == ' ')
			$html = substr($html, 0, -2);
		else
			$html = substr($html, 0, -1);
		
		$html = array( 'src' => $html,  'number' => '', 'outline' => '', 'commentnum' => $commentnum,);
		if($mknumber) $html['number'] = $this->makeNumber($num_of_line-2); // �Ǹ�˲��Ԥ����������� -2
		return $html;
	}

    /**
      * ����������HTML����
      */
    function srcToHTML($string, $lang, $id_number, &$option) {

        // �ơ��֥른�����ѥϥå���
        $switchHash = Array();
        $capital = FALSE; // ��ʸ����ʸ������̤��ʤ�
		$mkoutline = $option["outline"];
		$mknumber  = $option["number"];

		// ����
        $switchHash["\n"] = CARRIAGERETURN;

        $switchHash['\\'] = ESCAPE;
        // ���̻ҳ���ʸ��
        for ($i = ord("a"); $i <= ord("z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        for ($i = ord("A"); $i <= ord("Z"); $i++)
            $switchHash[chr($i)] = IDENTIFIRE;
        $switchHash["_"] = IDENTIFIRE;

        // ʸ���󳫻�ʸ��
        $switchHash["\""] = STRING_LITERAL;

        // ��������ե������ɤ߹���
        include(PLUGIN_DIR.'code/keyword.'.$lang.'.php');
		
        // ʸ��->html�Ѵ��ѥϥå���
        $htmlHash = Array("\"" => "&quot;", "'" => "&#039;", "<" => "&lt;", ">" => "&gt;", 
						  "&" => "&amp;", "\t" => WIDTHOFTAB);

        $html = "";
        $str_len = strlen($string);
        $str_pos = 0;
        $line = 0;  // �Կ��򥫥����
        // for outline
        $outline = Array();// $outline[lineno][nest] $outline[lineno][blockno]�����롣
        $nest = 1;// �ͥ���
        $blockno = 0;// �����ܤΥ֥�å�����ID���ˡ����ˤ��뤿����Ѥ���
        $last_start = false;// �Ǹ�˥֥�å����Ϥ��ä�����������
		$commentno = 0;

        // �ǽ�θ����Ѥ��ɤ߹���
        if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++];// getc
        while ($code !== false) {

            switch ($switchHash[$code]) {

			case CARRIAGERETURN: // ����
				$line++;
				$html .="\n";
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case ESCAPE:
				// escape charactor
				$start = $code;
				// Ƚ���Ѥˤ⤦1ʸ���ɤ߹���
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				if (ctype_alnum($code)) {
					// ʸ��(�ѿ�)�ʤ齪ü�ޤǸ��դ���
					$str_pos--; // ���顼�����������ʤ�����preg_match��ɬ�����Ĥ���褦�ˤ���
					$result = substr($string, $str_pos);
					preg_match("/[A-Za-z0-9_]+/", $result, $matches);
					$str_pos += strlen($matches[0]);
					$result = $matches[0];
				} else {
					// ����ʤ�1ʸ�������ڤ�Ф�
					$result = $code;
					if ($code == "\n") $line++;
				}
				
				// html���ɲ�
				$html .= htmlspecialchars($start.$result, ENT_QUOTES);
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case COMMENT:
				// ������
				
				// �����¤�Ĺ�����̻Ҥ�����
				$str_pos--;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if(preg_match($pattern, $result, $matches)==1) {
						$str_pos += strlen($matches[0]);
						$result = $matches[0];
						
						// �饤����������
						$line+=substr_count($result,"\n");
						$commentno++;
						
						// html���ɲ�
						$result = str_replace("\t", WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						if ($option["link"]) 
							$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
												   "<a href=\"$0\">$0</a>",$result);
						$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// ���θ����Ѥ��ɤ߹���
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				// �����ȤǤϤʤ�
				$str_pos++;
				break;
				
			case COMMENT_WORD:
				// ʸ���󤫤�Ϥޤ륳����
				
				// �����¤�Ĺ�����̻Ҥ�����
				$str_pos--;
				$result = substr($string, $str_pos);
				foreach($code_comment[$code] as $pattern) {
					if(preg_match($pattern, $result, $matches)==1) {
						$str_pos += strlen($matches[0]);
						$result = $matches[0];
						
						// �饤����������
						$line+=substr_count($result,"\n");
						$commentno++;
						
						// html���ɲ�
						$result = str_replace("\t", WIDTHOFTAB, htmlspecialchars($result, ENT_QUOTES));
						if ($option["link"]) 
							$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
												   "<a href=\"$0\">$0</a>",$result);
						$html .= '<span class="'.CODE_HEADER.'comment" id="'.CODE_HEADER.$id_number.'_cmt_'.$commentno.'">'
							.$result.'</span>';
						
						// ���θ����Ѥ��ɤ߹���
						if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
						continue 3;
					}
				}
				$str_pos++;
				// �����ȤǤʤ����ʸ���� break ��Ȥ�ʤ�
			case IDENTIFIRE:
				// ���̻�(����ե��٥åȤ���ϤޤäƤ���)
				
				// �����¤�Ĺ�����̻Ҥ�����
				$str_pos--;// ���顼�����������ʤ�����preg_match��ɬ�����Ĥ���褦�ˤ���
				$result = substr($string, $str_pos);
				preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $matches[0];
				
				// html���ɲ�
				if($capital)
					$index = $code_keyword[strtolower($result)];// ��ʸ����ʸ������̤��ʤ�
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index != "")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				//else              $html .= $start.$result.$end;
				else
					$html .= $result;
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case SPECIAL_IDENTIFIRE:
				// �ü�ʸ������Ϥޤ뼱�̻�
				// ����ʸ�����ѻ���Ƚ��
				if (!ctype_alpha($string[$str_pos])) break;
				$result = substr($string, $str_pos);
				preg_match("/[A-Za-z0-9_\-]+/", $result, $matches);
				$str_pos += strlen($matches[0]);
				$result = $code.$matches[0];
				// html���ɲ�
				if($capital)
					$index = $code_keyword[strtolower($result)];// ��ʸ����ʸ������̤��ʤ�
				else
					$index = $code_keyword[$result];
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($index!="")
					$html .= '<span class="'.CODE_HEADER.$code_css[$index-1].'">'.$result.'</span>';
				else
					$html .= $result;
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;

			case STRING_LITERAL:
				// ʸ����
				
				// ʸ�����ƥ�������
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $code); // ʸ����λʸ������
					if ($pos1 === false) { // ʸ���󤬽����ʤ��ä��Τ�����ʸ����Ȥ���
						$str_pos = $str_len;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == "\\"); // ����ʸ��������������ʸ���ʤ�³����
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// �饤����������
				$line+=substr_count($result,"\n");
				
				// html���ɲ�
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option["link"]) 
					$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										   "<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case NONESCAPE_LITERAL:
				// ����������ʸ���ȼ�Ÿ����̵�뤷��ʸ����
				// ʸ�����ƥ�������

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // ʸ����λʸ������
				if ($pos1 === false) { // ʸ���󤬽����ʤ��ä��Τ�����ʸ����Ȥ���
					$str_pos = $str_len;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// html���ɲ�
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option["link"]) 
					$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										   "<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case PAIR_LITERAL:
				// �е���ǰϤޤ줿ʸ�����ƥ������� PostScript
				$pos = $str_pos;
				do {
					$result = substr($string, $str_pos);
					$pos1 = strpos($result, $literal_delimiter); // ʸ����λʸ������
					if ($pos1 === false) { // ʸ���󤬽����ʤ��ä��Τ�����ʸ����Ȥ���
						$str_pos = $str_len;
						break;
					}
					$str_pos += $pos1 + 1;
				} while ($string[$str_pos-2] == "\\"); // ����ʸ��������������ʸ���ʤ�³����
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// �饤����������
				$line+=substr_count($result,"\n");
				
				// html���ɲ�
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option["link"]) 
					$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										   "<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'string">'.$result.'</span>';
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case FORMULA:
				// TeX�ο����˻��� ����Ū�ˤ���������������� 

				$pos = $str_pos;
				$result = substr($string, $str_pos);
				$pos1 = strpos($result, $code); // ʸ����λʸ������
				if ($pos1 === false) { // ʸ���󤬽����ʤ��ä��Τ�����ʸ����Ȥ���
					$str_pos = $str_len;
				} else {
					$str_pos += $pos1 + 1;
				}
				$result = $code.substr($string, $pos, $str_pos - $pos);
				
				// html���ɲ�
				$result = htmlspecialchars($result, ENT_QUOTES);
				if ($option["link"]) 
					$result = preg_replace("/(s?https?:\/\/|ftp:\/\/|mailto:)([-_.!~*()a-zA-Z0-9;\/:@?=+$,%#]|&amp;)+/",
										   "<a href=\"$0\">$0</a>",$result);
				$html .= '<span class="'.CODE_HEADER.'formula">'.$result.'</span>';
				
				// ���θ����Ѥ��ɤ߹���
				if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc
				continue 2;
				
			case BLOCK_START:
				// outline ɽ���ѳ���ʸ�� {, (
				
				$blockno++;
				$nest++;
				if(!array_key_exists($line,$outline)) {
					$outline[$line]=Array();
				}
				array_push($outline[$line],Array("nest"=>$nest, "blockno"=>$blockno));
				// �����ȥ饤���Ĥ�������ɽ�������������������
				$html .= $code.'<span id="'.CODE_HEADER.$id_number._.$blockno.'_img" display="none"></span>'
					.'<span id="'.CODE_HEADER.$id_number._.$blockno.'">';
				if ($str_len == $str_pos)
					$code = false;
				else
					$code = $string[$str_pos++]; // getc
				continue 2;
				
			case BLOCK_END:
				// outline ɽ����λʸ�� }, )
				
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
			
            // ����¾��ʸ��
            $result = $htmlHash[$code];
            if ($result) 
				$html .= $result;
            else
                $html .= $code;

            // ���θ����Ѥ��ɤ߹���
            if ($str_len == $str_pos) $code = false; else $code = $string[$str_pos++]; // getc

        }// while

		// �Ǹ��;ʬ�ʲ��Ԥ���
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

	// outline �η���
	function makeOutline(&$html,$line,$nest,$mknumber,$tree,$blockno,$id_number) {
		while($nest>1) {// �ͥ��Ȥ������Ȥ��Ƥʤ��ä������к�
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
		while ($nest>1) {// �ͥ��Ȥ������Ȥ��Ƥʤ��ä������к�
			$number .= "</span>";
			$outline .= "</span>";
			$nest--;
		}
		$html['number'] = $number;
		$html['outline'] = $outline;
		return $html;
	}
	
	
	// number �η���
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
	 * PHP��ɸ��ؿ��ǥϥ��饤�Ȥ���
	 */
	function highlightPHP($src) {
		// php������¸�ߤ��뤫��
		$phptagf = false;
		if(!strstr($src,"<?php")) {
			$phptagf = TRUE;
			$src="<"."?php ".$src." ?".">";
		}
		ob_start(); //���ϤΥХåե���󥰤�ͭ����
		highlight_string($src); //php��ɸ��ؿ��ǥϥ��饤��
		$html = ob_get_contents(); //�Хåե������Ƥ�����
		ob_end_clean(); //�Хåե����ꥢ?
		// php�������������
		if ($phptagf) {
			$html = preg_replace("/&lt;\?php (.*)?(<font[^>]*>\?&gt;<\/font>|\?&gt;)/m","$1",$html);
		}
		$html = str_replace('&nbsp;', ' ', $html);
		$html = str_replace("\n", '', $html); //$html���"\n"��''���֤�������
		$html = str_replace('<br />', "\n", $html);
		//Vaild XHTML 1.1 Patch (thanks miko)
		$html = str_replace('<font color="', '<span style="color:', $html);
		$html = str_replace('</font>', '</span>', $html);
		return $html;
	}


}

/**
 * ���δؿ��ϰ�����Ϳ����줿�ե���������Ƥ�ʸ������Ѵ������֤�
 * ʸ�������ɤ� PukiWiki��Ʊ��, ���Ԥ� \n �Ǥ���
 */
function code_read_file_data($name) {
    global $vars;
    $filedata = '';
    $arraydata = array();
    // ź�եե�����Τ���ڡ���: default�ϸ��ߤΥڡ���̾
    $page = isset($vars['page']) ? $vars['page'] : '';

    // ź�եե�����ޤǤΥѥ������(�ºݤ�)�ե�����̾
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
    } else {  // ź�եե�����
        if (! is_dir(UPLOAD_DIR)) {
            $params['_error'] = 'No UPLOAD_DIR';
            return $params;
        }

        $matches = array();
        // �ե�����̾�˥ڡ���̾(�ڡ������ȥѥ�)����������Ƥ��뤫
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

	// �ե�����κǸ����Ԥˤ���
	if($fdata[strlen($fdata)-1] != "\n")
		$fdata .= "\n";

	$params['data'] = $fdata;

    return $params;
}

/**
 * ���ץ�������
 * �������б����륭����On�ˤ���
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
