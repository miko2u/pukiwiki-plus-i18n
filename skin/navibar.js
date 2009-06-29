/*
	Navigation Menu System for nIE4up, Netscape6up, and Opera6up
	copyright(C)2002 SHIN-ICHI ( http://www.h2.dion.ne.jp/~survive/ )

	required boolean : IE4, NN6 and OP6
*/
var nIE4 = false;	// Internet Explorer 4,5,6
var nDOM = false;	// Netscape 6,7
var nOP6 = false;	// Opera 6,7
var nSAF = false;	// Safari

// 特有のオブジェクトを取得してバージョン確認
objSaf = (navigator.userAgent.indexOf("Safari",0) != -1)?1:0;
objOP6 = (navigator.userAgent.indexOf("Opera",0) != -1)?1:0;
objDOM = document.getElementById;
objIE4 = document.all;

if( objOP6 ){ nOP6 = true; }
else if( objIE4 ){ nIE4 = true; }
else if( objDOM	){ nDOM = true; }
if( objSaf ){ nSAF = true; }

var objOpenedNaviMenu = null;

var strNaviBarID;    	// NavigationBar DIV ID
var strNaviMenuID;   	// + n	MenuItemTitle TD ID
var strNaviMenuClass;	//    	MenuItemTitle TD and A Class
var strNaviLinkID;  	// + n	MenuItemTitle A ID 
var strMenuBlockID; 	// + n 	MenuItemFloating DIV ID
                     	//    	MenuItemFolating DIV Class --> Don't use this script, but css for desgin
var strMenuItemClass; 	//    	SubItemTitle DIV and A Class


// Navigation System Start Method
function startNaviMenu(NaviBarID, NaviMenuID, NaviMenuClass, NaviLinkID, MenuBlockID, MenuItemClass)
{
    // init properties
    strNaviBarID	 = NaviBarID;
    strNaviMenuID	 = NaviMenuID;
    strNaviMenuClass = NaviMenuClass;
    strNaviLinkID	 = NaviLinkID;
    strMenuBlockID	 = MenuBlockID;
    strMenuItemClass = MenuItemClass; 

    // event handling
	if(nIE4 || nOP6) {
		document.onmouseover = viewNaviMenuIEandOpera; 
	} else if(nDOM) {
		window.addEventListener("mouseover", viewNaviMenuN6, true);
	}
}

function GetPositionTop(o){
 var p = o.offsetParent;

 // if(p.tagName == 'BODY'){
 if(p.tagName == 'BODY' || p.tagName == 'HTML' || p.offsetParent == null){
  return o.offsetTop;
 }
 else{
  return o.offsetTop + GetPositionTop(p);
 }
}
function GetPositionLeft(o){
 var p = o.offsetParent;

 // if(p.tagName == 'BODY'){
 if(p.tagName == 'BODY' || p.tagName == 'HTML' || p.offsetParent == null){
  return o.offsetLeft;
 }
 else{
  return o.offsetLeft + GetPositionLeft(p);
 }
}

// Common Open Menu
function viewNaviMenuCommon(objEvent){

    objPosition = document.getElementById(strNaviBarID);
    if(objEvent.className == strNaviMenuClass) {
        closeNaviMenu();
        var strThisID = objEvent.id;
        if (strThisID.indexOf(strNaviLinkID) >= 0) {
            strThisID = strThisID.replace(strNaviLinkID, strNaviMenuID);
        }

        objItem = document.getElementById(strThisID);
        if (objItem != null) {
	        objOpenedNaviMenu = document.getElementById(strThisID.replace(strNaviMenuID, strMenuBlockID));
	        if (objOpenedNaviMenu != null) {
			patchHeight = 0;
			if ((nSAF && document.documentMode < 4) || (nIE4 && document.documentMode == 7)) { patchHeight = 11; }
		        objOpenedNaviMenu.style.top  = GetPositionTop(objItem) + objPosition.offsetHeight + patchHeight + "px";
		        if ( GetPositionLeft(objItem) + objOpenedNaviMenu.offsetWidth >= GetPositionLeft(objPosition) + objPosition.offsetWidth)  {
				objOpenedNaviMenu.style.left = GetPositionLeft(objPosition) + objPosition.offsetWidth - objOpenedNaviMenu.offsetWidth + "px";
		        } else {
				objOpenedNaviMenu.style.left = GetPositionLeft(objItem) + "px";
			}
		        objOpenedNaviMenu.style.visibility = "visible";
		}
	}
    } else if( objOpenedNaviMenu != null ) {

        if( nDOM ) {
            if( objEvent.id.indexOf( strMenuBlockID ) >= 0 ){ return; }
            if( objEvent.className == strMenuItemClass ){ return; }

        } else {
            if( objOpenedNaviMenu.contains(objEvent) == true
                || objPosition.contains(objEvent) == true){ return; }
        }
    	closeNaviMenu();
    }

}

// Internet Explorer 4,5,6 and Opera 6,7 Event Handler
function viewNaviMenuIEandOpera(){
    viewNaviMenuCommon( window.event.srcElement );
}

// Netscape 6,7 Event Handler
function viewNaviMenuN6(eventMouseOver){
    viewNaviMenuCommon( eventMouseOver.target );
}

// Common Close Menu
function closeNaviMenu(){
    if(objOpenedNaviMenu != null){
        objOpenedNaviMenu.style.visibility = "hidden";
    }
    objOpenedNaviMenu = null
}
