<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.ini.php,v 1.18.3 2005/05/16 03:04:14 miko Exp $
//
// PukiWiki setting file (user agent:default)

/////////////////////////////////////////////////
// Skin file
if (defined('TDIARY_THEME')) { 
//	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'tdiary.skin.php');
	define('SKIN_FILE_DEFAULT', DATA_HOME . SKIN_DIR . 'tdiary.skin.php'); 
} else {
//	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'pukiwiki.skin.php');
	define('SKIN_FILE_DEFAULT', DATA_HOME . SKIN_DIR . 'default.skin.php');
}
$skin_file = SKIN_FILE_DEFAULT;

/////////////////////////////////////////////////
// �����Ȥ���ڡ������ɤ߹��ߤ��ǽ�ˤ���
$load_template_func = 0;

/////////////////////////////////////////////////
// ����ʸ�����ʬ������
$search_word_color = 1;

/////////////////////////////////////////////////
// �����ڡ�����Ƭʸ������ǥå�����Ĥ���
$list_index = 1;

/////////////////////////////////////////////////
// �ü쥷��ܥ�
$_symbol_paraedit = '<img src="./image/plus/paraedit.png" width="9" height="9" alt="Edit" title="Edit" />';
$_symbol_extanchor = '<img src="./image/plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\'$1\', \'$2\');" />';
$_symbol_innanchor = '<img src="./image/plus/inn.png" alt="" title="" class="inn" onclick="return open_uri(\'$1\', \'$2\');" />';

/////////////////////////////////////////////////
// �ꥹ�ȹ�¤�κ��ޡ�����
$_ul_left_margin = 0;   // �ꥹ�ȤȲ��̺�ü�Ȥδֳ�(px)
$_ul_margin = 16;       // �ꥹ�Ȥγ��ش֤δֳ�(px)
$_ol_left_margin = 0;   // �ꥹ�ȤȲ��̺�ü�Ȥδֳ�(px)
$_ol_margin = 16;       // �ꥹ�Ȥγ��ش֤δֳ�(px)
$_dl_left_margin = 0;   // �ꥹ�ȤȲ��̺�ü�Ȥδֳ�(px)
$_dl_margin = 16;        // �ꥹ�Ȥγ��ش֤δֳ�(px)
$_list_pad_str = ' class="list%d" style="padding-left:%dpx;margin-left:%dpx"';

/////////////////////////////////////////////////
// �ƥ����ȥ��ꥢ�Υ�����
$cols = 80;

/////////////////////////////////////////////////
// �ƥ����ȥ��ꥢ�ιԿ�
$rows = 20;

/////////////////////////////////////////////////
// �硦�����Ф������ܼ�������󥯤�ʸ��
$top = $_msg_content_back_to_top;

/////////////////////////////////////////////////
// ��Ϣ�ڡ���ɽ���Υڡ���̾�ζ��ڤ�ʸ��
$related_str = "\n ";

/////////////////////////////////////////////////
// �����롼��Ǥδ�Ϣ�ڡ���ɽ���Υڡ���̾�ζ��ڤ�ʸ��
$rule_related_str = "</li>\n<li>";

/////////////////////////////////////////////////
// ��ʿ���Υ���
$hr = '<hr class="full_hr" />';

/////////////////////////////////////////////////
// ����ǽ��Ϣ

// ����Υ��󥫡������Хѥ���ɽ������ (0 = ���Хѥ�)
//  * ���Хѥ��ξ�硢�����ΥС�������Opera������ˤʤ뤳�Ȥ�����ޤ�
//  * ���Хѥ��ξ�硢calendar_viewer�ʤɤ�����ˤʤ뤳�Ȥ�����ޤ�
// (�ܤ�����: BugTrack/698)
define('PKWK_ALLOW_RELATIVE_FOOTNOTE_ANCHOR', 1);

// ʸ��������ľ����ɽ�����륿��
$note_hr = '<hr class="note_hr" />';

/////////////////////////////////////////////////
// ��Ϣ�����󥯤���ɽ������(��ô��������ޤ�)
$related_link = 1;

/////////////////////////////////////////////////
// WikiName,BracketName�˷в���֤��ղä���
$show_passage = 1;

/////////////////////////////////////////////////
// ���ɽ���򥳥�ѥ��Ȥˤ���
$link_compact = 0;

/////////////////////////////////////////////////
// �ե������ޡ�������Ѥ���
$usefacemark = 1;

/////////////////////////////////////////////////
// �桼������롼��
//
//  ����ɽ���ǵ��Ҥ��Ƥ���������?(){}-*./+\$^|�ʤ�
//  �� \? �Τ褦�˥������Ȥ��Ƥ���������
//  �����ɬ�� / ��ޤ�Ƥ�����������Ƭ����� ^ ��Ƭ�ˡ�
//  ��������� $ ����ˡ�
//
/////////////////////////////////////////////////
// �桼������롼��(����С��Ȼ����ִ�)
$line_rules = array(
	'COLOR\(([^\(\)]*)\){([^}]*)}'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\){([^}]*)}'	=> '<span style="font-size:$1px">$2</span>',
	'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'	=> '<span class="size$1">$2</span>',
	'SUP{([^}]*)}' => '<span style="font-size:60%;vertical-align:super;">$1</span>',
	'SUB{([^}]*)}' => '<span style="font-size:60%;vertical-align:sub;">$1</span>',
	'%%%(?!%)((?:(?!%%%).)*)%%%'	=> '<ins>$1</ins>',
	'%%(?!%)((?:(?!%%).)*)%%'	=> '<del>$1</del>',
	"'''(?!')((?:(?!''').)*)'''"	=> '<em>$1</em>',
	"''(?!')((?:(?!'').)*)''"	=> '<strong>$1</strong>',
);

/////////////////////////////////////////////////
// �ե������ޡ�������롼��(����С��Ȼ����ִ�)

// $usefacemark = 1�ʤ�ե������ޡ������ִ�����ޤ�
// ʸ�����XD�ʤɤ����ä�����facemark���ִ�����Ƥ��ޤ��Τ�
// ɬ�פΤʤ����� $usefacemark��0�ˤ��Ƥ���������

$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/smile.png" />',
	'\s(\:D)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'\s(\:p)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'\s(\:d)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'\s(XD)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'\s(X\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'\s(;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'\s(;\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'\s(\:\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'&amp;(smile);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/smile.png" />',
	'&amp;(bigsmile);'=>' <img alt="[$1]" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'&amp;(huh);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/huh.png" />',
	'&amp;(oh);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/oh.png" />',
	'&amp;(wink);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/wink.png" />',
	'&amp;(sad);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/sad.png" />',
	'&amp;(heart);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/heart.png" />',
	'&amp;(worried);'=>' <img alt="[$1]" src="' . IMAGE_URI . 'face/worried.png" />',
	'&amp;(sweat);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/worried.png" />',
	'&amp;(tear);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/tear.png" />',
	'&amp;(umm);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/umm.png" />',
	'&amp;(star);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/star.gif" />',

	// Face marks, Japanese style
	'(\(\^\^\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/smile.png" />',
	'(\(\^-\^)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'(\(\^Q\^)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'(\(\.\.;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'(\(\^_-)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'(\(\^_-\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'(\(--;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'(\(\^\^;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/worried.png" />',
	'(\(\^\^;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/worried.png" />',
	'(\(\T-T)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\T-T\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\;_;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\;_;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(__;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/umm.png" />',

	// Push buttons, 0-9 and sharp (Compatibility with cell phones)
	'&amp;(pb1);'	=> '[1]',
	'&amp;(pb2);'	=> '[2]',
	'&amp;(pb3);'	=> '[3]',
	'&amp;(pb4);'	=> '[4]',
	'&amp;(pb5);'	=> '[5]',
	'&amp;(pb6);'	=> '[6]',
	'&amp;(pb7);'	=> '[7]',
	'&amp;(pb8);'	=> '[8]',
	'&amp;(pb9);'	=> '[9]',
	'&amp;(pb0);'	=> '[0]',
	'&amp;(pb#);'	=> '[#]',

	// Other icons (Compatibility with cell phones)
	'&amp;(zzz);'	=> '[zzz]',
	'&amp;(man);'	=> '[man]',
	'&amp;(clock);'	=> '[clock]',
	'&amp;(mail);'	=> '[mail]',
	'&amp;(mailto);'=> '[mailto]',
	'&amp;(phone);'	=> '[phone]',
	'&amp;(phoneto);'=>'[phoneto]',
	'&amp;(faxto);'	=> '[faxto]',
);

?>
