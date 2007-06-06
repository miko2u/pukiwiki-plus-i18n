var ajax_apx = false;
var ajax_tim = 0;

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
		if (navigator.userAgent.indexOf("Safari",0) != -1) {
			outer.style.display = "block";
		} else {
			outer.style.display = "inline";
		}
		outer.style.overflow = "scroll";
		outer.style.width = "660px";
		msg.rows = msg.rows / 2;
	}
	pukiwiki_apv(page,msg);
}

var ajax_count = 0;

function pukiwiki_apv(page,oSource)
{
	if (ajax_apx) {
		var source = oSource.value;
		if (navigator.userAgent.indexOf("Safari",0) == -1) {
			if (++ajax_count != 1) return;
			if (oSource.setSelectionRange) {
				sttlen = oSource.selectionStart;
				endlen = oSource.value.length - oSource.selectionEnd;
				sellen = oSource.selectionEnd-sttlen;
				finlen = source.lastIndexOf("\n",sttlen);
				source = source.substring(0,finlen) + "\n\n" + '&editmark;' + "\n\n" + source.substring(finlen);
			} else if (document.selection.createRange) {
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
		}
		preview_onload = function(htmldoc) {
			var result = document.getElementById("realview");
			result.innerHTML = htmldoc.responseText;
			var innbox = document.getElementById("realview_outer");
			var marker = document.getElementById("editmark");
			innbox.scrollTop = marker.offsetTop - 8;
			if (ajax_count==1) {
				ajax_count = 0;
			} else {
				ajax_count = 0;
				pukiwiki_apv(page,oSource);
			}
		};
		var postdata = 'page=' + encodeURIComponent(page) + '&msg=' + encodeURIComponent(source);
		var html = new TextLoader(preview_onload,null);
		html.load('?cmd=edit&realview=1',postdata);
	}
}
