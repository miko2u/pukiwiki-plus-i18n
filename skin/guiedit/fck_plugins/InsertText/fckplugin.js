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
FCKCommands.RegisterCommand('InsertText',
	new FCKDialogCommand('InsertText', FCKLang.InsertTextDlgTitle,
						 FCKPlugins.Items['InsertText'].Path + 'InsertText.html', 460, 280
	)
);

FCKCommands.GetCommand('InsertText').GetState = function() {
	
	return FCK_TRISTATE_OFF;
}


// ツールバー・ボタン
FCKToolbarItems.RegisterItem('InsertText', new FCKToolbarButton('InsertText', FCKLang.InsertTextBtn));

FCK.Events.AttachEvent('OnSelectionChange', function () { FCKToolbarItems.GetItem('InsertText').RefreshState() });
