<?php
/**
 * PukiWiki Plus! brokenlink Plugin
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: brokenlink.inc.php,v 0.1 2006/08/25 00:25:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */

function plugin_brokenlink_init()
{
	$messages = array(
		'_brokenlink_msg' => array(
			'msg_title'		=> _('Broken Link List'),
			'msg_all_ok'		=> _('All links are effective.'),
			'msg_param_error'	=> _('<p>The parameter is illegal.</p>'),
			'msg_not_access'	=> _('<p>Not authorized to access.</p>'),
			'msg_not_found_xbel'	=> _('<p>The xbel plugin is not found.</p>'),
		)
	);
	set_plugin_messages($messages);
}

function plugin_brokenlink_action()
{
	global $vars, $_brokenlink_msg;

	$retval = array('msg'=>$_brokenlink_msg['msg_title'], 'body'=>'');

	if (empty($vars['page'])) {
		$retval['body'] = $_brokenlink_msg['msg_param_error'];
		return $retval;
	}

	// ユーザ認証されていない
	$id = auth::check_auth();
	if (empty($id)) {
		$retval['body'] = $_brokenlink_msg['msg_not_access'];
		return $retval;
	}

	if (! exist_plugin('xbel')) {
		$retval['body'] = $_brokenlink_msg['msg_not_found_xbel'];
		return $retval;
	}

	$links = xbel::get_link_list($vars['page']);

	$data = '';
	foreach($links as $href=>$aname) {
		$rc = http_request($href, 'HEAD');
		switch ($rc['rc']) {
		case 200:
		case 401:
			continue;
		default:
			$data .= '-[['.$aname.'>'.$href.']] ('.$rc['rc'].")\n";
		}
	}

	if ($data == '') {
		$data = $_brokenlink_msg['msg_all_ok'];
	}

	$retval['body'] = convert_html($data);
	return $retval;
}

?>
