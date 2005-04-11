<?php
// $Id: dump.inc.php,v 1.36.1 2005/03/27 12:16:50 upk Exp $
//
// Remote dump / restore plugin
// Originated as tarfile.inc.php by teanan / Interfair Laboratory 2004.

/////////////////////////////////////////////////
// User defines

// Allow using resture function
define('PLUGIN_DUMP_ALLOW_RESTORE', FALSE); // FALSE, TRUE

// �ڡ���̾��ǥ��쥯�ȥ깽¤���Ѵ�����ݤ�ʸ�������� (for mbstring)
define('PLUGIN_DUMP_FILENAME_ENCORDING', 'SJIS');

// ���祢�åץ��ɥ�����
define('PLUGIN_DUMP_MAX_FILESIZE', 1024); // Kbyte

/////////////////////////////////////////////////
// Internal defines

// Action
define('PLUGIN_DUMP_DUMP',    'dump');    // Dump & download
define('PLUGIN_DUMP_RESTORE', 'restore'); // Upload & restore

global $_STORAGE;

// DATA_DIR (wiki/*.txt)
$_STORAGE['DATA_DIR']['add_filter']     = '^[0-9A-F]+\.txt';
$_STORAGE['DATA_DIR']['extract_filter'] = '^' . preg_quote(DATA_DIR, '/')   . '((?:[0-9A-F])+)(\.txt){0,1}';

// UPLOAD_DIR (attach/*)
$_STORAGE['UPLOAD_DIR']['add_filter']     = '^[0-9A-F_]+';
$_STORAGE['UPLOAD_DIR']['extract_filter'] = '^' . preg_quote(UPLOAD_DIR, '/') . '((?:[0-9A-F]{2})+)_((?:[0-9A-F])+)';

// BACKUP_DIR (backup/*.gz)
$_STORAGE['BACKUP_DIR']['add_filter']     = '^[0-9A-F]+\.gz';
$_STORAGE['BACKUP_DIR']['extract_filter'] =  '^' . preg_quote(BACKUP_DIR, '/') . '((?:[0-9A-F])+)(\.gz){0,1}';


/////////////////////////////////////////////////
// �ץ饰��������
function plugin_dump_action()
{
	global $vars;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits this');

	$pass = isset($_POST['pass']) ? $_POST['pass'] : NULL;
	$act  = isset($vars['act'])   ? $vars['act']   : NULL;

	$body = '';

	if ($pass !== NULL) {
		if (! pkwk_login($pass)) {
			$body = "<p><strong>" . _("The password is different.") . "</strong></p>\n";
		} else {
			switch($act){
			case PLUGIN_DUMP_DUMP:
				$body = plugin_dump_download();
				break;
			case PLUGIN_DUMP_RESTORE:
				$retcode = plugin_dump_upload();
				if ($retcode['code'] == TRUE) {
					$msg = _("Up-loading was completed.");
				} else {
					$msg = _("It failed in up-loading.");
				}
				$body .= $retcode['msg'];
				return array('msg' => $msg, 'body' => $body);
				break;
			}
		}
	}

	// ���ϥե������ɽ��
	$body .= plugin_dump_disp_form();

	$msg = '';
	if (PLUGIN_DUMP_ALLOW_RESTORE) {
		$msg = 'dump & restore';
	} else {
		$msg = 'dump';
	}

	return array('msg' => $msg, 'body' => $body);
}

/////////////////////////////////////////////////
// �ե�����Υ��������
function plugin_dump_download()
{
	global $vars, $_STORAGE;

	// ���������֤μ���
	$arc_kind = ($vars['pcmd'] == 'tar') ? 'tar' : 'tgz';

	// �ڡ���̾���Ѵ�����
	$namedecode = isset($vars['namedecode']) ? TRUE : FALSE;

	// �Хå����åץǥ��쥯�ȥ�
	$bk_wiki   = isset($vars['bk_wiki'])   ? TRUE : FALSE;
	$bk_attach = isset($vars['bk_attach']) ? TRUE : FALSE;
	$bk_backup = isset($vars['bk_backup']) ? TRUE : FALSE;

	$filecount = 0;
	$tar = new tarlib();
	$tar->create(CACHE_DIR, $arc_kind) or
		die_message( _("It failed in the generation of a temporary file.") );

	if ($bk_wiki)   $filecount += $tar->add_dir(DATA_DIR,   $_STORAGE['DATA_DIR']['add_filter'],   $namedecode);
	if ($bk_attach) $filecount += $tar->add_dir(UPLOAD_DIR, $_STORAGE['UPLOAD_DIR']['add_filter'], $namedecode);
	if ($bk_backup) $filecount += $tar->add_dir(BACKUP_DIR, $_STORAGE['BACKUP_DIR']['add_filter'], $namedecode);

	$tar->close();

	if ($filecount === 0) {
		@unlink($tar->filename);
		return '<p><strong>' . _("The file was not found.") . '</strong></p>';
	} else {
		// ���������
		download_tarfile($tar->filename, $arc_kind);
		@unlink($tar->filename);
		exit;	// ���ｪλ
	}
}

/////////////////////////////////////////////////
// �ե�����Υ��åץ���
function plugin_dump_upload()
{
	global $vars, $_STORAGE;

	if (! PLUGIN_DUMP_ALLOW_RESTORE)
		return array('code' => FALSE , 'msg' => 'Restoring function is not allowed');

	$filename = $_FILES['upload_file']['name'];
	$matches  = array();
	$arc_kind = FALSE;
	if(! preg_match('/(\.tar|\.tar.gz|\.tgz)$/', $filename, $matches)){
		die_message('Invalid file suffix');
	} else { 
		$matches[1] = strtolower($matches[1]);
		switch ($matches[1]) {
		case '.tar':    $arc_kind = 'tar'; break;
		case '.tgz':    $arc_kind = 'tar'; break;
		case '.tar.gz': $arc_kind = 'tgz'; break;
		default: die_message('Invalid file suffix: ' . $matches[1]); }
	}

	if ($_FILES['upload_file']['size'] >  PLUGIN_DUMP_MAX_FILESIZE * 1024)
		die_message('Max file size exceeded: ' . PLUGIN_DUMP_MAX_FILESIZE . 'KB');

	// Create a temporary tar file
	$uploadfile = tempnam(CACHE_DIR, 'tarlib_uploaded_');
	$tar = new tarlib();
	if(! move_uploaded_file($_FILES['upload_file']['tmp_name'], $uploadfile) ||
	   ! $tar->open($uploadfile, $arc_kind)) {
		@unlink($uploadfile);
		die_message( _("The file was not found.") );
	}

	$pattern = "(({$_STORAGE['DATA_DIR']['extract_filter']})|" .
		    "({$_STORAGE['UPLOAD_DIR']['extract_filter']})|" .
		    "({$_STORAGE['BACKUP_DIR']['extract_filter']}))";
	$files = $tar->extract($pattern);
	if (empty($files)) {
		@unlink($uploadfile);
		return array('code' => FALSE, 'msg' => '<p>' . _("There was no file that was able to be expanded.") . '</p>');
	}

	$msg  = '<p><strong>' . _("Progressing file list") . '</strong><ul>';
	foreach($files as $name) {
		$msg .= "<li>$name</li>\n";
	}
	$msg .= '</ul></p>';

	$tar->close();
	@unlink($uploadfile);

	return array('code' => TRUE, 'msg' => $msg);
}

/////////////////////////////////////////////////
// tar�ե�����Υ��������
function download_tarfile($tempnam, $arc_kind)
{
	$size = filesize($tempnam);

	$filename = strftime('tar%Y%m%d', time());
	if ($arc_kind == 'tgz') {
		$filename .= '.tar.gz';
	} else {
		$filename .= '.tar';
	}

	pkwk_common_headers();
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Length: ' . $size);
	header('Content-Type: application/octet-stream');
	header('Pragma: no-cache');
	@readfile($tempnam);
}

/////////////////////////////////////////////////
// ���ϥե������ɽ��
function plugin_dump_disp_form()
{
	global $script, $defaultpage;

	$act_down = PLUGIN_DUMP_DUMP;
	$act_up   = PLUGIN_DUMP_RESTORE;
	$maxsize  = PLUGIN_DUMP_MAX_FILESIZE;

	$_dump_h3_data = _("Download of data");
	$_dump_arc     = _("Form of archive");
	$_dump_form    = _("form");
	$_dump_backdir = _("Backup directory");
	$_dump_option  = _("Options");
	$_dump_namedecode  = _("Virtual of page name encode is converted into the file name with the directory.") .
		_("(The restoration that uses this data cannot be done.") .
		_("Moreover, a part of character is substituted for '_'.)");
	$_dump_admin   = _("Administrator password");
	$_dump_h3_store = _(" Restoration of data");
	$_dump_caution = _("[IMPORTANCE]");
	$_dump_write   = _("Attention: Please note that the data file of the same name is overwrited.");
	$_dump_upload  = _("The size of the maximum file that can be up-loaded is up to $maxsize KByte.<br />");
	$_dump_file = _("File");

	$data = <<<EOD
<span class="small">
</span>
<h3>$_dump_h3_data</h3>
<form action="$script" method="post">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_down" />

<p><strong>$_dump_arc</strong>
<br />
  <input type="radio" name="pcmd" id="_p_dump_tgz" value="tgz" checked="checked" />
  <label for="_p_dump_tgz"> .tar.gz $_dump_form</label><br />
  <input type="radio" name="pcmd" id="_p_dump_tar" value="tar" />
  <label for="_p_dump_tar"> .tar $_dump_form</label>
</p>
<p><strong>$_dump_backdir</strong>
<br />
  <input type="checkbox" name="bk_wiki" id="_p_dump_d_wiki" checked="checked" />
  <label for="_p_dump_d_wiki">wiki</label><br />
  <input type="checkbox" name="bk_attach" id="_p_dump_d_attach" />
  <label for="_p_dump_d_attach">attach</label><br />
  <input type="checkbox" name="bk_backup" id="_p_dump_d_backup" />
  <label for="_p_dump_d_backup">backup</label><br />
</p>
<p><strong>$_dump_option</strong>
<br />
  <input type="checkbox" name="namedecode" id="_p_dump_namedecode" />
  <label for="_p_dump_namedecode">$_dump_namedecode</label><br />
</p>
<p><label for="_p_dump_adminpass_dump"><strong>$_dump_admin</strong></label>
  <input type="password" name="pass" id="_p_dump_adminpass_dump" size="12" />
  <input type="submit"   name="ok"   value="OK" />
</p>
 </div>
</form>
EOD;

	if(PLUGIN_DUMP_ALLOW_RESTORE) {
		$data .= <<<EOD
<h3>$_dump_h3_store (*.tar, *.tar.gz)</h3>
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_up" />
<p><strong>$_dump_caution $_dump_write</strong></p>
<p><span class="small">
$_dump_upload
</span>
  <label for="_p_dump_upload_file">$_dump_file:</label>
  <input type="file" name="upload_file" id="_p_dump_upload_file" size="40" />
</p>
<p><label for="_p_dump_adminpass_restore"><strong>$_dump_admin</strong></label>
  <input type="password" name="pass" id="_p_dump_adminpass_restore" size="12" />
  <input type="submit"   name="ok"   value="OK" />
</p>
 </div>
</form>
EOD;
	}

	return $data;
}

/////////////////////////////////////////////////
// tarlib: a class library for tar file creation and expansion

// Tar related definition
define('TARLIB_HDR_LEN',           512);	// �إå����礭��
define('TARLIB_BLK_LEN',           512);	// ñ�̥֥�å�Ĺ��
define('TARLIB_HDR_NAME_OFFSET',     0);	// �ե�����̾�Υ��ե��å�
define('TARLIB_HDR_NAME_LEN',      100);	// �ե�����̾�κ���Ĺ��
define('TARLIB_HDR_MODE_OFFSET',   100);	// mode�ؤΥ��ե��å�
define('TARLIB_HDR_UID_OFFSET',    108);	// uid�ؤΥ��ե��å�
define('TARLIB_HDR_GID_OFFSET',    116);	// gid�ؤΥ��ե��å�
define('TARLIB_HDR_SIZE_OFFSET',   124);	// �������ؤΥ��ե��å�
define('TARLIB_HDR_SIZE_LEN',       12);	// ��������Ĺ��
define('TARLIB_HDR_MTIME_OFFSET',  136);	// �ǽ���������Υ��ե��å�
define('TARLIB_HDR_MTIME_LEN',      12);	// �ǽ����������Ĺ��
define('TARLIB_HDR_CHKSUM_OFFSET', 148);	// �����å�����Υ��ե��å�
define('TARLIB_HDR_CHKSUM_LEN',      8);	// �����å������Ĺ��
define('TARLIB_HDR_TYPE_OFFSET',   156);	// �ե����륿���פؤΥ��ե��å�

// Status
define('TARLIB_STATUS_INIT',    0);		// �������
define('TARLIB_STATUS_OPEN',   10);		// �ɤ߼��
define('TARLIB_STATUS_CREATE', 20);		// �񤭹���

define('TARLIB_DATA_MODE',      '100666 ');	// �ե�����ѡ��ߥå����
define('TARLIB_DATA_UGID',      '000000 ');	// uid / gid
define('TARLIB_DATA_CHKBLANKS', '        ');

// GNU��ĥ����(��󥰥ե�����̾�б�)
define('TARLIB_DATA_LONGLINK', '././@LongLink');

// Type flag
define('TARLIB_HDR_FILE', '0');
define('TARLIB_HDR_LINK', 'L');

// Kind of the archive
define('TARLIB_KIND_TGZ', 0);
define('TARLIB_KIND_TAR', 1);

class tarlib
{
	var $filename;
	var $fp;
	var $status;
	var $arc_kind;
	var $dummydata;

	// ���󥹥ȥ饯��
	function tarlib() {
		$this->filename = '';
		$this->fp       = FALSE;
		$this->status   = TARLIB_STATUS_INIT;
		$this->arc_kind = TARLIB_KIND_TGZ;
	}
	
	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�ե�������������
	// ����  : tar�ե�������������ѥ�
	// �֤���: TRUE .. ���� , FALSE .. ����
	////////////////////////////////////////////////////////////
	function create($tempdir, $kind = 'tgz')
	{
		$tempnam = tempnam($tempdir, 'tarlib_create_');
		if ($tempnam === FALSE) return FALSE;

		if ($kind == 'tgz') {
			$this->arc_kind = TARLIB_KIND_TGZ;
			$this->fp       = gzopen($tempnam, 'wb');
		} else {
			$this->arc_kind = TARLIB_KIND_TAR;
			$this->fp       = @fopen($tempnam, 'wb');
		}

		if ($this->fp === FALSE) {
			@unlink($tempnam);
			return FALSE;
		} else {
			$this->filename  = $tempnam;
			$this->dummydata = join('', array_fill(0, TARLIB_BLK_LEN, "\0"));
			$this->status    = TARLIB_STATUS_CREATE;
			rewind($this->fp);
			return TRUE;
		}
	}

	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�ե�����˥ǥ��쥯�ȥ���ɲä���
	// ����  : $dir    .. �ǥ��쥯�ȥ�̾
	//         $mask   .. �ɲä���ե�����(����ɽ��)
	//         $decode .. �ڡ���̾���Ѵ��򤹤뤫
	// �֤���: ���������ե������
	////////////////////////////////////////////////////////////
	function add_dir($dir, $mask, $decode = FALSE)
	{
		$retvalue = 0;
		
		if ($this->status != TARLIB_STATUS_CREATE)
			return ''; // File is not created

		unset($files);

		//  ���ꤵ�줿�ѥ��Υե�����Υꥹ�Ȥ��������
		$dp = @opendir($dir);
		if($dp === FALSE) {
			@unlink($this->filename);
			die_message($dir . ' is not found or not readable.');
		}

		while ($filename = readdir($dp)) {
			if (preg_match("/$mask/", $filename))
				$files[] = $dir . $filename;
		}
		closedir($dp);
		
		sort($files);

		$matches = array();
		foreach($files as $name)
		{
			// Tar�˳�Ǽ����ե�����̾��decode
			if ($decode === FALSE) {
				$filename = $name;
			} else {
				$dirname  = dirname(trim($name)) . '/';
				$filename = basename(trim($name));
				if (preg_match("/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)/", $filename, $matches)) {
					// attach�ե�����̾
					$filename = decode($matches[1]) . '/' . decode($matches[2]);
				} else {
					$pattern = '^((?:[0-9A-F]{2})+)((\.txt|\.gz)*)$';
					if (preg_match("/$pattern/", $filename, $matches)) {
						$filename = decode($matches[1]) . $matches[2];

						// ��ʤ������ɤ��ִ����Ƥ���
						$filename = str_replace(':',  '_', $filename);
						$filename = str_replace('\\', '_', $filename);
					}
				}
				$filename = $dirname . $filename;
				// �ե�����̾��ʸ�������ɤ��Ѵ�
				if (function_exists('mb_convert_encoding'))
					$filename = mb_convert_encoding($filename, PLUGIN_DUMP_FILENAME_ENCORDING);
			}

			// �ǽ���������
			$mtime = filemtime($name);

			// �ե�����̾Ĺ�Υ����å�
			if (strlen($filename) > TARLIB_HDR_NAME_LEN) {
				// LongLink�б�
				$size = strlen($filename);
				// LonkLink�إå�����
				$tar_data = $this->_make_header(TARLIB_DATA_LONGLINK, $size, $mtime, TARLIB_HDR_LINK);
				// �ե��������
	 			$this->_write_data(join('', $tar_data), $filename, $size);
			}

			// �ե����륵���������
			$size = filesize($name);
			if ($size === FALSE) {
				@unlink($this->filename);
				die_message($name . ' is not found or not readable.');
			}

			// �إå�����
			$tar_data = $this->_make_header($filename, $size, $mtime, TARLIB_HDR_FILE);

			// �ե�����ǡ����μ���
			$fpr = @fopen($name , 'rb');
			flock($fpr, LOCK_SH);
			$data = fread($fpr, $size);
			flock($fpr, LOCK_UN);
			fclose( $fpr );

			// �ե��������
			$this->_write_data(join('', $tar_data), $data, $size);
			++$retvalue;
		}
		return $retvalue;
	}
	
	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�Υإå�������������� (add)
	// ����  : $filename .. �ե�����̾
	//         $size     .. �ǡ���������
	//         $mtime    .. �ǽ�������
	//         $typeflag .. TypeFlag (file/link)
	// �����: tar�إå�����
	////////////////////////////////////////////////////////////
	function _make_header($filename, $size, $mtime, $typeflag)
	{
		$tar_data = array_fill(0, TARLIB_HDR_LEN, "\0");
		
		// �ե�����̾����¸
		for($i = 0; $i < strlen($filename); $i++ ) {
			if ($i < TARLIB_HDR_NAME_LEN) {
				$tar_data[$i + TARLIB_HDR_NAME_OFFSET] = $filename{$i};
			} else {
				break;	// �ե�����̾��Ĺ����
			}
		}

		// mode
		$modeid = TARLIB_DATA_MODE;
		for($i = 0; $i < strlen($modeid); $i++ ) {
			$tar_data[$i + TARLIB_HDR_MODE_OFFSET] = $modeid{$i};
		}

		// uid / gid
		$ugid = TARLIB_DATA_UGID;
		for($i = 0; $i < strlen($ugid); $i++ ) {
			$tar_data[$i + TARLIB_HDR_UID_OFFSET] = $ugid{$i};
			$tar_data[$i + TARLIB_HDR_GID_OFFSET] = $ugid{$i};
		}

		// ������
		$strsize = sprintf('%11o', $size);
		for($i = 0; $i < strlen($strsize); $i++ ) {
			$tar_data[$i + TARLIB_HDR_SIZE_OFFSET] = $strsize{$i};
		}

		// �ǽ���������
		$strmtime = sprintf('%o', $mtime);
		for($i = 0; $i < strlen($strmtime); $i++ ) {
			$tar_data[$i + TARLIB_HDR_MTIME_OFFSET] = $strmtime{$i};
		}

		// �����å�����׻��ѤΥ֥�󥯤�����
		$chkblanks = TARLIB_DATA_CHKBLANKS;
		for($i = 0; $i < strlen($chkblanks); $i++ ) {
			$tar_data[$i + TARLIB_HDR_CHKSUM_OFFSET] = $chkblanks{$i};
		}

		// �����ץե饰
		$tar_data[TARLIB_HDR_TYPE_OFFSET] = $typeflag;

		// �����å�����η׻�
		$sum = 0;
		for($i = 0; $i < TARLIB_BLK_LEN; $i++ ) {
			$sum += 0xff & ord($tar_data[$i]);
		}
		$strchksum = sprintf('%7o',$sum);
		for($i = 0; $i < strlen($strchksum); $i++ ) {
			$tar_data[$i + TARLIB_HDR_CHKSUM_OFFSET] = $strchksum{$i};
		}

		return $tar_data;
	}
	
	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�ǡ����Υե�������� (add)
	// ����  : $header .. tar�إå�����
	//         $body   .. tar�ǡ���
	//         $size   .. �ǡ���������
	// �����: �ʤ�
	////////////////////////////////////////////////////////////
	function _write_data($header, $body, $size)
	{
		$fixsize  = ceil($size / TARLIB_BLK_LEN) * TARLIB_BLK_LEN - $size;

		if ($this->arc_kind == TARLIB_KIND_TGZ) {
			gzwrite($this->fp, $header, TARLIB_HDR_LEN);    // Header
			gzwrite($this->fp, $body, $size);               // Body
			gzwrite($this->fp, $this->dummydata, $fixsize); // Padding
		} else {
			 fwrite($this->fp, $header, TARLIB_HDR_LEN);    // Header
			 fwrite($this->fp, $body, $size);               // Body
			 fwrite($this->fp, $this->dummydata, $fixsize); // Padding
		}
	}

	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�ե�����򳫤�
	// ����  : tar�ե�����̾
	// �֤���: TRUE .. ���� , FALSE .. ����
	////////////////////////////////////////////////////////////
	function open($name = '', $kind = 'tgz')
	{
		if (! PLUGIN_DUMP_ALLOW_RESTORE) return FALSE; // Not allowed

		if ($name != '') $this->filename = $name;

		if ($kind == 'tgz') {
			$this->arc_kind = TARLIB_KIND_TGZ;
			$this->fp = gzopen($this->filename, 'rb');
		} else {
			$this->arc_kind = TARLIB_KIND_TAR;
			$this->fp =  fopen($this->filename, 'rb');
		}

		if ($this->fp === FALSE) {
			return FALSE;	// No such file
		} else {
			$this->status = TARLIB_STATUS_OPEN;
			rewind($this->fp);
			return TRUE;
		}
	}

	////////////////////////////////////////////////////////////
	// �ؿ�  : ���ꤷ���ǥ��쥯�ȥ��tar�ե������Ÿ������
	// ����  : Ÿ������ե�����ѥ�����(����ɽ��)
	// �֤���: Ÿ�������ե�����̾�ΰ���
	// ��­  : ARAI�����attach�ץ饰����ѥå��򻲹ͤˤ��ޤ���
	////////////////////////////////////////////////////////////
	function extract($pattern)
	{
		if ($this->status != TARLIB_STATUS_OPEN) return ''; // Not opened
		
		$files = array();
		$longname = '';

		while(1) {
			$buff = fread($this->fp, TARLIB_HDR_LEN);
			if (strlen($buff) != TARLIB_HDR_LEN) break;

			// �ե�����̾
			$name = '';
			if ($longname != '') {
				$name     = $longname;	// LongLink�б�
				$longname = '';
			} else {
				for ($i = 0; $i < TARLIB_HDR_NAME_LEN; $i++ ) {
					if ($buff{$i + TARLIB_HDR_NAME_OFFSET} != "\0") {
						$name .= $buff{$i + TARLIB_HDR_NAME_OFFSET};
					} else {
						break;
					}
				}
			}
			$name = trim($name);

			if ($name == '') break;	// Ÿ����λ

			// �����å������������Ĥġ��֥�󥯤��ִ����Ƥ���
			$checksum = '';
			$chkblanks = TARLIB_DATA_CHKBLANKS;
			for ($i = 0; $i < TARLIB_HDR_CHKSUM_LEN; $i++ ) {
				$checksum .= $buff{$i + TARLIB_HDR_CHKSUM_OFFSET};
				$buff{$i + TARLIB_HDR_CHKSUM_OFFSET} = $chkblanks{$i};
			}
			list($checksum) = sscanf('0' . trim($checksum), '%i');

			// Compute checksum
			$sum = 0;
			for($i = 0; $i < TARLIB_BLK_LEN; $i++ ) {
				$sum += 0xff & ord($buff{$i});
			}
			if ($sum != $checksum) break; // Error
				
			// Size
			$size = '';
			for ($i = 0; $i < TARLIB_HDR_SIZE_LEN; $i++ ) {
				$size .= $buff{$i + TARLIB_HDR_SIZE_OFFSET};
			}
			list($size) = sscanf('0' . trim($size), '%i');

			// ceil
			// �ǡ����֥�å���512byte�ǥѥǥ��󥰤���Ƥ���
			$pdsz = ceil($size / TARLIB_BLK_LEN) * TARLIB_BLK_LEN;

			// �ǽ���������
			$strmtime = '';
			for ($i = 0; $i < TARLIB_HDR_MTIME_LEN; $i++ ) {
				$strmtime .= $buff{$i + TARLIB_HDR_MTIME_OFFSET};
			}
			list($mtime) = sscanf('0' . trim($strmtime), '%i');

			// �����ץե饰
//			 $type = $buff{TARLIB_HDR_TYPE_OFFSET};

			if ($name == TARLIB_DATA_LONGLINK) {
				// LongLink
				$buff     = fread($this->fp, $pdsz);
				$longname = substr($buff, 0, $size);
			} else if (preg_match("/$pattern/", $name) ) {
//			} else if ($type == 0 && preg_match("/$pattern/", $name) ) {
				$buff = fread($this->fp, $pdsz);

				// ����Ʊ���ե����뤬������Ͼ�񤭤����
				$fpw = @fopen($name, 'wb');
				if ($fpw !== FALSE) {
					flock($fpw, LOCK_EX);
					fwrite($fpw, $buff, $size);
					@chmod($name, 0666);
					@touch($name, $mtime);
					flock($fpw, LOCK_UN);

					fclose($fpw);
					$files[] = $name;
				}
			} else {
				// �ե�����ݥ��󥿤�ʤ��
				@fseek($this->fp, $pdsz, SEEK_CUR);
			}
		}
		return $files;
	}

	////////////////////////////////////////////////////////////
	// �ؿ�  : tar�ե�������Ĥ���
	// ����  : �ʤ�
	// �֤���: �ʤ�
	////////////////////////////////////////////////////////////
	function close()
	{
		if ($this->status == TARLIB_STATUS_CREATE) {
			// �ե�������Ĥ���
			if ($this->arc_kind == TARLIB_KIND_TGZ) {
				// �Х��ʥ꡼�����1024�Х��Ƚ���
				gzwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				gzwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				gzclose($this->fp);
			} else {
				// �Х��ʥ꡼�����1024�Х��Ƚ���
				fwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				fwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				fclose($this->fp);
			}
		} else if ($this->status == TARLIB_STATUS_OPEN) {
			if ($this->arc_kind == TARLIB_KIND_TGZ) {
				gzclose($this->fp);
			} else {
				 fclose($this->fp);
			}
		}

		$this->status = TARLIB_STATUS_INIT;
	}

}
?>
