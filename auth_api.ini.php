<?php

$auth_api = array(
        // Basic or Digest
        'plus'                  => array(
                'use'           => 0,
                'displayname'   => 'Normal',
        ),
        // TypeKey
        'typekey'               => array(
                'use'           => 0,
                'site_token'    => '',
                'need_email'    => 0,
        ),
        // Hatena
        'hatena'                => array(
                'use'           => 0,
                'api_key'       => '',
                'sec_key'       => '',
        ),
        // JugemKey
        'jugemkey'              => array(
                'use'           => 0,
                'api_key'       => '',
                'sec_key'       => '',
        ),
	// RemoteIP
        'remoteip'              => array(
                'use'           => 0,
                'hidden'        => 1,
        ),
        // livedoor Auth
        'livedoor'              => array(
                'use'           => 0,
                'app_key'       => '',
                'sec_key'       => '',
        ),
	// OpenID
	'openid'		=> array(
		'use'		=> 0,
	),
	// mixi
	'mixi'			=> array(
		'use'		=> 0,
	),
	// QueryStringAuth
	'querystringauth'	=> array(
		'use'		=> 0,
		'hidden'	=> 1,
	),

);

?>
