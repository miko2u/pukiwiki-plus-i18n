<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: addline.inc.php,v 0.13.1 2005/03/09 23:15:10 miko Exp $
// Original is sha(0.13)
/* 
*�ץ饰���� addline
 ���ξ�ˡ������ʸ������ɲä��롣

*Usage
 #addline(����̾[,above|below|up|down|number|nonumber][,btn:<�ܥ���ƥ�����>][,ltext:<���ƥ�����>][,rtext:<���ƥ�����>])
 &addline(����̾[,before|after|above|below|up|down|number|nonumber]){<�ܥ���ƥ�����>};

*�ѥ�᡼��
  ����:   ��:config/plugin/addline/����פ�����̾�򵭺�
  above|below|up|down: �夫�����ɲä��롣
  before|after: �ܥ���ƥ����Ȥ���������ɲä��롣
  ltext: �ܥ���κ�¦�Υƥ�����
  rtext: �ܥ���α�¦�Υƥ�����

*����ڡ���������
 �ɲä���ʸ����򵭺ܤ��롣ʣ���ԤǤ�褤��
 �㡧
    |&attachref;|&attachref;|&attachref;|
    |������|������|������|
*/

/////////////////////////////////////////////////
// �����Ȥ������������ 1:����� 0:��θ�
define('ADDLINE_INS','1');

function plugin_addline_init()
{
	$messages = array(
		'_addline_messages' => array(
			'btn_submit' => _('add'),
			'title_collided' => _('On updating  $1, a collision has occurred.'),
			'msg_collided' => _('It seems that someone has already updated the page you were editing.<br />
 The string was added, alhough it may be inserted in the wrong position.<br />')
		),
	);
	set_plugin_messages($messages);
}
function plugin_addline_convert()
{
	global $script,$vars,$digest;
	global $_addline_messages;
	static $numbers = array();
	static $no_flag = 0;

	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$addline_no = $numbers[$vars['page']]++;
	
	$above = ADDLINE_INS;
	$configname = 'default';
        $btn_text = $_addline_messages['btn_submit'];
        $right_text = $left_text = '';
    	if ( func_num_args() ){
            foreach ( func_get_args() as $opt ){
        	if ( $opt === 'above' || $opt === 'up' ){
                    $above = 1;
                }
                else if (preg_match("/btn:(.+)/i",$opt,$args)){
                    $btn_text = htmlspecialchars($args[1]);
                }
                else if (preg_match("/rtext:(.+)/i",$opt,$args)){
                    $right_text = htmlspecialchars($args[1]);
                }
                else if (preg_match("/ltext:(.+)/i",$opt,$args)){
                    $left_text = htmlspecialchars($args[1]);
                }
                else if ( $opt === 'below' || $opt === 'down' ){
                    $above = 0;
                }
       	        else if ( $opt === 'number' ){
		    $no_flag = 1;
	    	}
       	        else if ( $opt === 'nonumber' ){
		    $no_flag = 0;
	    	}
                else {
                    $configname = $opt;
                }
            }
            if ( $no_flag == 1 ) $btn_text .= "[$addline_no]";
        }
    
        $f_page   = htmlspecialchars($vars['page']);
		$f_config = htmlspecialchars($configname);
    
        $string = <<<EOD
 <form action="$script" method="post">
  <div style="margin:0px auto 0px auto;text-align:center;">
   <input type="hidden" name="addline_no" value="$addline_no" />
   <input type="hidden" name="refer" value="$f_page" />
   <input type="hidden" name="plugin" value="addline" />
   <input type="hidden" name="above" value="$above" />
   <input type="hidden" name="digest" value="$digest" />
   <input type="hidden" name="configname"  value="$f_config" />
   $left_text
   <input type="submit" name="addline" value="$btn_text" />
   $right_text
  </div>
 </form>
EOD;
	return $string;
}
function plugin_addline_inline()
{
	global $script,$vars,$digest;
	global $_addline_messages;
	static $numbers = array();
	static $no_flag = 0;
	
	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$addline_no = $numbers[$vars['page']]++;
	
	$above = ADDLINE_INS;
	$configname = 'default';
        $btn_text = $_addline_messages['btn_submit'];
    	if ( func_num_args() ){
            $args =func_get_args();
            $opt = array_pop($args);
            $btn_text = $opt ? htmlspecialchars($opt) : $btn_text;
            foreach ( $args as $opt ){
        	if ( $opt === 'before' ){
                    $above = 3;
                }
                else if ( $opt === 'after' ){
                    $above = 2;
                }
        	else if ( $opt === 'above' || $opt === 'up' ){
                    $above = 1;
                }
                else if ( $opt === 'below' || $opt === 'down' ){
                    $above = 0;
                }
       	        else if ( $opt === 'number' ){
		    $no_flag = 1;
	    	}
       	        else if ( $opt === 'nonumber' ){
		    $no_flag = 0;
	    	}
                else {
                    $configname = $opt;
                }
            }
            if ( $no_flag == 1 ) $btn_text .= "[$addline_no]";
        }
    
        $f_page   = rawurlencode($vars['page']);
		$f_config = rawurlencode($configname);
    
        $string = <<<EOD
<a href="$script?plugin=addline&amp;addline_inno=$addline_no&amp;above=$above&amp;refer=$f_page&amp;configname=$f_config&amp;digest=$digest">$btn_text</a>
EOD;
	return $string;
}
function plugin_addline_action()
{
	global $script,$vars,$post,$now;
	global $_title_updated;
	global $_addline_messages;
	
	$refer         = $vars['refer'];
	$postdata_old  = get_source($refer);
	$configname = $vars['configname'];
	$above      = $vars['above'];

	$block_plugin = 1;
	if ( array_key_exists('addline_inno', $vars) ) {
	    $addline_no = $vars['addline_inno'];
	    $block_plugin = 0;
	}
	else if ( array_key_exists('addline_no', $vars) ) {
	    $addline_no = $vars['addline_no'];
	}
	
	
	$config = new Config('plugin/addline/'.$configname);
	if (!$config->read())
	{
		return array( 'msg' => 'addline error', 'body' => "<p>config file '".htmlspecialchars($configname)."' is not exist.");
	}
	$config->config_name = $configname;
	$addline = join('', addline_get_source($config->page));
	$addline = rtrim($addline);
        if ( $block_plugin ){
	    $postdata = addline_block($addline,$postdata_old,$addline_no,$above);
	}
	else {
	    $postdata = addline_inline($addline,$postdata_old,$addline_no,$above);
	}

	$title = $_title_updated;
	$body = '';
	if (md5(@join('',$postdata_old)) != $vars['digest'])
	{
		$title = $_addline_messages['title_collided'];
		$body  = $_addline_messages['msg_collided'] . make_pagelink($refer);
	}
	
	
//	$body = $postdata; // debug
//	foreach ( $vars as $k=>$v ){$body .= "[$k:$v]&br;";}
	page_write($refer,$postdata);
	
	$retvars['msg'] = $title;
	$retvars['body'] = $body;
//	$post['page'] = $get['page'] = $vars['page'] = $refer;
	$post['refer'] = $get['refer'] = $vars['refer'] = $refer;
	return $retvars;
}
function addline_block($addline,$postdata_old,$addline_no,$above)
{
    $postdata = '';
    $addline_ct = 0;
    foreach ($postdata_old as $line)
    {
        if (!$above) 	$postdata .= $line;
        if (preg_match('/^#addline/',$line) and $addline_ct++ == $addline_no)
        {
            $postdata = rtrim($postdata)."\n$addline\n";
	    if ($above)  $postdata .= "\n";
	}
    	if ($above) $postdata .= $line;
    }
    return $postdata;
}
function addline_inline($addline,$postdata_old,$addline_no,$above)
{
    $postdata = '';
    $addline_ct = 0;
    $skipflag = 0;
    foreach ($postdata_old as $line)
    {
		if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
		    $postdata .= $line;
	    	continue;
		}
		$ct = preg_match_all('/&addline\([^();]*\)({[^{};]*})?;/',$line, $out);
		if ( $ct ){
	    	for($i=0; $i < $ct; $i++){
				if ($addline_ct++ == $addline_no ){
			    	if ( $above == 3 ){ // before
						$line = preg_replace('/(&addline\([^();]*\)({[^{};]*})?;)/', $addline.'$1',$line,1);
		    		}
		    		else if ( $above == 2 ){ //after
						$line = preg_replace('/(&addline\([^();]*\)({[^{};]*})?;)/','$1'.$addline,$line,1);
		    		}
		    		else if ( $above == 1 ){ // above
						$line = $addline . "\n" . $line;
		    		}
		    		else if ( $above == 0 ){ //below
						$line .= $addline . "\n";
		    		}
		    		$skipflag = 1;
		   			break;
				}
				else if ( $above == 2 || $above == 3 ){
		    		$line = preg_replace('/&addline(\([^();]*\)({[^{};]*})?);/','&___addline$1___;',$line,1);
				}
	    	}
	    	if ( $above == 2 || $above == 3 ){
				$line = preg_replace('/&___addline(\([^();]*\)({[^{};]*})?)___;/','&addline$1;',$line);
	    	}
		}
		$postdata .= $line;
    }
    return $postdata;
}
function addline_get_source($page) // tracker.inc.php��tracker_list����
{
	$source = get_source($page);
	// ���Ф��θ�ͭID������
	$source = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',$source);
	// #freeze����
	return preg_replace('/^#freeze\s*$/m','',$source);
}
?>
