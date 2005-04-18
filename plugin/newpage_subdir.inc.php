<?php
// $Id: newpage_subdir.inc.php,v 1.2 2003/05/14 15:26:43 rokudo Exp $
// @based_on newpage.inc.php
// @based_on ls2.inc.php
// @thanks to panda (auther of newpage.inc.php/ls2.inc.php)


function build_directory_list($roots, $option) {

	global $WikiName,$BracketName,$script;
	
	$existingPages = get_existpages();
	foreach($roots as $root) {
		$matched = FALSE;
		foreach($existingPages as $page) {
			$page = strip_bracket($page);
//			if (preg_match("/^$root.*$/", $page)){
			if (strpos($page,$root) === 0){
				if($option['directory only'] && strrpos($page, "/") >= strlen($root) ) {
					$page = substr($page,0, strrpos($page, "/"));
					$list["directory"][] = $page;
				} else {
					$list["directory"][] = $page;
				}
				while( strrpos($page, "/") >= strlen($root) ) {
					$page = substr($page,0, strrpos($page, "/"));
					$list["directory"][] = $page;
				}
				$matched = TRUE;
			}
		}
		if(!$matched) {
			$list["directory"][] = $root;

			$warnings[] = 
				"<font color=\"red\">" . sprintf( _("#%s doesn't have the corresponding page. "),$root) . "</font>" .
				"(<a href=\"$script?" . rawurlencode($root) . "\">" . _("making") . "</a>)<br>\n";
		}
	}
	$list["directory"] = array_unique($list["directory"]);
	natcasesort($list["directory"]);

	if(!$option["quiet"]) {
		$list["warning"] = $warnings;
	}
	return $list;
}

function print_form_string( $list ) {
	global $script,$vars;
	
	$form_string = "<form action=\"$script\" method=\"post\">\n";
	$form_string.= "<div>\n";
	$form_string.= _('New page') . ": ";

	if($list["directory"]) {
		$form_string.= "<select name=\"directory\">\n";
		foreach( $list["directory"] as $dir ) {
			$form_string.= "<option>$dir/</option>\n";
		}
		$form_string.= "</select>\n";
	}
	
	$form_string.= "<input type=\"hidden\" name=\"plugin\" value=\"newpage_subdir\" />\n";
	$form_string.= "<input type=\"hidden\" name=\"refer\" value=\"$vars[page]\" />\n";
	$form_string.= "<input type=\"text\" name=\"page\" size=\"30\" value=\"\" />\n";
	$form_string.= "<input type=\"submit\" value=\"" . _('Edit') . "\" />\n";
	$form_string.= "</div>\n";
	$form_string.= "</form>\n";

	if($list["warning"]) {
		foreach( $list["warning"] as $warning ) {
			$form_string.= $warning;
		}
	}

	return $form_string;
}

function print_help_message() {
	return
		"#newpage_subdir([directory]... ,[option]... )<br />\n" .
		"<br />\n" .
		_("DESCRIPTION<br />\n") .
		_("	　The field that adds a new page below the directory specified for [directory] is made.<br />\n");
		_("	　The order of the parameter is arbitrary.<br />") .
		_("	　When an undefined option is specified, Help is displayed with the message.<br />") .
		"<br />\n" .
		_("OPTION<br />\n") .
		_("　-d Directory Only.	It limits it only to the one with the child page. (The directory specified specifying it is an exception. )<br />\n") .
		_("　-h Help.		This Description is displayed. <br />\n") .
		_("　-q Quiet.		Warning is not displayed.<br />\n") .
		"<br />\n" .
		_("EXAMPLE<br />\n") .
		"#newpage_subdir() -&gt; implies: #newpage_subdir(&lt;current dir&gt;)　<br />\n".
		"#newpage_subdir(foo/var)<br />\n" .
		"#newpage_subdir(foo/var, -n)<br />\n" .
		"#newpage_subdir(-d,-q, foo/var, foo/vaz)<br />\n" .
		"#newpage_subdir(-h)<br />\n" .
		"#newpage_subdir(-XYZ) -&gt; implies : #newpage_subdir(-h)<br />\n";
}

function plugin_newpage_subdir_convert()
{
	global $vars;
	$available_option="rdhq";

	// parsing all parameters
	foreach(func_get_args() as $arg) {
		$arg = trim($arg);
		// options
		if(preg_match("/^\-[a-z\-\s]+\$/",$arg)) {
			for($i=1;$i<strlen($arg);$i++){
				switch($arg{$i}) {
					case 'd' : 
						$option['directory only'] |= 1; 
						break;
					case 'q' : 
						$option['quiet'] |= 1; 
						break;
					case ' ' :
					case '-' :
						break;
					case 'h' :						
					default:
						$option['help'] |= 1; break;
						break;
				}
			}
		}
		// directory roots
		else {
			$roots[] = $arg;
		}
	}
	
	if($option['help']) {
		return print_help_message();
	}
	if(!$roots) {
		$roots[] = strip_bracket($vars["page"]);
	}

	return print_form_string(build_directory_list($roots, $option));
}

function plugin_newpage_subdir_action()
{
	global $script, $vars;

	if(!$vars["page"]) {
		if($vars["directory"]) {
			$directory = strip_bracket($vars["directory"]);
			$roots[] =  substr($directory, 0, strlen($directory)-1);
			// $msg_prefix = $directory."..に";
			$msg_prefix = _("To $directory.");
		}
		$action_messages["msg"] = $msg_prefix . _('New page');
		$action_messages["body"] = print_form_string(build_directory_list($roots));
		return $action_messages;
	}
	
	header("Location: $script?".rawurlencode($vars['directory'].$vars["page"]));
	die();
}
?>
