<?php
/**
 *
 * PukiWiki - Yet another WikiWikiWeb clone.
 *
 * backup.php
 *
 * �Хå����åפ��������
 *
 * @package org.pukiwiki
 * @access  public
 * @author
 * @create
 * @version $Id: backup.php,v 1.9.1 2005/04/30 05:21:00 miko Exp $
 * Copyright (C)
 *   2005      PukiWiki Plus! Team
 *   2002-2005 PukiWiki Developers Team
 *   2001-2002 Originally written by yu-ji
 * License: GPL v2 or (at your option) any later version
 **/

/**
 * make_backup
 * �Хå����åפ��������
 *
 * @access    public
 * @param     String    $page        �ڡ���̾
 * @param     Boolean   $delete      TRUE:�Хå����åפ�������
 *
 * @return    Void
 */

function make_backup($page, $delete = FALSE)
{
	global $cycle, $maxage;
	global $do_backup, $del_backup;

	if (PKWK_READONLY || ! $do_backup) return;

	if ($del_backup && $delete) {
		_backup_delete($page);
		return;
	}

	if (! is_page($page)) return;

	$lastmod = _backup_get_filetime($page);
	if ($lastmod == 0 || UTIME - $lastmod > 60 * 60 * $cycle)
	{
		$backups = get_backup($page);
		$count   = count($backups) + 1;

		// ľ���1���ɲä���Τǡ�(������ - 1)��Ķ�������Ǥ�ΤƤ�
		if ($count > $maxage)
			array_splice($backups, 0, $count - $maxage);

		$strout = '';
		foreach($backups as $age=>$data) {
			// BugTrack/685 by UPK
			//$strout .= PKWK_SPLITTER . ' ' . $data['time'] . "\n"; // Splitter format
			$strout .= PKWK_SPLITTER . ' ' . $data['time'] . ' ' . $data['real'] . "\n"; // Splitter format
			$strout .= join('', $data['data']);
		}
		$strout = preg_replace("/([^\n])\n*$/", "$1\n", $strout);

		// Escape 'lines equal to PKWK_SPLITTER', by inserting a space
		$body = preg_replace('/^(' . preg_quote(PKWK_SPLITTER) . "\s\d+)$/", '$1 ', get_source($page));
		// BugTrack/685 by UPK
		// $body = PKWK_SPLITTER . ' ' . get_filetime($page) . "\n" . join('', $body);
		$body = PKWK_SPLITTER . ' ' . get_filetime($page) . ' ' . UTIME. "\n" . join('', $body);
		$body = preg_replace("/\n*$/", "\n", $body);

		$fp = _backup_fopen($page, 'wb')
			or die_message('cannot write file ' . htmlspecialchars($realfilename) .
			'<br />maybe permission is not writable or filename is too long');
		_backup_fputs($fp, $strout);
		_backup_fputs($fp, $body);
		_backup_fclose($fp);
	}
}

/**
 * get_backup
 * �Хå����åפ��������
 * $age = 0�ޤ��Ͼ�ά : ���ƤΥХå����åץǡ���������Ǽ�������
 * $age > 0           : ���ꤷ������ΥХå����åץǡ������������
 *
 * @access    public
 * @param     String    $page        �ڡ���̾
 * @param     Integer   $age         �Хå����åפ������ֹ� ��ά��������
 *
 * @return    String    �Хå����å�       ($age != 0)
 *            Array     �Хå����åפ����� ($age == 0)
 */
function get_backup($page, $age = 0)
{
	$lines = _backup_file($page);
	if (! is_array($lines)) return array();

	$_age = 0;
	$retvars = $match = array();
	$regex_splitter = '/^' . preg_quote(PKWK_SPLITTER) . '\s(\d+)$/';
	// BugTrack/685 by UPK
	$regex_splitter_new = '/^' . preg_quote(PKWK_SPLITTER) . '\s(\d+)\s(\d+)$/';
	foreach($lines as $line) {
		// BugTrack/685 by UPK
		// if (preg_match($regex_splitter, $line, $match)) {
		if (preg_match($regex_splitter, $line, $match) ||
		    preg_match($regex_splitter_new, $line, $match)) {
			++$_age;
			if ($age > 0 && $_age > $age)
				return $retvars[$age];

			// BugTrack/685 by UPK
			// $retvars[$_age] = array('time'=>$match[1], 'data'=>array());
			$now = (isset($match[2])) ? $match[2] : $match[1];
			$retvars[$_age] = array('time'=>$match[1], 'real'=>$now, 'data'=>array());
		} else {
			$retvars[$_age]['data'][] = $line;
		}
	}

	return $retvars;
}

/**
 * _backup_get_filename
 * �Хå����åץե�����̾���������
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    String    �Хå����åפΥե�����̾
 */
function _backup_get_filename($page)
{
	return BACKUP_DIR . encode($page) . BACKUP_EXT;
}

/**
 * _backup_file_exists
 * �Хå����åץե����뤬¸�ߤ��뤫
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    Boolean   TRUE:���� FALSE:�ʤ�
 */
function _backup_file_exists($page)
{
	return file_exists(_backup_get_filename($page));
}

/**
 * _backup_get_filetime
 * �Хå����åץե�����ι������������
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    Integer   �ե�����ι�������(GMT)
 */

function _backup_get_filetime($page)
{
	return _backup_file_exists($page) ?
		filemtime(_backup_get_filename($page)) - LOCALZONE : 0;
}

/**
 * _backup_delete
 * �Хå����åץե������������
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    Boolean   FALSE:����
 */
function _backup_delete($page)
{
	return unlink(_backup_get_filename($page));
}

/////////////////////////////////////////////////

if (extension_loaded('zlib')) {
	// �ե����륷���ƥ�ؿ�
	// zlib�ؿ������
	define('BACKUP_EXT', '.gz');

/**
 * _backup_fopen
 * �Хå����åץե�����򳫤�
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 * @param     String    $mode        �⡼��
 *
 * @return    Boolean   FALSE:����
 */
	function _backup_fopen($page, $mode)
	{
		return gzopen(_backup_get_filename($page), $mode);
	}

/**
 * _backup_fputs
 * �Хå����åץե�����˽񤭹���
 *
 * @access    private
 * @param     Integer   $zp          �ե�����ݥ���
 * @param     String    $str         ʸ����
 *
 * @return    Boolean   FALSE:���� ����¾:�񤭹�����Х��ȿ�
 */
	function _backup_fputs($zp, $str)
	{
		return gzputs($zp, $str);
	}

/**
 * _backup_fclose
 * �Хå����åץե�������Ĥ���
 *
 * @access    private
 * @param     Integer   $zp          �ե�����ݥ���
 *
 * @return    Boolean   FALSE:����
 */
	function _backup_fclose($zp)
	{
		return gzclose($zp);
	}

/**
 * _backup_file
 * �Хå����åץե���������Ƥ��������
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    Array     �ե����������
 */
	function _backup_file($page)
	{
		return _backup_file_exists($page) ?
			gzfile(_backup_get_filename($page)) :
			array();
	}
}
/////////////////////////////////////////////////
else
{
	// �ե����륷���ƥ�ؿ�
	define('BACKUP_EXT', '.txt');

/**
 * _backup_fopen
 * �Хå����åץե�����򳫤�
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 * @param     String    $mode        �⡼��
 *
 * @return    Boolean   FALSE:����
 */
	function _backup_fopen($page, $mode)
	{
		return fopen(_backup_get_filename($page), $mode);
	}

/**
 * _backup_fputs
 * �Хå����åץե�����˽񤭹���
 *
 * @access    private
 * @param     Integer   $zp          �ե�����ݥ���
 * @param     String    $str         ʸ����
 *
 * @return    Boolean   FALSE:���� ����¾:�񤭹�����Х��ȿ�
 */
	function _backup_fputs($zp, $str)
	{
		return fputs($zp, $str);
	}

/**
 * _backup_fclose
 * �Хå����åץե�������Ĥ���
 *
 * @access    private
 * @param     Integer   $zp          �ե�����ݥ���
 *
 * @return    Boolean   FALSE:����
 */
	function _backup_fclose($zp)
	{
		return fclose($zp);
	}

/**
 * _backup_file
 * �Хå����åץե���������Ƥ��������
 *
 * @access    private
 * @param     String    $page        �ڡ���̾
 *
 * @return    Array     �ե����������
 */
	function _backup_file($page)
	{
		return _backup_file_exists($page) ?
			file(_backup_get_filename($page)) :
			array();
	}
}
?>
