<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: attachref.inc.php,v 0.14.12 2008/06/19 22:46:00 upk Exp $
// Copyright (C)
//   2005-2006,2008 PukiWiki Plus! Team
//   2002-2004 sha
//
// File attach & ref plugin

defined('ATTACHREF_UPLOAD_MAX_FILESIZE') or define('ATTACHREF_UPLOAD_MAX_FILESIZE', '4M'); // default: 4MB
// max file size for upload on PHP(PHP default 2MB)
ini_set('upload_max_filesize', ATTACHREF_UPLOAD_MAX_FILESIZE);
// max file size for upload on script of PukiWiki(default 2MB)
defined('ATTACHREF_MAX_FILESIZE')        or define('ATTACHREF_MAX_FILESIZE',(2048 * 1024));
// Admin Only for upload/delete
defined('ATTACHREF_UPLOAD_ADMIN_ONLY')   or define('ATTACHREF_UPLOAD_ADMIN_ONLY', FALSE); // FALSE or TRUE
// Requied password for upload/delete (ATTACHREF_UPLOAD_ADMIN_ONLY gives priority)
defined('ATTACHREF_PASSWORD_REQUIRE')    or define('ATTACHREF_PASSWORD_REQUIRE', FALSE);  // FALSE or TRUE
// Text wrapping
defined('PLUGIN_ATTACHREF_WRAP_TABLE')   or define('PLUGIN_ATTACHREF_WRAP_TABLE', FALSE); // FALSE or TRUE

function plugin_attachref_init()
{
	$messages = array(
		'_attachref_messages' => array(
			// copy of attach.inc.php
			'msg_upload'   => _("Upload to $1"),
			'msg_maxsize'  => _("Maximum file size is %s."),
			'msg_adminpass'=> _("Administrator password"),
			'msg_password' => _("password"),
			'msg_file'     => _("Attach file"),
			'btn_upload'   => _("Upload"),

			// original attachref.inc.php
			'btn_submit'    => _("[Upload]"),
			'msg_title'     => _("Attach and Ref to $1"),
			'msg_title_collided' => _("On updating $1, a collision has occurred."),
			'msg_collided'  => _("It seems that someone has already updated the page you were editing.<br />The attach file was added, alhough it may be inserted in the wrong position.<br />"),
		),
	);
	set_plugin_messages($messages);
}

function plugin_attachref_options(&$extra_options, $args)
{
	global $vars;
	static $numbers = array();
	static $no_flag = 0;
	$button = 0;

	if (!isset($numbers[$vars['page']])) {
		$numbers[$vars['page']] = 0;
	}

	$attachref_no = $numbers[$vars['page']]++;

	$options = array();
	foreach ($args as $opt) {
		switch ($opt) {
		case 'button':
			$button = 1;
			break;
		case 'number':
			$no_flag = 1;
			break;
		case 'nonumber':
			$no_flag = 0;
			break;
		default:
			array_push($options, $opt);
		}
	}

	$extra_options['button'] = $button;
	$extra_options['refnum'] = $attachref_no;
	$extra_options['text'] = $no_flag ? '['.$attachref_no.']':'';
	return $options;
}

function plugin_attachref_convert()
{
	global $script,$vars,$digest;
	global $_attachref_messages;

	$extra_options = array();
	$args = func_get_args();
//	$btn_text = array_pop($args);
//	$btn_text = $btn_text ? $btn_text : $_attachref_messages['btn_submit'];
	$btn_text = $_attachref_messages['btn_submit'];
	$options = plugin_attachref_options($extra_options, $args);
	$button = $extra_options['button'];
	$attachref_no = $extra_options['refnum'];
	$btn_text .= $extra_options['text'];

	$ret = '';
	$dispattach = 1;

	$args = $options;
	if ( count($args) and $args[0] != '' ) {
		require_once(PLUGIN_DIR . 'ref.inc.php');
		$params = plugin_ref_body(func_get_args());
		if (isset($params['_error']) && $params['_error'] != '') {
			$ret = $params['_error'];
			$dispattach = 1;
		} else {
			// start --- copy of plugin_ref_convert()
			if ((PLUGIN_ATTACHREF_WRAP_TABLE && ! $params['nowrap']) || $params['wrap']) {
				$margin = ($params['around'] ? '0px' : 'auto');
				$margin_align = ($params['_align'] == 'center') ? '' : ';margin-' . $params['_align'] . ':0px';
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
				$style = 'text-align:' . $params['_align'];
			}
			// wrapped "div"
			$ret = '<div class="img_margin" style="' . $style . '">' . $params['_body'] . "</div>\n";
			// final --- copy of plugin_ref_convert()
			$dispattach = 0;
		}
	}
	if ( $dispattach ) {
	    // Escape foreign value
	    $s_args = trim(join(",", $args));
	    if ( $button ){
		} else {
			$f_btn_text = preg_replace('/<[^<>]+>/','',$btn_text);
			$f_page = rawurlencode($vars['page']);
			$f_args = rawurlencode($s_args);
			$ret = <<<EOD
  $ret<a href="$script?plugin=attachref&amp;attachref_no=$attachref_no&amp;attachref_opt=$f_args&amp;refer=$f_page&amp;digest=$digest" title="$f_btn_text">$btn_text</a>
EOD;
		}
	}

	return $ret;
}

function plugin_attachref_inline()
{
	global $script, $vars, $digest;
	global $_attachref_messages;
#	static $numbers = array();
#	static $no_flag = 0;

#	if (!array_key_exists($vars['page'],$numbers))
#	{
#		$numbers[$vars['page']] = 0;
#	}
#	$attachref_no = $numbers[$vars['page']]++;

	$ret = '';
	$dispattach = 1;

	$extra_options = array();
	$args = func_get_args();
	$btn_text = array_pop($args);
	$btn_text = $btn_text ? $btn_text : $_attachref_messages['btn_submit'];
	$options = plugin_attachref_options($extra_options, $args);
	$button = $extra_options['button'];
	$attachref_no = $extra_options['refnum'];
	$btn_text .= $extra_options['text'];
#	$button = 0;

#	$args = func_get_args();
#   $btn_text = array_pop($args);
#   $btn_text = $btn_text ? $btn_text : $_attachref_messages['btn_submit'];

#   $options = array();
#   foreach ( $args as $opt ){
#	    if ( $opt === 'button' ){
#	        $button = 1;
#	    }
#	    else if ( $opt === 'number' ){
#		$no_flag = 1;
#	    }
#	    else if ( $opt === 'nonumber' ){
#		$no_flag = 0;
#	    }
#	    else {
#	        array_push($options, $opt);
#	    }
#	}
#   if ( $no_flag == 1 ) $btn_text .= "[$attachref_no]";
	$args = $options;
	if (count($args) && $args[0] != '') {
		require_once(PLUGIN_DIR . 'ref.inc.php');
	    $params = plugin_ref_body($args, $vars['page']);
	    if ($params['_error'] != '') {
			$ret = $params['_error'];
			$dispattach = 1;
	    } else {
			$ret = $params['_body'];
			$dispattach = 0;
	    }
	}

	if ($dispattach) {
	    // Escape foreign value
	    $s_args = trim(join(",", $args));
	    if ($button) {
			$s_args .= ',button';
			$f_page = htmlspecialchars($vars['page']);
			$f_args = htmlspecialchars($s_args);
			$ret = <<<EOD
  <form action="$script" method="post">
  <div>
  <input type="hidden" name="encode_hint" value="ぷ" />
  <input type="hidden" name="attachref_no" value="$attachref_no" />
  <input type="hidden" name="attachref_opt" value="$f_args" />
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="refer" value="$f_page" />
  $ret
  <input type="submit" value="$btn_text" />
  </div>
  </form>
EOD;
	    } else {
			$f_btn_text = preg_replace('/<[^<>]+>/', '', $btn_text);
			$f_page = rawurlencode($vars['page']);
			$f_args = rawurlencode($s_args);
			$ret = <<<EOD
  $ret<a href="$script?plugin=attachref&amp;attachref_no=$attachref_no&amp;attachref_opt=$f_args&amp;refer=$f_page&amp;digest=$digest" title="$f_btn_text">$btn_text</a>
EOD;
	    }
	}
	return $ret;
}

function plugin_attachref_action()
{
	global $script,$vars;
	global $_attachref_messages;
	global $pkwk_dtd;

	$retval['msg'] = $_attachref_messages['msg_title'];
	$retval['body'] = '';

	if (isset($_FILES['attach_file']) && isset($vars['refer']) && is_page($vars['refer'])) {
		$file = $_FILES['attach_file'];
		$attachname = $file['name'];
		$filename = preg_replace('/\..+$/','', $attachname,1);

		// If exist file, add a name '_0', '_1', ...
		$count = '_0';
		while (file_exists(UPLOAD_DIR .encode($vars['refer']).'_'.encode($attachname)))
		{
			$attachname = preg_replace('/^[^\.]+/',$filename.$count++,$file['name']);
		}
		
		$file['name'] = $attachname;
		
		require_once(PLUGIN_DIR.'attach.inc.php');
		if (!exist_plugin('attach') or !function_exists('attach_upload'))
		{
			return array('msg'=>'attach.inc.php not found or not correct version.');
		}
		$attach_filename = attachref_get_attach_filename($file);
		$pass = isset($vars['pass']) ? md5($vars['pass']) : NULL;
		$retval = attach_upload($file, $vars['refer'], $pass);
		if ($retval['result'] == TRUE) {
			$retval = attachref_insert_ref($attach_filename);
		}
	}
	else
	{
		$retval = attachref_showform();
		// XHTML 1.0 Transitional
		if (! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1)
			$pkwk_dtd = PKWK_DTD_XHTML_1_0_TRANSITIONAL;
	}
	return $retval;
}

function attachref_get_attach_filename(&$file)
{
	$type = get_mimeinfo($file['tmp_name']);
	$must_compress = attach_is_compress($type,PLUGIN_ATTACH_UNKNOWN_COMPRESS);

	if ($must_compress && is_uploaded_file($file['tmp_name'])) {
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'TGZ' && exist_plugin('dump')) {
			return $file['name'] . '.tgz';
		} else
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'GZ' && extension_loaded('zlib')) {
			return $file['name'] . '.gz';
		} else
		if (PLUGIN_ATTACH_COMPRESS_TYPE == 'ZIP' && class_exists('ZipArchive')) {
			return $file['name'] . '.zip';
		}
	}
	return $file['name'];
}

function attachref_insert_ref($filename)
{
	global $script, $vars, $now, $do_backup;
	global $_attachref_messages;

	$ret['msg'] = $_attachref_messages['msg_title'];

	$args = split(',', $vars['attachref_opt']);
	if (count($args)) {
	    $args[0] = './' . $filename;
	    $s_args = join(",", $args);
	} else {
	    $s_args = './' . $filename;
	}
	$msg = '&attachref('.$s_args.')';
	$msg_block = '#attachref('.$s_args.')';
	
	$refer = $vars['refer'];
	$digest = $vars['digest'];
	$postdata_old = get_source($refer);
	$thedigest = md5(join('',$postdata_old));

	$postdata = '';
	$attachref_ct = 0; // count of '#attachref'
	$attachref_no = $vars['attachref_no'];
	$skipflag = 0;
	$postdata_tmp = array();

	// Numbering inline plugin
	foreach ($postdata_old as $line)
	{
		if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
//			$postdata .= $line;
			array_push($postdata_tmp, $line);
			continue;
		}
		$ct = preg_match_all('/&attachref(?=[({;])/', $line, $out);
		if ( $ct ){
			for($i=0; $i < $ct; $i++){
				if ($attachref_ct++ == $attachref_no ){
					$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
					$skipflag = 1;
					break;
				} else {
					$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/','&___attachref$1$2___;',$line,1);
				}
			}
			$line = preg_replace('/&___attachref(\([^(){};]*\))?(\{[^{}]*\})?___;/','&attachref$1$2;',$line);
//			$postdata .= "|$ct|$attachref_ct|$attachref_no|$line";
	    }
//		$postdata .= $line;
		array_push($postdata_tmp, $line);
	}

	// Numbering block-type plugin
	foreach($postdata_tmp as $line) {
		if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
			$postdata .= $line;
			continue;
		}
		$ct = preg_match_all('/^#attachref/', $line, $out);
		if ( $ct ){
			for($i=0; $i < $ct; $i++){
				if ($attachref_ct++ == $attachref_no ){
					$line = preg_replace('/^#attachref(\([^(){};]*\))?(\{[^{}]*\})?/',$msg_block.'$2',$line,1);
					$skipflag = 1;
					break;
				} else {
					$line = preg_replace('/^#attachref(\([^(){};]*\))?(\{[^{}]*\})?/','#___attachref$1$2___',$line,1);
				}
			}
			$line = preg_replace('/^#___attachref(\([^(){};]*\))?(\{[^{}]*\})?___/','#attachref$1$2',$line);
//			$postdata .= "|$ct|$attachref_ct|$attachref_no|$line";
	    }
		$postdata .= $line;
	}

	// Detect conflict of update
	if ( $thedigest != $digest ) {
		$ret['msg']  = $_attachref_messages['msg_title_collided'];
		$ret['body'] = $_attachref_messages['msg_collided'];
	}

//	$postdata .= "<hr />$refer, " . join('/',array_keys($vars)) . ", " . join("/",array_values($vars)) . ", s_args=$s_args";
//	$ret['body'] = $postdata;

	page_write($vars['refer'], $postdata);

	return $ret;
}

// Show upload form
function attachref_showform()
{
	global $vars;
	global $_attachref_messages;

	$vars['page'] = $vars['refer'];
	$body = ini_get('file_uploads') ? attachref_form($vars['page']) : _('file_uploads disabled.');

	return array('msg'=>$_attachref_messages['msg_upload'], 'body'=>$body);
}

// Create html of upload form
function attachref_form($page)
{
	global $script, $vars;
	global $_attachref_messages;

	if (!(bool)ini_get('file_uploads')) return '';

	$s_page = htmlspecialchars($page);

	$f_digest = isset($vars['digest']) ? $vars['digest'] : '';
	$f_no = (isset($vars['attachref_no']) && is_numeric($vars['attachref_no'])) ?
		$vars['attachref_no'] + 0 : 0;

	$maxsize = ATTACHREF_MAX_FILESIZE;
	$msg_maxsize = sprintf($_attachref_messages['msg_maxsize'],number_format($maxsize/1000)."KB");

	$pass = '';
	if (ATTACHREF_PASSWORD_REQUIRE or ATTACHREF_UPLOAD_ADMIN_ONLY) {
		$title = $_attachref_messages[ATTACHREF_UPLOAD_ADMIN_ONLY ? 'msg_adminpass' : 'msg_password'];
		$pass = '<br />' . $title . ': <input type="password" name="pass" size="8" />';
	}
	return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="pcmd" value="post" />
  <input type="hidden" name="attachref_no" value="$f_no" />
  <input type="hidden" name="attachref_opt" value="{$vars['attachref_opt']}" />
  <input type="hidden" name="digest" value="$f_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="max_file_size" value="$maxsize" />
  <span class="small">
   $msg_maxsize
  </span><br />
  {$_attachref_messages['msg_file']}: <input type="file" name="attach_file" />
  $pass
  <input type="submit" value="{$_attachref_messages['btn_upload']}" />
 </div>
</form>
EOD;
}
?>
