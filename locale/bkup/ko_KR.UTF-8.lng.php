<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $PWId: ko.UTF-8.lng.php,v 1.6.1 2005/01/15 02:51:44 miko Exp $
//
// PukiWiki message file (Korean)

// NOTE: Encoding of this file, must equal to encoding setting

// $RCSfile: ko.lng.php,v $ $Revision: 1.4 $
//
// 한글 번역은 en.lng.php를 기본으로 ja.lng.php를 번역기로
// 번역한 결과를 참고해서 했기때문에 오역의 가능성이 있습니다.

// Encoding hint
$_LANG['encode_hint']['ko_KR'] = '&#44032;';

///////////////////////////////////////
// Page titles
$_title_cannotedit = ' $1은(는) 편집할 수 없습니다.';
$_title_edit       = '$1 편집';
$_title_preview    = '$1 미리보기';
$_title_collided   = '$1을(를) 저장하는도중 충돌이 일어났습니다.';
$_title_updated    = ' $1을(를) 갱신했습니다.';
$_title_deleted    = ' $1을(를) 삭제했습니다.';
$_title_help       = '도움말';
$_title_invalidwn  = '올바른 WikiName이 아닙니다.';
$_title_backuplist = '백업목록';

// 버튼을 이 아래의 메세지들에서 불러쓰기 위해 위로 끌어올렸음.
///////////////////////////////////////
// Form buttons
$_btn_preview   = '미리보기';
$_btn_repreview = '미리보기';
$_btn_update    = '저장';
$_btn_cancel    = '취소';
$_btn_notchangetimestamp = '타임스탬프를 변경하지 않음';
$_btn_addtop    = '페이지 위에 추가';
$_btn_template  = '서식으로 사용할 페이지';
$_btn_load      = '불러오기';
$_btn_edit      = '편집';
$_btn_delete    = '삭제';

///////////////////////////////////////
// Messages
$_msg_unfreeze = '잠금해제';
$_msg_preview  = '변경된 내용을 확인후 페이지 아래쪽의 '. $_btn_update .' 버튼을 눌러 저장하세요.';
$_msg_preview_delete = '(페이지의 내용이 비어있습니다. 저장하면 이 페이지는 삭제됩니다.)';
$_msg_collided = '편집하는 동안 다른사람이 같은 페이지를 갱신해 버린것 같습니다.<br />
이번에 추가한 줄은 +로 시작되어 있습니다.<br />
!로 시작되는 줄은 변경되었을 가능성이 있습니다.<br />
+나 !로 시작되는 줄을 수정후 다시 '.$_btn_update.'하세요.';

$_msg_collided_auto = '편집하는 동안 다른사람이 같은 페이지를 갱신해 버린것 같습니다.<br />
자동으로 충돌을 해결했지만 문제가 있을 가능성이 있습니다.<br />
확인 후 '. $_btn_update .'을(를) 눌러주세요. <br />';

$_msg_invalidiwn  = ' $1은(는) 올바른 $2이(가) 아닙니다.';
$_msg_invalidpass = '비밀번호가 맞지 않습니다.';
$_msg_notfound    = '지정한 페이지를 찾지 못했습니다.';
$_msg_addline     = '추가된 줄은 <span class="diff_added">이 색</span>입니다.';
$_msg_delline     = '삭제된 줄은 <span class="diff_removed">이 색</span>입니다.';
$_msg_goto        = '$1에 가기';
$_msg_andresult   = '검색어 $1을(를) 모두 포함한 페이지는 <strong>$3</strong>페이지중 <strong>$2</strong>페이지가 발견되었습니다.';
$_msg_orresult    = '검색어 $1을(를) 하나라도 포함한 페이지는 <strong>$3</strong>페이지중 <strong>$2</strong>페이지가 발견되었습니다.';
$_msg_notfoundresult = '$1을(를) 포함한 페이지는 발견되지 않았습니다.';
$_msg_symbol      = '기호';
$_msg_other       = '기타';
$_msg_help        = '문법을 표시';
$_msg_week        = array('일','월','화','수','목','금','토');
$_msg_content_back_to_top = '<div class="jumpmenu"><a href="#navigator">&uarr;</a></div>';
$_msg_word        = '다음 검색어가 하이라이트 되었습니다:';

///////////////////////////////////////
// Symbols
$_symbol_anchor   = '&dagger;';
$_symbol_noexists = '?';


///////////////////////////////////////
// Authentication
$_title_cannotread = ' $1은(는) 열람할 수 없습니다.';
$_msg_auth         = 'PukiWikiAuth';

///////////////////////////////////////
// Help 'wiki format rule'.
$rule_page = '문법';
$help_page = '도움말';

///////////////////////////////////////
// TrackBack
$_tb_title  = '트랙백: %s 에서 토론은 계속됩니다...';
$_tb_header = '토론의 계속...';
$_tb_entry  = '이 엔트리의 트랙백 URL:';
$_tb_refer  = ' 이 목록은 다음의 엔트리를 참조하고 있습니다: %s, %s.';
$_tb_header_Excerpt = '요약:';
$_tb_header_Weblog  = '웹로그:';
$_tb_header_Tracked = '일시:';
$_tb_date   = 'Y년 n월 j일 H:i:s';

/////////////////////////////////////////////////
// No subject (article)
$_no_subject = '제목없음';

/////////////////////////////////////////////////
// No name (article,comment,pcomment)
$_no_name = '';

/////////////////////////////////////////////////
// Skin
/////////////////////////////////////////////////

$_LANG['skin']['add']       = '추가';
$_LANG['skin']['backup']    = '백업';
$_LANG['skin']['copy']      = '복사';
$_LANG['skin']['diff']      = '비교';
$_LANG['skin']['edit']      = '편집';
$_LANG['skin']['filelist']  = '페이지 파일의 목록';	// List of filenames
$_LANG['skin']['freeze']    = '잠금';
$_LANG['skin']['help']      = '도움말';
$_LANG['skin']['list']      = '목록';
$_LANG['skin']['new']       = '새페이지';
$_LANG['skin']['rdf']       = '갱신내역의 RDF';
$_LANG['skin']['recent']    = '갱신내역';	// RecentChanges
$_LANG['skin']['refer']     = '리퍼러';	// Show list of referer
$_LANG['skin']['reload']    = '새로고침';
$_LANG['skin']['rename']    = '이름변경';	// Rename a page (and related)
$_LANG['skin']['rss']       = '갱신내역의 RSS';
$_LANG['skin']['rss10']     = & $_LANG['skin']['rss'];
$_LANG['skin']['rss20']     = & $_LANG['skin']['rss'];
$_LANG['skin']['search']    = '검색';
$_LANG['skin']['top']       = '대문';	// Top page
$_LANG['skin']['trackback'] = '트랙백';	// Show list of trackback
$_LANG['skin']['unfreeze']  = '잠금해제';
$_LANG['skin']['upload']    = '첨부';	// Attach a file

///////////////////////////////////////
// Plug-in message
///////////////////////////////////////
// add.inc.php
$_title_add = '$1에 추가';
$_msg_add = '페이지에 추가하면 현재 페이지에 줄바꿈 두개와 함께 입력 내용이 추가됩니다.';


///////////////////////////////////////
// article.inc.php
$_btn_name    = '이름: ';
$_btn_article = '기사 투고';
$_btn_subject = '제목: ';
$_msg_article_mail_sender = '글쓴이: ';
$_msg_article_mail_page   = '페이지: ';

///////////////////////////////////////
// attach.inc.php
$_attach_messages = array(
	'msg_uploaded' => '$1에 파일을 올렸습니다.',
	'msg_deleted'  => '$1에서 파일을 삭제했습니다.',
	'msg_freezed'  => '첨부파일을 잠궜습니다.',
	'msg_unfreezed'=> '첨부파일의 잠금을 해제했습니다.',
	'msg_upload'   => '$1로 첨부',
	'msg_info'     => '첨부파일 정보',
	'msg_confirm'  => '<p>%s을(를) 삭제합니다.</p>',
	'msg_list'     => '첨부파일 목록',
	'msg_listpage' => '$1의 첨부파일 목록',
	'msg_listall'  => '모든 페이지의 첨부파일 목록',
	'msg_file'     => '첨부파일',
	'msg_maxsize'  => '업로드 가능한 파일의 최대 크기는 %s 입니다.',
	'msg_count'    => ' <span class="small">%s번 다운받음</span>',
	'msg_password' => '비밀번호',
	'msg_adminpass'=> '관리자 비밀번호',
	'msg_delete'   => '이 파일을 삭제합니다.',
	'msg_freeze'   => '이 파일을 잠급니다.',
	'msg_unfreeze' => '이 파일의 잠금을 해제합니다.',
	'msg_isfreeze' => '이 파일은 잠겨있습니다.',
	'msg_require'  => '(관리자 비밀번호가 필요합니다.)',
	'msg_filesize' => '크기',
	'msg_date'     => '등록일시',
	'msg_dlcount'  => '조회수',
	'msg_md5hash'  => 'MD5 hash',
	'msg_page'     => '페이지',
	'msg_filename' => '저장된 파일이름',
	'err_noparm'   => '$1에는 파일을 올리거나 지울수 없습니다.',
	'err_exceed'   => '$1에 올리기에는 파일 크기가 너무 큽니다.',
	'err_exists'   => '$1에 같은 파일명이 존재합니다.',
	'err_notfound' => '$1에서 그런 파일을 찾지 못했습니다.',
	'err_noexist'  => '첨부파일이 없습니다.',
	'err_delete'   => '$1에서 파일을 지울수 없습니다.',
	'err_password' => '비밀번호가 일치하지 않습니다.',
	'err_adminpass'=> '관리자 비밀번호가 일치하지 않습니다.',
	'btn_upload'   => '업로드',
	'btn_info'     => '자세히',
	'btn_submit'   => '실행'
);

///////////////////////////////////////
// back.inc.php
$_msg_back_word = '뒤로';

///////////////////////////////////////
// backup.inc.php
$_title_backup_delete  = '$1의 백업을 삭제';
$_title_backupdiff     = '$1의 백업을 비교(No.$2)';
$_title_backupnowdiff  = '$1의 백업을 현재와 비교(No.$2)';
$_title_backupsource   = '$1의 백업 소스(No.$2)';
$_title_backup         = '$1의 백업 (No.$2)';
$_title_pagebackuplist = '$1의 백업 목록';
$_title_backuplist     = '백업 목록';
$_msg_backup_deleted   = '$1의 백업을 삭제했습니다.';
$_msg_backup_adminpass = '삭제용 비밀번호를 입력하세요.';
$_msg_backuplist       = '백업 목록';
$_msg_nobackup         = '$1의 백업은 없습니다.';
$_msg_diff             = '비교';
$_msg_nowdiff          = '현재와 비교';
$_msg_source           = '소스';
$_msg_backup           = '백업';
$_msg_view             = '$1을(를) 보기';
$_msg_deleted          = '$1은(는) 삭제되었습니다.';

///////////////////////////////////////
// calendar_viewer.inc.php
$_err_calendar_viewer_param2   = '두번째 변수가 잘못되었습니다.';
$_msg_calendar_viewer_right    = '다음 %d개&gt;&gt;';
$_msg_calendar_viewer_left     = '&lt;&lt; 이전 %d개';
$_msg_calendar_viewer_restrict = '$1는 열람 제한이 되어있기 때문에 calendar_viewer를 사용한 참조를 할 수 없습니다.';

///////////////////////////////////////
// calendar2.inc.php
$_calendar2_plugin_edit  = '[편집]';
$_calendar2_plugin_empty = '%s은(는) 비어있습니다.';

///////////////////////////////////////
// comment.inc.php
$_btn_name    = '이름: ';
$_btn_comment = '코멘트 올리기';
$_msg_comment = '코멘트: ';
$_title_comment_collided = '$1을(를) 저장하는도중 충돌이 일어났습니다.';
$_msg_comment_collided   = '편집하는 동안 다른사람이 같은 페이지를 갱신해 버린것 같습니다.<br />
코멘트는 추가되었지만, 잘못된 위치에 삽입되었을지도 모릅니다. <br />';

///////////////////////////////////////
// deleted.inc.php
$_deleted_plugin_title = '삭제된 페이지 목록';
$_deleted_plugin_title_withfilename = '삭제된 페이지의 첨부파일 목록';

///////////////////////////////////////
// diff.inc.php
$_title_diff         = '$1의 diff';
$_title_diff_delete  = '$1의 비교를 삭제';
$_msg_diff_deleted   = '$1의 비교를 삭제했습니다.';
$_msg_diff_adminpass = '삭제용의 비밀번호를 입력하세요.';

///////////////////////////////////////
// filelist.inc.php (list.inc.php)
$_title_filelist = '페이지 파일의 목록';

///////////////////////////////////////
// freeze.inc.php
$_title_isfreezed = '$1은(는) 이미 잠겨 있습니다.';
$_title_freezed   = '$1을(를) 잠궜습니다.';
$_title_freeze    = '$1을 잠금';
$_msg_freezing    = '잠그기 위한 비밀번호를 입력하세요.';
$_btn_freeze      = '잠금';

///////////////////////////////////////
// include.inc.php
$_msg_include_restrict = '$1는 열람 제한이 되어있기 때문에 include 할 수 없습니다.';

///////////////////////////////////////
// insert.inc.php
$_btn_insert = '추가';

///////////////////////////////////////
// interwiki.inc.php
$_title_invalidiwn = '올바른 InterWikiName이 아닙니다.';

///////////////////////////////////////
// list.inc.php
$_title_list = '페이지 목록';

///////////////////////////////////////
// ls2.inc.php
$_ls2_err_nopages = '<p>\'$1\'에는 하위 페이지가 없습니다.</p>';
$_ls2_msg_title   = '\'$1\'(으)로 시작하는 페이지의 목록';

///////////////////////////////////////
// memo.inc.php
$_btn_memo_update = '메모 갱신';

///////////////////////////////////////
// navi.inc.php
$_navi_prev = '이전';
$_navi_next = '다음';
$_navi_up   = '위';
$_navi_home = '처음';

///////////////////////////////////////
// newpage.inc.php
$_msg_newpage = '새로운 페이지 만들기';

///////////////////////////////////////
// paint.inc.php
$_paint_messages = array(
	'field_name'    => '이름',
	'field_filename'=> '파일명',
	'field_comment' => '코멘트',
	'btn_submit'    => 'paint',
	'msg_max'       => '(최대 %d x %d)',
	'msg_title'     => 'Paint and Attach to  $1',
	'msg_title_collided' => '$1을(를) 저장하는도중 충돌이 일어났습니다.',
	'msg_collided'  => '편집하는 동안 다른사람이 같은 페이지를 갱신해 버린것 같습니다.<br />
그림과 코멘트는 추가되었지만, 잘못된 위치에 삽입되었을지도 모릅니다. <br />'
);

///////////////////////////////////////
// pcomment.inc.php
$_pcmt_messages = array(
	'btn_name'       => '이름: ',
	'btn_comment'    => '코멘트 올리기',
	'msg_comment'    => '코멘트: ',
	'msg_recent'     => '최근 %d개의 코멘트를 표시합니다.',
	'msg_all'        => '코멘트 페이지로',
	'msg_none'       => '코멘트가 없습니다.',
	'title_collided' => '$1을(를) 저장하는도중 충돌이 일어났습니다.',
	'msg_collided'   => '편집하는 동안 다른사람이 같은 페이지를 갱신해 버린것 같습니다.<br />
코멘트는 추가되었지만, 잘못된 위치에 삽입되었을지도 모릅니다. <br />',
	'err_pagename'   => '페이지 이름 [[%s]]은(는) 사용할 수 없습니다. 올바른 페이지 이름을 지정하세요.',
);
$_msg_pcomment_restrict = '$1은(는) 열람 제한이 되어있기 때문에 코멘트를 읽어들일 수 없습니다.';

///////////////////////////////////////
// popular.inc.php
$_popular_plugin_frame       = '<h5>인기있는 %d개</h5><div>%s</div>';
$_popular_plugin_today_frame = '<h5>오늘의 %d개</h5><div>%s</div>';

///////////////////////////////////////
// recent.inc.php
$_recent_plugin_frame = '<h5>최신 글 %d개</h5>
 <div>%s</div>';

///////////////////////////////////////
// referer.inc.php
$_referer_msg = array(
	'msg_H0_Refer'       => '방문경로',
	'msg_Hed_LastUpdate' => '최종 갱신 일시',
	'msg_Hed_1stDate'    => '최초 등록 일시',
	'msg_Hed_RefCounter' => '카운터',
	'msg_Hed_Referer'    => '방문경로',
	'msg_Fmt_Date'       => 'Y년 n월 j일 H:i',
	'msg_Chr_uarr'       => '&uArr;',
	'msg_Chr_darr'       => '&dArr;',
);

///////////////////////////////////////
// rename.inc.php
$_rename_messages  = array(
	'err'            => '<p>오류:%s</p>',
	'err_nomatch'    => '매치되는 페이지가 없습니다.',
	'err_notvalid'   => '새 페이지 이름이 올바르지 않습니다.',
	'err_adminpass'  => '관리자 비밀번호가 맞지 않습니다.',
	'err_notpage'    => '%s은(는) 페이지 이름이 아닙니다.',
	'err_norename'   => '%s의 이름을 변경할 수 없습니다.',
	'err_already'    => '페이지가 이미 존재합니다: %s',
	'err_already_below' => '다음의 파일이 이미 존재합니다.',
	'msg_title'      => '페이지 이름 변경',
	'msg_page'       => '변경할 페이지 이름 지정',
	'msg_regex'      => '정규 표현식으로 치환',
	'msg_related'    => '관련 페이지',
	'msg_do_related' => '관련 페이지의 이름도 변경',
	'msg_rename'     => '%s의 이름을 변경합니다.',
	'msg_oldname'    => '현재 페이지 이름',
	'msg_newname'    => '새 페이지 이름',
	'msg_adminpass'  => '관리자 비밀번호',
	'msg_arrow'      => '->',
	'msg_exist_none' => '처리하지 않기',
	'msg_exist_overwrite' => '덮어쓰기',
	'msg_confirm'    => '다음 파일들의 이름을 변경합니다.',
	'msg_result'     => '다음 파일들을 덮어썼습니다.',
	'btn_submit'     => '실행',
	'btn_next'       => '다음'
);

///////////////////////////////////////
// search.inc.php
$_title_search  = '검색';
$_title_result  = '$1의 검색 결과';
$_msg_searching = '대소문자의 구별이 없이 모든 페이지로부터 단어를 검색합니다.';
$_btn_search    = '검색';
$_btn_and       = '<span title="모든 단어를 포함한 페이지">AND</span>';
$_btn_or        = '<span title="하나 이상의 단어를 포함한 페이지">OR</span>';

///////////////////////////////////////
// source.inc.php
$_source_messages = array(
	'msg_title'    => '$1의 소스',
	'msg_notfound' => '$1가(이) 발견되지 않았습니다.',
	'err_notfound' => '페이지의 소스를 표시할 수 없습니다.',
);

///////////////////////////////////////
// template.inc.php
$_msg_template_start   = '시작:<br />';
$_msg_template_end     = '종료:<br />';
$_msg_template_page    = '$1/복사';
$_msg_template_refer   = '페이지 이름:';
$_msg_template_force   = '기존의 페이지 이름으로 편집';
$_err_template_already = '$1은(는) 이미 존재합니다.';
$_err_template_invalid = '$1은(는) 올바른 페이지 이름이 아닙니다.';
$_btn_template_create  = '작성';
$_title_templatei      = '$1을(를) 템플릿으로 새 페이지 작성.';

///////////////////////////////////////
// tracker.inc.php
$_tracker_messages = array(
	'msg_list'   => '$1의 항목 목록',
	'msg_back'   => '<p>$1</p>',
	'msg_limit'  => '전체 $1건중 상위 $2건을 표시합니다.',
	'btn_page'   => '페이지',
	'btn_name'   => '이름',
	'btn_real'   => '실제이름',
	'btn_submit' => '추가',
	'btn_date'   => '날짜',
	'btn_refer'  => 'Refer page',
	'btn_base'   => 'Base page',
	'btn_update' => '갱신 일시',
	'btn_past'   => '경과',
);

///////////////////////////////////////
// unfreeze.inc.php
$_title_isunfreezed = '$1은(는) 잠겨있지 않습니다.';
$_title_unfreezed   = '$1의 잠금을 해제했습니다.';
$_title_unfreeze    = '$1 잠금 해제';
$_msg_unfreezing    = '잠금 해제를 위한 비밀번호를 입력하세요.';
$_btn_unfreeze      = '잠금 해제';

///////////////////////////////////////
// versionlist.inc.php
$_title_versionlist = '구성 파일의 버전 목록';

///////////////////////////////////////
// vote.inc.php
$_vote_plugin_choice = '선택사항';
$_vote_plugin_votes  = '투표';

///////////////////////////////////////
// yetlist.inc.php
$_title_yetlist = '만들어지지 않은 페이지 목록';
$_err_notexist  = '만들어지지 않은 페이지가 없습니다.';



// pcomment.inc.php
define('PCMT_PAGE', '[[코멘트/%s]]');

// bugtrack.inc.php
$_bugtrack_plugin_priority_list	= array('긴급', '중요', '보통', '낮음');
$_bugtrack_plugin_state_list	= array('제안', '착수', 'CVS 대기', '완료', '보류', '각하');
$_bugtrack_plugin_state_sort	= array('착수', 'CVS 대기', '보류', '완료', '제안', '각하');
$_bugtrack_plugin_state_bgcolor	= array('#ccccff', '#ffcc99', '#ccddcc', '#ccffcc', '#ffccff', '#cccccc', '#ff3333');
$_bugtrack_plugin_title			= '$1 Bugtrack Plugin';
$_bugtrack_plugin_base			= '페이지';
$_bugtrack_plugin_summary		= '제목';
$_bugtrack_plugin_priority		= '우선순위';
$_bugtrack_plugin_state			= '상태';
$_bugtrack_plugin_name			= '이름';
$_bugtrack_plugin_date			= '날짜';
$_bugtrack_plugin_body			= '메세지';
$_bugtrack_plugin_category		= '분류';
$_bugtrack_plugin_pagename		= '페이지이름';
$_bugtrack_plugin_pagename_comment	= '<small>비어있으면 자동으로 페이지이름이 만들어집니다. </small>';
$_bugtrack_plugin_version_comment	= '<small>비어있어도 괜찮습니다.</small>';
$_bugtrack_plugin_version		= '버젼';
$_bugtrack_plugin_submit		= '제출';

// links.inc.php
$_links_messages = array(
		'title_update'	=> '캐시 갱신',
		'msg_adminpass'	=> '관리자 비밀번호',
		'btn_submit'	=> '실행',
		'msg_done'		=> '캐시의 갱신이 완료되었습니다.',
		'msg_usage'		=> '
* 처리 내용

:캐시를 갱신|
모든 페이지를 검색해서 어느 페이지가 어느 페이지로부터 링크 되고 있는지를
확인해, 캐시에 기록합니다.

* 주의
실행에 몇분이 걸리는 경우도 있습니다. 실행 버튼을 누른 뒤, 잠깐 기다려 주세요.

* 실행
관리자 비밀번호를 입력후 [실행]버튼을 클릭해 주세요.
'
);

// update_entities.inc.php
$_entities_messages	=	array(
		'title_update'  => '캐시 갱신',
		'msg_adminpass' => '관리자 비밀번호',
		'btn_submit'    => '실행',
		'msg_done'      => '캐시의 갱신이 완료되었습니다. ',
		'msg_usage'     => '
* 처리 내용

:文字実体参照にマッチする正規表現パターンのキャッシュを更新|
PHPの持つテーブルおよびW3CのDTDをスキャンして、キャッシュに記録します。

:캐릭터 실체 참조에 매치 하는 정규 표현 패턴의 캐시를 갱신|
PHP가 가지는 테이블 및 W3C의 DTD를 검색해서 캐시에 기록합니다.~
(번역기의 한계입니다.  고쳐주세요.)

* 처리 대상
「COLOR(red){not found. }」라고 표시된 파일은 처리되지 않습니다.
-%s

* 실행
관리자 패스워드를 입력해,[실행]버튼을 클릭해 주세요.
'
);


?>
