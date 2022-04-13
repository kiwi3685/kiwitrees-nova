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

 $action		= safe_GET('action', '','go');
 $gedcom_id	= safe_GET('gedcom_id', array_keys(KT_Tree::getAll()), KT_GED_ID);
 $country	= safe_GET('country', KT_REGEX_UNSAFE, 'XYZ');
 $state		= safe_GET('state', KT_REGEX_UNSAFE, 'XYZ');
 $matching	= safe_GET_bool('matching');

 $par_id		= array();

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
 </div>
 <?php
 //Start of User Defined options
 echo '
     <form method="get" name="placecheck" action="module.php">
         <input type="hidden" name="mod" value="', $this->getName(), '">
         <input type="hidden" name="mod_action" value="admin_placecheck">
         <div class="gm_check">
             <label>', KT_I18N::translate('Family tree'), '</label>';
             echo select_edit_control('gedcom_id', KT_Tree::getIdList(), null, $gedcom_id, ' onchange="this.form.submit();"');
             echo '<label>', KT_I18N::translate('Country'), '</label>
             <select name="country" onchange="this.form.submit();">
                 <option value="XYZ" selected="selected">', /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'), '</option>
                 <option value="XYZ">', KT_I18N::translate('All'), '</option>';
                     $rows=KT_DB::prepare("SELECT pl_id, pl_place FROM `##placelocation` WHERE pl_level=0 ORDER BY pl_place")
                         ->fetchAssoc();
                     foreach ($rows as $id=>$place) {
                         echo '<option value="', htmlspecialchars($place), '"';
                         if ($place == $country) {
                             echo ' selected="selected"';
                             $par_id=$id;
                         }
                         echo '>', htmlspecialchars($place), '</option>';
                     }
             echo '</select>';
             if ($country!='XYZ') {
                 echo '<label>', /* I18N: Part of a country, state/region/county */ KT_I18N::translate('Subdivision'), '</label>
                     <select name="state" onchange="this.form.submit();">
                         <option value="XYZ" selected="selected">', KT_I18N::translate('Select'), '</option>
                         <option value="XYZ">', KT_I18N::translate('All'), '</option>';
                         $places=KT_DB::prepare("SELECT pl_place FROM `##placelocation` WHERE pl_parent_id=? ORDER BY pl_place")
                             ->execute(array($par_id))
                             ->fetchOneColumn();
                         foreach ($places as $place) {
                             echo '<option value="', htmlspecialchars($place), '"', $place == $state?' selected="selected"':'', '>', htmlspecialchars($place), '</option>';
                         }
                         echo '</select>';
                     }
             echo '<label>', KT_I18N::translate('Include fully matched places: '), '</label>';
             echo '<input type="checkbox" name="matching" value="1" onchange="this.form.submit();"';
             if ($matching) {
                 echo ' checked="checked"';
             }
             echo '>';
         echo '</div>';// close div gm_check
         echo '<input type="hidden" name="action" value="go">';
     echo '</form>';//close form placecheck
     echo '<hr>';

 switch ($action) {
 case 'go':
     $table_id = 'gm_check_details';
     $controller
         ->addExternalJavascript(KT_DATATABLES_JS)
         ->addExternalJavascript(KT_DATATABLES_HTML5)
         ->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
         ->addInlineJavascript('
             jQuery("#' . $table_id . '").dataTable({
                 dom: \'<"H"<"filtersH_' . $table_id . '">T<"clear">pBf<"clear">irl>t<"F"pl<"clear"><"filtersF_' . $table_id.'">>\',
                 ' . KT_I18N::datatablesI18N() . ',
                 buttons: [{extend: "csv"}],
                 jQueryUI: true,
                 autoWidth: false,
                 pageLength: 20,
                 pagingType: "full_numbers",
                 stateSave: true,
                 stateDuration: 300
             });
             jQuery("#gm_check_details").css("visibility", "visible");
             jQuery(".loading-image").css("display", "none");
         ');
     //Identify gedcom file
     $trees=KT_Tree::getAll();
     echo '<div id="gm_check_title">', $trees[$gedcom_id]->tree_title_html, '</div>';
     //Select all '2 PLAC ' tags in the file and create array
     $place_list=array();
     $ged_data=KT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_gedcom LIKE ? AND i_file=?")
         ->execute(array("%\n2 PLAC %", $gedcom_id))
         ->fetchOneColumn();
     foreach ($ged_data as $ged_datum) {
         preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
         foreach ($matches[1] as $match) {
             $place_list[$match]=true;
         }
     }
     $ged_data=KT_DB::prepare("SELECT f_gedcom FROM `##families` WHERE f_gedcom LIKE ? AND f_file=?")
         ->execute(array("%\n2 PLAC %", $gedcom_id))
         ->fetchOneColumn();
     foreach ($ged_data as $ged_datum) {
         preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
         foreach ($matches[1] as $match) {
             $place_list[$match]=true;
         }
     }
     // Unique list of places
     $place_list=array_keys($place_list);

     // Apply_filter
     if ($country == 'XYZ') {
         $filter='.*$';
     } else {
         $filter=preg_quote($country).'$';
         if ($state!='XYZ') {
             $filter=preg_quote($state).', '.$filter;
         }
     }
     $place_list=preg_grep('/'.$filter.'/', $place_list);

     //sort the array, limit to unique values, and count them
     $place_parts=array();
     usort($place_list, "utf8_strcasecmp");
     $i=count($place_list);

     //calculate maximum no. of levels to display
     $x=0;
     $max=0;
     while ($x<$i) {
         $levels=explode(",", $place_list[$x]);
         $parts=count($levels);
         if ($parts > $max) {
             $max = $parts;
         }
         $x++;
     }
     $x=0;

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

     //start to produce the display table
     $cols=0;
     $span=$max*3+3;
     echo '<div class="loading-image">&nbsp;</div>
     <div class="gm_check_details">
         <table id="gm_check_details" style="width: 100%; visibility: hidden;">
             <thead>
                 <tr>
                     <th rowspan="3">', KT_I18N::translate('Family tree place'), '</th>
                     <th colspan="', $span, '">', KT_I18N::translate('Google Maps location data'), '</th>
                 </tr>
                 <tr>';
                     while ($cols<$max) {
                         if ($cols == 0) {
                             echo '<th colspan="3">', KT_I18N::translate('Country'), '</th>';
                         } else {
                             echo '<th colspan="3">', KT_I18N::translate('Level'), '&nbsp;', $cols+1, '</th>';
                         }
                         $cols++;
                     }
                 echo '</tr>
                 <tr>';
                     $cols=0;
                     while ($cols<$max) {
                         echo '
                             <th>' . KT_Gedcom_Tag::getLabel('PLAC') . '</th>
                             <th>' . KT_I18N::translate('Latitude') . '</th>
                             <th>' . KT_I18N::translate('Longitude') . '</th>';
                         $cols++;
                     }
                 echo '</tr>
             </thead>
             <tbody>';
                 $countrows=0;
                 while ($x<$i) {
                     $placestr 	= '';
                     $levels 	= explode(', ', $place_list[$x]);
                     $parts		= count($levels);
                     $levels		= array_reverse($levels);
                     $placestr	.= '<a href="placelist.php?action=show';
                     foreach ($levels as $pindex=>$ppart) {
                         $placestr .= '&amp;parent[' . $pindex . ']=' . urlencode($ppart);
                     }
                     $placestr		.= '">' . $place_list[$x] . "</a>";
                     $gedplace		= '<tr><td>' . $placestr . '</td>';
                     $prev_lati		= 1;
                     $z				= 0;
                     $y				= 0;
                     $id				= 0;
                     $level			= 0;
                     $matched[$x]	= 0;// used to exclude places where the gedcom place is matched at all levels
                     $mapstr_edit	= '<a href="#" dir="auto" onclick="edit_place_location(\'';
                     $mapstr_add		= '<a href="#" dir="auto" onclick="add_place_location(\'';
                     $mapstr3		= '';
                     $mapstr4   		= '';
                     $mapstr5		= '\')" title=\'';
                     $mapstr6		= '\' >';
                     $mapstr7		= '\')">';
                     $mapstr8		= '</a>';
                     while ($z<$parts) {
                         if ($levels[$z] == ' ' || $levels[$z] == '')
                             $levels[$z] = 'unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,

                         $levels[$z] = rtrim(ltrim($levels[$z]));
                         $placelist	= create_possible_place_names($levels[$z], $z+1); // add the necessary prefix/postfix values to the place name
                         foreach ($placelist as $key=>$placename) {
                             $row =
                                 KT_DB::prepare("SELECT pl_id, pl_place, pl_long, pl_lati, pl_zoom FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
                                 ->execute(array($z, $id, $placename))
                                 ->fetchOneRow(PDO::FETCH_ASSOC);
                             if (!empty($row['pl_id'])) {
                                 $row['pl_placerequested'] = $levels[$z]; // keep the actual place name that was requested so we can display that instead of what is in the db
                                 break;
                             }
                         }

                         if (!empty($row['pl_id'])) { $id = $row['pl_id'];}

                         if (!empty($row['pl_place'])) {
                             $placestr2 = $mapstr_edit . $id . '&amp;level=' . $level . $mapstr3 . $mapstr5 . KT_I18N::translate('Zoom=') . $row['pl_zoom'] . $mapstr6 . $row['pl_placerequested'] . $mapstr8;
                             if ($row['pl_place'] == 'unknown')
                                 $matched[$x]++;
                         } else {
                             if ($levels[$z] == 'unknown') {
                                 $placestr2 = $mapstr_add . $id . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<strong>' . rtrim(ltrim(KT_I18N::translate('unknown'))) . "</strong>" . $mapstr8;
                                 $matched[$x]++;
                             } else {
                                 $placestr2 = $mapstr_add . $id . '&amp;place_name=' . urlencode($levels[$z]) . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<span class="error">' . rtrim(ltrim($levels[$z])) . '</span>' . $mapstr8;
                                 $matched[$x]++;
                             }
                         }

                         if ($prev_lati == 0) { // no link to edit if parent has no coordinates
                             $plac[$z] = '<td class="CellWithComment">' .
                                 $levels[$z] . '
                                 <span class="CellComment">' . KT_I18N::translate('Coordinates can not be added here until the parent place has coordinates.') . '</span>
                             </td>';
                         } else {
                             $plac[$z] = '<td>' . $placestr2 . '</td>';
                         }

                         if (!empty($row['pl_lati']) && $row['pl_lati'] == '0') {
                             $lati[$z] = '<td class="error"><strong>' . $row['pl_lati'] . '</strong></td>';
                         } elseif (!empty($row['pl_lati']) && $row['pl_lati'] <> '0') {
                             $lati[$z] = '<td>' . $row['pl_lati'] . '</td>';
                         } else {
                             $lati[$z] = '<td class="error center"><strong>X</strong></td>';
                             $prev_lati = 0;
                             $matched[$x]++;
                         }
                         if (!empty($row['pl_long']) && $row['pl_long'] == '0') {
                             $long[$z] = '<td class="error"><strong>' . $row['pl_long'] . '</strong></td>';
                         } elseif (!empty($row['pl_long'])  && $row['pl_long'] <> '0') {
                             $long[$z] = '<td>' . $row['pl_long'] . '</td>';
                         } else {
                             $long[$z] = "<td class='error center'><strong>X</strong></td>";
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
                             } else {
                                 echo '<td>&nbsp;</td>
                                 <td>&nbsp;</td>
                                 <td>&nbsp;</td>';
                             }
                             $z++;
                         }
                         echo '</tr>';
                         $countrows++;
                     }
                     $x++;
                 }
             echo '</tbody>
         </table>
     </div>';
     break;
 }
