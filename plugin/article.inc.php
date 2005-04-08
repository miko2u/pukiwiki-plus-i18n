<?php
// $Id: article.inc.php,v 1.22.3 2005/04/08 07:01:56 miko Exp $
 /*

 PukiWiki BBS���ץ饰����

 CopyRight 2002 OKAWARA,Satoshi
 http://www.dml.co.jp/~kawara/pukiwiki/pukiwiki.php
 kawara@dml.co.jp

 ��å��������ѹ�����������LANGUAGE�ե�����˲������ͤ��ɲä��Ƥ��餴���Ѥ�������
	$_btn_name = '��̾��';
	$_btn_article = '���������';
	$_btn_subject = '��̾: ';

 ��$_btn_name��comment�ץ饰����Ǵ������ꤵ��Ƥ����礬����ޤ�

 ������Ƥμ�ư�᡼��ž����ǽ�򤴻��Ѥˤʤꤿ������
 -������ƤΥ᡼�뼫ư�ۿ�
 -������ƤΥ᡼�뼫ư�ۿ���
 ������ξ塢�����Ѥ���������

 */

define('PLUGIN_ARTICLE_COLS',	70); // �ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_ARTICLE_ROWS',	 5); // �ƥ����ȥ��ꥢ�ιԿ�
define('PLUGIN_ARTICLE_NAME_COLS',	24); // ̾���ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_ARTICLE_SUBJECT_COLS',	60); // ��̾�ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_ARTICLE_NAME_FORMAT',	'[[$name]]'); // ̾���������ե����ޥå�
define('PLUGIN_ARTICLE_SUBJECT_FORMAT',	'**$subject'); // ��̾�������ե����ޥå�

define('PLUGIN_ARTICLE_INS',	0); // ����������� 1:����� 0:��θ�
define('PLUGIN_ARTICLE_COMMENT',	1); // �񤭹��ߤβ��˰�ԥ����Ȥ������ 1:����� 0:����ʤ�
define('PLUGIN_ARTICLE_AUTO_BR',	1); // ���Ԥ�ưŪ�Ѵ� 1:���� 0:���ʤ�

define('PLUGIN_ARTICLE_MAIL_AUTO_SEND',	0); // ������ƤΥ᡼�뼫ư�ۿ� 1:���� 0:���ʤ�
define('PLUGIN_ARTICLE_MAIL_FROM',	''); // ������ƤΥ᡼���������������ԥ᡼�륢�ɥ쥹
define('PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX', "[someone's PukiWiki]"); // ������ƤΥ᡼������������̾

// ������ƤΥ᡼�뼫ư�ۿ���
global $_plugin_article_mailto;
$_plugin_article_mailto = array (
	''
);

function plugin_article_action()
{
	global $script, $post, $vars, $cols, $rows, $now;
//	global $_title_collided, $_msg_collided, $_title_updated;
	global $_plugin_article_mailto, $_no_subject, $_no_name;

$_title_collided   = _('On updating $1, a collision has occurred.');
$_title_updated    = _('$1 was updated');
$_msg_collided = _('It seems that someone has already updated this page while you were editing it.<br />
 + is placed at the beginning of a line that was newly added.<br />
 ! is placed at the beginning of a line that has possibly been updated.<br />
 Edit those lines, and submit again.');

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if ($post['msg'] == '')
		return array('msg'=>'','body'=>'');

	$name = ($post['name'] == '') ? $_no_name : $post['name'];
	$name = ($name == '') ? '' : str_replace('$name', $name, PLUGIN_ARTICLE_NAME_FORMAT);
	$subject = ($post['subject'] == '') ? $_no_subject : $post['subject'];
	$subject = ($subject == '') ? '' : str_replace('$subject', $subject, PLUGIN_ARTICLE_SUBJECT_FORMAT);
	$article  = $subject . "\n" . '>' . $name . ' (' . $now . ')~' . "\n" . '~' . "\n";

	$msg = rtrim($post['msg']);
	if (PLUGIN_ARTICLE_AUTO_BR) {
		//���Ԥμ�갷���Ϥ��ä�������ä�URL�������Ȥ��ϡ�
		//�����ȹԡ������Ѥ߹Ԥˤ�~��Ĥ��ʤ��褦�� arino
		$msg = join("\n", preg_replace('/^(?!\/\/)(?!\s)(.*)$/', '$1~', explode("\n", $msg)));
	}
	$article .= $msg . "\n\n" . '//';

	if (PLUGIN_ARTICLE_COMMENT) $article .= "\n\n" . '#comment' . "\n";

	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$article_no = 0;

	foreach($postdata_old as $line) {
		if (! PLUGIN_ARTICLE_INS) $postdata .= $line;
		if (preg_match('/^#article/i', $line)) {
			if ($article_no == $post['article_no'] && $post['msg'] != '')
				$postdata .= $article . "\n";
			$article_no++;
		}
		if (PLUGIN_ARTICLE_INS) $postdata .= $line;
	}

	$postdata_input = $article . "\n";
	$body = '';

	if (md5(@join('', get_source($post['refer']))) != $post['digest']) {
		$title = $_title_collided;

		$body = $_msg_collided . "\n";

		$s_refer    = htmlspecialchars($post['refer']);
		$s_digest   = htmlspecialchars($post['digest']);
		$s_postdata = htmlspecialchars($postdata_input);
		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata</textarea><br />
 </div>
</form>
EOD;

	} else {
		page_write($post['refer'], trim($postdata));

		// ������ƤΥ᡼�뼫ư����
		if (PLUGIN_ARTICLE_MAIL_AUTO_SEND) {
			$mailaddress = implode(',', $_plugin_article_mailto);
			$mailsubject = PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX . ' ' . str_replace('**', '', $subject);
			if ($post['name'])
				$mailsubject .= '/' . $post['name'];
			$mailsubject = mb_encode_mimeheader($mailsubject);

			$mailbody = $post['msg'];
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= _('Author: ') . $post['name'] . ' (' . $now . ')' . "\n";
			$mailbody .= _('Page: ') . $post['refer'] . "\n";
			$mailbody .= '�� URL: ' . $script . '?' . rawurlencode($post['refer']) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');

			$mailaddheader = 'From: ' . PLUGIN_ARTICLE_MAIL_FROM;

			mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
		}

		$title = $_title_updated;
	}
	$retvars['msg'] = $title;
	$retvars['body'] = $body;

	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];

	return $retvars;
}

function plugin_article_convert()
{
	global $script, $vars, $digest;
//	global $_btn_article, $_btn_name, $_btn_subject;
	static $numbers = array();

	$_btn_name    = _('Name: ');
	$_btn_article = _('Submit');
	$_btn_subject = _('Subject: ');

	if (PKWK_READONLY) return ''; // Show nothing

	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;

	$article_no = $numbers[$vars['page']]++;

	$helptags = edit_form_assistant();

	$s_page   = htmlspecialchars($vars['page']);
	$s_digest = htmlspecialchars($digest);
	$name_cols = PLUGIN_ARTICLE_NAME_COLS;
	$subject_cols = PLUGIN_ARTICLE_SUBJECT_COLS;
	$article_rows = PLUGIN_ARTICLE_ROWS;
	$article_cols = PLUGIN_ARTICLE_COLS;
	$string = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="article_no" value="$article_no" />
  <input type="hidden" name="plugin" value="article" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  $_btn_name <input type="text" name="name" size="$name_cols" /><br />
  $_btn_subject <input type="text" name="subject" size="$subject_cols" /><br />
  <textarea name="msg" rows="$article_rows" cols="$article_cols">\n</textarea><br />
  <input type="submit" name="article" value="$_btn_article" />
  $helptags
 </div>
</form>
EOD;

	return $string;
}
?>
