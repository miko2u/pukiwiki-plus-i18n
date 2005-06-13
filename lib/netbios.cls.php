<?php
/**
 * RFC 1002
 * Protocol standard for a NetBIOS service
 * on a TCP/UDP transport:
 * Detailed specifications
 *
 * @copyright	Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: netbios.cls.php,v 0.2 2005/05/27 01:07:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * NetBIOS 全般の処理
 * @abstract
 */
class netbios
{
	var $data;
	var $info , $domain, $computername, $username;
	var $macaddress;
	var $netbios_name = array(
		//     CD   PC  Name
		array('03', 0, 'Messenger Service'),
		array('06', 1, 'RAS Server Service'),
		array('1B', 0, 'Domain Master Browser'),
		array('1D', 0, 'Master Browser'),
		array('1E', 0, 'Browser Service Elections'),
		array('21', 1, 'RAS Client Service'),
		array('22', 1, 'Microsoft Exchange Interchange(MSMail Connector)'),
		array('23', 1, 'Microsoft Exchange Store'),
		array('24', 1, 'Microsoft Exchange Directory'),
		array('2B', 0, 'Lotus Notes Server Service'),
		array('2F', 0, 'Lotus Notes'),
		array('30', 1, 'Modem Sharing Server Service'),
		array('31', 1, 'Modem Sharing Client Service'),
		array('33', 0, 'Lotus Notes'),
		array('43', 1, 'SMS Clients Remote Control'),
		array('44', 1, 'SMS Administrators Remote Control Tool'),
		array('45', 1, 'SMS Clients Remote Chat'),
		array('46', 1, 'SMS Clients Remote Transfer'),
		array('4C', 1, 'DEC Pathworks TCPIP service on Windows NT'),
		array('52', 1, 'DEC Pathworks TCPIP service on Windows NT'),
		array('6A', 1, 'Microsoft Exchange IMC'),
		array('87', 1, 'Microsoft Exchange MTA'),
		array('BE', 1, 'Network Monitor Agent'),
		array('BF', 1, 'Network Monitor Application'),
	);

	function netbios($ip)
	{
		$this->info = array();
		$this->domain = '';
		$this->computername = '';
		$this->username = '';
	
		// スペシャルパケットの送信
		$fp = fsockopen('udp://'.$ip, 137);
		fwrite($fp, "\x80b\0\0\0\1\0\0\0\0\0\0 CKAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\0\0!\0\1");
	
		// 2秒待ち、戻りのパケットを取得
		socket_set_timeout($fp, 2);
		$this->data = fread($fp, 1024);
	
		if (!strlen($this->data)) return; // length zero
	
		// NetBIOS番号の取得
		$nbrec = ord($this->data[56]);

		for($i = 0; $i < $nbrec; $i++) {
			$offset = 18 * $i;
			$this->info[] = array(
				sprintf("%02X",ord($this->data[72 + $offset])),
				trim(substr($this->data, 57 + $offset, 15)),
				substr(sprintf("%08b",ord($this->data[73 + $offset])),0,1),
				);
		}

		$this->get_domain();
		$this->get_computername();
		$this->get_username();
		$this->get_macaddress();

		return;
	}

	function get_macaddress()
	{
		$this->macaddress = '';
		$mac = 57 + ( ord($this->data[56]) * 18);
		for($i = 0; $i < 6; $i++) {
			if (!empty($this->macaddress)) $this->macaddress .= "-";
			$this->macaddress .= sprintf("%02X", ord($this->data[($i+$mac)]));
		}
	}

	/**
	 * ドメイン名取得
	 */
	function get_domain()
	{
		if (!empty($this->domain)) return;
		foreach($this->info as $x)
		{
			switch ($x[0]) {
			case '00': // Domain Name
				if ($x[2]) {
					$this->domain = $x[1];
					return;
				}
				continue;
			case '1B': // Domain Master Browser
			case '1D': // Master Browser
			case '1E': // Browser Service Elections
				$this->domain = $x[1];
				return;
			case '1C': // Domain Controllers
				if ($x[1] != 'INet~Services') {
					$this->domain = $x[1];
					return;
				}
			}
		}
	}

	/**
	 * コンピュータ名取得 (前提としてドメイン名を取得済)
	 */
	function get_computername()
	{
		if (!empty($this->computername)) return;

		foreach($this->info as $x)
		{
			switch ($x[0]) {
			case '00':
				// Domain Name
				if ($x[2]) continue;
				// IS~computer name
				if (substr($x[1],0,3) == 'IS~') {
					$this->computername = substr($x[1],3);
					return;
				}
				// Workstation Service
				$this->computername = $x[1];
				return;
			case '01':
				if (substr($x[1],2,12) == '__MSBROWSE__') continue;
				$this->computername = $x[1];
				return;
			case '06':
			case '1F':
			case '21':
			case '22':
			case '23':
			case '24':
			case '2B':
			case '30':
			case '31':
			case '43':
			case '44':
			case '45':
			case '46':
			case '4C':
			case '52':
			case '6A':
			case '87':
			case 'BE':
			case 'BF':
				$this->computername = $x[1];
				return;
			// 03 での判定は止めておく
			// case '03': //Messenger Service
			case '20':
				if ($x[1] != 'Forte_\$ND800ZA') {
					$this->computername = $x[1];
					return;
				}
				continue;
			}
		}
	}

	/*
	 * ユーザ名取得
	 */
	function get_username()
	{
		if (!empty($this->username)) return;

		foreach($this->info as $x)
		{
			if ($x[0] != '03') continue;
			if ($this->computername == $x[1]) continue; // 設定不能
			$this->username = $x[1];
			return;
		}
	}

	/*
	 * コードから名前に変換
	 */
	function code2name($code,$val)
	{
		foreach($this->netbios_name as $x)
		{
			if ($code == $x[0]) return array($x[2],$val);
		}

		switch($code) {
		// Workstation Service
		// IIS
		// Domain Name
		case '00':
			if (substr($val,0,3) == 'IS~') return array('IIS',$val);
			if ($this->domain == $val) return array('Domain Name',$val);
			if (empty($this->computername)) $this->computername = $val;
			return array('Workstation Service',$val);
		// Messenger Service
		// Master Browser
		case '01':
			if (substr($val,2,12) == '__MSBROWSE__') return array('Master Browser','..__MSBROWSE__.');
			if (empty($this->computername)) $this->computername = $val;
			return array('Messenger Service',$val);
		// Domain Controllers
		// IIS
		case '1C':
			if ($val == 'INet~Services') return array('IIS',$val);
			return array('Domain Controllers',$val);
		// File Server Service
		// DCA IrmaLan Gateway Server Service
		case '20':
			if ($val == 'Forte_\$ND800ZA') return array('DCA IrmaLan Gateway Server Service',$val);
			return array('File Server Service',$val);
		default:
			return array($code,$val);
		}
	}

}

?>
