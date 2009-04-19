//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2007 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2007 Frederico Caldeira Knabben
//


//	FCKSelection
FCKSelection.GetSelectedHtml = function() {
	var html;
	var oSelection = FCKSelection.GetSelection2();
	
	if (!oSelection) {
		return null;
	}
	
	if (FCKBrowserInfo.IsIE) {
		html = oSelection.createRange().htmlText;
		html = html.replace(/\r\n/g, '');
	}
	else {
		var oRange = oSelection.getRangeAt(0);
		var oElement = document.createElement('BODY');
		oElement.appendChild(oRange.cloneContents());
		html = oElement.innerHTML;
	}
	
	return html;
};

FCKSelection.GetSelectedElement2 = function() {
	if (FCKBrowserInfo.IsOpera || FCKBrowserInfo.IsSafari) {
		return FCKSelection.GetParentElement();
	}
	
	return FCKSelection.GetSelectedElement();
};

FCKSelection.GetSelection2 = function() {
	if (FCKBrowserInfo.IsIE) {
		return FCK.EditorDocument.selection;
	}
	
	return FCK.EditorWindow.getSelection();
};


//	FCKCodeFormatter
FCKCodeFormatter.Init = function() {
	var oRegex = this.Regex = new Object() ;

	// Regex for line breaks.
	oRegex.BlocksOpener = /\<(P|DIV|H2|H3|H4|PRE|OL|UL|LI|DL|DT|DD|TD|TH|BLOCKQUOTE)[^\>]*\>/gi ;
	oRegex.BlocksCloser = /\<\/(P|DIV|H2|H3|H4|PRE|OL|UL|LI|DL|DT|DD|TD|TH|BLOCKQUOTE)[^\>]*\>/gi ;

	oRegex.NewLineTags	= /\<(HR)[^\>]*\>/gi ;

	oRegex.MainTags = /\<\/?(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR)[^\>]*\>/gi ;

	oRegex.LineSplitter = /\s*\n+\s*/g ;

	oRegex.ProtectedTags = /(<PRE[^>]*>)([\s\S]*?)(<\/PRE>)/gi ;
}

FCKCodeFormatter._ProtectData = function( outer, opener, data, closer ) {
	return opener + '___FCKpd___' + ( FCKCodeFormatter.ProtectedData.push( data ) - 1 ) + closer ;
}

FCKCodeFormatter._InlineProtectData = function(data) {
	return "___FCKipd___" + ( FCKCodeFormatter.InlineProtectedData.push(data) - 1);
}

FCKCodeFormatter.Format = function( html ) {
	if ( !this.Regex )
		this.Init() ;

	// Protected content that remain untouched during the
	// process go in the following array.
	FCKCodeFormatter.ProtectedData = new Array() ;
	FCKCodeFormatter.InlineProtectedData = new Array();

	var sFormatted = html.replace( this.Regex.ProtectedTags, FCKCodeFormatter._ProtectData ) ;
	var sFormatted = html.replace( /<IMG[^>]*>/gi,  FCKCodeFormatter._InlineProtectData) ;

	// Line breaks.
	sFormatted		= sFormatted.replace( this.Regex.BlocksOpener, '\n$&' ) ;
	sFormatted		= sFormatted.replace( this.Regex.BlocksCloser, '$&\n' ) ;
	sFormatted		= sFormatted.replace( this.Regex.NewLineTags, '$&\n' ) ;
	sFormatted		= sFormatted.replace( this.Regex.MainTags, '\n$&\n' ) ;
	sFormatted		= sFormatted.replace( this.Regex.LineSplitter, '\n' ) ;
	sFormatted		= sFormatted.replace( /(\<BLOCKQUOTE\>)\n?/gi, '$1\n' ) ;
	sFormatted		= sFormatted.replace( /\n?(\<\/BLOCKQUOTE\>)/gi, '\n$1' ) ;
	sFormatted		= sFormatted.replace( /((\<|&lt;)BR\s\/(>|&gt;))\n/gi, '$1' ) ;

	// Now we put back the protected data.
	for ( var j = 0 ; j < FCKCodeFormatter.ProtectedData.length ; j++ )
	{
		var oRegex = new RegExp( '___FCKpd___' + j ) ;
		var sData = FCKCodeFormatter.ProtectedData[j].replace( /\$/g, '$$$$' );
	//	sData = sData.replace(/(<BR\s\/>)?\n/gi, "<br />");
		sFormatted = sFormatted.replace( oRegex,  sData) ;
	}

	for (i = 0; i < FCKCodeFormatter.InlineProtectedData.length; i++) {
		oRegex = new RegExp( '___FCKipd___' + i ) ;
		sData = FCKCodeFormatter.InlineProtectedData[i].replace(/\r?\n/g, "___br___");
		sFormatted = sFormatted.replace(oRegex, sData);
	}

	return sFormatted.Trim() ;
}


//	FCKXHtmlEntities
FCKXHtmlEntities.Initialize = function() {
	var sChars = '' ;

	FCKXHtmlEntities.Entities = {
		// Latin-1 Entities
		' ':'nbsp',
		'¡':'iexcl',
		'¢':'cent',
		'£':'pound',
		'¤':'curren',
		'¥':'yen',
		'¦':'brvbar',
		'©':'copy',
		'ª':'ordf',
		'«':'laquo',
		'¬':'not',
		'­':'shy',
		'®':'reg',
		'¯':'macr',
		'²':'sup2',
		'³':'sup3',
		'µ':'micro',
		'·':'middot',
		'¸':'cedil',
		'¹':'sup1',
		'º':'ordm',
		'»':'raquo',
		'¼':'frac14',
		'½':'frac12',
		'¾':'frac34',
		'¿':'iquest',

		// Symbols
		'ƒ':'fnof',
		'•':'bull',
		'‾':'oline',
		'⁄':'frasl',
		'℘':'weierp',
		'ℑ':'image',
		'ℜ':'real',
		'™':'trade',
		'ℵ':'alefsym',
		'↔':'harr',
		'↵':'crarr',
		'⇐':'lArr',
		'⇑':'uArr',
		'⇓':'dArr',
		'∅':'empty',
		'∉':'notin',
		'∏':'prod',
		'−':'minus',
		'∗':'lowast',
		'∼':'sim',
		'≅':'cong',
		'≈':'asymp',
		'≤':'le',
		'≥':'ge',
		'⊄':'nsub',
		'⊕':'oplus',
		'⊗':'otimes',
		'⋅':'sdot',
		'◊':'loz',
		'♠':'spades',
		'♣':'clubs',
		'♥':'hearts',
		'♦':'diams',
			
		//Latin Letters Entities
		'À':'Agrave',
		'Á':'Aacute',
		'Â':'Acirc',
		'Ã':'Atilde',
		'Ä':'Auml',
		'Å':'Aring',
		'Æ':'AElig',
		'Ç':'Ccedil',
		'È':'Egrave',
		'É':'Eacute',
		'Ê':'Ecirc',
		'Ë':'Euml',
		'Ì':'Igrave',
		'Í':'Iacute',
		'Î':'Icirc',
		'Ï':'Iuml',
		'Ð':'ETH',
		'Ñ':'Ntilde',
		'Ò':'Ograve',
		'Ó':'Oacute',
		'Ô':'Ocirc',
		'Õ':'Otilde',
		'Ö':'Ouml',
		'Ø':'Oslash',
		'Ù':'Ugrave',
		'Ú':'Uacute',
		'Û':'Ucirc',
		'Ü':'Uuml',
		'Ý':'Yacute',
		'Þ':'THORN',
		'ß':'szlig',
		'à':'agrave',
		'á':'aacute',
		'â':'acirc',
		'ã':'atilde',
		'ä':'auml',
		'å':'aring',
		'æ':'aelig',
		'ç':'ccedil',
		'è':'egrave',
		'é':'eacute',
		'ê':'ecirc',
		'ë':'euml',
		'ì':'igrave',
		'í':'iacute',
		'î':'icirc',
		'ï':'iuml',
		'ð':'eth',
		'ñ':'ntilde',
		'ò':'ograve',
		'ó':'oacute',
		'ô':'ocirc',
		'õ':'otilde',
		'ö':'ouml',
		'ø':'oslash',
		'ù':'ugrave',
		'ú':'uacute',
		'û':'ucirc',
		'ü':'uuml',
		'ý':'yacute',
		'þ':'thorn',
		'ÿ':'yuml',
		'Œ':'OElig',
		'œ':'oelig',
		'Š':'Scaron',
		'š':'scaron',
		'Ÿ':'Yuml',
		'ς':'sigmaf',

		// Other Special Characters 
		'"':'quot',
		'ˆ':'circ',
		'˜':'tilde',
		' ':'ensp',
		' ':'emsp',
		' ':'thinsp',
		'‌':'zwnj',
		'‍':'zwj',
		'‎':'lrm',
		'‏':'rlm',
		'–':'ndash',
		'—':'mdash',
		'‚':'sbquo',
		'„':'bdquo',
		'‹':'lsaquo',
		'›':'rsaquo',
		'€':'euro'
	};

	for (e in FCKXHtmlEntities.Entities) {
		sChars += e;
	}

	var sRegexPattern = '[' + sChars + ']';
	
	FCKXHtmlEntities.EntitiesRegex = new RegExp(sRegexPattern, 'g');
}
