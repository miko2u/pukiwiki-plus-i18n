/**
 * HTTP load class for IE+MSXML/Gecko (textloader.js)
 * Script Version: 0.1.1 (c)2005 Miko.Hoshina
 *
 * [constructor]
 * @param function _loadHandler
 * @param function _errorHandler
 **/
function TextLoader(_loadHandler, _errorHandler)
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
		var xmlhttp; //DOMDocument
		var loadHandler, errorHandler;

		loadHandler = this.onload;
		errorHandler= this.onerror;

		try {
			xmlhttp = new ActiveXObject("MSXML2.XMLHTTP");
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		if (!xmlhttp) {
			return false;
		}

		xmlhttp.onreadystatechange = function()
		{
			if(xmlhttp.readyState==4) {
				if(TextLoaderError.isError(xmlhttp)) {
					errorHandler(new TextLoaderError("指定されたアドレスが見つかりません。(" + xmlhttp.status + ")", url) );
				} else {
					loadHandler(xmlhttp);
				}
			}
		}

		
		try {
		    if(postdata==undefined) {
				xmlhttp.open("GET", url);
				xmlhttp.send(null);
		    } else {
				xmlhttp.open("POST", url);
				xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xmlhttp.send(postdata);
		    }
		}
		catch(e) {
			errorHandler(new TextLoaderError("指定されたリソースが見つかりません。" + e.toString(), url));
		}
		return true;
	}
}
/**
 * @static
 * @param string url
 * @param function loadHandler
 * @param function errorHandler
 * @return bool
 **/
TextLoader.load = function (url, loadHandler, errorHandler) {
	var loader = new TextLoader(loadHandler, errorHandler);
	return loader.load(url);
}

/**
 * class TextLoaderError
 * 
 * [constructor]
 * @param string reason
 * @param string url
 * @param int line
 * @param int pos
 * @param string src
 * 
 **/
function TextLoaderError(reason, url, line, pos, src) {
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
TextLoaderError.isError = function(doc) {
	return (doc.status != 200);
}
