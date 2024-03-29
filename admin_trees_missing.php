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

define('KT_SCRIPT_NAME', 'admin_trees_missing.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
require KT_ROOT . 'includes/functions/functions_print_facts.php';
include KT_THEME_URL . 'templates/adminData.php';

global $DEFAULT_PEDIGREE_GENERATIONS, $iconStyle;

$controller = new KT_Controller_Page();
$controller
    ->requireManagerLogin()
    ->setPageTitle(KT_I18N::translate('Missing fact or event details'))
    ->pageHeader()
    ->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
    ->addInlineJavascript('autocomplete();');

//-- variables
$fact             = KT_Filter::post('fact');
$go               = KT_Filter::post('go');
$rootid           = KT_Filter::get('rootid');
$root_id          = KT_Filter::post('root_id');
$rootid           = empty($root_id) ? $rootid : $root_id;
$choose_relatives = KT_Filter::post('choose_relatives') ? KT_Filter::post('choose_relatives') : 'child-family';
$maxgen           = KT_Filter::post('generations', KT_REGEX_INTEGER, $DEFAULT_PEDIGREE_GENERATIONS);
$person           = KT_Person::getInstance($rootid) ? KT_Person::getInstance($rootid) : '';
$personName       = $person ? strip_tags($person->getLifespanName()) : '';
$gedID 	          = KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
$tree             = KT_Tree::getNameFromId($gedID);

//-- set list of all configured individual tags (level 1)
$indifacts      = preg_split("/[, ;:]+/", get_gedcom_setting($gedID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
$uniqueIndfacts = preg_split("/[, ;:]+/", get_gedcom_setting($gedID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
$indifacts      = array_merge($indifacts, $uniqueIndfacts);

$famfacts       = preg_split("/[, ;:]+/", get_gedcom_setting($gedID, 'FAM_FACTS_ADD'),     -1, PREG_SPLIT_NO_EMPTY);
$uniqueFamfacts = preg_split("/[, ;:]+/", get_gedcom_setting($gedID, 'FAM_FACTS_UNIQUE'),  -1, PREG_SPLIT_NO_EMPTY);
$famfacts       = array_merge($famfacts, $uniqueFamfacts);

$facts = array_merge($indifacts, $famfacts);

$translated_facts = array();
foreach ($facts as $addfact) {
    $translated_facts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
}
uasort($translated_facts, 'factsort');

$select = array(
    'child-family'     => KT_I18N::translate('Parents and siblings'),
    'spouse-family'    => KT_I18N::translate('Spouses and children'),
    'direct-ancestors' => KT_I18N::translate('Direct line ancestors'),
    'ancestors'        => KT_I18N::translate('Direct line ancestors and their families'),
    'descendants'      => KT_I18N::translate('Descendants'),
    'all'              => KT_I18N::translate('All relatives')
);

$generations = array(
    1  => KT_I18N::number(1),
    2  => KT_I18N::number(2),
    3  => KT_I18N::number(3),
    4  => KT_I18N::number(4),
    5  => KT_I18N::number(5),
    6  => KT_I18N::number(6),
    7  => KT_I18N::number(7),
    8  => KT_I18N::number(8),
    9  => KT_I18N::number(9),
    10 => KT_I18N::number(10),
    -1 => KT_I18N::translate('All')
);

$false = '<i class="alert ' . $iconStyle . ' fa-xmark"></i>';
$true  = '<i class="success ' . $iconStyle . ' fa-check"></i>';

echo relatedPages($trees, KT_SCRIPT_NAME);?>

<div id="missing_data-page" class="cell">
    <h4><?php echo $controller->getPageTitle(); ?></h4>
    <h5><?php echo /* I18N: Sub-title for missing data admin page */ KT_I18N::translate('A list of information missing from events or facts of an individual and their relatives.'); ?></h5>
    <div class="cell callout info-help ">
        <?php echo /* I18N: Help content for missing data admin page */ KT_I18N::translate('Whenever possible names are followed by the individual\'s lifespan dates for ease of identification. Note that these may include dates of baptism, christening, burial and cremation if birth and death dates are missing.<br>The list also ignores any estimates of dates or ages, so living people will be listed as missing death dates and places.<br>Some facts such as "Religion" do not commonly have sub-tags like date, place or source, so here only the fact itself is checked for.<br><b>Limitations: </b>It is only practical for this tool to search for media objects directly linked to the selected event ("level 2 object"). Other media such as attached directly to the individual (level 1 object), or linked to a source itself linked to the event ("level 3 object"), will not be checked here.'); ?>
    </div>
    <div class="grid-x grid-margin-x hide-for-print">
        <form class="cell" method="post"  name="tree" action="<?php echo KT_SCRIPT_NAME; ?>">
            <input type="hidden" name="go" value="1">
            <div class="grid-x grid-padding-x">
                <div class="cell medium-3">
                    <label><?php echo KT_I18N::translate('Family tree'); ?></label>
                    <?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="tree.submit();"'); ?>
                </div>
                <div class="cell medium-3">
                    <label for="select-missing"><?php echo KT_I18N::translate('Individual'); ?></label>
                    <?php echo autocompleteHtml(
                        'missing', // id suffix
                        'INDI', // TYPE
                        $tree , // autocomplete-ged
                        $personName, // input value
                        '', // placeholder
                        'root_id', // hidden input name
                        $rootid, // hidden input value
                        'required', // Optional required setting
                        '', // optional other entry
                    ); ?>
                </div>
                <div class="cell medium-3">
                    <label for = "fact"><?php echo KT_I18N::translate('Fact or event'); ?></label>
                    <select name="fact" id="fact" required>
                        <option value="fact" disabled selected ><?php echo /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'); ?></option>
                        <?php foreach ($translated_facts as $key => $fact_name) {
                            if ($key !== 'EVEN' && $key !== 'FACT') {
                                echo '<option value="' . $key . '"' . ($key == $fact ? ' selected ' : '') . '>' . $fact_name . '</option>';
                            }
                        }
                        echo '<option value="EVEN"' . ($fact == 'EVEN'? ' selected ' : '') . '>' . KT_I18N::translate('Custom event') . '</option>';
                        echo '<option value="FACT"' . ($fact == 'FACT'? ' selected ' : '') . '>' . KT_I18N::translate('Custom Fact') . '</option>';
                        ?>
                    </select>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="cell medium-3">
                    <label for = "choose_relatives"><?php echo KT_I18N::translate('Choose relatives'); ?></label>
                    <?php echo select_edit_control('choose_relatives', $select,	null, $choose_relatives); ?>
                </div>
                <div class="cell medium-2">
                    <label for = "generations"><?php echo KT_I18N::translate('Generations'); ?></label>
                    <?php echo select_edit_control('generations', $generations, null, $maxgen); ?>
                </div>
                <div class="cell medium-2">
                    <button type="submit" class="button" >
                        <i class="<?php echo $iconStyle; ?> fa-check"></i>
                        <?php echo KT_I18N::translate('Show'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- end of form -->
    <?php if ($go == 1) {
        $controller
            ->addExternalJavascript(KT_DATATABLES_JS)
        	->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
        	->addExternalJavascript(KT_DATATABLES_BUTTONS)
        	->addExternalJavascript(KT_DATATABLES_HTML5)
            ->addInlineJavascript('
                jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
                jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
                jQuery("#missing_data").dataTable({
                    dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
                    ' . KT_I18N::datatablesI18N() . ',
                    buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
                    autoWidth: false,
                    pagingType: "full_numbers",
                    lengthChange: true,
                    filter: true,
                    info: true,
                    sorting: [0,"asc"],
                    displayLength: 20,
                    stateSave: true,
                    stateDuration: -1
                });
            ');

        // prepare final list
        $result = array();
        $check  = array();
        $list   = array();
        $note   = '';
        $n      = 0;

        if ($person && $person->canDisplayDetails()) {
            $list = getRelatives($rootid, $person, $choose_relatives, $maxgen);

            foreach ($list as $relative) {
                if (in_array($fact, $after_death) && !$relative->isDead()) {
                    $n ++;
                    continue;
                }
                if (in_array($fact, $famfacts)) {
                    $fam_record = array();
                    // collect FAMS records for this person
                    $ct = preg_match_all('/\n1 FAMS @(.+)@/', $relative->getGedcomRecord(), $matches, PREG_SET_ORDER); // collect family info for FAM records ($matches)
                    foreach ($matches as $match) {
                        if (!in_array($match[1], $check)) {
                            $check[]      = $match[1]; // avoid duplicate data from both spouses
                            $fam_record[] = KT_Family::getInstance($match[1]);
                        }
                    }
                    foreach ($fam_record as $family) {
                        $event = $family->getFactByType($fact);
                        if ($event) {
                            $check_event = check_events($event);
                            if ($check_event['date'] && $check_event['place'] && $check_event['source'] && $check_event['obje']) {
                            continue;
                            } else {
                                $result[$family->getXref()]['gen']                             = $relative->getGeneration();
                                $result[$family->getXref()]['name']                            = '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
                                $result[$family->getXref()]['fact']                            = 1;
                                $check_event['date']	? $result[$family->getXref()]['date']     = 1 : $result[$family->getXref()]['date']     = 0;
                                $check_event['place']	? $result[$family->getXref()]['place']   = 1 : $result[$family->getXref()]['place']   = 0;
                                $check_event['source']	? $result[$family->getXref()]['source'] = 1 : $result[$family->getXref()]['source'] = 0;
                                $check_event['obje']	? $result[$family->getXref()]['obje']     = 1 : $result[$family->getXref()]['obje']     = 0;
                            }
                        } else {
                            $result[$family->getXref()]['gen']    = $relative->getGeneration();
                            $result[$family->getXref()]['name']   = '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
                            $result[$family->getXref()]['fact']   = 0;
                            $result[$family->getXref()]['date']   = 0;
                            $result[$family->getXref()]['place']  = 0;
                            $result[$family->getXref()]['source'] = 0;
                            $result[$family->getXref()]['obje']   = 0;
                        }
                    }
                } else {
                    $event = $relative->getFactByType($fact);
                    if ($event) {
                        $check_event = check_events($event);
                        if ($check_event['date'] && $check_event['place'] && $check_event['source'] && $check_event['obje']) {
                            continue;
                        } else {
                            $result[$relative->getXref()]['gen']                             = $relative->getGeneration();
                            $result[$relative->getXref()]['name']                            = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
                            $result[$relative->getXref()]['fact']                            = 1;
                            $check_event['date']	? $result[$relative->getXref()]['date']     = 1 : $result[$relative->getXref()]['date']     = 0;
                            $check_event['place']	? $result[$relative->getXref()]['place']   = 1 : $result[$relative->getXref()]['place']   = 0;
                            $check_event['source']	? $result[$relative->getXref()]['source'] = 1 : $result[$relative->getXref()]['source'] = 0;
                            $check_event['obje']	? $result[$relative->getXref()]['obje']     = 1 : $result[$relative->getXref()]['obje']     = 0;
                        }
                    } else {
                        $result[$relative->getXref()]['gen']    = $relative->getGeneration();
                        $result[$relative->getXref()]['name']   = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
                        $result[$relative->getXref()]['fact']   = 0;
                        $result[$relative->getXref()]['date']   = 0;
                        $result[$relative->getXref()]['place']  = 0;
                        $result[$relative->getXref()]['source'] = 0;
                        $result[$relative->getXref()]['obje']   = 0;
                    }
                }
            } ?>

            <!-- output results as table  -->
            <hr>
            <h5><?php echo /* I18N: heading for report on missing data */ KT_I18N::translate('%1s related to %2s ', $select[$choose_relatives], $person->getLifespanName()); ?></h5>
            <h5 class="subheader"><?php echo /* I18N: sub-heading for report on missing data listing selected event types */ KT_I18N::translate('Missing <u>%s</u> data', strtolower(KT_Gedcom_Tag::getLabel($fact))); ?></h5>
            <?php if ($n > 0) { ?>
                <div class="callout warning">
                    <?php echo KT_I18N::plural('<b>Note: </b>%s person excluded as they are, or are believed to be, still living', '<b>Note: </b>%s people excluded as they are, or are believed to be, still living', $n, $n); ?>
                </div>
            <?php } ?>
            <table id="missing_data">
                <thead>
                    <tr>
                        <th><span title="<?php echo KT_I18N::translate('Generation'); ?>"><?php echo /* I18N: Short abbrevisation for "Generation" */ KT_I18N::translate('Gen'); ?></span></th>
                        <th><?php echo KT_I18N::translate('Name'); ?></th>
                        <th><?php echo KT_Gedcom_Tag::getLabel($fact); ?></th>
                        <th><?php echo KT_I18N::translate('Date'); ?></th>
                        <th><?php echo KT_I18N::translate('Place'); ?></th>
                        <th><?php echo KT_I18N::translate('Source'); ?></th>
                        <th><?php echo KT_I18N::translate('Media'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $relative) { ?>
                        <tr>
                            <td><?php  echo $relative['gen']; ?></td>
                            <td><?php  echo $relative['name']; ?></td>
                            <td><span style="visibility:hidden;"><?php  echo $relative['fact']; ?></span><?php  echo $relative['fact']     == 1 ? $true : $false; ?></td>
                            <td><span style="visibility:hidden;"><?php  echo $relative['date']; ?></span><?php  echo $relative['date']     == 1 ? $true : $false; ?></td>
                            <td><span style="visibility:hidden;"><?php  echo $relative['place']; ?></span><?php  echo $relative['place']   == 1 ? $true : $false; ?></td>
                            <td><span style="visibility:hidden;"><?php  echo $relative['source']; ?></span><?php  echo $relative['source'] == 1 ? $true : $false; ?></td>
                            <td><span style="visibility:hidden;"><?php  echo $relative['obje']; ?></span><?php  echo $relative['obje']     == 1 ? $true : $false; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }
    } ?>
</div> <!-- close missing_data page div -->

<?php
function getRelatives($rootid, $person, $choose_relatives, $maxgen) {
    // collect list of relatives
    $list          = array();
    $list[$rootid] = $person;
    switch ($choose_relatives) {
        case "child-family":
            foreach ($person->getChildFamilies() as $family) {
                $husband = $family->getHusband();
                $wife    = $family->getWife();
                if (!empty($husband)) {
                    $list[$husband->getXref()] = $husband;
                }
                if (!empty($wife)) {
                    $list[$wife->getXref()] = $wife;
                }
                $children = $family->getChildren();
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $list[$child->getXref()] = $child;
                    }
                }
            }
            break;
        case "spouse-family":
            foreach ($person->getSpouseFamilies() as $family) {
                $husband = $family->getHusband();
                $wife    = $family->getWife();
                if (!empty($husband)) {
                    $list[$husband->getXref()] = $husband;
                }
                if (!empty($wife)) {
                    $list[$wife->getXref()] = $wife;
                }
                $children = $family->getChildren();
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $list[$child->getXref()] = $child;
                    }
                }
            }
            break;
        case "direct-ancestors":
            add_ancestors($list, $rootid, false, $maxgen);
            break;
        case "ancestors":
            add_ancestors($list, $rootid, true, $maxgen);
            break;
        case "descendants":
            $list[$rootid]->generation = 1;
            add_descendancy($list, $rootid, false, $maxgen);
            break;
        case "all":
            add_ancestors($list, $rootid, true, $maxgen);
            add_descendancy($list, $rootid, true, $maxgen);
            break;
    }

    return $list;

}

function check_events ($event) {
    $date = $place = $source = $obje = false;

    if ($event && $event->getDate()->JD() != 0) {
    $date = true;
    }

    if ($event && $event->getPlace()) {
    $place = true;
    }

    $event ? $ct_s = preg_match_all("/\d SOUR @(.*)@/", $event->getGedcomRecord(), $match) : $ct_s = 0;
    if ($ct_s != 0) {
    $source = true;
    }

    $event ? $ct_o = preg_match_all("/\d OBJE @(.*)@/", $event->getGedcomRecord(), $match) : $ct_o = 0;
    if ($ct_o != 0) {
    $obje = true;
    }

    return array('date' => $date, 'place' => $place, 'source' => $source, 'obje' => $obje);
}
