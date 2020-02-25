<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Theme name - this needs double quotes, as file is scanned/parsed by script
$theme_name		= "kaponga";
$theme_display	= "Kaponga"; // Māori language text - not translatable


$headerfile = KT_THEME_DIR . 'header.php';

/**
 * Tell menu constructor they fontawesome style icons required
 * @ iconStyle type	= string
 * @ fas	= solid
 * @ far	= regular
 * @ fal	 light
 */
$iconStyle	= 'fas'; // fontawesome style set
//$iconStyle	= 'far';

//-- pedigree chart variables
$bwidth			= 270;	// width of boxes on pedigree chart
$bheight		= 90;	// height of boxes on pedigree chart
$baseyoffset	= 10;	// position the entire pedigree tree relative to the top of the page
$basexoffset	= 10;	// position the entire pedigree tree relative to the left of the page
$bxspacing		= 20;	// horizontal spacing between boxes on the pedigree chart
$byspacing		= 30;	// vertical spacing between boxes on the pedigree chart
$linewidth		= 1.5;	// width of joining lines
$shadowcolor	= "";	// shadow color for joining lines
$shadowblur		= 0;	// shadow blur for joining lines
$shadowoffsetX	= 0;	// shadowOffsetX for joining lines
$shadowoffsetY	= 0;	// shadowOffsetY for joining lines

// descendancy - relationship chart variables
$Dbaseyoffset	= 20;	// position the entire descendancy tree relative to the top of the page
$Dbasexoffset	= 20;	// position the entire descendancy tree relative to the left of the page
$Dbxspacing		= 5;	// horizontal spacing between boxes
$Dbyspacing		= 20;	// vertical spacing between boxes
$Dbwidth		= 270;	// width of DIV layer boxes
$Dbheight		= 90;	// height of DIV layer boxes
$Dindent		= 15;	// width to indent descendancy boxes
$Darrowwidth	= 30;	// additional width to include for the up arrows

// -- Dimensions for compact version of chart displays
$cbwidth	 = 240;
$cbheight	 = 60;

// --  The largest possible area for charts is 300,000 pixels, so the maximum height or width is 1000 pixels
$KT_STATS_S_CHART_X = 550;
$KT_STATS_S_CHART_Y = 200;
$KT_STATS_L_CHART_X = 900;
// --  For map charts, the maximum size is 440 pixels wide by 220 pixels high
$KT_STATS_MAP_X = 440;
$KT_STATS_MAP_Y = 220;

$KT_STATS_CHART_COLOR1 = "ffffff";
$KT_STATS_CHART_COLOR2 = "9ca3d4";
$KT_STATS_CHART_COLOR3 = "e5e6ef";

// -- fanchart variables
$fanChart = array(
	'color'   =>'#555555',
	'bgColor' =>'#e3e3e3',
	'bgMColor'=>'#b1cff0',
	'bgFColor'=>'#e9daf1'
);

if (file_exists(KT_THEME_URL . 'mytheme.php')) {
	include 'mytheme.php';
}
