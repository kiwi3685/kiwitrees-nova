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
function pageStart($title, $pageTitle = '', $includeTitle = 'y', $subTitle = '', $faq = '') {
	$pageTitle ? $pageTitle = $pageTitle : $pageTitle = $title;
	$faq ? $faq = faqLink($faq) : $faq = '';

	if ($includeTitle == 'n') {
		$pageTitle = '';
	} else {
		$pageTitle = '<h3>' . $pageTitle . '</h3>';
	}

	if ($subTitle !== '') {
		$subTitle = '<h4>' . $subTitle . '</h4>';
	}

	return '
		<div id="' . strtolower($title) . '-page" class="grid-x grid-margin-x">
			<div class="cell titles medium-9">' .
				$pageTitle .
				$subTitle .
			'</div>
			<div class="cell medium-3 text-right">' .
				$faq . 
			'</div>';

	// function pageClose() must be added after content to close this div element
}

/**
 * print end of all pages
 */
 function pageClose() {
 	'</div>';
 }

/**
 * print Family tree select box and label
 *
 */
 function familyTree($gedID, $other=' onchange="tree.submit();"') {

	 return '
		<div class="cell medium-2">
			<label for="ged">' . KT_I18N::translate('Family tree') . '</label>
		</div>
		<div class="cell medium-4">
			<form method="post" action="#" name="tree">
				' . select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, $other) . '
			</form>
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
 * 	 'dna_id_b', // id suffix
 * 	 'INDI', // TYPE
 * 	 '', // autocomplete-ged
 * 	 strip_tags(($person_b ? $person_b->getLifespanName() : '')), // input value
 * 	 '', // placeholder
 * 	 'dna_id_b', // hidden input name
 * 	 $dna_id_b // hidden input value
 *   'required' // Optional required setting
 *   'string' // optional other entry
 * );
 *
 */
function autocompleteHtml($suffix, $type, $tree, $valueInput, $placeHolder, $inputName, $valueHidden, $required = '', $other = '', $validator = '' ) {
	global $iconStyle;

//	$class = (KT_SCRIPT_NAME == 'admin_trees_config.php') ? 'hidden' : '';
	$class = '';

	$html = '
		<div id="select-' . $suffix . '" class="input-group autocomplete_container ' . $class . '">
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
				if ($validator) {$html .= $validator;}
			$html .= '>
			<span class="input-group-label">
				<button id="' . $suffix . '" type="button" class="adminClearAutocomplete autocomplete_icon">
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
 * @string  single-quoted name to display on button like 'Save'
 *
 * @return string[]
 */
function singleButton($title = '') {
   global $iconStyle; ?>

	<?php switch ($title) {
		   	case 'Save':
		   	default:
		   	$title = 'Save';
				echo '
					<button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-save"></i>' .
				   	KT_I18N::translate($title);
				break;
			case 'Show':
				echo '
					<button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-eye"></i>' .
				   	KT_I18N::translate($title);
				break;
			case 'Continue':
			   echo '
					<button class="button" type="submit">
						<i class="' . $iconStyle . ' fa-play"></i>' .
				   	KT_I18N::translate($title);
				break;
			case 'Back':
				echo '
					<button class="button primary" type="button" onclick="history.back()">
						<i class="' . $iconStyle . ' fa-arrow-left"></i>' .
				   	KT_I18N::translate($title);
				break;

			case 'Next':
			case 'Import':
			case 'Merge':
			   echo '
				   <button class="button primary" type="submit">' .
				   	KT_I18N::translate($title) . '
						<i class="' . $iconStyle . ' fa-arrow-right"></i>';
				break;
			case 'Save new order':
			   echo '
				   <button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-bars"></i>' .
				   	KT_I18N::translate($title);
				break;
			} ?>

	   </button>

   <?php
}

/**
 * A standard "Save / Cancel" pair of buttons, used on many pages
 *
 * @return string[]
 */
function submitButtons($extra = '', $onClick = '') {
   global $iconStyle;
   $onClickHtml = '';

   if ($onClick) {
	   $onClickHtml = 'onclick=' . $onClick;
   }

   ?>
   <div class="cell align-left button-group">
	   <button class="button primary" type="submit">
			<i class="<?php echo $iconStyle; ?> fa-save"></i>
			<?php echo KT_I18N::translate('Save'); ?>
	   </button>
	   <button class="button hollow " type="button" <?php echo $extra . ' ' . $onClickHtml; ?>>
		   <i class="<?php echo $iconStyle; ?> fa-xmark"></i>
		   <?php echo KT_I18N::translate('Cancel'); ?>
	   </button>
   </div>

   <?php
}

/**
 * A standard "Show / Reset" pair of buttons, used on report pages
 *
 * @return string[]
 */
function resetButtons($onClick = '') {
   global $iconStyle;

   $onClickHtml = '';
   $submitReset = '<input type="hidden" name="reset" value="1">';

   if($onClick) {
	   $onClickHtml = 'onclick="' . $onClick . ';"';
	   $submitReset = '';
   }

   $buttonHtml = '
	   <div class="cell align-left button-group">
		   <button id="buttonSubmit" class="button primary" type="submit">
			   <i class="' . $iconStyle . ' fa-eye"></i>'
				. KT_I18N::translate('Show') .
		   '</button>
		   <button class="button hollow" type="submit" name="reset" value="reset" ' . $onClickHtml . '>
			   <i class="' . $iconStyle . ' fa-rotate"></i>'
				. KT_I18N::translate('Reset') .
		   '</button>
	   </div>
   ';

   return $buttonHtml;

}

/**
 * Create a link to faqs
 *
 * @return string[]
 */
function faqLink($url) {
	global $iconStyle;
	$link = KT_KIWITREES_URL . $url;
	return '
		<div class="cell">
			<a
				class="current faq_link show-for-large"
				href="' . $link . '"
				target="_blank"
				rel="noopener noreferrer"
				title="' . KT_I18N::translate('View FAQ for this page.') . '"
			>' .
				KT_I18N::translate('View FAQ for this page.') . '
				<i class="' . $iconStyle . ' fa-comments"></i>
			</a>
		</div>
	';
}

// Create a <select> control for a form to choose GEDCOM file
// $name     - the ID for the form element
// $values   - array of value=>display items
// $empty    - if not null, then add an entry ""=>$empty
// $selected - the currently selected item (if any)
// $access    - extra markup for field (e.g. tab key sequence)
function select_ged_control($name, $values, $empty, $selected, $extra='') {
	if (is_null($empty)) {
		$html = '';
	} else {
		if (empty($selected)) {
			$html = '<option value="" selected="selected">' . htmlspecialchars((string) $empty) . '</option>';
		} else {
			$html = '<option value="">' . htmlspecialchars((string) $empty) . '</option>';
		}
	}
	// A completely empty list would be invalid, and break various things
	if (empty($values) && empty($html)) {
		$html = '<option value=""></option>';
	}
	foreach ($values as $key=>$value) {
		if (userGedcomAdmin(KT_USER_ID, $key)) {
			if ((string)$key === (string)$selected) { // Because "0" != ""
				$html .= '<option value="' . htmlspecialchars((string) $key) . '" selected="selected" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
			} else {
				$html .= '<option value="' . htmlspecialchars((string) $key) . '" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
			}
		}
	}

	$element_id = $name . '-' . (int)(microtime(true)*1000000);

	return '<select id="' . $element_id.'" name="' . $name . '" ' . $extra .'>' . $html . '</select>';
}

function loadingImage() {
	global $iconStyle;

	return '
	<div class="cell loading-image">
		<div class="fa-2x">
		  <i class="' . $iconStyle . ' fa-spinner fa-spin-pulse"></i>
		</div>
	</div>';
}

/**
 * Print links to related admin pages
 *
 * //@param string $title name of page
 */
function relatedPages($links, $self = '') {
	global $iconStyle;

	// remove summary page and links to self
	foreach ($links as $key => $item) {
	    if (strstr($key, 'admin_summary_') || $key = $self) {
	        unset($links[$key]);
		}
	}

	$html =  '
		<div class="grid-x relatedPages show-for-medium">
			<div class="cell text-right">
				<label>' .
					KT_I18N::translate('Related pages') . '
				</label>';

				foreach ($links as $link => $title) {
						$html .= '
							<a href="' . $link. '" class="button small large-down-expanded">
							' . $title . '
						</a>';
				}

			$html .= '</div>
		</div>
	';

	return $html;

}
