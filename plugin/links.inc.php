<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: links.inc.php,v 1.23 2005/02/27 09:43:12 henoheno Exp $
//
// Update link cache plugin

// Message setting
function plugin_links_init()
{
	$messages = array(
		'_links_messages'=>array(
			'title_update'  => '����å��幹��',
			'msg_adminpass' => '�����ԥѥ����',
			'btn_submit'    => '�¹�',
			'msg_done'      => '����å���ι�������λ���ޤ�����',
			'msg_usage'     => "
* ��������

:����å���򹹿�|
���ƤΥڡ����򥹥���󤷡�����ڡ������ɤΥڡ��������󥯤���Ƥ��뤫��Ĵ�����ơ�����å���˵�Ͽ���ޤ���

* ���
�¹ԤˤϿ�ʬ��������⤢��ޤ����¹ԥܥ���򲡤������ȡ����Ф餯���Ԥ�����������

* �¹�
�����ԥѥ���ɤ����Ϥ��ơ�[�¹�]�ܥ���򥯥�å����Ƥ���������
"
		)
	);
	set_plugin_messages($messages);
}

function plugin_links_action()
{
	global $script, $post, $vars, $foot_explain;
	global $_links_messages;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits this');

	$msg = $body = '';
	if (empty($vars['action']) || empty($post['adminpass']) || ! pkwk_login($post['adminpass'])) {
		$msg   = & $_links_messages['title_update'];
		$body  = convert_html($_links_messages['msg_usage']);
		$body .= <<<EOD
<form method="POST" action="$script">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="action" value="update" />
  <label for="_p_links_adminpass">{$_links_messages['msg_adminpass']}</label>
  <input type="password" name="adminpass" id="_p_links_adminpass" size="20" value="" />
  <input type="submit" value="{$_links_messages['btn_submit']}" />
 </div>
</form>
EOD;

	} else if ($vars['action'] == 'update') {
		links_init();
		$foot_explain = array(); // Exhaust footnotes
		$msg  = & $_links_messages['title_update'];
		$body = & $_links_messages['msg_done'    ];
	} else {
		$msg  = & $_links_messages['title_update'];
		$body = & $_links_messages['err_invalid' ];
	}
	return array('msg'=>$msg, 'body'=>$body);
}
?>
