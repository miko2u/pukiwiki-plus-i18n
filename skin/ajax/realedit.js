function pukiwiki_apv(page,source)
{
	preview_onload = function(htmldoc) {
		var result = document.getElementById("realview");
		result.innerHTML = htmldoc.responseText;
	};

	var postdata = 'page=' + encodeURIComponent(page) + '&msg=' + encodeURIComponent(source);
	var html = new TextLoader(preview_onload,null);
	html.load('?cmd=edit&realview=1',postdata);
}
