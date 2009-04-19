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


FCKCommands.GetCommand('Rule').Execute = function() {
	var oElement = FCK.CreateElement('HR');
	
	oElement.className = 'full_hr';
	FCKTools.AddEventListener(oElement, 'resizestart', HRuleEx.OnResizeStart);
}

FCKCommands.GetCommand('Rule').GetState = function() {
	var tags = new Array('H2', 'H3', 'H4', 'PRE', 'TABLE', 'DT');
	for (i = 0; i < tags.length; i++) {
		if (FCKSelection.HasAncestorNode(tags[i])) {
			return FCK_TRISTATE_DISABLED;
		}
	}
	
	var oElement = FCKSelection.GetSelectedElement();
	if (oElement && oElement.tagName.Equals('IMG', 'TABLE', 'DIV', 'SPAN')) {
		return FCK_TRISTATE_DISABLED;
	}
	
	return FCK.GetNamedCommandState('InsertHorizontalRule');
}


var HRuleEx = new Object();

HRuleEx._SetupResizeListener = function() {
	var aTags = FCK.EditorDocument.getElementsByTagName('HR');
	for (i = 0; i < aTags.length; i++) {
		FCKTools.AddEventListener(aTags[i], 'resizestart', HRuleEx.OnResizeStart);
	}
}

HRuleEx.OnResizeStart = function() {
	FCK.EditorWindow.event.returnValue = false;
	return false;
}

FCK.Events.AttachEvent('OnAfterSetHTML', HRuleEx._SetupResizeListener);
