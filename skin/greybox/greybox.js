/****
 GreyBox - The pop-up window thingie
   Copyright Amir Salihefendic 2006
 AUTHOR
   4mir Salihefendic (http://amix.dk) - amix@amix.dk
 VERSION
	 1.31 (10/02/06 20:59:07)
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

var GB_HEIGHT = 400;
var GB_WIDTH = 400;

var GB_caption = null;

function GB_show(caption, url /* optional */, height, width) {
  if(height != 'undefined')
    GB_HEIGHT = height;
  if(width != 'undefined')
    GB_WIDTH = width;

  initIfNeeded();

  GB_caption.innerHTML = caption;

  if(GB_ANIMATION) {
    //22 is for header height
    GB_HEADER.style.top = -(GB_HEIGHT) + "px";
    GB_WINDOW.style.top = -(GB_HEIGHT+22) + "px";
  }

  showElement(GB_OVERLAY);
  showElement(GB_HEADER);
  showElement(GB_WINDOW);

  GB_IFRAME.src = url;
  GB_IFRAME.opener = this;

  GB_position();

  if(GB_ANIMATION) {
    GB_animateOut(-GB_HEIGHT);
  }

}

function GB_hide() {
  GB_IFRAME.href = "about:blank";
  hideElement(GB_WINDOW);
  hideElement(GB_HEADER);
  hideElement(GB_OVERLAY);
}


function GB_animateOut(top) {
  if(top < 0) {
    GB_WINDOW.style.top = (top+22) + "px";
    GB_HEADER.style.top = top + "px";
    GB_TIMEOUT = window.setTimeout(function() { GB_animateOut(top+50); }, 1);
  }
  else {
    GB_WINDOW.style.top = 22 + "px";
    GB_HEADER.style.top = 0 + "px";
    clearTimeout(GB_TIMEOUT);
  }
}

function GB_position() {
  var array_page_size = GB_getWindowSize();

  //Set size
  GB_WINDOW.style.width = GB_WIDTH + "px";
  GB_IFRAME.style.width = GB_WIDTH + "px";
  GB_HEADER.style.width = GB_WIDTH + "px";

  GB_WINDOW.style.height = GB_HEIGHT + "px";
  GB_IFRAME.style.height = GB_HEIGHT - 5 + "px";

  GB_OVERLAY.style.width = array_page_size[0] + "px";

  var max_height = Math.max(array_page_size[1], GB_HEIGHT+30);
  GB_OVERLAY.style.height =  max_height + "px";

  GB_WINDOW.style.left = ((array_page_size[0] - GB_WINDOW.offsetWidth) /2) + "px";
  GB_HEADER.style.left = ((array_page_size[0] - GB_HEADER.offsetWidth) /2) + "px";
  
}

function GB_init() {
  //Create the overlay
  GB_OVERLAY = DIV({'id': 'GB_overlay'});

  if(GB_overlay_click_close)
    GB_OVERLAY.onclick = GB_hide;

  getBody().insertBefore(GB_OVERLAY, getBody().firstChild);

  //Create the window
  GB_WINDOW = DIV({'id': 'GB_window'});

  GB_HEADER = DIV({'id': 'GB_header'});
  GB_caption = DIV({'id': 'GB_caption'}, "");

  // JO1UPK : 2006-02-13
  // var close = DIV({'id': 'GB_close'}, IMG({'src': 'greybox/close.gif', 'alt': 'Close window'}));
  var close = DIV({'id': 'GB_close'}, IMG({'src': SKIN_DIR+'greybox/close.gif', 'alt': 'Close window'}));
  close.onclick = GB_hide;
  ACN(GB_HEADER, close, GB_caption);

  getBody().insertBefore(GB_WINDOW, GB_OVERLAY.nextSibling);
  getBody().insertBefore(GB_HEADER, GB_OVERLAY.nextSibling);

}

function initIfNeeded() {
  if(GB_OVERLAY == null) {
    GB_init();
    GB_addOnWinResize(GB_position);
  } 
  new_stuff = IFRAME({'id': 'GB_frame', 'name': 'GB_frame'});
  RCN(GB_WINDOW, new_stuff);
  GB_IFRAME = new_stuff;
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


