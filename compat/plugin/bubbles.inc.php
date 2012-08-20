<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: bubbles.inc.php,v 1.2.1 2007/02/13 14:25:25 miko Exp $
// Copyright (C) 2007 PukiWiki Plus! Team
// License: GPLv2
//
// Show Bubbles

function plugin_bubbles_convert()
{
  switch(func_num_args()) {
  case 2:
    list($title, $body) = func_get_args();
    $title = htmlspecialchars($title);
    break;
  case 1:
    list($body) = func_get_args();
    break;
  default:
    return FALSE;
  }


  $lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $body);
  $lines = preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines));

  static $bubbles = FALSE;

  if ($bubbles === FALSE) {
    global $head_tags;
    $head_tags[] = ' <link rel="stylesheet" href="' . SKIN_URI . 'bubbles.css" type="text/css" media="screen" charset="utf-8" />';
    $head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'bubbles.js"></script>';
    $bubbles = TRUE;
  }

  return <<<EOD
  <div class="bubble">
    <div class="rounded">
      <blockquote>
        <p>{$lines}</p>
      </blockquote>
    </div>
    <cite class="rounded"><strong>{$title}</strong></cite>
  </div>
EOD;
}
?>
