<?php
/**
 * auth_file.cls.php
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_file.cls.php,v 0.8 2006/08/07 00:44:00 upk Exp $
 *
 */

class auth_file
{
	var $auth_users, $file;
	var $exist, $write, $f_name;

	function auth_file($file,$name='auth_users')
	{
		$this->file = $file;
		$this->write = FALSE;
		$this->f_name = $name;

		if (file_exists($this->file)) {
			$this->exist = TRUE;
			include($this->file);
			$this->auth_users = $$name;
		} else {
			$this->exist = FALSE;
			$this->auth_users = array();
		}
	}

	function write_auth_file()
	{
		if (! $this->write) return;
		if ($this->auth_users == array()) return;

		$fp = fopen($this->file,'w');
		@flock($fp, LOCK_EX);
		fputs($fp, "<?php\n\$".$this->f_name." = array(\n");

		foreach($this->auth_users as $user=>$val) {
			fputs($fp, "\t'".$user.'\' => array(\''.$val[0].'\'');

			for ($i=1;$i<count($val);$i++){
				if (! empty($val[$i])) {
					fputs($fp, ','.$val[$i]);
				}
			}

			fputs($fp, "),\n");
		}

		fputs($fp, ");\n?>\n");
		@flock($fp, LOCK_UN);
		@fclose($fp);
	}

	function set_passwd($user,$passwd,$role='')
	{
		// 1:追加
		if (empty($this->auth_users[$user])) {
			$this->write = TRUE;
			$this->auth_users[$user][0] = $passwd;
			if ($role != '') {
				$this->auth_users[$user][1] = $role;
			}
			return 1;
		}

		$tmp_role = (empty($this->auth_users[$user][1])) ? '' : $this->auth_users[$user][1];

		// 0:変更なし
		if ($this->auth_users[$user][0] == $passwd && $tmp_role == $role) return 0;

		// 2:パスワード変更あり 3:変更あり
		$this->write = TRUE;
		$rc = ($this->auth_users[$user][0] != $passwd) ? 2 : 3;

		$this->auth_users[$user][0] = $passwd;
		$this->auth_users[$user][1] = $role;
		return $rc;
	}

	function get_data($user) 
	{
		if (empty($this->auth_users[$user])) {
			// scheme, salt, role
			return array('','','');
		}
		$role = (empty($this->auth_users[$user][1])) ? '' : $this->auth_users[$user][1];
		list($scheme,$salt) = auth::passwd_parse($this->auth_users[$user][0]);
		return array($scheme,$salt,$role);
	}
}

?>
