//
// MSXML Emulation Based Library
// Based on http://sarissa.sourceforge.net/
//
function ElementNodeList(i){
	this.length = i;
};
ElementNodeList.prototype = new Array(0);
ElementNodeList.prototype.constructor = Array;
ElementNodeList.prototype.item = function(i) {
    return (i < 0 || i >= this.length)?null:this[i];
};
ElementNodeList.prototype.expr = "";

//
// MSXML Emulation
// Based on http://sarissa.sourceforge.net/
//
if(document.implementation && document.implementation.createDocument)
{
	// MSXML XPath Emulation
	if (document.implementation.hasFeature("XPath", "3.0")) {
		// MSXML selectNodes
		Element.prototype.selectNodes = function(sExpr) {
			var doc = this.ownerDocument;
			if(doc.selectNodes)
				return doc.selectNodes(sExpr, this);
			else
				throw "Method selectNodes is only supported by XML Elements";
		};

		// MSXML selectSingleNode
		Element.prototype.selectSingleNode = function(sExpr){
			var doc = this.ownerDocument;
			if(doc.selectSingleNode)
				return doc.selectSingleNode(sExpr, this);
			else
				throw "Method selectNodes is only supported by XML Elements";
		};
	}
}
