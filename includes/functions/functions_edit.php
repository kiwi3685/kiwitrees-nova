<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

//require_once KT_ROOT . 'includes/functions/functions_import.php';

// Create an edit control for inline editing using jeditable
function edit_field_inline($name, $value, $controller = null)
{
	$html = '<span class="editable" id="' . $name . '">' . KT_Filter::escapeHtml($value) . '</span>';
	$js = 'jQuery("#' . $name . '").editable("' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'save.php", {tooltip: " ' . KT_I18N::translate('click to edit') . '", submitdata: {csrf: KT_CSRF_TOKEN}, submit:"&nbsp;&nbsp;' . /* I18N: button label */ KT_I18N::translate('Save') . '&nbsp;&nbsp;", style:"inherit", placeholder: "' . KT_I18N::translate('click to edit') . '"});';

	if ($controller) {
		$controller->addInlineJavascript($js);

		return $html;
	}
	// For AJAX callbacks
	return $html . '<script>' . $js . '</script>';
}

// Create a text area for inline editing using jeditable
function edit_text_inline($name, $value, $controller = null)
{
	$html = '<span class="editable" style="white-space:pre-wrap;" id="' . $name . '">' . KT_Filter::escapeHtml($value) . '</span>';
	$js = 'jQuery("#' . $name . '").editable("' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'save.php", {tooltip: " ' . KT_I18N::translate('click to edit') . '", submitdata: {csrf: KT_CSRF_TOKEN}, submit:"&nbsp;&nbsp;' . KT_I18N::translate('Save') . '&nbsp;&nbsp;", style:"inherit", placeholder: "' . KT_I18N::translate('click to edit') . '", type: "textarea", rows:4, cols:60 });';

	if ($controller) {
		$controller->addInlineJavascript($js);

		return $html;
	}
	// For AJAX callbacks
	return $html . '<script>' . $js . '</script>';
}

/**
 * Create a <select> control for a form
 * $name     - the ID for the form element
 * $values   - array of value=>display items
 * $empty    - if not null, then add an entry ""=>$empty
 * $selected - the currently selected item (if any)
 * $extra    - extra markup for field (e.g. tab key sequence or onclick())
 */
function select_edit_control($name, $values, $empty, $selected = '', $extra = '')
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
	if (empty($values) && is_null($empty)) {
		$html = '<option value=""></option>';
	}
	foreach ($values as $key => $value) {
		if ((string) $key === (string) $selected) { // Because "0" != ""
			$html .= '<option value="' . htmlspecialchars((string) $key) . '" selected="selected" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
		} else {
			$html .= '<option value="' . htmlspecialchars((string) $key) . '" dir="auto">' . htmlspecialchars((string) $value) . '</option>';
		}
	}

	$element_id = $name . '-' . (int) (microtime(true) * 1000000);

	return '<select id="' . $element_id . '" name="' . $name . '" ' . $extra . '>' . $html . '</select>';
}

// An inline-editing version of select_edit_control()
function select_edit_control_inline($name, $values, $empty, $selected, $controller = null)
{
	if (isset($empty)) {
		// Push ''=>$empty onto the front of the array, maintaining keys
		$tmp = ['' => htmlspecialchars((string) $empty)];
		foreach ($values as $key => $value) {
			$tmp[$key] = htmlspecialchars((string) $value);
		}
		$values = $tmp;
	}
	$values['selected'] = htmlspecialchars((string) $selected);

	$html = '<span class="editable" id="' . $name . '">' . (array_key_exists($selected, $values) ? $values[$selected] : '') . '</span>';
	$js = 'jQuery("#' . $name . '").editable("' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'save.php", {tooltip: " ' . KT_I18N::translate('click to edit') . '", submitdata: {csrf: KT_CSRF_TOKEN}, type:"select", data:' . json_encode($values) . ', submit:"&nbsp;&nbsp;' . KT_I18N::translate('Save') . '&nbsp;&nbsp;", style:"inherit", placeholder: "' . KT_I18N::translate('click to edit') . '", callback:function(value, settings) {jQuery(this).html(settings.data[value]);} });';

	if ($controller) {
		$controller->addInlineJavascript($js);

		return $html;
	}
	// For AJAX callbacks
	return $html . '<script>' . $js . '</script>';
}

/**
 * Create an on-off switch for a form.
 *
 * @param string $name         - the name and ID for the form element
 * @param array  $values       - array of value=>display items
 * @param string $selected     - the currently selected item (if any)
 * @param string $activeText   - label for active response
 * @param string $inactiveText - label for inactive response
 * @param mixed  $value
 * @param mixed  $disabled
 * @param mixed  $size
 */
function simple_switch($name, $value, $selected, $disabled = '', $activeText = 'Yes', $inactiveText = 'No', $size = 'small')
{
	$html = '
		<div class="grid-x grid-margin-y">
			<div class="switch ' . $size . ' cell small-8 medium-4 large-2">
				<input class="switch-input" ' . $disabled . ' id="' . $name . '" type="checkbox" name="' . $name . '" value="' . $value . '"';
	if ((string) $value === (string) $selected) {
		$html .= ' checked';
	}
	$html .= '>' . '
				<label class="switch-paddle" for="' . $name . '">
					<span class="show-for-sr">' . $value . '</span>
					<span class="switch-active" aria-hidden="true">' . KT_I18N::translate($activeText) . '</span>
					<span class="switch-inactive" aria-hidden="true">' . KT_I18N::translate($inactiveText) . '</span>
				</label>
			</div>
		</div>
	';

	return $html;
}

/**
 * Create a set of switches for a form (only one can be "on").
 *
 * @param string $name     - the ID for the form element
 * @param array  $values   - array of value=>display items
 * @param string $selected - the currently selected item (if any)
 * @param mixed  $extra
 */
function radio_switch_group($name, $values, $selected, $extra = '')
{
	$html = '<div class="grid-x grid-margin-y">';
	foreach ($values as $key => $value) {
		$uniqueID = $name . (int) (microtime(true) * 1000000);
		$html .= '
				<div class="switch cell small-8 medium-4 large-2">
					<label>' . $value . '</label>
					<input class="switch-input" id="' . $uniqueID . '" type="radio" name="' . $name . '" value="' . htmlspecialchars((string) $key) . '"';
		if ((string) $key === (string) $selected) {
			$html .= ' checked';
		}
		if ($extra) {
			$html .= ' ' . $extra;
		}
		$html .= '>' . '
					<label class="switch-paddle" for="' . $uniqueID . '">
						<span class="show-for-sr">' . $value . '</span>
					</label>
				</div>
			';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Create a set of switches for a form (any can be "on").
 *
 * @param string $name     - the ID for the form element
 * @param array  $values   - array of value=>display items
 * @param string $selected - the currently selected item (if any)
 */
function checkbox_switch_group($name, $values, $selected)
{
	$html = '<div class="grid-x grid-margin-y">';
	foreach ($values as $key => $value) {
		$uniqueID = $key . (int) (microtime(true) * 1000000);
		$html .= '
				<div class="switch cell small-4 medium-3">
					<label>' . $value . '</label>
					<input class="switch-input" id="' . $uniqueID . '" type="radio" value="' . htmlspecialchars((string) $key) . '"';
		if ((string) $key === (string) $selected) {
			$html .= ' checked';
		}
		$html .= '>' . '
					<label class="switch-paddle" for="' . $uniqueID . '">
						<span class="show-for-sr">' . $value . '</span>
					</label>
				</div>
			';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Create a set of radio buttons for a form.
 *
 * @param string $name     - the ID for the form element
 * @param array  $values   - array of value=>display items
 * @param string $selected - the currently selected item (if any)
 * @param string $extra    - extra markup for field (optional class)
 */
function radio_buttons($name, $values, $selected, $extra = '')
{
	$html = '';
	foreach ($values as $key => $value) {
		$uniqueID = $name . (int) (microtime(true) * 1000000);
		$html .= '
			<label for="' . $uniqueID . '" ' . $extra . '>
				<input type="radio" name="' . $name . '" id="' . $uniqueID . '" value="' . htmlspecialchars((string) $key) . '"';
		if ((string) $key === (string) $selected) {
			$html .= ' checked';
		}
		$html .= '>' .
		htmlspecialchars((string) $value) . '
			</label>
		';
	}

	return $html;
}

// Print an edit control for a Yes/No field
function edit_field_yes_no($name, $selected = false, $extra = 'class="radio_inline"')
{
	return radio_buttons(
		$name,
		[false => KT_I18N::translate('No'), true => KT_I18N::translate('Yes')],
		$selected,
		$extra
	);
}

// Print an edit control for a checkbox
function checkbox($name, $is_checked = false, $extra = '')
{
	return '<input type="checkbox" name="' . $name . '" value="1" ' . ($is_checked ? ' checked ' : '') . $extra . '><label></label>';
}

// Print an edit control for a checkbox, with a hidden field to store one of the two states.
// By default, a checkbox is either set, or not sent.
// This function gives us a three options, set, unset or not sent.
// Useful for dynamically generated forms where we don't know what elements are present.
function two_state_checkbox($name, $is_checked = 0, $extra = '')
{
	return
		'<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . ($is_checked ? 1 : 0) . '">' .
		'<input type="checkbox" name="' . $name . '-GUI-ONLY" value="1"' .
		($is_checked ? ' checked="checked"' : '') .
		' onclick="document.getElementById(\'' . $name . '\').value=(this.checked?1:0);" ' . $extra . '>';
}

// Print a set of edit controls to select languages
function edit_language_checkboxes($field_prefix, $languages)
{
	$used_languages = KT_I18N::installed_languages();
	// sort by localised name
	foreach ($used_languages as $code => $name) {
		$used_languages[$code] = KT_I18N::translate($name);
	}
	asort($used_languages);

	echo '<ul class="vertList">';
	foreach ($used_languages as $code => $name) {
		$content = '<input type="checkbox" name="' . $field_prefix . $code . '" id="' . $field_prefix . $code . '"';
		if (false !== strpos(",{$languages},", ",{$code},")) {
			$content .= 'checked="checked"';
		}
		$content .= '><label for="' . $field_prefix . $code . '"> ' . KT_I18N::translate($name) . '</label>';
		echo '<li>' . $content . '</li>';
	}
	echo '</ul>';
}

// Print an edit control for access level
function edit_field_access_level($name, $selected = '', $extra = '', $priv = false)
{
	if (false == $priv) {
		$ACCESS_LEVEL = [
			KT_PRIV_PUBLIC => KT_I18N::translate('Show to everyone'),
			KT_PRIV_USER   => KT_I18N::translate('Show to members'),
			KT_PRIV_NONE   => KT_I18N::translate('Show to managers'),
			KT_PRIV_HIDE   => KT_I18N::translate('Hide from everyone'),
		];
	} else {
		$ACCESS_LEVEL = [
			KT_PRIV_USER => KT_I18N::translate('Show to members'),
			KT_PRIV_NONE => KT_I18N::translate('Show to managers'),
			KT_PRIV_HIDE => KT_I18N::translate('Hide from everyone'),
		];
	}

	return select_edit_control($name, $ACCESS_LEVEL, null, $selected, $extra);
}

// Print an edit control for a RESN field
function edit_field_resn($name, $selected = '', $extra = '')
{
	$RESN = [
		'' => '',
		'none'         => KT_I18N::translate('Show to everyone'), // Not valid GEDCOM, but very useful
		'privacy'      => KT_I18N::translate('Show to members'),
		'confidential' => KT_I18N::translate('Show to managers'),
		'locked'       => KT_I18N::translate('Only managers can edit'),
	];

	return select_edit_control($name, $RESN, null, $selected, $extra);
}

// Print an edit control for a contact method field
function edit_field_contact($name, $selected = '', $extra = '')
{
	// Different ways to contact the users
	$CONTACT_METHODS = [
		'messaging' => KT_I18N::translate('Kiwitrees sends emails'),
		'mailto'    => KT_I18N::translate('Mailto link'),
		'none'      => KT_I18N::translate('No contact'),
	];

	return select_edit_control($name, $CONTACT_METHODS, null, $selected, $extra);
}

function edit_field_contact_inline($name, $selected = '', $controller = null)
{
	// Different ways to contact the users
	$CONTACT_METHODS = [
		'messaging' => KT_I18N::translate('Kiwitrees sends emails'),
		'mailto' => KT_I18N::translate('Mailto link'),
		'none' => KT_I18N::translate('No contact'),
	];

	return select_edit_control_inline($name, $CONTACT_METHODS, null, $selected, $controller);
}

// Print an edit control for a language field
function edit_field_language($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_I18N::used_languages(), null, $selected, $extra);
}

// An inline-editing version of edit_field_language()
function edit_field_language_inline($name, $selected = false, $controller = null)
{
	return select_edit_control_inline(
		$name,
		KT_I18N::used_languages(),
		null,
		$selected,
		$controller
	);
}

// Print an edit control for a range of integers
function edit_field_integers($name, $min, $max, $selected = false, $extra = false)
{
	$array = [];
	for ($i = $min; $i <= $max; $i++) {
		$array[$i] = KT_I18N::number($i);
	}

	return select_edit_control($name, $array, null, $selected, $extra);
}

// Print an edit control for a username
function edit_field_username($name, $selected = '', $extra = '')
{
	$all_users = KT_DB::prepare(
		"SELECT user_name, CONCAT_WS(' ', real_name, '-', user_name) FROM `##user` ORDER BY real_name"
	)->fetchAssoc();
	// The currently selected user may not exist
	if ($selected && !array_key_exists($selected, $all_users)) {
		$all_users[$selected] = $selected;
	}

	return select_edit_control($name, $all_users, '-', $selected, $extra);
}


function print_addnewmedia_link($element_id)
{
	global $iconStyle;

	return '
		<a href="#" onclick="pastefield=document.getElementById(\'' . $element_id . '\'); window.open(\'addmedia.php?action=showmediaform&type=event\', \'_blank\', \'\'); return false;" title="' . KT_I18N::translate('Add a media object') . '" data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-square-plus"></i>
		</a>';
}

function print_addnewrepository_link($element_id)
{
	global $iconStyle;

	return '
		<a href="#" onclick="addnewrepository(document.getElementById(\'' . $element_id . '\')); return false;" title="' . KT_I18N::translate('Create Repository') . '" data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-square-plus"></i>
		</a>';
}

function print_addnewnote_link($element_id)
{
	global $iconStyle;

	return '
		<a href="edit_interface.php?action=addnewnote&amp;noteid=newnote&amp;' . KT_TIMESTAMP . '&ged=' . KT_GEDCOM . '" target="_blank" title="' . KT_I18N::translate('Create a new Shared Note') . '" data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-square-plus"></i>

		</a>
	';
}

// / Used in GEDFact CENS assistant
function print_addnewnote_assisted_link($element_id, $pid)
{
	return '<a href="#" onclick="addnewnote_assisted(document.getElementById(\'' . $element_id . '\'), \'' . $pid . '\'); return false;" target="_blank" rel="noopener noreferrer">' . KT_I18N::translate('Create a new Shared Note using Assistant') . '</a>';
}

function print_editnote_link($note_id)
{
	return '<a href="#" onclick="edit_note(\'' . $note_id . '\'); return false;" class="icon-button_note" title="' . KT_I18N::translate('Edit shared note') . '"></a>';
}

function print_addnewsource_link($element_id)
{
	global $iconStyle;

	return '
		<a href="#" onclick="addnewsource(document.getElementById(\'' . $element_id . '\')); return false;" title="' . KT_I18N::translate('Create a new source') . '" data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-square-plus"></i>
		</a>';
}

/**
 * Genearate a <select> element, with the dates/places of all known censuses.
 *
 * @param string $locale - Sort the censuses for this locale
 * @param string $xref   - The individual for whom we are adding a census
 */
function censusDateSelector($locale, $xref)
{
	global $controller;

	// Show more likely census details at the top of the list.
	switch (KT_LOCALE) {
		case 'cs':
			$census_places = [new KT_Census_CensusOfCzechRepublic()];

			break;

		case 'en_AU':
		case 'en_GB':
			$census_places = [new KT_Census_CensusOfEngland(), new KT_Census_CensusOfWales(), new KT_Census_CensusOfScotland()];

			break;

		case 'en_US':
			$census_places = [new KT_Census_CensusOfUnitedStates()];

			break;

		case 'fr':
		case 'fr_CA':
			$census_places = [new KT_Census_CensusOfFrance()];

			break;

		case 'da':
			$census_places = [new KT_Census_CensusOfDenmark()];

			break;

		case 'de':
			$census_places = [new KT_Census_CensusOfDeutschland()];

			break;

		default:
			$census_places = [];

			break;
	}

	foreach (KT_Census_Census::allCensusPlaces() as $census_place) {
		if (!in_array($census_place, $census_places)) {
			$census_places[] = $census_place;
		}
	}

	$controller->addInlineJavascript('
			function selectCensus(el) {
				var option = jQuery(":selected", el);
				jQuery("div.input input.DATE").val(option.val());
				jQuery("div.input input.PLAC").val(option.data("place"));
				jQuery("input.census-class", jQuery(el).closest("div.input")).val(option.data("census"));
				if (option.data("place")) {
					jQuery("#assistant-link").show();
				} else {
					jQuery("#assistant-link").hide();
				}
			}
			function set_pid_array(pa) {
				jQuery("#pid_array").val(pa);
			}
			function activateCensusAssistant() {
				if (jQuery("#newshared_note_img").hasClass("icon-plus")) {
					expand_layer("newshared_note");
				}
				var field  = jQuery("#newshared_note input.NOTE")[0];
				var xref   = jQuery("input[name=pid]").val();
				var census = jQuery(".census-assistant-selector :selected").data("census");
				return addnewnote_assisted(field, xref, census);
			}
		');

	$options = '<option value="">' . KT_I18N::translate('Select a census country and date') . '</option>';

	foreach ($census_places as $census_place) {
		$options .= '<optgroup label="' . $census_place->censusPlace() . '">';
		foreach ($census_place->allCensusDates() as $census) {
			$date = new KT_Date($census->censusDate());
			$year = $date->minimumDate()->format('%Y');
			$place_hierarchy = explode(', ', $census->censusPlace());
			$options .= '<option value="' . $census->censusDate() . '" data-place="' . $census->censusPlace() . '" data-census="' . get_class($census) . '">' . $place_hierarchy[0] . ' ' . $year . '</option>';
		}
		$options .= '</optgroup>';
	}

	return
		'<input type="hidden" id="pid_array" name="pid_array" value="">' .
		'<select class="census-assistant-selector" onchange="selectCensus(this);">' . $options . '</select>';
}

/**
 * A list of known surname traditions, with their descriptions.
 *
 * @return string[]
 */
function surnameDescriptions()
{
	return [
		'paternal' => KT_I18N::translate_c('Surname tradition', 'paternal') .
			' - ' . /* I18N: In the paternal surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.') .
			' ' . /* I18N: In the paternal surname tradition, ... */ KT_I18N::translate('Wives take their husband’s surname.'),
		/* I18N: A system where children take their father’s surname */ 'patrilineal' => KT_I18N::translate('patrilineal') .
			' - ' . /* I18N: In the patrilineal surname tradition, ... */ KT_I18N::translate('Children take their father’s surname.'),
		/* I18N: A system where children take their mother’s surname */ 'matrilineal' => KT_I18N::translate('matrilineal') .
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
		'none' => KT_I18N::translate_c('Surname tradition', 'none'),
	];
}

// Keep the existing CHAN record when editing
function no_update_chan(KT_GedcomRecord $record = null)
{
	global $NO_UPDATE_CHAN;

	$checked = $NO_UPDATE_CHAN ? ' checked="checked"' : '';

	if (KT_USER_IS_ADMIN && KT_SCRIPT_NAME !== 'admin_trees_addunlinked.php') { ?>
		<div class="cell last_change">
			<div class="grid-x">
				<div class="cell medium-3">
					<label>
						<?php echo KT_Gedcom_Tag::getLabel('CHAN'); ?>
						<?php if ($record) { ?>
							<h6 class="subheader"><?php echo KT_Gedcom_Tag::getLabelValue('DATE', $record->LastChangeTimestamp()); ?></h6>
							<h6 class="subheader"><?php echo KT_Gedcom_Tag::getLabelValue('_KT_USER', $record->LastChangeUser()); ?></h6>
						<?php } ?>
					</label>
				</div>
				<div class="cell medium-9 input">
					<div class="checkbox-label">
						<?php echo KT_I18N::translate('Prevent updates to the “last change” record'); ?>
					</div>
					<?php echo simple_switch(
		'preserve_last_changed',
		'',
		$checked,
		'',
		KT_I18N::translate('Yes'),
		KT_I18N::translate('No'),
		'tiny',
	); ?>
					<div class="cell callout info-help  show-for-medium">
						<?php echo KT_I18N::translate('
							Administrators sometimes need to clean up and correct the data submitted by users.
							<br>
							When Administrators make such corrections information about the original change
							is replaced.
							<br>When this option is selected kiwitrees will retain the original change information
							instead of replacing it.
						'); ?>
					</div>
				</div>
			</div>
		</div>
	<?php } else {
		return '';
	}
}

/**
 * Remove a complete directory
 * used in site-clean and
 * in custom language pages.
 *
 * @param mixed $dir
 */
function full_rmdir($dir)
{
	if (!is_writable($dir)) {
		if (!@chmod($dir, KT_PERM_EXE)) {
			return false;
		}
	}

	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ('.' == $entry || '..' == $entry) {
			continue;
		}
		$entry = $dir . '/' . $entry;
		if (is_dir($entry)) {
			if (!full_rmdir($entry)) {
				return false;
			}

			continue;
		}
		if (!@unlink($entry)) {
			$d->close();

			return false;
		}
	}

	$d->close();
	rmdir($dir);

	return true;
}
