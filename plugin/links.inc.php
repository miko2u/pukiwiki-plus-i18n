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
			'title_update'  => _("Cash update"),
			'msg_adminpass' => _("Administrator password"),
			'btn_submit'    => _("Exec"),
			'msg_done'      => _("The update of cash was completed."),
			'msg_usage'     => 
_("* Content of processing\n") .
_(":Cash update|\n") .
_("All pages are scanned, whether on which page certain pages have been linked is investigated, and it records in the cache.\n\n") .
_("* CAUTION\n") .
_("It is likely to drive it for a few minutes in execution.") .
_("Please wait for a while after pushing the execution button.\n\n") .
_("* EXEC\n") .
_("Please input the Administrator password, and click the [Exec] button.\n")
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
		$body  = convert_html( sprintf($_links_messages['msg_usage']) );
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
