<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: resource.php,v 0.4 2007/05/03 21:21:00 upk Exp $
//
// Resource of String
// Warning: This file is PukiWiki "core" resource strings.
//          Please Without customizing it. 

$help_page = 'Help';
$rule_page = 'FormattingRules';

$weeklabels = array(
	_('Sun'),_('Mon'),_('Tue'),_('Wed'),_('Thu'),_('Fri'),_('Sat'),
);

$_string = array(
	// Common core string(s)
	'freeze'    => _('Freeze'),
	'unfreeze'  => _('Unfreeze'),
	'symbol'    => _('Symbols'),
	'other'     => _('Others'),

	// Common core message(s)
	'andresult' => _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain all the terms $1 were found.'),
	'orresult'  => _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain at least one of the terms $1 were found.'),
	'notfoundresult' => _('No page which contains $1 has been found.'),
	'word' => _('These search terms have been highlighted:'),
	'help' => _('View Text Formatting Rules'),

	// Common core error message(s)
	'invalidpass' => _('Invalid password.'),
	'invalidiwn'  => _('$1 is not a valid $2.'),
	'collided_comment' => _('It seems that someone has already updated the page you were editing.<br />The string was added, alhough it may be inserted in the wrong position.<br />'),
);

$_button = array(
	// Native button
	'preview'   => _('Preview'),
	'repreview' => _('Preview again'),
	'update'    => _('Update'),
	'cancel'    => _('Cancel'),
	'add'       => _('Add'),
	'search'    => _('Search'),
	'load'      => _('Load'),
	'edit'      => _('Edit'),
	'delete'    => _('Delete'),

	// CheckBox labels
	'notchangetimestamp' => _('Do not change timestamp'),
	'addtop'             => _('Add to top of page'),
	'template'           => _('Use page as template'),
	'and' => _('AND'),
	'or'  => _('OR'),
);

$_title = array(
	// Message title
	'cannotedit' => _('$1 is not editable'),
	'cannotread' => _('$1 is not readable'),
	'collided' => _('On updating $1, a collision has occurred.'),
	'updated' => _('$1 was updated'),
);


// Encoding hint
$_LANG['encode_hint'] = _('encode_hint');

$_LANG['skin'] = array(
	'add'       => _('Add'),
	'backup'    => _('Backup'),
	'copy'      => _('Copy'),
	'diff'      => _('Diff'),
	'edit'      => _('Edit'),
	'filelist'  => _('List of page files'),
	'freeze'    => _('Freeze'),
	'help'      => _('Help'),
	'list'      => _('List of pages'),
	'new'       => _('New'),
	'newsub'    => _('Lower page making'),
	'rdf'       => _('RDF of recent changes'),
	'recent'    => _('Recent changes'),
	'refer'     => _('Referer'),
	'reload'    => _('Reload'),
	'rename'    => _('Rename'),
	'print'     => _('Image of print'),
	'rss'       => _('RSS of recent changes'),
	'rss10'     => _('RSS of recent changes'),
	'rss20'     => _('RSS of recent changes'),
	'rssplus'   => _('RSS of recent changes'),
	'mixirss'   => _('RSS of recent changes'),
	'search'    => _('Search'),
	'source'    => _('Source'),
	'template'  => _('Template'),
	'top'       => _('Front page'),
	'trackback' => _('Trackback'),
	'unfreeze'  => _('Unfreeze'),
	'upload'    => _('Upload'),
	'skeylist'  => _('Search Key List'),
	'linklist'  => _('Link List'),
	'log_browse'=> _('Browse Log'),
	'log_update'=> _('Update Log'),
	'log_down'  => _('Download Log'),
	'log'       => _('Log'),
	'logo'      => _('Logo'),
);

?>
