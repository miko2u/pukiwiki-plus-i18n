<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: resource.php,v 0.1 2005/03/15 03:29:34 miko Exp $
//
// Resource of String
// Warning: This file is PukiWiki "core" resource strings.
//          Please Without customizing it. 

$_string = array(
	// Common country string(s)
	'week'      => array(_('Sun'),_('Mon'),_('Tue'),_('Wed'),_('Thu'),_('Fri'),_('Sat'));

	// Common core string(s)
	'realm'     => _('PukiWikiAuth'),
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

?>