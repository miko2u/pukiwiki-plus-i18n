<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: mail.php,v 1.7.1 2005/06/09 15:16:41 miko Exp $
// Copyright (C)
//   2005      PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
//   2003      Originally written by upk
// License: GPL v2 or (at your option) any later version
//
// E-mail related functions

// Send a mail to the administrator
function pkwk_mail_notify($subject, $message, $footer = array())
{
	global $smtp_server, $smtp_auth, $notify_to, $notify_from, $notify_header;
	static $_to, $_headers, $_after_pop;

	// Init and lock
	if (! isset($_to)) {
		if (! PKWK_OPTIMISE) {
			// Validation check
			$func = 'pkwk_mail_notify(): ';
			$mail_regex   = '/[^@]+@[^@]{1,}\.[^@]{2,}/';
			if (! preg_match($mail_regex, $notify_to))
				return false;
//@plus-comment	die($func . 'Invalid $notify_to');
			if (! preg_match($mail_regex, $notify_from))
				return false;
//@plus-comment	die($func . 'Invalid $notify_from');
			if ($notify_header != '') {
				$header_regex = "/\A(?:\r\n|\r|\n)|\r\n\r\n/";
				if (preg_match($header_regex, $notify_header))
					return false;
//@plus-comment		die($func . 'Invalid $notify_header');
				if (preg_match('/^From:/im', $notify_header))
					return false;
//@plus-comment		die($func . 'Redundant \'From:\' in $notify_header');
			}
		}

		$_to      = $notify_to;
		$_headers =
			'X-Mailer: PukiWiki/' . S_VERSION .
			' PHP/' . phpversion() . "\r\n" .
			'From: ' . $notify_from;
			
		// Additional header(s) by admin
		if ($notify_header != '') $_headers .= "\r\n" . $notify_header;

		$_after_pop = $smtp_auth;
	}

	if ($subject == '' || ($message == '' && empty($footer))) return FALSE;

	// Subject:
	if (isset($footer['PAGE'])) $subject = str_replace('$page', $footer['PAGE'], $subject);

	// Footer
	if (isset($footer['REMOTE_ADDR'])) $footer['REMOTE_ADDR'] = & $_SERVER['REMOTE_ADDR'];
	if (isset($footer['USER_AGENT']))
		$footer['USER_AGENT']  = '(' . UA_PROFILE . ') ' . UA_NAME . '/' . UA_VERS;
	if (! empty($footer)) {
		$_footer = '';
		if ($message != '') $_footer = "\n" . str_repeat('-', 30) . "\n";
		foreach($footer as $key => $value)
			$_footer .= $key . ': ' . $value . "\n";
		$message .= $_footer;
	}

	// Wait POP/APOP auth completion
	if ($_after_pop) {
		$result = pop_before_smtp();
//		if ($result !== TRUE) die($result);
		if ($result !== TRUE) return false;
	}

	ini_set('SMTP', $smtp_server);
	mb_language(LANG);
	if ($_headers == '') {
		return mb_send_mail($_to, $subject, $message);
	} else {
		return mb_send_mail($_to, $subject, $message, $_headers);
	}
}

// APOP/POP Before SMTP
function pop_before_smtp($pop_userid = '', $pop_passwd = '',
	$pop_server = 'localhost', $pop_port = 110)
{
	$pop_auth_use_apop = TRUE;	// Always try APOP, by default
	$must_use_apop     = FALSE;	// Always try POP for APOP-disabled server
	if (isset($GLOBALS['pop_auth_use_apop'])) {
		// Force APOP only, or POP only
		$pop_auth_use_apop = $must_use_apop = $GLOBALS['pop_auth_use_apop'];
	}

	// Compat: GLOBALS > function arguments
	foreach(array('pop_userid', 'pop_passwd', 'pop_server', 'pop_port') as $global) {
		if(isset($GLOBALS[$global]) && $GLOBALS[$global] !== '')
			$$global = $GLOBALS[$global];
	}

	// Check
	$die = '';
	foreach(array('pop_userid', 'pop_server', 'pop_port') as $global)
		if($$global == '') $die .= 'pop_before_smtp(): $' . $global . ' seems blank' . "\n";
	if ($die) return ($die);

	// Connect
	$errno = 0; $errstr = '';
	$fp = @fsockopen($pop_server, $pop_port, $errno, $errstr, 30);
	if (! $fp) return ('pop_before_smtp(): ' . $errstr . ' (' . $errno . ')');

	// Greeting message from server, may include <challenge-string> of APOP
	$message = fgets($fp, 1024); // 512byte max
	if (! preg_match('/^\+OK/', $message)) {
		fclose($fp);
		return ('pop_before_smtp(): Greeting message seems invalid');
	}

	$challenge = array();
	if ($pop_auth_use_apop &&
	   (preg_match('/<.*>/', $message, $challenge) || $must_use_apop)) {
		$method = 'APOP'; // APOP auth
		if (! isset($challenge[0])) {
			$response = md5(time()); // Someting worthless but variable
		} else {
			$response = md5($challenge[0] . $pop_passwd);
		}
		fputs($fp, 'APOP ' . $pop_userid . ' ' . $response . "\r\n");
	} else {
		$method = 'POP'; // POP auth
		fputs($fp, 'USER ' . $pop_userid . "\r\n");
		$message = fgets($fp, 1024); // 512byte max
		if (! preg_match('/^\+OK/', $message)) {
			fclose($fp);
			return ('pop_before_smtp(): USER seems invalid');
		}
		fputs($fp, 'PASS ' . $pop_passwd . "\r\n");
	}

	$result = fgets($fp, 1024); // 512byte max, auth result
	$auth   = preg_match('/^\+OK/', $result);

	if ($auth) {
		fputs($fp, 'STAT' . "\r\n"); // STAT, trigger SMTP relay!
		$message = fgets($fp, 1024); // 512byte max
	}

	// Disconnect anyway
	fputs($fp, 'QUIT' . "\r\n");
	$message = fgets($fp, 1024); // 512byte max, last '+OK'
	fclose($fp);

	if (! $auth) {
		return ('pop_before_smtp(): ' . $method . ' authentication failed');
	} else {
		return TRUE;	// Success
	}
}
?>
