<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

/**
 * print start of all pages
 *
 * @param string $title name of page
 */
function pageStart($title, $pageTitle = '', $includeTitle = 'y', $subTitle = '') {
	$pageTitle ? $pageTitle = $pageTitle : $pageTitle = $title;

	if ($includeTitle == 'n') {
		$pageTitle = '';
	} else {
		$pageTitle = '<h3>' . $pageTitle . '</h3>';
	}

	if ($subTitle !== '') {
		$subTitle = '<h4 class="hide-for-print">' . $subTitle . '</h4>';
	}
	return '
		<div id="' . strtolower($title) . '-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">' .
				$pageTitle . $subTitle;

	// function pageClose() must be added after content to close this div element
}

/**
 * print end of all pages
 *
 */
function pageClose() {
	'</div>
		</div>
	';
}

/**
 * Provides consistent structure of autocomplete elements
 *
 * @param string $suffix :  variable used as suffix on element IDs
 * @param string $type : variable (uppercase) to specifiy autocomplete type (INDI, FAM, SOUR, etc)
 * @param string $tree :  variable used with  'autocomplete-ged-' in some cases
 * @param string $valueInput :  variable displayed in visible input field
 * @param string $valueHidden :  variable used in hidden input field
 * @param string $placeHolder : variable used as placeholder in visible input field
 *
 * Returns :  $html
 *
 * Example:
 *  <?php echo autocompleteHtml(
 * 	 'dna_id_b', // id
 * 	 'INDI', // TYPE
 * 	 '', // autocomplete-ged
 * 	 strip_tags(($person_b ? $person_b->getLifespanName() : '')), // input value
 * 	 '', // placeholder
 * 	 'dna_id_b', // hidden input name
 * 	 $dna_id_b // hidden input value
 * ); ?>
 *
 */
 function autocompleteHtml($suffix, $type, $tree, $valueInput, $placeHolder, $inputName, $valueHidden, $required = '', $other = '' ) {
 	global $iconStyle;
 	$html = '
 		<div class="input-group autocomplete_container">
 			<input
 				id="autocompleteInput-' . $suffix . '"
 				data-autocomplete-type="' . $type . '"';
 				if ($tree) {$html .= 'data-autocomplete-ged="' . $tree . '"';}
 				$html .= '
 				type="text"
 				value="' . $valueInput . '"';
 				if ($placeHolder) {$html .= 'placeholder="' . $placeHolder . '"';}
				if ($required) {$html .= ' required ';}
 				if ($other) {$html .= $other;}
			$html .= '>
 			<input
 				type="hidden"
 				name="' . $inputName . '"
 				id="selectedValue-' . $suffix . '"';
				if ($valueHidden) {$html .= 'value="' . $valueHidden . '"';}
			$html .= '>
 			<span class="input-group-label">
 				<button id="' . $suffix . '" class="clearAutocomplete autocomplete_icon">
 					<i class="' . $iconStyle . ' fa-xmark"></i>
 				</button>
 			</span>
 		</div>
 	';

 	return $html;

 }

 /**
  * A basic "Show" single submit buttons
  *
  * @return string[]
  */
 function singleButton() {
    global $iconStyle;

    $buttonHtml = '
 	   <div class="cell align-left button-group">
 		   <button class="button primary" type="submit">
 			   <i class="' . $iconStyle . ' fa-save"></i>'
 				. KT_I18N::translate('Save') .
 		   '</button>
 	   </div>
    ';

    return $buttonHtml;

 }

 /**
  * A stadard "Save / Cancel" pair of buttons, used on many pages
  *
  * @return string[]
  */
  function submitButtons($onClick = '') {
     global $iconStyle;

     if($onClick) {
  	   $onClickHtml = 'onclick="' . $onClick . ';"';
   } else {
	   $onClickHtml = 'onclick="window.close();"';
   }

 	$buttonHtml = '
 		<div class="cell align-left button-group">
 			<button class="button primary" type="submit">
 				<i class="' . $iconStyle . ' fa-save"></i>'
 				 . KT_I18N::translate('Save') .
 			'</button>
			<button class="button hollow" type="button" ' . $onClickHtml . '>
 				<i class="' . $iconStyle . ' fa-xmark"></i>'
 				 . KT_I18N::translate('Cancel') .
 			'</button>
 		</div>
 	';

 	return $buttonHtml;

 }

 /**
  * A standard "Show / Reset" pair of buttons, used on report pages
  *
  * @return string[]
  */
 function resetButtons() {
 	global $iconStyle;

 	$buttonHtml = '
 		<div class="cell align-left button-group">
 			<button class="button primary" type="submit">
 				<i class="' . $iconStyle . ' fa-eye"></i>'
 				 . KT_I18N::translate('Show') .
 			'</button>
 			<button class="button hollow" type="submit" name="reset" value="reset">
 				<i class="' . $iconStyle . ' fa-rotate"></i>'
 				 . KT_I18N::translate('Reset') .
 			'</button>
 		</div>
 	';

 	return $buttonHtml;

 }

 /**
  * Google map links to admin pages
  *
  * $parent (array) - id of parent - used where links connect to specific places
  *	$coords (string) - latlng settings - used where links connect to specific places
  * $gedcom (string) - Only required if link to admin_trees_places.php used
  * $update (bool) -
  *
  * @return string[]
  */
 function googlemap_links($parent = array(), $coords = '', $gedcom = KT_GED_ID, $update = false) {
 	global $iconStyle;

	$preferences_url	= 'module.php?mod=googlemap&amp;mod_action=admin_preferences';
	$placecheck_url		= 'module.php?mod=googlemap&amp;mod_action=admin_placecheck&amp;gedcom_id=1&amp;matching=1';
	$adminplaces_url	= 'module.php?mod=googlemap&amp;mod_action=admin_places&amp;parent=' . $coords . '&status=all';
	$update_places_url	= 'admin_trees_places.php?ged=' . $gedcom;

	$class1 = $class2 = $class3 = ' small-4';
	if ($update) {
		$class1 = ' small-4 text-left';
		$class2 = ' small-3 text-left';
		$class3 = ' small-2 text-center';
		$class4 = ' small-3 text-right';
	}

/*
	if ($parent && isset($parent[0]) ) {
		$placecheck_url .= '&amp;country=' . $parent[0];
		if (isset($parent[1])) {
			$placecheck_url .= '&amp;state=' . $parent[1];
		}
		$update_places_url = 'admin_trees_places.php?ged=' . KT_GEDCOM . '&amp;search=' . $parent[0];
	}
	$adminplaces_url = 'module.php?mod=googlemap&amp;mod_action=admin_places';
	if ($latlng && isset($latlng['pl_id'])) {
		$adminplaces_url .= '&amp;parent='.$latlng['pl_id'];
	}
*/

	if ($parent && isset($parent[0])) {
		$placecheck_url .= '&amp;country=' . $parent[0];
		if (isset($parent[1])) {
			$placecheck_url .= '&amp;state=' . $parent[1];
		}
		$update_places_url .= '&amp;search=' . implode(', ', array_reverse($parent));
	}

	$html = '<div class="grid-x">';

		$html .= '
				<div class="cell' . $class1 . '">
					<a href="' . $preferences_url . '">
						<i class="' . $iconStyle . ' fa-globe"></i>
						' . KT_I18N::translate('Google Maps™ preferences') . '
					</a>
				</div>
				<div class="cell' . $class2 . '">
					<a href="' . $adminplaces_url . '">
						<i class="' . $iconStyle . ' fa-map-pin"></i>
						' . KT_I18N::translate('Geographic data') . '
					</a>
				</div>
				<div class="cell' . $class3 . '">
					<a href="' . $placecheck_url . '">
						<i class="' . $iconStyle . ' fa-location-crosshairs"></i>
						' . KT_I18N::translate('Place Check') . '
					</a>
				</div>
			';

			if ($update) {
				$html .= '
					<div class="cell' . $class4 . '">
						<a href="' . $update_places_url . '">
							<i class="' . $iconStyle . ' fa-pen-to-square"></i>
							' . KT_I18N::translate('Update place names') . '
						</a>
					</div>
				';
			}

	$html .= '</div>';

	return $html;
}
