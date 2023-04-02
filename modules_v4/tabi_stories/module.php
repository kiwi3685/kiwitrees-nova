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

class tabi_stories_KT_Module extends KT_Module implements KT_Module_Block, KT_Module_IndiTab, KT_Module_Config, KT_Module_Menu
{
	// Extend class KT_Module
	public function getTitle()
	{
		return /* I18N: Name of a module */ KT_I18N::translate('Stories');
	}

	// Extend class KT_Module
	public function getDescription()
	{
		return /* I18N: Description of the “Stories” module */ KT_I18N::translate('Add narrative stories to individuals in the family tree.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder()
	{
		return 160;
	}

	// Extend class KT_Module
	public function defaultAccessLevel()
	{
		return KT_PRIV_HIDE;
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
				$this->edit();
				break;
			case 'show_list':
				$this->show_list();
				break;
			case 'admin_delete':
				$this->delete();
				$this->config();

				break;
			case 'story_link':
				$this->story_link();
				break;
			case 'remove_indi':
				$indi     = KT_Filter::get('indi_ref');
				$block_id = KT_Filter::get('block_id');
				if ($indi && $block_id) {
					self::removeIndi($indi, $block_id);
				}
				unset($_GET['action']);
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
		return false;
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
		return false;
	}

	// Implement class KT_Module_IndiTab
	public function defaultTabOrder()
	{
		return 50;
	}

	// Implement class KT_Module_IndiTab
	public function hasTabContent()
	{
		return KT_USER_CAN_EDIT || $this->getStoriesCount() > 0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut()
	{
		return 0 == $this->getStoriesCount();
	}

	// Implement class KT_Module_IndiTab
	public function canLoadAjax()
	{
		return false;
	}

	// Implement class KT_Module_IndiTab
	public function getPreLoadContent()
	{
		return '';
	}

	public function getStoriesCount()
	{
		global $controller;

		$count_of_stories =
			KT_DB::prepare("
				SELECT COUNT(##block.block_id)
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]')
				 AND gedcom_id=?
			")->execute([
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID,
			])->fetchOne();

		return $count_of_stories;
	}

	// Implement KT_Module_Menu
	public function getMenu()
	{
		global $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		// -- Stories menu item
		$menu = new KT_Menu('<span>' . $this->getTitle() . '</span>', 'module.php?mod=' . $this->getName() . '&amp;mod_action=show_list', 'menu-story');
		$menu->addClass('', '', 'fa-pen-fancy');

		return $menu;
	}

	public function getMenuTitle() {
		$default_title = KT_I18N::translate('Stories');
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getMenuIcon() {
		$default_icon = 'fa-book-open-reader';
		$HEADER_ICON = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_ICON', $default_icon));
		return $HEADER_ICON;
	}

	public function getSummaryDescription() {
		$default_description = '';
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
		return $HEADER_DESCRIPTION;
	}

	// Return the list of stories
	private function getItemList()
	{
		$sql = '
			SELECT block_id, block_order, gedcom_id
			FROM ##block
			WHERE module_name = ?
			AND gedcom_id = ?
		';

		return KT_DB::prepare($sql)->execute([$this->getName(), KT_GED_ID])->fetchAll();
	}

	// Implement class KT_Module_IndiTab
	public function getTabContent()
	{
		global $controller, $iconStyle;

		$block_ids =
			KT_DB::prepare("
				SELECT ##block.block_id
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block.block_id = ##block_setting.block_id
				 AND (##block_setting.setting_name = 'xref' AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]'))
				 AND ##block.gedcom_id=?
				 ORDER BY ##block.block_order
			")->execute([
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID,
			])->fetchOneColumn();

		$html          = '';
		$class         = '';
		$ids           = [];
		$count_stories = 0;

		foreach ($block_ids as $block_id) {
			$block_order = get_block_order($block_id);
			// check how many stories can be shown in a language
			$languages = get_block_setting($block_id, 'languages');
			if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) {
				$count_stories++;
				$ids[] = $block_order;
			}
		}

		// establish first story id from lowest block_order
		$ids ? $first_story = min($ids) : $first_story = '';
		foreach ($block_ids as $block_id) {
			$block_order = get_block_order($block_id);
			if ($block_order == $first_story) {
				$first_story = $block_id;
			}
		}

		ob_start(); ?>

		<?php if (KT_USER_GEDCOM_ADMIN) { ?>
			<div class="cell tabHeader">
				<div class="grid-x">
					<div class="cell shrink">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_add&amp;xref=<?php echo $controller->record->getXref(); ?>">
							<i class="<?php echo $iconStyle; ?> fa-plus"></i>
							<?php echo KT_I18N::translate('Add story'); ?>
						</a>
					</div>
					<div class="cell auto">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config&amp;xref=<?php echo $controller->record->getXref(); ?>">
							<i class="<?php echo $iconStyle; ?> fa-link"></i>
							<?php echo KT_I18N::translate('Link this individual to an existing story '); ?>
						</a>
					</div>
				</div>
			</div>
		<?php }

		if ($count_stories > 1) {
			$class = 'story';
			$controller->addInlineJavascript('
				// Start with all stories hidden except the first
				jQuery("#story_contents div.story").hide();
				jQuery("#story_contents #stories_" + ' . $first_story . ').show();

				// Calculate scroll value
				var posn = jQuery("#navbar").height() - jQuery("#indi_header").height() + jQuery(".ui-tabs-nav").height() + 25;
				if (jQuery("#navbar").css("position") == "fixed") {
					var posn = jQuery("#indi_header").height() + jQuery(".ui-tabs-nav").height() - 20;
				}

				// On clicking a title hide all stories except the chosen one
				jQuery("#contents_list a").click(function(e){
					e.preventDefault();
					var id = jQuery(this).attr("id").split("_");
					jQuery("#story_contents .story").hide();
					jQuery("#story_contents #stories_" + id[1]).show();
					jQuery("html, body").stop().animate({scrollTop: jQuery("#stories").offset().top - posn}, 2000);
				});
			'); ?>

			<h4><?php echo KT_I18N::translate('List of stories'); ?></h4>
			<ol id="contents_list">
				<?php foreach ($block_ids as $block_id) {
					$languages = get_block_setting($block_id, 'languages');
					if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
						<li style="padding:2px 8px;">
							<a href="#" id="title_<?php echo $block_id; ?>"><?php echo get_block_setting($block_id, 'title'); ?></a>
						</li>
					<?php }
					} ?>
			</ol>
			<hr class="stories_divider">
		<?php } ?>

		<div class="grid-x grid-margin-y" id="story_contents">
			<?php foreach ($block_ids as $block_id) {
				$languages = get_block_setting($block_id, 'languages');
				if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
					<div class="cell <?php echo $class; ?>" id="stories_<?php echo $block_id; ?>">
						<?php if (KT_USER_CAN_EDIT) { ?>
							<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $block_id; ?>">
								<i class="<?php echo $iconStyle; ?> fa-pen-to-square"></i>
								<?php echo KT_I18N::translate('Edit story'); ?>
							</a>
						<?php } ?>
						<h3 class="text-center">
							<?php echo get_block_setting($block_id, 'story_title'); ?>
						</h3>

						<?php echo get_block_setting($block_id, 'story_content'); ?>

						<?php if ($count_stories > 1) { ?>
							<hr class="stories_divider">
						<?php } ?>
					</div>
				<?php }
			} ?>
		</div>

		<?php return ob_get_clean();

	}

	private function show_list()
	{
		global $controller;
		$controller = new KT_Controller_Page();
		$controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
		;

		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_DATATABLES_BUTTONS)
				->addExternalJavascript(KT_DATATABLES_HTML5)
			;
			$buttons = 'B';
		} else {
			$buttons = '';
		}

		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery("#story_table").dataTable({
					dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csvHtml5", exportOptions: {columns: ":visible"}}],
					autoWidth: false,
					processing: true,
					retrieve: true,
					displayLength: 20,
					pagingType: "full_numbers",
					sorting: [[0,"asc"]],
					columns: [
						/* 0-name */ null,
						/* 1-NAME */ null
					]
				});
			')
		;

		$items = $this->getItemList();

		echo pageStart($this->getTitle(), $controller->getPageTitle()); ?>

			<div class="grid-x">
				<?php if(!is_null($this->getSummaryDescription())) { ?>
					<div class="cell">
						<?php echo $this->getSummaryDescription(); ?>
					</div>				
				<?php } ?>
				<?php if (count($items) > 0) { ?>
					<table id="story_table" class="width100">
						<thead>
							<tr>
								<th><?php echo KT_I18N::translate('Story title'); ?></th>
								<th><?php echo KT_I18N::translate('Individual'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($items as $item) {
								$item_title = get_block_setting($item->block_id, 'story_title');
								$xref = explode(',', (string) get_block_setting($item->block_id, 'xref'));
								$count_xref = count($xref);
								// if one indi is private, the whole story is private.
								$private = 0;
								for ($x = 0; $x < $count_xref; $x++) {
									$indi[$x] = KT_Person::getInstance($xref[$x]);
									if ($indi[$x] && !$indi[$x]->canDisplayDetails()) {
										$private = $x + 1;
									}
								}
								if (0 == $private) {
									$languages = get_block_setting($item->block_id, 'languages');
									if (!$languages || in_array(KT_LOCALE, explode(',', (string) $languages))) { ?>
										<tr>
											<td><?php echo $item_title; ?></td>
											<td>
												<?php for ($x = 0; $x < $count_xref; $x++) {
													$indi[$x] = KT_Person::getInstance($xref[$x]);
													if (!$indi[$x]) { ?>
														<p class="error"><?php echo $xref[$x]; ?></p>
													<?php } else { ?>
														<p>
															<a href="<?php echo $indi[$x]->getHtmlUrl(); ?>#stories" class="current">
																<?php echo $indi[$x]->getFullName(); ?>
															</a>
														</p>
													<?php }
												} ?>
											</td>
										</tr>
									<?php }
								}
							} ?>
						</tbody>
					</table>
				<?php } else { ?>
					<div class="cell callout warning">
						<?php echo KT_I18N::translate('No stories have been written yet'); ?>
					</div>
				<?php } ?>
			</div>

		<?php pageClose();

	}

	// Delete a story from the database
	private function delete()
	{
		if (KT_USER_CAN_EDIT) {
			$block_id = KT_Filter::get('block_id');

			$block_order = KT_DB::prepare('
				SELECT block_order FROM `##block` WHERE block_id=?
			')->execute([$block_id])->fetchOne();

			KT_DB::prepare('
				DELETE FROM `##block_setting` WHERE block_id=?
			')->execute([$block_id]);

			KT_DB::prepare('
				DELETE FROM `##block` WHERE block_id=?
			')->execute([$block_id]);
		} else {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);

			exit;
		}
	}

	// Link an individual to an existing story directly
	private function story_link()
	{
		if (KT_USER_GEDCOM_ADMIN) {
			$block_id = KT_Filter::get('block_id');
			$new_xref = KT_Filter::get('xref', KT_REGEX_XREF);
			$xref = explode(',', (string) get_block_setting($block_id, 'xref'));
			$xref[] = $new_xref;
			set_block_setting($block_id, 'xref', implode(',', $xref));
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'individual.php?pid=' . $new_xref);
		} else {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);

			exit;
		}
	}

	// Delete an individual linked to a story, from the database
	private function removeIndi($indi, $block_id)
	{
		$xref = explode(',', (string) get_block_setting($block_id, 'xref'));
		$xref = array_diff($xref, [$indi]);
		set_block_setting($block_id, 'xref', implode(',', $xref));
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'module.php?mod=' . $this->getName() . '&mod_action=admin_edit&block_id=' . $block_id);
	}
}
