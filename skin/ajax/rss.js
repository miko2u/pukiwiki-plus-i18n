/**
 * RSSをHTML出力するクラス
 */
function RssRenderer(){
	this.rssTitle;
	this.rssLink;
	this.rssDescription;
	this.rssItems;
}

RssRenderer.prototype.write = function( rss , htmlObj){
	document.title = rss.title;
	
	// ページのタイトル
	if( this.rssTitle ){
		var a = this.rssTitle.firstChild;
		a.setAttribute('href', rss.link);
		a.firstChild.nodeValue = rss.title;
	}
	else{
		var a = document.createElement('a');
		a.setAttribute( 'href' , rss.link );
		a.appendChild( document.createTextNode(rss.title) );
		
		this.rssTitle = document.createElement('h4');
		this.rssTitle.appendChild(a);
		
		htmlObj.appendChild(this.rssTitle);
	}
	
	// ページの説明
	if( this.rssDescription ){
		this.rssDescription.firstChild.nodeValue = rss.description;
	}
	else{
		this.rssDescription = document.createElement('p');
		this.rssDescription.appendChild( document.createTextNode(rss.description) );
		htmlObj.appendChild(this.rssDescription);
	}
	
	// 項目一覧
	if( this.rssItems ){
		while( this.rssItems.hasChildNodes() ){
			this.rssItems.removeChild( this.rssItems.lastChild );
		}
	}
	else{
		this.rssItems = document.createElement('dl');
		htmlObj.appendChild(this.rssItems);
	}
	
	for( var i = 0 ; i < rss.items.length ; i++ ){
	
		var a = document.createElement('a');
		a.setAttribute('href', rss.items[i].link );
		a.appendChild(document.createTextNode(rss.items[i].title));
		
		var dt = document.createElement('dt');
		dt.appendChild(a);
		
		this.rssItems.appendChild(dt);
		
		
		var dd = document.createElement('dd');
		if( rss.items[i].description ){
			dd.appendChild( document.createTextNode( rss.items[i].description ) );
		}
		else{
			dd.style.display = 'none' ;
		}
		this.rssItems.appendChild(dd);
	}
};


function RssItem(){
	this.title;
	this.link;
	this.description;
}

function Rss(){
	this.title;
	this.link;
	this.description;
	this.image;
	this.items;
}


Rss.prototype.load = function( uri ) {

	this.items = new Array();

	// parse RSS
	this.parseRss = function(xml) {
		xml.setProperty('SelectionLanguage' , 'XPath');

		this.title = xml.selectSingleNode('/rss/channel/title/text()').nodeValue;
		this.link = xml.selectSingleNode('/rss/channel/link/text()').nodeValue;
		this.description = xml.selectSingleNode('/rss/channel/description/text()').nodeValue;

		//var items = xml.getElementsByTagName('item');
		var items = xml.selectNodes('/rss/channel/item');
		for( var i = 0 ; i < items.length ; i++){
			var rItem = new RssItem();
			try {
				rItem.title = items[i].selectSingleNode("title/text()").nodeValue;
				rItem.link = items[i].selectSingleNode("link/text()").nodeValue;
				if( items[i].selectSingleNode("description/text()") ){
					rItem.description = items[i].selectSingleNode("description/text()").nodeValue;
				}
			}
			catch(e) {
				continue;
			}
			this.items.push(rItem);
		}
	};

	// parse RDF
	this.parseRdf = function(xml) {

		xml.setProperty('SelectionLanguage' , 'XPath');
		xml.setProperty("SelectionNamespaces" , "xmlns:rss='http://purl.org/rss/1.0/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'");

		this.title = xml.selectSingleNode('/rdf:RDF/rss:channel/rss:title/text()').nodeValue;
		this.link = xml.selectSingleNode('/rdf:RDF/rss:channel/rss:link/text()').nodeValue;
		this.description = xml.selectSingleNode('/rdf:RDF/rss:channel/rss:description/text()').nodeValue;

		//var items = xml.getElementsByTagNameNS( 'rdf','item' );
		//var items = xml.getElementsByTagName( 'item' );
		var items = xml.selectNodes( '/rdf:RDF/rss:item' );
		for( var i = 0 ; i < items.length ; i++){
			var rItem = new RssItem();
			try {
				rItem.title = items[i].selectSingleNode("rss:title/text()").nodeValue;
				rItem.link = items[i].selectSingleNode("rss:link/text()").nodeValue;
				if( items[i].selectSingleNode("rss:description/text()") ){
					rItem.description = items[i].selectSingleNode("rss:description/text()").nodeValue;
				}
			}
			catch(e) {
				continue;
			}
			this.items.push(rItem);
		}
	};

	// parse ATOM
	this.parseAtom = function(xml) {

		xml.setProperty('SelectionLanguage' , 'XPath');
		xml.setProperty("SelectionNamespaces" , "xmlns:rss='http://purl.org/atom/ns#'");

		this.title = xml.selectSingleNode('/rss:feed/rss:head/rss:title/text()').nodeValue;
		this.link = xml.selectSingleNode('/rdf:RDF/rss:link').getAttribute('href');
		this.description = xml.selectSingleNode('/rdf:RDF/rss:tagline/text()').nodeValue;

		var items = xml.selectNodes( '/rdf:feed/rss:entry' );
		for( var i = 0 ; i < items.length ; i++){
			var rItem = new RssItem();
			try{
				rItem.title = items[i].selectSingleNode("rss:title/text()").nodeValue;
				rItem.link = items[i].selectSingleNode("rss:link").getAttribute('href');
				if( items[i].selectSingleNode("rss:summary/text()") ){
					rItem.description = items[i].selectSingleNode("rss:summary/text()").nodeValue;
				}
			}
			catch(e){
				continue;
			}
			this.items.push(rItem);
		}
	};

	xml_onload = function(xmldoc) {
	};

	var xml = new XmlLoader(xml_onload,null);
	var xmldoc = xml.loadsync(uri);

	if( xmldoc.getElementsByTagName('rss').length > 0 ){
		this.parseRss(xmldoc);
	}
	else{
		this.parseRdf(xmldoc);
	}
};

