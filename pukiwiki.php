<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: pukiwiki.php,v 1.41.1 2005/04/29 11:26:28 miko Exp $
// Copyright (C)
//   2005      PukiWiki Plus! Team
//   2004-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version

/////////////////////////////////////////////////
// Error reporting

// error_reporting(0): // Nothing
error_reporting(E_ERROR | E_PARSE); // Avoid E_WARNING, E_NOTICE, etc
// error_reporting(E_ALL);

/////////////////////////////////////////////////
// Directory definition
// (Ended with a slash like '../path/to/pkwk/', or '')
//
// define('PLUS_HOME',     '../wiki-common/');
define('PLUS_HOME',     '');
// define('DATA_HOME',     '../../wiki-data/contents/');
define('DATA_HOME',     '');
define('LIB_DIR',       PLUS_HOME . 'lib/');

/////////////////////////////////////////////////
require(LIB_DIR . 'pukiwiki.php');
?>
