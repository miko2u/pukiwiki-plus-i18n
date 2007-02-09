<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: versionlist.inc.php,v 1.16.4 2007/01/21 14:18:52 miko Exp $
// Copyright (C)
//	 2005-2007 PukiWiki Plus! Team
//	 2002-2006 PukiWiki Developers Team
//	 2002      S.YOSHIMURA GPL2 yosimura@excellence.ac.jp
// License: GPL v2
//
// Listing cvs revisions of files

function plugin_versionlist_action()
{
	// if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');
	if (auth::check_role('safemode')) die_message('PKWK_SAFE_MODE prohibits this');

	return array(
		'msg' => _('version list'),
		'body' => plugin_versionlist_convert()
	);
}

function plugin_versionlist_convert()
{
	// if (PKWK_SAFE_MODE) return ''; // Show nothi
	if (auth::check_role('safemode')) return ''; // Show nothi

	/* Set search directory */
	$SCRIPT_DIR = array('./');
	if (LIB_DIR   != './') array_push($SCRIPT_DIR, LIB_DIR);
	if (DATA_HOME != './' && DATA_HOME != LIB_DIR) array_push($SCRIPT_DIR, DATA_HOME);
	array_push($SCRIPT_DIR, PLUGIN_DIR, SKIN_DIR);

	$comments = array();

	foreach ($SCRIPT_DIR as $sdir)
	{
		if (!$dir = @dir($sdir))
		{
			// die_message('directory '.$sdir.' is not found or not readable.');
			continue;
		}
		while($file = $dir->read())
		{
			if (!preg_match("/\.(php|lng|css|js)$/i",$file))
			{
				continue;
			}
			$data = join('',file($sdir.$file));
			$comment = array('file'=>htmlspecialchars($sdir.$file),'rev'=>'','date'=>'');
			if (preg_match('/\$'.'Id: (.+),v (\d+\.\d+) (\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) (.+) Exp/',$data,$matches))
			{
				if ($matches[4] != 'arino' && $matches[4] != 'henoheno' && $matches[4] != 'panda') {
					$comment['file'] = '<span style="color:blue">'.htmlspecialchars($comment['file']).'</span>';
					$comment['rev'] = '<span style="color:blue">'.htmlspecialchars($matches[2]).'</span>';
					$comment['date'] = '<span style="color:blue">'.htmlspecialchars($matches[3]).'</span>';
					$comment['author'] = '<span style="color:blue">'.htmlspecialchars($matches[4]).'</span>';
				} else {
//					$comment['file'] = htmlspecialchars($sdir.$matches[1]);
					$comment['rev'] = htmlspecialchars($matches[2]);
					$comment['date'] = htmlspecialchars($matches[3]);
					$comment['author'] = htmlspecialchars($matches[4]);
				}
			}
			else if (preg_match('/\$'.'Id: (.+),v (\d+\.\d+.\d) (\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) (.+) Exp/',$data,$matches))
			{
				$comment['file'] = '<span style="color:red">'.htmlspecialchars($comment['file']).'</span>';
				$comment['rev'] = '<span style="color:red">'.htmlspecialchars($matches[2]).'</span>';
				$comment['date'] = '<span style="color:red">'.htmlspecialchars($matches[3]).'</span>';
				$comment['author'] = '<span style="color:red">'.htmlspecialchars($matches[4]).'</span>';
			}
			$comments[$sdir.$file] = $comment;
		}
		$dir->close();
	}
	if (count($comments) == 0)
	{
		return '';
	}
	ksort($comments);
	$retval = '';
	foreach ($comments as $comment)
	{
		$retval .= <<<EOD

  <tr>
   <td class="style_td">{$comment['file']}</td>
   <td class="style_td">{$comment['rev']}</td>
   <td class="style_td">{$comment['date']}</td>
   <td class="style_td">{$comment['author']}</td>
  </tr>
EOD;
	}
	$retval = <<<EOD
<table align="center" border="1" cellspacing="0">
 <thead>
  <tr>
   <th class="style_th">filename</th>
   <th class="style_th">revision</th>
   <th class="style_th">date</th>
   <th class="style_th">author</th>
  </tr>
 </thead>
 <tbody>
$retval
 </tbody>
</table>
EOD;
	return $retval;
}
?>
