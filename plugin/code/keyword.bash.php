<?php
/**
 * bash キーワード定義ファイル
 */

$switchHash['$'] = PLUGIN_CODE_ESCAPE;            // $ はエスケープ
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL; // ' はエスケープしない文字列リテラル
$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['#'] = PLUGIN_CODE_COMMENT;	// コメントは # から改行まで (例外あり)
$code_comment = Array(
	'#' => Array(
				 Array('/^#[^{]/', "\n", 1),
	)
);


$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  'environment',  // 環境変数 
  );

$code_keyword = Array(
  //'operator',		// オペレータ関数
  //'identifier',	// その他の識別子
  	'for' => 2,
	'in' => 2,
  	'while' => 2,
  	'do' => 2,
  	'done' => 2,
	'until' => 2,
	'begin' => 2,
	'end' => 2,
  	'if' => 2,
  	'fi' => 2,
	'then' => 2,
  	'else' => 2,
  	'case' => 2,
	'esac' => 2,
	'break' => 2,
  	'return' => 2,
  	'selsct' => 2,
    'endif' => 2,
  //'pragma',		// module, import と pragma
  //'system',		// 処理系組み込みの奴 __stdcall とか
		'source' => 4,
		'alias' => 4,
		'bg' => 4,
		'bind' => 4,
		'break' => 4,
		'builtin' => 4,
		'cd' => 4,
		'command' => 4,
		'compgen' => 4,
		'complete' => 4,
		'continue' => 4,
		'declare' => 4,
		'typeset' => 4,
		'dirs' => 4,
		'disown' => 4,
		'echo' => 4,
		'enable' => 4,
		'eval' => 4,
		'exec' => 4,
		'exit' => 4,
		'export' => 4,
		'fc' => 4,
		'fg' => 4,
		'getopts' => 4,
		'hash' => 4,
		'help' => 4,
		'history' => 4,
		'jobs' => 4,
		'kill' => 4,
		'let' => 4,
		'local' => 4,
		'logout' => 4,
		'popd' => 4,
		'printf' => 4,
		'pushd' => 4,
		'pwd' => 4,
		'read' => 4,
		'readonly' => 4,
		'return' => 4,
		'set' => 4,
		'shift' => 4,
		'shopt' => 4,
		'suspend' => 4,
		'test' => 4,
		'times' => 4,
		'trap' => 4,
		'type' => 4,
		'ulimit' => 4,
		'umask' => 4,
		'unalias' => 4,
		'unset' => 4,
		'wait' => 4,
  //'environment',  // 環境変数 
		'PPID' => 5,
		'PWD' => 5,
		'OLDPWD' => 5,
		'REPLY' => 5,
		'UID' => 5,
		'EUID' => 5,
		'BASH' => 5,
		'BASH_VERSION' => 5,
		'SHLVL' => 5,
		'RANDOM' => 5,
		'SECONDS' => 5,
		'LINENO' => 5,
		'HISTCMD' => 5,
		'OPTARG' => 5,
		'OPTIND' => 5,
		'HOSTTYPE' => 5,
		'OSTYPE' => 5,
		'IFS' => 5,
		'PATH' => 5,
		'HOME' => 5,
		'CDPATH' => 5,
		'ENV' => 5,
		'MAIL' => 5,
		'MAILCHECK' => 5,
		'MAILPATH' => 5,
		'MAIL_WARNING' => 5,
		'PS1' => 5,
		'PS2' => 5,
		'PS3' => 5,
		'PS4' => 5,
		'HISTSIZE' => 5,
		'HISTFILE' => 5,
		'HISTFILESIZE' => 5,
		'OPTERR' => 5,
		'PROMPT_COMMAND' => 5,
		'IGNOREEOF' => 5,
		'TMOUT' => 5,
		'FCEDIT' => 5,
		'FIGNORE' => 5,
		'INPUTRC' => 5,
		'history_control' => 5,
		'HISTCONTROL' => 5,
		'command_oriented_history' => 5,
		'glob_dot_filenames' => 5,
		'allow_null_glob_expansion' => 5,
		'histchars' => 5,
		'nolinks' => 5,
		'hostname_completion_file' => 5,
		'HOSTFILE' => 5,
		'noclobber' => 5,
		'auto_resume' => 5,
		'no_exit_on_failed_exec' => 5,
		'cdable_vars' => 5,
		'horizontal-scroll-mode' => 5,
		'editing-mode' => 5,
		'mark-modified-lines' => 5,
		'bell-style' => 5,
		'comment-begin' => 5,
		'meta-flag' => 5,
		'convert-meta' => 5,
		'output-meta' => 5,
		'completion-query-items' => 5,
		'keymap' => 5,
		'show-all-if-ambiguous' => 5,
		'expand-tilde' => 5,
);
?>