var ajax_apx = false;

function pukiwiki_apx(page,checked)
{
	var result = document.getElementById("realview");
	if (checked) {
		ajax_apx = checked;
		result.style.display = "";
	} else {
		ajax_apx = checked;
		result.style.display = "none";
	}
	var msg = document.getElementById("msg");
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
