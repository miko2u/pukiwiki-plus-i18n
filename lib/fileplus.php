<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: fileplus.php,v 1.2.2 2009/04/17 20:29:00 upk Exp $
// Copyright (C)
//   2005-2006,2009 PukiWiki Plus! Team
// License: GPL v2 or (at your option) any later version
//
// File related functions - extra functions

// Read Ticket DB
function read_ticket($addr)
{
	$file = CACHE_DIR . 'ticket.dat';
	if (! file_exists($file))
	{
		return FALSE;
	}

	$ticket = FALSE;
	$fp = fopen($file, 'r') or die_message('Cannot open ticket.dat');
	set_file_buffer($fp, 0);
	@flock($fp, LOCK_EX);
	rewind($fp);
	while ($data = @fgets($fp, 8192))
	{
		$data = csv_explode("\t", $data);
		if ($data[0] == $addr)
		{
			$ticket = $data[1];
			break;
		}
	}
	@flock($fp, LOCK_UN);
	fclose($fp);

	return $ticket;
}

// Write Ticket DB
function write_ticket($addr, $ticket)
{
	$file = CACHE_DIR . 'ticket.dat';

	pkwk_touch_file($file);
	$fp = fopen($file, 'r+') or die_message('Cannot open ticket.dat');
	set_file_buffer($fp, 0);
	@flock($fp, LOCK_EX);
	$last = ignore_user_abort(1);
	rewind($fp);
	$result = array();
	while ($data = @fgets($fp, 8192))
	{
		$data = csv_explode("\t", $data);
		$result[$data[0]] = $data;
	}
	if (isset($result[$addr]))
	{
		$result[$addr][1] = $ticket;
	}
	else
	{
		$result[$addr] = array($addr, $ticket);
	}
	rewind($fp);
	foreach ($result as $line)
	{
       	$str = trim(implode("\t", $line));
       	if ($str != '') fwrite($fp, $str . "\n");
	}
	ignore_user_abort($last);
	@flock($fp, LOCK_UN);
	fclose($fp);
}

// Get Ticket
function get_ticket($newticket = FALSE)
{
	$addr = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'].','.$_SERVER['REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR'];

	if ($newticket !== FALSE)
	{
		$ticket = rtrim(base64_encode(sha1(mt_rand(), TRUE)), '=');
	}
	else
	{
		$ticket = read_ticket($addr);
	}

	write_ticket($addr, $ticket);

	return $ticket;
}

// Get EXIF data
function get_exif_data($file)
{
	if (!extension_loaded('exif')) { return FALSE; }
	if (!function_exists('exif_read_data')) { return FALSE; }
	$exif_rawdata = @exif_read_data($file);
	return $exif_rawdata;
}

function plus_readfile($filename)
{
	while (@ob_end_flush());
	if (($fp = fopen($filename,'rb')) === FALSE) return FALSE;
	while (!feof($fp))
	{
		echo fread($fp, 4096);
		flush();
	}
	fclose($fp);
}
