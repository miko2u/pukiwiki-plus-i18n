<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: qrcode.inc.php,v 0.8.4 2005/07/29 13:57:36 miko Exp $
//
/*
*内容
QR画像を生成するプラグイン
*書式
 &qrcode{バーコード化する文字列};
 &qrcode(サイズ){バーコード化する文字列};
 &qrcode(サイズ,訂正方法){バーコード化する文字列};
 &qrcode(サイズ,訂正方法,バージョン){バーコード化する文字列};
 &qrcode(サイズ,訂正方法,バージョン,分割数){バーコード化する文字列};
*引数
|サイズ     |バーコードの最小ピクセル            | 1 |
|訂正方法   |訂正コードのレベルです(N/M/H/Q)     | M |
|バージョン |使用するQRコードのバージョン(1～10) |自動判別  |
|分割数     |分割バーコード数(2～16)             |分割しない|
|文字列     |バーコード化する文字列              |(省略不可)|
*著作権
QRcode Perl CGI & PHP scripts ver. 0.50g, (c)2001-2005 Y.Swetake
QRcode PukiWiki 1.4 Plug-in, (c)2004-2005 Miko.Hoshina
*ライセンス
GPL
*コメント
分割数は悩んだ末にバージョンの後ろにしました。
これは分割するなら画像サイズ（＝バージョン）が
そろっていたほうがいいとおもったためです。
*/

// QRデータの格納ディレクトリ
define('QRCODE_DATA_DIR',  PLUGIN_DIR.'qrcode/');
// QRイメージの格納ディレクトリ
define('QRCODE_IMAGE_DIR', PLUGIN_DIR.'qrcode/');
// 最大扱えるバージョン
define('QRCODE_MAX_VERSION','10');
// 最大扱える分割数
define('QRCODE_MAX_SPLIT','16');
// 漢字を使用する場合の変換コード
define('QRCODE_ENCODING','SJIS');

// インラインはアクション用のアドレスを作成するのみ
function plugin_qrcode_inline()
{
	global $script;

	if (!plugin_qrcode_issupported()) {
		return FALSE;
	}

	if (func_num_args() == 5) {
		list($s,$e,$v,$n,$d) = func_get_args();
	}
	else if (func_num_args() == 4) {
		list($s,$e,$v,$d) = func_get_args();
	}
	else if (func_num_args() == 3) {
		list($s,$e,$d) = func_get_args();
	}
	else if (func_num_args() == 2) {
		list($s,$d) = func_get_args();
	}
	else if (func_num_args() == 1) {
		list($d) = func_get_args();
	}
	else {
		return FALSE;
	}

	// thx, nanashi and customized
	$s = intval($s);
	if ( $s <= 0 ) { $s = 0; }
	$v = isset($v) ? intval($v):0;
	if ( $v <= 0 && $v > QRCODE_MAX_VERSION ) { $v = 0; }
	$n = isset($n) ? intval($n):0;
	if ( $n <= 0 && $n > QRCODE_MAX_SPLIT ) { $n = 0; }
	$e = htmlspecialchars(isset($e)?$e:'');

	// if no string, no display.
	if (empty($d)) return FALSE;

	// thx, nao-pon
	$d = str_replace("<br />","\r\n",$d);
	$d = strip_tags($d);

	// docomo is s-jis encoding
	$d = mb_convert_encoding($d,QRCODE_ENCODING,SOURCE_ENCODING);

	$addsize = '';
	$addparam = '';
	if ($s > 0) { $addsize .= "&amp;s=$s"; }
	if ($v > 0 && $v <= QRCODE_MAX_VERSION) { $addparam .= "&amp;v=$v"; }
	if ($e != '') { $addparam .= "&amp;e=$e"; }
	if ($n < 2 || $n > 16) {
		$d = rawurlencode($d);
		if (defined('UA_MOBILE') && UA_MOBILE != 0) {
			$result = "<a href=\"$script?plugin=qrcode&amp;d=$d&amp;s=4$addparam\"><img src=\"$script?plugin=qrcode&amp;d=$d$addsize$addparam\" alt=\"$d\" title=\"keitai\" /></a>";
		} else {
			$result = "<a href=\"$script?plugin=qrcode&amp;d=$d&amp;s=4$addparam\"><img src=\"$script?plugin=qrcode&amp;d=$d$addsize$addparam\" alt=\"$d\" title=\"$d\" /></a>";
		}
	} else {
		// パリティを計算
		$l=strlen($d);
		if ($l>1){
			$p=0;
			$i=0;
			while ($i<$l){
				$p=($p ^ ord(substr($d,$i,1)));
				$i++;
			}
		}
		// 並べる(本来ならPNGを合成するのがきれいでしょうけどね)
		$result = "<nobr>";
		$i=0;
		for ($j=1;$j<=$n;$j++) {
			$splitdata = substr($d,$i,ceil($l/$n));
			$i += ceil($l/$n);
			$splitdata = rawurlencode($splitdata);
			$result .= "<img src=\"$script?plugin=qrcode&amp;p=$p&amp;m=$j&amp;n=$n&amp;d=$splitdata$addsize$addparam\" />";
		}
		$result .= "</nobr>\n";
	}
	return $result;
}

// アクションでは、実際の画像を作成
function plugin_qrcode_action()
{
	global $vars;

	if (!plugin_qrcode_issupported()) {
		return FALSE;
	}
	if (empty($vars['d'])) {
		return FALSE;
	}

	$qr['data']   = rawurldecode($vars['d']);
	$qr['size']   = (empty($vars['s'])) ? 1 : $vars['s'];
	$qr['ver']    = (empty($vars['v'])) ? 0 : $vars['v'];
	$qr['ecc']    = (empty($vars['e'])) ? 'M' : $vars['e'];
	$qr['split']  = (empty($vars['m'])) ? 1 : $vars['m'];
	$qr['total']  = (empty($vars['n'])) ? 1 : $vars['n'];
	$qr['parity'] = (empty($vars['p'])) ? 0 : $vars['p'];
	
	/* Thanks nanashi */
	echo QRcode($qr);
	exit;
}

// 画像をサポートしているか？
function plugin_qrcode_issupported()
{
	$issupported = FALSE;
	if (function_exists("gd_info")) {
		$gdinfo = gd_info();
		if (isset($gdinfo['PNG Support']) && $gdinfo['PNG Support'] === TRUE) {
			$issupported = TRUE;
		}
	}
	return $issupported;
}

// 実際にQRコードの画像の中身を作成する
function QRcode($qr)
{
	$qrcode_data_string            = $qr['data'];
	$qrcode_module_size            = $qr['size'];
	$qrcode_error_correct          = $qr['ecc'];
	$qrcode_version                = $qr['ver'];
	$qrcode_structureappend_m      = $qr['split'];
	$qrcode_structureappend_n      = $qr['total'];
	$qrcode_structureappend_parity = $qr['parity'];
	$data_length = strlen($qrcode_data_string);
	if ($data_length<=0) {
	    trigger_error("QRcode : Data do not exist.", E_USER_ERROR);
		return '';
	}

	$data_counter = 0;
	if ($qrcode_structureappend_n>1 && $qrcode_structureappend_n<=16
	 && $qrcode_structureappend_m>0 && $qrcode_structureqppend_m<=16)
	{
		$data_value[0]=3;
		$data_bits[0]=4;

		$data_value[1]=$qrcode_structureappend_m-1;
		$data_bits[1]=4;

		$data_value[2]=$qrcode_structureappend_n-1;
		$data_bits[2]=4;

		$originaldata_length=strlen($qrcode_structureappend_originaldata);
		if ($originaldata_length>1){
			$qrcode_structureappend_parity=0;
			$i=0;
			while ($i<$originaldata_length){
				$qrcode_structureappend_parity=($qrcode_structureappend_parity ^ ord(substr($qrcode_structureappend_originaldata,$i,1)));
				$i++;
			}
		}

		$data_value[3]=$qrcode_structureappend_parity;
		$data_bits[3]=8;

		$data_counter=4;
	}
	$data_bits[$data_counter] = 4;

	if (!ereg("[^0-9]",$qrcode_data_string))
	{
		// numeric mode
		$codeword_num_plus = array('',
			0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,
			2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
		);
		$data_value[$datacounter]=1;
		$data_counter++;
		$data_value[$data_counter]=$data_length;
		$data_bits[$data_counter]=10;  /* #version 1-9 */
		$codeword_num_counter_value=$data_counter;

		$i=0;
		$data_counter++;
		while ($i<$data_length)
		{
			if (($i % 3)==0)
			{
				$data_value[$data_counter]=substr($qrcode_data_string,$i,1);
				$data_bits[$data_counter]=4;
			}
			else
			{
				$data_value[$data_counter]=$data_value[$data_counter]*10+substr($qrcode_data_string,$i,1);
				if (($i % 3)==1)
				{
					$data_bits[$data_counter]=7;
				}
				else
				{
					$data_bits[$data_counter]=10;
					$data_counter++;
				}
			}
			$i++;
		}
	}
	else if (!ereg("[^0-9A-Z \$\*\%\+\-\.\/\:]",$qrcode_data_string))
	{
		// alphanum mode
        $codeword_num_plus = array('',
        	0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,
        	2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
        );
        $data_value[$data_counter]=2;
        $data_counter++;
        $data_value[$data_counter]=$data_length;
        $data_bits[$data_counter]=9;  /* #version 1-9 */
        $codeword_num_counter_value=$data_counter;

        $alphanumeric_character_hash = array(
        	'0'=> 0,'1'=> 1,'2'=> 2,'3'=> 3,'4'=> 4,'5'=> 5,'6'=> 6,'7'=> 7,'8'=> 8, '9'=>9,
        	'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,'I'=>18,'J'=>19,
        	'K'=>20,'L'=>21,'M'=>22,'N'=>23,'O'=>24,'P'=>25,'Q'=>26,'R'=>27,'S'=>28,'T'=>29,
        	'U'=>30,'V'=>31,'W'=>32,'X'=>33,'Y'=>34,'Z'=>35,' '=>36,'$'=>37,'%'=>38,'*'=>39,
        	'+'=>40,'-'=>41,'.'=>42,'/'=>43,':'=>44
        );

        $i=0;
        $data_counter++;
        while ($i<$data_length)
        {
            if (($i %2)==0)
            {
                $data_value[$data_counter]=$alphanumeric_character_hash[substr($qrcode_data_string,$i,1)];
                $data_bits[$data_counter]=6;
            }
            else
            {
                $data_value[$data_counter]=$data_value[$data_counter]*45+$alphanumeric_character_hash[substr($qrcode_data_string,$i,1)];
                $data_bits[$data_counter]=11;
                $data_counter++;
            }
            $i++;
        }

	}
	else
	{
		// binary(8bit) mode
		$codeword_num_plus = array('',
			0,0,0,0,0,0,0,0,0,8,8,8,8,8,8,8,8,8,8,8,
			8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8
		);
        $data_value[$data_counter]=4;
        $data_counter++;
        $data_value[$data_counter]=$data_length;
        $data_bits[$data_counter]=8;   /* #version 1-9 */
        $codeword_num_counter_value=$data_counter;

        $data_counter++;
        $i=0;
        while ($i<$data_length)
		{
            $data_value[$data_counter]=ord(substr($qrcode_data_string,$i,1));
            $data_bits[$data_counter]=8;
            $data_counter++;
            $i++;
        }
	}

	if (@$data_bits[$data_counter]>0)
	{
		$data_counter++;
	}
	$i=0;
	$total_data_bits=0;
	while($i<$data_counter)
	{
		$total_data_bits+=$data_bits[$i];
		$i++;
	}

	$ecc_character_hash=array(
		'L'=>'1',
		'l'=>'1',
		'M'=>'0',
		'm'=>'0',
		'Q'=>'3',
		'q'=>'3',
		'H'=>'2',
		'h'=>'2'
	);

	$ec = @$ecc_character_hash[$qrcode_error_correct]; 
	if (!$ec){ $ec=0; }

	$max_data_bits_array = array(0,
		  128,  224,  352,  512,  688,  864,  992, 1232, 1456, 1728,
		  152,  272,  440,  640,  864, 1088, 1248, 1552, 1856, 2192,
		   72,  128,  208,  288,  368,  480,  528,  688,  800,  976,
		  104,  176,  272,  384,  496,  608,  704,  880, 1056, 1232,
	);

	if (!is_numeric($qrcode_version)) {
	    $qrcode_version=0;
	}

	// バージョンを設定していないときは、自動的に設定
	if (!$qrcode_version)
	{
		$i= 1 + (QRCODE_MAX_VERSION * $ec);
		$j= $i + (QRCODE_MAX_VERSION - 1);
		$qrcode_version=1; 
		while ($i<=$j)
		{
			if (($max_data_bits_array[$i])>=$total_data_bits+$codeword_num_plus[$qrcode_version])
			{
				$max_data_bits=$max_data_bits_array[$i];
				break;
			}
			$i++;
			$qrcode_version++;
		}
	}
	else
	{
		$max_data_bits=$max_data_bits_array[$qrcode_version+(QRCODE_MAX_VERSION * $ec)];
	}

	if ($qrcode_version > QRCODE_MAX_VERSION) {
		trigger_error("QRcode : too large version.", E_USER_ERROR);
		return '';
	}

	$total_data_bits+=$codeword_num_plus[$qrcode_version];
    $data_bits[$codeword_num_counter_value]+=$codeword_num_plus[$qrcode_version];

	$max_codewords_array = array(0,
		  26,  44,  70, 100, 134, 172, 196, 242, 292, 346,
		 404, 466, 532, 581, 655, 733, 815, 901, 991,1085,
		1156,1258,1364,1474,1588,1706,1828,1921,2051,2185,
		2323,2465,2611,2761,2876,3034,3196,3362,3532,3706
	);

	$max_codewords=$max_codewords_array[$qrcode_version];
	$max_modules_1side=17+($qrcode_version <<2);

	$matrix_remain_bit = array(0,
		0,7,7,7,7,7,0,0,0,0,
		0,0,0,3,3,3,3,3,3,3,
		4,4,4,4,4,4,4,3,3,3,
		3,3,3,3,0,0,0,0,0,0
	);

	/* read version ECC data file */
	$byte_num=$matrix_remain_bit[$qrcode_version]+($max_codewords << 3);
	$filename=QRCODE_DATA_DIR ."/qrv".$qrcode_version."_".$ec.".dat";

	$fp1=fopen($filename,"rb");
	$matx=fread($fp1,$byte_num);
	$maty=fread($fp1,$byte_num);
	$masks=fread($fp1,$byte_num);
	$fi_x=fread($fp1,15);
	$fi_y=fread($fp1,15);
	$rs_ecc_codewords=ord(fread($fp1,1));
	$rso=fread($fp1,128);
	fclose($fp1);

	$matrix_x_array=unpack("C*",$matx);
	$matrix_y_array=unpack("C*",$maty);
	$mask_array=unpack("C*",$masks);

	$rs_block_order=unpack("C*",$rso);

	$format_information_x2=unpack("C*",$fi_x);
	$format_information_y2=unpack("C*",$fi_y);
	$format_information_x1=array(0,1,2,3,4,5,7,8,8,8,8,8,8,8,8);
	$format_information_y1=array(8,8,8,8,8,8,8,8,7,5,4,3,2,1,0);

	$max_data_codewords=($max_data_bits >>3);

	$filename = QRCODE_DATA_DIR ."/rsc".$rs_ecc_codewords.".dat";
	$fp0 = fopen($filename,"rb");
	$i=0;
	while ($i<256)
	{
		$rs_cal_table_array[$i]=fread($fp0,$rs_ecc_codewords);
		$i++;
	}
	fclose ($fp0);

	/*  --- set terminator */
	if ($total_data_bits<=$max_data_bits-4)
	{
		$data_value[$data_counter]=0;
		$data_bits[$data_counter]=4;
	} else {
		if ($total_data_bits<$max_data_bits) {
			$data_value[$data_counter]=0;
			$data_bits[$data_counter]=$max_data_bits-$total_data_bits;
		} else {
			if ($total_data_bits>$max_data_bits) {
			    trigger_error("QRcode : Overflow error",E_USER_ERROR);
				return '';
			}
		}
	}

	/* ----divide data by 8bit */
	$i=0;
	$codewords_counter=0;
	$codewords[0]=0;
	$remaining_bits=8;

	while ($i<=$data_counter) {
	    $buffer=@$data_value[$i];
	    $buffer_bits=@$data_bits[$i];

	    $flag=1;
	    while ($flag) {
	        if ($remaining_bits>$buffer_bits){  
	            $codewords[$codewords_counter]=((@$codewords[$codewords_counter]<<$buffer_bits) | $buffer);
	            $remaining_bits-=$buffer_bits;
	            $flag=0;
	        } else {
	            $buffer_bits-=$remaining_bits;
	            $codewords[$codewords_counter]=(($codewords[$codewords_counter] << $remaining_bits) | ($buffer >> $buffer_bits));

	            if ($buffer_bits==0) {
	                $flag=0;
	            } else {
	                $buffer= ($buffer & ((1 << $buffer_bits)-1) );
	                $flag=1;   
	            }

	            $codewords_counter++;
	            if ($codewords_counter<$max_data_codewords-1){
	                $codewords[$codewords_counter]=0;
	            }
	            $remaining_bits=8;
	        }
	    }
	    $i++;
	}
	if ($remaining_bits!=8) {
	    $codewords[$codewords_counter]=$codewords[$codewords_counter] << $remaining_bits;
	} else {
	    $codewords_counter--;
	}

	/* ----  set padding character */
	if ($codewords_counter<$max_data_codewords-1){
	    $flag=1;
	    while ($codewords_counter<$max_data_codewords-1){
	        $codewords_counter++;
	        if ($flag==1) {
	            $codewords[$codewords_counter]=236;
	        } else {
	            $codewords[$codewords_counter]=17;
	        }
	        $flag=$flag*(-1);
	    }
	}

	/* ---- RS-ECC prepare */
	$i=0;
	$j=0;
	$rs_block_number=0;
	$rs_temp[0]="";

	while($i<$max_data_codewords){

	    $rs_temp[$rs_block_number].=chr($codewords[$i]);
	    $j++;

	    if ($j>=$rs_block_order[$rs_block_number+1]-$rs_ecc_codewords){
	        $j=0;
	        $rs_block_number++;
	        $rs_temp[$rs_block_number]="";
	    }
	    $i++;
	}


	/*
	#
	# RS-ECC main
	#
	*/
	$rs_block_number=0;
	$rs_block_order_num=count($rs_block_order);

	while ($rs_block_number<$rs_block_order_num){

		$rs_codewords=$rs_block_order[$rs_block_number+1];
		$rs_data_codewords=$rs_codewords-$rs_ecc_codewords;

		$rstemp=$rs_temp[$rs_block_number].str_repeat(chr(0),$rs_ecc_codewords);
		$padding_data=str_repeat(chr(0),$rs_data_codewords);

    $j=$rs_data_codewords;
    while($j>0){
        $first=ord(substr($rstemp,0,1));

        if ($first){
            $left_chr=substr($rstemp,1);
            $cal=$rs_cal_table_array[$first].$padding_data;
            $rstemp=$left_chr ^ $cal;
        } else {
            $rstemp=substr($rstemp,1);
        }

        $j--;
    }

    $codewords=array_merge($codewords,unpack("C*",$rstemp));

    $rs_block_number++;
}

	// マトリックスの初期化
//	for ($i=0;$i<$max_modules_1side;$i++) {
//		for ($j=0;$j<$max_modules_1side;$j++) {
//			$matrix_content[$j][$i]=0;
//		}
//	}
	$matrix_content = array(array());

	// データの埋め込み
	$i=0;
	while ($i<$max_codewords)
	{
		$codeword_i=$codewords[$i];
		$j=8;
		while ($j>=1) {
			$codeword_bits_number=($i << 3) +  $j;
			$matrix_content[ $matrix_x_array[$codeword_bits_number] ][ $matrix_y_array[$codeword_bits_number] ]=((255*($codeword_i & 1)) ^ $mask_array[$codeword_bits_number] ); 
			$codeword_i= $codeword_i >> 1;
			$j--;
		}
		$i++;
	}

	$matrix_remain=$matrix_remain_bit[$qrcode_version];
	while ($matrix_remain)
	{
		$remain_bit_temp = $matrix_remain + ( $max_codewords <<3);
		$matrix_content[ $matrix_x_array[$remain_bit_temp] ][ $matrix_y_array[$remain_bit_temp] ]  =  ( 255 ^ $mask_array[$remain_bit_temp] );
		$matrix_remain--;
	}

	#--- mask select

	$min_demerit_score=0;
	    $hor_master="";
	    $ver_master="";
	    $k=0;
	    while($k<$max_modules_1side){
	        $l=0;
	        while($l<$max_modules_1side){
	            $hor_master=$hor_master.chr(isset($matrix_content[$l][$k])?$matrix_content[$l][$k]:0);
	            $ver_master=$ver_master.chr(isset($matrix_content[$k][$l])?$matrix_content[$k][$l]:0);
	            $l++;
	        }
	        $k++;
	    }
	$i=0;
	$all_matrix=$max_modules_1side * $max_modules_1side;

	while ($i<8){
	    $demerit_n1=0;
	    $ptn_temp=array();
	    $bit= 1<< $i;
	    $bit_r=(~$bit)&255;
	    $bit_mask=str_repeat(chr($bit),$all_matrix);
	    $hor = $hor_master & $bit_mask;
	    $ver = $ver_master & $bit_mask;

	    $ver_shift1=$ver.str_repeat(chr(170),$max_modules_1side);
	    $ver_shift2=str_repeat(chr(170),$max_modules_1side).$ver;
	    $ver_shift1_0=$ver.str_repeat(chr(0),$max_modules_1side);
	    $ver_shift2_0=str_repeat(chr(0),$max_modules_1side).$ver;
	    $ver_or=chunk_split(~($ver_shift1 | $ver_shift2),$max_modules_1side,chr(170));
	    $ver_and=chunk_split(~($ver_shift1 & $ver_shift2),$max_modules_1side,chr(170));

	    $hor=chunk_split(~$hor,$max_modules_1side,chr(170));
	    $ver=chunk_split(~$ver,$max_modules_1side,chr(170));
	    $hor=$hor.chr(170).$ver;

	    $n1_search="/".str_repeat(chr(255),5)."+|".str_repeat(chr($bit_r),5)."+/";
	    $n3_search=chr($bit_r).chr(255).chr($bit_r).chr($bit_r).chr($bit_r).chr(255).chr($bit_r);

		$demerit_n3=substr_count($hor,$n3_search)*40;
		$demerit_n4=floor(abs(( (100* (substr_count($ver,chr($bit_r))/($byte_num)) )-50)/5))*10;


	   $n2_search1="/".chr($bit_r).chr($bit_r)."+/";
	   $n2_search2="/".chr(255).chr(255)."+/";
	   $demerit_n2=0;
	   preg_match_all($n2_search1,$ver_and,$ptn_temp);
	   foreach($ptn_temp[0] as $str_temp){
	       $demerit_n2+=(strlen($str_temp)-1);
	   }
	   $ptn_temp=array();
	   preg_match_all($n2_search2,$ver_or,$ptn_temp);
	   foreach($ptn_temp[0] as $str_temp){
	       $demerit_n2+=(strlen($str_temp)-1);
	   }
	   $demerit_n2*=3;
	  
	   $ptn_temp=array();

	   preg_match_all($n1_search,$hor,$ptn_temp);
	   foreach($ptn_temp[0] as $str_temp){
	       $demerit_n1+=(strlen($str_temp)-2);
	   }

	   $demerit_score=$demerit_n1+$demerit_n2+$demerit_n3+$demerit_n4;

	   if ($demerit_score<=$min_demerit_score || $i==0){
	        $mask_number=$i;
	        $min_demerit_score=$demerit_score;
	   }

	$i++;
	}

	$mask_content=1 << $mask_number;

	# --- format information
	$format_information_value=(($ec << 3) | $mask_number);
	$format_information_array=array(
		"101010000010010","101000100100101","101111001111100","101101101001011",
		"100010111111001","100000011001110","100111110010111","100101010100000",
		"111011111000100","111001011110011","111110110101010","111100010011101",
		"110011000101111","110001100011000","110110001000001","110100101110110",
		"001011010001001","001001110111110","001110011100111","001100111010000",
		"000011101100010","000001001010101","000110100001100","000100000111011",
		"011010101011111","011000001101000","011111100110001","011101000000110",
		"010010010110100","010000110000011","010111011011010","010101111101101"
	);
	$i=0;
	while ($i<15){
		$content=substr($format_information_array[$format_information_value],$i,1);
		$matrix_content[$format_information_x1[$i]][$format_information_y1[$i]]=$content * 255;
		$matrix_content[$format_information_x2[$i+1]][$format_information_y2[$i+1]]=$content * 255;
		$i++;
	}

	$mib = $max_modules_1side + 8;
	$qrcode_image_size = $mib * $qrcode_module_size;
	if ($qrcode_image_size>1480){
		trigger_error("QRcode : Too large image size",E_USER_ERROR);
		return '';
	}
	$output_image = ImageCreateTrueColor($qrcode_image_size,$qrcode_image_size);
	$base_image = ImageCreateFromPNG(QRCODE_IMAGE_DIR ."/qrv".$qrcode_version.".png");
	$col[1]=ImageColorAllocate($base_image,  0,  0,  0);
	$col[0]=ImageColorAllocate($base_image,255,255,255);

	// 4 is white-pixel margin
	$i = 4;
	$mxe = 4 + $max_modules_1side;
	$ii = 0;
	while ($i<$mxe) {
		$j=4;
		$jj=0;
		while ($j<$mxe) {
			if (isset($matrix_content[$ii][$jj]) && ($matrix_content[$ii][$jj] & $mask_content)) {
				ImageSetPixel($base_image,$i,$j,$col[1]); 
			}
			$j++;
			$jj++;
		}
		$i++;
		$ii++;
	}

	/* Output Images(Thanks nanashi) */
	if (ImageTypes() & IMG_GIF) {
		header("Content-type: image/gif");
	} else {
		header("Content-type: image/png");
	}
	ImageCopyResized($output_image,$base_image,0,0,0,0,$qrcode_image_size,$qrcode_image_size,$mib,$mib);
	ImageTrueColorToPalette($output_image,false,2);
	if (ImageTypes() & IMG_GIF) {
		ImageGif($output_image);
	} else {
		ImagePng($output_image);
	}
}
?>
