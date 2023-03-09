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

class faq_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Abbreviation for “Frequently Asked Questions” */ KT_I18N::translate('Frequently asked questions');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “faq” module */ KT_I18N::translate('A list of frequently asked questions and answers.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 130;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_add':
			case 'admin_config':
			case 'admin_edit':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			case 'admin_delete':
				$this->delete();
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/admin_config.php';
				break;
			case 'show':
				$this->show();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}

	public function getMenuTitle() {
		$default_title = KT_I18N::translate('Faq');
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getMenuIcon() {
		$default_icon = 'fa-comments';
		$HEADER_ICON = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_ICON', $default_icon));
		return $HEADER_ICON;
	}

	public function getSummaryDescription() {
		$default_description = '';
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
		return $HEADER_DESCRIPTION;
	}

	// Return the list of gallerys
	private function getItemList($search)
	{
		$sql = "
			SELECT block_id, block_order,
			bs1.setting_value AS faq_title,
			bs2.setting_value AS faq_access,
			bs3.setting_value AS faq_content
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			JOIN `##block_setting` bs3 USING (block_id)
			WHERE module_name = ?
			AND bs1.setting_name = 'faq_title'
			AND bs2.setting_name = 'faq_access'
			AND bs3.setting_name = 'faq_content'
			AND (gedcom_id IS NULL OR gedcom_id = ?)
			AND (bs2.setting_value LIKE '%" . $search . "%' OR bs1.setting_value LIKE '% . $search . %')
			ORDER BY block_order
		";

		$items = KT_DB::prepare($sql)->execute([$this->getName(), KT_GED_ID])->fetchAll();

		$itemList = [];

		// Filter for valid lanuage and access
		foreach ($items as $item) {
			$languages   = get_block_setting($item->block_id, 'languages');
			$item_access = get_block_setting($item->block_id, 'faq_access');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item_access >= KT_USER_ACCESS_LEVEL) {
				$itemList[] = $item;
			}
		}

		return $itemList;

	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$items = KT_DB::prepare(
			"SELECT block_id FROM `##block` b WHERE module_name=? AND IFNULL(gedcom_id, ?)=?"
		)->execute(array($this->getName(), KT_GED_ID, KT_GED_ID))->fetchAll();

		if (!$items) {
			return null;
		}

		$menu = new KT_Menu('<span>' . $this->getMenuTitle() . '</span>', 'module.php?mod=faq&amp;mod_action=show', 'menu-help');
		$menu->addClass('', '', $this->getMenuIcon());

		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit faq items'), $this->getConfigLink(), 'menu-faq-edit');
			$menu->addSubmenu($submenu);
		}

		return $menu;
	}

	private function show() {
		global $controller;
		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader();

		if (KT_Filter::post('query_faq')) {
			$search = KT_Filter::post('query_faq');
		} else {
			$search = '%';
		};

		echo pageStart('faq', $controller->getPageTitle());

			 $items = $this->getItemList($search); ?>

			<div class="grid-x">
				<div class="cell">
					<?php echo $this->getSummaryDescription(); ?>
				</div>
				<form class="cell medium-4 medium-offset-4" id="faq_search" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show" >
					<input
						type="search"
						name="query_faq"
						value="<?php echo ($search == '%' ? '' : $search); ?>"
						placeholder="<?php echo KT_I18N::translate('Search faq'); ?>"
					>
				</form>

				<div class="cell accordion" data-accordion data-allow-all-closed="true" data-deep-link="true">
					<?php foreach ($items as $item) {
						$item_title       = get_block_setting($item->block_id, 'faq_title');
						$item_description = get_block_setting($item->block_id, 'faq_content');
						$item_access      = get_block_setting($item->block_id, 'faq_access');
						$languages        = get_block_setting($item->block_id, 'languages');
						?>
						<div class="accordion-item" data-accordion-item>
							<a href="#" class="accordion-title">
								<span><?php echo $this->faq_search_hits($item->faq_title, $search); ?></span>
							</a>
							<div class="accordion-content" data-tab-content>
								<?php echo $this->faq_search_hits(substr($item_description, 0, 1) == '<' ? $item_description : nl2br($item_description), $search); ?>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>

		<?php echo pageClose();

	}

	private function delete() {
		$block_id = KT_Filter::get('block_id');

		KT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		KT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	// Return search of faqs
	function faq_search_hits($string, $search) {
		if ($search != '%') {
			return preg_replace('/' . $search . '/i', '<span class="search_hit">$0</span>', $string);
		} else {
			return $string;
		}
	}

}
