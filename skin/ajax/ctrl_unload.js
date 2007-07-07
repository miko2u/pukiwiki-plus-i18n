// PukiWiki - Yet another WikiWikiWeb clone.
// $Id:$
var ctrl_unload_form;

function init_ctrl_unload() {
	ctrl_unload_form = document.getElementById('form');

	document.getElementById('cancel').onclick 
	= function() {
		return is_changed()?confirm(pukiwiki_msg_cancel):true;
	};
	ctrl_unload_form.onsubmit
	= function() {
		window.onunload = function(){};
	};
	window.onunload = ctrl_unload;
}

function ctrl_unload() {
	if (is_changed() && confirm(pukiwiki_msg_unload)) {
		ctrl_unload_form.appendChild(document.createElement('input')).setAttribute('name', 'write');
		ctrl_unload_form.submit();
		alert(pukiwiki_msg_submit);
	}
}

function is_changed() {
	return (document.getElementById('msg').value != document.getElementById('original').value);
}
