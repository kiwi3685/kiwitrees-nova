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

define('KT_SCRIPT_NAME', 'admin_summary_custom.php');

global $iconStyle;
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Customization'))
	->pageHeader();

/**
 * Array of site administration menu items
 * $custom [array]
 */
$custom = array(
	 "admin_custom_lang.php" => array(
 	   KT_I18N::translate('Custom translations'),
	   KT_I18N::translate('
	   		Modify language translations that you want to be different from the
			standard. These changes can be for any language, and apply right
			across your site.
		'),
	   KT_I18N::translate('Administrator access only'),
	   'alert'
   ),
	 "admin_custom_theme.php" => array(
 	   KT_I18N::translate('Custom file editing'),
	   KT_I18N::translate('
	   		Create and edit personalized theme files for your site,
	   		including style sheets, headers, and the Home page layout template.
		'),
	   KT_I18N::translate('Administrator access only'),
	   'alert'
   ),
	 "module.php?mod=custom_js&mod_action=admin_config"	=> array(
 	   KT_I18N::translate('Custom javascript'),
	   KT_I18N::translate('
	   		Add or edit any javascript you need for your site here.
			An examples might be Google Analytics, or other similar tools.
		'),
	   KT_I18N::translate('Administrator access only'),
	   'alert'
   ),
);
asort($custom);

echo pageStart('custom_admin', $controller->getPageTitle()); ?>

	<div class="cell callout info-help help_content">
		<?php echo KT_I18N::translate('
			Tools to help personalize your site beyond the standard features set.
		'); ?>
	</div>
	<div class="cell">
		<div class="grid-x grid-margin-x grid-margin-y">
			<?php foreach ($custom as $link => $file) {
				if (($file[3] == 'alert' && KT_USER_IS_ADMIN) || ($file[3] != 'alert' && KT_USER_GEDCOM_ADMIN)) {
					if ($link == KT_I18N::translate('Custom javascript') && !KT_Module::isActiveMenu(KT_GED_ID, 'custom_js', KT_USER_ACCESS_LEVEL)) {
						continue;
					}
					$title  = $file[0];
					$user    = '<span class="show-for-medium ' . $file[3] . '" data-tooltip title="' . $file[2] . '" data-position="top" data-alignment="right">
									<i class ="' . $iconStyle . ' fa-user"></i>
								</span>';
					$descr   = $file[1];

					echo AdminSummaryCard ($link, $title, $user, $descr);

				}
			} ?>
		</div>
	</div>

<?php echo pageClose();
