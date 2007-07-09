<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: index.php,v 1.9.3 2007/07/09 23:32:00 upk Exp $
// Copyright (C)
//   2005-2007 PukiWiki Plus! Team
//   2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version

// Error reporting
//error_reporting(0); // Nothing
error_reporting(E_ERROR | E_PARSE); // Avoid E_WARNING, E_NOTICE, etc
//error_reporting(E_ALL); // Debug purpose

// Special
//define('PKWK_READONLY',  1); // 0,1,2,3,4
//define('PKWK_SAFE_MODE', 1); // 0,1,2,3,4
//define('PKWK_OPTIMISE',  1); // Obsolete - Plus! not used
//define('TDIARY_THEME',   'digital_gadgets');

// Directory definition
// (Ended with a slash like '../path/to/pkwk/', or '')
// define('SITE_HOME',     '../wiki-common/');
define('SITE_HOME',	'');
// define('DATA_HOME',     '../../wiki-data/contents/');
define('DATA_HOME',	'');

define('ROOT_URI', '');
define('WWW_HOME', '');

define('LIB_DIR',	SITE_HOME . 'lib/');

require(LIB_DIR . 'pukiwiki.php');
?>
