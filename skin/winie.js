var pukiwiki_elem;
var pukiwiki_crl;
var pukiwiki_scrx;
var pukiwiki_scry;
var pukiwiki_rngx;
var pukiwiki_rngy;

function h_pukiwiki_make_copy_button(arg)
{
	document.write ("<input class=\"copyButton\" type=\"button\" value=\"COPY\" onclick=\"h_pukiwiki_doCopy('" + arg + "')\"><br />");
}

function h_pukiwiki_doCopy(arg)
{
	var doc = document.body.createTextRange();
	doc.moveToElementText(document.all(arg));
	doc.execCommand("copy");
	alert(pukiwiki_msg_copyed);
}

function pukiwiki_pos(){
	var et = document.activeElement.type;
	if (!(et == "text" || et == "textarea"))
	{
		return;
	}
	
	var r=document.selection.createRange();
	pukiwiki_elem = document.activeElement;
	if (et == "text")
	{
		r.moveEnd("textedit");
		pukiwiki_crl =r.text.length;
	}
	else if (et == "textarea")
	{
		pukiwiki_rngx=r.offsetLeft;
		pukiwiki_rngy=r.offsetTop;
		pukiwiki_scrx=document.body.scrollLeft;
		pukiwiki_scry=document.body.scrollTop;
	}
}

function pukiwiki_eclr(){
	pukiwiki_elem = NULL;
}

function pukiwiki_ins(v)
{
	if(!pukiwiki_elem)
	{
		alert(pukiwiki_msg_elem);
		return;	
	}
	
	if (v == "&(){};")
	{
		inp = prompt(pukiwiki_msg_inline1, '');
		if (inp == null) {pukiwiki_elem.focus();return;}
		v = "&" + inp;
		inp = prompt(pukiwiki_msg_inline2, '');
		if (inp == null) {pukiwiki_elem.focus();return;}
		v = v + "(" + inp + ")";
		inp = prompt(pukiwiki_msg_inline3, '');
		if (inp == null) {pukiwiki_elem.focus();return;}
		v = v + "{" + inp + "}";
		v = v + ";";
	}
	
	if (pukiwiki_elem.type=="textarea")
	{
		document.body.scrollLeft=pukiwiki_scrx;
		document.body.scrollTop=pukiwiki_scry;
		var r=pukiwiki_elem.createTextRange();
		r.moveToPoint(pukiwiki_rngx,pukiwiki_rngy);
		r.text= v;
		pukiwiki_elem.focus();
		pukiwiki_pos();
	}
	else if (pukiwiki_elem.type=="text")
	{
		var r=pukiwiki_elem.createTextRange();
		r.collapse();
		r.moveStart("character",pukiwiki_elem.value.length-pukiwiki_crl);
		r.text= v;
		pukiwiki_elem.focus();
	}
}

function pukiwiki_face(v)
{
	if(!pukiwiki_elem)
	{
//		alert(pukiwiki_msg_elem);
		return;	
	}

	if (pukiwiki_elem.type=="textarea")
	{
		document.body.scrollLeft=pukiwiki_scrx;
		document.body.scrollTop=pukiwiki_scry;
		var r=pukiwiki_elem.createTextRange();
		r.moveToPoint(pukiwiki_rngx,pukiwiki_rngy);
		r.text= ' ' + v + ' ';
		pukiwiki_elem.focus();
		pukiwiki_pos();
	}
	else if (pukiwiki_elem.type=="text")
	{
		var r=pukiwiki_elem.createTextRange();
		r.collapse();
		r.moveStart("character",pukiwiki_elem.value.length-pukiwiki_crl);
		r.text= ' ' + v + ' ';
		pukiwiki_elem.focus();
	}
}

function pukiwiki_tag(v) {
	if (!document.selection || !pukiwiki_elem)
	{
//		alert(pukiwiki_msg_elem);
		return;	
	}
	
	var str =
		document.selection.createRange().text;
	if (!str)
	{
		alert(pukiwiki_msg_select);
		return;
	}
	if ( v == 'size' )
	{
		var default_size = "";
		v = prompt(pukiwiki_msg_fontsize, default_size);
		if (!v) return;
		if (!v.match(/\d+/))
			return;
		str = '&size(' + v + '){' + str + '};';
//		if (!v.match(/(%|pt)$/))
//			v += "pt";
//		if (!v.match(/\d+(%|pt)/))
//			return;
	}
//mikoadded
	else if ( v == 'b' )
	{
		str = "''" + str + "''";
	}
	else if ( v == 'i' )
	{
		str = "'''" + str + "'''";
	}
	else if ( v == 'u' )
	{
		str = '%%%' + str + '%%%';
	}
	else
//mikoadded + changed font -> color
	{
		if (str.match(/^&color\([^\)]*\)\{.*\};$/))
		{
			str = str.replace(/^(&color\([^\)]*)(\)\{.*\};)$/,"$1," + v + "$2");
		}
		else
		{
			str = '&color(' + v + '){' + str + '};';
		}
	}
	document.selection.createRange().text = str;
	pukiwiki_elem.focus();
	//if (pukiwiki_elem != null) pukiwiki_elem = null;
}

function pukiwiki_linkPrompt(v) {
	if (!document.selection || !pukiwiki_elem)
	{
//		alert(pukiwiki_msg_elem);
		return;	
	}

	var str = document.selection.createRange().text;
	if (!str)
	{
		str = prompt(pukiwiki_msg_link, '');
		if (str == null) {pukiwiki_elem.focus();return;}
	}
	var default_url = "http://";
	regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
	var cbText = clipboardData.getData("Text");
	if(cbText && cbText.match(regex))
		default_url = cbText;
	var my_link = prompt('URL: ', default_url);
	if (my_link != null)
		document.selection.createRange().text = '[[' + str + '>' + my_link + ']]';
	pukiwiki_elem.focus();
	//if (pukiwiki_elem != null) pukiwiki_elem = null;
}

function pukiwiki_charcode()
{
	if (!document.selection || !pukiwiki_elem)
	{
//		alert(pukiwiki_msg_elem);
		return;	
	}

	var str = document.selection.createRange().text;
	if (!str)
	{
		alert(pukiwiki_msg_select);
		return;
	}
	
	var j ="";
	for(var n = 0; n < str.length; n++) j += ("&#"+(str.charCodeAt(n))+";");
	str = j;
		
	document.selection.createRange().text = str;
	pukiwiki_elem.focus();
}

function pukiwiki_initTexts()
{
	return;
}

function pukiwiki_show_hint()
{
	alert(pukiwiki_msg_winie_hint_text);
	
	if (pukiwiki_elem != null) pukiwiki_elem.focus();
}
