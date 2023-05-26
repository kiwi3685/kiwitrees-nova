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
* Add a tag input field
*
* called for each fact to be edited on a form.
* Fact level=0 means a new empty form : data are POSTed by name
* else data are POSTed using arrays :
* glevels[] : tag level
*  islink[] : tag is a link
*     tag[] : tag name
*    text[] : tag value
*
* @param string $tag fact record to edit (eg 2 DATE xxxxx)
* @param string $upperlevel optional upper level tag (eg BIRT)
* @param string $label An optional label to echo instead of the default
* @param string $extra optional text to display after the input field
* @param boolean $rowDisplay True to have the row displayed by default, false to hide it by default
*/
function add_simple_tag($tag, $upperlevel = '', $label = '', $extra = null, $rowDisplay = true)
{
    global $MEDIA_DIRECTORY, $tags, $emptyfacts, $main_fact, $TEXT_DIRECTION;
    global $NPFX_accept, $SPFX_accept, $NSFX_accept, $FILE_FORM_accept, $upload_count;
    global $pid, $gender, $linkToID, $bdm, $action, $event_add, $iconStyle;
    global $QUICK_REQUIRED_FACTS, $QUICK_REQUIRED_FAMFACTS, $PREFER_LEVEL2_SOURCES;

    require_once KT_ROOT.'includes/functions/functions_print.php';
    // Keep track of SOUR fields, so we can reference them in subsequent PAGE fields.
    static $source_element_id;

    init_calendar_popup();

    $namefacts          = ['GIVN', 'SURN', 'NPFX', 'SPFX','NSFX', '_MARNM_SURN'];
    $records            = ['INDI', 'FAM', 'OBJE', 'NOTE', 'REPO', 'SOUR'];
    $subsourfacts       = ['TEXT', 'PAGE', 'OBJE', 'QUAY', 'DATE', 'NOTE'];
    $linkfacts          = ['REPO', 'SOUR', 'OBJE', 'FAMC'];
    $specialchar        = ['TYPE', 'TIME', 'NOTE', 'SOUR', 'REPO', 'OBJE', 'ASSO', '_ASSO', 'AGE', 'DATE'];
    $mapfacts           = ['DATA', 'MAP', 'LATI', 'LONG'];
    $autocompleteTags   = ['ALIA', 'ASSO', '_ASSO', 'CAUS', 'GIVN', 'NPFX', 'NPFX', 'SPFX', 'SURN', 'NOTE', 'OBJE', 'OCCU', 'PAGE', 'PLAC', 'REPO', 'SOUR', '_MARNM_SURN'];
    $labelIndent        = '';
    $class              = '';
    $style              = '';
    $other              = '';

    preg_match('/^(?:(\d+) (' . KT_REGEX_TAG . ') ?(.*))/', $tag, $match);
    if ($match) {
        [, $level, $fact, $value] = $match;
    }

    if (substr($tag, 0, strpos($tag, "CENS"))) {
        $event_add = "census_add";
    }

    if (substr($tag, 0, strpos($tag, "PLAC"))) {
        ?>
        <script>
            function valid_lati_long(field, pos, neg) {
                // valid LATI or LONG according to Gedcom standard
                // pos (+) : N or E
                // neg (-) : S or W
                var txt=field.value.toUpperCase();
                txt=txt.replace(/(^\s*)|(\s*$)/g, ''); // trim
                txt=txt.replace(/ /g, ':'); // N12 34  ==> N12.34
                txt=txt.replace(/\+/g, ''); // +17.1234  ==> 17.1234
                txt=txt.replace(/-/g, neg); // -0.5698  ==> W0.5698
                txt=txt.replace(/,/g, '.'); // 0,5698 ==> 0.5698
                // 0�34'11 ==> 0:34:11
                txt=txt.replace(/\uB0/g, ':'); // �
                txt=txt.replace(/\u27/g, ':'); // '
                // 0:34:11.2W ==> W0.5698
                txt=txt.replace(/^([0-9]+):([0-9]+):([0-9.]+)(.*)/g, function($0, $1, $2, $3, $4) { var n=parseFloat($1); n+=($2/60); n+=($3/3600); n=Math.round(n*1E4)/1E4; return $4+n; });
                // 0:34W ==> W0.5667
                txt=txt.replace(/^([0-9]+):([0-9]+)(.*)/g, function($0, $1, $2, $3) { var n=parseFloat($1); n+=($2/60); n=Math.round(n*1E4)/1E4; return $3+n; });
                // 0.5698W ==> W0.5698
                txt=txt.replace(/(.*)([N|S|E|W]+)$/g, '$2$1');
                // 17.1234 ==> N17.1234
                if (txt && txt.charAt(0)!=neg && txt.charAt(0)!=pos)
                    txt=pos+txt;
                field.value = txt;
            }
        </script>
        <?php
    }

    if (empty($linkToID)){
        $linkToID = $pid;
    }

    // element name : used to POST data
    if ($level == 0) {
        if ($upperlevel) {
            $element_name = $upperlevel . "_" . $fact; // ex: BIRT_DATE | DEAT_DATE | ...
        } else {
            $element_name = $fact; // ex: OCCU
        }
    } else $element_name = "text[]";

    if ($level == 1) {
        $main_fact = $fact;
    }

    // element id : used by javascript functions
    if ($level == 0) {
            $element_id = $fact; // ex: NPFX | GIVN ...
    } else {
            $element_id = $fact . (int)(microtime(true)*1000000); // ex: SOUR56402
    }

    if ($upperlevel) {
        $element_id = $upperlevel . "_" . $fact . (int)(microtime(true)*1000000); // ex: BIRT_DATE56402 | DEAT_DATE56402 ...
    }

    // field value
    $islink = (substr($value, 0, 1) == "@" && substr($value, 0, 2) != "@#");

    if ($islink) {
        $value = trim(trim(substr($tag, strlen($fact) + 3)), " @\r");
    } else {
        $value = trim(substr($tag, strlen($fact) + 3));
    }

    if ($fact == 'REPO' || $fact == 'SOUR' || $fact == 'OBJE' || $fact == 'FAMC'){
        $islink = true;
    }

    if ($fact === 'SHARED_NOTE_EDIT' || $fact === 'SHARED_NOTE') {
        $islink = true;
        $fact = "NOTE";
    }

    if ($fact === 'SOUR' || ($source_element_id && $level > 2 && in_array($fact, $subsourfacts))) {
        $class = ' class="sour_facts"';
    }

    if (in_array($fact, $mapfacts) && $value === '') {
        $style = ' style="display:none;"';
    }

    if (
        in_array($fact, ['LATI', 'LONG', 'PAGE', 'DATA', 'TEXT', 'NPFX', 'SPFX', 'NSFX']) || 
        (in_array($fact, ['OBJE', 'NOTE']) && $level >= 2) || 
        (in_array($fact, ['ADDR']) && $upperlevel === 'PLAC')
    ) {
        $labelIndent = ' style="text-indent: 3rem;"';
    } ?>

     <?php // Layout ?>
    <div id="<?php echo $element_id; ?>_factdiv" class="cell <?php echo $class; ?>" <?php echo $style; ?>>
        <div class="grid-x">

             <?php // Label ?>
            <div class="cell small-12 medium-3">
                <label class="middle" <?php echo $labelIndent; ?>>

                    <?php if (KT_DEBUG) {
                        echo $element_name . '<br>';
                    } ?>

                    <?php echo label($label, $upperlevel, $fact); ?>

                </label>
            </div>

             <?php // Value ?>
            <div class="cell small-10 medium-7">

                    <?php if (KT_DEBUG) {
                        echo $tag, "<br>";
                    } ?>

                     <?php // Hidden tag level input fields ?>
                    <?php echo tagLevel($level, $islink, $fact); ?>

                     <?php // Retrieve linked NOTE ?>
                    <?php echo retrieveNote($fact,$islink, $value); ?>

                     <?php // Display HUSB / WIFE names for information only on MARR edit form ?>
                    <?php echo displaySpouses($pid, $fact); ?>

                     <?php // Create input field ?>
                    <?php if (in_array($fact, $autocompleteTags)) { ?>
                        <div class="input-group autocomplete_container">

                            <?php // Print link to add new record ?>
                            <?php echo newRecordLinks($fact, $element_id, $value, $islink, $action, $pid, $event_add); ?>

                            <?php echo autocompleteInputs($fact, $element_id, $element_name, $value, $namefacts, $level, $tags, $records, $islink); ?>
                            <?php if (in_array($fact, ['ALIA', 'ASSO', '_ASSO'])) {
                                $source_element_id = '';
                            } ?>
 
                    <?php } else if ($fact === 'DATE' || $fact === 'TIME') { ?>
                        <div class="input-group date">

                            <?php // Print link to add new record ?>
                            <?php echo newRecordLinks($fact, $element_id, $value, $islink, $action, $pid, $event_add); ?>

                            <?php //echo dateSelection($fact, $element_id, $element_name, $value); ?>
                            <?php echo dateSelection($element_id, $element_name, $value); ?>

                    <?php } else { ?>
                        <div class="input-group">

                            <?php // Print link to add new record ?>
                            <?php echo newRecordLinks($fact, $element_id, $value, $islink, $action, $pid, $event_add); ?>

                            <?php echo createInput($fact, $emptyfacts, $value, $element_id, $element_name, $source_element_id, $pid, $gender, $upperlevel, $action, $namefacts, $level, $tags, $islink); ?>
       
                    <?php } ?>

                         <?php // Specialised input types:  ?>
                        <?php echo otherInputs($fact, $level, $element_id, $upperlevel, $tags, $pid, $element_name, $value, $specialchar, $emptyfacts); ?>

                         <?php // Help links ?>
                        <?php helpText($label, $upperlevel, $fact, $level, $action); ?>
     
                    </div>

                     <?php // Current value / description used to go here ?>

            </div>

             <?php // Icon sets ?>           
            <div class="cell small-2 popup_links">

                <?php // echo popupLinks($fact, $element_id, $upperlevel, $level, $tags, $element_name, $value, $action, $event_add, $islink, $pid); ?>
  
            </div>

             <?php // Optional text to display after the input field (so that additional text can be printed in the box) ?> 
            <?php echo $extra; ?>

        </div>

         <?php // Checkboxes to apply '1 SOUR' to BIRT/MARR/DEAT as '2 SOUR' ?>
        <?php if ($fact == 'SOUR' && $level == 1) { ?>
            <div class="source_links">
                <h4><?php echo KT_I18N::translate('Link this source to these records'); ?></h4>

                 <?php echo sourceLinks($bdm); ?>

            </div>

        <?php } ?>

    </div>

    <?php return $element_id;

}

/**
 * Label for add new tags
 *
 * @param string $upperlevel optional upper level tag (eg BIRT)
 * @param string $label An optional label to echo instead of the default
 * 
**/
function label($label, $upperlevel, $fact)
{

    // tag name
    if ($label) {
        echo $label;
    } elseif ($upperlevel) {
        echo KT_Gedcom_Tag::getLabel($upperlevel . ':' . $fact);
    } else {
        echo KT_Gedcom_Tag::getLabel($fact);
    }

}

/**
 * 
 * 
 * 
**/
function tagLevel($level, $islink, $fact)
{
    // tag level
    if ($level > 0) {
        if ('TEXT' == $fact && $level > 1) { ?>
            <input type="hidden" name="glevels[]" value="<?php echo $level - 1; ?>">
            <input type="hidden" name="islink[]" value="0">
            <input type="hidden" name="tag[]" value="DATA">
             <?php // leave data text[] value empty because the following TEXT line will cause the DATA to be added ?>
            <input type="hidden" name="text[]" value="">
        <?php } ?>
        <input type="hidden" name="glevels[]" value="<?php echo $level; ?>">
        <input type="hidden" name="islink[]" value="<?php echo $islink; ?>">
        <input type="hidden" name="tag[]" value="<?php echo $fact; ?>">
    <?php }

}

/**
 * NOT IN USE 
 * Disply current value / description under input field
 *
 * @param string $upperlevel optional upper level tag (eg BIRT)
 * @param string $label An optional label to echo instead of the default
 * 
**/
function currentValue($fact, $islink, $value, $upperlevel, $element_id)
{
    $currentValue = ['ASSO', '_ASSO', 'SOUR', 'OBJE']; ?>

    <div id="<?php echo $element_id; ?>_description">

        <?php // current value
        if ($fact == 'DATE') {
            $date = new KT_Date($value);
            echo $date->Display();
        }
        if ((in_array($fact, $currentValue) || ($fact == 'NOTE' && $islink)) && $value) {
            $record = KT_GedcomRecord::getInstance($value);
            if ($record) {
                echo ' ', $record->getFullName();
            } elseif ($value != 'new') {
                echo ' ', $value;
            }
        }
        // pastable values
        if ($fact === 'FORM' && $upperlevel === 'OBJE') {
            print_autopaste_link($element_id, $FILE_FORM_accept);
        } ?>

    </div>
    <?php

}

/**
 * Retrieve linked NOTE
 *
 * @param string $fact
 * @param string $islink
 * @param string $value
 * 
**/
function retrieveNote($fact, $islink, $value)
{
    if ($fact == "NOTE" && $islink) {
        $note1 = KT_Note::getInstance($value);
        if ($note1) {
            $noterec = $note1->getGedcomRecord();
            preg_match("/$value/i", $noterec, $notematch);
            $value = $notematch[0];
        }
    }

}

/**
 * Display HUSB / WIFE names for information only on MARR edit form.
 *
 * @param string $fact
 * @param string $islink
 * @param string $value
 * 
**/
function displaySpouses($pid, $fact)
{
    $tmp = KT_GedcomRecord::GetInstance($pid);
    if ($fact == 'HUSB') {
        $husb = KT_Person::getInstance($tmp->getHusband()->getXref());
        echo $husb->getFullName();
    }
    if ($fact == 'WIFE') {
        $wife = KT_Person::getInstance($tmp->getWife()->getXref());
        echo $wife->getFullName();
    }

}

/**
 * Display a help text link.
 *
 * @param string $fact
 * @param string $islink
 * @param string $value
 * 
**/
function helpText($label, $upperlevel, $fact, $level, $action)
{
    // help text
    if ($action == "addnewnote_assisted") {
        // Do not print on census_assistant window
    } else {
        // Not all facts have help text.
        switch ($fact) {
            case 'NAME':
                if ($upperlevel !== 'REPO' && $upperlevel !== 'UNKNOWN') {
                    echo helpInputLabel($fact);
                }
                break;
            case 'ASSO':
            case '_ASSO': // Some apps (including kiwitrees) use "2 _ASSO", since "2 ASSO" is not strictly valid GEDCOM
                if ($level == 1) {
                    echo helpInputLabel('ASSO_1');
                } else {
                    echo helpInputLabel('ASSO_2');
                }
                break;
            case 'ADDR':
            case 'AGNC':
            case 'CAUS':
            case 'DATE':
            case 'EMAI':
            case 'EMAIL':
            case 'EMAL':
            case '_EMAIL':
            case 'FAX':
            case 'OBJE':
            case '_MARNM_SURN':
            case 'PAGE':
            case 'PEDI':
            case 'PHON':
            case 'PLAC':
            case 'RELA':
            case 'RESN':
            case 'ROMN':
            case 'SEX':
            case 'SOUR':
            case 'STAT':
            case 'SURN':
            case 'TEMP':
            case 'TEXT':
            case 'TIME':
            case 'URL':
            case '_HEB':
                echo helpInputLabel($fact);
                break;
        }
    }

}

/**
 * Create input field
 *
 * @param string $fact
 * @param string $value
 * 
**/
function createInput($fact, $emptyfacts, $value, $element_id, $element_name, $source_element_id, $pid, $gender, $upperlevel, $action, $namefacts, $level, $tags, $islink)
{
    global $iconStyle;

    if (in_array($fact, $emptyfacts) && ($value === '' || $value === 'Y' || $value === 'y')) { ?>
        <input type="hidden" id="<?php echo $element_id; ?>" name="<?php echo $element_name; ?>" value="<?php echo htmlspecialchars((string) $value); ?>" <?php echo placeholder($fact); ?> >
 
        <?php if ($level <= 1) { ?>
            <?php echo '<input type="checkbox" ';
                if ($value) {
                    echo ' checked="checked"';
                }
                echo ' onclick="if (this.checked) ' . $element_id . '.value="Y"; else ' . $element_id . '.value=""';
            echo '">'; ?>
            <span class="yes"><?php echo KT_I18N::translate('This event occurred, but the details are unknown.'); ?></span>
        <?php }

        if ($fact === 'CENS' && $value === 'Y') {
            if (array_key_exists('census_assistant', KT_Module::getActiveModules()) && KT_GedcomRecord::getInstance($pid) instanceof KT_Person) {
                echo censusDateSelector(KT_LOCALE, $pid); ?>
                <br>
                <div class="cell medium-11 auto">
                    <a href="#" style="display: none;" id="assistant-link" onclick="return activateCensusAssistant();">
                        <?php echo KT_I18N::translate('Create a shared note using the census assistant'); ?>
                    </a>
                </div>
            <?php }
        }

    } else if ($fact == "TEMP") {
        echo select_edit_control($element_name, KT_Gedcom_Code_Temp::templeNames(), KT_I18N::translate('No Temple - Living Ordinance'), $value);
    } else if ($fact == "ADOP") {
        switch ($gender) {
            case 'M': echo edit_field_adop_m($element_name, $value); break;
            case 'F': echo edit_field_adop_f($element_name, $value); break;
            default:  echo edit_field_adop_u($element_name, $value); break;
        }
    } else if ($fact == "PEDI") {
        switch ($gender) {
            case 'M': echo edit_field_pedi_m($element_name, $value); break;
            case 'F': echo edit_field_pedi_f($element_name, $value); break;
            default:  echo edit_field_pedi_u($element_name, $value); break;
        }
    } else if ($fact == 'STAT') {
        echo select_edit_control($element_name, KT_Gedcom_Code_Stat::statusNames($upperlevel), '', $value);
    } else if ($fact == 'RELA') {
        echo edit_field_rela($element_name, strtolower($value));
    } else if ($fact == 'QUAY') {
        echo select_edit_control($element_name, KT_Gedcom_Code_Quay::getValues(), '', $value);
    } else if ($fact == '_KT_USER') {
        echo edit_field_username($element_name, $value);
    } else if ($fact == 'RESN') {
        echo edit_field_resn($element_name, $value);
    } else if ($fact == '_PRIM') {
        echo '<select id="', $element_id, '" name="', $element_name, '" >';
        echo '<option value="N"';
        if ($value == 'N') echo ' selected="selected"';
        echo '>', KT_I18N::translate('no'), '</option>';
        echo '<option value="Y"';
        if ($value == 'Y') echo ' selected="selected"';
        echo '>', KT_I18N::translate('yes'), '</option>';
        echo '</select>';
    } else if ($fact == 'SEX') {
        echo '<select id="', $element_id, '" name="', $element_name, '"><option value="M"';
        if ($value == 'M') echo ' selected="selected"';
        echo '>', KT_I18N::translate('Male'), '</option><option value="F"';
        if ($value == 'F') echo ' selected="selected"';
        echo '>', KT_I18N::translate('Female'), '</option><option value="U"';
        if ($value == 'U' || empty($value)) echo ' selected="selected"';
        echo '>', KT_I18N::translate_c('unknown gender', 'Unknown'), '</option></select>';
    } else if ($fact == 'TYPE' && $level == '3') {
        //-- Build the selector for the Media 'TYPE' Fact
        echo '<select name="text[]"><option selected="selected" value="" ></option>';
        $selectedValue = strtolower($value);
        if (!array_key_exists($selectedValue, KT_Gedcom_Tag::getFileFormTypes())) {
            echo '<option selected="selected" value="', htmlspecialchars((string) $value), '" >', htmlspecialchars((string) $value), '</option>';
        }
        foreach (KT_Gedcom_Tag::getFileFormTypes() as $typeName => $typeValue) {
            echo '<option value="', $typeName, '"';
            if ($selectedValue == $typeName) {
                echo ' selected="selected"';
            }
            echo '>', $typeValue, '</option>';
        }
        echo '</select>';
    } else if (($fact == 'NAME' && $upperlevel != 'REPO' && $upperlevel !== 'UNKNOWN') || $fact == '_MARNM') { ?>
         <?php // Populated in javascript from sub-tags ?>
        <span class="input-group-label"
            onclick="convertHidden('<?php echo $element_id; ?>'); return false;" 
            title="<?php echo KT_I18N::translate('Edit name'); ?>"
        >
            <i class="<?php echo $iconStyle; ?> fa-user-pen"></i>
        </span>
        <input 
            type="text" 
            id="<?php echo $element_id; ?>" 
            class="<?php echo $fact; ?> readonly" 
            name="<?php echo $element_name; ?>" 
            onchange="updateTextName('<?php echo $element_id; ?>'); ?>" 
            value="//<?php //echo htmlspecialchars((string) $value); ?>" 
            readonly
        >
    <?php } else { ?>
 
         <?php // Text and Textarea input fields ?>
        <?php if ($fact == 'TEXT' || $fact == 'ADDR' || ($fact == 'NOTE' && !$islink)) { ?>
            <textarea id="<?php echo $element_id; ?>" name="<?php echo $element_name; ?>" dir="auto"  rows='1'><?php echo htmlspecialchars((string) $value); ?></textarea>
        <?php } else { ?>
             <?php // If using census_assistant window ?>
            <?php if (in_array($fact, $namefacts)) {
               $extra_markup = ' 
                    onblur="updatewholename();" 
                    onkeyup="updatewholename();" 
                ';
            } ?>
            <input 
                class="<?php echo $fact; ?>" 
                type="text" 
                id="<?php echo $element_id; ?>" 
                name="<?php echo $element_name; ?>" 
                value="<?php echo htmlspecialchars((string) $value); ?>" 
                dir="ltr"
                <?php echo placeholder($fact); ?>
            >
        <?php }
    }

}

/**
 * Generates javascript code for calendar popup in user's language
 *
 * @param string $fact
 * @param string $value
 * 
**/
function dateSelection($element_id, $element_name, $value)
{
    global $iconStyle; ?>

    <a 
        class="input-group-label"
        href="#"
        onclick="cal_toggleDate('caldiv<?php echo $element_id; ?>', '<?php echo $element_id; ?>'); return false;" 
    >
        <i class="<?php echo $iconStyle; ?> fa-calendar-days"></i>
    </a>
    <input 
        type="text" 
        name="<?php echo $element_name; ?>" 
        id="<?php echo $element_id; ?>" 
        value="<?php echo htmlspecialchars((string) $value); ?>" 
        onblur="valid_date(this);" 
        onmouseout="valid_date(this);"
    >
    <?php // Holder for calendar ?>
    <div id="caldiv<?php echo $element_id; ?>" style="visibility: hidden;"></div>
    <?php

}

/**
 * Sets of icons attached to input fields
 *
 * @param string $fact
 * @param string $value
 * 
**/
function autocompleteInputs($fact, $element_id, $element_name, $value, $namefacts, $level, $tags, $records, $islink)
{
    global $iconStyle;

    $autocomplete = $fact; ?>

     <?php // Special cases ?>
    <?php 
    if (in_array($fact, ['ALIA', 'ASSO', '_ASSO'])) {
        $autocomplete = $fact . ' data-autocomplete-extra="input.DATE"';
    }

    if ($fact === '_MARNM_SURN') {
        $autocomplete = 'SURN';
    }

    if ($fact === 'TYPE') {
        if ($level == 2 && $tags[0] == 'EVEN') {
            $autocomplete = 'EVEN_TYPE';
        } elseif ($level == 2 && $tags[0] == 'FACT') {
            $autocomplete = 'FACT_TYPE';
        }
    }

    in_array($fact, $namefacts) ? $other = 'onblur="updatewholename();" onkeyup="updatewholename();"' : $other = '';

    $value ? $title = $value : $title = '';

    if (in_array($fact, $records) && $value) {
        $id = '';

        switch ($fact) {
            case 'INDI':
                $id    = KT_Person::getInstance($value);
                $title = strip_tags($id->getLifeName());
               break;
            case 'FAM':
                $id    = KT_Family::getInstance($value);
                $title = strip_tags($id->getFullName());
                break;
            case 'OBJE':
                $id    = KT_Media::getInstance($value);
                $title = strip_tags($id->getTitle());
                break;
            case 'NOTE':
                if ($islink) {
                    $id    = KT_NOTE::getInstance($value);
                    $title = strip_tags($id->getFullName());
                }
                break;
            case 'REPO':
                $id    = KT_Repository::getInstance($value);
                $title = strip_tags($id->getFullName());
                break;
            case 'SOUR':
                $id    = KT_Source::getInstance($value);
                $title = strip_tags($id->getFullName());
                break;
        }

    }

    echo autocompleteHtml(
        $element_id, 
        $autocomplete, 
        '', 
        $title, 
        '', 
        $element_name, 
        $value, 
        '', 
        $other
    );

}

/**
 * Sets of icons attached to input fields
 *
 * @param string $fact
 * @param string $value
 * 
**/
function newRecordLinks($fact, $element_id, $value, $islink, $action, $pid, $event_add)
{

    if ($fact) { ?>
        <span class="input-group-label addnew">
            <?php switch ($fact) {
                case 'SOUR':
                    echo print_addnewsource_link($element_id);
                    break;
                case 'REPO':
                    echo print_addnewrepository_link($element_id);
                    break;
                case 'NOTE':
                    // Shared Notes Icons ========================================
                    if ($islink) {
                        // Print regular Shared Note icons ---------------------------
                        echo print_addnewnote_link($element_id);

                        // If census_assistant module exists && we are on the INDI page and the action is a census assistant addition.
                        // Then show the add Shared note assisted icon, if not  ... show regular Shared note icons.
                        if (($action == 'add' || $action == 'edit') && $pid && array_key_exists('census_assistant', KT_Module::getActiveModules())) {
                            // Check if a CENS event ---------------------------
                            if ($event_add == 'census_add') {
                                $type_pid = KT_GedcomRecord::getInstance($pid);
                                if ($type_pid->getType() == 'INDI' ) {
                                    echo '
                                        <div>
                                            <a href="#" style="display: none;" id="assistant-link" onclick="return activateCensusAssistant();">' .
                                                KT_I18N::translate('Create a shared note using the census assistant') . '
                                            </a>
                                        </div>
                                    ';
                                }
                            }
                        }
                    }
                    break;
                case 'OBJE':
                    if (!$value) {
                        echo print_addnewmedia_link($element_id);
                        $value = 'new';
                    }
                    break;
            } ?>

        </span>
    <?php }

}

/**
 * Specialised input types
 *
 * @param string $fact
 * @param string $value
 * 
**/
function otherInputs($fact, $level, $element_id, $upperlevel, $tags, $pid, $element_name, $value, $specialchar, $emptyfacts)
{

    global $iconStyle;

    // split PLAC
    if ($fact === "PLAC") { ?>
        <span class="input-group-label">
            <?php echo print_specialchar_link($element_id); ?>
        </span>
        <span 
            class="input-group-label" 
            onclick="jQuery('div[id^=<?php echo $upperlevel; ?>_LATI], div[id^=<?php echo $upperlevel; ?>_LONG], div[id^=INDI_LATI], div[id^=INDI_LONG], div[id^=LATI], div[id^=LONG]').toggle('fast'); return false;" 
            title="<?php echo KT_Gedcom_Tag::getLabel('LATI'); ?> / <?php echo KT_Gedcom_Tag::getLabel('LONG'); ?>" 
            data-tooltip 
            data-position="top" 
            data-alignment="center"
        >
            <i class="<?php echo $iconStyle; ?> fa-location-dot"></i>
        </span>

    <?php } elseif (!in_array($fact, $specialchar) && !in_array($fact, $emptyfacts) && ('' !== $value || 'Y' !== $value || 'y' !== $value)) { ?>
        <span class="input-group-label">
            <?php echo print_specialchar_link($element_id); ?>
        </span>
    <?php }

    // MARRiage TYPE : hide text field and show a selection list
    if ($fact == 'TYPE' && $level == 2 && $tags[0] == 'MARR') {
        echo '<script>
            document.getElementById("' . $element_id . '").style.display="none"
        </script>
        <select id="' . $element_id . '_sel" onchange="document.getElementById(\'' . $element_id . '\').value=this.value;" >';
            foreach (array("Unknown", "Civil", "Religious", "Partners", "Common") as $indexval => $key) {
                if ($key == "Unknown") {
                    echo '<option value=""';
                } else {
                    echo '<option value="' . $key . '"';
                }
                    $a = strtolower($key);
                    $b = strtolower($value);
                    if (@strpos($a, $b) !== false || @strpos($b, $a) !== false) {
                        echo ' selected="selected"';
                    }
                    $tmp = "MARR_" . strtoupper($key);
                echo '>' .
                    KT_Gedcom_Tag::getLabel($tmp) . '
                </option>';
            }
        echo '</select>';
    } else if ($fact == 'TYPE' && $level == 0) {
        // NAME TYPE : hide text field and show a selection list
        $onchange = 'onchange="document.getElementById(\'' . $element_id . '\').value=this.value;"';
        switch (KT_Person::getInstance($pid)->getSex()) {
            case 'M':
                echo edit_field_name_type_m($element_name, $value, $onchange);
                break;
            case 'F':
                echo edit_field_name_type_f($element_name, $value, $onchange);
                break;
            default:
                echo edit_field_name_type_u($element_name, $value, $onchange);
                break;
        }
        echo '
            <script>
                document.getElementById("' . $element_id . '").style.display="none";
            </script>
        ';
    }

}

/**
 * Checkboxes to apply '1 SOUR' to BIRT/MARR/DEAT as '2 SOUR'
 *
 * @param string $fact
 * @param string $value
 * 
**/
function sourceLinks($bdm)
{

    if ($PREFER_LEVEL2_SOURCES === '0') {
        $level1_checked = '';
        $level2_checked = '';
    } else if ($PREFER_LEVEL2_SOURCES === '1' || $PREFER_LEVEL2_SOURCES === true) {
        $level1_checked = '';
        $level2_checked = ' checked="checked"';
    } else {
        $level1_checked = ' checked="checked"';
        $level2_checked = '';

    }

    if (strpos($bdm, 'B') !== false) {
        echo '
            <p>
                <input type="checkbox" name="SOUR_INDI" ', $level1_checked, ' value="Y">',
                KT_I18N::translate('Individual'),
            '</p>';
        if (preg_match_all('/('.KT_REGEX_TAG.')/', $QUICK_REQUIRED_FACTS, $matches)) {
            foreach ($matches[1] as $match) {
                if (!in_array($match, explode('|', KT_EVENTS_DEAT))) {
                    echo '
                        <p>
                            <input type="checkbox" name="SOUR_', $match, '"', $level2_checked, ' value="Y">',
                            KT_Gedcom_Tag::getLabel($match),
                        '</p>';
                }
            }
        }
    }

    if (strpos($bdm, 'D') !== false) {
        if (preg_match_all('/('.KT_REGEX_TAG.')/', $QUICK_REQUIRED_FACTS, $matches)) {
            foreach ($matches[1] as $match) {
                if (in_array($match, explode('|', KT_EVENTS_DEAT))) {
                    echo '
                        <p>
                            <input type="checkbox" name="SOUR_', $match, '"', $level2_checked, ' value="Y">',
                            KT_Gedcom_Tag::getLabel($match),
                        '</p>';
                }
            }
        }
    }

    if (strpos($bdm, 'M') !== false) {
        echo '
            <p>
                <input type="checkbox" name="SOUR_FAM" ', $level1_checked, ' value="Y">',
                KT_I18N::translate('Family'),
            '</p>';
        if (preg_match_all('/('.KT_REGEX_TAG.')/', $QUICK_REQUIRED_FAMFACTS, $matches)) {
            foreach ($matches[1] as $match) {
                echo '
                    <p>
                        <input type="checkbox" name="SOUR_', $match, '"', $level2_checked, ' value="Y">',
                        KT_Gedcom_Tag::getLabel($match),
                    '</p>';
            }
        }
    }

}

/**
 * Input field placeholder texts
 *
 * @param string $fact
 * @param string $value
 * 
**/
function placeholder($fact)
{  

    switch ($fact) {
        case 'AGE':
            return 'placeholder="33y 5m 2d"';
            break;
        
        default:
            break;
    }
}
