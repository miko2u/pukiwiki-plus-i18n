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

function pukiwiki_apv(page,source)
{
	if (ajax_apx) {
		preview_onload = function(htmldoc) {
			var result = document.getElementById("realview");
			result.innerHTML = htmldoc.responseText;
		};

		var postdata = 'page=' + encodeURIComponent(page) + '&msg=' + encodeURIComponent(source);
		var html = new TextLoader(preview_onload,null);
		html.load('?cmd=edit&realview=1',postdata);
	}
}
