/*
Last Modified: 01/06/06 21:47:24

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
    2.9
  SITE
    http://amix.dk/projects/AmiJS
**/

var AJS = {
  BASE_URL: "",

////
// Accessor functions
////
  //Shortcut: AJS.$
  getElement: function(id) {
    if(typeof(id) == "string") 
      return document.getElementById(id);
    else
      return id;
  },

  //Shortcut: AJS.$$
  getElements: function(/*id1, id2, id3*/) {
    var elements = new Array();
      for (var i = 0; i < arguments.length; i++) {
        var element = this.getElement(arguments[i]);
        elements.push(element);
      }
      return elements;
  },

  getQueryArgument: function(var_name) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      if (pair[0] == var_name) {
        return pair[1];
      }
    }
    return null;
  },

  isIe: function() {
    return (navigator.userAgent.toLowerCase().indexOf("msie") != -1 && navigator.userAgent.toLowerCase().indexOf("opera") == -1);
  },
  isNetscape7: function() {
    return (navigator.userAgent.toLowerCase().indexOf("netscape") != -1 && navigator.userAgent.toLowerCase().indexOf("7.") != -1);
  },
  isSafari: function() {
    return (navigator.userAgent.toLowerCase().indexOf("khtml") != -1);
  },
  isOpera: function() {
    return (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
  },
  isMozilla: function() {
    return (navigator.userAgent.toLowerCase().indexOf("gecko") != -1 && navigator.productSub >= 20030210);
  },

  getBody: function() {
    return this.getElementsByTagAndClassName('body')[0] 
  },

  //Shortcut: AJS.$bytc
  getElementsByTagAndClassName: function(tag_name, class_name, /*optional*/ parent) {
    var class_elements = [];
    if(!this.isDefined(parent))
      parent = document;
    if(!this.isDefined(tag_name))
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
  },

  nodeName: function(elm) {
    return elm.nodeName.toLowerCase();
  },

  isElementHidden: function(elm) {
    return elm.style.visibility == "hidden";
  },

  getLast: function(list) {
    if(list.length > 0)
      return list[list.length-1];
    else
      return null;
  },

  getFirst: function(list) {
    if(list.length > 0)
      return list[0];
    else
      return null;
  },


////
// Array functions
////
  //Shortcut: AJS.$A
  createArray: function(v) {
    if(this.isArray(v))
      return v;
    else if(!v)
      return [];
    else
      return [v];
  },

  map: function(list, fn,/*optional*/ start_index, end_index) {
    var i = 0, l = list.length;
    if(start_index)
       i = start_index;
    if(end_index)
       l = end_index;
    //From a mapped function this means AmiJS
    for(i; i < l; i++)
      fn.apply(this, [list[i]]);
  },

  isIn: function(str, list) {
    var ein = false;
    var fn = function(elm) {
      if(str == elm)
        ein = true;
    };
    this.map(list, fn);
    return ein;
  },


////
// DOM manipulation
////
  //Shortcut: AJS.ACN
  appendChildNodes: function(node/*, nodes...*/) {
    if(arguments.length >= 2) {
      AJS.map(arguments, function(n) { 
        if(this.isString(n))
          n = this.TN(n);
        if(this.isDefined(n))
          node.appendChild(n);
      }, 1);
    }
    return node;
  },

  //Shortcut: AJS.RCN
  replaceChildNodes: function(node/*, nodes...*/) {
    var child;
    while ((child = node.firstChild)) 
      node.removeChild(child);
    if (arguments.length < 2)
      return node;
    else
      return this.appendChildNodes.apply(this, arguments);
    return node;
  },

  insertAfter: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode.nextSibling);
    return node;
  },
  
  insertBefore: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode);
    return node;
  },
  
  showElement: function(/*elms...*/) {
    this.map(arguments, function(elm) { elm.style.display = ''});
  },
  
  hideElement: function(elm) {
    this.map(arguments, function(elm) { elm.style.display = 'none'});
  },
  
  swapDOM: function(dest, src) {
    dest = this.getElement(dest);
    var parent = dest.parentNode;
    if (src) {
      src = this.getElement(src);
      parent.replaceChild(src, dest);
    } else {
      parent.removeChild(dest);
    }
    return src;
  },

  removeElement: function(/*elm1, elm2...*/) {
    this.map(arguments, function(elm) { AJS.swapDOM(elm, null); });
  },

  createDOM: function(name, attrs) {
    var i=0, attr;
    elm = document.createElement(name);

    if(this.isDict(attrs[i])) {
      for(k in attrs[0]) {
        if(k == "style")
          elm.style.cssText = attrs[0][k];
        else if(k == "class")
          elm.className = attrs[0][k];
        else {
          attr = attrs[0][k];
          elm.setAttribute(k, attr);
        }
      }
      i++;
    }

    if(attrs[0] == null)
      i = 1;

    AJS.map(attrs, function(n) {
      if(this.isDefined(n)) {
        if(this.isString(n))
          n = this.TN(n);
        elm.appendChild(n);
      }
    }, i);
    return elm;
  },

  UL: function() { return this.createDOM.apply(this, ["ul", arguments]); },
  LI: function() { return this.createDOM.apply(this, ["li", arguments]); },
  TD: function() { return this.createDOM.apply(this, ["td", arguments]); },
  TR: function() { return this.createDOM.apply(this, ["tr", arguments]); },
  TH: function() { return this.createDOM.apply(this, ["th", arguments]); },
  TBODY: function() { return this.createDOM.apply(this, ["tbody", arguments]); },
  TABLE: function() { return this.createDOM.apply(this, ["table", arguments]); },
  INPUT: function() { return this.createDOM.apply(this, ["input", arguments]); },
  SPAN: function() { return this.createDOM.apply(this, ["span", arguments]); },
  B: function() { return this.createDOM.apply(this, ["b", arguments]); },
  A: function() { return this.createDOM.apply(this, ["a", arguments]); },
  DIV: function() { return this.createDOM.apply(this, ["div", arguments]); },
  IMG: function() { return this.createDOM.apply(this, ["img", arguments]); },
  BUTTON: function() { return this.createDOM.apply(this, ["button", arguments]); },
  H1: function() { return this.createDOM.apply(this, ["h1", arguments]); },
  H2: function() { return this.createDOM.apply(this, ["h2", arguments]); },
  H3: function() { return this.createDOM.apply(this, ["h3", arguments]); },
  BR: function() { return this.createDOM.apply(this, ["br", arguments]); },
  TEXTAREA: function() { return this.createDOM.apply(this, ["textarea", arguments]); },
  FORM: function() { return this.createDOM.apply(this, ["form", arguments]); },
  P: function() { return this.createDOM.apply(this, ["p", arguments]); },
  SELECT: function() { return this.createDOM.apply(this, ["select", arguments]); },
  OPTION: function() { return this.createDOM.apply(this, ["option", arguments]); },
  TN: function(text) { return document.createTextNode(text); },
  IFRAME: function() { return this.createDOM.apply(this, ["iframe", arguments]); },
  SCRIPT: function() { return this.createDOM.apply(this, ["script", arguments]); },
  CENTER: function() { return this.createDOM.apply(this, ["center", arguments]); },

  getCssDim: function(dim) {
    if(this.isString(dim))
      return dim;
    else
      return dim + "px";
  },

  setWidth: function(/*elm1, elm2..., width*/) {
    var w = this.getLast(arguments);
    this.map(arguments, function(elm) { elm.style.width = this.getCssDim(w)}, 0, arguments.length-1);
  }, 
  setHeight: function(/*elm1, elm2..., height*/) {
    var h = this.getLast(arguments);
    this.map(arguments, function(elm) { elm.style.height = this.getCssDim(h)}, 0, arguments.length-1);
  }, 
  setLeft: function(/*elm1, elm2..., left*/) {
    var l = this.getLast(arguments);
    this.map(arguments, function(elm) { elm.style.left = this.getCssDim(l)}, 0, arguments.length-1);
  }, 
  setTop: function(/*elm1, elm2..., top*/) {
    var t = this.getLast(arguments);
    this.map(arguments, function(elm) { elm.style.top = this.getCssDim(t)}, 0, arguments.length-1);
  }, 

////
// Ajax functions
////
  getXMLHttpRequest: function() {
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
  },
  
  doSimpleXMLHttpRequest: function(url) {
    var req = this.getXMLHttpRequest();
    if(url.indexOf("http://") == -1)
      url = AJS.BASE_URL + url;
    req.open("GET", url, true);
    return this.sendXMLHttpRequest(req);
  },

  getRequest: function(url, data) {
    var req = this.getXMLHttpRequest();
    if(url.indexOf("http://") == -1)
      url = AJS.BASE_URL + url;
    req.open("POST", url, true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return this.sendXMLHttpRequest(req);
  },

  sendXMLHttpRequest: function(req, data) {
    var d = new AJSDeferred(req);

    var onreadystatechange = function () {
      if (req.readyState == 4) {
        try {
          status = req.status;
        }
        catch(e) {};
        if(status == 200 || status == 304 || req.responseText == null) {
          d.callback(req, data);
        }
        else {
          d.errback();
        }
      }
    }
    req.onreadystatechange = onreadystatechange;
    return d;
  },
  
  reprString: function(o) {
    return ('"' + o.replace(/(["\\])/g, '\\$1') + '"'
    ).replace(/[\f]/g, "\\f"
    ).replace(/[\b]/g, "\\b"
    ).replace(/[\n]/g, "\\n"
    ).replace(/[\t]/g, "\\t"
    ).replace(/[\r]/g, "\\r");
  },
  
  serializeJSON: function(o) {
    var objtype = typeof(o);
    if (objtype == "undefined") {
      return "undefined";
    } else if (objtype == "number" || objtype == "boolean") {
      return o + "";
    } else if (o === null) {
      return "null";
    }
    if (objtype == "string") {
      return AJS.reprString(o);
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
        useKey = AJS.reprString(k);
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
  },

  loadJSONDoc: function(url) {
    var d = this.getRequest(url);
    var eval_req = function(req) {
      var text = req.responseText;
      if(text == "Error")
        d.errback(req);
      else
        return eval('(' + text + ')');
    };
    d.addCallback(eval_req);
    return d;
  },

  evalScriptTags: function(html) {
    var script_data = html.match(/<script.*?>((\n|\r|.)*?)<\/script>/g);
    if(script_data != null) {
      for(var i=0; i < script_data.length; i++) {
        var script_only = script_data[i].replace(/<script.*?>/g, "");
        script_only = script_only.replace(/<\/script>/g, "");
        eval(script_only);
      }
    }
  },
  
  
////
// Position
////
  getMousePos: function(e) {
    var posx = 0;
    var posy = 0;
    if (!e) var e = window.event;
    if (e.pageX || e.pageY)
    {
      posx = e.pageX;
      posy = e.pageY;
    }
    else if (e.clientX || e.clientY)
    {
      posx = e.clientX + document.body.scrollLeft;
      posy = e.clientY + document.body.scrollTop;
    }
    return [posx, posy];
  },

  findPosX: function(obj) {
    var curleft = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        curleft += obj.offsetLeft
        obj = obj.offsetParent;
      }
    }
    else if (obj.x)
      curleft += obj.x;
    return curleft;
  },

  findPosY: function(obj) {
    var curtop = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        curtop += obj.offsetTop
        obj = obj.offsetParent;
      }
    }
    else if (obj.y)
      curtop += obj.y;
    return curtop;
  },

  getScrollTop: function() {
    //From: http://www.quirksmode.org/js/doctypes.html
    var t;
    if (document.documentElement && document.documentElement.scrollTop)
        t = document.documentElement.scrollTop;
    else if (document.body)
        t = document.body.scrollTop;
    return t;
  },

  absolutePosition: function(elm) {
    var posObj = {'x': elm.offsetLeft, 'y': elm.offsetTop};
    if(elm.offsetParent) {
      var temp_pos =	this.absolutePosition(elm.offsetParent);
      posObj.x += temp_pos.x;
      posObj.y += temp_pos.y;
    }
    return posObj;
  },


////
// Events
////
  getEventElm: function(e) {
    if(e && !e.type && !e.keyCode)
      return e
    var targ;
    if (!e) var e = window.event;
    if (e.target) targ = e.target;
    else if (e.srcElement) targ = e.srcElement;
    if (targ.nodeType == 3) // defeat Safari bug
      targ = targ.parentNode;
    return targ;
  },

  //Shortcut: AJS.GRS
  getRealScope: function(fn, /*optional*/ extra_args, dont_send_event) {
    var scope = window;
    extra_args = this.$A(extra_args);
    if(fn._cscope)
      scope = fn._cscope;

    return function() {
      //Append all the orginal arguments + extra_args
      var args = [];
      var i = 0;
      if(dont_send_event)
        i = 1;

      AJS.map(arguments, function(arg) { args.push(arg) }, i);
      args = args.concat(extra_args);
      return fn.apply(scope, args);
    };
  },

  unloadListeners: function() {
    if(AJS.listeners)
      AJS.map(AJS.listeners, function(elm, type, fn) {AJS.removeEventListener(elm, type, fn)});
    AJS.listeners = [];
  },

  //Shortcut: AJS.REV
  removeEventListener: function(elm, type, fn) {
    if(elm.removeEventListener)
      elm.removeEventListener(type, fn, false);
    else if(elm.detachEvent)
      elm.detachEvent("on" + type, fn);
  },

  //Shortcut: AJS.AEV
  addEventListener: function(elm, type, fn, list_once) {
    var elms = this.$A(elm);
    this.map(elms, function(elmz) {
      if(list_once) 
        fn = this.listenOnce(elmz, type, fn);

      if(AJS.isIn(type, ['submit', 'load', 'scroll', 'resize'])) {
        var old = elm['on' + type];
        elm['on' + type] = function() {
          if(old) {
            fn(arguments);
            return old(arguments);
          }
          else
            return fn(arguments);
        };
        return;
      }
      if (elmz.attachEvent)
        elmz.attachEvent("on" + type, fn);
      else if(elmz.addEventListener)
        elmz.addEventListener(type, fn, false);

      this.listeners = AJS.$A(this.listeners);
      this.listeners.push([elmz, type, fn]);
    });
  },

  //Shortcut: AJS.$b
  bind: function(fn, bind_to, /*optional*/ extra_args, dont_send_event) {
    fn._cscope = bind_to;
    return AJS.GRS(fn, extra_args, dont_send_event);
  },

  listenOnce: function(elm, type, fn) {
    var r_fn = function() { 
      AJS.removeEventListener(elm, type, r_fn);
      fn(arguments);
    }
    return r_fn;
  },

  callLater: function(fn, interval) { 
    var fn_no_send = function() {
      fn();
    };
    window.setTimeout(fn_no_send, interval); 
  },


////
// Effects
////

////
// Misc.
////
  keys: function(obj) {
    var rval = [];
    for (var prop in obj) {
      rval.push(prop);
    }
    return rval;
  },

  urlencode: function(str) {
    return encodeURIComponent(str.toString());
  },

  isDefined: function(o) {
    return (o != "undefined" && o != null)
  },
  
  isArray: function(obj) {
    try { 
      if(this.isDefined(obj[0]))
        return true;
      else
        return false;
    }
    catch(e){ 
      return false; 
    }
  },

  isString: function(obj) {
    return (typeof obj == 'string'); 
  },

  isObject: function(obj) {
    return (typeof obj == 'object');
  },

  isDict: function(o) {
    var str_repr = String(o);
    return str_repr.indexOf(" Object") != -1;
  },

  exportToGlobalScope: function() {
    for(e in AJS)
      eval(e + " = this." + e);
  }
}

//Shortcuts
AJS.$ = AJS.getElement;
AJS.$$ = AJS.getElement;
AJS.$b = AJS.bind;
AJS.$A = AJS.createArray;
AJS.ACN = AJS.appendChildNodes;
AJS.RCN = AJS.replaceChildNodes;
AJS.AEV = AJS.addEventListener;
AJS.REV = AJS.removeEventListener;
AJS.GRS = AJS.getRealScope;
AJS.$bytc = AJS.getElementsByTagAndClassName;

AJSDeferred = function(req) {
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
    if(AJS.isObject(data)) {
      var post_data = [];
      for(k in data) {
        post_data.push(k + "=" + AJS.urlencode(data[k]));
      }
      post_data = post_data.join("&");
      this.req.send(post_data);
    }
    else if(AJS.isDefined(data))
      this.req.send(data);
    else {
      this.req.send("");
    }
  };
};
AJSDeferred.prototype = new AJSDeferred();

//Prevent memory-leaks
AJS.addEventListener(window, 'unload', AJS.unloadListeners);
