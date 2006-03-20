/****
  AmiJs library
    A very small library with DOM and Ajax functions.
    For a much larger script look on http://www.mochikit.com/
  AUTHOR
    4mir Salihefendic (http://amix.dk) - amix@amix.dk
  LICENSE
    Copyright (c) 2006 Amir Salihefendic. All rights reserved.
    Copyright (c) 2005 Bob Ippolito. All rights reserved.
    http://www.opensource.org/licenses/mit-license.php
  VERSION
    1.31 (20/02/06 22:41:35)
  SITE
    http://amix.dk/amijs
 ****/

/**** 
  Assessor functions 
 ****/
function getElement(id) {
  if(typeof(id) == "string") 
    return document.getElementById(id);
  else
    return id;
}
var $ = getElement;

function getBody() { return getElementsByTagAndClassName('body')[0] };

function getElementsByTagAndClassName(tag_name, class_name, /* optional */parent) {
  var class_elements = new Array();
  if(parent == null || parent == "undefined")
    parent = document;
  if(tag_name == null || tag_name == "undefined")
    tag_name = '*';

  var els = parent.getElementsByTagName(tag_name);
  var els_len = els.length;
  var pattern = new RegExp("(^|\\s)" + class_name + "(\\s|$)");

  for (i = 0, j = 0; i < els_len; i++) {
    if ( pattern.test(els[i].className) || class_name == null ) {
      class_elements[j] = els[i];
      j++;
    }
  }
  return class_elements;
}


/**** 
  DOM manipulation 
 ****/
function appendChildNodes(node/*, nodes...*/) {
  if(arguments.length >= 2) {
    for(var i = 1; i < arguments.length; i++) {
      var n = arguments[i];
      if(typeof(n) == "string")
        n = document.createTextNode(n);
      else if(isDefined(n))
        node.appendChild(n);
    }
  }
  return node;
}
var ACN = appendChildNodes;

function replaceChildNodes(node/*, nodes...*/) {
  var child;
  while ((child = node.firstChild)) {
    node.removeChild(child);
  }
  if (arguments.length < 2) {
    return node;
  } else {
    return appendChildNodes.apply(this, arguments);
  }
}
var RCN = replaceChildNodes;

function insertAfter(node, referenceNode) {
  referenceNode.parentNode.insertBefore(node, referenceNode.nextSibling);
}

function insertBefore(node, referenceNode) {
  referenceNode.parentNode.insertBefore(node, referenceNode);
}

function showElement(elm) { elm.style.display = ''; }
function hideElement(elm) { elm.style.display = 'none'; }

function swapDOM(dest, src) {
  dest = getElement(dest);
  var parent = dest.parentNode;
  if (src) {
    src = getElement(src);
    parent.replaceChild(src, dest);
  } else {
    parent.removeChild(dest);
  }
  return src;
}

function removeElement(elm) {
  swapDOM(elm, null);
}

function createDOM(name, attrs) {
  var i = 1;
  elm = document.createElement(name);

  if(isDefined(attrs[0]) && typeof(attrs[0]) != "string") {
    for(k in attrs[0]) {
      if(k == "style")
        elm.style.cssText = attrs[0][k];
      else if(k == "class")
        elm.className = attrs[0][k];
      else
        elm.setAttribute(k, attrs[0][k]);
    }
    for(i; i < attrs.length; i++) {
      var n = attrs[i];
      if(isDefined(n)) {
        if(typeof(n) == "string")
          n = document.createTextNode(n);
        elm.appendChild(n);
      }
    }
  }
  else {
    //We have just a string...
    var n = attrs[0];
    if(isDefined(n)) {
      n = document.createTextNode(n);
      elm.appendChild(n);
    }
  }
  return elm;
}

var UL = function() { return createDOM.apply(this, ["ul", arguments]); };
var LI = function() { return createDOM.apply(this, ["li", arguments]); };
var TD = function() { return createDOM.apply(this, ["td", arguments]); };
var TR = function() { return createDOM.apply(this, ["tr", arguments]); };
var TH = function() { return createDOM.apply(this, ["th", arguments]); };
var TBODY = function() { return createDOM.apply(this, ["tbody", arguments]); };
var TABLE = function() { return createDOM.apply(this, ["table", arguments]); };
var INPUT = function() { return createDOM.apply(this, ["input", arguments]); };
var SPAN = function() { return createDOM.apply(this, ["span", arguments]); };
var B = function() { return createDOM.apply(this, ["b", arguments]); };
var A = function() { return createDOM.apply(this, ["a", arguments]); };
var DIV = function() { return createDOM.apply(this, ["div", arguments]); };
var IMG = function() { return createDOM.apply(this, ["img", arguments]); };
var BUTTON = function() { return createDOM.apply(this, ["button", arguments]); };
var H1 = function() { return createDOM.apply(this, ["h1", arguments]); };
var H2 = function() { return createDOM.apply(this, ["h2", arguments]); };
var H3 = function() { return createDOM.apply(this, ["h3", arguments]); };
var BR = function() { return createDOM.apply(this, ["br", arguments]); };
var TEXTAREA = function() { return createDOM.apply(this, ["textarea", arguments]); };
var FORM = function() { return createDOM.apply(this, ["form", arguments]); };
var P = function() { return createDOM.apply(this, ["p", arguments]); };
var SELECT = function() { return createDOM.apply(this, ["select", arguments]); };
var OPTION = function() { return createDOM.apply(this, ["option", arguments]); };
var TN = function(text) { return document.createTextNode(text); };
var IFRAME = function() { return createDOM.apply(this, ["iframe", arguments]); };
var SCRIPT = function() { return createDOM.apply(this, ["script", arguments]); };


/**** 
  Ajax functions 
 ****/
function getXMLHttpRequest() {
  var try_these = [
    function () { return new XMLHttpRequest(); },
    function () { return new ActiveXObject('Msxml2.XMLHTTP'); },
    function () { return new ActiveXObject('Microsoft.XMLHTTP'); },
    function () { return new ActiveXObject('Msxml2.XMLHTTP.4.0'); },
    function () { throw "Browser does not support XMLHttpRequest"; }
  ];
  for (var i = 0; i < try_these.length; i++) {
    var func = try_these[i];
    try {
      return func();
    } catch (e) {
    }
  }
}

function doSimpleXMLHttpRequest(url) {
  var req = getXMLHttpRequest();
  req.open("GET", url, true);
  return sendXMLHttpRequest(req);
}

Deferred = function(req) {
  this.callbacks = [];
  this.req = req;

  this.callback = function (res) {
    while (this.callbacks.length > 0) {
      var fn = this.callbacks.pop();
      res = fn(res);
    }
  };

  this.errback = function(e){
    alert("Error encountered:\n" + e);
  };

  this.addErrback = function(fn) {
    this.errback = fn;
  };

  this.addCallback = function(fn) {
    this.callbacks.unshift(fn);
  };

  this.addCallbacks = function(fn1, fn2) {
    this.addCallback(fn1);
    this.addErrback(fn2);
  };

  this.sendReq = function(data) {
    if(isDefined(data))
      this.req.send(data);
    else
      this.req.send(null);
  };
}

Deferred.prototype = new Deferred();

function sendXMLHttpRequest(req, data) {
  var d = new Deferred(req);

  var onreadystatechange = function () {
    if (req.readyState == 4) {
      try {
        status = req.status;
      }
      catch(e) {};
      if(status == 200 || status == 304) {
        d.callback(req, data);
      }
      else {
        d.errback();
      }
    }
  }
  req.onreadystatechange = onreadystatechange;
  return d;
}

function reprString(o) {
  return ('"' + o.replace(/(["\\])/g, '\\$1') + '"'
  ).replace(/[\f]/g, "\\f"
  ).replace(/[\b]/g, "\\b"
  ).replace(/[\n]/g, "\\n"
  ).replace(/[\t]/g, "\\t"
  ).replace(/[\r]/g, "\\r");
}

function serializeJSON(o) {
  var objtype = typeof(o);
  if (objtype == "undefined") {
    return "undefined";
  } else if (objtype == "number" || objtype == "boolean") {
    return o + "";
  } else if (o === null) {
    return "null";
  }
  if (objtype == "string") {
    return reprString(o);
  }
  var me = arguments.callee;
  var newObj;
  if (typeof(o.__json__) == "function") {
    newObj = o.__json__();
    if (o !== newObj) {
      return me(newObj);
    }
  }
  if (typeof(o.json) == "function") {
    newObj = o.json();
    if (o !== newObj) {
      return me(newObj);
    }
  }
  if (objtype != "function" && typeof(o.length) == "number") {
    var res = [];
    for (var i = 0; i < o.length; i++) {
      var val = me(o[i]);
      if (typeof(val) != "string") {
        val = "undefined";
      }
      res.push(val);
    }
    return "[" + res.join(",") + "]";
  }
  res = [];
  for (var k in o) {
    var useKey;
    if (typeof(k) == "number") {
      useKey = '"' + k + '"';
    } else if (typeof(k) == "string") {
      useKey = reprString(k);
    } else {
      // skip non-string or number keys
      continue;
    }
    val = me(o[k]);
    if (typeof(val) != "string") {
      // skip non-serializable values
      continue;
    }
    res.push(useKey + ":" + val);
  }
  return "{" + res.join(",") + "}";
}

function loadJSONDoc(url) {
  var d = doSimpleXMLHttpRequest(url);
  var eval_req = function(req) {
    return eval('(' + req.responseText + ')');
  };
  d.addCallback(eval_req);
  return d;
}

function postJSONDoc(url, data) {
  var req = getXMLHttpRequest();
  req.open("POST", url, true);
  req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  var d = sendXMLHttpRequest(req, data);
  return d;
}


/**** 
  Misc 
 ****/
function keys(obj) {
  var rval = [];
  for (var prop in obj) {
    rval.push(prop);
  }
  return rval;
}

function isDefined(o) {
  return (o != "undefined" && o != null)
}

function isArray(obj) { 
  try { return (typeof(obj.length) == "undefined") ? false : true; }
  catch(e)
  { return false; }
}
