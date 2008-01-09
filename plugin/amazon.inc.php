<?php
/////////////////////////////////////////////////
//
// Amazon プラグイン Ver.2.3
//
// $Id: amazon.inc.php,v 2.3.2 2005/12/20 00:00:00 raku/miko $
//
// Copyright: 2003-2004 By 閑舎 <raku@rakunet.org>
//
// Thanks: To reimy, t, Ynak, WikiRoom, upk, 水橋希 and PukiWiki Developers Team.
//
// ファイルサイズを考え、詳細は amazon.inc.txt に移動
//
///////////////////////////////////////////////// 要変更箇所
// テスト使用後はアマゾンアソシエイトとなり、rakujuku09-22 を自サイトのアソシエイト ID に変更のこと
if (!defined('AMAZON_AID')) {
	define('AMAZON_AID','mikoscafeterr-22');
}
// 買物かごを使うには、さらに Web サービス契約を結び、自サイトの Developer's Token に変更すること
if (!defined('AMAZON_DT')) {
	define('AMAZON_DT','D1C2RO8WE4EZPC');
}
///////////////////////////////////////////////// 変更してもよい箇所
// expire 写影/タイトル/価格キャッシュを何時間で削除するか。基本的にキャッシュは 24 時間
define('AMAZON_EXPIRE_img', 365*24); // アソシエイトの固定リンク作成の指示により、これで問題なし
define('AMAZON_EXPIRE_heavy', 365*24); // 基本的に固定の情報。画像に準じる
define('AMAZON_EXPIRE_lite', 24); // 価格情報等。買物かごがあれば 1 に(Web サービス規約)
define('AMAZON_ALLOW_LNK', true); // true にすると、写影以外にリンク作成(AZ...link)、イメージリンク(AZ...gif)等が可能
define('AMAZON_ALLOW_CONT', true); // true にすると、紹介本文取り込みが可能
define('USE_CARGO', true); // true にすると買物かごを使用可能
define('AMAZON_SIM', ''); // '' から '/ref=nosim' に変えると直接商品紹介ページにリンク('' がアマゾン推奨)。 
// 写影なしの画像/買物かごのアイコン
define('AMAZON_NO_IMAGE', IMAGE_URI . 'noimage.gif');
define('AMAZON_CARGO', IMAGE_URI . 'remote-buy-jp.gif');
///////////////////////////////////////////////// 変更してはならない箇所
define('AMAZON_SHOP','http://www.amazon.co.jp/exec/obidos/ASIN/');
define('AMAZON_LIB0','http://www.amazon.co.jp/exec/obidos/redirect-home?tag=' . AMAZON_AID);
define('AMAZON_LIB','http://www.amazon.co.jp/exec/obidos/redirect?tag=' . AMAZON_AID . '&path=tg/browse/-/');
define('AMAZON_IMAGE','http://images-jp.amazon.com/images/P/');
// amazon 商品情報問合せ。2004/3/21 以降変更
define('AMAZON_XML','http://xml.amazon.co.jp/onca/xml3?t=webservices-20&dev-t=' . AMAZON_DT . '&page=1&f=xml&locale=jp&');

function plugin_amazon_init() {
  global $amazon_body, $stat, $keys, $genre;

  $stat = 0;
  $keys = array(); 
  $genre = array("books", "videogames", "dvd", "music");

  $amazon_body = sprintf(
    "-" . _("Author") . ": [[" . _("THIS EDIT") . "]]\n" .
    "-" . _("Critic") . ": " . _("MY_NAME") . "\n" .
    "-" . _("Date") . ": &date;\n" .
    "**" . _("RECOMMENDATION") . "\n" .
    "[[" . _("THIS EDIT") . "]]\n" .
    "\n" .
    "#amazon(,clear)\n" .
    "**" . _("Impression") . "\n" .
    "[[" . _("THIS EDIT") . "]]\n" .
    "\n" .
    _("// First of all, please delete the full text when you stop this review, and push the [update] button on the page.\n") .
    _("// (It has already been registered in PukiWiki.)\n") .
    _("// Please delete the [[THIS EDIT]] part on including parentheses, and rewrite it if you continue.\n") .
    _("// Please change the MY_NAME part to my name.\n") .
    _("// **RECOMMENDATION Above, please do not add a new line. Because it uses it for the contents making.\n") .
    _("// Please cut all the comment lines that start in // finally.\n") .
    _("// There is a possibility that contents cannot be normally made.\n") .
    "#comment\n"
  );

  $msg = array(
    '_amazon_msg' => array(
      'msg_ReviewEdit'     => _("Review edit"),
      'msg_Code'           => _("(ISBN(10) or ASIN(12))"),
      'msg_Cargo'          => _("To the shopping basket"),
      'msg_Price'          => _("Price"),
      'msg_FixedPrice'     => _("Fixed price"),
      'msg_Tax'            => _("(Including tax)"),
      'msg_BookReviewEdit' => _("Book review edit"),
      'msg_amazon'         => _("Amazon.co.jp associate"),
      'msg_Relation'       => _("Relation"),
    )
  );
  set_plugin_messages($msg);
}

function plugin_amazon_convert() {
  global $script, $vars;
  global $_amazon_msg;

  $aryargs = func_get_args();
  if (func_num_args() == 0) { // レビュー作成
    $s_page = htmlspecialchars($vars['page']);
    if ($s_page == '') $s_page = $vars['refer'];
    $ret = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="amazon" />
  <input type="hidden" name="refer" value="$s_page" />
  ASIN:
  <input type="text" name="asin" size="30" value="" />
  <input type="submit" value="{$_amazon_msg['msg_ReviewEdit']}" /> {$_amazon_msg['msg_Code']}
 </div>
</form>
EOD;
    return $ret;
  } elseif (func_num_args() > 4) return false;

  $align = strtolower(trim($aryargs[1]));
  if ($align == 'clearl') return '<div style="clear:left;display:block;"></div>'; // 改行挿入
  if ($align == 'clearr') return '<div style="clear:right;display:block;"></div>'; // 改行挿入
  if ($align == 'clear') return '<div style="clear:both;"></div>'; // 改行挿入
  if ($align == '') $align = 'right';
  if (preg_match("/^(right|left|center)$/", $align) == false) return false;

  $item = htmlspecialchars(trim($aryargs[2])); // for XSS

  $check = new amazon_check_asin(htmlspecialchars($aryargs[0])); // for XSS
  $css_avail = ($align == 'right')? ';margin-left:150px': '';
  if ($check->is_asin) {
    if ($item == 'image' or $item == 'simage') {
      $m_s = ($item == 'image')? 'm': 's';
      $info = new amazon_getinfo($check->asin, 'heavy');
      $div = "<div class='amazon_img' style='float:$align'>";
      $div .= amazon_get_imagelink($check->asin, $check->ext, $aryargs[3], $info->items['title'], $m_s) . '</div>';
    } elseif (preg_match("/^((no)?content[csS]?|subscriptc?)$/", $item)) {
      $iscargo = false;
      if (preg_match("/^((no)?contentc|subscriptc)$/", $item)) {
	if (USE_CARGO) $iscargo = true;
	$item = substr($item, 0, strlen($item) - 1);
      }
      $info = new amazon_getinfo($check->asin, 'both');
      if (preg_match("/^((no)?content[sS]?)$/", $item)) {
        $m_s = (preg_match("/^(no)?content[sS]$/", $item))? 's': 'm';
	$css_m_s = ($m_s == 's')? '_s': '';
	$div = "<div class='amazon_imgetc' style='float:$align'>";
	$div .= amazon_get_imagelink($check->asin, $check->ext, $aryargs[3], $info->items['title'], $m_s) . "</div>";
      }
      if ($iscargo) {
	$div .= "<form method='post' action='http://www.amazon.co.jp/exec/obidos/dt/assoc/handle-buy-box=$check->asin'><div id='asin.$check->asin'>";
	$div .= "<input type='hidden' name='asin.$check->asin' value='1' />";
	$div .= "<input type='hidden' name='tag-value' value='" . AMAZON_AID . "' />";
	$div .= "<input type='hidden' name='tag_value' value='" . AMAZON_AID . "' />";
	$div .= "<input type='hidden' name='dev-tag-value' value='" . AMAZON_DT . "' />";
	$div .= "<div class='amazon_sub" . $css_m_s . "' style='text-align:$align'>";
	$div .= "<input type='image' src='" . AMAZON_CARGO . "' name='submit' value='{$_amazon_msg['msg_Cargo']}' /><br />";
      } else $div .= "<div class='amazon_sub" . $css_m_s . "' style='text-align:$align'>";
      if (preg_match("/^((no)?contentS)$/", $item))
        $div .= '<a href="' . AMAZON_SHOP . "$check->asin/" . AMAZON_AID . AMAZON_SIM . '">' . $info->items['title'] . "</a><br />";
      $div .= $info->items['author'] . "<br />";
      $div .= $info->items['manufact'] . " (". $info->items['media'] . ")<br />";
      $div .= "<b>{$_amazon_msg['msg_FixedPrice']}:</b> <del>" . $info->items['pricel'] . "</del>, <b>{$_amazon_msg['msg_Price']}:</b> " . $info->items['price'] . "{$_amazon_msg['msg_Tax']}</div>";
      $div .= "<div class='amazon_avail" . $css_m_s . "' style='text-align:$align" . $css_avail . "'>" . $info->items['avail'] . "</div>";
      if ($iscargo) $div .= "</div></form>";
      if ($item == 'content') $div .= "<br />" . $info->items['content'] . '<div style="display:block;"></div>';
    } elseif ($item == 'delimage') {
      $del_it = unlink(CACHE_DIR . "ASIN" . $check->asin . ".jpg");
      if (unlink(CACHE_DIR . "ASIN" . $check->asin . ".gif") and $del_it == false) $del_it = true;
      if ($del_it) return "Image of $check->asin deleted...";
      else return "Image of $check->asin NOT DELETED...";
    } elseif ($item == 'deltitle') {
      $del_it = unlink(CACHE_DIR . "ASIN" . $check->asin . ".heavy");
      if (unlink(CACHE_DIR . "ASIN" . $check->asin . ".lite") and $del_it == false) $del_it = true; 
      if ($del_it) return "Title of $check->asin deleted...";
      else return "Title of $check->asin NOT DELETED...";
    } elseif ($item == 'delete') {
      $del_it = unlink(CACHE_DIR . "ASIN" . $check->asin . ".heavy");
      if (unlink(CACHE_DIR . "ASIN" . $check->asin . ".lite") and $del_it == false) $del_it = true; 
      if (unlink(CACHE_DIR . "ASIN" . $check->asin . ".jpg") and $del_it == false) $del_it = true;
      if (unlink(CACHE_DIR . "ASIN" . $check->asin . ".gif") and $del_it == false) $del_it = true;
      if ($del_it) return "Title and Image of $check->asin deleted...";
      else return "Title and Image of $check->asin NOT DELETED...";
    } else {
      if ($item == '') {
	$info = new amazon_getinfo($check->asin, 'heavy');
	$item = $info->items['title'];
      }
      $div = "<div class='amazon_img' style='float:$align'>";
      $div .= "<table class='amazon_tbl'><tr><td class='amazon_td'>";
      $div .= amazon_get_imagelink($check->asin, $check->ext, $aryargs[3], $info->items['title'], 'm') . '</td></tr>';
      $div .= "<tr><td class='amazon_td'><a href='" . AMAZON_SHOP . $check->asin;
      $div .= "/" . AMAZON_AID . AMAZON_SIM . "'>" . $item . "</a></td></tr></table></div>";
    }
    return $div;
  } else return false;
}

function plugin_amazon_action() {
  global $vars, $script;
  global $amazon_body;
  global $_amazon_msg;

  $check = new amazon_check_asin(htmlspecialchars(rawurlencode(strip_bracket($vars['asin']))));
  if (! $check->is_asin) {
    $retvars['msg'] = $_amazon_msg['msg_BookReviewEdit'];
    $retvars['refer'] = $vars['refer'];
    $s_page = $vars['refer'];
    $r_page = $s_page . '/' . $check->asin;
    $retvars['body'] = plugin_amazon_convert();
    return $retvars;
  }

  $s_page = $vars['refer'];
  $r_page = $s_page . '/' . $check->asin;
  // $r_page_url = rawurlencode($r_page);

  if (!check_readable($r_page, false, false)) header('Location: ' . get_page_location_uri($r_page));
  elseif (check_editable($r_page, false, false)) {
    $info = new amazon_getinfo($check->asin, 'heavy');
    $title = $info->items['title'];
    if ($title == '' or preg_match('/^\//', $s_page)) {
      header('Location: '.get_script_absuri().'?'.encode($s_page));
    }
    $body = "#amazon($check->asin,,image)\n*$title\n" . $amazon_body;
    amazon_review_save($r_page, $body);
    header('Location: '.get_location_uri('edit',$r_page));
  } else return false;
  die();
}

function plugin_amazon_inline() {
  $aryargs = func_get_args();
  if (func_num_args() < 2 or func_num_args() > 5) return false;
  elseif (func_num_args() == 2) $item = 'title';
  else {
    $item = htmlspecialchars(trim($aryargs[1])); // for XSS
    if (preg_match("/^(title|author|manufact|media|pricel|price|avail|content|s?image|link|lib0?|key)$/", $item) == false)
      return false;
  }

  $asin = htmlspecialchars($aryargs[0]);
  if (AMAZON_ALLOW_LNK) {
    if ($item == "link") return amazon_getlink($asin);
    elseif ($item == "key") return amazon_getkey($asin, $aryargs[2], $aryargs[3]); // &amazon(ID, key, Perl スクリプト, books);
    elseif ($item == "lib0") return amazon_getlib($asin, 0);
    elseif ($item == "lib") return amazon_getlib($asin, 1);
  }
  $check = new amazon_check_asin($asin); // for XSS
  if ($check->is_asin) {
    if ($item == 'image' or $item == 'simage') {
      $m_s = ($item == 'image')? 'm': 's';
      $info = new amazon_getinfo($check->asin, 'heavy');
      return amazon_get_imagelink($check->asin, $check->ext, '', $info->items['title'], $m_s);
    } elseif (preg_match("/^(title|author|manufact|media|content)$/", $item) == true) {
      $info = new amazon_getinfo($check->asin, 'heavy');
      if ($item == 'title') {
	return '<a href="' . AMAZON_SHOP . "$check->asin/" . AMAZON_AID . AMAZON_SIM . '">' . $info->items[$item] . '</a>';
      } else return $info->items[$item];
    } else {
      $info = new amazon_getinfo($check->asin, 'lite');
      return $info->items[$item];
    }
  }
}

// 書籍データを保存
function amazon_review_save($page, $data) {
  $filename = DATA_DIR . encode($page) . ".txt";

  if (!is_readable($filename))
    if (amazon_savefile($filename, $data)) return true;
  return false;
}

function amazon_getlib($id, $type) {
  global $_amazon_msg;

  $id = trim($id);
  $tmpary = array();
  if (! preg_match("/^([0-9]+)([a-z])?$/", $id, $tmpary)) return false; // a-z 1 文字はグラフィックパターン
  $id0 = $tmpary[1];
  if ($type == 1) $imagelink = '<a href="' . AMAZON_LIB . $id0 . '"><img src="' . CACHE_DIR . "AZ" . $id . '.gif" ';
  else $imagelink = '<a href="' . AMAZON_LIB0 . '"><img src="' . CACHE_DIR . "AZ" . $id . '.gif" ';
  $imagelink .= 'alt="{$_amazon_msg["msg_amazon"]}" /></a>';
  return $imagelink;
}

function amazon_get_imagelink($asin, $ext, $imgfile, $title, $m_s) {
  global $script;

  $imgfile = trim($imgfile);
  if (! preg_match("/^[0-9A-Za-z]+$/", $asin)) return false;
  if ($imgfile == '') {
    $img = new amazon_getimage($asin, $ext, $m_s);
    $imgfile = $img->file;
    // UPK
    $imgfile = $script.'?plugin=cache_ref&amp;src='.$imgfile;
  }
  $imagelink = '<a href="' . AMAZON_SHOP . $asin . "/" . AMAZON_AID . AMAZON_SIM . '">';
  $imagelink .= '<img src="' . $imgfile . '" alt="' . $title . '" /></a>';
  return $imagelink;
}

function amazon_getlink ($id) {
  $filename = CACHE_DIR . "AZ" . $id . ".link";
  $body = amazon_getfile($filename);
  return $body;
}

function amazon_getkey ($id, $key, $media) {
  global $vars, $array, $genre;
  global $_amazon_msg;

  $filename = CACHE_DIR . "AZ" . $id . ".link";
  $body = amazon_getfile($filename);
  $key = htmlspecialchars($key);
  //ページタイトル、空白で区切り数個並べる、単独使用
  $keys = ($key == '')? get_keys($vars['page']): split(' ', $key);
  srand();
  $key = $key0 = $keys[rand(0, count($keys) - 1)];
  $search = "search=" . rawurlencode(mb_convert_encoding($key, 'UTF-8', SOURCE_ENCODING));
  $media = trim(htmlspecialchars($media));
  $medias = preg_split('/ +/', $media);
  $media = ($media == '')? $genre[rand(0, count($genre) - 1)]: $medias[rand(0, count($medias) - 1)];
  $mode = "mode=$media-jp";

  $body = preg_replace("'mode=[^&]+'", $mode, $body);
  $body = preg_replace("'search=[^&]+'", $search, $body);
  $footer = "</iframe><div style='text-align:center'><span style='color:red'>$key0" . "{$_amazon_msg['msg_Relation']}<br />($media)</span></div>";
  $body = preg_replace("'</iframe>'", $footer, $body);

  return $body;
}

function amazon_savefile($file, $body) {
  $lock = "$file.lock.amazon"; // For Lock
  for ($loop = 10; $loop > 0; $loop--) {
    if (! is_readable($lock)) break;
    usleep(300000);
  }
  $fl = fopen($lock, "wb");
  fwrite($fl, "w");
  fclose($fl);
  $fp = fopen($file, "wb");
  if (! $fp) {
    unlink($lock);
    return false;
  }
  fwrite($fp, $body);
  fclose ($fp);
  unlink($lock);
  return true;
}

function amazon_getfile($file) {
  if (! preg_match('/^http:/', $file)) { // For Lock
    $lock = "$file.lock.amazon";
    for ($loop = 10; $loop > 0; $loop--) {
      if (! is_readable($lock)) break;
      usleep(300000);
    }
    if ($loop == 0) return '';
  }
  $fl = fopen($lock, "wb");
  fwrite($fl, "w");
  fclose($fl);
  $fp = fopen($file, "rb");
  if (! $fp) {
    unlink($lock);
    return '';
  }
  $body = '';
  while (!feof($fp)) $body .= fread($fp, 4096);
  fclose ($fp);
  unlink($lock);
  return $body;
}

function get_keys_1($stat, $stat1, $p0, $i, $str) {
  global $keys, $stat;
  $check = ($stat1 == 1)? false: ($stat == 1 and $i - $p0 > 1);
  $check2 = ($stat1 == 0)? true: ($stat != $stat1);
  if ($check or $check2 and $stat > 1 and $i - $p0 > 2) {
    $keys[count($keys)] = mb_convert_encoding(substr($str, $p0, $i - $p0), SOURCE_ENCODING, 'EUC-JP');
     $p0 = $i;
  } elseif ($stat == 0) $p0 = $i;
  $stat = $stat1;
  return $p0;
}

function get_keys($str) {
  global $keys, $stat;
  $str = mb_convert_encoding($str,'EUC-JP',SOURCE_ENCODING);
  $p0 = $stat = 0;
  for ($i = 0; $i < strlen($str); $i++) {
    $num = ord(substr($str, $i));
    if ($num >= 0x41 and $num <= 0x5a or $num >= 0x61 and $num <= 0x7a) $p0 = get_keys_1($stat, 1, $p0, $i, $str); // a-zA-Z
    elseif ($num >= 0xa1 and $num <= 0xfe) { // JIS X 0208
      $ku = $num - 0xa0;
      $ten = ord(substr($str, $i + 1)) - 0xa0;
      if ($ku == 5 or ($ku == 1 and ($ten == 6 or $ten == 28))) $p0 = get_keys_1($stat, 2, $p0, $i, $str); // 片仮名、ー、・
      elseif ($ku >= 16) $p0 = get_keys_1($stat, 3, $p0, $i, $str); // 漢字
      else $p0 = get_keys_1($stat, 0, $p0, $i, $str);
      $i++;
    } else $p0 = get_keys_1($stat, 0, $p0, $i, $str);
  }
  $p0 = get_keys_1($stat, 0, $p0, $i, $str);
  if (count($keys) == 0) $keys[0] = $str;
  return $keys;
}

class amazon_getinfo {
  function amazon_getinfo ($asin, $which) {
    global $body;
    if ($which != 'lite') {
      $this->amazon_cache_save($asin, "heavy");
      $filename = CACHE_DIR . "ASIN" . $asin . ".heavy";
      $body = amazon_getfile($filename);
      $this->items['title'] = $this->amazon_get_item('/<ProductName>([^<]*)</');
      $this->items['manufact'] = $this->amazon_get_item('/<Manufacturer>([^<]*)</');
      $this->items['media'] = $this->amazon_get_item('/<Media>([^<]*)</');
      if (AMAZON_ALLOW_CONT) {
	$this->items['content'] = $this->amazon_get_item('/<ProductDescription>([^<]*)</');
	$this->items['content'] = preg_replace("'&amp;'", '&', $this->items['content']);
	$this->items['content'] = preg_replace("'&lt;'", '<', $this->items['content']);
      } else $this->items['content'] = '';
      if (! $this->amazon_add_author('|<Author>(.[^<]*)<|U'))
	if (! $this->amazon_add_author('|<Director>(.[^<]*)<|U')) $this->amazon_add_author('|<Artist>(.[^<]*)<|U');
    }
    if ($which != 'heavy') {
      $this->amazon_cache_save($asin, "lite");
      $filename = CACHE_DIR . "ASIN" . $asin . ".lite";
      $body = amazon_getfile($filename);
      $this->items['title'] = $this->amazon_get_item('/<ProductName>([^<]*)</');
      $this->items['manufact'] = $this->amazon_get_item('/<Manufacturer>([^<]*)</');
      $this->items['pricel'] = $this->amazon_get_item('/<ListPrice>￥ *([^<]*)</');
      $this->items['price'] = $this->amazon_get_item('/<OurPrice>￥ *([^<]*)</');
      $this->items['avail'] = $this->amazon_get_item('/<Availability>([^<]*)</');
    }
  }
  function amazon_add_author ($regexp) {
    global $body;
    $count = preg_match_all($regexp, $body, $tmpary);
    if ($count > 0) {
      for ($i=0; $i<$count; $i++) {
	if ($i > 0) $this->items['author'] .= ", ";
	$this->items['author'] .= trim($tmpary[1][$i]);
      }
      return true;
    } else return false;
  }
  function amazon_get_item ($regexp) {
    global $body;
    return (preg_match($regexp, $body, $tmpary))? trim($tmpary[1]): "";
  }
  function amazon_cache_save ($asin, $type) {
    $filename = CACHE_DIR . "ASIN" . $asin . "." . $type;
    $get_it = 0;
    $expire = ($type == 'lite')? AMAZON_EXPIRE_lite: AMAZON_EXPIRE_heavy; 
    if (!is_readable($filename)) $get_it = 1;
    elseif ($expire * 3600 < time() - filemtime($filename)) $get_it = 1;
    if ($get_it) {
      $rc = http_request(AMAZON_XML . "type=$type&AsinSearch=" . $asin);
      // $body = mb_convert_encoding($rc["data"], SOURCE_ENCODING, "UTF-8");
      $body = $rc["data"];
      amazon_savefile($filename, $body);
      if ($type == 'heavy') {
	$filename = CACHE_DIR . "ASIN" . $asin . ".lite";
	amazon_savefile($filename, $body);
      }
    }
  }
}

class amazon_check_asin {
  var $asin;
  var $ext;
  function amazon_check_asin($asin_old) {
    $tmpary = array();
    if (preg_match("/^([A-Z0-9]{10}).?([0-9][0-9])?$/", $asin_old, $tmpary) == true) {
      $this->asin = $tmpary[1];
      $this->ext = $tmpary[2];
      if ($asin_ext == '') $this->ext = "09";
      $this->is_asin = true;
    } else $this->is_asin = false;
  }
}

class amazon_getimage {
  function amazon_getimage ($asin, $ext, $m_s) {
    global $use_proxy;

    $filename = "ASIN" . $asin . (($m_s == "s")? ".gif": ".jpg");
    $fileext = ($m_s == "s")? "TZZZZZZZ.gif": "MZZZZZZZ.jpg";
    $get_it = 0;
    if (!is_readable(CACHE_DIR.$filename)) $get_it = 1;
    elseif (AMAZON_EXPIRE_img * 3600 < time() - filemtime(CACHE_DIR.$filename)) $get_it = 1;
    if ($get_it) {
      if ($use_proxy) $rc = http_request(AMAZON_IMAGE . "$asin.$ext.$fileext"); // Thanks to upk
      else $rc["data"] = amazon_getfile(AMAZON_IMAGE . "$asin.$ext.$fileext");
      if ($this->amazon_getimagesize($rc["data"], $asin) <= 1) { // 通常は 1 が返る (reimy)
	// キャッシュを NO_IMAGE のコピーとする
	if ($ext == "09") {
	  if ($use_proxy) $rc = http_request(AMAZON_IMAGE . "$asin.01.$fileext");
	  else $rc["data"] = amazon_getfile(AMAZON_IMAGE . "$asin.01.$fileext");
	  if ($this->amazon_getimagesize($rc["data"], $asin) <= 1) {
	    $rc["data"] = amazon_getfile(AMAZON_NO_IMAGE);
	    if ($rc["data"] == "") return false; 
	  }
	}
      }
      if (! amazon_savefile(CACHE_DIR.$filename, $rc["data"])) return false;
    }
    $this->file = $filename;
  }
  function amazon_getimagesize($body, $asin) {
    if ($body == "") return 0;
    $tmpfile = CACHE_DIR . "ASIN$asin.jpg.0";
    if (! amazon_savefile($tmpfile, $body)) return 0;
    $size = getimagesize($tmpfile);
    unlink($tmpfile);
    return $size[1];
  }
}

?>
