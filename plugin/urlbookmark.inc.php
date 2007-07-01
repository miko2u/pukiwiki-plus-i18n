<?php
// $Id$

/*
 * PukiWiki urlbookmark プラグイン
 * Copyright (C) 2003, Kazunori Mizushima <kazunori@mzsm.net>
 *
 * URL のブックマークを作るためのプラグインです。
 * URL を入力すれば、そのサイトのHTMLを読込みタイトルを自動的に取得します。
 * comment プラグインをベースに作ったので、comment プラグインと同じ引数が使えます。
 *
 * [使用例]
 * #urlbookmark
 * #urlbookmark(below)
 * #urlbookmark(nodate)
 * #urlbookmark(nodate,notitle)
 *
 * [引数]
 * 次の引数をカンマで区切って指定することができます。
 * below   入力フォームの下に追加されていきます。 
 * nodate  日付がつきません。 
 * notile  タイトルの入力項目が表示されません。
 */

/////////////////////////////////////////////////
// URLテキストエリアのカラム数
defined('URLBOOKMARK_URL_COLS') or define('URLBOOKMARK_URL_COLS',30);
/////////////////////////////////////////////////
// タイトルテキストエリアのカラム数
defined('URLBOOKMARK_TITLE_COLS') or define('URLBOOKMARK_TITLE_COLS',30);
/////////////////////////////////////////////////
// コメントのテキストエリアのカラム数
defined('URLBOOKMARK_COMMENT_COLS') or define('URLBOOKMARK_COMMENT_COLS',70);
/////////////////////////////////////////////////
// ブックマークの挿入フォーマット
defined('URLBOOKMARK_NAME_FORMAT') or define('URLBOOKMARK_NAME_FORMAT','$name');
defined('URLBOOKMARK_MSG_FORMAT') or define('URLBOOKMARK_MSG_FORMAT',' -- $msg');
defined('URLBOOKMARK_NOW_FORMAT') or define('URLBOOKMARK_NOW_FORMAT',' SIZE(10){$now}');
/////////////////////////////////////////////////
// ブックマークの挿入フォーマット(コメント内容)
defined('URLBOOKMARK_FORMAT') or define('URLBOOKMARK_FORMAT',"\x08NAME\x08 \x08MSG\x08 \x08NOW\x08");
/////////////////////////////////////////////////
// ブックマークを挿入する位置 1:欄の前 0:欄の後
defined('URLBOOKMARK_INS') or define('URLBOOKMARK_INS',1);
/////////////////////////////////////////////////
// ブックマークが投稿された場合、内容をメールで送る先
//define('URLBOOKMARK_MAIL',FALSE);

function plugin_urlbookmark_action()
{
	global $script,$vars,$post,$now;

	if( auth::check_role('readonly') ) die_message('PKWK_READONLY prohibits editing');

	$post['msg'] = preg_replace("/\n/",'',$post['msg']);

	$url = $post['url'];
	
	if ($url == '') {
		return array('msg'=>'','body'=>'');
	}
	
	$head = '';
	if (preg_match('/^(-{1,2})(.*)/',$post['msg'],$match))
	{
		$head = $match[1];
		$post['msg'] = $match[2];
	}

	$title = $post['title'];
	if ($title == '')
	{
		// try to get the title from the site
		$title = plugin_urlbookmark_get_title($url);
	}
	

	if ($title == '')
	{
		$_name = str_replace('$name',$url,URLBOOKMARK_NAME_FORMAT);
	}
	else
	{
		$patterns = array ("/:/", "/\[/", "/\]/");
		$replace  = array (" ", "(", ")");
		$title = preg_replace($patterns, $replace,$title);
		$_name = str_replace('$name','[['.$title.":".$url.']]',URLBOOKMARK_NAME_FORMAT);
	}

	$_msg  =                                 str_replace('$msg', $post['msg'], URLBOOKMARK_MSG_FORMAT);
	$_now  = ($post['nodate'] == '1') ? '' : str_replace('$now', $now,         URLBOOKMARK_NOW_FORMAT);
	
	$urlbookmark = str_replace("\x08MSG\x08", $_msg, URLBOOKMARK_FORMAT);
	$urlbookmark = str_replace("\x08NAME\x08",$_name,$urlbookmark);
	$urlbookmark = str_replace("\x08NOW\x08", $_now, $urlbookmark);
	$urlbookmark = $head.$urlbookmark;
	
	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$urlbookmark_no = 0;
	$urlbookmark_ins = ($post['above'] == '1');
	
	foreach ($postdata_old as $line)
	{
		if (!$urlbookmark_ins)
		{
			$postdata .= $line;
		}
		if (preg_match('/^#urlbookmark/',$line) and $urlbookmark_no++ == $post['urlbookmark_no'])
		{
			$postdata = rtrim($postdata)."\n-$urlbookmark\n";
			if ($urlbookmark_ins)
			{
				$postdata .= "\n";
			}
		}
		if ($urlbookmark_ins)
		{
			$postdata .= $line;
		}
	}
	
	$title = _(" $1 was updated");
	$body = '';
	if (md5(@join('',get_source($post['refer']))) != $post['digest'])
	{
		$title = _("On updating  $1, a collision has occurred.");
		$body  = _("It seems that someone has already updated this page while you were editing it.<br />") .
			 _("It is likely to be inserted in a different position though it bookmarked.<br />") .
			 make_pagelink($post['refer']);
	}
	
	page_write($post['refer'],$postdata);
	
	$retvars['msg'] = $title;
	$retvars['body'] = $body;
	
	$post['page'] = $vars['page'] = $post['refer'];
	
	return $retvars;
}

function plugin_urlbookmark_convert()
{
	global $script,$vars,$digest;
	static $numbers = array();

	if( auth::check_role('readonly') ) return '';
	
	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$urlbookmark_no = $numbers[$vars['page']]++;
	
	$options = func_num_args() ? func_get_args() : array();
	
	if (in_array('notitle',$options)) {
		$titletags = '';
	}
	else {
		$titletags = _("Title: ") . "<input type='text' name='title' size='".URLBOOKMARK_TITLE_COLS."' /><br/>\n";
	}
		
	$nodate = in_array('nodate',$options) ? '1' : '0';
	$above = in_array('above',$options) ? '1' : (in_array('below',$options) ? '0' : URLBOOKMARK_INS);
	
	$s_page = htmlspecialchars($vars['page']);
	$urlbookmark_cols = URLBOOKMARK_COMMENT_COLS;
	$url_cols = URLBOOKMARK_URL_COLS;

	$_msg_urlbookmark = _("Comment: ");
	$_btn_urlbookmark = _("Add URL");
	$_btn_url         = _("URL: ");

	$string = <<<EOD
<br />
<form action="$script" method="post">
 <div>
  <input type="hidden" name="urlbookmark_no" value="$urlbookmark_no" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="plugin" value="urlbookmark" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above" value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $_btn_url <input type="text" name="url" size="$url_cols" /><br/>
  $titletags
  $_msg_urlbookmark <input type="text" name="msg" size="$urlbookmark_cols" /><br/>
  <input type="submit" name="urlbookmark" value="$_btn_urlbookmark" />
 </div>
</form>
EOD;
	
	return $string;
}

function plugin_urlbookmark_get_title($url) {
	$str = '';
	$found_title = false;
	
	$data = http_request($url);
	if ($data['rc'] !== 200)
	{
		return '';
	}
	$buf = preg_replace("/(\r|\n)+/i", '', $data['data']);
	$buf = mb_convert_encoding($buf,SOURCE_ENCODING,"auto");

	$tmpary = array();
	preg_match('/<title(\s+[^>]+)*>(.*)<\/title\s*>/i', $buf, $tmpary);

	return trim($tmpary[2]);
}
?>
