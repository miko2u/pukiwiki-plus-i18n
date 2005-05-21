<?php
// $Id: includesubmenu.inc.php,v 1.5 2004/07/31 03:09:20 henoheno Exp $

function plugin_includesubmenu_convert()
{
  global $script,$vars;

  $ShowPageName = FALSE;

  if (func_num_args()) {
    $aryargs = func_get_args();
    if ($aryargs[0] == 'showpagename') {
      $ShowPageName = TRUE;
    }
  }

  $SubMenuPageName = '';

  $tmppage = strip_bracket($vars['page']);
  //下階層のSubMenuページ名
  $SubMenuPageName1 = $tmppage . '/SubMenu';

  //同階層のSubMenuページ名
  $LastSlash= strrpos($tmppage,'/');
  if ($LastSlash === FALSE) {
    $SubMenuPageName2 = 'SubMenu';
  } else {
    $SubMenuPageName2 = substr($tmppage,0,$LastSlash) . '/SubMenu';
  }
  //echo "$SubMenuPageName1 <br>";
  //echo "$SubMenuPageName2 <br>";
  //下階層にSubMenuがあるかチェック
  //あれば、それを使用
  if (is_page($SubMenuPageName1)) {
    //下階層にSubMenu有り
    $SubMenuPageName = $SubMenuPageName1;
  }
  else if (is_page($SubMenuPageName2)) {
    //同階層にSubMenu有り
    $SubMenuPageName = $SubMenuPageName2;
  }
  else {
    //SubMenu無し
    return "";
  }

  $body = convert_html(get_source($SubMenuPageName));

  if ($ShowPageName) {
    $r_page = rawurlencode($SubMenuPageName);
    $s_page = htmlspecialchars($SubMenuPageName);
    $link = "<a href=\"$script?cmd=edit&amp;page=$r_page\">$s_page</a>";
    $body = "<h1>$link</h1>\n$body";
  }
  return $body;
}
?>
