<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: links.inc.php,v 1.24.2 2007/04/08 10:29:24 upk Exp $
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
			'msg_usage1'	=> 
_("* Content of processing\n") .
_(":Cash update|\n") .
_("All pages are scanned, whether on which page certain pages have been linked is investigated, and it records in the cache.\n\n") .
_("* CAUTION\n") .
_("It is likely to drive it for a few minutes in execution.") .
_("Please wait for a while after pushing the execution button.\n\n"),
			'msg_usage2'	=> 
_("* EXEC\n") .
_("Please input the Administrator password, and click the [Exec] button.\n")
		),
	);
	set_plugin_messages($messages);
}

function plugin_links_action()
{
	global $script, $post, $vars, $foot_explain;
	global $_links_messages;

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits this');
	if (auth::check_role('readonly')) die_message( _("PKWK_READONLY prohibits this") );

	$admin_pass = (empty($post['adminpass'])) ? '' : $post['adminpass'];
	if ( isset($vars['menu']) && (! auth::check_role('role_adm_contents') || pkwk_login($admin_pass) )) {
		set_time_limit(0);
		links_init();
		$foot_explain = array(); // Exhaust footnotes
		$msg  = & $_links_messages['title_update'];
		$body = & $_links_messages['msg_done'    ];
		return array('msg'=>$msg, 'body'=>$body);
	}

	$msg   = & $_links_messages['title_update'];
	$body  = convert_html( sprintf($_links_messages['msg_usage1']) );
	$body .= <<<EOD
<form method="post" action="$script">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="menu" value="1" />
EOD;
	if (auth::check_role('role_adm_contents')) {
		$body .= convert_html( sprintf($_links_messages['msg_usage2']) );
		$body .= <<<EOD
  <label for="_p_links_adminpass">{$_links_messages['msg_adminpass']}</label>
  <input type="password" name="adminpass" id="_p_links_adminpass" size="20" value="" />
EOD;
	}
	$body .= <<<EOD
  <input type="submit" value="{$_links_messages['btn_submit']}" />
 </div>
</form>
EOD;

	return array('msg'=>$msg, 'body'=>$body);
}
?>
