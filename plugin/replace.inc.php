<?php
//////////////////////////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: replace.inc.php,v 1.1.4 2004/09/22 12:25:20 miko Exp $
//
// �ե�����̾������ɽ��
// cmd=replace

// ��뤷�Ƥ���ڡ�����ʸ�����ִ����оݤȤ���
define(REPLACE_IGNORE_FREEZE, TRUE);

function plugin_replace_init()
{
	global $_replace_msg;

	$messages = array(
		'_replace_msg' => array(
			'msg_input_pass'         => _('Please input the retrieval character string, the substitution character string, and the password for the Administrator.'),
			'msg_input_search_word'  => _('Retrieval character string:'),
			'msg_input_replace_word' => _('Substitution character string:'),
			'btn_exec'               => _('Exec'),
			'msg_warn_pass'          => _('SECURITY ERROR:') .
						    _('It remains as the Administrator password distributes it.') .
						    _('Please change the password.'),
			'msg_no_pass'            => _('The password is wrong.'),
			'msg_no_search'          => _('The retrieval character string to substitute it is empty.'),
			'msg_H0_replace'         => _('All page character string substitution'),
			'msg_no_replaced'        => _('There is no substituted character string.'),
			'msg_replaced'           => _('The following pages were substituted.'),
		)
	);
	set_plugin_messages($messages);
}

function plugin_replace_action()
{
	global $post, $adminpass, $cycle, $cantedit;
	global $_replace_msg;

	$pass    = isset($post['pass'])    ? $post['pass']    : '__nopass__';
	$search  = isset($post['search'])  ? $post['search']  : NULL;
	$replace = isset($post['replace']) ? $post['replace'] : NULL;

	// �ѥ���ɤȸ���ʸ���󤬤ʤ����ִ��ϤǤ��ʤ���
	if ($search == '' || md5($pass) != $adminpass || $pass == 'pass') {
		$vars['cmd'] = 'read';
		return replace_adm($pass,$search);
	}

	// �ѥ���ɤ���äƤ��餤�褤���ִ�
	$pages = get_existpages();
	$replaced_pages = array();
	foreach ($pages as $page)
	{
		if (REPLACE_IGNORE_FREEZE) {
			$editable = (
				! in_array($page, $cantedit)
			);
		} else {
			$editable = (
				! is_freeze($page) and
				! in_array($page, $cantedit)
                	);
		}
		if ($editable) {
			// �ѥ���ɰ���
			$postdata = '';
			$postdata_old = get_source($page);
			foreach ($postdata_old as $line)
			{
				// ������ɤ��ִ�
				$line = str_replace($search,$replace,$line);
				$postdata .= $line;
			}
			if ($postdata != join('',$postdata_old)) {
				$cycle = 0;
				set_time_limit(30);
				page_write($page,$postdata);
				$replaced_pages[] = htmlspecialchars($page);
			}
		}
	}
	$vars['cmd'] = 'read';
	if ( count($replaced_pages) == 0 ) {
		return array(
			'msg'  => 'No search data.',
			'body' => '<p>' . $_replace_msg['msg_no_replaced'] . '</p>'
		);
	}
	return array(
		'msg'  => 'Replaced.',
		'body' => '<p>' . $_replace_msg['msg_replaced'] . "</p>\n<p>" . join("<br />\n", $replaced_pages) . '</p>'
	);
}

// �ִ�ʸ�������ϲ���
function replace_adm($pass,$search)
{
	global $_replace_msg;
	global $script;

	$label1 = $_replace_msg['msg_input_search_word'];
	$label2 = $_replace_msg['msg_input_replace_word'];
	$msg = $_replace_msg['msg_input_pass'];
	$btn = $_replace_msg['btn_exec'];
	$body = "";

	if ($pass == 'pass') {
		$body .= "<p><strong>".$_replace_msg['msg_warn_pass']."</strong></p>\n";
	} elseif ($pass != '__nopass__') {
		$body .= "<p><strong>".$_replace_msg['msg_no_pass']."</strong></p>\n";
	}
	if ($search === '') {
		$body .= "<p><strong>".$_replace_msg['msg_no_search']."</strong></p>\n";
	}

	$body .= <<<EOD
<p>$msg</p>
<form action="$script" method="post">
 <div>
  <input type="hidden" name="cmd" value="replace" />
  $label1<br />
  <input type="text" name="search" size="24" /> <br />
  $label2<br />
  <input type="text" name="replace" size="24" /> <br />
  Password<br />
  <input type="password" name="pass" size="12" /> <br />
  <input type="submit" name="ok" value="$btn" />
 </div>
</form>
EOD;

	return array('msg'=>$_replace_msg['msg_H0_replace'],'body'=>$body);
}
?>
