//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2007 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2007 Frederico Caldeira Knabben
//	PukiWiki Plus! : Copyright (C) 2009 Katsumi Saito
//
//	File:
//	  guiedit.js
//	  guiedit プラグインに使用する JavaScript
//


//	FCKeditor のインスタンス
var editor = null;

//	FCKeditor で編集する HTML
var html = '';

//	Ajax 送受信の開始時間
var start_time;

//	ページが読み込まれた時
window.onload = function () {
	if (!FCKeditor_IsCompatibleBrowser()) {
		document.location = document.URL + '&text=1';
	}
	
	GetSource();

	var oFCKeditor = new FCKeditor('msg', '100%', 300, 'Normal');
	
	oFCKeditor.BasePath = FCK_PATH;
	oFCKeditor.Config['CustomConfigurationsPath'] = GUIEDIT_PATH + "fckconfig.js";
	oFCKeditor.Config['EditorAreaCSS'] = GUIEDIT_PATH + "editorarea.css.php";
	oFCKeditor.Config['SkinPath'] = GUIEDIT_PATH + "fck_skin/";
	oFCKeditor.Config['PluginsPath'] = GUIEDIT_PATH + "fck_plugins/";
	oFCKeditor.Config['SmileyPath'] = SMILEY_PATH;
	
	oFCKeditor.ReplaceTextarea();
}

//	FCKeditor の作成が完了した時
function FCKeditor_OnComplete(editorInstance) {
	editor = editorInstance;
	
	SetBeforeUnload(true);
	
	if (html) {
		editor.SetHTML(html, true);
		html = '';
	}
}

//	onbeforeunload イベントの設定
function SetBeforeUnload(bEnable) {
	window.onbeforeunload = function () {
		if(bEnable && editor.IsDirty()){
			return '続行すると変更内容が破棄されます。';
		}
		return;
	};
}

//	編集するデータ
function GetSource() {
	var element = document.getElementById('edit_form');
	var postdata = new Array();
	postdata['cmd'] = 'guiedit';
	postdata['edit'] = 1;
	postdata['page'] = element.page.value;
	postdata['id'] = element.id.value;

	sendRequest(OnSourceLoaded, postdata, 'POST', element.action, true, true);
}

//	編集するデータを受信した時
function OnSourceLoaded(obj) {
	var doc = obj.responseXML;
	html = doc.documentElement.firstChild.nodeValue;
	
	if (editor) {
		editor.SetHTML(html, true);
		html = '';
	}
}

//	テンプレート
function Template() {
	var element = document.getElementById('edit_form');
	
	if (element.template_page.selectedIndex == '0') {
		return;
	}
	
	var postdata = new Array();
	postdata['cmd'] = 'guiedit';
	postdata['template'] = 1;
	postdata['page'] = element.page.value;
	postdata['template_page'] = element.template_page.value;

	sendRequest(OnTemplateLoaded, postdata, 'POST', element.action, true, true);
}

//	テンプレートのデータを受信した時
function OnTemplateLoaded(obj) {
	var doc = obj.responseXML;
	var html = doc.documentElement.firstChild.nodeValue;
	
	editor.SetHTML(html);
}

//	プレビュー
function Preview() {
	var element = document.getElementById('edit_form');
	start_time = (new Date()).getTime();
	
	var indicator = document.getElementById('preview_indicator');
	if (indicator.style.display == 'none') {
		indicator.style.display = 'block';
		document.getElementById('preview_area').style.display = 'block';
	}
	indicator.innerHTML = "プレビュー ： 受信中";
	
	
	var postdata = new Array();
	postdata['cmd'] = 'guiedit';
	postdata['preview'] = 1;
	postdata['page'] = element.page.value;
	postdata['msg'] = editor.GetXHTML(true);
	
	sendRequest(OnPreviewLoaded, postdata, 'POST', element.action, true, true);
}

//	プレビューのデータを受信した時
function OnPreviewLoaded(obj) {
	var doc = obj.responseXML;
	var text = doc.documentElement.firstChild.nodeValue;
	document.getElementById('preview_area').innerHTML = text;
	
	var time = ((new Date()).getTime() - start_time) / 1000;
	start_time = 0;
	
	document.getElementById('preview_indicator').innerHTML = "プレビュー ： 完了 (" + time + " s)";
}

//	ページの更新
function Write() {
	SetBeforeUnload(false);
}
