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

class pages_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return KT_I18N::translate('Pages');
	}

	public function getMenuTitle() {
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', 'Resources'));
		return $HEADER_TITLE;
	}

	// Extend class KT_Module
	public function getDescription() {
		return KT_I18N::translate('Display resource pages.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 40;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;
		$block_id = KT_Filter::get('block_id');
		$blockId_list = array();
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
				$blockId_list[] = $items->block_id;
			}
		}
		if( !empty( $blockId_list ) ) {
			$default_block = $blockId_list[0];
		} else {
			$default_block = "";
		}

		if ($SEARCH_SPIDER) {
			return null;
		}

		//-- main PAGES menu item
		$menu = '';
		$menu = new KT_Menu($this->getMenuTitle(), 'module.php?mod=' . $this->getName().'&amp;mod_action=show#' . $default_block, 'menu-my_pages', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');
		$menu->addClass('', '', 'fa-book-reader');
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod=' . $this->getName() . '&amp;mod_action=show#' . $items->block_id;
				$submenu = new KT_Menu(KT_I18N::translate($items->pages_title), $path, 'menu-my_pages-' . $items->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit pages'), $this->getConfigLink(), 'menu-my_pages-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		case 'admin_config':
			$this->config();
			break;
		case 'admin_delete':
			$this->delete();
			$this->config();
			break;
		case 'admin_edit':
			$this->edit();
			break;
		case 'admin_movedown':
			$this->movedown();
			$this->config();
			break;
		case 'admin_moveup':
			$this->moveup();
			$this->config();
			break;
		}
	}

	// Action from the configuration page
	private function config() {
		global $iconStyle;
		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery("#pages_module").sortable({
					items: ".sortme",
					forceHelperSize: true,
					forcePlaceholderSize: true,
					opacity: 0.7,
					cursor: "move",
					axis: "y"
				});

				//-- update the order numbers after drag-n-drop sorting is complete
				jQuery("#pages_module").bind("sortupdate", function(event, ui) {
					jQuery("#"+jQuery(this).attr("id")+" input").each(
						function (index, value) {
							value.value = index+1;
						}
					);
				});
			');

		if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
			ckeditor_KT_Module::enableEditor($controller);
		}

		$items = KT_DB::prepare(
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_content".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_content'".
			" AND IFNULL(gedcom_id, ?)=?".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID, KT_GED_ID))->fetchAll();

		$action = KT_Filter::post('action');

		switch ($action) {
			case 'update':
				set_module_setting($this->getName(), 'HEADER_TITLE', KT_Filter::post('NEW_HEADER_TITLE'));
				set_module_setting($this->getName(), 'HEADER_DESCRIPTION', KT_Filter::post('NEW_HEADER_DESCRIPTION', KT_REGEX_UNSAFE)); // allow html
				AddToLog($this->getName() . ' config updated', 'config');
				break;
			case 'updatePagesList':
				foreach ($items as $item) {
					$order = KT_Filter::post('order-' . $item->block_id);
					KT_DB::prepare(
						"UPDATE `##block` SET block_order=? WHERE block_id=?"
					)->execute(array($order, $item->block_id));
				}
				break;
			default:
				// code...
				break;
		}

		$HEADER_TITLE			= get_module_setting($this->getName(), 'HEADER_TITLE', KT_I18N::translate('Resources'));
		$HEADER_DESCRIPTION		= get_module_setting($this->getName(), 'HEADER_DESCRIPTION', KT_I18N::translate('These are resources'));
		?>

		<div id="<?php echo $this->getName(); ?>" class="cell">
			<div class="grid-x grid-margin-x grid-margin-y">
				<div class="cell">
					<h4 class="inline"><?php echo $this->getTitle(); ?></h4>
<!--					<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/modules-faqs/pages/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="<?php echo $iconStyle; ?> fa-comments"></i></a>-->
					<h5  class="subheader"><?php echo KT_I18N::translate('Configuration'); ?></h5>
					<ul id="module_pages_tabs" class="tabs" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" data-deep-link="true">
						<li class="tabs-title is-active">
							<a href="#pages_summary"><?php echo KT_I18N::translate('Summary'); ?></a>
						</li>
						<li class="tabs-title">
							<a href="#pages_pages"><?php echo KT_I18N::translate('Pages'); ?></a>
						</li>
					</ul>
					<div class="tabs-content" data-tabs-content="module_pages_tabs">
						<div id="pages_summary" class="tabs-panel is-active">
							<form method="post" action="module.php?mod=<?php echo$this->getName(); ?>&mod_action=admin_config#pages_pages">
								<input type="hidden" name="action" value="update">
								<div class="cell">
									<label>
										<?php echo KT_I18N::translate('Main menu and summary page title'); ?>
									</label>
									<span class="help-text"><?php echo KT_I18N::translate('This is a brief title. It is displayed in two places.<ol><li> It is used as the main menu item name if your theme uses names, and you have more than one page. If you only have one page, then the title of that page is used. It should be kept short or it might break the menu display.</li><li>It is used as the main title on the display page, above the tabbed list of pages.</li></ol>'); ?></span>
									<input type="text" name="NEW_HEADER_TITLE" value="<?php echo $HEADER_TITLE; ?>">
								</div>
								<div class="cell">
									<label>
										<?php echo KT_I18N::translate('Summary page description'); ?>
									</label>
									<span class="help-text"><?php echo KT_I18N::translate('This is a sub-heading that will display below the <b>Summary Page title</b>, above the tabbed list of pages. It can contain HTML elements including an image if you wish. Simply ensure there is no content if you do not want to display it.'); ?></span>
									<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" rows="5"><?php echo $HEADER_DESCRIPTION; ?></textarea>
								</div>
								<button class="button margin-0" type="submit">
									<i class="<?php echo $iconStyle; ?> fa-save"></i>
									<?php echo KT_I18N::translate('Save'); ?>
								</button>
							</form>
						</div>
						<div id="pages_pages" class="tabs-panel">
							<form class="grid-x" method="post" action="module.php?mod=<?php echo$this->getName(); ?>&mod_action=admin_config">
								<input type="hidden" name="action" value="updatePagesList">
								<div class="cell medium-6">
									<div class="input-group">
										<span class="input-group-label"><?php echo KT_I18N::translate('Family tree'); ?></span>
										<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM, 'class="input-group-field"'); ?>
										<div class="input-group-button">
											<button class="button" type="submit">
												<i class="<?php echo $iconStyle; ?> fa-save"></i>
												<?php echo KT_I18N::translate('Show'); ?>
											</button>
										</div>
									</div>
								</div>
							</form>
							<a class="button margin-0" href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit">
								<i class="<?php echo $iconStyle; ?> fa-file-word"></i>
								&nbsp;
								<?php echo KT_I18N::translate('Add page'); ?>
							</a>
							<hr>
							<?php if ($items) { ?>
								<!-- List all pages for selected family tree-->
								<form class="grid-x" method="post" action="module.php?mod=<?php echo$this->getName(); ?>&mod_action=admin_config#pages_pages">
									<input type="hidden" name="action" value="updatePagesList">
									<table id="pages_module">
										<thead>
											<tr>
												<th colspan="2"><?php echo KT_I18N::translate('Family tree'); ?></th>
												<th><?php echo KT_I18N::translate('Page title'); ?></th>
												<th><?php echo KT_I18N::translate('Order'); ?></th>
												<th class="text-center"><?php echo KT_I18N::translate('Edit'); ?></th>
												<th class="text-center"><?php echo KT_I18N::translate('Delete'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php $trees = KT_Tree::getAll();
												foreach ($items as $item) { ?>
													<tr class="sortme">
														<td>
															<i class="<?php echo $iconStyle; ?> fa-bars"></i>
														</td>
														<td>
															<?php if ($item->gedcom_id == null) {
																echo KT_I18N::translate('All');
															} else {
																echo $trees[$item->gedcom_id]->tree_title_html;
															} ?>
														</td>
														<td>
															<span><?php echo KT_I18N::translate($item->pages_title); ?></span>
														</td>
														<td>
															<input type="number" size="3" value="<?php echo $item->block_order; ?>" name="order-<?php echo $item->block_id; ?>">
														</td>
														<td class="text-center">
															<!--edit--><a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $item->block_id; ?>"><i class="<?php echo $iconStyle; ?> fa-edit"></i></a>
														</td>
														<td class="text-center">
															<!--delete--><a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $item->block_id; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to delete this page?'); ?>');"><i class="<?php echo $iconStyle; ?> fa-trash-can"></i></a>
														</td>
													</tr>
												<?php } ?>
										</tbody>
									</table>
									<button class="button margin-0" type="submit">
										<i class="<?php echo $iconStyle; ?> fa-save"></i>
										<?php echo KT_I18N::translate('Save'); ?>
									</button>
								</form>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }

	private function edit() {
		if (KT_USER_IS_ADMIN) {
			global $iconStyle;
			require_once KT_ROOT . 'includes/functions/functions_edit.php';

			if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
				$block_id = KT_Filter::post('block_id');
				if ($block_id) {
					KT_DB::prepare(
						"UPDATE `##block` SET gedcom_id=NULLIF(?, ''), block_order=? WHERE block_id=?"
					)->execute(array(
						KT_Filter::post('gedcom_id'),
						(int)KT_Filter::post('block_order'),
						$block_id
					));
				} else {
					KT_DB::prepare(
						"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (NULLIF(?, ''), ?, ?)"
					)->execute(array(
						KT_Filter::post('gedcom_id'),
						$this->getName(),
						(int)KT_Filter::post('block_order')
					));
					$block_id = KT_DB::getInstance()->lastInsertId();
				}

				set_block_setting($block_id, 'pages_title', KT_Filter::post('pages_title', KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'pages_content', KT_Filter::post('pages_content', KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'pages_access', KT_Filter::post('pages_access', KT_REGEX_UNSAFE));

				$languages = array();
				foreach (KT_I18N::used_languages() as $code=>$name) {
					if (KT_Filter::postBool('lang_' . $code)) {
						$languages[] = $code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));

				$this->config();

			} else {
				$block_id	= KT_Filter::get('block_id');
				$controller	= new KT_Controller_Page();

				if ($block_id) {
					$controller->setPageTitle(KT_I18N::translate('Edit pages'));
					$items_title=get_block_setting($block_id, 'pages_title');
					$items_content=get_block_setting($block_id, 'pages_content');
					$items_access=get_block_setting($block_id, 'pages_access');
					$block_order=KT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id=KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(KT_I18N::translate('Add pages'));
					$items_title	= '';
					$items_content	= '';
					$items_access	= 1;
					$block_order	= KT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id=KT_GED_ID;
				}

				$controller->pageHeader();

				if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
					ckeditor_KT_Module::enableEditor($controller);
				}
				?>

				<div id="<?php echo $this->getName(); ?>" class="cell">
					<div class="grid-x grid-margin-x grid-margin-y">
						<div class="cell">
							<h4><?php echo $this->getTitle(); ?></h4>
							<h5  class="subheader"><?php echo KT_I18N::translate('Add / edit'); ?></h5>
							<form id="pagesform1" method="post">
								<?php echo KT_Filter::getCsrf(); ?>
								<input type="hidden" name="save" value="1">
								<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
								<div class="grid-x grid-margin-x">
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Title'); ?>
										</label>
										<input type="text" name="pages_title" size="90" value="<?php echo htmlspecialchars($items_title); ?>">
									</div>
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Content'); ?>
										</label>
										<textarea name="pages_content" class="html-edit" rows="10" cols="90"><?php echo htmlspecialchars($items_content); ?></textarea>
									</div>
									<div class="cell medium-4">
										<label class="h5">
											<?php echo KT_I18N::translate('Access level'); ?>
										</label>
										<?php echo edit_field_access_level('pages_access', $items_access); ?>
									</div>
									<div class="cell medium-4">
										<label class="h5">
											<?php echo KT_I18N::translate('Page position'); ?>
										</label>
										<input type="number" name="block_order" size="3" value="<?php echo $block_order; ?>">
										<span class="help-text">
											<?php echo KT_I18N::translate('This field controls the order in which the pages are displayed.') . '<br>' . KT_I18N::translate('You do not have to enter the numbers sequentially. If you leave holes in the numbering scheme, you can insert other pages later. For example, if you use the numbers 1, 6, 11, 16, you can later insert pages with the missing sequence numbers. Negative numbers and zero are allowed, and can be used to insert pages in front of the first one.') . '<br>' . KT_I18N::translate('When more than one page has the same position number, only one of these pages will be visible.'); ?>
										</span>
									</div>
									<div class="cell medium-4">
										<label class="h5">
											<?php echo KT_I18N::translate('Page visibility'), help_link('pages_visibility', $this->getName()); ?>
										</label>
										<?php echo select_edit_control('gedcom_id', KT_Tree::getIdList(), '', $gedcom_id); ?>
										<span class="help-text">
											<?php echo KT_I18N::translate('You can determine whether this page will be visible regardless of family tree, or whether it will be visible only to the current family tree.').
											'<br><ul><li><b>' . KT_I18N::translate('All') . '</b>&nbsp;&nbsp;&nbsp;' . KT_I18N::translate('The page will always appear, regardless of family tree.') . '</li><li><b>' . get_gedcom_setting(KT_GED_ID, 'title') . '</b>&nbsp;&nbsp;&nbsp;' . KT_I18N::translate('The pages will appear only in the currently active family trees\'s pages.') . '</li></ul>'; ?>
										</span>
									</div>
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Show this pages for which languages?'); ?>
										</label>
										<span class="help-text">
											<?php echo KT_I18N::translate('Either leave all languages un-ticked to display the page contents in every language, or tick the specific languages you want to display it for.<br>To create translated pages for different languages create multiple copies setting the appropriate language only for each version.'); ?>
										</span>
										<?php echo $languages = get_block_setting($block_id, 'languages');
										echo edit_language_checkboxes('lang_', $languages); ?>
									</div>
									<button class="button" type="submit">
										<i class="<?php echo $iconStyle; ?> fa-save"></i>
										<?php echo KT_I18N::translate('Save'); ?>
									</button>
									<button class="button secondary" type="submit" onclick="window.location='<?php echo $this->getConfigLink(); ?>';">
										<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
										<?php echo KT_I18N::translate('Cancel'); ?>
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php
				exit;
			}
		} else {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
		}
	}

	private function show() {
		global $controller, $iconStyle;

		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', 'Resources'));
		$HEADER_DESCRIPTION = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_DESCRIPTION', 'These are resources'));

		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($HEADER_TITLE)
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
			');

		$items_list		= $this->getPagesList();
		$count_items	= 0;
		foreach ($items_list as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
				$count_items = $count_items + 1;
			}
		}
		?>
		<div class="grid-x">
			<div class="cell medium-10 medium-offset-1" id="pages-page">
				<h3><?php echo $HEADER_TITLE; ?></h3>
				<?php echo $HEADER_DESCRIPTION; ?>
				<div class="clearfix"></div>
				<?php
				switch ($count_items) {
					case '0': ?>
						<h4><?php echo KT_I18N::translate('No content'); ?></h4>
						<?php break;
					case '1':
						echo $items_content;
						break;
					default: ?>
						<ul id="module_pages_content" class="tabs" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" data-deep-link="true" data-update-history="true">
							<?php $i = 1;
							foreach ($items_list as $items) {
								$i == 1 ? $class = " is-active" : $class = '';
								$languages = get_block_setting($items->block_id, 'languages');
								if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) { ?>
									<li class="tabs-title<?php echo $class; ?>">
										<a data-tabs-target="<?php echo $items->block_id; ?>" href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show#<?php echo $items->block_id; ?>">
											<span title="<?php echo KT_I18N::translate($items->pages_title); ?>"><?php echo KT_I18N::translate($items->pages_title); ?></span>
										</a>
									</li>
									<?php $i++;
								}
							} ?>
						</ul>
						<div class="tabs-content" data-tabs-content="module_pages_content" data-tabs-content="deeplinked-tabs">
								<?php $i = 1;
								foreach ($items_list as $items) {
									$i == 1 ? $class = " is-active" : $class = '';
									$languages = get_block_setting($items->block_id, 'languages');
									if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
										$items_content = $items->pages_content;
										if (strpos($items_content, '#') !== false) {
											$stats			= new KT_Stats(KT_GED_ID);
											$items_content	= $stats->embedTags($items_content);
										} ?>
										<div id="<?php echo $items->block_id; ?>" class="tabs-panel<?php echo $class; ?>">
											<?php echo $items_content; ?>
										</div>
										<?php $i++;
									}
								} ?>
						</div>
					<?php break;
				} ?>
			</div>
		</div>
	<?php }

	private function delete() {
		$block_id = KT_Filter::get('block_id');

		KT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		KT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	// Return the list of pages
	private function getPagesList() {
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_access, bs3.setting_value AS pages_content".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" JOIN `##block_setting` bs3 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_access'".
			" AND bs3.setting_name='pages_content'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

	// Return the list of pages for menu
	private function getMenupagesList() {
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_access".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_access'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

}
