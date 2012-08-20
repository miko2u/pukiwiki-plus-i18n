<?php
defined('PLUGIN_TWEET_VIA')||define('PLUGIN_TWEET_VIA', 'miko2u');

function plugin_tweet_convert()
{
	return '<div class="tweet">'.plugin_tweet_inline().'</div>';
}

// Show a form
function plugin_tweet_inline()
{
	global $vars;
	static $tracker_count = 0;

	if ($tracker_count == 0) {
		global $head_tags;
		$head_tags[] = '<script type="text/javascript" src="http://platform.twitter.com/widgets.js" charset="utf-8"></script>';
		$tracker_count++;
	}

	$page = $vars['page'];
	$title = plugin_tweet_get_page_title($page);
	$uri = get_page_uri($page);
	$via = PLUGIN_TWEET_VIA;

	return <<<EOD
<a href="http://twitter.com/share" class="twitter-share-button"
   data-url="{$uri}"
   data-text="{$title}"
   data-via="{$via}"
   data-count="horizontal"
   data-lang="ja">Tweet</a>
EOD;
}

function plugin_tweet_get_page_title($page)
{
    if ( ! is_page($page) ) return FALSE;
    $src = get_source($page);
    $ct = 0;
    foreach ($src as $line)
	{
        if ($ct++ > 99) break;
        if (preg_match('/^\*{1,3}(.*)\[#[A-Za-z][\w\-]+\].*$/', $line, $match))
		{
            return trim($match[1]);
        }
        else if (preg_match('/^\*{1,3}(.*)$/', $line, $match))
		{
            return trim($match[1]);
        }
    }
    return FALSE;
}
