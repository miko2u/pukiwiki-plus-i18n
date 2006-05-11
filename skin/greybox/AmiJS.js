/*
Last Modified: 28/04/06 16:28:09

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
    2.1
  SITE
    http://amix.dk/amijs
**/

var AJS = {
////
// Accessor functions
////
  /**
   * @returns The element with the id
   */
  getElement: function(id) {
    if(typeof(id) == "string") 
      return document.getElementById(id);
    else
      return id;
  },

  /**
   * @returns The elements with the ids
   */
  getElements: function(/*id1, id2, id3*/) {
    var elements = new Array();
      for (var i = 0; i < arguments.length; i++) {
        var element = this.getElement(arguments[i]);
        elements.push(element);
      }
      return elements;
  },

  /**
   * @returns The GET query argument
   */
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

  /**
   * @returns If the browser is Internet Explorer
   */
  isIe: function() {
    return (navigator.userAgent.toLowerCase().indexOf("msie") != -1 && navigator.userAgent.toLowerCase().indexOf("opera") == -1);
  },

  /**
   * @returns The document body   
   */
  getBody: function() {
    return this.getElementsByTagAndClassName('body')[0] 
  },

  /**
   * @returns All the elements that have a specific tag name or class name
   */
  getElementsByTagAndClassName: function(tag_name, class_name, /*optional*/ parent) {
    var class_elements = new Array();
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


////
// DOM manipulation
////
  /**
   * Appends some nodes to a node
   */
  appendChildNodes: function(node/*, nodes...*/) {
    if(arguments.length >= 2) {
      for(var i=1; i < arguments.length; i++) {
        var n = arguments[i];
        if(typeof(n) == "string")
          n = document.createTextNode(n);
        if(this.isDefined(n))
          node.appendChild(n);
      }
    }
    return node;
  },

  /**
   * Replaces a nodes children with another node(s)
   */
  replaceChildNodes: function(node/*, nodes...*/) {
    var child;
    while ((child = node.firstChild)) {
      node.removeChild(child);
    }
    if (arguments.length < 2) {
      return node;
    } else {
      return this.appendChildNodes.apply(this, arguments);
    }
  },

  /**
   * Insert a node after another node
   */
  insertAfter: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode.nextSibling);
  },
  
  /**
   * Insert a node before another node
   */
  insertBefore: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode);
  },
  
  /**
   * Shows the element
   */
  showElement: function(elm) {
    elm.style.display = '';
  },
  
  /**
   * Hides the element
   */
  hideElement: function(elm) {
    elm.style.display = 'none';
  },

  isElementHidden: function(elm) {
    return elm.style.visibility == "hidden";
  },
  
  /**
   * Swaps one element with another. To delete use swapDOM(elm, null)
   */
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

  /**
   * Removes an element from the world
   */
  removeElement: function(elm) {
    this.swapDOM(elm, null);
  },

  /**
   * @returns Is an object a dictionary?
   */
  isDict: function(o) {
    var str_repr = String(o);
    return str_repr.indexOf(" Object") != -1;
  },
  
  /**
   * Creates a DOM element
   * @param {String} name The elements DOM name
   * @param {Dict} attrs Attributes sent to the function
   */
  createDOM: function(name, attrs) {
    var i=0;
    elm = document.createElement(name);

    if(this.isDict(attrs[i])) {
      for(k in attrs[0]) {
        if(k == "style")
          elm.style.cssText = attrs[0][k];
        else if(k == "class")
          elm.className = attrs[0][k];
        else
          elm.setAttribute(k, attrs[0][k]);
      }
      i++;
    }

    if(attrs[0] == null)
      i = 1;

    for(i; i < attrs.length; i++) {
      var n = attrs[i];
      if(this.isDefined(n)) {
        if(typeof(n) == "string")
          n = document.createTextNode(n);
        elm.appendChild(n);
      }
    }
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

////
// Ajax functions
////
  /**
   * @returns A new XMLHttpRequest object 
   */
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
  
  /**
   * Use this function to do a simple HTTP Request
   */
  doSimpleXMLHttpRequest: function(url) {
    var req = this.getXMLHttpRequest();
    req.open("GET", url, true);
    return this.sendXMLHttpRequest(req);
  },

  getRequest: function(url, data) {
    var req = this.getXMLHttpRequest();
    req.open("POST", url, true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return this.sendXMLHttpRequest(req);
  },

  /**
   * Send a XMLHttpRequest
   */
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
  
  /**
   * Represent an object as a string
   */
  reprString: function(o) {
    return ('"' + o.replace(/(["\\])/g, '\\$1') + '"'
    ).replace(/[\f]/g, "\\f"
    ).replace(/[\b]/g, "\\b"
    ).replace(/[\n]/g, "\\n"
    ).replace(/[\t]/g, "\\t"
    ).replace(/[\r]/g, "\\r");
  },
  
  /**
   * Serialize an object to JSON notation
   */
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
      return this.reprString(o);
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
        useKey = this.reprString(k);
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

  /**
   * Send and recive JSON using GET
   */
  loadJSONDoc: function(url) {
    var d = this.getRequest(url);
    var eval_req = function(req) {
      var text = req.responseText;
      return eval('(' + text + ')');
    };
    d.addCallback(eval_req);
    return d;
  },
  
  
////
// Misc.
////
  /**
   * Alert the objects key attrs 
   */
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

  /**
   * @returns True if the object is defined, otherwise false
   */
  isDefined: function(o) {
    return (o != "undefined" && o != null)
  },
  
  /**
   * @returns True if an object is a array, false otherwise
   */
  isArray: function(obj) {
    try { return (typeof(obj.length) == "undefined") ? false : true; }
    catch(e)
    { return false; }
  },

  isObject: function(obj) {
    return (obj && typeof obj == 'object');
  },

  /**
   * Export DOM elements to the global namespace
   */
  exportDOMElements: function() {
    UL = this.UL;
    LI = this.LI;
    TD = this.TD;
    TR = this.TR;
    TH = this.TH;
    TBODY = this.TBODY;
    TABLE = this.TABLE;
    INPUT = this.INPUT;
    SPAN = this.SPAN;
    B = this.B;
    A = this.A;
    DIV = this.DIV;
    IMG = this.IMG;
    BUTTON = this.BUTTON;
    H1 = this.H1;
    H2 = this.H2;
    H3 = this.H3;
    BR = this.BR;
    TEXTAREA = this.TEXTAREA;
    FORM = this.FORM;
    P = this.P;
    SELECT = this.SELECT;
    OPTION = this.OPTION;
    TN = this.TN;
    IFRAME = this.IFRAME;
    SCRIPT = this.SCRIPT;
  },

  /**
   * Export AmiJS functions to the global namespace
   */
  exportToGlobalScope: function() {
    getElement = this.getElement;
    getQueryArgument = this.getQueryArgument;
    isIe = this.isIe;
    $ = this.getElement;
    getElements = this.getElements;
    getBody = this.getBody;
    getElementsByTagAndClassName = this.getElementsByTagAndClassName;
    appendChildNodes = this.appendChildNodes;
    ACN = appendChildNodes;
    replaceChildNodes = this.replaceChildNodes;
    RCN = replaceChildNodes;
    insertAfter = this.insertAfter;
    insertBefore = this.insertBefore;
    showElement = this.showElement;
    hideElement = this.hideElement;
    isElementHidden = this.isElementHidden;
    swapDOM = this.swapDOM;
    removeElement = this.removeElement;
    isDict = this.isDict;
    createDOM = this.createDOM;
    this.exportDOMElements();
    getXMLHttpRequest = this.getXMLHttpRequest;
    doSimpleXMLHttpRequest = this.doSimpleXMLHttpRequest;
    getRequest = this.getRequest;
    sendXMLHttpRequest = this.sendXMLHttpRequest;
    reprString = this.reprString;
    serializeJSON = this.serializeJSON;
    loadJSONDoc = this.loadJSONDoc;
    keys = this.keys;
    isDefined = this.isDefined;
    isArray = this.isArray;
  }
}



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
