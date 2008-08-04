<?php
/*
| 2 |ROLE_ADM          |サイト管理者    |
| 3 |ROLE_ADM_CONTENTS |コンテンツ管理者|
| 4 |ROLE_ENROLLEE     |登録者(会員)    |
*/

$auth_wkgrp_user = array(
	// ex. 'user_name' => array('role'=>ROLE_ADM, 'displayname'=>'ななし','group'=>'','home'=>''),
	'typekey'	=> array(
		// 'user_name1'	=> array('role'=>ROLE_ADM),
		// 'user_name2'	=> array('role'=>ROLE_ADM_CONTENTS),
	),
	'hatena'	=> array(
		// 'user_name1'	=> array('role'=>ROLE_ADM),
		// 'user_name2'	=> array('role'=>ROLE_ADM_CONTENTS),
	),
	'jugemkey'	=> array(
		// 'user_name1'	=> array('role'=>ROLE_ADM),
		// 'user_name2'	=> array('role'=>ROLE_ADM_CONTENTS),
	),
	'remoteip'	=> array(
		// 'user_name1' => array('role'=>ROLE_ADM),
		// 'user_name2' => array('role'=>ROLE_ADM_CONTENTS),
	),
	'livedoor'	=> array(
		// 'user_name1' => array('role'=>ROLE_ADM),
		// 'user_name2' => array('role'=>ROLE_ADM_CONTENTS),
	),
	'openid'	=> array(
		// openid_identity (openid.delegate)
		// 'http://profile.livedoor.com/YOURNAME/' => array('role'=>ROLE_ADM),
		// 'http://YOURNAME.openid.ne.jp'          => array('role'=>ROLE_ADM_CONTENTS),
		// 'http://YOURNAME.myopenid.com/'         => array('role'=>ROLE_ADM_CONTENTS),
	),
);

?>
