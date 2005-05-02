var ajax_apx = false;

function pukiwiki_apx(page)
{
	var outer = document.getElementById("realview_outer");
	var msg = document.getElementById("msg");
	if (ajax_apx) {
		ajax_apx = false;
		outer.style.display = "none";
		msg.rows = msg.rows * 2;
	} else {
		ajax_apx = true;
		outer.style.display = "inline";
		msg.rows = msg.rows / 2;
	}
	pukiwiki_apv(page,msg.value);
}

function pukiwiki_apv(page,oSource)
{
	var source = oSource.value;
	if (document.selection.createRange) {
		var sel = document.selection.createRange();
		sellen = sel.text.length;
		var end = oSource.createTextRange();
		var all = end.text.length;
		end.moveToPoint(sel.offsetLeft,sel.offsetTop);
		end.moveEnd("textedit");
		endlen = end.text.length;
		sttlen = all - endlen;
		finlen = source.lastIndexOf("\n",sttlen);
		source = source.substring(0,finlen) + "\n\n" + '&editmark;' + "\n\n" + source.substring(finlen);
	}
	if (ajax_apx) {
		preview_onload = function(htmldoc) {
			var result = document.getElementById("realview");
			result.innerHTML = htmldoc.responseText;
			var innbox = document.getElementById("realview_outer");
			var marker = document.getElementById("editmark");
			innbox.scrollTop = marker.offsetTop - 16;
		};
		var postdata = 'page=' + encodeURIComponent(page) + '&msg=' + encodeURIComponent(source);
		var html = new TextLoader(preview_onload,null);
		html.load('?cmd=edit&realview=1',postdata);
	}
}
