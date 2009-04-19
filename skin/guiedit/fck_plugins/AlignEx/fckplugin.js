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


var aCommandName = ['JustifyLeft', 'JustifyCenter', 'JustifyRight'];

for (j = 0; j < 3; j++) {
	FCKCommands.GetCommand(aCommandName[j]).GetState = function() {
		var tags = new Array('H2', 'H3', 'H4', 'PRE', 'TABLE', 'DT');
		for (i = 0; i < tags.length; i++) {
			if (FCKSelection.HasAncestorNode(tags[i])) {
				return FCK_TRISTATE_DISABLED;
			}
		}
		
		var oElement = FCKSelection.GetSelectedElement();
		if (oElement && oElement.tagName.Equals('HR', 'IMG', 'TABLE', 'DIV', 'SPAN')) {
			return FCK_TRISTATE_DISABLED;
		}
		
		oElement = FCKSelection.GetParentElement();
		if (oElement && oElement.tagName.Equals('DIV') && oElement.className.Equals('plugin', 'ref')) {
			return FCK_TRISTATE_DISABLED;
		}
		
		// Disabled if not WYSIWYG.
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG || ! FCK.EditorWindow )
			return FCK_TRISTATE_DISABLED ;

		// Retrieve the first selected block.
		var path = new FCKElementPath( FCKSelection.GetBoundaryParentElement( true ) ) ;
		var firstBlock = path.Block || path.BlockLimit ;

		if ( !firstBlock || firstBlock.nodeName.toLowerCase() == 'body' )
			return FCK_TRISTATE_OFF ;

		// Check if the desired style is already applied to the block.
		var currentAlign ;
		if ( FCKBrowserInfo.IsIE )
			currentAlign = firstBlock.currentStyle.textAlign ;
		else
			currentAlign = FCK.EditorWindow.getComputedStyle( firstBlock, '' ).getPropertyValue( 'text-align' );
		currentAlign = currentAlign.replace( /(-moz-|-webkit-|start|auto)/i, '' );
		if ( ( !currentAlign && this.IsDefaultAlign ) || currentAlign == this.AlignValue )
			return FCK_TRISTATE_ON ;
		return FCK_TRISTATE_OFF ;
	}
}
