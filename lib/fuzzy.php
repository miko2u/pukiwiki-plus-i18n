<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: fuzzy.php,v 0.2.1 2005/03/10 13:26:37 miko Exp $
//
// 'Search' main function
function do_search_fuzzy($word, $type = 'AND', $non_format = FALSE, $non_fuzzy = FALSE)
{
	global $script, $whatsnew, $non_list, $search_non_list;
 	global $search_auth, $search_fuzzy;

	static $fuzzypattern = array(
		'ヴァ' => 'バ',	'ヴィ' => 'ビ',	'ヴェ' => 'ベ',	'ヴォ' => 'ボ',
		'ヴ' => 'ブ',	'ヰ' => 'イ',	'ヱ' => 'エ',	'ヵ' => 'カ',
		'ァ' => 'ア',	'ィ' => 'イ',	'ゥ' => 'ウ',	'ェ' => 'エ',
		'ォ' => 'オ',	'ャ' => 'ヤ',	'ュ' => 'ユ',	'ョ' => 'ヨ');

	$retval = array();

	$b_type = ($type == 'AND'); // AND:TRUE OR:FALSE
	$keys = get_search_words(preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));

	$_pages = get_existpages();
	$pages = array();

	$non_list_pattern = '/' . $non_list . '/';
	foreach ($_pages as $page) {
		if ($page == $whatsnew || (! $search_non_list && preg_match($non_list_pattern, $page)))
			continue;

		// 検索対象ページの制限をかけるかどうか (ページ名は制限外)
		if ($search_auth && ! check_readable($page, false, false)) {
			$source = get_source(); // 検索対象ページ内容を空に。
		} else {
			$source = get_source($page);
		}
		if (! $non_format)
			array_unshift($source, $page); // ページ名も検索対象に

		$b_match = FALSE;
//miko modified
		if (!$search_fuzzy || $non_fuzzy) {
			foreach ($keys as $key) {
				$tmp     = preg_grep('/' . $key . '/', $source);
				$b_match = ! empty($tmp);
				if ($b_match xor $b_type) break;
			}
			if ($b_match) $pages[$page] = get_filetime($page);
		} else {
			$fuzzy_from = array_keys($fuzzypattern);
			$fuzzy_to = array_values($fuzzypattern);
			$words = preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY);
			$_source = mb_strtolower(mb_convert_kana(join("\n",$source), 'KVCas'));
			for ($i=0; $i<count($fuzzy_from); $i++) {
				$_source = mb_ereg_replace($fuzzy_from[$i], $fuzzy_to[$i], $_source);
			}
			$_source = mb_ereg_replace('[ッー・゛゜、。]', '', $_source);
			foreach ($keys as $key) {
				$_keyword = mb_strtolower(mb_convert_kana($word, 'KVCas'));
				for ($i=0; $i<count($fuzzy_from); $i++) {
					$_keyword = mb_ereg_replace($fuzzy_from[$i], $fuzzy_to[$i], $_keyword);
				}
				$_keyword = mb_ereg_replace('[ッー・゛゜、。]', '', $_keyword);
				$b_match = mb_ereg(mb_ereg_quote($_keyword), $_source);
			}
			if ($b_match) $pages[$page] = get_filetime($page);
		}
//miko modified
	}
	if ($non_format) return array_keys($pages);

	$r_word = rawurlencode($word);
	$s_word = htmlspecialchars($word);
	if (empty($pages))
		return str_replace('$1', $s_word, $_string['notfoundresult']);

	ksort($pages);
	$retval = '<ul>' . "\n";
	foreach ($pages as $page=>$time) {
		$r_page  = rawurlencode($page);
		$s_page  = htmlspecialchars($page);
		$passage = get_passage($time);
		$retval .= ' <li><a href="' . $script . '?cmd=read&amp;page=' .
			$r_page . '&amp;word=' . $r_word . '">' . $s_page .
			'</a>' . $passage . '</li>' . "\n";
	}
	$retval .= '</ul>' . "\n";

	$retval .= str_replace('$1', $s_word, str_replace('$2', count($pages),
		str_replace('$3', count($_pages), $b_type ? $_string['andresult'] : $_string['orresult'])));

	return $retval;
}
?>
