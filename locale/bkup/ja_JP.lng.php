<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: ja.lng.php,v 1.6.5 2005/01/15 02:51:44 miko Exp $
//
// PukiWiki message file (japanese)

// �����Υե������ʸ�������ɤϡ����󥳡��ǥ��󥰤�����Ȱ���
//   ���Ƥ���ɬ�פ�����ޤ�

// Encoding hint
$_LANG['encode_hint']['ja'] = '��';

///////////////////////////////////////
// Page titles
$_title_cannotedit = '$1 ���Խ��Ǥ��ޤ���';
$_title_edit       = '$1 ���Խ�';
$_title_preview    = '$1 �Υץ�ӥ塼';
$_title_collided   = '$1 �ǡڹ����ξ��ۤ͡������ޤ���';
$_title_updated    = '$1 �򹹿����ޤ���';
$_title_deleted    = '$1 �������ޤ���';
$_title_help       = '�إ��';
$_title_invalidwn  = 'ͭ����WikiName�ǤϤ���ޤ���';
$_title_backuplist = '�Хå����åװ���';

///////////////////////////////////////
// Messages
$_msg_unfreeze       = '�����';
$_msg_preview        = '�ʲ��Υץ�ӥ塼���ǧ���ơ��褱��Хڡ��������Υܥ���ǹ������Ƥ���������';
$_msg_preview_delete = '�ʥڡ��������Ƥ϶��Ǥ�����������Ȥ��Υڡ����Ϻ������ޤ�����';
$_msg_collided       = '���ʤ������Υڡ������Խ����Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
�����ɲä����Ԥ� +�ǻϤޤäƤ��ޤ���<br />
!�ǻϤޤ�Ԥ��ѹ����줿��ǽ��������ޤ���<br />
!��+�ǻϤޤ�Ԥ������ƺ��٥ڡ����ι�����ԤäƤ���������<br />';

$_msg_collided_auto  = '���ʤ������Υڡ������Խ����Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
��ư�Ǿ��ͤ��ä��ޤ����������꤬�����ǽ��������ޤ���<br />
��ǧ�塢[�ڡ����ι���]�򲡤��Ƥ���������<br />';

$_msg_invalidiwn     = '$1 ��ͭ���� $2 �ǤϤ���ޤ���';
$_msg_invalidpass    = '�ѥ���ɤ��ְ�äƤ��ޤ���';
$_msg_notfound       = '���ꤵ�줿�ڡ����ϸ��Ĥ���ޤ���Ǥ�����';
$_msg_addline        = '�ɲä��줿�Ԥ�<span class="diff_added">���ο�</span>�Ǥ���';
$_msg_delline        = '������줿�Ԥ�<span class="diff_removed">���ο�</span>�Ǥ���';
$_msg_goto           = '$1 �عԤ���';
$_msg_andresult      = '$1 �Τ��٤Ƥ�ޤ�ڡ����� <strong>$3</strong> �ڡ����桢 <strong>$2</strong> �ڡ������Ĥ���ޤ�����';
$_msg_orresult       = '$1 �Τ����줫��ޤ�ڡ����� <strong>$3</strong> �ڡ����桢 <strong>$2</strong> �ڡ������Ĥ���ޤ�����';
$_msg_notfoundresult = '$1 ��ޤ�ڡ����ϸ��Ĥ���ޤ���Ǥ�����';
$_msg_symbol         = '����';
$_msg_other          = '���ܸ�';
$_msg_help           = '�ƥ����������Υ롼���ɽ������';
$_msg_week           = array('��','��','��','��','��','��','��');
$_msg_content_back_to_top = '<div class="jumpmenu"><a href="#header">��</a>&nbsp;<a href="#footer">��</a></div>';
$_msg_word           = '�����Υ�����ɤ��ϥ��饤�Ȥ���Ƥ��ޤ���';

///////////////////////////////////////
// Symbols
//$_symbol_anchor   = '&dagger;';
$_symbol_anchor   = '';
$_symbol_noexists = '?';

///////////////////////////////////////
// Form buttons
$_btn_preview   = '�ץ�ӥ塼';
$_btn_repreview = '���٥ץ�ӥ塼';
$_btn_update    = '�ڡ����ι���';
$_btn_cancel    = '����󥻥�';
$_btn_notchangetimestamp = '�����ॹ����פ��ѹ����ʤ�';
$_btn_addtop    = '�ڡ����ξ���ɲ�';
$_btn_template  = '�����Ȥ���ڡ���';
$_btn_load      = '�ɹ�';
$_btn_edit      = '�Խ�';
$_btn_delete    = '���';

///////////////////////////////////////
// Authentication
$_title_cannotread = '$1 �ϱ����Ǥ��ޤ���';
$_msg_auth         = 'PukiWikiAuth';

///////////////////////////////////////
// Help 'wiki format rule'.
$rule_page = '�����롼��';
$help_page = '�إ��';

///////////////////////////////////////
// TrackBack
$_tb_title  = 'TrackBack: %s �ؤε����Ϸ�³�����...';
$_tb_header = '�ǥ������å����η�³...';
$_tb_entry  = '���Υ���ȥ�� TrackBack URL:';
$_tb_refer  = ' ���ΰ����ϡ����Υ���ȥ�򻲾Ȥ��Ƥ��ޤ�: %s, %s.';
$_tb_header_Excerpt = '����:';
$_tb_header_Weblog  = 'Weblog:';
$_tb_header_Tracked = '����:';
$_tb_date  = 'Yǯn��j�� H:i:s';

/////////////////////////////////////////////////
// ��̾��̤�����ξ���ɽ�� (article)
$_no_subject = '̵��';

/////////////////////////////////////////////////
// ̾����̤�����ξ���ɽ�� (article, comment, pcomment)
$_no_name = '';

/////////////////////////////////////////////////
// Skin
/////////////////////////////////////////////////

$_LANG['skin']['add']       = '�ɲ�';
$_LANG['skin']['backup']    = '�Хå����å�';
$_LANG['skin']['copy']      = 'ʣ��';
$_LANG['skin']['diff']      = '��ʬ';
$_LANG['skin']['edit']      = '�Խ�';
$_LANG['skin']['filelist']  = '�ե�����̾����';	// List of filenames
$_LANG['skin']['freeze']    = '���';
$_LANG['skin']['help']      = '�إ��';
$_LANG['skin']['list']      = '����';	// List of pages
$_LANG['skin']['new']       = '����';
$_LANG['skin']['rdf']       = '�ǽ�������RDF';  // RDF of RecentChanges
$_LANG['skin']['recent']    = '�ǽ�����';	// RecentChanges
$_LANG['skin']['refer']     = '��󥯸�';	// Show list of referer
$_LANG['skin']['reload']    = '�����';
$_LANG['skin']['rename']    = '̾���ѹ�';	// Rename a page (and related)
$_LANG['skin']['rss']       = '�ǽ�������RSS';	// RSS of RecentChanges
$_LANG['skin']['rss10']     = & $_LANG['skin']['rss']; 
$_LANG['skin']['rss20']     = & $_LANG['skin']['rss']; 
$_LANG['skin']['mixirss']   = & $_LANG['skin']['rss']; 
$_LANG['skin']['search']    = 'ñ�측��';
$_LANG['skin']['top']       = '�ۡ���';	// Top page
$_LANG['skin']['trackback'] = 'Trackback';	// Show list of trackback
$_LANG['skin']['unfreeze']  = '�����';
$_LANG['skin']['upload']    = 'ź��';	// Attach a file

///////////////////////////////////////
// Plug-in message
///////////////////////////////////////
// add.inc.php
$_title_add = '$1 �ؤ��ɲ�';
$_msg_add   = '�ڡ����ؤ��ɲäϡ����ߤΥڡ������Ƥ˲��Ԥ���Ĥ��������Ƥ��ɲä���ޤ���';

///////////////////////////////////////
// article.inc.php
$_btn_name    = '��̾��';
$_btn_article = '���������';
$_btn_subject = '��̾: ';
$_msg_article_mail_sender = '��Ƽ�: ';
$_msg_article_mail_page   = '�����: ';


///////////////////////////////////////
// attach.inc.php
$_attach_messages = array(
	'msg_uploaded' => '$1 �˥��åץ��ɤ��ޤ���',
	'msg_deleted'  => '$1 ����ե�����������ޤ���',
	'msg_freezed'  => 'ź�եե��������뤷�ޤ�����',
	'msg_unfreezed'=> 'ź�եե��������������ޤ�����',
	'msg_upload'   => '$1 �ؤ�ź��',
	'msg_info'     => 'ź�եե�����ξ���',
	'msg_confirm'  => '<p>%s �������ޤ���</p>',
	'msg_list'     => 'ź�եե��������',
	'msg_listpage' => '$1 ��ź�եե��������',
	'msg_listall'  => '���ڡ�����ź�եե��������',
	'msg_file'     => 'ź�եե�����',
	'msg_maxsize'  => '���åץ��ɲ�ǽ����ե����륵������ %s �Ǥ���',
	'msg_count'    => ' <span class="small">%s��</span>',
	'msg_password' => '�ѥ����',
	'msg_adminpass'=> '�����ԥѥ����',
	'msg_delete'   => '���Υե�����������ޤ���',
	'msg_freeze'   => '���Υե��������뤷�ޤ���',
	'msg_unfreeze' => '���Υե��������������ޤ���',
	'msg_isfreeze' => '���Υե��������뤵��Ƥ��ޤ���',
	'msg_require'  => '(�����ԥѥ���ɤ�ɬ�פǤ�)',
	'msg_filesize' => '������',
	'msg_date'     => '��Ͽ����',
	'msg_dlcount'  => '����������',
	'msg_md5hash'  => 'MD5�ϥå�����',
	'msg_page'     => '�ڡ���',
	'msg_filename' => '��Ǽ�ե�����̾',
	'err_noparm'   => '$1 �ؤϥ��åץ��ɡ�����ϤǤ��ޤ���',
	'err_exceed'   => '$1 �ؤΥե����륵�������礭�����ޤ�',
	'err_exists'   => '$1 ��Ʊ���ե�����̾��¸�ߤ��ޤ�',
	'err_notfound' => '$1 �ˤ��Υե�����ϸ��Ĥ���ޤ���',
	'err_noexist'  => 'ź�եե����뤬����ޤ���',
	'err_delete'   => '$1 ����ե���������Ǥ��ޤ���Ǥ���',
	'err_password' => '�ѥ���ɤ����פ��ޤ���',
	'err_adminpass'=> '�����ԥѥ���ɤ����פ��ޤ���',
	'btn_upload'   => '���åץ���',
	'btn_info'     => '�ܺ�',
	'btn_submit'   => '�¹�'
);

///////////////////////////////////////
// back.inc.php
$_msg_back_word = '���';

///////////////////////////////////////
// backup.inc.php
$_title_backup_delete  = '$1 �ΥХå����åפ���';
$_title_backupdiff     = '$1 �ΥХå����å׺�ʬ(No.$2)';
$_title_backupnowdiff  = '$1 �ΥХå����åפθ��ߤȤκ�ʬ(No.$2)';
$_title_backupvisualdiff = '$1 �ΥХå����åפθ��ߤȤκ�ʬ(No.$2) - Visual';
$_title_backupsource   = '$1 �ΥХå����åץ�����(No.$2)';
$_title_backup         = '$1 �ΥХå����å�(No.$2)';
$_title_pagebackuplist = '$1 �ΥХå����åװ���';
$_title_backuplist     = '�Хå����åװ���';
$_msg_backup_deleted   = '$1 �ΥХå����åפ������ޤ�����';
$_msg_backup_adminpass = '����ѤΥѥ���ɤ����Ϥ��Ƥ���������';
$_msg_backuplist       = '�Хå����åװ���';
$_msg_nobackup         = '$1 �ΥХå����åפϤ���ޤ���';
$_msg_diff             = '��ʬ';
$_msg_nowdiff          = '���ߤȤκ�ʬ';
$_msg_visualdiff       = '���ߤȤκ�ʬ - Visual';
$_msg_source           = '������';
$_msg_backup           = '�Хå����å�';
$_msg_view             = '$1 ��ɽ��';
$_msg_deleted          = '$1 �Ϻ������Ƥ��ޤ���';

///////////////////////////////////////
// calendar_viewer.inc.php
$_err_calendar_viewer_param2 = '��2�������Ѥ���';
$_msg_calendar_viewer_right  = '����%d��&gt;&gt;';
$_msg_calendar_viewer_left   = '&lt;&lt;����%d��';
$_msg_calendar_viewer_restrict = '$1 �ϱ������¤������äƤ��뤿��calendar_viewer�ˤ�뻲�ȤϤǤ��ޤ���';

///////////////////////////////////////
// calendar2.inc.php
$_calendar2_plugin_edit  = '[�����������Խ�]';
$_calendar2_plugin_empty = '%s�϶��Ǥ���';

///////////////////////////////////////
// comment.inc.php
$_btn_name    = '��̾��: ';
$_btn_comment = '�����Ȥ�����';
$_msg_comment = '������: ';
$_msg_comment_help = '<br /><script type="text/javascript" src="skin/assistant.js"></script>';
$_title_comment_collided = '$1 �ǡڹ����ξ��ۤ͡������ޤ���';
$_msg_comment_collided   = '���ʤ������Υڡ������Խ����Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
�����Ȥ��ɲä��ޤ��������㤦���֤���������Ƥ��뤫�⤷��ޤ���<br />';

///////////////////////////////////////
// deleted.inc.php
$_deleted_plugin_title = '����ڡ����ΰ���';
$_deleted_plugin_title_withfilename = '����ڡ����ե�����ΰ���';

///////////////////////////////////////
// diff.inc.php
$_title_diff = '$1 ���ѹ���';
$_title_diff_delete  = '$1 �κ�ʬ����';
$_msg_diff_deleted   = '$1 �κ�ʬ�������ޤ�����';
$_msg_diff_adminpass = '����ѤΥѥ���ɤ����Ϥ��Ƥ���������';

///////////////////////////////////////
// filelist.inc.php (list.inc.php)
$_title_filelist = '�ڡ����ե�����ΰ���';

///////////////////////////////////////
// freeze.inc.php
$_title_isfreezed = '$1 �Ϥ��Ǥ���뤵��Ƥ��ޤ�';
$_title_freezed   = '$1 ����뤷�ޤ���';
$_title_freeze    = '$1 �����';
$_msg_freezing    = '����ѤΥѥ���ɤ����Ϥ��Ƥ���������';
$_btn_freeze      = '���';

///////////////////////////////////////
// insert.inc.php
$_btn_insert = '�ɲ�';

///////////////////////////////////////
// include.inc.php
$_msg_include_restrict = '$1 �ϱ������¤������äƤ��뤿��include�Ǥ��ޤ���';

///////////////////////////////////////
// interwiki.inc.php
$_title_invalidiwn = 'ͭ����InterWikiName�ǤϤ���ޤ���';

///////////////////////////////////////
// list.inc.php
$_title_list = '�ڡ����ΰ���';

///////////////////////////////////////
// ls2.inc.php
$_ls2_err_nopages = '<p>\'$1\' �ˤϡ������ؤΥڡ���������ޤ���</p>';
$_ls2_msg_title   = '\'$1\'�ǻϤޤ�ڡ����ΰ���';

///////////////////////////////////////
// memo.inc.php
$_btn_memo_update = '��⹹��';

///////////////////////////////////////
// navi.inc.php
$_navi_prev = 'Prev';
$_navi_next = 'Next';
$_navi_up   = 'Up';
$_navi_home = 'Home';

///////////////////////////////////////
// newpage.inc.php
$_msg_newpage = '�ڡ�����������';

///////////////////////////////////////
// paint.inc.php
$_paint_messages = array(
	'field_name'    => '��̾��',
	'field_filename'=> '�ե�����̾',
	'field_comment' => '������',
	'btn_submit'    => 'paint',
	'msg_max'       => '(���� %d x %d)',
	'msg_title'     => 'Paint and Attach to $1',
	'msg_title_collided' => '$1 �ǡڹ����ξ��ۤ͡������ޤ���',
	'msg_collided'  => '���ʤ����������Խ����Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
�����ȥ����Ȥ��ɲä��ޤ��������㤦���֤���������Ƥ��뤫�⤷��ޤ���<br />'
);

///////////////////////////////////////
// pcomment.inc.php
$_pcmt_messages = array(
	'btn_name'     => '��̾��: ',
	'btn_comment'  => '�����Ȥ�����',
	'msg_comment'  => '������: ',
	'msg_recent'   => '�ǿ���%d���ɽ�����Ƥ��ޤ���',
	'msg_all'      => '�����ȥڡ����򻲾�',
	'msg_none'     => '�����ȤϤ���ޤ���',
	'title_collided' => '$1 �ǡڹ����ξ��ۤ͡������ޤ���',
	'msg_collided' => '���ʤ������Υڡ������Խ����Ƥ���֤ˡ�¾�οͤ�Ʊ���ڡ����򹹿����Ƥ��ޤä��褦�Ǥ���<br />
�����Ȥ��ɲä��ޤ��������㤦���֤���������Ƥ��뤫�⤷��ޤ���<br />',
	'err_pagename' => '�ڡ���̾ [[%s]] �ϻ��ѤǤ��ޤ��� �������ڡ���̾����ꤷ�Ƥ���������',
);
$_msg_pcomment_restrict = '�������¤������äƤ��뤿�ᡢ$1����ϥ����Ȥ��ɤߤ��ळ�Ȥ��Ǥ��ޤ���';

///////////////////////////////////////
// popular.inc.php
$_popular_plugin_frame       = '<h5>�͵���%d��</h5><div>%s</div>';
$_popular_plugin_today_frame = '<h5>������%d��</h5><div>%s</div>';

///////////////////////////////////////
// recent.inc.php
$_recent_plugin_frame = '<h5>�ǿ���%d��</h5>
<div>%s</div>';

///////////////////////////////////////
// referer.inc.php
$_referer_msg = array(
	'msg_H0_Refer'       => '��󥯸���ɽ��',
	'msg_Hed_LastUpdate' => '�ǽ���������',
	'msg_Hed_1stDate'    => '�����Ͽ����',
	'msg_Hed_RefCounter' => '������',
	'msg_Hed_Referer'    => 'Referer',
	'msg_Fmt_Date'       => 'Yǯn��j�� H:i',
	'msg_Chr_uarr'       => '��',
	'msg_Chr_darr'       => '��',
);

///////////////////////////////////////
// rename.inc.php
$_rename_messages  = array(
	'err' => '<p>���顼:%s</p>',
	'err_nomatch'    => '�ޥå�����ڡ���������ޤ���',
	'err_notvalid'   => '��͡����Υڡ���̾������������ޤ���',
	'err_adminpass'  => '�����ԥѥ���ɤ�����������ޤ���',
	'err_notpage'    => '%s�ϥڡ���̾�ǤϤ���ޤ���',
	'err_norename'   => '%s���͡��ह�뤳�ȤϤǤ��ޤ���',
	'err_already'    => '�ڡ��������Ǥ�¸�ߤ��ޤ���:%s',
	'err_already_below' => '�ʲ��Υե����뤬���Ǥ�¸�ߤ��ޤ���',
	'msg_title'      => '�ڡ���̾���ѹ�',
	'msg_page'       => '�ѹ����ڡ��������',
	'msg_regex'      => '����ɽ�����ִ�',
	'msg_related'    => '��Ϣ�ڡ���',
	'msg_do_related' => '��Ϣ�ڡ������͡��ह��',
	'msg_rename'     => '%s��̾�����ѹ����ޤ���',
	'msg_oldname'    => '���ߤ�̾��',
	'msg_newname'    => '������̾��',
	'msg_adminpass'  => '�����ԥѥ����',
	'msg_arrow'      => '��',
	'msg_exist_none' => '���Υڡ�����������ʤ�',
	'msg_exist_overwrite' => '���Υե�������񤭤���',
	'msg_confirm'    => '�ʲ��Υե�������͡��ष�ޤ���',
	'msg_result'     => '�ʲ��Υե�������񤭤��ޤ�����',
	'btn_submit'     => '�¹�',
	'btn_next'       => '����'
);

///////////////////////////////////////
// search.inc.php
$_title_search  = 'ñ�측��';
$_title_result  = '$1 �θ������';
$_msg_searching = '���ƤΥڡ�������ñ��򸡺����ޤ�����ʸ����ʸ���ζ��̤Ϥ���ޤ���';
$_btn_search    = '����';
$_btn_and       = 'AND����';
$_btn_or        = 'OR����';

///////////////////////////////////////
// source.inc.php
$_source_messages = array(
	'msg_title'    => '$1�Υ�����',
	'msg_notfound' => '$1�����Ĥ���ޤ���',
	'err_notfound' => '�ڡ����Υ�������ɽ���Ǥ��ޤ���'
);

///////////////////////////////////////
// template.inc.php
$_msg_template_start   = '���Ϲ�:<br />';
$_msg_template_end     = '��λ��:<br />';
$_msg_template_page    = '$1/ʣ��';
$_msg_template_refer   = '�ڡ���̾:';
$_msg_template_force   = '��¸�Υڡ���̾���Խ�����';
$_err_template_already = '$1 �Ϥ��Ǥ�¸�ߤ��ޤ���';
$_err_template_invalid = '$1 ��ͭ���ʥڡ���̾�ǤϤ���ޤ���';
$_btn_template_create  = '����';
$_title_template       = '$1 ��ƥ�ץ졼�Ȥˤ��ƺ���';

///////////////////////////////////////
// tracker.inc.php
$_tracker_messages = array(
	'msg_list'   => '$1 �ι��ܰ���',
	'msg_back'   => '<p>$1</p>',
	'msg_limit'  => '��$1���桢���$2���ɽ�����Ƥ��ޤ���',
	'btn_page'   => '�ڡ���̾',
	'btn_name'   => '�ڡ���̾',
	'btn_real'   => '�ڡ���̾',
	'btn_submit' => '�ɲ�',
	'btn_date'   => '����',
	'btn_refer'  => '����',
	'btn_base'   => '����',
	'btn_update' => '��������',
	'btn_past'   => '�в�',
);

///////////////////////////////////////
// unfreeze.inc.php
$_title_isunfreezed = '$1 ����뤵��Ƥ��ޤ���';
$_title_unfreezed   = '$1 �����������ޤ���';
$_title_unfreeze    = '$1 �������';
$_msg_unfreezing    = '������ѤΥѥ���ɤ����Ϥ��Ƥ���������';
$_btn_unfreeze      = '�����';

///////////////////////////////////////
// versionlist.inc.php
$_title_versionlist = '�����ե�����ΥС���������';

///////////////////////////////////////
// vote.inc.php
$_vote_plugin_choice = '�����';
$_vote_plugin_votes  = '��ɼ';

///////////////////////////////////////
// yetlist.inc.php
$_title_yetlist = '̤�����Υڡ�������';
$_err_notexist  = '̤�����Υڡ����Ϥ���ޤ���';
?>
