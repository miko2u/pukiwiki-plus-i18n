<?php
/*
 * Class produce HTML code to display a'la bar chart.
 * Author: Viatcheslav Ivanov, E-Witness Inc., Canada;
 * mail: ivanov@e-witness.ca, v_iv@hotmail.com;
 * web: www.e-witness.ca; www.coolwater.ca; www.strongpost.net;
 * version: 1.0 /11.22.2002
 *
 * MODIFICATION
 * http://www.phpclasses.org/browse/package/933.html
 * 2006-02-26 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 *
 */
class BARCHART {
    var $before_bar = ''; 		// any number, word ... before bar
    var $after_bar = '';		// any number, word ... after bar
    var $min_point = '';		// minimum value
    var $curr_point = '';		// current value
    var $max_point = '';		// maximum value
    var $img_compound = '';		// image to complete bar
    var $img_bg = '';			// image to complete background
    var $color_compound = '#ff0000';	// color to complete bar
    var $color_bg = '#c5c5c5';		// color to complete background
    var $color_border = '#000000';	// border color, if not specified it doesn't draw border at all
    var $bar_length = '100%';		// specify length of bar in percentage or in pixels

	// INITIALIZATION
	function BARCHART($min_point = 0, $curr_point = 0, $max_point = 0, $init_strbeforebar = '', $init_strafterbar = '') {
		$this->setStrBeforeBar($init_strbeforebar);
		$this->setStrAfterBar($init_strafterbar);
		$this->setMinPoint($min_point);
		$this->setMaxPoint($max_point);
		$this->setCurrPoint($curr_point);
	}

	// SET METHODS
	// set any string before bar
	function setStrBeforeBar($anystring) {
		$this->before_bar = $anystring;
	}
	// set any string after bar
	function setStrAfterBar($anystring) {
		$this->after_bar = $anystring;
	}
	// set minimumm value
	function setMinPoint($point) {
		$this->min_point = $point;
	}
	// set maximum value
	function setMaxPoint($point) {
		$this->max_point = $point;
	}
	// set current value
	function setCurrPoint($point) {
		$this->curr_point = $point;
	}
	// set image to complete bar
	function setIMGCompound($img_url) {
		$this->img_compound = $img_url;
	}
	// set image to complete background
	function setIMGBg($img_url) {
		$this->img_bg = $img_url;
	}
	// set border color ($color in format "#000000" or "black")
	function setColorBorder($color) {
		$this->color_border = $color;
	}
	// set color to complete bar ($color in format "#000000" or "black")
	function setColorCompound($color) {
		$this->color_compound = $color;
	}
	// set color to complete background ($color in format "#000000" or "black")
	function setColorBg($color) {
		$this->color_bg = $color;
	}
	// set length of bar in percentage or in pixels ($length format: "100%" or "200")
	function setLengthBar($length) {
		$this->bar_length = $length;
	}

	// GET METHODS
	// calculate percentage value
	function getPercentage($precision = 1) {
		return ($this->max_point - $this->min_point == 0) ? sprintf("%.".$precision."f", 0) : sprintf("%.".$precision."f", (($this->curr_point/($this->max_point - $this->min_point)) * 100));
	}
	// get bar method
	function getBar() {
		$output = '';
		$fixed_width = (strstr($this->bar_length, '%')) ? '' : $this->bar_length;
		$output.= '<table border="0" cellspacing="0" cellpadding="0"';
		$output.= ($fixed_width) ? ">\n" : ' width="'.$this->bar_length.'">'."\n";
		$output.= "<tr>\n";
		if ($this->before_bar) $output.= '<td>'.$this->before_bar."</td>\n";
		$output.= ($fixed_width) ? "<td>\n" : '<td width="100%">'."\n";
		if ($this->color_border) {
			$output.= '<table border="0" cellspacing="0" cellpadding="1"';
			$output.= ($fixed_width) ? ">\n" : ' width="100%">'."\n";
			$output.= '<tr><td bgcolor="'.$this->color_border.'">'."\n";
		}
		$output.= '<table border="0" cellspacing="0" cellpadding="0"';
		$output.= ($fixed_width) ? ' width="'.$this->bar_length.'">'."\n" : ' width="100%">'."\n";
		$output.= "<tr>\n";
		if ($this->curr_point == $this->min_point) {
			$output.= ($this->img_bg) ? '<td width="100%" background="'.$this->img_bg.'" bgcolor="'.$this->color_bg.'">&nbsp;</td>' : '<td width="100%" bgcolor="'.$this->color_bg.'">&nbsp;</td>';
		} elseif ($this->curr_point >= $this->max_point) {
			$output.= ($this->img_compound) ? '<td width="100%" background="'.$this->img_compound.'" bgcolor="'.$this->color_compound.'">&nbsp;</td>' : '<td width="100%" bgcolor="'.$this->color_compound.'">&nbsp;</td>';
		} else {
			$output.= ($this->img_compound) ? '<td width="'.$this->getPercentage(0).'%" background="'.$this->img_compound.'" bgcolor="'.$this->color_compound.'">&nbsp;</td>' : '<td width="'.$this->getPercentage(0).'%" bgcolor="'.$this->color_compound.'">&nbsp;</td>';
			$output.= ($this->img_bg) ? '<td width="'.(100 - $this->getPercentage(0)).'%" background="'.$this->img_bg.'" bgcolor="'.$this->color_bg.'">&nbsp;</td>' : '<td width="'.(100 - $this->getPercentage(0)).'%" bgcolor="'.$this->color_bg.'">&nbsp;</td>';
		}
		// jo1upk 2006-02-23
		// $output.= "</td></tr>\n";
		$output.= "</table>\n";
		if ($this->color_border) {
			$output.= "</td></tr>\n";
			$output.= "</table>\n";
		}
		$output.= "</td>\n";
		if ($this->after_bar) $output.= '<td>'.$this->after_bar."</td>\n";
		$output.= "</tr>\n";
		$output.= "</table>\n";
		return $output;
	}
}
?>
