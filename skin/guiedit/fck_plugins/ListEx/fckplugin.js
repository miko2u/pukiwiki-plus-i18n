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


////////////////////////////////////////////////////////////////////////////////
//	ツールバー・ボタン

FCKToolbarItems.RegisterItem('DList', new FCKToolbarButton('DList', FCKLang.DList));

FCK.Events.AttachEvent('OnSelectionChange', function() { FCKToolbarItems.GetItem('DList').RefreshState() });


////////////////////////////////////////////////////////////////////////////////
//	コマンド

//	定義リスト挿入
FCKCommands.RegisterCommand('DList',
	new FCKDialogCommand('DList', FCKLang.DListDlgTitle, FCKPlugins.Items['ListEx'].Path + 'DList.html', 400, 200)
);

FCKCommands.GetCommand('DList').TagName = 'DL';

var aListName = new Array('InsertOrderedList', 'InsertUnorderedList', 'DList');
for (j = 0; j < aListName.length; j++) {
	FCKCommands.GetCommand(aListName[j]).GetState = function() {
		var tags = new Array('H2', 'H3', 'H4', 'PRE', 'TABLE');
		for (i = 0; i < tags.length; i++) {
			if (FCKSelection.HasAncestorNode(tags[i])) {
				return FCK_TRISTATE_DISABLED;
			}
		}
		
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG || ! FCK.EditorWindow )
			return FCK_TRISTATE_DISABLED ;

		var startContainer = FCKSelection.GetBoundaryParentElement( true ) ;
		var listNode = startContainer ;
		while ( listNode )
		{
			if ( listNode.nodeName.IEquals( [ 'ul', 'ol' , 'dl' ] ) )
				break ;
			listNode = listNode.parentNode ;
		}
		if ( listNode && listNode.nodeName.IEquals( this.TagName ) )
			return FCK_TRISTATE_ON ;
		else
			return FCK_TRISTATE_OFF ;
	}
}

//	定義リスト削除
FCKCommands.RegisterCommand('DListDelete', {
	Execute : function() {
		var oDList = FCKSelection.MoveToAncestorNode('DL');
		if (!oDList) {
			return;
		}
		
		FCKUndo.SaveUndoStep();
		
		oDList.parentNode.removeChild(oDList);
		
		FCKUndo.SaveUndoStep();
	},

	GetState : function() { return FCK_TRISTATE_OFF; }
});

//	定義項目削除
FCKCommands.RegisterCommand('DListItemDelete', {
	Execute : function() {
		var oNode = FCKSelection.MoveToAncestorNode('DT') || FCKSelection.MoveToAncestorNode('DD');
		if (!oNode) {
			return;
		}
		
		FCKUndo.SaveUndoStep();
		
		if (oNode.tagName == 'DT') {
			var oNextNode = oNode.nextSibling;
			if (oNextNode && oNextNode.tagName == 'DD') {
				oNode.parentNode.removeChild(oNextNode);
			}
		}
		else {
			var oPrevNode = oNode.previousSibling;
			if (oPrevNode && oPrevNode.tagName == 'DT') {
				oNode.parentNode.removeChild(oPrevNode);
			}
		}
		oNode.parentNode.removeChild(oNode);
		
		FCKUndo.SaveUndoStep();
	},

	GetState : function() { return FCK_TRISTATE_OFF; }
});


////////////////////////////////////////////////////////////////////////////////
//	コンテキストメニュー

FCK.ContextMenu.RegisterListener( {
	AddItems : function(menu, tag, tagName) {
		if (FCKSelection.HasAncestorNode('DL')) {
			menu.AddSeparator();
			menu.AddItem('DList', FCKLang.DListItemInsert);
			menu.AddItem('DListItemDelete', FCKLang.DListItemDelete);
			menu.AddSeparator();
			menu.AddItem('DListDelete', FCKLang.DListDelete);
	 	}
	}}
);
