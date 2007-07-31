<?php
// $Id: dump.inc.php,v 1.40.7 2007/05/13 22:11:23 miko Exp $
// Copyright (C)
//   2005-2007 PukiWiki Plus! Team
//   2004-2007 PukiWiki Developers Team
//   2004      teanan / Interfair Laboratory
// License: GPL v2 or (at your option) any later version
//
// Remote dump / restore plugin
// Originated as tarfile.inc.php by teanan / Interfair Laboratory 2004.

/////////////////////////////////////////////////
// User defines

// Allow using resture function
define('PLUGIN_DUMP_ALLOW_RESTORE', FALSE); // FALSE, TRUE

// ページ名をディレクトリ構造に変換する際の文字コード (for mbstring)
define('PLUGIN_DUMP_FILENAME_ENCORDING', 'SJIS');

// 最大アップロードサイズ
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
// プラグイン本体
function plugin_dump_action()
{
	global $vars, $auth_users, $realm;

	// if (PKWK_READONLY) die_message('PKWK_READONLY prohibits this');
	if (auth::check_role('readonly')) die_message( _("PKWK_READONLY prohibits this") );

	$msg = (PLUGIN_DUMP_ALLOW_RESTORE) ? _("dump & restore") : _("dump");
	$body = '';

 	while (auth::check_role('role_adm')) {
		unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		if (!auth::auth_pw($auth_users))
		{
			header('WWW-Authenticate: Basic realm="'.$realm.'"');
			header( 'HTTP/1.0 401 Unauthorized' );

			$body = "<p><strong>" . _("The password is different.") . "</strong></p>\n";
			return array('msg' => $msg, 'body' => $body);

		}
	}

	// メニューを表示する必要があるか？
	if (! isset($vars['menu'])) {
		// 入力フォームを表示
		$body = plugin_dump_disp_form();
		return array('msg' => $msg, 'body' => $body);
	}

	$act  = isset($vars['act'])   ? $vars['act']   : NULL;
	set_time_limit(0);

	switch($act){
	case PLUGIN_DUMP_DUMP:
		$body = plugin_dump_download();
		break;
	case PLUGIN_DUMP_RESTORE:
		$retcode = plugin_dump_upload();
		$msg = ($retcode['code'] == TRUE) ? _("Up-loading was completed.") : _("It failed in up-loading.");
		$body = $retcode['msg'];
		break;
	default:
		// 無効な命令です。
		$body = _("It is an invalid instruction.");
	}

	return array('msg' => $msg, 'body' => $body);

}

/////////////////////////////////////////////////
// ファイルのダウンロード
function plugin_dump_download()
{
	global $vars, $_STORAGE;

	// アーカイブの種類
	$arc_kind = ($vars['pcmd'] == 'tar') ? 'tar' : 'tgz';

	// ページ名に変換する
	$namedecode = isset($vars['namedecode']) ? TRUE : FALSE;

	// バックアップディレクトリ
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
		// ダウンロード
		download_tarfile($tar->filename, $arc_kind);
		@unlink($tar->filename);
		exit;	// 正常終了
	}
}

/////////////////////////////////////////////////
// ファイルのアップロード
function plugin_dump_upload()
{
	global $vars, $_STORAGE;

	if (! PLUGIN_DUMP_ALLOW_RESTORE)
		return array('code' => FALSE , 'msg' => _("Restoring function is not allowed") );

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
		default: die_message( _("Invalid file suffix: ") . $matches[1]); }
	}

	if ($_FILES['upload_file']['size'] >  PLUGIN_DUMP_MAX_FILESIZE * 1024)
		die_message( _("Max file size exceeded: ") . PLUGIN_DUMP_MAX_FILESIZE . 'KB');

	// Create a temporary tar file
	$uploadfile = tempnam(realpath(CACHE_DIR), 'tarlib_uploaded_');
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
// tarファイルのダウンロード
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
// 入力フォームを表示
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
	// $_dump_admin   = _("Administrator password");
	$_dump_h3_store = _(" Restoration of data");
	$_dump_caution = _("[IMPORTANCE]");
	$_dump_write   = _("Attention: Please note that the data file of the same name is overwrited.");
	$_dump_upload  = _("The size of the maximum file that can be up-loaded is up to $maxsize KByte.<br />");
	$_dump_file = _("File");
	$_dump_btn_down = _("Download execution");
	$_dump_btn_up = _("Upload execution");

	$data = <<<EOD
<span class="small">
</span>
<h3>$_dump_h3_data</h3>
<form action="$script" method="post">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_down" />
  <input type="hidden" name="menu" value="1" />

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
<p>
  <input type="submit" name="ok" value="$_dump_btn_down" />
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
<p>
  <input type="submit" name="ok" value="$_dump_btn_up" />
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
define('TARLIB_HDR_LEN',           512);	// ヘッダの大きさ
define('TARLIB_BLK_LEN',           512);	// 単位ブロック長さ
define('TARLIB_HDR_NAME_OFFSET',     0);	// ファイル名のオフセット
define('TARLIB_HDR_NAME_LEN',      100);	// ファイル名の最大長さ
define('TARLIB_HDR_MODE_OFFSET',   100);	// modeへのオフセット
define('TARLIB_HDR_UID_OFFSET',    108);	// uidへのオフセット
define('TARLIB_HDR_GID_OFFSET',    116);	// gidへのオフセット
define('TARLIB_HDR_SIZE_OFFSET',   124);	// サイズへのオフセット
define('TARLIB_HDR_SIZE_LEN',       12);	// サイズの長さ
define('TARLIB_HDR_MTIME_OFFSET',  136);	// 最終更新時刻のオフセット
define('TARLIB_HDR_MTIME_LEN',      12);	// 最終更新時刻の長さ
define('TARLIB_HDR_CHKSUM_OFFSET', 148);	// チェックサムのオフセット
define('TARLIB_HDR_CHKSUM_LEN',      8);	// チェックサムの長さ
define('TARLIB_HDR_TYPE_OFFSET',   156);	// ファイルタイプへのオフセット

// Status
define('TARLIB_STATUS_INIT',    0);		// 初期状態
define('TARLIB_STATUS_OPEN',   10);		// 読み取り
define('TARLIB_STATUS_CREATE', 20);		// 書き込み

define('TARLIB_DATA_MODE',      '100666 ');	// ファイルパーミッション
define('TARLIB_DATA_UGID',      '000000 ');	// uid / gid
define('TARLIB_DATA_CHKBLANKS', '        ');

// GNU拡張仕様(ロングファイル名対応)
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

	// コンストラクタ
	function tarlib() {
		$this->filename = '';
		$this->fp       = FALSE;
		$this->status   = TARLIB_STATUS_INIT;
		$this->arc_kind = TARLIB_KIND_TGZ;
	}
	
	////////////////////////////////////////////////////////////
	// 関数  : tarファイルを作成する
	// 引数  : tarファイルを作成するパス
	// 返り値: TRUE .. 成功 , FALSE .. 失敗
	////////////////////////////////////////////////////////////
	function create($tempdir, $kind = 'tgz')
	{
		$tempnam = tempnam(realpath($tempdir), 'tarlib_create_');
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
	// 関数  : tarファイルにファイルを追加する
	//
	function add_file($name, $filename = '', $decode = FALSE)
	{
		if ($this->status != TARLIB_STATUS_CREATE)
			return ''; // File is not created

		// Tarに格納するファイル名をdecode
		if ($decode === FALSE) {
			if ($filename == '') {
				$filename = $name;
			}
		} else {
			$dirname  = dirname(trim($name)) . '/';
			$filename = basename(trim($name));
			if (preg_match("/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)/", $filename, $matches)) {
				// attachファイル名
				$filename = decode($matches[1]) . '/' . decode($matches[2]);
			} else {
				$pattern = '^((?:[0-9A-F]{2})+)((\.txt|\.gz)*)$';
				if (preg_match("/$pattern/", $filename, $matches)) {
					$filename = decode($matches[1]) . $matches[2];

					// 危ないコードは置換しておく
					$filename = str_replace(':',  '_', $filename);
					$filename = str_replace('\\', '_', $filename);
				}
			}
			$filename = $dirname . $filename;
			// ファイル名の文字コードを変換
			if (function_exists('mb_convert_encoding'))
				$filename = mb_convert_encoding($filename, PLUGIN_DUMP_FILENAME_ENCORDING);
		}

		// 最終更新時刻
		$mtime = filemtime($name);

		// ファイル名長のチェック
		if (strlen($filename) > TARLIB_HDR_NAME_LEN) {
			// LongLink対応
			$size = strlen($filename);
			// LonkLinkヘッダ生成
			$tar_data = $this->_make_header(TARLIB_DATA_LONGLINK, $size, $mtime, TARLIB_HDR_LINK);
			// ファイル出力
 			$this->_write_data(join('', $tar_data), $filename, $size);
		}

		// ファイルサイズを取得
		$size = filesize($name);
		if ($size === FALSE) {
			@unlink($this->filename);
			die_message($name . ' is not found or not readable.');
		}

		// ヘッダ生成
		$tar_data = $this->_make_header($filename, $size, $mtime, TARLIB_HDR_FILE);

		// ファイルデータの取得
		$fpr = @fopen($name , 'rb');
		flock($fpr, LOCK_SH);
		$data = fread($fpr, $size);
		flock($fpr, LOCK_UN);
		fclose( $fpr );

		// ファイル出力
		$this->_write_data(join('', $tar_data), $data, $size);
		return 1;
	}

	////////////////////////////////////////////////////////////
	// 関数  : tarファイルにディレクトリを追加する
	// 引数  : $dir    .. ディレクトリ名
	//         $mask   .. 追加するファイル(正規表現)
	//         $decode .. ページ名の変換をするか
	// 返り値: 作成したファイル数
	////////////////////////////////////////////////////////////
	function add_dir($dir, $mask, $decode = FALSE)
	{
		$retvalue = 0;
		
		if ($this->status != TARLIB_STATUS_CREATE)
			return ''; // File is not created

		unset($files);

		//  指定されたパスのファイルのリストを取得する
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
		
		sort($files, SORT_STRING);

		$matches = array();
		foreach($files as $name)
		{
			// Tarに格納するファイル名をdecode
			if ($decode === FALSE) {
				$filename = $name;
			} else {
				$dirname  = dirname(trim($name)) . '/';
				$filename = basename(trim($name));
				if (preg_match("/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)/", $filename, $matches)) {
					// attachファイル名
					$filename = decode($matches[1]) . '/' . decode($matches[2]);
				} else {
					$pattern = '^((?:[0-9A-F]{2})+)((\.txt|\.gz)*)$';
					if (preg_match("/$pattern/", $filename, $matches)) {
						$filename = decode($matches[1]) . $matches[2];

						// 危ないコードは置換しておく
						$filename = str_replace(':',  '_', $filename);
						$filename = str_replace('\\', '_', $filename);
					}
				}
				$filename = $dirname . $filename;
				// ファイル名の文字コードを変換
				if (function_exists('mb_convert_encoding'))
					$filename = mb_convert_encoding($filename, PLUGIN_DUMP_FILENAME_ENCORDING);
			}

			// 最終更新時刻
			$mtime = filemtime($name);

			// ファイル名長のチェック
			if (strlen($filename) > TARLIB_HDR_NAME_LEN) {
				// LongLink対応
				$size = strlen($filename);
				// LonkLinkヘッダ生成
				$tar_data = $this->_make_header(TARLIB_DATA_LONGLINK, $size, $mtime, TARLIB_HDR_LINK);
				// ファイル出力
	 			$this->_write_data(join('', $tar_data), $filename, $size);
			}

			// ファイルサイズを取得
			$size = filesize($name);
			if ($size === FALSE) {
				@unlink($this->filename);
				die_message($name . ' is not found or not readable.');
			}

			// ヘッダ生成
			$tar_data = $this->_make_header($filename, $size, $mtime, TARLIB_HDR_FILE);

			// ファイルデータの取得
			$fpr = @fopen($name , 'rb');
			flock($fpr, LOCK_SH);
			$data = fread($fpr, $size);
			flock($fpr, LOCK_UN);
			fclose( $fpr );

			// ファイル出力
			$this->_write_data(join('', $tar_data), $data, $size);
			++$retvalue;
		}
		return $retvalue;
	}
	
	////////////////////////////////////////////////////////////
	// 関数  : tarのヘッダ情報を生成する (add)
	// 引数  : $filename .. ファイル名
	//         $size     .. データサイズ
	//         $mtime    .. 最終更新日
	//         $typeflag .. TypeFlag (file/link)
	// 戻り値: tarヘッダ情報
	////////////////////////////////////////////////////////////
	function _make_header($filename, $size, $mtime, $typeflag)
	{
		$tar_data = array_fill(0, TARLIB_HDR_LEN, "\0");
		
		// ファイル名を保存
		for($i = 0; $i < strlen($filename); $i++ ) {
			if ($i < TARLIB_HDR_NAME_LEN) {
				$tar_data[$i + TARLIB_HDR_NAME_OFFSET] = $filename{$i};
			} else {
				break;	// ファイル名が長すぎ
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

		// サイズ
		$strsize = sprintf('%11o', $size);
		for($i = 0; $i < strlen($strsize); $i++ ) {
			$tar_data[$i + TARLIB_HDR_SIZE_OFFSET] = $strsize{$i};
		}

		// 最終更新時刻
		$strmtime = sprintf('%o', $mtime);
		for($i = 0; $i < strlen($strmtime); $i++ ) {
			$tar_data[$i + TARLIB_HDR_MTIME_OFFSET] = $strmtime{$i};
		}

		// チェックサム計算用のブランクを設定
		$chkblanks = TARLIB_DATA_CHKBLANKS;
		for($i = 0; $i < strlen($chkblanks); $i++ ) {
			$tar_data[$i + TARLIB_HDR_CHKSUM_OFFSET] = $chkblanks{$i};
		}

		// タイプフラグ
		$tar_data[TARLIB_HDR_TYPE_OFFSET] = $typeflag;

		// チェックサムの計算
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
	// 関数  : tarデータのファイル出力 (add)
	// 引数  : $header .. tarヘッダ情報
	//         $body   .. tarデータ
	//         $size   .. データサイズ
	// 戻り値: なし
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
	// 関数  : tarファイルを開く
	// 引数  : tarファイル名
	// 返り値: TRUE .. 成功 , FALSE .. 失敗
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
	// 関数  : 指定したディレクトリにtarファイルを展開する
	// 引数  : 展開するファイルパターン(正規表現)
	// 返り値: 展開したファイル名の一覧
	// 補足  : ARAIさんのattachプラグインパッチを参考にしました
	////////////////////////////////////////////////////////////
	function extract($pattern)
	{
		if ($this->status != TARLIB_STATUS_OPEN) return ''; // Not opened
		
		$files = array();
		$longname = '';

		while(1) {
			$buff = fread($this->fp, TARLIB_HDR_LEN);
			if (strlen($buff) != TARLIB_HDR_LEN) break;

			// ファイル名
			$name = '';
			if ($longname != '') {
				$name     = $longname;	// LongLink対応
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

			if ($name == '') break;	// 展開終了

			// チェックサムを取得しつつ、ブランクに置換していく
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
			// データブロックは512byteでパディングされている
			$pdsz = ceil($size / TARLIB_BLK_LEN) * TARLIB_BLK_LEN;

			// 最終更新時刻
			$strmtime = '';
			for ($i = 0; $i < TARLIB_HDR_MTIME_LEN; $i++ ) {
				$strmtime .= $buff{$i + TARLIB_HDR_MTIME_OFFSET};
			}
			list($mtime) = sscanf('0' . trim($strmtime), '%i');

			// タイプフラグ
//			 $type = $buff{TARLIB_HDR_TYPE_OFFSET};

			if ($name == TARLIB_DATA_LONGLINK) {
				// LongLink
				$buff     = fread($this->fp, $pdsz);
				$longname = substr($buff, 0, $size);
			} else if (preg_match("/$pattern/", $name) ) {
//			} else if ($type == 0 && preg_match("/$pattern/", $name) ) {
				$buff = fread($this->fp, $pdsz);

				// 既に同じファイルがある場合は上書きされる
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
				// ファイルポインタを進める
				@fseek($this->fp, $pdsz, SEEK_CUR);
			}
		}
		return $files;
	}

	////////////////////////////////////////////////////////////
	// 関数  : tarファイルを閉じる
	// 引数  : なし
	// 返り値: なし
	////////////////////////////////////////////////////////////
	function close()
	{
		if ($this->status == TARLIB_STATUS_CREATE) {
			// ファイルを閉じる
			if ($this->arc_kind == TARLIB_KIND_TGZ) {
				// バイナリーゼロを1024バイト出力
				gzwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				gzwrite($this->fp, $this->dummydata, TARLIB_HDR_LEN);
				gzclose($this->fp);
			} else {
				// バイナリーゼロを1024バイト出力
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
