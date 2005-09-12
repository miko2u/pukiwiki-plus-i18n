<?php
/*
 * MS-DOSバッチ キーワード定義ファイル
 */

$capital = true;	// 予約語の大文字小文字を区別しない

// コメント定義
$switchHash['r'] = PLUGIN_CODE_COMMENT_WORD;	// コメントは REM から行末まで
$switchHash['R'] = PLUGIN_CODE_COMMENT_WORD;
$code_comment = Array(
	'r' => Array(
				 Array('/^rem /i', "\n", 1),
		),
	'R' => Array(
				 Array('/^rem /i', "\n", 1),
		),
);
// アウトライン用
if($mkoutline){
  $switchHash['('] = PLUGIN_CODE_BLOCK_START;
  $switchHash[')'] = PLUGIN_CODE_BLOCK_END;
}

$code_css = Array(
  'operator',	// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',	// module, import と pragma
  'system',	// 処理系組み込みの奴 __stdcall とか
  'environment',	// 環境変数 
  );

$code_keyword = Array(
	//'operator',	// オペレータ関数
	//'identifier',	// その他の識別子
	// 制御構文系
	'cmdextversion' => 2,
	'defined' => 2,
	'do' => 2,
	'else' => 2,
	'errorlevel' => 2,
	'exist' => 2,
	'exit' => 2,
	'for' => 2,
	'goto' => 2,
	'if' => 2,
	'not' => 2,
	//'pragma',	// module, import と pragma
	//'system',	// 処理系組み込みの奴 __stdcall とか
	// 内部コマンド系
	'assoc' => 4,
	'break' => 4,
	'call' => 4,
	'cd' => 4,
	'chdir' => 4,
	'cls' => 4,
	'color' => 4,
	'copy' => 4,
	'date' => 4,
	'dir' => 4,
	'del' => 4,
	'echo' => 4,
	'endlocal' => 4,
	'erase' => 4,
	'ftype' => 4,
	'md' => 4,
	'mkdir' => 4,
	'move' => 4,
	'path' => 4,
	'pause' => 4,
	'popd' => 4,
	'prompt' => 4,
	'pushd' => 4,
	'rd' => 4,
	'ren' => 4,
	'rename' => 4,
	'rmdir' => 4,
	'set' => 4,
	'setlocal' => 4,
	'shift' => 4,
	'start' => 4,
	'time' => 4,
	'title' => 4,
	'type' => 4,
	'ver' => 4,
	'verify' => 4,
	'vol' => 4,
	// 外部コマンド系
	'at' => 4,
	'attrib' => 4,
	'cacls' => 4,
	'chcp' => 4,
	'chkdsk' => 4,
	'chkntfs' => 4,
	'cmd' => 4,
	'comp' => 4,
	'compact' => 4,
	'convert' => 4,
	'diskcomp' => 4,
	'doskey' => 4,
	'fc' => 4,
	'find' => 4,
	'findstr' => 4,
	'format' => 4,
	'graftable' => 4,
	'help' => 4,
	'label' => 4,
	'mode' => 4,
	'more' => 4,
	'print' => 4,
	'recover' => 4,
	'replace' => 4,
	'sort' => 4,
	'subst' => 4,
	'tree' => 4,
	'xcopy' => 4,
	//'environment',	// 環境変数
	'alluserprofile' => 5,
	'appdata' => 5,
//	'cd' => 5,
	'cmdcmdline' => 5,
	'cmdextversion' => 5,
	'computername' => 5,
	'comspec' => 5,
//	'date' => 5,
//	'errorlevel' => 5,
	'homedrive' => 5,
	'homepath' => 5,
	'homeshare' => 5,
	'logonserver' => 5,
	'number_of_processors' => 5,
	'os' => 5,
//	'path' => 5,
	'pathext' => 5,
	'processor_architecture' => 5,
	'processor_identifier' => 5,
	'processor_level' => 5,
	'processor_revision' => 5,
//	'prompt' => 5,
	'random' => 5,
	'systemdrive' => 5,
	'systemroot' => 5,
	'temp' => 5,
	'tmp' => 5,
//	'time' => 5,
	'userdomain' => 5,
	'username' => 5,
	'userprofile' => 5,
	'windir' => 5,
);
?>