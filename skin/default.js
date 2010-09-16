/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.js,v 2.0.11 2006/02/15 02:15:00 upk Exp $
// Original is nao-pon
//

// Set masseges.
// move to skin/msg/lang.js

// Init.
var pukiwiki_WinIE=(document.all&&!window.opera&&navigator.platform=="Win32");
var pukiwiki_Gecko=(navigator && navigator.userAgent && navigator.userAgent.indexOf("Gecko/") != -1);

// Common function.
function open_mini(URL,width,height){
	aWindow = window.open(URL, "mini", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=yes,resizable=no,width="+width+",height="+height);
}

// Common Plus function.
function open_uri(href, frame)
{
	if (!frame) {
		return false;
	}
	window.open(href, frame);
	return false;
}

function open_attach_uri(href, frame)
{
	if (!frame) {
		return false;
	}
	window.open(href, frame, "width=100,height=100");
	return false;
}

// cookie
var pukiwiki_adv = pukiwiki_load_cookie("pwplus");

// Helper image tag set
var pukiwiki_adv_tag = '';
if (pukiwiki_adv == "on") pukiwiki_adv_tag = '<span style="cursor:hand;">'+
'<img src="'+IMAGE_DIR+'plus/ncr.gif" width="22" height="16" border="0" title="'+pukiwiki_msg_to_ncr+'" alt="'+pukiwiki_msg_to_ncr+'" onClick="javascript:pukiwiki_charcode(); return false;" />'+
'<img src="'+IMAGE_DIR+'plus/br.gif" width="18" height="16" border="0" title="&amp;br;" alt="&amp;br;" onClick="javascript:pukiwiki_ins(\'&br;\'); return false;" />'+
'<'+'/'+'span>&nbsp;';

//'<img src="'+IMAGE_DIR+'plus/iplugin.gif" width="18" height="16" border="0" title="Inline Plugin" alt="Inline Plugin" onClick="javascript:pukiwiki_ins(\'&(){};\'); return false;" />'+

var pukiwiki_helper_img = 
'<img src="'+IMAGE_DIR+'plus/buttons.gif" width="103" height="16" border="0" usemap="#map_button" tabindex="-1" />&nbsp;'+
pukiwiki_adv_tag +
'<img src="'+IMAGE_DIR+'plus/colors.gif" width="64" height="16" border="0" usemap="#map_color" tabindex="-1" />&nbsp;'+
'<span style="cursor:hand;">'+
'<img src="'+IMAGE_DIR+'face/smile.png" width="15" height="15" border="0" title="(^^)" alt="(^^)" onClick="javascript:pukiwiki_face(\'(^^)\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/bigsmile.png" width="15" height="15" border="0" title="(^-^" alt="(^-^" onClick="javascript:pukiwiki_face(\'(^-^\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/huh.png" width="15" height="15" border="0" title="(^Q^" alt="(^Q^" onClick="javascript:pukiwiki_face(\'(^Q^\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/oh.png" width="15" height="15" border="0" title="(..;" alt="(..;" onClick="javascript:pukiwiki_face(\'(..;\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/wink.png" width="15" height="15" border="0" title="(^_-" alt="(^_-" onClick="javascript:pukiwiki_face(\'(^_-\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/sad.png" width="15" height="15" border="0" title="(--;" alt="(--;" onClick="javascript:pukiwiki_face(\'(--;\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/worried.png" width="15" height="15" border="0" title="(^^;" alt="(^^;" onclick="javascript:pukiwiki_face(\'(^^\;\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/tear.png" width="15" height="15" border="0" title="(T-T" alt="(T-T" onclick="javascript:pukiwiki_face(\'(T-T\'); return false;" />'+
'<img src="'+IMAGE_DIR+'face/heart.png" width="15" height="15" border="0" title="&amp;heart;" alt="&amp;heart;" onClick="javascript:pukiwiki_face(\'&amp;heart;\'); return false;" />'+
'<'+'/'+'span>';

//'<img src="'+IMAGE_DIR+'face/star.gif" width="15" height="15" border="0" title="&amp;star;" alt="&amp;star;" onClick="javascript:pukiwiki_face(\'&amp;star;\'); return false;" />'+

// Helper function.
function pukiwiki_show_fontset_img()
{
	var str =  pukiwiki_helper_img + '&nbsp;<a href="#" onClick="javascript:pukiwiki_show_hint(); return false;">' + pukiwiki_msg_hint + '<'+'/'+'a>';
	
	if (pukiwiki_adv == "on")
	{
		str = str + '<a href="#" onClick="javascript:pukiwiki_adv_switch(); return false;">' + pukiwiki_msg_to_easy_t + '<'+'/'+'a>';
	}
	else
	{
		str = str + '<a href="#" onClick="javascript:pukiwiki_adv_switch(); return false;">' + pukiwiki_msg_to_adv_t + '<'+'/'+'a>';
	}
	
	document.write(str);
}

function pukiwiki_adv_switch()
{
	if (pukiwiki_adv == "on")
	{
		pukiwiki_adv = "off";
		pukiwiki_ans = confirm(pukiwiki_msg_to_easy);
	}
	else
	{
		pukiwiki_adv = "on";
		pukiwiki_ans = confirm(pukiwiki_msg_to_adv);
	}
	pukiwiki_save_cookie("pwplus",pukiwiki_adv,1,"/");
	if (pukiwiki_ans) window.location.reload();
}
function pukiwiki_save_cookie(arg1,arg2,arg3,arg4){ //arg1=dataname arg2=data arg3=expiration days
	if(arg1&&arg2)
	{
		if(arg3)
		{
			xDay = new Date;
			xDay.setDate(xDay.getDate() + eval(arg3));
			xDay = xDay.toGMTString();
			_exp = ";expires=" + xDay;
		}
		else
		{
			_exp ="";
		}
		if(arg4)
		{
			_path = ";path=" + arg4;
		}
		else
		{
			_path= "";
		}
		document.cookie = escape(arg1) + "=" + escape(arg2) + _exp + _path +";";
	}
}

function pukiwiki_load_cookie(arg){ //arg=dataname
	if(arg)
	{
		cookieData = document.cookie + ";" ;
		arg = escape(arg);
		startPoint1 = cookieData.indexOf(arg);
		startPoint2 = cookieData.indexOf("=",startPoint1) +1;
		endPoint = cookieData.indexOf(";",startPoint1);
		if(startPoint2 < endPoint && startPoint1 > -1 &&startPoint2-startPoint1 == arg.length+1)
		{
			cookieData = cookieData.substring(startPoint2,endPoint);
			cookieData = unescape(cookieData);
			return cookieData
		}
	}
	return false
}

function pukiwiki_area_highlite(id,mode)
{
	if (mode)
	{
		document.getElementById(id).className = "area_on";
	}
	else
	{
		document.getElementById(id).className = "area_off";
	}
	
}
// Branch.
if (pukiwiki_WinIE)
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="'+SKIN_DIR+'winie.js"></scr'+'ipt>');
}
else if (pukiwiki_Gecko)
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="'+SKIN_DIR+'gecko.js"></scr'+'ipt>');
}
else
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="'+SKIN_DIR+'other.js"></scr'+'ipt>');
}

var __default_onload_save = window.onload;
window.onload = function() {
	if (__default_onload_save) __default_onload_save();
	pukiwiki_initTexts();
}

//GreyBox configuration
//Use animation?
var GB_ANIMATION = true;
var GB_IMG_DIR = SKIN_DIR+"greybox/";
//Clicking on the transparent overlay closes the GreyBox window?
var GB_overlay_click_close = false;
//Demo change headline - look more in demoiframe
//$ function is like getElementById
function changeHeadline(text){
  $('headline').innerHTML = text;
}

