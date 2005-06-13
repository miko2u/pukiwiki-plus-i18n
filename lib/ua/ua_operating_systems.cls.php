<?php
/*
 * Operating System
 *
 * @copyright   Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: ua_operating_systems.cls.php,v 0.1 2005/06/14 00:10:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * o 参考にしたコード(AWStats)
 *   http://awstats.sourceforge.net/
 *   Copyright (C) 2000-2005 - Laurent Destailleur - eldy@users.sourceforge.net
 *   awstats-6.5/wwwroot/cgi-bin/lib/operating_systems.pm
 */

class ua_operating_systems
{
	var $OSHashID = array(
		# Windows OS family
		array('windows[_+ ]?2005',		'winlong'),	// winlong.png
		array('windows[_+ ]nt[_+ ]6\.0',	'winlong'),	
		array('windows[_+ ]?2003',		'win2003'),	// win2003.png
		array('windows[_+ ]nt[_+ ]5\.2',	'win2003'),	
		array('windows[_+ ]xp',			'winxp'),	// winxp.png
		array('windows[_+ ]nt[_+ ]5\.1',	'winxp'),	
		array('windows[_+ ]me',			'winme'),	// winme.png
		array('win[_+ ]9x',			'winme'),	
		array('windows[_+ ]?2000',		'win2000'),	// win2000.png
		array('windows[_+ ]nt[_+ ]5',		'win2000'),	
		array('winnt',				'winnt'),	// winnt.png
		array('windows[_+ \-]?nt',		'winnt'),	
		array('win32',				'winnt'),	
		array('win(.*)98',			'win98'),	// win98.png
		array('win(.*)95',			'win95'),	// win95.png
		array('win(.*)16',			'win16'),	// win16.png
		array('windows[_+ ]3',			'win16'),	
		array('win(.*)ce',			'wince'),	// wince.png
		# Macintosh OS family
		array('mac[_+ ]os[_+ ]x',		'macosx'),	// macosx.png
		array('mac[_+ ]?p',			'macintosh'),	// macintosh.png
		array('mac[_+ ]68',			'macintosh'),	
		array('macweb',				'macintosh'),	
		array('macintosh',			'macintosh'),	
		# Unix like OS
		array('linux',				'linux'),	// linux.png
		array('aix',				'aix'),		// aix.png
		array('sunos',				'sunos'),	// sunos.png
		array('irix',				'irix'),	// irix.png
		array('osf',				'osf'),		// osf.png
		array('hp-ux',				'hpux'),	// hpux.png
		array('netbsd',				'netbsd'),	// netbsd.png
		array('bsdi',				'bsdi'),	// bsdi.png
		array('freebsd',			'freebsd'),	// freebsd.png
		array('openbsd',			'openbsd'),	// openbsd.png
		array('gnu',				'gnu'),		// gnu.png
		array('unix',				'unix'),	// unix.png
		array('x11',				'unix'),	
		# Other famous OS
		array('beos',				'beos'),	// beos.png
		array('os/2',				'os2'),		// os2.png
		array('amiga',				'amigaos'),	// amigaos.png
		array('atari',				'atari'),	// atari.png
		array('vms',				'vms'),		// vms.png
		# Miscellanous OS
		array('cp/m',				'cpm'),		// cpm.png
		array('crayos',				'crayos'),	
		array('dreamcast',			'dreamcast'),	// dreamcast.png
		array('risc[_+ ]?os',			'riscos'),	// riscos.png
		array('symbian',			'symbian'),	// symbian.png
		array('webtv',				'webtv'),	// webtv.png
	);

	function get_icon($ua)
	{
		foreach($this->OSHashID as $x) {
			$pat = "'".$x[0]."'si";
			if (preg_match($pat,$ua,$regs)) {
				return $x[1];
			}
		}
		return '';
	}
}

?>
