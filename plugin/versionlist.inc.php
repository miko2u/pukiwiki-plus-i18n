<?php
/*
 * PukiWiki versionlistプラグイン
 *
 * CopyRight 2002 S.YOSHIMURA GPL2
 * http://masui.net/pukiwiki/ yosimura@excellence.ac.jp
 *
 * $Id: versionlist.inc.php,v 1.13.1 2004/08/01 01:22:37 miko Exp $
 */

function plugin_versionlist_action()
{
	global $_title_versionlist;

	return array(
		'msg' => $_title_versionlist,
		'body' => plugin_versionlist_convert()
	);
}

function plugin_versionlist_convert()
{
	/* 探索ディレクトリ設定 */
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
