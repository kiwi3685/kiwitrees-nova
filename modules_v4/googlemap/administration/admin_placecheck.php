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

 require KT_ROOT . 'includes/functions/functions_edit.php';

 $action	= KT_Filter::get('action', '','go');
 $gedcom_id	= KT_Filter::get('gedcom_id', '', KT_GED_ID);
 $country	= KT_Filter::get('country', KT_REGEX_UNSAFE, 'XYZ');
 $state		= KT_Filter::get('state', KT_REGEX_UNSAFE, 'XYZ');
 $matching	= KT_Filter::getBool('matching');
 $par_id	= array();

 if (!empty($KT_SESSION['placecheck_gedcom_id'])) {
     $gedcom_id = $KT_SESSION['placecheck_gedcom_id'];
 } else {
     $KT_SESSION['placecheck_gedcom_id'] = $gedcom_id;
 }

 if (!empty($KT_SESSION['placecheck_country'])) {
     $country = $KT_SESSION['placecheck_country'];
 } else {
     $KT_SESSION['placecheck_country'] = $country;
 }

 if (!empty($KT_SESSION['placecheck_state'])) {
     $state = $KT_SESSION['placecheck_state'];
 } else {
     $KT_SESSION['placecheck_state'] = $state;
 }

 $controller = new KT_Controller_Page();
 $controller
     ->restrictAccess(KT_USER_IS_ADMIN)
     ->setPageTitle(KT_I18N::translate('Google Maps™'))
     ->pageHeader();

 ?>

<div id="gm_config" class="cell">
    <h4><?php echo $controller->getPageTitle(); ?></h4>

    <ul class="tabs" id="gm_pages">
        <li class="tabs-title medium-4 text-center">
            <a href="module.php?mod=googlemap&amp;mod_action=admin_config">
                <?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
            </a>
        </li>
        <li class="tabs-title text-center">
            <a href="module.php?mod=googlemap&amp;mod_action=admin_places">
                <?php echo KT_I18N::translate('Geographic data'); ?>
            </a>
        </li>
        <li class="tabs-title text-center is-active">
            <a href="module.php?mod=googlemap&amp;mod_action=admin_placecheck" class="current" aria-selected="true">
                <?php echo KT_I18N::translate('Place check'); ?>
            </a>
        </li>
    </ul>

    <div class="grid-x grid-margin-x grid-margin-y" id="gm_check">
        <form class="cell" method="get" name="placecheck" action="module.php">
            <input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
            <input type="hidden" name="mod_action" value="admin_placecheck">

            <div class="grid-x grid-margin-x">
                 <div class="cell medium-1">
                     <label>
                         <?php echo KT_I18N::translate('Family tree'); ?>
                     </label>
                 </div>
                <div class="cell medium-2">
                    <?php echo select_edit_control('gedcom_id', KT_Tree::getIdList(), null, $gedcom_id, ' onchange="this.form.submit();"'); ?>
                </div>
                <div class="cell medium-1">
                    <label>
                        <?php echo KT_I18N::translate('Country'); ?>
                    </label>
                </div>
                <div class="cell medium-2">
                    <select name="country" onchange="this.form.submit();">
                        <option value="XYZ" selected="selected">
                            <?php echo /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'); ?>
                        </option>
                        <option value="XYZ">
                            <?php echo KT_I18N::translate('All'); ?>
                        </option>
                        <?php $rows = KT_DB::prepare("
                            SELECT pl_id, pl_place
                            FROM `##placelocation`
                            WHERE pl_level=0 ORDER BY pl_place
                        ")->fetchAssoc();
                        foreach ($rows as $id => $place) { ?>
                            <option value="<?php echo htmlspecialchars((string) $place); ?>"
                                <?php if ($place == $country) {
                                    echo ' selected="selected"';
                                    $par_id = $id;
                                } ?>
                            >
                                <?php echo htmlspecialchars((string) $place); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <?php
                // Optional sCountry subdivisions
                if ($country != 'XYZ') { ?>
                    <div class="cell medium-1">
                        <label>
                            <?php echo /* I18N: Part of a country, state/region/county */ KT_I18N::translate('Subdivision'); ?>
                        </label>
                    </div>
                    <div class="cell medium-2">
                        <select name="state" onchange="this.form.submit();">
                            <option value="XYZ" selected="selected">
                                <?php echo KT_I18N::translate('Select'); ?></option>
                            <option value="XYZ">
                                <?php echo KT_I18N::translate('All'); ?>
                            </option>
                            <?php $places = KT_DB::prepare("
                                SELECT pl_place
                                FROM `##placelocation`
                                WHERE pl_parent_id=?
                                ORDER BY pl_place
                            ")->execute(array($par_id))->fetchOneColumn();
                            foreach ($places as $place) { ?>
                                <option value="<?php echo htmlspecialchars((string) $place); ?>"
                                    <?php if ($place == $state) {
                                        echo ' selected="selected"';
                                    } ?>
                                >
                                    <?php echo htmlspecialchars((string) $place); ?>
                                </option>
                            <?php } ?>
                          </select>
                    </div>
                <?php } ?>
                <div class="cell medium-3">
                    <div class="checkbox">
                        <label>
                            <?php echo KT_I18N::translate('Include fully matched places: '); ?>
                            <input type="checkbox" name="matching" value="1" onchange="this.form.submit();"
                                <?php if ($matching) {
                                    echo ' checked="checked"';
                                } ?>
                            >
                        </label>
                    </div>
                </div>
            </div>
        </form>
        <hr class="cell">

        <div class="cell">

            <?php if ($action === 'go') {
                $controller
             		->addExternalJavascript(KT_DATATABLES_JS)
             		->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
            		->addExternalJavascript(KT_DATATABLES_BUTTONS)
            		->addExternalJavascript(KT_DATATABLES_HTML5)
                    ->addInlineJavascript('
                         jQuery("#gm_check_details").dataTable({
                             dom: \'<"H"<"filtersH_gm_check_details">T<"clear">pBf<"clear">irl>t<"F"pl<"clear"><"filtersF_gm_check_details">>\',
                             ' . KT_I18N::datatablesI18N() . ',
                             buttons: [{extend: "csvHtml5"}],
                             processing: true,
                             retrieve: true,
                             displayLength: 20,
                             pagingType: "full_numbers",
                             stateSave: true,
                             stateSaveParams: function (settings, data) {
                                 data.columns.forEach(function(column) {
                                     delete column.search;
                                 });
                             },
                             stateDuration: -1,
                         });
                         jQuery("#gm_check_details").css("visibility", "visible");
                         jQuery(".loading-image").css("display", "none");
                     ');

                     //Select all '2 PLAC ' tags in the file and create array
                     $place_list = array();
                     $ged_data = KT_DB::prepare("
                        SELECT i_gedcom
                        FROM `##individuals`
                        WHERE i_gedcom LIKE ?
                        AND i_file=?
                     ")
                         ->execute(array("%\n2 PLAC %", $gedcom_id))
                         ->fetchOneColumn();

                     foreach ($ged_data as $ged_datum) {
                         preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
                         foreach ($matches[1] as $match) {
                             $place_list[$match]=true;
                         }
                     }

                     $ged_data = KT_DB::prepare("
                        SELECT f_gedcom
                        FROM `##families`
                        WHERE f_gedcom LIKE ? AND f_file=?
                     ")
                         ->execute(array("%\n2 PLAC %", $gedcom_id))
                         ->fetchOneColumn();

                     foreach ($ged_data as $ged_datum) {
                         preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
                         foreach ($matches[1] as $match) {
                             $place_list[$match] = true;
                         }
                     }

                     // Unique list of places
                     $place_list = array_keys($place_list);

                     // Apply_filter
                     if ($country == 'XYZ') {
                         $filter = '.*$';
                     } else {
                         $filter = preg_quote($country) . '$';
                         if ($state != 'XYZ') {
                             $filter = preg_quote($state) . ', ' . $filter;
                         }
                     }

                     $place_list = preg_grep('/' . $filter . '/', $place_list);

                     //sort the array, limit to unique values, and count them
                     $place_parts = array();
                     usort($place_list, "utf8_strcasecmp");
                     $i = count($place_list);

                     // Calculate maximum no. of levels to display
                     $x     = 0;
                     $max   = 0;
                     while ($x < $i) {
                         $levels    = explode(",", $place_list[$x]);
                         $parts     = count($levels);
                         if ($parts > $max) {
                             $max = $parts;
                         }
                         $x ++;
                     }
                     $x = 0;

                     //start to produce the display table
                     $cols  = 0;
                     $span  = $max * 3 + 3; ?>
                     <div class="loading-image">&nbsp;</div>

                     <table id="gm_check_details">
                         <thead>
                             <tr>
                                 <th rowspan="3"><?php echo KT_I18N::translate('Family tree place'); ?></th>
                                 <th class="text-center" colspan="<?php echo $span; ?>"><?php echo KT_I18N::translate('Google Maps location data'); ?></th>
                             </tr>
                             <tr>
                                <?php  while ($cols < $max) {
                                     if ($cols == 0) { ?>
                                         <th class="text-center" colspan="3"><?php echo KT_I18N::translate('Country'); ?></th>
                                     <?php } else { ?>
                                         <th class="text-center" colspan="3"><?php echo KT_I18N::translate('Level'); ?>&nbsp;<?php echo $cols+1; ?></th>
                                     <?php }
                                     $cols ++;
                                 } ?>
                             </tr>
                             <tr>
                                 <?php
                                 $cols = 0;
                                 while ($cols < $max) { ?>
                                     <th class="place"><?php echo KT_Gedcom_Tag::getLabel('PLAC'); ?></th>
                                     <th class="latlong"><?php echo /* I18N 3-character abbreviation for Latitude */ KT_I18N::translate('Lat.'); ?></th>
                                     <th class="latlong"><?php echo /* I18N 3-character abbreviation for Longitude */KT_I18N::translate('Lon.'); ?></th>
                                     <?php $cols ++;
                                 } ?>
                             </tr>
                         </thead>
                         <tbody>
                             <?php
                             $countrows = 0;
                             while ($x < $i) {
                                 $placestr 	= '';
                                 $levels 	= explode(', ', $place_list[$x]);
                                 $parts		= count($levels);
                                 $levels	= array_reverse($levels);
                                 $placestr	.= '<a href="placelist.php?action=show';

                                 foreach ($levels as $pindex=>$ppart) {
                                     $placestr .= '&amp;parent[' . $pindex . ']=' . urlencode($ppart);
                                 }

                                 $placestr		.= '">' . $place_list[$x] . "</a>";
                                 $gedplace		= '<tr><td>' . $placestr . '</td>';
                                 $prev_lati		= 1;
                                 $z				= 0;
                                 $y				= 0;
                                 $id			= 0;
                                 $level			= 0;
                                 $matched[$x]	= 0;// used to exclude places where the gedcom place is matched at all levels
                                 $mapstr_edit	= '<a href="#" dir="auto" onclick="edit_place_location(\'';
                                 $mapstr_add	= '<a href="#" dir="auto" onclick="add_place_location(\'';
                                 $mapstr3		= '';
                                 $mapstr4   	= '';
                                 $mapstr5		= '\')" title=\'';
                                 $mapstr6		= '\' >';
                                 $mapstr7		= '\')">';
                                 $mapstr8		= '</a>';

                                 while ($z < $parts) {
                                     if ($levels[$z] == ' ' || $levels[$z] == '')
                                         $levels[$z] = 'unknown'; // GoogleMap module uses "unknown" while GEDCOM uses , ,

                                     $levels[$z] = rtrim(ltrim($levels[$z]));
                                     $placelist	= create_possible_place_names($levels[$z], $z+1); // add the necessary prefix/postfix values to the place name
                                      foreach ($placelist as $key => $placename) {
                                         $row =  KT_DB::prepare("
                                            SELECT pl_id, pl_place, pl_long, pl_lati, pl_zoom
                                            FROM `##placelocation`
                                            WHERE pl_level=?
                                            AND pl_parent_id=?
                                            AND pl_place LIKE ?
                                            ORDER BY pl_place
                                        ") ->execute(array($z, $id, $placename))
                                           ->fetchOneRow(PDO::FETCH_ASSOC);

                                         if (!empty($row['pl_id'])) {
                                             $row['pl_placerequested'] = $levels[$z]; // keep the actual place name that was requested so we can display that instead of what is in the db
                                             break;
                                         }
                                     }

                                     if (!empty($row['pl_id'])) {
                                         $id = $row['pl_id'];
                                     }

                                     if (!empty($row['pl_place'])) {
                                         $placestr2 = $mapstr_edit . $id . '&amp;level=' . $level . $mapstr3 . $mapstr5 . KT_I18N::translate('Zoom=') . $row['pl_zoom'] . $mapstr6 . $row['pl_placerequested'] . $mapstr8;
                                         if ($row['pl_place'] == 'unknown')
                                             $matched[$x] ++;
                                     } else {
                                         if ($levels[$z] == 'unknown') {
                                             $placestr2 = $mapstr_add . $id . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<strong>' . rtrim(ltrim(KT_I18N::translate('unknown'))) . "</strong>" . $mapstr8;
                                             $matched[$x] ++;
                                         } else {
                                             $placestr2 = $mapstr_add . $id . '&amp;place_name=' . urlencode($levels[$z]) . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<span class="alert">' . rtrim(ltrim($levels[$z])) . '</span>' . $mapstr8;
                                             $matched[$x] ++;
                                         }
                                     }

                                     if ($prev_lati == 0) { // no link to edit if parent has no coordinates
                                         $plac[$z] = '<td>
                                             <span
                                                data-tooltip class="top "
                                                title="' . KT_I18N::translate('
                                                    Coordinates can not be added here until the parent place has coordinates.
                                                    ') . '
                                                ">' .
                                                    $levels[$z] . '
                                            </span>
                                         </td>';
                                     } else {
                                         $plac[$z] = '<td>' . $placestr2 . '</td>';
                                     }

                                     if (!empty($row['pl_lati']) && $row['pl_lati'] == '0') {
                                         $lati[$z] = '<td class="alert"><strong>' . $row['pl_lati'] . '</strong></td>';
                                     } elseif (!empty($row['pl_lati']) && $row['pl_lati'] <> '0') {
                                         $lati[$z] = '<td>' . $row['pl_lati'] . '</td>';
                                     } else {
                                         $lati[$z] = '<td class="alert center"><strong>X</strong></td>';
                                         $prev_lati = 0;
                                         $matched[$x]++;
                                     }
                                     if (!empty($row['pl_long']) && $row['pl_long'] == '0') {
                                         $long[$z] = '<td class="alert"><strong>' . $row['pl_long'] . '</strong></td>';
                                     } elseif (!empty($row['pl_long'])  && $row['pl_long'] <> '0') {
                                         $long[$z] = '<td>' . $row['pl_long'] . '</td>';
                                     } else {
                                         $long[$z] = "<td class='alert center'><strong>X</strong></td>";
                                         $matched[$x]++;
                                     }
                                     $level++;
                                     if (!empty($row['pl_placerequested'])) {
                                         $mapstr3 = $mapstr3 . '&amp;parent[' . $z . ']=' . addslashes($row['pl_placerequested']);
                                     }
                                     $mapstr4 = $mapstr4 . '&amp;parent[' . $z . ']=' . addslashes(rtrim(ltrim($levels[$z])));
                                     $z++;
                                 }
                                 if ($matching) {
                                     $matched[$x] = 1;
                                 }
                                 if ($matched[$x] != 0) {
                                     echo $gedplace;
                                     $z = 0;
                                     while ($z < $max) {
                                         if ($z < $parts) {
                                             echo $plac[$z];
                                             echo $lati[$z];
                                             echo $long[$z];
                                         } else { ?>
                                             <td>&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td>&nbsp;</td>
                                         <?php }
                                         $z ++;
                                     } ?>
                                     </tr>
                                     <?php $countrows ++;
                                 }
                                 $x ++;
                             } ?>
                         </tbody>
                     </table>
                 <?php } ?>
            </div>
        </div>
    </div>

 <?php

 //scripts for edit, add and refresh
 ?>
 <script>
 function edit_place_location(placeid) {
     window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=update&placeid=' + placeid, '_blank');
     return false;
 }

 function add_place_location(placeid) {
     window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=add&placeid=' + placeid, '_blank');
     return false;
 }
 </script>
 <?php
