/////////////////////////////////////////////////
// PukiWikiPlus! - Yet another WikiWikiWeb clone.
//
// $Id: gecko.js,v 1.2.4 2007/07/07 10:12:00 upk Exp $
// Original is nao-pon
//
var pukiwki_elem;

function pukiwiki_pos()
{
	return;
}
function h_pukiwiki_make_copy_button(arg)
{
	document.write("");
}

function pukiwiki_face(v)
{
	if (pukiwiki_elem != null)
	{
		var ss = pukiwiki_getSelectStart(pukiwiki_elem);
		var se = pukiwiki_getSelectEnd(pukiwiki_elem);
		var s1 = (pukiwiki_elem.value).substring(0,ss);
		var s2 = (pukiwiki_elem.value).substring(se,pukiwiki_getTextLength(pukiwiki_elem));
		var s3 = pukiwiki_getMozSelection(pukiwiki_elem);
		if (!s1 && !s2 && !s3) s1 = pukiwiki_elem.value;
		pukiwiki_elem.value = s1 + s3 + ' ' + v + ' ' + s2;
		se = se + v.length + 2;
		pukiwiki_elem.setSelectionRange(se, se);
		pukiwiki_elem.focus();
	}
//	else
//	{
//		alert(pukiwiki_msg_elem);
//		return;	
//	}
}

function pukiwiki_ins(v)
{
	if (pukiwiki_elem != null)
	{
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
		
		var ss = pukiwiki_getSelectStart(pukiwiki_elem);
		var se = pukiwiki_getSelectEnd(pukiwiki_elem);
		var s1 = (pukiwiki_elem.value).substring(0,ss);
		var s2 = (pukiwiki_elem.value).substring(se,pukiwiki_getTextLength(pukiwiki_elem));
		var s3 = pukiwiki_getMozSelection(pukiwiki_elem);
		if (!s1 && !s2 && !s3) s1 = pukiwiki_elem.value;
		pukiwiki_elem.value = s1 + s3 + v + s2;
		se = se + v.length + 2;
		pukiwiki_elem.setSelectionRange(se, se);
		pukiwiki_elem.focus();
	}
//	else
//	{
//		alert(pukiwiki_msg_elem);
//		return;	
//	}
}

function pukiwiki_tag(v)
{
	if (pukiwiki_elem != null)
	{
		var ss = pukiwiki_getSelectStart(pukiwiki_elem);
		var se = pukiwiki_getSelectEnd(pukiwiki_elem);
		var s1 = (pukiwiki_elem.value).substring(0,ss);
		var s2 = (pukiwiki_elem.value).substring(se,pukiwiki_getTextLength(pukiwiki_elem));
		
		var str = pukiwiki_getMozSelection(pukiwiki_elem);
		
		if (!s1 && !s2 && !str) s1 = pukiwiki_elem.value;
		
		if (!str)
		{
			alert(pukiwiki_msg_select);
			return;
		}
		if ( v == 'size' )
		{
			var default_size = "%";
			v = prompt(pukiwiki_msg_fontsize, default_size);
			if (!v) return;
			if (!v.match(/\d+/)) return;
			str = '&size(' + v + '){' + str + '};';
//			if (!v.match(/(%|pt)$/))
//				v += "pt";
//			if (!v.match(/\d+(%|pt)/))
//				return;
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
		pukiwiki_elem.value = s1 + str + s2;
		se = ss + str.length;
		pukiwiki_elem.setSelectionRange(ss, se);
		pukiwiki_elem.focus();
	}
//	else
//	{
//		alert(pukiwiki_msg_elem);
//		return;	
//	}
}

function pukiwiki_linkPrompt(v)
{
	if (pukiwiki_elem != null)
	{
		var ss = pukiwiki_getSelectStart(pukiwiki_elem);
		var se = pukiwiki_getSelectEnd(pukiwiki_elem);
		var s1 = (pukiwiki_elem.value).substring(0,ss);
		var s2 = (pukiwiki_elem.value).substring(se,pukiwiki_getTextLength(pukiwiki_elem));
		
		var str = pukiwiki_getMozSelection(pukiwiki_elem);
		
		if (!s1 && !s2 && !str) s1 = pukiwiki_elem.value;
		
		if (!str)
		{
			str = prompt(pukiwiki_msg_link, '');
			if (str == null) {pukiwiki_elem.focus();return;}
		}
		var default_url = "http://";
		regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
		var my_link = prompt(pukiwiki_msg_url, default_url);
		if (my_link != null)
		{
			str = '[[' + str + '>' + my_link + ']]';
			pukiwiki_elem.value = s1 + str + s2;
			se = ss + str.length;
			pukiwiki_elem.setSelectionRange(se, se);
			pukiwiki_elem.focus();
		
		}
	}
	else
	{
		alert(pukiwiki_msg_elem);
		return;	
	}
}

function pukiwiki_charcode()
{
	if (pukiwiki_elem != null)
	{
		var ss = pukiwiki_getSelectStart(pukiwiki_elem);
		var se = pukiwiki_getSelectEnd(pukiwiki_elem);
		var s1 = (pukiwiki_elem.value).substring(0,ss);
		var s2 = (pukiwiki_elem.value).substring(se,pukiwiki_getTextLength(pukiwiki_elem));
		
		var str = pukiwiki_getMozSelection(pukiwiki_elem);
		if (!str)
		{
			alert(pukiwiki_msg_select);
			return;
		}
		var j ="";
		for(var n = 0; n < str.length; n++) j += ("&#"+(str.charCodeAt(n))+";");
		str = j;
		
		pukiwiki_elem.value = s1 + str + s2;
		se = ss + str.length;
		pukiwiki_elem.setSelectionRange(ss, se);
		pukiwiki_elem.focus();
	}
//	else
//	{
//		alert(pukiwiki_msg_elem);
//		return;	
//	}
}

function pukiwiki_setActive(e)
{
	pukiwiki_elem =	 e.target;
}
function pukiwiki_initTexts()
{
	pukiwiki_elem = null;
	oElements = document.getElementsByTagName("input");
	for (i = 0; i < oElements.length; i++)
	{
		oElement = oElements[i];
		if (oElement.type == "text")
		{
			oElement.addEventListener('focus', pukiwiki_setActive, true);
		}
	}
	oElements = document.getElementsByTagName("textarea");
	for (i = 0; i < oElements.length; i++)
	{
		oElement = oElements[i];
		oElement.addEventListener('focus', pukiwiki_setActive, true);
	}
}

function pukiwiki_getSelectStart(s)
{
	return s.selectionStart;
}

function pukiwiki_getSelectEnd(s)
{
	return s.selectionEnd;
}

function pukiwiki_getTextLength(s)
{
	return s.textLength;
}

function pukiwiki_getMozSelection(s)
{
	return (s.value).substring(pukiwiki_getSelectStart(s), pukiwiki_getSelectEnd(s))
}

function pukiwiki_setMozSelection(a,z)
{
	pukiwiki_elem.selectionStart = a;
	pukiwiki_elem.selectionEnd = z;
}

function pukiwiki_show_hint()
{
	alert(pukiwiki_msg_gecko_hint_text);
	
	if (pukiwiki_elem != null) pukiwiki_elem.focus();
}
