/**
 * XML Load用クラス for IE+MSXML/Gecko (xmlloader.js)
 * Script Version: 0.1.0
 * 
 * [constructor]
 * @param function _loadHandler
 * @param function _errorHandler
 **/
function XmlLoader(_loadHandler, _errorHandler)
{
	if(_errorHandler==null) _errorHandler = function(err){};

	this.onload = _loadHandler;
	this.onerror= _errorHandler;

	/**
	 * Geckoの場合、連続して読み込むとうまくいかない場合があるので
	 * loadHandler の実行を_geckoWaitTime ミリ秒待つ
	 * 
	 * @access private
	 * @var int
	 **/
	this._geckoWaitTime = 50;

	/**
	 * @access public
	 * @param string url
	 * @return bool
	 **/
	this.load = function(url,postdata) {
		var xmldoc; //DOMDocument
		var loadHandler, errorHandler;

		loadHandler = this.onload;
		errorHandler= this.onerror;

		if(document.implementation && document.implementation.createDocument)
		{
			// DOM Core Level2(DOMImplementation)
			xmldoc = document.implementation.createDocument("", "", null);
			xmldoc.onload = function()
			{
				if(xmldoc.documentElement==null) {
					errorHandler(new XmlLoaderError("指定されたアドレスが見つかりません。", url));
				} else if(XmlLoaderError.isError(xmldoc)) {
					var e = XmlLoaderError.fromXml(xmldoc);
					errorHandler(e);
				} else {
					setTimeout(function(){ loadHandler(xmldoc); }, this._geckoWaitTime);
				}
			}
		}
		else if(window.ActiveXObject && document.getElementById)
		{
			xmldoc = new ActiveXObject('Microsoft.XMLDOM');
			xmldoc.onreadystatechange = function()
			{
				if(xmldoc.readyState==4) {
					if(XmlLoaderError.isError(xmldoc)) {
						errorHandler(XmlLoaderError.fromIXMLDOMParseError(xmldoc.parseError));
					} else {
						loadHandler(xmldoc);
					}
				}
			}
		}
		else {
			return false;
		}

		
		try {
		    if(postdata==undefined) {
				xmldoc.load(url);
		    } else {
				xmldoc.open("POST",url,true);
				xmldoc.send(postdata);
		    }
		}
		catch(e) {
			errorHandler(new XmlLoaderError("指定されたリソースが見つかりません。" + e.toString(), url));
		}
		return true;
	}
	this.loadsync = function(url, postdata) {
		var xmldoc; //DOMDocument
		var loadHandler, errorHandler;

		loadHandler = this.onload;
		errorHandler= this.onerror;

		if(document.implementation && document.implementation.createDocument)
		{
			// DOM Core Level2(DOMImplementation)
			xmldoc = document.implementation.createDocument("", "", null);
			xmldoc.async = false;
			xmldoc.onload = function()
			{
				if(xmldoc.documentElement==null) {
					errorHandler(new XmlLoaderError("指定されたアドレスが見つかりません。", url));
				} else if(XmlLoaderError.isError(xmldoc)) {
					var e = XmlLoaderError.fromXml(xmldoc);
					errorHandler(e);
				} else {
					setTimeout(function(){ loadHandler(xmldoc); }, this._geckoWaitTime);
				}
			}
		}
		else if(window.ActiveXObject && document.getElementById)
		{
			xmldoc = new ActiveXObject('Microsoft.XMLDOM');
			xmldoc.async = false;
			xmldoc.onreadystatechange = function()
			{
				if(xmldoc.readyState==4) {
					if(XmlLoaderError.isError(xmldoc)) {
						errorHandler(XmlLoaderError.fromIXMLDOMParseError(xmldoc.parseError));
					} else {
						loadHandler(xmldoc);
					}
				}
			}
		}
		else {
			return false;
		}

		
		try {
		    if(postdata==undefined) {
				xmldoc.load(url);
		    } else {
				xmldoc.open("POST",url,true);
				xmldoc.send(postdata);
		    }
		}
		catch(e) {
			errorHandler(new XmlLoaderError("指定されたリソースが見つかりません。" + e.toString(), url));
		}
		return xmldoc;
	}
}
/**
 * @static
 * @param string url
 * @param function loadHandler
 * @param function errorHandler
 * @return bool
 **/
XmlLoader.load = function(url, loadHandler, errorHandler) {
	var loader = new XmlLoader(loadHandler, errorHandler);
	return loader.load(url);
}

/**
 * class XmlLoaderError
 * 
 * [constructor]
 * @param string reason
 * @param string url
 * @param int line
 * @param int pos
 * @param string src
 * 
 **/
function XmlLoaderError(reason, url, line, pos, src) {
	this.reason  = reason!=null ? reason: "";
	this.url     = url   !=null ? url   : "";
	this.line    = line  !=null ? line  : 0;
	this.linepos = pos   !=null ? pos   : 0;
	this.srcText = src   !=null ? src   : "";
	
	this.toString= function() {
		return this.reason +"\nURL:"+ this.url 
			+"\nLine "+ this.line +", Column "+ this.linepos +"\nsourcetext:\n"+ this.srcText;
	}
}
/**
 * @static
 * @param DOMDocument doc
 * @return bool
 **/
XmlLoaderError.isError = function(doc) {
	return false;
	return (doc.parseError!=null && doc.parseError.errorCode!=0) ||
		(doc.documentElement.tagName=='parsererror' 
			&& doc.documentElement.namespaceURI=='http://www.mozilla.org/newlayout/xml/parsererror.xml');
}

/**
 * @static
 * @param DOMDocument doc
 * @return XmlLoaderError
 **/
XmlLoaderError.fromXml = function(doc) {
	var errElm = doc.documentElement;
	var msgs = errElm.firstChild.nodeValue.split(/\n/);
	var reason = msgs[0];
	var url = msgs[1].replace(/^.+[: ](.+)/, "$1");
	var pos = msgs[2].replace(/^[^\d]+(\d+)[^\d]+(\d+)[^\d]*$/, "$1;$2").split(/;/);
	var src = errElm.getElementsByTagName('sourcetext').item(0).firstChild.nodeValue;

	return new XmlLoaderError(reason, url, pos[0], pos[1], src);
}

/**
 * @static
 * @param IXMLDOMParseError perr
 * @return XmlLoaderError
 **/
XmlLoaderError.fromIXMLDOMParseError = function(perr) {
	var e = new XmlLoaderError();
	for(var p in e) { e[p]=perr[p]; }
	return e;
}
