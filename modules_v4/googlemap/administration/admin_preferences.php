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

 $precision = [
     0 => KT_I18N::translate('Country'),
     1 => KT_I18N::translate('State'),
     3 => KT_I18N::translate('Neighbourhood'),
     4 => KT_I18N::translate('House'),
     5 => KT_I18N::translate('Max')
 ];

 $selected = [
     '0' => $GOOGLEMAP_PRECISION_0,
     '1' => $GOOGLEMAP_PRECISION_1,
     '3' => $GOOGLEMAP_PRECISION_2,
     '4' => $GOOGLEMAP_PRECISION_3,
     '5' => $GOOGLEMAP_PRECISION_4
 ];

 $controller = new KT_Controller_Page();
 $controller
     ->restrictAccess(KT_USER_IS_ADMIN)
     ->setPageTitle(KT_I18N::translate('Google Maps™'))
     ->pageHeader();

     switch (KT_Filter::post('action')) {
         case 'update' :
             set_module_setting('googlemap', 'GM_PLACE_HIERARCHY',   KT_Filter::postBool('NEW_GM_PLACE_HIERARCHY'));
             set_module_setting('googlemap', 'GM_PH_MARKER',         KT_Filter::post('NEW_GM_PH_MARKER'));
             set_module_setting('googlemap', 'GM_DISP_SHORT_PLACE',  KT_Filter::postBool('NEW_GM_DISP_SHORT_PLACE'));
             set_module_setting('googlemap', 'GM_COORD',             KT_Filter::postBool('NEW_GM_COORD'));
             set_module_setting('googlemap', 'GM_MAP_TYPE',          KT_Filter::post('NEW_GM_MAP_TYPE'));
             set_module_setting('googlemap', 'GM_MIN_ZOOM',          KT_Filter::post('NEW_GM_MIN_ZOOM'));
             set_module_setting('googlemap', 'GM_MAX_ZOOM',          KT_Filter::post('NEW_GM_MAX_ZOOM'));
             set_module_setting('googlemap', 'GM_API_KEY',  			KT_Filter::post('NEW_GM_API_KEY'));
             set_module_setting('googlemap', 'GM_PRECISION_0',       KT_Filter::post('NEW_GM_PRECISION_0'));
             set_module_setting('googlemap', 'GM_PRECISION_1',       KT_Filter::post('NEW_GM_PRECISION_1'));
             set_module_setting('googlemap', 'GM_PRECISION_2',       KT_Filter::post('NEW_GM_PRECISION_2'));
             set_module_setting('googlemap', 'GM_PRECISION_3',       KT_Filter::post('NEW_GM_PRECISION_3'));
             set_module_setting('googlemap', 'GM_PRECISION_4',       KT_Filter::post('NEW_GM_PRECISION_4'));
             set_module_setting('googlemap', 'GM_PRECISION_5',       KT_Filter::post('NEW_GM_PRECISION_5'));
             set_module_setting('googlemap', 'GM_DEFAULT_TOP_VALUE', KT_Filter::post('NEW_GM_DEFAULT_TOP_LEVEL'));

             for ($i = 1; $i <= 9; $i ++) {
                 set_module_setting('googlemap', 'GM_PREFIX_' . $i,  KT_Filter::post('NEW_GM_PREFIX_' . $i));
                 set_module_setting('googlemap', 'GM_POSTFIX_' . $i, KT_Filter::post('NEW_GM_POSTFIX_' . $i));
             }
             AddToLog('Googlemap settings updated', 'config');
             // read the config file again, to set the vars
             require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
             // Reload the page, so that the settings take effect immediately.
             echo '<script>
                 window.location.href="module.php?mod=googlemap&mod_action=admin_preferences";
             </script>';
             exit;

     } ?>

     <div id="gm_config" class="cell">
         <h4><?php echo $controller->getPageTitle(); ?></h4>

         <ul class="tabs" id="gm_pages">
             <li class="tabs-title medium-4 text-center is-active">
                 <a href="module.php?mod=googlemap&amp;mod_action=admin_preferences" class="current" aria-selected="true">
                     <?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
                 </a>
             </li>
             <li class="tabs-title text-center">
                 <a href="module.php?mod=googlemap&amp;mod_action=admin_places">
                     <?php echo KT_I18N::translate('Geographic data'); ?>
                 </a>
             </li>
             <li class="tabs-title text-center">
                 <a href="module.php?mod=googlemap&amp;mod_action=admin_placecheck">
                     <?php echo KT_I18N::translate('Place check'); ?>
                 </a>
             </li>
         </ul>

         <form class="cell" method="post" name="configform" action="module.php?mod=googlemap&mod_action=admin_preferences">
             <input type="hidden" name="action" value="update">
             <div class="grid-x grid-margin-x">
                 <div class="cell medium-3">
                     <label class="middle">
                         <?php echo KT_I18N::translate('Use Google Maps™ for the Place hierarchy'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9">
                     <?php echo simple_switch(
                         'NEW_GM_PLACE_HIERARCHY',
                         true,
                         get_module_setting('googlemap', 'GM_PLACE_HIERARCHY', '0'),
                         '',
                         KT_I18N::translate('yes'),
                         KT_I18N::translate('no')
                     ); ?>
                 </div>
                 <div class="cell medium-3">
                     <label for="mapApi" class="middle">
                         <?php echo /* I18N: Optional Google Map API key */ KT_I18N::translate('Google Maps™ API key'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9">
                     <input type="text" name="NEW_GM_API_KEY" value="<?php echo $GM_API_KEY; ?>">
                     <div class="callout warning helpcontent">
                         <?php echo KT_I18N::translate('
                             Google require that users of Google Maps™ obtain an API key from them.
                             This is linked to their usage restrictions described at
                             https://developers.google.com/maps/documentation/geocoding/usage-limits.
                             The same page has a link to get a key.
                             You can continue to use the maps feature without the API key if you do not exceed the restrictions
                             but Google will add a warning message overlaid on the map .'); ?>
                     </div>
                 </div>
                 <div class="cell medium-3">
                     <label for="mapType" class="middle">
                         <?php echo KT_I18N::translate('Default map type'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9 radio">
                     <?php echo radio_switch_group (
                         'NEW_GM_MAP_TYPE',
                         array(
                             'ROADMAP'	=> KT_I18N::translate('Road map'),
                             'SATELLITE'	=> KT_I18N::translate('Satellite'),
                             'HYBRID'	=> KT_I18N::translate('Hybrid'),
                             'TERRAIN'	=> KT_I18N::translate('Terrain')
                         ),
                         $GOOGLEMAP_MAP_TYPE
                     ); ?>
                 </div>
                 <div class="cell medium-3">
                     <label for="mapMin" class="middle">
                         <?php echo KT_I18N::translate('Zoom factor of map'); ?>
                     </label>
                 </div>
                 <div class="cell medium-4">
                     <div class="input-group">
                         <span class="input-group-label"><?php echo KT_I18N::translate('Minimum'); ?></span>
                         <select id="mapMin" name="NEW_GM_MIN_ZOOM">
                             <?php for ($j = 1; $j < 15; $j ++) { ?>
                                 <option value="<?php echo $j; ?>";
                                     <?php if ($GOOGLEMAP_MIN_ZOOM == $j) {
                                         echo ' selected="selected"';
                                     } ?>
                                 >
                                     <?php echo $j; ?>
                                 </option>
                             <?php } ?>
                         </select>
                     </div>
                 </div>
                 <div class="cell medium-4">
                     <div class="input-group">
                         <span class="input-group-label"><?php echo KT_I18N::translate('Maximum'); ?></span>
                         <select id="mapMax" name="NEW_GM_MAX_ZOOM">
                             <?php for ($j = 1; $j < 21; $j ++) { ?>
                                 <option value="<?php echo $j; ?>";
                                     <?php if ($GOOGLEMAP_MAX_ZOOM == $j) {
                                         echo ' selected="selected"';
                                     } ?>
                                 >
                                     <?php echo $j; ?>
                                 </option>
                             <?php } ?>
                         </select>
                     </div>
                 </div>
                 <div class="callout warning helpcontent medium-9 medium-offset-3">
                         <?php echo KT_I18N::translate('Minimum and maximum zoom factor for the Google map. 1 is the full map, 15 is single house. Note that 15 is only available in certain areas.'); ?>
                 </div>
                 <div class="cell medium-3">
                     <label for="gm_marker" class="middle">
                         <?php echo KT_I18N::translate('Type of place markers in Place Hierarchy'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9 radio">
                     <?php echo radio_switch_group (
                         'NEW_GM_PH_MARKER',
                         array(
                             'G_DEFAULT_ICON'	=> KT_I18N::translate('Standard'),
                             'G_FLAG'			=> KT_I18N::translate('Flag')
                         ),
                         $GOOGLEMAP_PH_MARKER
                     ); ?>
                 </div>
                 <div class="cell medium-3">
                     <label class="middle">
                         <?php echo KT_I18N::translate('Display short placenames'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9">
                     <?php echo simple_switch (
                         'NEW_GM_DISP_SHORT_PLACE',
                         true,
                         $GM_DISP_SHORT_PLACE,
                         '',
                         KT_I18N::translate('yes'),
                         KT_I18N::translate('no')
                     ); ?>
                     <div class="callout warning helpcontent">
                         <?php echo KT_I18N::translate('
                             Here you can choose between two types of displaying places names in hierarchy.
                             If set Yes the place has short name or actual level name, if No - full name.
                             <br />
                             <b>Examples:<br />
                             Full name: </b>Chicago, Illinois, USA&nbsp;&nbsp;&nbsp;<b>Short name: </b>Chicago
                             <br />
                             <b>Full name: </b>Illinois, USA&nbsp;&nbsp;&nbsp;<b>Short name: </b>Illinois
                         '); ?>
                     </div>
                 </div>
                 <div class="cell medium-3">
                     <label class="middle">
                         <?php echo KT_I18N::translate('Display Map Coordinates'); ?>
                     </label>
                 </div>
                 <div class="cell medium-9">
                     <?php echo simple_switch(
                         'NEW_GM_COORD',
                         true,
                         $GOOGLEMAP_COORD,
                         '',
                         KT_I18N::translate('yes'),
                         KT_I18N::translate('no')
                     ); ?>
                     <div class="callout warning helpcontent">
                         <?php echo KT_I18N::translate('
                             This options sets whether Latitude and Longitude are displayed on the pop-up window
                             attached to map markers.
                         '); ?>
                     </div>
                 </div>
                 <div class="cell medium-6 small-up-1">
                     <div class="grid-x grid-margin-x close">
                         <h6 class="cell strong"><?php echo KT_I18N::translate('Precision of the latitude and longitude'); ?></h6>
                             <?php foreach ($precision as $key => $value) { ?>
                                 <div class="cell medium-6">
                                     <label for="precision-<?php echo $key; ?>" class="middle">
                                         <?php echo $value; ?>
                                     </label>
                                 </div>
                                 <div class="cell medium-6">
                                     <div class="input-group">
                                         <select id="precision-<?php echo $key; ?>" name="NEW_GM_PRECISION_<?php echo $key; ?>">
                                             <?php for ($j = 0; $j < 10; $j ++) {
                                                 $select = $selected[$key]; ?>
                                                 <option value="<?php echo $j; ?>"
                                                     <?php if ($select == $j) {
                                                         echo ' selected="selected"';
                                                     } ?>
                                                 >
                                                     <?php echo $j; ?>
                                                 </option>
                                             <?php } ?>
                                         </select>
                                         <span class="input-group-label"><?php echo KT_I18N::translate('digits'); ?></span>
                                     </div>
                                 </div>
                             <?php } ?>
                     </div>
                 </div>
                 <div class="cell">
                     <div class="grid-x grid-margin-x close">
                         <div class="cell medium-3">
                             <label for="gm_default" class="middle">
                                 <?php echo KT_I18N::translate('Default value for top-level'); ?>
                             </label>
                         </div>
                         <div class="cell callout warning helpcontent medium-9">
                             <?php echo KT_I18N::translate('Here the default level for the highest level in the place-hierarchy can be defined. If a place cannot be found this name is added as the highest level (country) and the database is searched again.'); ?>
                         </div>
                         <div class="cell medium-9 medium-offset-3">
                             <input
                                 id="gm_default"
                                 type="text"
                                 name="NEW_GM_DEFAULT_TOP_LEVEL"
                                 value="<?php echo $GM_DEFAULT_TOP_VALUE; ?>"
                             >
                         </div>
                     </div>
                 </div>
                 <div class="cell medium-3">
                     <label class="h6 strong middle success">
                         <?php echo KT_I18N::translate('Optional prefixes and suffixes');?>
                     </label>
                 </div>
                 <div class="cell callout warning helpcontent medium-9">
                     <?php echo KT_I18N::translate('Some place names may be written with optional prefixes and suffixes.  For example “Orange” versus “Orange County”.  If the family tree contains the full place names, but the geographic database contains the short place names, then you should specify a list of the prefixes and suffixes to be disregarded.  Multiple options should be separated with semicolons.  For example “County;County of” or “Township;Twp;Twp.”.'); ?>
                 </div>
                 <div class="cell medium-9 close small-up-1">
                     <div class="grid-x grid-margin-x">
                         <div class="cell medium-4 medium-offset-4">
                             <label class="strong text-center middle">
                                 <?php echo KT_I18N::translate('Prefixes'); ?>
                             </label>
                         </div>
                         <div class="cell medium-4">
                             <label class="strong text-center middle">
                                 <?php echo KT_I18N::translate('Suffixes'); ?>
                             </label>
                         </div>
                     </div>
                     <div class="grid-x grid-margin-x">
                         <?php for ($level = 1; $level <= 9; $level ++) { ?>
                             <?php if ($level == 1) { ?>
                                 <div class="cell medium-4">
                                     <label  class="success middle">
                                         <?php echo KT_I18N::translate('Country'); ?>
                                     </label>
                                 </div>
                             <?php } else { ?>
                                 <div class="cell medium-4">
                                     <label class="success middle">
                                         <?php echo KT_I18N::translate('Level %s', $level); ?>
                                     </label>
                                 </div>
                             <?php } ?>
                             <div class="cell medium-4">
                                 <input
                                     type="text"
                                     name="NEW_GM_PREFIX_<?php echo $level; ?>"
                                     value="<?php echo $GM_PREFIX[$level]; ?>"
                                 >
                             </div>
                             <div class="medium-4">
                                 <input
                                     type="text"
                                     name="NEW_GM_POSTFIX_<?php echo $level; ?>"
                                     value="<?php echo $GM_POSTFIX[$level]; ?>"
                                 >
                             </div>
                         <?php } ?>
                     </div>
                 </div>
                 <div class="medium-3"></div>
                 <button type="submit" class="button">
                     <i class="<?php echo $iconStyle; ?> fa-save"></i>
                     <?php echo KT_I18N::translate('Save'); ?>
                 </button>
             </div>
         </form>
     </div>
<?php
