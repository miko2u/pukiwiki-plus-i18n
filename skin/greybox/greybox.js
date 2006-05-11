/****
Last Modified: 28/04/06 16:23:20

 GreyBox - The pop-up window thingie
   Copyright Amir Salihefendic 2006
 AUTHOR
   4mir Salihefendic (http://amix.dk) - amix@amix.dk
 VERSION
	 1.92
 LICENSE
  LGPL (read more in LGPL.txt)
 SITE
   http://amix.dk/greybox
****/
var GB_HEADER = null;
var GB_WINDOW = null;
var GB_IFRAME = null;
var GB_OVERLAY = null;
var GB_TIMEOUT = null;

var GB_URL;

var GB_HEIGHT = 400;
var GB_WIDTH = 400;

var GB_caption = null;

//The url that was visited last
var GB_last_win_url = null;

function GB_show(caption, url /* optional */, height, width, return_win) {
  if(height != 'undefined')
    GB_HEIGHT = height;
  if(width != 'undefined')
    GB_WIDTH = width;

  initIfNeeded();

  GB_caption.innerHTML = caption;

  GB_setPosition();
  if(GB_ANIMATION) {
    positionRightVertically(GB_HEADER, -(GB_HEIGHT));
    positionRightVertically(GB_WINDOW, -(GB_HEIGHT+22));
  }

  AJS.showElement(GB_OVERLAY);
  AJS.showElement(GB_HEADER);
  AJS.showElement(GB_WINDOW);

  GB_setWidth();

  if(GB_ANIMATION) {
    GB_animateOut(-GB_HEIGHT);
    GB_URL = url;
  }
  else {
    GB_IFRAME.src = url;
    GB_IFRAME.opener = this;
  }
  return false;
}

function GB_hide() {
  GB_IFRAME.src = GB_IMG_DIR + "blank.html";
  AJS.hideElement(GB_WINDOW);
  AJS.hideElement(GB_HEADER);
  AJS.hideElement(GB_OVERLAY);
}

function GB_setPosition() {
  positionRightVertically(GB_HEADER, 0);
  positionRightVertically(GB_WINDOW, 22);
}

function GB_animateOut(top) {
  if(top+getScrollTop() < 0) {
    positionRightVertically(GB_WINDOW, top+22);
    positionRightVertically(GB_HEADER, top);
    GB_TIMEOUT = window.setTimeout(function() { GB_animateOut(top+50); }, 1);
  }
  else {
    GB_IFRAME.src = GB_URL;
    GB_IFRAME.opener = this;
    GB_WINDOW.style.top = getScrollTop()+22+"px";
    GB_HEADER.style.top = getScrollTop()+"px";
    clearTimeout(GB_TIMEOUT);
  }
}

function GB_setWidth() {
  var array_page_size = GB_getWindowSize();

  //Set size
  GB_WINDOW.style.width = GB_WIDTH + "px";
  GB_IFRAME.style.width = GB_WIDTH + "px";
  GB_HEADER.style.width = GB_WIDTH + "px";

  GB_WINDOW.style.height = GB_HEIGHT + "px";
  GB_IFRAME.style.height = GB_HEIGHT - 5 + "px";

  if((navigator.userAgent.toLowerCase().indexOf("firefox") != -1))
    GB_OVERLAY.style.width = "100%";
  else
    GB_OVERLAY.style.width = array_page_size[0] + "px";

  var max_height = Math.max(getScrollTop()+array_page_size[1], getScrollTop()+GB_HEIGHT+30);
  GB_OVERLAY.style.height = max_height + "px";

  GB_WINDOW.style.left = ((array_page_size[0] - GB_WINDOW.offsetWidth) /2) + "px";
  GB_HEADER.style.left = ((array_page_size[0] - GB_HEADER.offsetWidth) /2) + "px";
  
}

function GB_init() {
  //Create the overlay
  GB_OVERLAY = AJS.DIV({'id': 'GB_overlay'});
  if(AJS.isIe()) {
    GB_OVERLAY.style.backgroundColor = "#000000";
    GB_OVERLAY.style.backgroundColor = "transparent";
    GB_OVERLAY.style.backgroundImage = "url("+ GB_IMG_DIR +"blank.gif)";
    GB_OVERLAY.runtimeStyle.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + GB_IMG_DIR + "overlay.png',sizingMethod='scale')";
  }
  else 
    GB_OVERLAY.style.backgroundImage = "url("+ GB_IMG_DIR +"overlay.png)";

  if(GB_overlay_click_close)
    GB_OVERLAY.onclick = GB_hide;

  AJS.getBody().insertBefore(GB_OVERLAY, AJS.getBody().firstChild);

  //Create the window
  GB_WINDOW = AJS.DIV({'id': 'GB_window'});

  GB_HEADER = AJS.DIV({'id': 'GB_header'});
  GB_caption = AJS.DIV({'id': 'GB_caption'}, "");

  var close = AJS.DIV({'id': 'GB_close'}, AJS.IMG({'src': GB_IMG_DIR + 'close.gif', 'alt': 'Close window'}));
  close.onclick = GB_hide;
  AJS.appendChildNodes(GB_HEADER, close, GB_caption);

  AJS.getBody().insertBefore(GB_WINDOW, GB_OVERLAY.nextSibling);
  AJS.getBody().insertBefore(GB_HEADER, GB_OVERLAY.nextSibling);

}

function initIfNeeded() {
  if(GB_OVERLAY == null) {
    GB_init();
    GB_addOnWinResize(GB_setWidth);
    window.onscroll = function() { GB_setPosition(); GB_setWidth(); };
  } 
  //Remove the old iFrame
  var new_frame = AJS.IFRAME({'id': 'GB_frame', 'name': 'GB_frame'});
  if (GB_IFRAME != null)
    AJS.removeElement(GB_IFRAME);
  AJS.appendChildNodes(GB_WINDOW, new_frame);
  GB_IFRAME = new_frame;
}

function GB_getWindowSize(){
	var window_width, window_height;
	if (self.innerHeight) {	// all except Explorer
		window_width = self.innerWidth;
		window_height = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		window_width = document.documentElement.clientWidth;
		window_height = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		window_width = document.body.clientWidth;
		window_height = document.body.clientHeight;
	}	
	return [window_width, window_height];
}

function GB_addOnWinResize(func) {
  var oldonrezise = window.onresize;
  if (typeof window.onresize != 'function')
    window.onresize = func;
  else {
    window.onresize = function() {
      oldonrezise();
      func();
    }
  }
}

function positionRightVertically(elm, value) {
  elm.style.top = getScrollTop()+value+"px";
}

function getScrollTop() {
  //From: http://www.quirksmode.org/js/doctypes.html
  var theTop;
  if (document.documentElement && document.documentElement.scrollTop)
      theTop = document.documentElement.scrollTop;
  else if (document.body)
      theTop = document.body.scrollTop;
  return theTop;
}
