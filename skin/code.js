function code_outline(id,path)
{
    if(navigator.appVersion.match(/MSIE\s*(6\.|5\.5)/)){
        if(document.getElementById(id+"_img")) {
			document.getElementById(id+"_img").style.height="1.2em";
			document.getElementById(id+"_img").style.verticalAlign="bottom";
		}
    }
    var vis=document.getElementById(id).style.display;
    if (vis=="none") {
        if(document.getElementById(id))document.getElementById(id).style.display="";
        if(document.getElementById(id+"n"))document.getElementById(id+"n").style.display="";
        if(document.getElementById(id+"o"))document.getElementById(id+"o").style.display="";
        if(document.getElementById(id+"a"))document.getElementById(id+"a").innerHTML='-';
        if(document.getElementById(id+"_img"))document.getElementById(id+"_img").
											   innerHTML="";
    } else {
        if(document.getElementById(id))document.getElementById(id).style.display="none";
        if(document.getElementById(id+"n"))document.getElementById(id+"n").style.display="none";
        if(document.getElementById(id+"o"))document.getElementById(id+"o").style.display="none";
        if(document.getElementById(id+"a"))document.getElementById(id+"a").innerHTML='+';
        if(document.getElementById(id+"_img"))document.getElementById(id+"_img").
											   innerHTML="<img src=\""+path+"code_dot.png\" alt=\"\" title=\"...\" />";
    }
}

function code_all_outline(id,num,disp,path)
{
    var ch = '';
	var dotimage = '';
    if (disp=="") {
        ch = '-';
		dotimage = '';
    } else {
		ch = '+';
		dotimage = "<img src=\""+path+"code_dot.png\" alt=\"\" title=\"...\" />";
	}
    for (var i=num; i>0; i--) {
        if(navigator.appVersion.match(/MSIE\s*(6\.|5\.5)/)) {
			document.getElementById(id+"_"+i+"_img").style.height="1.2em";
			document.getElementById(id+"_"+i+"_img").style.verticalAlign="bottom";
		}
        if(document.getElementById(id+"_"+i+"o")) {
			if(document.getElementById(id+"_"+i)) document.getElementById(id+"_"+i).style.display=disp;
			if(document.getElementById(id+"_"+i+"_img")) document.getElementById(id+"_"+i+"_img").innerHTML=dotimage;
		}
		if(document.getElementById(id+"_"+i+"n")) document.getElementById(id+"_"+i+"n").style.display=disp;
		if(document.getElementById(id+"_"+i+"o")) document.getElementById(id+"_"+i+"o").style.display=disp;
		if(document.getElementById(id+"_"+i+"a")) document.getElementById(id+"_"+i+"a").innerHTML=ch;
    }
}

function code_comment(id,num,disp)
{
	for (var i=num; i>0; i--) {
		if(document.getElementById(id+"_cmt_"+i)) document.getElementById(id+"_cmt_"+i).style.display=disp;	
		if(document.getElementById(id+"_cmt_"+i+"n")) document.getElementById(id+"_cmt_"+i+"n").style.display=disp;
		if(document.getElementById(id+"_cmt_"+i+"o")) document.getElementById(id+"_cmt_"+i+"o").style.display=disp;
	}
}