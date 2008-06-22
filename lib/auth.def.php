<?php
/**
 * PukiWiki Plus! 認証処理
 *
 * @author	Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth.def.php,v 0.5 2008/06/21 23:13:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

defined('ROLE_GUEST')                or define('ROLE_GUEST', 0);
defined('ROLE_FORCE')                or define('ROLE_FORCE', 1);
defined('ROLE_ADM')                  or define('ROLE_ADM', 2);
defined('ROLE_ADM_CONTENTS')         or define('ROLE_ADM_CONTENTS', 3);
defined('ROLE_ADM_CONTENTS_TEMP')    or define('ROLE_ADM_CONTENTS_TEMP', 3.1);
defined('ROLE_ENROLLEE')             or define('ROLE_ENROLLEE', 4);
defined('ROLE_AUTH')                 or define('ROLE_AUTH', 5);
defined('ROLE_AUTH_TEMP')            or define('ROLE_AUTH_TEMP', 5.1);
defined('ROLE_AUTH_TYPEKEY')         or define('ROLE_AUTH_TYPEKEY', 5.2);
defined('ROLE_AUTH_HATENA')          or define('ROLE_AUTH_HATENA', 5.3);
defined('ROLE_AUTH_JUGEMKEY')        or define('ROLE_AUTH_JUGEMKEY', 5.4);
defined('ROLE_AUTH_REMOTEIP')        or define('ROLE_AUTH_REMOTEIP', 5.5);
defined('ROLE_AUTH_LIVEDOOR')        or define('ROLE_AUTH_LIVEDOOR', 5.6);
defined('ROLE_AUTH_OPENID')          or define('ROLE_AUTH_OPENID', 5.7);
defined('ROLE_AUTH_MIXI')            or define('ROLE_AUTH_MIXI', 5.8);
defined('UNAME_ADM_CONTENTS_TEMP')   or define('UNAME_ADM_CONTENTS_TEMP', 'admin');

// role level => login plugin name
$login_api = array(
	strval(ROLE_AUTH_TYPEKEY)	  => 'typekey',
	strval(ROLE_AUTH_HATENA)	  => 'hatena',
	strval(ROLE_AUTH_JUGEMKEY)	  => 'jugemkey',
	strval(ROLE_AUTH_REMOTEIP)        => 'remoteip',
	strval(ROLE_AUTH_LIVEDOOR)        => 'livedoor',
	strval(ROLE_AUTH_OPENID)          => 'openid',
	strval(ROLE_AUTH_MIXI)            => 'mixi',
);

?>
