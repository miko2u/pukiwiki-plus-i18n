<?php
/**
 * TODO plugin for PukiWiki
 *
 * 指定ページ以下の [TODO] 表記などのタグを拾って一覧を生成する。
 * 名前の通り元々は TODO の一覧管理に使っていたが、Wiki 空間から
 * 横断的に（サブ）トピック的に拾えるように任意のタグ名で拾える
 * ように拡張し現在の形になった。
 *
 * 意図としては TopicName/SubTopic 形式だと構造が硬直化するので、
 * フラット階層で使いつつ「/」的なグルーピングを可能にするのが目的。
 *
 * 使い方は
 *
 *   #todo(BaseTopic, token) // BaseTopic ページ以下の [token] 行を出す
 *   #todo('', token)        // 全ページ中の [token] 行を出す
 *
 * で。また、間違ったパラメータが出た時はヘルプを出す。
 *
 * @author  Taisuke Yamada <tai@iij.ad.jp>
 * @version 0.01
 *
 * MODIFICATION
 * 2006-08-26 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * 2007-11-26 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 *            - BaseTopic を指定した場合の文書名は、BaseTopic を外して表示
 *            - token を外して表示
 *            - ファイル名順で整列
 *            - 番号付き・番号なしリストの定義を追加(TODOが増えると、番号がないと厳しい)
 * 2008-06-25 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 *            - NOTICE 対策
 */

defined('TODO_LABEL_PREFIX') or define('TODO_LABEL_PREFIX','+');

/***************************************************************************
 * プラグインモジュールインタフェースの実装
 ***************************************************************************/

/**
 * 直接 URL で ?plugin=todo 指定された場合の呼び出しエントリポイント。
 *
 * @returns $result = array(msg => $pagename, body => $content)
 */
function plugin_todo_action()
{
	global $vars;

	$page = strip_bracket($vars['page']);
	$body = todo_generate_index($vars, $page, $vars['mark']);
	$mesg = array('msg' => 'TodoIndex', 'body' => $body);

# echo "<pre>"; print_r($mesg); exit;

	return $mesg;
}

/**
 * ページ内で #todo 指定された場合の呼び出しエントリポイント。
 *
 * @returns $result = $content
 */
function plugin_todo_convert()
{
	global $vars, $script;

	// default is to traverse all subtopics under own topic
	$page = strip_bracket($vars['page']);

	switch (func_num_args()) {
	case 2:
		$mark = func_get_arg(1);
	case 1:
		$page = func_get_arg(0);
	default:
	}

	$body = todo_generate_index($vars, $page, $mark);

# echo "<pre>"; print_r($body); exit;

	return $body;
}

/***************************************************************************
 * 内部関数
 ***************************************************************************/
function todo_generate_index($vars, $page, $mark)
{
	$page = trim($page);
	$page_len = ($page == "''") ? 0 : strlen($page);
	$mark = trim($mark);
	if (empty($mark)) $mark = 'todo';

	$todo = todo_search($vars, $page, $mark);
	$html = '';

	foreach ($todo as $page => $list) {
		//sort($list);
		foreach ($list as $line) {
			$msg = substr($line,strlen($mark)+2);
			$name = ($page_len > 0) ? substr($page,$page_len) : $page;
			if (substr($name,0,1) == '/') $name = substr($name,1);
			$html .= TODO_LABEL_PREFIX . ' ' . $msg;
			// 自身のページ
			if (! empty($name)) {
				$html .= ' ([[' . $name. '>' . $page . "]])\n";
			} else {
				$html .= "\n";
			}

		}
	}
	return convert_html($html);
}

function todo_search($vars, $page, $mark)
{
	if ($page == "''") $page = '';

	// 検索対象を選択
	foreach (auth::get_existpages() as $file => $name) {
		if (strncmp($name, $page, strlen($page)) == 0) {
			$scan[$file] = $name;
		}
	}

	// 探索するマーク行のパターン
	// $expr = "/^[\*\-\s]*(\[".$mark."\].*)/i";
	$expr = "/^[\*\-\+\s]*(\[".$mark."\].*)/i";

	// [TODO] マークされているエントリを探す
	$link = array();
	foreach ($scan as $file => $name) {
		foreach (get_source($name) as $line) {
			$line = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',$line);
			if (preg_match($expr, $line, $match)) {
				$link[$name][] = $match[1];
			}
		}
	}

	ksort($link);
	return $link;
}

?>
