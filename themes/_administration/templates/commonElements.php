<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>
 */
if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');

	exit;
}

/**
 * print start of all pages.
 *
 * @param string $title (name of page)
 * @param mixed  $pageTitle
 * @param mixed  $includeTitle
 * @param mixed  $subTitle
 * @param mixed  $faq
 */
function pageStart($title, $pageTitle = '', $includeTitle = 'y', $subTitle = '', $faq = '')
{
	$pageTitle ? $pageTitle = $pageTitle : $pageTitle = $title;
	$faq ? $faq = faqLink($faq) : $faq = '';

	if ('n' == $includeTitle) {
		$pageTitle = '';
	} else {
		$pageTitle = '<h3>' . $pageTitle . '</h3>';
	}

	if ('' !== $subTitle) {
		$subTitle = '<h5>' . $subTitle . '</h5>';
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
 * print end of all pages.
 */
function pageClose()
{
}

/**
 * print Family tree select box and label.
 *
 * @param mixed $gedID
 * @param mixed $other
 */
function familyTree($gedID, $other = ' onchange="tree.submit();"')
{
	return '
		<div class="cell medium-2">
			<label for="gedID">' . KT_I18N::translate('Family tree') . '</label>
		</div>
		<div class="cell medium-4">
			<form method="post" action="#" name="tree">
				' . select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, $other) . '
			</form>
		</div>
	';
}

/**
 * Provides consistent structure of autocomplete elements.
 *
 * @param string $suffix      :  variable used as suffix on element IDs
 * @param string $type        : variable (uppercase) to specifiy autocomplete type (INDI, FAM, SOUR, etc)
 * @param string $tree        :  variable used with  'autocomplete-ged-' in some cases
 * @param string $valueInput  :  variable displayed in visible input field
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
 * @param mixed $inputName
 * @param mixed $required
 * @param mixed $other
 * @param mixed $validator
 */
function autocompleteHtml($suffix, $type, $tree, $valueInput, $placeHolder, $inputName, $valueHidden, $required = '', $other = '', $validator = '')
{
	global $iconStyle;

//	$class = (KT_SCRIPT_NAME == 'admin_trees_config.php') ? 'hidden' : '';
	$class = '';

	$html = '
		<div id="select-' . $suffix . '" class="input-group autocomplete_container ' . $class . '">
			<input
				id="autocompleteInput-' . $suffix . '"
				data-autocomplete-type="' . $type . '"';
	if ($tree) {
		$html .= 'data-autocomplete-ged="' . $tree . '"';
	}
	$html .= '
				type="text"
				value="' . $valueInput . '"';
	if ($placeHolder) {
		$html .= 'placeholder="' . $placeHolder . '"';
	}
	if ($required) {
		$html .= ' required ';
	}
	if ($other) {
		$html .= $other;
	}
	$html .= '>
			<input
				type="hidden"
				name="' . $inputName . '"
				id="selectedValue-' . $suffix . '"';
	if ($valueHidden) {
		$html .= 'value="' . $valueHidden . '"';
	}
	if ($validator) {
		$html .= $validator;
	}
	$html .= '>
			<span class="input-group-label">
				<button id="' . $suffix . '" type="button" class="clearAutocomplete autocomplete_icon" title="' . KT_I18N::translate('Delete autocomplete entry') .'" data-tooltip data-position="top" data-alignment="center">
					<i class="' . $iconStyle . ' fa-xmark"></i>
				</button>
			</span>
		</div>
	';

	return $html;
}

/**
 * A basic "Show" single submit buttons.
 *
 * @string  single-quoted name to display on button like 'Save'
 *
 * @param mixed $title
 * @param mixed $note
 *
 * @return string[]
 */
function singleButton($title = '', $note = '', $gedID = '', $moduleName = '') {
	global $iconStyle;

	if ($note) {
		switch ($note) {
			case '1':
				$noteText = KT_I18n::translate('
					Note: This save button only records changes to the visisble modules above.
					Modules on other pages of the table are not saved.
				');

				break;
		}
	} ?>

	<?php switch ($title) {
		case 'Save':
		default:
			$title = 'Save';
			echo '
				<div class="cell medium-2 buttonDiv">
					<button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-save"></i>' .
						KT_I18N::translate($title) . '
					</button>
				</div>
			';
			break;
		case 'Show':
			echo '
				<div class="cell medium-2">
					<button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-eye"></i>' .
						KT_I18N::translate($title) . '
					</button>
				</div>
			';
			break;
		case 'Continue':
			echo '
				<div class="cell medium-2">
					<button class="button" type="submit">
						<i class="' . $iconStyle . ' fa-play"></i>' .
						KT_I18N::translate($title) . '
					</button>
				</div>
			';
			break;
		case 'Back':
			echo '
				<div class="cell medium-2">
					<button class="button primary" type="button" onclick="history.back()">
						<i class="' . $iconStyle . ' fa-arrow-left"></i>' .
						KT_I18N::translate($title) . '
					</button>
				</div>
			';
			break;
		case 'Next':
		case 'Import':
		case 'Merge':
			echo '
				<div class="cell medium-2">
					<button class="button primary" type="submit">' .
						KT_I18N::translate($title) . '
						<i class="' . $iconStyle . ' fa-arrow-right"></i>
					</button>
				</div>
			';
			break;
		case 'Save new order':
			echo '
				<div class="cell small-6 medium-2">
					<button class="button primary" type="submit">
						<i class="' . $iconStyle . ' fa-bars"></i>' .
						KT_I18N::translate($title) . '
					</button>
				</div>
			';
			break;
		case 'Add another item':
			echo '
				<div class="cell small-6 medium-2">
					<a class="button primary" href="module.php?mod=' . $moduleName . '&amp;mod_action=admin_add&amp;gedID=' . $gedID . '">
						<i class="' . $iconStyle . ' fa-plus"></i>' .
						KT_I18N::translate($title) . '
					</a>
				</div>
			';
			break;
	} ?>

   <?php if ($note) { ?>
		<div class="cell medium-9 callout warning">
			<?php echo $noteText; ?>
		</div>
   <?php }

}

/**
 * A standard "Save / Cancel" pair of buttons, used on many pages.
 *
 * @param mixed $extra
 * @param mixed $onClick
 *
 * @return string[]
 */
function submitButtons($extra = '', $onClick = '')
{
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
 * A standard "Show / Reset" pair of buttons, used on report pages.
 *
 * @param mixed $firstButton
 * @param mixed $onClick
 *
 * @return string[]
 */
function resetButtons($firstButton = 'Show', $onClick = '')
{
	global $iconStyle;

	$onClickHtml = '';
	$submitReset = '<input type="hidden" name="reset" value="1">';

	if ($onClick) {
		$onClickHtml = 'onclick="' . $onClick . ';"';
		$submitReset = '';
	}

	$buttonHtml = '
	   <div class="cell align-left button-group">
		   <button id="buttonSubmit" class="button primary" type="submit">
			   <i class="' . $iconStyle . ' fa-eye"></i>'
				. KT_I18N::translate($firstButton) .
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
 * Create a link to faqs.
 *
 * @param mixed $url
 *
 * @return string[]
 */
function faqLink($url)
{
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
function select_ged_control($name, $values, $empty, $selected, $extra = '')
{
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
	foreach ($values as $key => $value) {
		if (userGedcomAdmin(KT_USER_ID, $key)) {
			if ((string) $key === (string) $selected) { // Because "0" != ""
				$html .= '<option value="' . htmlspecialchars((string) $key) . '" selected="selected" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
			} else {
				$html .= '<option value="' . htmlspecialchars((string) $key) . '" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
			}
		}
	}

	$element_id = $name . '-' . (int) (microtime(true) * 1000000);

	return '<select id="' . $element_id . '" name="' . $name . '" ' . $extra . '>' . $html . '</select>';
}

function loadingImage()
{
	global $iconStyle;

	return '
	<div class="cell loading-image">
		<div class="fa-2x">
		  <i class="' . $iconStyle . ' fa-spinner fa-spin-pulse"></i>
		</div>
	</div>';
}

/**
 * Print links to related admin pages.
 *
 * //@param string $title name of page
 *
 * @param mixed $links
 * @param mixed $self
 */
function relatedPages($links, $self = '')
{
	global $iconStyle;

	// remove summary page and links to self
	foreach ($links as $key => $item) {
		if (strstr($key, 'admin_summary_') || $key = $self) {
			unset($links[$key]);
		}
	} ?>

	<div class="grid-x relatedPages show-for-large">
		<div class="cell text-right">
			<label>
				<?php echo KT_I18N::translate('Related pages'); ?>
			</label>

			<?php foreach ($links as $link => $title) { ?>
				<a href="<?php echo $link; ?>" class="button small large-down-expanded">
					<?php echo $title; ?>
				</a>
			<?php } ?>
		</div>
	</div>

	<div class="grid-x relatedPages hide-for-large">
		<div class="cell">
			<label>
				<?php echo KT_I18N::translate('Related pages'); ?>
			</label>
			<?php foreach ($links as $link => $title) { ?>
				<a href="<?php echo $link; ?>" class="button tiny hollow primary">
					<?php echo $title; ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php
}

/**
 * Summary page cards.
 *
 * @param string $link		// href link to summary page
 * @param string $title		// title of the module linked to
 * @param string $user		// type and color of usr icon (alert, warning)
 * @param string $tooltip	// text displyed on hover on user icon
 * @param string $descr		// text description of the module linked to, when an image is not used
 * @param string $image		// an image used in place of the descr (description)
 * @param int	 $x			// unique number applied to images used as description for cliable link
 */
function AdminSummaryCard($link, $title, $user, $tooltip, $descr, $image = '', $x = 0)
{
	global $iconStyle;

	$dropdownID = 'dropdownID' . (int) (microtime(true) * 1000000); ?>

	<div class="card cell small-6 medium-3">
		<div class="card-divider text-center medium-text-left">
			<a href="<?php echo $link; ?>">
				<?php echo $title; ?>
			</a>
			<span
				class="show-for-small-only info"
				data-position="top"
				data-alignment="right"
				data-toggle="<?php echo $dropdownID; ?>"
			>
					<i class ="<?php echo $iconStyle; ?> fa-circle-info"></i>
			</span>
			<span
				class="show-for-medium <?php echo $user; ?>"
				data-tooltip title="<?php echo $tooltip; ?>"
				data-position="top"
				data-alignment="right"
			>
					<i class ="<?php echo $iconStyle; ?> fa-user"></i>
			</span>
		</div>
		<div class="card-section show-for-medium">
			<?php if (!$image || 'admin_modules.php' == $link) { ?>
				<p><?php echo $descr; ?></p>
			<?php } else {
				echo $image;
			} ?>
		</div>
	</div>

	<!-- hidden reveal - main image -->
	<?php if ($image && $x) { ?>
		<div class="reveal" id="moduleImage<?php echo $x; ?>" data-reveal>
			<h6 class="text-center">
				<?php echo $title; ?>
				<button class="close-button" aria-label="Dismiss image" type="button" data-close>
					<span aria-hidden="true">
						<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					</span>
				</button>
			</h6>
			<?php echo $image; ?>
		</div>
	<?php } ?>

	<!-- hidden drop down - description on small devices -->
	<div class="dropdown-pane" id="<?php echo $dropdownID; ?>" data-dropdown data-close-on-click=true>
		<?php echo $descr; ?>
	</div>
	<?php

}

/**
 * Multi select input

 * @param var $new New stored value
 * @param var $old Old stored value, if it exists
 * @param var $gedID The index of the relevant gedcom file
 *
 */
 function multiSelect($title, $help, $new, $old, $gedID, $tags) {
 	?>

	<a href="#" class="accordion-title"><?php echo $title; ?></a>
	<div class="cell callout info-help">
		<?php echo $help; ?>
	</div>
	<div class="accordion-content" data-tab-content>
		<div class="cell">
	 		<select id="<?php echo $new; ?>" placeholder="<?php echo KT_I18N::translate('Click here to edit selection ...'); ?>" multiple class="tom-select" name="<?php echo $new; ?>[]">
				<?php $allIndiTags = explode(',', get_gedcom_setting($gedID, $old));
				foreach (KT_Gedcom_Tag::getPicklistFacts($tags) as $factId => $factName) {
			 		$selected = in_array($factId, $allIndiTags) ? ' selected=selected ' : ' ';
			 		echo '<option' . $selected . 'value="' . $factId . '">' . $factName . '&nbsp;(' . $factId . ')&nbsp;</option>';
				} ?>
	 		</select>
		</div>
	</div>
	 <?php

 }

 /**
  * A list of known surname traditions, with their descriptions.
  *
  * @return string[]
  */
 function surnameDescriptions()
 {
	 return [
		 'paternal' => KT_I18N::translate_c('Surname tradition', 'Paternal') .
			 ' - ' . /* I18N: In the paternal surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.') .
			 ' ' . /* I18N: In the paternal surname tradition, ... */ KT_I18N::translate('Wives take their husband’s surname.'),
		 /* I18N: A system where children take their father’s surname */ 'Patrilineal' => KT_I18N::translate('Patrilineal') .
			 ' - ' . /* I18N: In the patrilineal surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.'),
		 /* I18N: A system where children take their mother’s surname */ 'Matrilineal' => KT_I18N::translate('Matrilineal') .
			 ' - ' . /* I18N: In the matrilineal surname tradition, ... */ KT_I18N::translate('Children take their mother’s surname.'),
		 'spanish' => KT_I18N::translate_c('Surname tradition', 'Spanish') .
			 ' - ' . /* I18N: In the Spanish surname tradition, ... */ KT_I18N::translate('Children take one surname from the father and one surname from the mother.'),
		 'portuguese' => KT_I18N::translate_c('Surname tradition', 'Portuguese') .
			 ' - ' . /* I18N: In the Portuguese surname tradition, ... */ KT_I18N::translate('Children take one surname from the mother and one surname from the father.'),
		 'icelandic' => KT_I18N::translate_c('Surname tradition', 'Icelandic') .
			 ' - ' . /* I18N: In the Icelandic surname tradition, ... */ KT_I18N::translate('Children take a patronym instead of a surname.'),
		 'polish' => KT_I18N::translate_c('Surname tradition', 'Polish') .
			 ' - ' . /* I18N: In the Polish surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.') .
			 ' ' . /* I18N: In the Polish surname tradition, ... */ KT_I18N::translate('Wives take their husband’s surname.') .
			 ' ' . /* I18N: In the Polish surname tradition, ... */ KT_I18N::translate('Surnames are inflected to indicate an individual’s gender.'),
		 'lithuanian' => KT_I18N::translate_c('Surname tradition', 'Lithuanian') .
			 ' - ' . /* I18N: In the Lithuanian surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.') .
			 ' ' . /* I18N: In the Lithuanian surname tradition, ... */ KT_I18N::translate('Wives take their husband’s surname.') .
			 ' ' . /* I18N: In the Lithuanian surname tradition, ... */ KT_I18N::translate('Surnames are inflected to indicate an individual’s gender and marital status.'),
		 'none' => KT_I18N::translate_c('Surname tradition', 'None'),
	 ];
 }

function verticalRadioSwitch ($name, $values, $selected, $extra = '', $activeText = 'Yes', $inactiveText = 'No', $size = '') {
	$html = '<div class="grid-x">';

		foreach ($values as $key => $value) {
			$uniqueID = $name . (int) (microtime(true) * 1000000);
			$html .= '
				<div class="switch ' . $size . ' cell medium-1">
					<input class="switch-input" id="' . $uniqueID . '" type="radio" name="' . $name . '" value="' . htmlspecialchars((string) $key) . '" ';
						if ((string) $key === (string) $selected) {
							$html .= ' checked';
						}
						if ($extra) {
							$html .= ' ' . $extra;
						}
					$html .= '>' . '
					<label class="switch-paddle" for="' . $uniqueID . '">
						<span class="show-for-sr">' . $value . '</span>
						<span class="switch-active" aria-hidden="true">' . KT_I18N::translate($activeText) . '</span>
						<span class="switch-inactive" aria-hidden="true">' . KT_I18N::translate($inactiveText) . '</span>
					</label>
				</div>
				<div class="cell medium-11">
					<label class="KT_switch_label middle">' . $value . '</label>
				</div>
			';
		}
	$html .= '</div>';

	return $html;

}

/**
 *print an element with a tool-tip or hint
 *
 * @param string $element		name of the element used
 * @param string $otherClass	extra classes other than the hint components
 * @param string $otherTags		other element tags not part of hint code
 * @param string $hint			the text displayed in the hint pop-up
 * @param string $text			the hint displayed within the element
 */
function hintElement($element = '', $otherClass = '', $otherTags = '', $hint = '', $text = '')
{
	$html = '
		<' . $element . '
			class="hint--top hint--medium hint--no-animate hint--rounded ' . $otherClass . '"
			aria-label="' . $hint . '" ' .
			$otherTags . '
		>' .
			$text . '
		</' . $element . '>
	';

	return $html;

}
