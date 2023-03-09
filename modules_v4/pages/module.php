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

class pages_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config
{
	// Extend class KT_Module
	public function getTitle()
	{
		return KT_I18N::translate('Pages');
	}

	// Extend class KT_Module
	public function getDescription()
	{
		return KT_I18N::translate('Display resource pages.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder()
	{
		return 40;
	}

	// Extend class KT_Module
	public function defaultAccessLevel()
	{
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType()
	{
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action)
	{
		switch ($mod_action) {
			case 'admin_add':
			case 'admin_config':
			case 'admin_edit':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			case 'show':
				$this->show();
				break;
			case 'admin_delete':
				$this->delete();
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/admin_config.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink()
	{
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null)
	{
	}

	// Implement class KT_Module_Block
	public function loadAjax()
	{
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock()
	{
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id)
	{
	}

	public function getMenuTitle()
	{
		$default_title = KT_I18N::translate('Pages');

		return KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
	}

	public function getMenuIcon()
	{
		$default_icon = 'fa-book-reader';

		return KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_ICON', $default_icon));
	}

	public function getSummaryDescription()
	{
		$default_description = KT_I18N::translate('These are pages');

		return get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
	}

	// Return the list of gallerys
	private function getItemList()
	{
		$sql = "
			SELECT block_id, block_order,
			bs1.setting_value AS gallery_title,
			bs2.setting_value AS gallery_access,
			bs3.setting_value AS gallery_content,
			bs4.setting_value AS gallery_folder
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			JOIN `##block_setting` bs3 USING (block_id)
			JOIN `##block_setting` bs4 USING (block_id)
			WHERE module_name = ?
			AND bs1.setting_name = 'gallery_title'
			AND bs2.setting_name = 'gallery_access'
			AND bs3.setting_name = 'gallery_content'
			AND bs4.setting_name = 'gallery_folder'
			AND (gedcom_id IS NULL OR gedcom_id = ?)
			ORDER BY block_order
		";

		$items = KT_DB::prepare($sql)->execute([$this->getName(), KT_GED_ID])->fetchAll();

		$itemList = [];

		// Filter for valid lanuage and access
		foreach ($items as $item) {
			$languages   = get_block_setting($item->block_id, 'languages');
			$item_access = get_block_setting($item->block_id, 'gallery_access');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item_access >= KT_USER_ACCESS_LEVEL) {
				$itemList[] = $item;
			}
		}

		return $itemList;

	}

	// Implement KT_Module_Menu
	public function getMenu()
	{
		global $controller, $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$block_id     = KT_Filter::get('block_id');
		$blockId_list = [];
		$items        = $this->getItemList();

		foreach ($items as $item) {
			$blockId_list[] = $item->block_id;
		}

		if (!empty($blockId_list)) {
			$default_block = $blockId_list[0];
		} else {
			$default_block = '';
		}

		// -- main PAGES menu item
		$menu = new KT_Menu('<span>' . $this->getMenuTitle() . '</span>', 'module.php?mod=' . $this->getName() . '&amp;mod_action=show#' . $default_block, 'menu-my_pages', 'down');
		$menu->addClass('', '', $this->getMenuIcon());

		foreach ($items as $item) {
			$path = 'module.php?mod=' . $this->getName() . '&amp;mod_action=show#' . $item->block_id;
			$submenu = new KT_Menu(KT_I18N::translate($item->pages_title), $path, 'menu-my_pages-' . $item->block_id);
			$menu->addSubmenu($submenu);
		}

		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit pages'), $this->getConfigLink(), 'menu-my_pages-edit');
			$menu->addSubmenu($submenu);
		}

		return $menu;
	}

	private function show()
	{
		global $controller;

		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader()
			// following is for custom js operations on pages
			->addInlineJavascript('
				// add custom sub-tabs
				jQuery(".mytabs").tabs();
				jQuery("a.button").click(function() {
					var hash = jQuery(this).prop("hash");
					if (hash) {
						jQuery(".mytabs").tabs("option", "active", hash);
						window.location.reload(true);
						window.scrollBy({top: 500, left: 0, behavior: "smooth"});
					}
			    });

			    function returnTop() {
			    	jQuery("html, body").stop().animate({scrollTop: jQuery("html, body").offset().top});
			    }

				// add active_link class for breadcrumbs
				jQuery( "a.reveal_link" ).click(function() {
					jQuery("a.reveal_link").removeClass("active_link");
					jQuery(this).addClass("active_link");
					returnTop();
				});
				// add optional return to top button with class "reveal_button"
				jQuery( ".reveal_button" ).click(function() {
					returnTop();
				});
			')
		;

		$item_id     = KT_Filter::get('pages_id');
		$count_items = 0;
		$items       = $this->getItemList();

		foreach ($items as $item) {
			$count_items = $count_items + 1;
		}

		echo pageStart('pages', $controller->getPageTitle()); ?>

			<div class="grid-x">
				<div class="cell">
					<?php echo $this->getSummaryDescription(); ?>
				</div>				
				<?php if ($count_items > 1) { ?>
					<ul class="cell tabs" data-tabs id="pages-tabs">
						<?php foreach ($items as $item) {
							$class = ($item_id == $item->block_id ? 'is-active' : ''); ?>
							<li class="tabs-title <?php echo $class; ?>">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;pages_id=<?php echo $item->block_id; ?>">
									<span title="<?php echo KT_I18N::translate($item->pages_title); ?>"><?php echo KT_I18N::translate($item->pages_title); ?>
									</span>
								</a>
							</li>
						<?php } ?>
					</ul>
				<?php } ?>

				<div class="cell tabs-content" data-tabs-content="pages-tabs">
					<div class="grid-x">
						<?php $item_pages = '';
						foreach ($items as $item) {
							$item_content = $item->pages_content;
						}
						if (!isset($item_content)) { ?>
							<div class="cell callout warning">
								<?php echo KT_I18N::translate('No pages have been written yet'); ?>
							</div>
						<?php } else {
							echo $item_content;
						} ?>
					</div>
				</div>

			</div>

		<?php echo pageClose();
	}

	private function delete()
	{
		$block_id = KT_Filter::get('block_id');

		KT_DB::prepare(
			'DELETE FROM `##block_setting` WHERE block_id=?'
		)->execute([$block_id]);

		KT_DB::prepare(
			'DELETE FROM `##block` WHERE block_id=?'
		)->execute([$block_id]);
	}

}
