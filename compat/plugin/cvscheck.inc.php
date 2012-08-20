<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: cvscheck.inc.php,v 0.19 2006/10/04 01:31:00 miko Exp $
/* 
*プラグイン cvscheck
 cvsのversionと比較して異なるものを表示

*Usage
  ./?plugin=cvscheck&refer=<page>&param=local,10m
  #cvscheck([local|optional|<数字>{m,h,d}])
  local: ローカルにのみ存在するファイルでバージョン記述の存在するものを表示
  <数字>m: キャッシュを有効にして、<数字>分だけ保存。
  <数字>h: キャッシュを有効にして、<数字>時間だけ保存。
  <数字>d: キャッシュを有効にして、<数字>日だけ保存。
*/
// 項目の取り出しに失敗したページを一覧に表示する
define('CVSCHECK_CACHE_FILE','cvscheck_cache.txt');
define('CVSCHECK_CACHE_LOCAL_FILE','cvscheck_cache_local.txt');

//=========================================================
function plugin_cvscheck_init()
{
	$messages = array(
	  '_cvscheck_messages' => array(
		 'URL'      => 'http://cvs.sourceforge.jp/cgi-bin/viewcvs.cgi/pukiwiki/pukiwiki',
		 'URL_FILE' => 'http://cvs.sourceforge.jp/cgi-bin/viewcvs.cgi/*checkout*/pukiwiki/pukiwiki/',
		 'OPTIONAL_URL' => 'http://pukiwiki.sourceforge.jp/?',
		 'OPTIONAL_OPT' => 'plugin=ls2&prefix=',
		 'OPTIONAL_DIR' => '%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2F',
		 'DIRS' => array("DATA_HOME"   => '/',
				 "PLUGIN_DIR"  =>'/plugin/',
				 "LIB_DIR"     =>'/lib/',
				 "SKIN_DIR"    =>'/skin/',
				 "DOC_DIR"     =>'/doc/' ),
		 'LDIRS' => array("DATA_HOME"  => DATA_HOME ,
				  "PLUGIN_DIR" => PLUGIN_DIR, 
				  "LIB_DIR"    => LIB_DIR,
				  "SKIN_DIR"   => SKIN_DIR,
				  "DOC_DIR"    => SITE_HOME . 'doc/', ),
		 'title_name'  => _('file name'),
		 'title_cvs'    => _('cvs versions'),
		 'title_local'  => _('local versions'),
		 'title_not_found' => _('not found'),
		 'msg_title'    => _('diff in cvscheck'),
		 'body_title'       => _('Difference of file version of this site and CVS'),
		 'body_title_local' => _('Version of file where only this site exists'),
		 'msg_new_files'   => _('It is not necessary to update it.'),
		 'msg_load_error'  => _('File Load Error'),
		 'body_load_error' => _('The file was not able to be read.'),
		 'msg_access_error'  => _('Access Error'),
		 'body_access_error' => _('It was not able to access the CVS site.'),
      ),
	);
	set_plugin_messages($messages);
}
//=========================================================
function plugin_cvscheck_convert()
{
	static $acvs = '';
	$cache_file = CACHE_DIR . CVSCHECK_CACHE_FILE;

	$debug  = '';
	$local_flag = $optional_flag = 0;
	$args = func_get_args();
	$interval = 0;
	foreach ( $args as $arg ){
		if ( $arg == 'local' ) $local_flag = 1;
		else if ( preg_match('/^(\d+)([mhd]?)$/',$arg,$match) ) {
			$interval = $match[1];
			switch ( $match[2] ){
				case 'm': $interval *= 60; break;
				case 'h': 
				default: 
					$interval *= 3600; break;
				case 'd': 
					$interval *= 86400; break;
			}
		}
//		else if ( $arg == 'optional' ) $optional_flag = 1;
	}
	$optional_flag = 1;

	$alocal = plugin_cvscheck_localversions();
	if ( ! count($alocal) ) return FALSE;
	$cache_write_flag = 0;
	if ( $acvs == '' and $interval > 0 ) {
		$acvs = plugin_cvscheck_cache_read($cache_file,$interval);
		if ( $acvs == '' ) $cache_write_flag = 1;
	}
//	$debug = "interval=$interval<br />\n$cache_file<br />\n$cache_write_flag<br />\n";
	if ( $acvs == '' ) $acvs = plugin_cvscheck_cvsversions();
	if ( $acvs == '' or ! count($acvs) ) return FALSE;
	if ( $cache_write_flag ) plugin_cvscheck_cache_write($cache_file,$acvs);
	if ( $local_flag == 0 ) {
		$puki = plugin_cvscheck_diff($alocal,$acvs);
	}
	else {
		$puki = plugin_cvscheck_diff_local($alocal,$acvs,$optional_flag, $interval);
	}
	return convert_html(join('',$puki));
}
//=========================================================
function plugin_cvscheck_action()
{
	global $vars, $_cvscheck_messages;
	$cache_file = CACHE_DIR . CVSCHECK_CACHE_FILE;

	$interval = $local_flag = $optional_flag = 0;
	if ( array_key_exists('param', $vars) ) $param = $vars['param'];
	foreach ( split(',',$param) as $arg ){
		if ( $arg == 'local' ) $local_flag = 1;
		else if ( preg_match('/^(\d+)([mhd]?)$/',$arg,$match) ) {
			$interval = $match[1];
			switch ( $match[2] ){
				case 'm': $interval *= 60; break;
				case 'h': 
				default: 
					$interval *= 3600; break;
				case 'd': 
					$interval *= 86400; break;
			}
		}
//		else if ( $arg == 'optional' ) $optional_flag = 1;
	}

	$alocal = plugin_cvscheck_localversions();
	if ( ! count($alocal) ){
		return array(
			'msg'  => $_cvscheck_messages['msg_load_error'], 
			'body' => $_cvscheck_messages['body_load_error'],
		);
	}
	
	$acvs = '';
	$cache_write_flag = 0;
	if ( $interval > 0 ) {
		$acvs = plugin_cvscheck_cache_read($cache_file,$interval);
		if ( $acvs == '' ) $cache_write_flag = 1;
	}
	if ( $acvs == '' ) $acvs = plugin_cvscheck_cvsversions();
	if ( ! count($acvs) ){
		return array(
			'msg'  => $_cvscheck_messages['msg_access_error'], 
			'body' => $_cvscheck_messages['body_access_error'],
		);
	}
	if ( $cache_write_flag == 1 ) plugin_cvscheck_cache_write($cache_file,$acvs);
	if ( $local_flag ){
		$optional_flag = 1;
		$puki = plugin_cvscheck_diff_local($alocal,$acvs, $optional_flag, $interval);
		array_unshift($puki, "*{$_cvscheck_messages['body_title_local']}\n");
	}
	else {
		$puki = plugin_cvscheck_diff($alocal,$acvs);
		array_unshift($puki, "*{$_cvscheck_messages['body_title']}\n");
	}

	if ( array_key_exists('refer', $vars) ){
		array_unshift($puki, "[[{$vars['refer']}]]\n");
	}
	$title = $_cvscheck_messages['msg_title'];
	$body = convert_html(join('',$puki));

//	$body .= 'debug: ' . $acvs['debug'];
//	$body .= 'debug: ' . $alocal['debug'];
//	$body .= 'debug: ' . $puki['debug'];

	return array('msg'=>$title, 'body'=>$body);
}
//=========================================================
function plugin_cvscheck_cache_read($file,$interval){
	if ( ! file_exists($file) ) return '';
	$lines = file($file);
	$time = $site = '';
	$outs = array();
	foreach ( $lines as $line ){
		if ( preg_match('/^(?:#|\/\/)/',$line) ) continue;
		if ( $time == '' and preg_match('/^time:\s*(\d+)$/',$line,$match) ) {
			$time = $match[1];
			if ( time() - $time > $interval ) return '';
			continue;
		}
		if ( preg_match('/^site:\s*([^\s]+)/',$line,$match) ){
			$site = $match[1];
			$outs[$site] = array();
			continue;
		}
		list($plugin,$ver) = preg_split('/\s+/',$line);
		if ( $plugin == '' or $ver == '' ) continue;
		$outs[$site][$plugin] = $ver;
	}
	return $outs;
}
//=========================================================
function plugin_cvscheck_cache_write($file,$acvs){
	$buf = array();
	foreach ( $acvs as $site=>$ary ){
		$buf[] = "site: $site\n";
		foreach ( $ary as $plugin=>$ver ){
			$buf[] = "$plugin $ver\n";
		}
	}
	$fp = fopen($file, 'w+');
	if ( ! $fp ) return $file;
	flock($fp,LOCK_EX);
	rewind($fp);
	ftruncate($fp,0);
	array_unshift($buf, sprintf("time: %d\n", time()));
	fwrite($fp,join('',$buf));
	flock($fp,LOCK_UN);
	fclose($fp);
	return 1;
}
//=========================================================
function plugin_cvscheck_diff($alocal,$acvs)
{
	global $_cvscheck_messages;

	$debug = '';
	$url  = preg_replace('/\/$/','',$_cvscheck_messages['URL']);
	$curl = preg_replace('/\/$/','',$_cvscheck_messages['URL_FILE']);
	$sites = $_cvscheck_messages['DIRS'];

	$outs = array();
	foreach ( $acvs as $site=>$ary ){
		foreach ( $ary as $file=>$cver ){
		        if(!array_key_exists($file, $alocal[$site]) ) continue;
			$lver = $alocal[$site][$file];
			if ( $lver == '' or $lver == $cver) continue;
			$lver = ( $lver == '' ) ? $_cvscheck_messages['title_not_found'] : $lver;
			$s = preg_replace('/^\//','',$sites[$site]) . $file;
			if (version_compare($lver,$cver) >= 0) {
				$outs[] = "|[[$s&nbsp;:$url/$s]]|COLOR(green)" . '{' . $lver . '}' . "|[[$cver:$curl/$s?rev=$cver]]|\n";
			} else {
				$outs[] = "|[[$s&nbsp;:$url/$s]]|COLOR(red)" . '{' . $lver . '}' . "|[[$cver:$curl/$s?rev=$cver]]|\n";
			}
		}
	}
	if ( count($outs) ){
		array_unshift($outs, 
			"|LEFT:|LEFT:|LEFT:|c\n",
			"|{$_cvscheck_messages['title_name']}|{$_cvscheck_messages['title_local']}|{$_cvscheck_messages['title_cvs']}|h\n"
		);
	}
	else {
		$outs = array(
			"CENTER:" . $_cvscheck_messages['msg_new_files'] . "\n",
		);
	}
//	$outs['debug'] = $debug;
	return $outs;
}
//=========================================================
function plugin_cvscheck_diff_local($alocal,$acvs, $optional_flag, $interval)
{
	global $_cvscheck_messages;
	static $aopt = '';
	$cache_file = CACHE_DIR . CVSCHECK_CACHE_LOCAL_FILE;

	$debug = $site = '';
	$cache_write_flag = 0;
	if ( $optional_flag ) {
		if ( $aopt == '' and $interval > 0 ) {
			$aopt = plugin_cvscheck_cache_read($cache_file,$interval);
			if ( $aopt == '' ) $cache_write_flag = 1;
		}
		if ( $aopt == '' ) $aopt = plugin_cvscheck_localoptional();
		if ( $aopt != '' and $cache_write_flag ) 
			plugin_cvscheck_cache_write($cache_file,$aopt);
		foreach ( $aopt as $dir=>$ary ) {
			$plugins = $ary; 
			$site    = $dir;
			break;
		}
	}
	$url  = $_cvscheck_messages['URL'];
	if ( preg_match('/^(.+)\/$/',$url,$mat) ) $url = $mat[1];
	$sites = $_cvscheck_messages['DIRS'];

	$outs = array();
	foreach ( $alocal as $site=>$ary ){
		ksort($ary);
		foreach ( $ary as $file=>$lver ){
			$e = array_key_exists($file, $acvs[$site]);
			if ( $e or $lver == '' ) continue;
			$s = preg_replace('/^\//','',$sites[$site]);
			$u = $plugins[$file];
			if ( $u ) $outs[] = "|[[$s$file:$u]]|$lver|\n";
			else      $outs[] = "|$s$file|$lver|\n";
		}
	}

	if ( count($outs) ){
		array_unshift($outs, 
			"|LEFT:|LEFT:|c\n",
			"|{$_cvscheck_messages['title_name']}|{$_cvscheck_messages['title_local']}|h\n"
		);
	}
	else {
		$outs = array(
			"CENTER:" . $_cvscheck_messages['msg_new_files'] . "\n",
		);
	}
//	$outs['debug'] = $debug;
	return $outs;
}
//=========================================================
function plugin_cvscheck_cvsversions()
{
	global $_cvscheck_messages;
	
	$debug = '';
	$outs = array();
	$url  = $_cvscheck_messages['URL'];
	if ( preg_match('/^(.+)\/$/',$url,$mat) ) $url = $mat[1];
	$sites = $_cvscheck_messages['DIRS'];
	foreach ( $sites as $key=>$site ){
		$ret = http_request($url . $site);
		if ( $ret['rc'] != 200 ) continue;
		$html = preg_replace('/&amp;/','&',$ret['data']);
		if ( !preg_match_all('/<tr(?>[^>])*>((?>.(?!<\/tr>))+.)<\/tr>/is',$html,$matches) ) continue;

		$ary = array();
		foreach ( $matches[1] as $line ){
			if ( !preg_match_all('/<td(?>[^>])*>((?>.(?!<\/td>))+.)<\/td>/is',$line,$match)) continue;
			if (count($match[1]) < 3)  continue;
			$file = htmlspecialchars(trim(preg_replace('/<[^>]+>|&\w+;/','',$match[1][0])));
			$ver  = htmlspecialchars(trim(preg_replace('/<[^>]+>|&\w+;/','',$match[1][2])));
			if ( !preg_match('/^[\w\.]+$/', $file) 
				or !preg_match('/^[\d\.]+$/',$ver) ) continue;
				$debug .= "$site - $file : $ver ---<br />\n";
			$ary[$file] = $ver;
		}
		$outs[$key] = $ary;
	}
//	$outs['debug'] = $debug;
	return $outs;
}
//=========================================================
function plugin_cvscheck_localversions()
{
	global $_cvscheck_messages;
	
	$debug = '';
	$outs = array();
	$dirs = $_cvscheck_messages['LDIRS'];
	foreach ($dirs as $key=>$adir) {
		$sdir = './' . $adir;
		if (!$dir = @dir($sdir)) continue;
		$ary = array();
		while($file = $dir->read())	{
			if (!preg_match('/\.\w+$/',$file)) continue;
			$data = join('',file($sdir.$file));
			if (preg_match('/\$'.'Id: (.+),v ([\d\.]+) (\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/',$data,$matches))
			{
				$file = htmlspecialchars($matches[1]);
				$ver  = htmlspecialchars($matches[2]);
				if ( plugin_cvscheck_greaterp($ver, $ary[$file]) ) $ary[$file] = $ver;
				$debug .= "$adir - $file : $ver ---<br />\n";
			}
			else {
				$file = htmlspecialchars($file);
				$ary[$file] = '';
			}
		}
		$dir->close();
		$outs[$key] = $ary;
	}
//	$outs['debug'] = $debug;
	return $outs;
}
//=========================================================
function plugin_cvscheck_localoptional()
{
	global $_cvscheck_messages;

	$debug = '';
	$outs = '';
	$dir  = $_cvscheck_messages['OPTIONAL_DIR'];
	if ( $dir == '' ) return $outs;
	$url  = $_cvscheck_messages['OPTIONAL_URL'];
	if ( $url == '' ) return $outs;
	$opt  = $_cvscheck_messages['OPTIONAL_OPT'];
	$ret = http_request($url . $opt . $dir);
	if ( $ret['rc'] != 200 ) return $outs;
	$html = $ret['data'];
	if ( !preg_match_all("/$dir([\w\.]+)\"/is",$html,$match)) return $outs;
	foreach ( $match[1] as $plugin ) {
		$outs[$dir][$plugin] = $url . $dir . $plugin;
	}
//	$outs['debug'] = $debug;
	return $outs;
}
function plugin_cvscheck_greaterp($ver1,$ver2)
{
	$a1 = preg_split("/\./", $ver1); 
	$a2 = preg_split("/\./", $ver2); 
	$len1 = count($a1);
	$len2 = count($a2);
	$max = ( $len1 > $len2 ) ? $len1 : $len2;
	for($i=0; $i < $max; $i++){
		if ( intval($a1[$i]) > intval($a2[$i]) ) return TRUE;
		if ( intval($a1[$i]) < intval($a2[$i]) ) return FALSE;
	}
	return FALSE;
}

?>
