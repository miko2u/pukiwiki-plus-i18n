function pukiwiki_apv(textval)
{
	preview_onload = function(htmldoc) {
		var result = document.getElementById("realview");
		result.innerHTML = htmldoc.responseText;
	};

	var postdata = 'msg=' + encodeURIComponent(textval);
	var html = new TextLoader(preview_onload,null);
	html.load('?cmd=edit&realview=1',postdata);
}
