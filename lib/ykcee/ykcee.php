<?php

/*******************************************
* $Id: ykcee.php,v 1.9 2000/08/30 14:34:26 alekca Exp $
*
* MODIFICATION
* 2006-08-21 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
*
*******************************************/

/*******************************************
* Globale Variablen
*******************************************/

global $DEBUG;
$DEBUG = 0;

/*******************************************
* Farbtabelle
*******************************************/

global $ColorArray;
$ColorArray = array(
	'black'		=> array(  0,   0,   0),
	'maroon'	=> array(128,   0,   0),
	'green'		=> array(  0, 128,   0),
	'olive'		=> array(128, 128,   0),
	'navy'		=> array(  0,   0, 128),
	'purple'	=> array(128,   0, 128),
	'teal'		=> array(  0, 128, 128),
	'gray'		=> array(128, 128, 128),
	'silver'	=> array(192, 192, 192),
	'red'		=> array(255,   0,   0),
	'lime'		=> array(  0, 255,   0),
	'yellow'	=> array(255, 255,   0),
	'blue'		=> array(  0,   0, 255),
	'fuchsia'	=> array(255,   0, 255),
	'aqua'		=> array(  0, 255, 255),
	'lrot'		=> array(211, 167, 168),
	'mrot'		=> array(140,  34,  34),
	'drot'		=> array( 87,  16,  12),
	'wueste'	=> array(195, 195, 180),
	'white'		=> array(255, 255, 255));			

function ColorSet($p_name) {
	global $ColorArray;
	return $ColorArray[$p_name];
}

/*******************************************
* class ykcee 
*******************************************/

class ykcee {
	var $img;
	var $image_width = 320;
	var $image_height = 240;
	var $graph_type;
	var $background_color = 'white';
	var $chart_type = 'bars';
	var $chart_area = array(0, 0, 320, 240);
	var $chart_background_color = 'silver';
	var $chart_border_color = array('black');
	var $chart_title = 'Header';
	var $chart_title_size = 14;
	var $chart_title_color;
	var $font_color;
	var $data_values;
	var $bar_color;
	var $bar_border_color;
	var $col_bar_color;
	var $col_bar_border_color;
	var $legend_position;
	var $x_label_height;
	var $y_label_width;
	var $title_axis_x;
	var $title_axis_y;
	var $axis_font_size = 8;
	var $tick_length;
	var $tick_interval;
	var $line_thickness = 1;
	var $grid_x;
	var $grid_y;
	var $grid_color;
	var $axis_color;
	var $axis_title_size;
	var $max_string_size = 12;
	var $record_count = 0;
	var $record_count_group = 0;
	var $record_min = 0;
	var $record_max = 0;
	var $record_group = 0;
	var $record_bar_width = 0;
	var $record_group_space = 0;
	var $point_size = 2;
	var $point_shape = 'rectangular';
	var $arc_start = 0;
	var $legend;
	var $title_font;
	var $font;
	var $no_data = 'Sorry, no data available';

	/***************************************
	* SetRecords
	***************************************/

	function SetRecords() {
		$min = 0;
		$max = 0;
		$group = 0;

		$extarr = $this->data_values;
		$anz_record = 0;

		foreach($extarr as $arr) {
			$gc = 0;
			foreach ($arr as $v) {
				if ($v != $arr[0]) {
					$v = (float)$v;
					if ($v > $max) $max = $v;
					if ($v < $min) $min = $v;
				}
					$gc++;
					$anz_record++;		
			}
			if ($gc > $group) $group = $gc;
		}

		if (ceil($max/10) * 10 == $max) {
			$max = (ceil($max/10) * 10) + ceil($max/10);	
		} else {
			$max = ceil($max/10) * 10;
		}

		$this->record_min = $min;
		$this->record_max = $max;
		$this->record_group = $group - 1;
		$this->record_count_group = count($this->data_values);
		$this->record_count = $anz_record - $this->record_count_group;
	}

	/***************************************
	* TTFStringSize
	***************************************/

	function TTFStringSize($size, $angle, $font, $string) {

		$arr = ImageTTFBBox($size, $angle, $font, $string);

		switch($angle) {
			case 0:
				$width 	= $arr[4] - $arr[6];
				$height = $arr[3] - $arr[5];
				break;
			case 90:
				$height = $arr[5] - $arr[7];
				$width = $arr[2] - $arr[4];
				break;
			default:
				$width = 0;
				$height = 0;
				break;
		}

		$width = abs($width);
		$height = abs($height);

		/*
		printf("Koordinaten des Strings <b>%s</b> bei Angle %s<br>
				(%s/%s) - (%s/%s)<br>
				(%s/%s) - (%s/%s)<br>
				somit: <br>
				width = %s<br>
				height = %s<br>
				&nbsp;<br>", 
			$string, 
			$angle,
			$arr[6], $arr[7], 
			$arr[4], $arr[5], 
			$arr[0], $arr[1], 
			$arr[2], $arr[3],
			$width,
			$height);
		*/

		return array($width, $height);	
	}

	/***************************************
	* SetFileFormat
	***************************************/

	function SetFileFormat($p_file_format) {
		$accepted = array('jpg', 'gif', 'png');
		$wished = strtolower(trim($p_file_format));
		
		if (in_array($wished, $accepted) == true) {
			$this->file_format = $wished;
			$rv = true;
		} else {
			$rv = false;
		}
		return $rv;
	}

	/***************************************
	* SetChartArea
	***************************************/

	function SetChartArea() {

		//print "<br>&nbsp;<br><b>Komplettdurchgang</b><br>";

		// a) Die ChartArea wird ebensogroß wie die gesamte Bildfläche gesetzt
		$this->chart_area = array(0, 0, $this->image_width, $this->image_height);

		// b) An der rechten Seite werden aus kosmetischen Gründen 2% abgezogen
		$this->chart_area[2] = $this->chart_area[2] - ($this->chart_area[2] * 0.02);	

		// c) Am oberen Rand wird Platz für die Überschrift geschaffen
		$title_size = $this->TTFStringSize($this->chart_title_size, 0, $this->title_font, $this->chart_title);
		$this->chart_area[1] = $this->chart_area[1] + ($title_size[1] * 2);

		// d) Am linken Rand wird Platz für die Achsenbeschriftung und den Achsentitel gemacht
		// PS: Ab hier stimmen die Bars nicht mehr
		$this->chart_area[0] = $this->chart_area[0] + ($this->y_label_width * 2);

		// e) Unten sollten die X-Achsenbeschriftungen und Werte hinkommen, also raufrücken
		$this->chart_area[3] = $this->chart_area[3] - ($this->x_label_height);
	}

	/***************************************
	* DoColorArrays
	***************************************/

	function DoColorArrays() {

		// bar_color
		foreach($this->bar_color as $col) {
			list($r, $g, $b) = ColorSet($col);
			$barcol = ImageColorAllocate($this->img, $r, $g, $b);
			$this->col_bar_color[] = $barcol;
		}

		// bar_border_color
		foreach($this->bar_border_color as $col) {
			list($r, $g, $b) = ColorSet($col);
			$barbordercol = ImageColorAllocate($this->img, $r, $g, $b);
			$this->col_bar_border_color[] = $barbordercol;
		}

		return true;
				
	}

	/***************************************
	* SetChartType
	***************************************/

	function SetChartType($p_chart_type) {
		$this->chart_type = $p_chart_type;
		return true;	
	}

	/***************************************
	* SetRecordCoord
	***************************************/

	function SetRecordCoord() {

		$hundred = ($this->chart_area[2] - $this->chart_area[0]);
		
		$space = $hundred / ($this->record_count_group * 4);
		$group_width = $space * 3;
		$bar_width = $group_width / $this->record_group;
	
		$this->record_group_space = $space;
		$this->record_bar_width = $bar_width;
	}

	/***************************************
	* ImageCreate
	***************************************/

	function ImageCreate() {
		$this->img = ImageCreate($this->image_width, $this->image_height);
		return true;
	}

	/***************************************
	* SetImageSize
	***************************************/

	function SetImageSize($p_image_width, $p_image_height) {
		$this->image_width = $p_image_width;
		$this->image_height = $p_image_height;
		return true;
	}

	/***************************************
	* SetMaxStringSize
	***************************************/

	function SetMaxStringSize($p_max_string_size) {
		$this->max_string_size = $p_max_string_size;
		return true;
	}

	/***************************************
	* SetNoData
	***************************************/

	function SetNoData($p_no_data) {
		$this->no_data = $p_no_data;
		return true;
	}

	/***************************************
	* SetLineThickness
	***************************************/

	function SetLineThickness($p_line_thickness) {
		$this->line_thickness = $p_line_thickness;
		return true;
	}

	/***************************************
	* SetPointSize
	***************************************/

	function SetPointSize($p_point_size) {
		$this->point_size = (int)$p_point_size;
		if ($this->point_shape == 'diamond' or $this->point_shape == 'triangle') {
			if ($this->point_size % 2 != 0) {
				$this->point_size++;
			}	
		}
		return true;
	}

	/***************************************
	* SetPointShape
	***************************************/

	function SetPointShape($p_point_shape) {
		$this->point_shape = $p_point_shape;
		return true;
	}

	/***************************************
	* SetXLabelHeight
	***************************************/

	function SetXLabelHeight() {
	
		$size = $this->TTFStringSize($this->axis_title_size, 0, $this->font, $this->title_axis_x);
		$beschr = $size[1];

		$string = Str_Repeat('m', $this->max_string_size);
		$size = $this->TTFStringSize($this->axis_font_size, 90, $this->font, $string);
		$data = $size[1];
		$gesamt = $beschr + $data;
		$this->x_label_height = $gesamt;
		return true;
	}

	/***************************************
	* SetYLabelWidth
	***************************************/
		
	function SetYLabelWidth() {
		$number = number_format($this->record_max, 0, ',', '.');
		$size = $this->TTFStringSize($this->axis_font_size, 0, $this->font, $number);


		$this->y_label_width = $size[0];
		return true;
	}

	/***************************************
	* SetBackgroundColor
	***************************************/

	function SetBackgroundColor($p_background_color) {
		if (! isset($this->img)) $this->ImageCreate();
		
		list($r, $g, $b) = ColorSet($p_background_color);
		$this->background_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetChartBackgroundColor
	***************************************/

	function SetChartBackgroundColor($p_chart_background_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_chart_background_color);
		$this->chart_background_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetChartBorderColor
	***************************************/

	function SetChartBorderColor($p_chart_border_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_chart_border_color);
		$this->chart_border_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetTitleFont
	***************************************/

	function SetTitleFont($p_title_font) {
		$this->title_font = $p_title_font;
	}

	/***************************************
	* SetFont
	***************************************/

	function SetFont ($p_font) {
		$this->font = $p_font;
	}

	/***************************************
	* SetChartTitle
	***************************************/

	function SetChartTitle($p_chart_title) {
		if (! isset($this->img)) $this->ImageCreate();

		$this->chart_title = $p_chart_title;
        $posarr = ImageTTFBBox($this->chart_title_size, 0, $this->title_font, $this->chart_title);

        $stringheight = abs($posarr[5]) - abs($posarr[3]);
		return true;
	}	

	/***************************************
	* SetChartTitleSize
	***************************************/

	function SetChartTitleSize($p_chart_title_size) {
		if (! isset($this->img)) $this->ImageCreate();
		$this->chart_title_size = $p_chart_title_size;
		return true;
	}

	/***************************************
	* SetChartTitleColor
	***************************************/

	function SetChartTitleColor($p_chart_title_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_chart_title_color);
		$this->chart_title_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetFontColor
	***************************************/

	function SetFontColor($p_font_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_font_color);
		$this->font_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	} 

	/***************************************
	* SetDataValues
	***************************************/

	function SetDataValues($p_data_values) {
		$this->data_values = $p_data_values;
		return true;
	}

	/***************************************
	* SetBarColor
	***************************************/

	function SetBarColor($p_bar_color) {
		if (! isset($this->img)) $this->ImageCreate();
		//TODO
		list($r, $g, $b) = ColorSet('blue');
		$this->bar_color=$p_bar_color;
		return true;
	}

	/***************************************
	* SetBarBorderColor
	***************************************/

	function SetBarBorderColor ($p_bar_border_color) {
		if (! isset($this->img)) $this->ImageCreate();
		//TODO
		list($r, $g, $b) = ColorSet('white');
		$this->bar_border_color=$p_bar_border_color;
		return true;
	}

	/***************************************
	* SetLegendPosition
	***************************************/

	function SetLegendPosition($p_legend_position) {
		$this->legend_position = $p_legend_position;
		return true;
	}

	/***************************************
	* SetLegend
	***************************************/

	function SetLegend($p_legend) {
		$this->legend = $p_legend;
		return true;
	}

	/***************************************
	* SetAxisFontSize
	***************************************/

	function SetAxisFontSize($p_axis_font_size) {
		$this->axis_font_size = $p_axis_font_size;
		return true;
	}

	/***************************************
	* SetTitleAxisX
	***************************************/

	function SetTitleAxisX ($p_title_axis_x) {
		$this->title_axis_x = $p_title_axis_x;
		return true;
	}

	/***************************************
	* SetTitleAxisY
	***************************************/

	function SetTitleAxisY ($p_title_axis_y) {
		$this->title_axis_y = $p_title_axis_y;
		return true;
	}

	/***************************************
	* SetAxisTitleSize
	***************************************/

	function SetAxisTitleSize ($p_axis_title_size) {
		$this->axis_title_size = $p_axis_title_size;
		return true;
	}

	/***************************************
	* SetTickLength
	***************************************/

	function SetTickLength ($p_tick_length) {
		$this->tick_length = $p_tick_length;
		return true;
	}

	/***************************************
	* SetTickInterval
	***************************************/

	function SetTickInterval ($p_tick_interval) {
		$this->tick_interval = $p_tick_interval;
		return true;
	}

	/***************************************
	* SetGridX
	***************************************/
		
	function SetGridX ($p_grid_x) {
		$this->grid_x = $p_grid_x;
		return true;
	}

	/***************************************
	* SetGridY
	***************************************/

	function SetGridY ($p_grid_y) {
		$this->grid_y = $p_grid_y;
		return true;
	}

	/***************************************
	* SetAxisColor
	***************************************/

	function SetAxisColor ($p_axis_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_axis_color);
		$this->axis_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetGridColor
	***************************************/

	function SetGridColor ($p_grid_color) {
		if (! isset($this->img)) $this->ImageCreate();
		list($r, $g, $b) = ColorSet($p_grid_color);
		$this->grid_color=ImageColorAllocate($this->img, $r, $g, $b);
		return true;
	}

	/***************************************
	* SetShading
	***************************************/

	function SetShading($p_shading) {
		$this->shading = (int)$p_shading;
		return true;
	}
	
	/***************************************
	* DrawTitle
	***************************************/

	function DrawTitle() {
		$init = $this->chart_area[0];
		$posarr = ImageTTFBBox($this->chart_title_size, 0, $this->title_font, $this->chart_title);

		$stringwidth = $posarr[2] - $posarr[0];
		$stringheight = abs($posarr[5]) - abs($posarr[3]);

		$chart_width = $this->chart_area[2] - $this->chart_area[0];

		$xpos = ($chart_width / 2)  - ($stringwidth / 2) + $init;


		$stringypos = $stringheight + ceil(($stringheight * 0.3));
		$stringxpos = ceil($xpos);

		if ($this->shading == 1) {
			list($r, $g, $b) = ColorSet('silver');
			$silver = ImageColorAllocate($this->img, $r, $g, $b);
			ImageTTFText($this->img, $this->chart_title_size, 0, $stringxpos + 1, $stringypos + 1, $silver, $this->title_font, $this->chart_title);
			ImageTTFText($this->img, $this->chart_title_size, 0, $stringxpos + 2, $stringypos + 2, $silver, $this->title_font, $this->chart_title);
		}
		
		ImageTTFText($this->img, $this->chart_title_size, 0, $stringxpos, $stringypos, $this->chart_title_color, $this->title_font, $this->chart_title);

		return true;
	}

	/***************************************
	* DrawBackground
	***************************************/
	
	function DrawBackground() {
		ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_heigth, $this->background_color);
		return true;
	}

	/***************************************
	* DrawXGrid
	***************************************/

	function DrawXGrid() {

		if ($this->grid_x > 0) {
			$height = $this->chart_area[3] - $this->chart_area[1];
			$step = $height / $this->grid_x;

			for ($i=$step; $i < $height; $i=$i+$step) {
				$pos = $i + $this->chart_area[1];	
				ImageLine($this->img, $this->chart_area[0], $pos, $this->chart_area[2], $pos, $this->grid_color);
			}
		}
		return true;
	}

	/***************************************
	* DrawYGrid
	***************************************/

	function DrawYGrid() {
		if ($this->grid_y > 0) {
			$width = $this->chart_area[2] - $this->chart_area[0];
			$step = $width / $this->record_count_group;

			for ($i=$step; $i < $width; $i=$i+$step) {
				$pos = $i + $this->chart_area[0];
				ImageLine($this->img, $pos, $this->chart_area[1], $pos, $this->chart_area[3], $this->grid_color);
			}
		}
		return true;
	}
	
	/***************************************
	* DrawChartBackground
	***************************************/

	function DrawChartBackground() {
		list($x1, $y1, $x2, $y2) = $this->chart_area;
		ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $this->chart_background_color);
		return true;
	}

	/***************************************
	* DrawChartBorder
	***************************************/

	function DrawChartBorder() {
		list($x1, $y1, $x2, $y2) = $this->chart_area;
		ImageRectangle($this->img, $x1, $y1, $x2 - 1, $y2, $this->chart_border_color);
		return true;
	}

	/***************************************
	* DrawTitleAxisX
	***************************************/

	function DrawTitleAxisX() {
	
		$width = $this->chart_area[2] - $this->chart_area[0];
		$string_size = $this->TTFStringSize($this->axis_title_size, 0, $this->font, $this->title_axis_x);
		$string_width = $string_size[0];
	

		$stringxpos = $width / 2;
		$stringypos = $this->image_height - ($this->image_height * 0.03);

		ImageTTFText($this->img, $this->axis_title_size, 0, $stringxpos, $stringypos, $this->font_color, $this->font, $this->title_axis_x);

		return true;
	}

	/***************************************
	* DrawTitleAxisY
	***************************************/

	function DrawTitleAxisY() {

		$posarr = ImageTTFBBox($this->axis_title_size, 90, $this->font, $this->title_axis_y);	
		
		$stringheight = abs($posarr[3]) - abs($posarr[1]);
		$stringwidth = abs($posarr[6]) - abs($posarr[0]);

		$stringypos = $this->chart_area[3] - $this->chart_area[1];
		$stringypos = ($stringypos / 2) + $stringheight;

		$stringxpos = $stringwidth * 2;

		ImageTTFText($this->img, $this->axis_title_size, 90, $stringxpos, $stringypos, $this->font_color, $this->font, $this->title_axis_y);
	
	}

	/***************************************
	* DrawBars
	***************************************/

	function DrawBars() {

		list($r, $g, $b) = ColorSet('gray');
		$shadow = ImageColorAllocate($this->img, $r, $g, $b);

		$chart_height = $this->chart_area[3] - $this->chart_area[1];	
		$chart_width  = $this->chart_area[2] - $this->chart_area[0];

		$abstand = $this->chart_area[0] + $this->record_group_space / 2;

		foreach ($this->data_values as $row) {
			//Print X-Achsen-Beschriftung mit $row[0]
			$colcount = 0;
			$colbarcount = 0;
			$rowcount=0;
			foreach ($row as $v) {
				if ($rowcount > 0) {	
					if ($rowcount == count($row))  $rowcount=0;
					// Draw Bars really! ($v)

					$x1 = $abstand;
					$y1 = $this->chart_area[3] - ($v / ($this->record_max) * $chart_height);
					$x2 = $abstand + $this->record_bar_width;
					$y2 = $this->chart_area[3] - 1;
					
					$x1 = round($x1, 0);
					$x2 = round($x2, 0);
					$y1 = round($y1, 0);
					$y2 = round($y2, 0);

					//print "Wert: $v, ($x1 / $y1), ($x2 / $y2)<br>";
		
					if ($colcount >= count($this->bar_color)) $colcount=0;	
					if ($colbarcount >= count($this->bar_border_color)) $colbarcount=0;
					$barcol = $this->col_bar_color[$colcount];
					$bordercol = $this->col_bar_border_color[$colbarcount];
	
					if ($this->shading == 1) {
						//Schattierung ja/nein
						ImageFilledRectangle($this->img, $x1+1, $y1-1, $x2+1, $y2-1, $shadow);
						ImageFilledRectangle($this->img, $x1+2, $y1-2, $x2+2, $y2-2, $shadow);
					}

					ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $barcol);
					ImageRectangle($this->img, $x1, $y1, $x2, $y2, $bordercol);
				
					$abstand = $abstand + $this->record_bar_width;
					$colcount++;
					$colbarcount++;
				}
				$rowcount++;
			}
			$abstand = $abstand + $this->record_group_space;
		}
	}

	/***************************************
	* DrawPoints
	***************************************/

	function DrawPoints() {

		$width = $this->chart_area[2] - $this->chart_area[0];
		$height = $this->chart_area[3] - $this->chart_area[1];
		$step = $width / $this->record_count_group;
		$corr = $step / 2;
		$xpos = $step;
		$init = $this->chart_area[0];
		$half_point = $this->point_size / 2;

        foreach ($this->data_values as $row) {
            $colcount = 0;
            foreach ($row as $v) {
                if ($v != $row[0]) {
					
					$x1 = $xpos + $init - $corr - $half_point;
					$x2 = $xpos + $init - $corr + $half_point;
                    $y = $this->chart_area[3] - ($v / ($this->record_max) * $height);
					$y1 = $y - $half_point;
					$y2 = $y + $half_point;

					//print "($x1 / $y1), ($x2 / $y2), half_point: $half_point<br>";
                    if ($colcount >= count($this->bar_color)) $colcount=0;
                    $barcol = $this->col_bar_color[$colcount];

					switch ($this->point_shape) {
						case 'rect':
							ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $barcol);
							break;
						case 'circle':
							ImageArc($this->img, $x1 + $half_point, $y1 + $half_point, $this->point_size, $this->point_size, 0, 360, $barcol);
							break;
						case 'diamond':
							$arrpoints = array(
								$x1,			$y1 + $half_point,
								$x1 + $half_point, 	$y1,
								$x2, 			$y1 + $half_point,
								$x1 + $half_point, 	$y2
							);

							ImageFilledPolygon($this->img, $arrpoints, 4, $barcol);
							break;
						case 'triangle':
							$arrpoints = array(
								$x1,                    $y1 + $half_point,
								$x2,                    $y1 + $half_point,
								$x1 + $half_point,      $y2
							);
							ImageFilledPolygon($this->img, $arrpoints, 3, $barcol);
							break;
						case 'dot':
							ImageArc($this->img, $x1 + $half_point, $y1 + $half_point, $this->point_size, $this->point_size, 0, 360, $barcol);
							ImageFillToBorder($this->img, $x1 + $half_point, $y1 + $half_point, $barcol, $barcol);
							break;
						default:
							ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $barcol);
							break;
					}

                    $colcount++;
                }
            }
			$xpos += $step;
        }


	}

	/***************************************
	* DrawLines
	***************************************/

	function DrawLines() {
        $width = $this->chart_area[2] - $this->chart_area[0];
        $height = $this->chart_area[3] - $this->chart_area[1];
        $step = $width / $this->record_count_group;
        $corr = $step / 2;
        $xpos = $step;
        $init = $this->chart_area[0];

		$oldx[0] = 0;
		$oldy[0] = 0;
        foreach ($this->data_values as $row) {
            $colcount = 0;
			$i = 0;
            foreach ($row as $v) {
                if ($v != $row[0]) {

                    $x1 = $xpos + $init - $corr;
                    $x2 = $xpos + $init - $corr + 3;
                    $y1 = $this->chart_area[3] - ($v / ($this->record_max) * $height);
                    $y2 = $y1 + 3;

                    if ($colcount >= count($this->bar_color)) $colcount=0;
                    $barcol = $this->col_bar_color[$colcount];
					
					if ($oldx[$i] > 0) {
						for ($thick = 0; $thick < $this->line_thickness; $thick++) {
							ImageLine($this->img, $x1, $y1 + $thick, $oldx[$i], $oldy[$i] + $thick, $barcol);	
						}
					}
					$oldx[$i] = $x1;
					$oldy[$i] = $y1;
                    $colcount++;
					$i++;
                }
            }
            $xpos += $step;
        }

		return true;
	}

	/***************************************
	* DrawArea
	***************************************/

	function DrawArea() {
		$width = $this->chart_area[2] - $this->chart_area[0];
		$height = $this->chart_area[3] - $this->chart_area[1];
		$step = $width / $this->record_count_group;
		$corr = $step / 2;
		$xpos = $step;
		$init = $this->chart_area[0];

		for ($i = 0; $i < $this->record_group; $i++) {
			$x = $this->chart_area[0] + $corr; 
			$x = round($x, 0);
			$y = $this->chart_area[3];
			$posarr[$i][] =  $x;
			$posarr[$i][] =  $y;
			
		}
	
		foreach ($this->data_values as $row) {
			$arrcounter = 0; 
			foreach ($row as $v) {		
				if ($v != $row[0]) {
					$x = $xpos + $init - $corr;
					$y = $this->chart_area[3] - ($v / ($this->record_max) * $height);
					$x = round($x, 0);
					$y = round($y, 0);
					$posarr[$arrcounter][] = $x;
					$posarr[$arrcounter][] = $y;	
					$arrcounter++;	
				}
			}
			$xpos += $step;
		}

		for ($i = 0; $i < $this->record_group; $i++) {
			$x = $this->chart_area[2] - $corr;
			$y = $this->chart_area[3];
			$posarr[$i][] =  $x;
			$posarr[$i][] =  $y;
		}	

		$colcount=0;
		foreach($posarr as $row) {
			if ($colcount >= count($this->bar_color)) $colcount=0;
			$barcol = $this->col_bar_color[$colcount];
			ImageFilledPolygon($this->img, $row, (count($row)) / 2, $barcol);
			$colcount++;
		}
	
	}

	/***************************************
	* DrawPie
	***************************************/

	function DrawPie() {
		$width = $this->chart_area[2] - $this->chart_area[0];
		$height = $this->chart_area[3] - $this->chart_area[1];
		
		list ($r, $g, $b) = ColorSet('black');
		$black = ImageColorAllocate($this->img, $r, $g, $b);

		$xpos = $this->chart_area[0] + ($width / 2);
		$ypos = $this->chart_area[1] + ($height / 2);
		$durchmesser = (min ($width, $height)) * 0.97;
		$radius = $durchmesser / 2;
		ImageArc($this->img, $xpos, $ypos, $durchmesser, $durchmesser, 0, 360, $black);
		//ImageFillToBorder($this->img, $xpos, $ypos, $black, $black);
		$helpcol = ImageColorAllocate($this->img, 12, 12, 12);
		ImageArc($this->img, $xpos, $ypos, $durchmesser, $durchmesser, 0, 360, $helpcol);

		$i = 0;
		$total = 0;

		foreach ($this->data_values as $row) {
			$colcount = 0;	
			$i = 0;
			foreach ($row as $v) {
				if ($v != $row[0]) {
					// sum up 
					$sumarr[$i] += $v;
					$total += $v;
				}
			$i++;
			}
		}

		$colcount = 0;
		$kreis_start = $this->arc_start;
		$umfang = $radius * 2 * pi();
		$winkel = $kreis_start;
		foreach($sumarr as $val) {
			if ($colcount >= count($this->bar_color)) $colcount=0;
			$prozent = number_format(($val / $total * 100), 1, ',', '.') . '%';
			$val = round ($val / $total * 360);

			$winkel += $val;
			$farbwinkel = $winkel - ($val / 2);

			$val += $kreis_start;

			$barcol = $this->bar_color[$colcount];
			list($r, $g, $b) = ColorSet($barcol);	
			$barcol = ImageColorAllocate($this->img, $r, $g, $b);

			//ImageArc($this->img, $xpos, $ypos, $durchmesser, $durchmesser, $kreis_start, $val, $black);
			ImageArc($this->img, $xpos, $ypos, $durchmesser, $durchmesser, 0, 3560, $black);

			$out_x = $radius * cos(deg2rad($winkel));
			$out_y = - $radius * sin(deg2rad($winkel)); 

			$halbradius = $radius / 2;
			$farb_x = $xpos + ($halbradius * cos(deg2rad($farbwinkel)));
			$farb_y = $ypos + (- $halbradius * sin(deg2rad($farbwinkel)));

			$out_x = $xpos + $out_x;
			$out_y = $ypos + $out_y;

			ImageLine($this->img, $xpos, $ypos, $out_x, $out_y, $black);
			//ImageLine($this->img, $xpos, $ypos, $farb_x, $farb_y, $black);
			ImageFillToBorder($this->img, $farb_x, $farb_y, $black, $barcol);

			ImageTTFText($this->img, $this->axis_font_size, 0, $farb_x, $farb_y, $black, $this->font, $prozent);
			
			$kreis_start = $val;

			$colcount++;		
		}

		//ImageArc($this->img, $xpos, $ypos, $durchmesser, $durchmesser, 0, 360, $black);
	
	}

	/***************************************
	* DrawLegend
	***************************************/

	function DrawLegend() {

		if ($this->legend_position != 0) {

			$width = 0;
			$height = 0;
			$anzahl = 0;
			$abstand = $this->chart_area[0] * 0.1;

			foreach ($this->legend as $row) {
				$size = $this->TTFStringSize($this->axis_font_size, 0, $this->font, $row);		
				if ($size[0] > $width) $width = $size[0];
				if ($size[1] > $height) $height = $size[1];
				$anzahl++;
			}
			
			$legend_height = ($anzahl * $height) + (($anzahl - 1) * ($height / 2)) + ($height * 2);
			$legend_width = 2.5 * $height + $width;

			switch ($this->legend_position) {
				case 1: // links oben
					$x1 = $this->chart_area[0] + $abstand;
					$y1 = $this->chart_area[1] + $abstand;
					break;
				case 2: // rechts oben
					$x1 = $this->chart_area[2] - $abstand - $legend_width;
					$y1 = $this->chart_area[1] + $abstand;
					break;
				case 3: // rechts unten
					$x1 = $this->chart_area[2] - $abstand - $legend_width;
					$y1 = $this->chart_area[3] - $abstand - $legend_height;
					break;
				case 4: // links unten
					$x1 = $this->chart_area[0] + $abstand;
					$y1 = $this->chart_area[3] - $abstand - $legend_height;
					break;
			}

			$x2 = $x1 + $legend_width;
			$y2 = $y1 + $legend_height;
	
			ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $this->background_color);	

			$xpos = $x1 + ($height * 2); // die schaut schon ganz brauchbar aus
			$ypos = $y1 + ($height * 2); // die passt, nimmer aendern!!
	
			$colcount = 0; 
			foreach ($this->legend as $row) {
				if ($colcount >= count($this->bar_color)) $colcount=0;
				$barcol = $this->bar_color[$colcount];
				list($r, $g, $b) = ColorSet($barcol);
				$barcol = ImageColorAllocate($this->img, $r, $g, $b);

				$barbordercol = $this->bar_border_color[$colcount];
				list($r, $g, $b) = ColorSet($barbordercol);
				$barbordercol = ImageColorAllocate($this->img, $r, $g, $b);
				ImageFilledRectangle($this->img, $xpos - ($height * 1.5), $ypos - $height, $xpos - ($height / 2), $ypos, $barcol);
				ImageRectangle($this->img, $xpos - ($height * 1.5), $ypos - $height, $xpos - ($height / 2), $ypos, $barbordercol);

				ImageTTFText($this->img, $this->axis_font_size, 0, $xpos, $ypos, $this->font_color, $this->font, $row);
				$ypos = $ypos + ($height * 1.5);
				$colcount++;
			}
			
			ImageRectangle($this->img, $x1, $y1, $x2, $y2, $this->chart_border_color);
		}
		return true;
	}

	/***************************************
	* DrawXTitles
	***************************************/

	function DrawXTitles() {

		if (StrLen($this->title_axis_x)>0) {
			$string_size = $this->TTFStringSize($this->axis_font_size, 0, $this->font, $this->title_axis_x);
			$string_height = $string_size[1] * 2;
			$ypos = $this->image_height - ($this->image_height * 0.03);
			$ypos -= ($string_height / 0.7);
		} else {
			$ypos = $this->image_height - ($this->image_height * 0.03);
		}

		$left_offset = $this->chart_area[0];
		$width = $this->chart_area[2] - $this->chart_area[0];
		$step = $width / $this->record_count_group;
		$corr = $step/2;
		$string_size = $this->TTFStringSize($this->axis_font_size, 90, $this->font, "AjxÜ");
		$string_width = $string_size[0];
		$corr = ($step/2) - ($string_width/2);

		$i = $step;
		foreach ($this->data_values as $row) {
			$string = SubStr($row[0], 0, $this->max_string_size);
			$xpos = $i + $left_offset - $corr;
			
			
			ImageTTFText($this->img, $this->axis_font_size, 90, $xpos, $ypos, $this->font_color, $this->font, $string);			
			$i += $step;
		}

		return true;
	}

	/***************************************
	* DrawYTitles
	***************************************/

	function DrawYTitles() {
		
		//Höhe der chart_area
		$height = $this->chart_area[3] - $this->chart_area[1];
		$init = $this->chart_area[1];
		$step = $this->record_max / $this->tick_interval;
		$xpos_standard = $this->image_width * 0.03;
		
		$maxnum = number_format($this->record_max, 0, ',', '.');
		$strsize = $this->TTFStringSize($this->axis_font_size, 0, $this->font, $maxnum);
		$halfstring = $strsize[1] / 2;
		$width = $strsize[0];

		for ($i = $this->record_max; $i>=0; $i -= $step) {
			$xpos = $xpos_standard;
			$ypos = ($i / $this->record_max * $height) + $init + $halfstring;
			$number = number_format(($this->record_max - $i), 0, ',', '.');

			$strsize = $this->TTFStringSize($this->axis_font_size, 0, $this->font, $number);

			if ($strsize[0] < $width) {
				$xpos += ($width - $strsize[0]);
			}
			ImageTTFText($this->img, $this->axis_font_size, 0, $xpos, $ypos, $this->axis_color, $this->font, $number);
			
		}
	}

	/***************************************
	* ShowDebug
	***************************************/

	function ShowDebug() {
		global $DEBUG;
		if ($DEBUG == 1) {
			print '<table width="400" border="1" cellpadding="2" cellspacing="0">';
			foreach ($this as $k => $v) {
				print '<tr><td>' . $k . '</td><td>' . $v . '</td></tr>';
			}
			print '</table>';
		}
	}
		
	/***************************************
	* PrintHeader
	***************************************/

	function PrintHeader() {
		global $DEBUG;
		if ($DEBUG != 1) Header('Content-type: image/png');
		return true;
	}

	/***************************************
	* PushImage
	***************************************/

	function PushImage() {
		switch($this->file_format) {
			case 'png':
				ImagePng($this->img);
				break;
			case 'jpg':
				ImageJPEG($this->img);
				break;
			case 'gif':
				ImageGif($this->img);
				break;
		}

		ImageDestroy($this->img);
	}

	/***************************************
	* DrawRundherum
	***************************************/
	
	function DrawRundherum() {
		$this->DrawTitleAxisY();
		$this->DrawTitleAxisX();
		$this->DrawXGrid();
		$this->DrawYGrid();
		$this->DrawXTitles();
		$this->DrawYTitles();
		return true;
	}

	/***************************************
	* DrawNothing
	***************************************/

	function DrawNothing() {
		$abstand = min (($this->image_width * 0.1), ($this->image_height * 0.1));
		$xpos = $abstand;
		$ypos = $this->image_height - $abstand;
		ImageTTFText($this->img, $this->axis_title_size, 0, $xpos, $ypos, $this->axis_color, $this->font, $this->no_data);	
	}

	/***************************************
	* CheckZero
	***************************************/

	function CheckZero() {
		$start = 0;
		foreach($this->data_values as $row) {
			rsort($row);
			if ($row[0] != 0) {
				$start = 1;
			}
		}

		if ($start == 0) {
			return true;
		} else {
			return false;
		}
	}

	/***************************************
	* DrawGraph
	***************************************/

	function DrawGraph() {
		global $DEBUG;

		if (! isset($this->img)) $this->ImageCreate();
		if (! is_array($this->data_values)) {
			$this->DrawBackground();
			$this->DrawNothing();
		} elseif ($this->CheckZero() == true) {
			$this->DrawBackground();
			$this->DrawNothing();
		} else {
			$this->SetChartArea();
			$this->DoColorArrays();
	
			$this->SetRecords();
			$this->SetChartArea();

			$this->SetXLabelHeight();		
			$this->SetChartArea();

			$this->SetYLabelWidth();
			$this->SetChartArea();

			$this->SetRecordCoord();
			$this->SetChartArea();
			$this->SetPointSize($this->point_size);

			$this->DrawBackground();
			$this->DrawTitle();
			$this->DrawChartBackground();

			switch ($this->chart_type) {
				case 'bars':
					$this->DrawRundherum();
					$this->DrawBars();
					$this->DrawChartBorder();
					break;
				case 'lines':
					$this->DrawRundherum();
					$this->DrawLines();
					break;
				case 'area':
					$this->DrawRundherum();
					$this->DrawArea();
					break;
				case 'linepoints':
					$this->DrawRundherum();
					$this->DrawLines();
					$this->DrawPoints();
					break;
				case 'points';
					$this->DrawRundherum();
					$this->DrawPoints();
					break;
				case 'pie':
					$this->DrawPie();
					break;
				default:
					$this->DrawRundherum();
					$this->DrawBars();
					break;
			}

			$this->DrawLegend();

		}
		$this->ShowDebug();
		$this->PrintHeader();
		$this->PushImage();
	}
}	

?>
