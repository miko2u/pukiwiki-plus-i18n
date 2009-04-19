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


// コマンド
FCKCommands.RegisterCommand('Comment',
	new FCKDialogCommand('Comment', FCKLang.CommentDlgTitle,
						 FCKPlugins.Items['Comment'].Path + 'Comment.html', 460, 200
	)
);

FCKCommands.RegisterCommand('Note',
	new FCKDialogCommand('Note', FCKLang.NoteDlgTitle,
						 FCKPlugins.Items['Comment'].Path + 'Note.html', 460, 200
	)
);

FCKCommands.GetCommand('Comment').GetState = function() {
	aTags = new Array('H2', 'H3', 'H4', 'PRE', 'TABLE')
	for (i = 0; i < aTags.length; i++) {
		if (FCKSelection.HasAncestorNode(aTags[i])) {
			return FCK_TRISTATE_DISABLED;
		}
	}
	
	var oElement = FCKSelection.GetSelectedElement();
	if (oElement && oElement.tagName == 'IMG' && oElement.alt == 'Comment') {
		return FCK_TRISTATE_ON;
	}
	else if (oElement) {
		return FCK_TRISTATE_DISABLED;
	}
	
	return FCK_TRISTATE_OFF;
}

FCKCommands.GetCommand('Note').GetState = function() {
	var oElement = FCKSelection.GetSelectedElement();
	if (oElement && oElement.tagName == 'IMG' && oElement.alt == 'Note') {
		return FCK_TRISTATE_ON;
	}
	else if (oElement) {
		return FCK_TRISTATE_DISABLED;
	}
	
	return FCK_TRISTATE_OFF;
}


// ツールバー・ボタン
FCKToolbarItems.RegisterItem('Comment', new FCKToolbarButton('Comment', FCKLang.CommentBtn));
FCKToolbarItems.RegisterItem('Note', new FCKToolbarButton('Note', FCKLang.NoteBtn));

FCK.Events.AttachEvent('OnSelectionChange', function () {
	FCKToolbarItems.GetItem('Comment').RefreshState();
	FCKToolbarItems.GetItem('Note').RefreshState();
});


//	コンテキストメニュー
FCK.ContextMenu.RegisterListener( {
	AddItems : function(menu, tag, tagName) {
		if (tagName == 'IMG' && tag.alt == 'Comment') {
			menu.AddSeparator();
			menu.AddItem('Comment', FCKLang.CommentDlgTitle,
								FCKToolbarItems.GetItem('Comment').IconPath);
		}
		else if (tagName == 'IMG' && tag.alt == 'Note') {
			menu.AddSeparator();
			menu.AddItem('Note', FCKLang.NoteDlgTitle,
								FCKToolbarItems.GetItem('Note').IconPath);
		}
	}
});


var Comment = new Object();

Comment.Add = function(type) {
	element = FCK.CreateElement('IMG');
	element.alt = type;
	element.src = FCKToolbarItems.GetItem(element.alt).IconPath;
	
	return element;
}

Comment.Redraw = function() {
	var aTags = FCK.EditorDocument.getElementsByTagName('IMG');
	for (i = 0; i < aTags.length; i++) {
		if (aTags[i].alt.Equals('Note', 'Comment')) {
			aTags[i].src = FCKToolbarItems.GetItem(aTags[i].alt).IconPath;
		}
	}
	if (FCKBrowserInfo.IsGecko) {
		Comment._SetupClickListener();
	}
}

FCK.Events.AttachEvent('OnAfterSetHTML', Comment.Redraw);

//	クリック イベント
Comment._SetupClickListener = function() {
	Comment._ClickListener = function(e) {
		if (e.target.tagName.Equals('IMG') && e.target.alt.Equals('Note', 'Comment')) {
			FCKSelection.SelectNode(e.target);
		}
	}

	FCK.EditorDocument.addEventListener('click', Comment._ClickListener, true);
}

//	ダブルクリック イベント
Comment.OnDoubleClick = function(element) {
	if (element.alt.Equals('Note', 'Comment')) {
		FCKCommands.GetCommand(element.alt).Execute();
	}
}

FCK.RegisterDoubleClickHandler(Comment.OnDoubleClick, 'IMG');
