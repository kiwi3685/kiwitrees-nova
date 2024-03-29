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

 global $controller, $iconStyle;

//get the widgets list
$widgets = KT_Module::getActiveWidgets();
?>
<!-- Close button -->
<button class="close-button" aria-label="Close menu" type="button" data-close>
  <span aria-hidden="true">
	  <i class="<?php echo $iconStyle; ?> fa-window-close"></i>
  </span>
  <?php echo KT_I18N::translate('Close'); ?>
</button>

<ul class="accordion" id="widget-bar" data-accordion data-multi-expand="true">
	<?php foreach ($widgets as $module_name => $module) {
		$class_name = $module_name . '_KT_Module';
		$module = new $class_name;
		$widget = KT_DB::prepare(
			"SELECT * FROM `##block` WHERE module_name = ?"
		)->execute(array($module_name))->fetchOneRow();
		if (!$widget) {
			KT_DB::prepare("INSERT INTO `##block` (module_name, block_order) VALUES (?, 0)")
				->execute(array($module_name));
			$widget = KT_DB::prepare("SELECT * FROM `##block` WHERE module_name = ?")
				->execute(array($module_name))->fetchOneRow();
		}
		echo $module->getWidget($widget->block_id);
	} ?>
</ul>
<?php
