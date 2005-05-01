<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: ref.inc.php,v 1.48.2 2005/01/28 11:58:56 miko Exp $
//
// Image refernce plugin
// Include an attached image-file as an inline-image

// File icon image
if (! defined('FILE_ICON'))
	define('FILE_ICON',
	'<img src="' . IMAGE_URI . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');

/////////////////////////////////////////////////
// Default settings

// Horizontal alignment
define('PLUGIN_REF_DEFAULT_ALIGN', 'left'); // 'left', 'center', 'right'

// Text wrapping
define('PLUGIN_REF_WRAP_TABLE', FALSE); // TRUE, FALSE

// URL������˲�����������������뤫
define('PLUGIN_REF_URL_GET_IMAGE_SIZE', TRUE); // FALSE, TRUE

// UPLOAD_DIR �Υǡ���(�����ե�����Τ�)��ľ�ܥ�������������
define('PLUGIN_REF_DIRECT_ACCESS', FALSE); // FALSE or TRUE
// - ����Ͻ���Υ���饤�󥤥᡼��������ߴ��Τ���˻Ĥ���Τ�
//   ���ꡢ��®���Τ���Υ��ץ����ǤϤ���ޤ���
// - UPLOAD_DIR ��Web�����С����Ϫ�Ф����Ƥ��ꡢ����ľ�ܥ�������
//   �Ǥ���(�����������¤��ʤ�)���֤Ǥ���ɬ�פ�����ޤ�
// - Apache �ʤɤǤ� UPLOAD_DIR/.htaccess ��������ɬ�פ�����ޤ�
// - �֥饦���ˤ�äƤϥ���饤�󥤥᡼����ɽ���䡢�֥���饤��
//   ���᡼��������ɽ���פ��������ʤɤ��Զ�礬�Ф��礬����ޤ�

/////////////////////////////////////////////////

// Image suffixes allowed
define('PLUGIN_REF_IMAGE', '/\.(gif|png|jpe?g)$/i');

// Usage (a part of)
define('PLUGIN_REF_USAGE', "([pagename/]attached-file-name[,parameters, ... ][,title])");

function plugin_ref_inline()
{
	// Not reached, because of "$aryargs[] = & $body" at plugin.php
	// if (! func_num_args())
	//	return '&amp;ref(): Usage:' . PLUGIN_REF_USAGE . ';';

	$params = plugin_ref_body(func_get_args());

	if (isset($params['_error']) && $params['_error'] != '') {
		// Error
		return '&amp;ref(): ' . $params['_error'] . ';';
	} else {
		return $params['_body'];
	}
}

function plugin_ref_convert()
{
	if (! func_num_args())
		return '<p>#ref(): Usage:' . PLUGIN_REF_USAGE . "</p>\n";

	$params = plugin_ref_body(func_get_args());

	if (isset($params['_error']) && $params['_error'] != '') {
		return "<p>#ref(): {$params['_error']}</p>\n";
	}

	if ((PLUGIN_REF_WRAP_TABLE && ! $params['nowrap']) || $params['wrap']) {
		// �Ȥ����
		// margin:auto
		//	Mozilla 1.x  = x (wrap,around�������ʤ�)
		//	Opera 6      = o
		//	Netscape 6   = x (wrap,around�������ʤ�)
		//	IE 6         = x (wrap,around�������ʤ�)
		// margin:0px
		//	Mozilla 1.x  = x (wrap�Ǵ󤻤������ʤ�)
		//	Opera 6      = x (wrap�Ǵ󤻤������ʤ�)
		//	Netscape 6   = x (wrap�Ǵ󤻤������ʤ�)
		//	IE6          = o
		$margin = ($params['around'] ? '0px' : 'auto');
		$margin_align = ($params['_align'] == 'center') ? '' : ";margin-{$params['_align']}:0px";
		$params['_body'] = <<<EOD
<table class="style_table" style="margin:$margin$margin_align">
 <tr>
  <td class="style_td">{$params['_body']}</td>
 </tr>
</table>
EOD;
	}

	if ($params['around']) {
		$style = ($params['_align'] == 'right') ? 'float:right' : 'float:left';
	} else {
		$style = "text-align:{$params['_align']}";
	}

	// div�����
	return "<div class=\"img_margin\" style=\"$style\">{$params['_body']}</div>\n";
}

function plugin_ref_body($args)
{
	global $script, $vars;
	global $WikiName, $BracketName; // compat

	// �����
	$params = array(
		'left'   => FALSE, // ����
		'center' => FALSE, // �����
		'right'  => FALSE, // ����
		'wrap'   => FALSE, // TABLE�ǰϤ�
		'nowrap' => FALSE, // TABLE�ǰϤޤʤ�
		'around' => FALSE, // ������
		'noicon' => FALSE, // ���������ɽ�����ʤ�
		'nolink' => FALSE, // ���ե�����ؤΥ�󥯤�ĥ��ʤ�
		'noimg'  => FALSE, // ������Ÿ�����ʤ�
		'zoom'   => FALSE, // �Ĳ�����ݻ�����
		'_size'  => FALSE, // ���������ꤢ��
		'_w'     => 0,       // ��
		'_h'     => 0,       // �⤵
		'_%'     => 0,     // ����Ψ
		'_args'  => array(),
		'_done'  => FALSE,
		'_error' => ''
	);

	// ź�եե�����Τ���ڡ���: default�ϸ��ߤΥڡ���̾
	$page = isset($vars['page']) ? $vars['page'] : '';

	// ź�եե�����Υե�����̾
	$name = '';

	// ź�եե�����ޤǤΥѥ������(�ºݤ�)�ե�����̾
	$file = '';

	// ������: "[�ڡ���̾�����/]ź�եե�����̾"�����뤤��"URL"�����
	$name = array_shift($args);
	$is_url = is_url($name);

	// �������� InterWiki ��
	if(is_interwiki($name)) {
		global $InterWikiName;
		preg_match("/^$InterWikiName$/", $name, $intermatch);
		$intername = $intermatch[2];
		$interparam = $intermatch[3];
		$interurl = get_interwiki_url($intername,$interparam);
		if ($interurl !== FALSE) {
			$name = $interurl;
			$is_url = is_url($name);
		}
	}

	if(! $is_url) {
		// ź�եե�����
		if (! is_dir(UPLOAD_DIR)) {
			$params['_error'] = 'No UPLOAD_DIR';
			return $params;
		}

		$matches = array();
		// �ե�����̾�˥ڡ���̾(�ڡ������ȥѥ�)����������Ƥ��뤫
		//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
		if (preg_match('#^(.+)/([^/]+)$#', $name, $matches)) {
			if ($matches[1] == '.' || $matches[1] == '..') {
				$matches[1] .= '/'; // Restore relative paths
			}
			$name = $matches[2];
			$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
			$file = UPLOAD_DIR . encode($page) . '_' . encode($name);
			$is_file = is_file($file);

		// ��������ʹߤ�¸�ߤ��������ref�Υ��ץ����̾�ΤʤɤȰ��פ��ʤ�
		} else if (isset($args[0]) && $args[0] != '' && ! isset($params[$args[0]])) {
			$e_name = encode($name);

			// Try the second argument, as a page-name or a path-name
			$_arg = get_fullname(strip_bracket($args[0]), $page); // strip is a compat
			$file = UPLOAD_DIR .  encode($_arg) . '_' . $e_name;
			$is_file_second = is_file($file);

			// If the second argument is WikiName, or double-bracket-inserted pagename (compat)
			$is_bracket_bracket = preg_match("/^($WikiName|\[\[$BracketName\]\])$/", $args[0]);

			if ($is_file_second && $is_bracket_bracket) {
				// Believe the second argument (compat)
				array_shift($args);
				$page = $_arg;
				$is_file = TRUE;
			} else {
				// Try default page, with default params
				$is_file_default = is_file(UPLOAD_DIR . encode($page) . '_' . $e_name);

				// Promote new design
				if ($is_file_default && $is_file_second) {
					// Because of race condition NOW
					$params['_error'] = htmlspecialchars('The same file name "' .
						$name . '" at both page: "' .  $page . '" and "' .  $_arg .
						'". Try ref(pagename/filename) to specify one of them');
				} else {
					// Because of possibility of race condition, in the future
					$params['_error'] = 'The style ref(filename,pagename) is ambiguous ' .
						'and become obsolete. ' .
						'Please try ref(pagename/filname)';
				}
				return $params;
			}
		} else {
			// Simple single argument
			$file = UPLOAD_DIR . encode($page) . '_' . encode($name);
			$is_file = is_file($file);
		}
		if (! $is_file) {
			$params['_error'] = htmlspecialchars('File not found: "' .
				$name . '" at page "' . $page . '"');
			return $params;
		}
	}

	// �Ĥ�ΰ����ν���
	if (! empty($args))
		foreach ($args as $arg)
			ref_check_arg($arg, $params);

/*
 $name���Ȥ˰ʲ����ѿ�������
 $url,$url2 : URL
 $title :�����ȥ�
 $is_image : �����ΤȤ�TRUE
 $info : �����ե�����ΤȤ�getimagesize()��'size'
         �����ե�����ʳ��Υե�����ξ���
         ź�եե�����ΤȤ� : �ե�����κǽ��������ȥ�����
         URL�ΤȤ� : URL���Τ��
*/
	$title = $url = $url2 = $info = '';
	$rawwidth = $rawheight = 0;
	$width = $height = 0;
	$matches = array();

	if ($is_url) {	// URL
		$url = $url2 = htmlspecialchars($name);
		$title = htmlspecialchars(preg_match('/([^\/]+)$/', $name, $matches) ? $matches[1] : $url);

		$is_image = (! $params['noimg'] && preg_match(PLUGIN_REF_IMAGE, $name));

		if ($is_image && PLUGIN_REF_URL_GET_IMAGE_SIZE && (bool)ini_get('allow_url_fopen')) {
			$size = @getimagesize($name);
			if (is_array($size)) {
				$rawwidth  = $width  = $size[0];
				$rawheight = $height = $size[1];
				$info   = $size[3];
			}
		}

	} else { // ź�եե�����

		$title = htmlspecialchars($name);

		$is_image = (! $params['noimg'] && preg_match(PLUGIN_REF_IMAGE, $name));

		// Count downloads with attach plugin
		$url = $script . '?plugin=attach' . '&amp;refer=' . rawurlencode($page) .
			'&amp;openfile=' . rawurlencode($name); // Show its filename at the last

		if ($is_image) {
			// Swap $url
			$url2 = $url;

			// URI for in-line image output
			if (! PLUGIN_REF_DIRECT_ACCESS) {
				// With ref plugin (faster than attach)
				$url = $script . '?plugin=ref' . '&amp;page=' . rawurlencode($page) .
					'&amp;src=' . rawurlencode($name); // Show its filename at the last
			} else {
				// Try direct-access, if possible
				$url = $file;
			}

			$width = $height = 0;
			$size = @getimagesize($file);
			if (is_array($size)) {
				$rawwidth  = $width  = $size[0];
				$rawheight = $height = $size[1];
			}
		} else {
			$info = get_date('Y/m/d H:i:s', filemtime($file) - LOCALZONE) .
				' ' . sprintf('%01.1f', round(filesize($file)/1024, 1)) . 'KB';
		}
	}

	// ��ĥ�ѥ�᡼��������å�
	if (! empty($params['_args'])) {
		$_title = array();
		foreach ($params['_args'] as $arg) {
			if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches)) {
				$params['_size'] = TRUE;
				$params['_w'] = $matches[1];
				$params['_h'] = $matches[2];

			} else if (preg_match('/^([0-9.]+)%$/', $arg, $matches) && $matches[1] > 0) {
				$params['_%'] = $matches[1];

			} else {
				$_title[] = $arg;
			}
		}

		if (! empty($_title)) {
			$title = htmlspecialchars(join(',', $_title));
			if ($is_image) $title = make_line_rules($title);
		}
	}

	// ����������Ĵ��
	if ($is_image) {
		// ���ꤵ�줿����������Ѥ���
		if ($params['_size']) {
			if ($width == 0 && $height == 0) {
				$width  = $params['_w'];
				$height = $params['_h'];
			} else if ($params['zoom']) {
				$_w = $params['_w'] ? $width  / $params['_w'] : 0;
				$_h = $params['_h'] ? $height / $params['_h'] : 0;
				$zoom = max($_w, $_h);
				if ($zoom) {
					$width  = (int)($width  / $zoom);
					$height = (int)($height / $zoom);
				}
			} else {
				$width  = $params['_w'] ? $params['_w'] : $width;
				$height = $params['_h'] ? $params['_h'] : $height;
			}
		}
		if ($params['_%']) {
			$width  = (int)($width  * $params['_%'] / 100);
			$height = (int)($height * $params['_%'] / 100);
		}
		if ($width && $height) $info = "width=\"$width\" height=\"$height\" ";
	}

	// ���饤�����Ƚ��
	$params['_align'] = PLUGIN_REF_DEFAULT_ALIGN;
	foreach (array('right', 'left', 'center') as $align) {
		if ($params[$align])  {
			$params['_align'] = $align;
			break;
		}
	}

	if ($is_image) {
		// DoCoMo�ο侩��128x128����
		// http://www.nttdocomo.co.jp/p_s/imode/xhtml/s1.html#1_4_2
		if ($rawwidth > 0 && $rawheight > 0 && $rawwidth<=240 && $rawheight<=240 && UA_PROFILE == 'keitai') {
			$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"keitai\" $info/>";
		} else {
			$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"$title\" $info/>";
			if (! $params['nolink'] && $url2)
				$params['_body'] = "<a href=\"$url2\" title=\"$title\">{$params['_body']}</a>";
		}
	} else {
		$icon = $params['noicon'] ? '' : FILE_ICON;
		$params['_body'] = "<a href=\"$url\" title=\"$info\">$icon$title</a>";
	}

	return $params;
}

// ���ץ�������Ϥ���
function ref_check_arg($val, & $params)
{
	if ($val == '') {
		$params['_done'] = TRUE;
		return;
	}

	if (! $params['_done']) {
		foreach (array_keys($params) as $key) {
			if (strpos($key, strtolower($val)) === 0) {
				$params[$key] = TRUE;
				return;
			}
		}
		$params['_done'] = TRUE;
	}

	$params['_args'][] = $val;
}

// Output an image (fast, non-logging <==> attach plugin)
function plugin_ref_action()
{
	global $vars;

	$usage = 'Usage: plugin=ref&amp;page=page_name&amp;src=attached_image_name';

	if (! isset($vars['page']) || ! isset($vars['src']))
		return array('msg'=>'Invalid argument', 'body'=>$usage);

	$page     = $vars['page'];
	$filename = $vars['src'] ;

	$ref = UPLOAD_DIR . encode($page) . '_' . encode(preg_replace('#^.*/#', '', $filename));
	if(! file_exists($ref))
		return array('msg'=>'Attach file not found', 'body'=>$usage);

	$got = @getimagesize($ref);
	if (! isset($got[2])) $got[2] = FALSE;
	switch ($got[2]) {
	case 1: $type = 'image/gif' ; break;
	case 2: $type = 'image/jpeg'; break;
	case 3: $type = 'image/png' ; break;
	case 4: $type = 'application/x-shockwave-flash'; break;
	default:
		return array('msg'=>'Seems not an image', 'body'=>$usage);
	}

	// Care for Japanese-character-included file name
	if (LANG == 'ja_JP') {
		switch(UA_NAME . '/' . UA_PROFILE){
		case 'Opera/default':
			// Care for using _auto-encode-detecting_ function
			$filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
			break;
		case 'MSIE/default':
			$filename = mb_convert_encoding($filename, 'SJIS', 'auto');
			break;
		}
	}
	$file = htmlspecialchars($filename);
	$size = filesize($ref);

	// Output
	pkwk_common_headers();
	header('Content-Disposition: inline; filename="' . $filename . '"');
	header('Content-Length: ' . $size);
	header('Content-Type: '   . $type);

	@readfile($ref);

	exit;
}
?>
