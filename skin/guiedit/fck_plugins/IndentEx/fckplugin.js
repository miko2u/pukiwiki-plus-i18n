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


FCKCommands.GetCommand('Indent').GetState = function() {
	var tags = new Array('H2', 'H3', 'H4', 'PRE', 'TABLE');
	for (i = 0; i < tags.length; i++) {
		if (FCKSelection.HasAncestorNode(tags[i])) {
			return FCK_TRISTATE_DISABLED;
		}
	}
	
	return FCK.GetNamedCommandState(this.Name);
}

FCKCommands.GetCommand('Outdent').GetState = function() {
	if (FCKCommands.GetCommand('Indent').GetState() == FCK_TRISTATE_DISABLED) {
		return FCK_TRISTATE_DISABLED;
	}
	
	var tags = new Array('BLOCKQUOTE', 'OL', 'UL', 'DL');
	for (i = 0; i < tags.length; i++) {
		if (FCKSelection.HasAncestorNode(tags[i])) {
			return FCK_TRISTATE_OFF;
		}
	}
	
	return FCK_TRISTATE_DISABLED;
}

FCKCommands.GetCommand('Indent').Execute = function() {
	FCK.ExecuteNamedCommand(this.Name);
}

FCKCommands.GetCommand('Outdent').Execute = function() {
	FCK.ExecuteNamedCommand(this.Name);
}
