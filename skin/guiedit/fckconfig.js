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
//      PukiWiki Plus! : Copyright (C) 2009 Katsumi Saito
//
//
//	File:
//	  fckconfig.js
//	  FCKeditor のカスタム設定
//


// プラグイン
FCKConfig.Plugins.Add('InternalEx');
FCKConfig.Plugins.Add('FontFormatEx', 'en,ja');
FCKConfig.Plugins.Add('AlignEx');
FCKConfig.Plugins.Add('ListEx', 'en,ja');
FCKConfig.Plugins.Add('IndentEx');
FCKConfig.Plugins.Add('InsertText', 'en,ja');
FCKConfig.Plugins.Add('PukiWikiPlugin', 'en,ja');
FCKConfig.Plugins.Add('TableEx', 'en,ja');
FCKConfig.Plugins.Add('HRuleEx');
FCKConfig.Plugins.Add('SmileyEx');
FCKConfig.Plugins.Add('SpecialCharEx', 'en,ja');
FCKConfig.Plugins.Add('Comment', 'en,ja');

//	文書型宣言
FCKConfig.DocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

//	オブジェクトのサイズ変更を無効
FCKConfig.DisableObjectResizing = true ;

//	ソースのポップアップ
FCKConfig.SourcePopup = true;


//	ショートカットキー
FCKConfig.Keystrokes = [
	[ CTRL + 65 /*A*/, true ],
	[ CTRL + 67 /*C*/, true ],
	[ CTRL + 70 /*F*/, true ],
	[ CTRL + 83 /*S*/, true ],
	[ CTRL + 88 /*X*/, true ],
	[ CTRL + 86 /*V*/, 'Paste' ],
	[ CTRL + 90 /*Z*/, 'Undo' ],
	[ CTRL + 89 /*Y*/, 'Redo' ],
	[ CTRL + 76 /*L*/, 'Link' ],
	[ CTRL + 50 /*B*/, 'Bold' ],
	[ CTRL + 73 /*I*/, 'Italic' ],
	[ CTRL + 85 /*U*/, 'Underline' ],
	[ CTRL + ALT + 13 /*ENTER*/, 'FitWindow' ],
	[ CTRL + ALT + 83 /*S*/, 'Source' ]
] ;

//	ツールバー
FCKConfig.ToolbarSets["Normal"] = [
	['Source','-','Cut','Copy','Paste','PasteText','PasteWord','-','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Link','Unlink','Anchor'],
	['InsertText','Attachment','PukiWikiPlugin','Note','Comment'],
	['Table','Rule','Smiley','SpecialChar','-','PageBreak'],
	['FitWindow','ShowBlocks','-','About'],
	'/',
	['FontFormat','FontSize'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','DList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['TextColor','BGColor']
];

//	コンテキストメニュー
FCKConfig.ContextMenu = ['Generic','Link','Anchor'] ;

//	フォーマット
FCKConfig.FontFormats = 'p;div;pre;h2;h3;h4' ;

//	文字の装飾
FCKConfig.FontSizes = '8px;9px;10px;11px;12px;14px;16px;18px;20px;24px;28px;32px;40px;48px;60px;'
					+ 'xx-small;x-small;small;medium;large;x-large;xx-large';
FCKConfig.CoreStyles['Size'].Styles['line-height'] = '130%';
FCKConfig.CoreStyles['Bold'].Element = 'strong';
FCKConfig.CoreStyles['Italic'].Element = 'em';

//	リンク
FCKConfig.LinkDlgHideTarget = true ;
FCKConfig.LinkDlgHideAdvanced = true ;
FCKConfig.LinkBrowser = false ;
FCKConfig.LinkUpload = false ;

//	顔文字
FCKConfig.SmileyImages	= ['smile.png','bigsmile.png','huh.png','oh.png','wink.png','sad.png','heart.png','worried.png','tear.png','umm.png','star.gif'];
FCKConfig.SmileyColumns = 4 ;
FCKConfig.SmileyWindowWidth		= 300 ;
FCKConfig.SmileyWindowHeight	= 200 ;
