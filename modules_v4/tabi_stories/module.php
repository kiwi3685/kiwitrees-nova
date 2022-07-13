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

class tabi_stories_KT_Module extends KT_Module implements KT_Module_Block, KT_Module_IndiTab, KT_Module_Config, KT_Module_Menu {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Stories');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Stories” module */ KT_I18N::translate('Add narrative stories to individuals in the family tree.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 160;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_HIDE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_edit':
			$this->edit();
			break;
		case 'admin_delete':
			$this->delete();
			$this->config();
			break;
		case 'admin_config':
			$this->config();
			break;
		case 'story_link':
			$this->story_link();
			break;
		case 'show_list':
			$this->show_list();
			break;
		case 'remove_indi':
			$indi  = KT_Filter::get('indi_ref');
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
		return false;
	}

	// Implement class KT_Module_IndiTab
	public function defaultTabOrder() {
		return 50;
	}

	// Implement class KT_Module_IndiTab
	public function getTabContent() {
		global  $controller;

		$block_ids =
			KT_DB::prepare("
				SELECT ##block.block_id
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block.block_id = ##block_setting.block_id
				 AND (##block_setting.setting_name = 'xref' AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]'))
				 AND ##block.gedcom_id=?
				 ORDER BY ##block.block_order
			")->execute(array(
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID
			))->fetchOneColumn();

		$html	= '';
		$class	= '';
		$ids	= array();
		$count_stories = 0;
		foreach ($block_ids as $block_id) {
			$block_order = get_block_order($block_id);
			// check how many stories can be shown in a language
			$languages = get_block_setting($block_id, 'languages');
			if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) {
				$count_stories ++;
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
		ob_start();

		if (KT_USER_GEDCOM_ADMIN) { // change this to KT_USER_CAN_EDIT to allow editors to create first story. ?>
			<div style="border-bottom:thin solid #aaa; margin:-10px; padding-bottom:2px;">
				<span>
					<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;xref=<?php echo $controller->record->getXref(); ?>">
						<i style="margin: 0 3px 0 10px;" class="icon-button_addnote">&nbsp;</i>
						<?php echo KT_I18N::translate('Add story'); ?>
					</a>
				</span>
				<span>
					<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config&amp;xref=<?php echo $controller->record->getXref(); ?>">
						<i style="margin: 0 3px 0 10px;" class="icon-button_linknote">&nbsp;</i>
						<?php echo KT_I18N::translate('Link this individual to an existing story '); ?>
					</a>
				</span>
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
		<div id="story_contents">
			<?php foreach ($block_ids as $block_id) {
				$languages = get_block_setting($block_id, 'languages');
				if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
					<div id="stories_<?php echo $block_id; ?>" class="<?php echo $class; ?>">
						<?php if (KT_USER_CAN_EDIT) { ?>
							<div style="margin-top:15px;">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $block_id; ?>">
									<i style="margin: 0 3px 0 0;" class="icon-button_note">&nbsp;</i><?php echo KT_I18N::translate('Edit story'); ?>
								</a>
							</div>
						<?php } ?>
						<h1><?php echo get_block_setting($block_id, 'title'); ?></h1>
						<div style="white-space: normal;">
							<?php echo get_block_setting($block_id, 'story_body'); ?>
						</div>
						<?php if ($count_stories > 1) { ?>
							<hr class="stories_divider">
						<?php } ?>
					</div>
				<?php }
			} ?>
		</div>

		<?php return '<div id="stories_tab_content">' . ob_get_clean() . '</div>';

	}

	function getStoriesCount() {
		global $controller;

		$count_of_stories =
			KT_DB::prepare("
				SELECT COUNT(##block.block_id)
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]')
				 AND gedcom_id=?
			")->execute(array(
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID
			))->fetchOne();

		return $count_of_stories;
	}

	// Implement class KT_Module_IndiTab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->getStoriesCount() > 0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return $this->getStoriesCount() == 0;
	}

	// Implement class KT_Module_IndiTab
	public function canLoadAjax() {
		return false;
	}

	// Implement class KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	// Action from the configuration page
	private function edit() {
		global $iconStyle;
		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		if (KT_USER_CAN_EDIT) {
			if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
				$block_id = KT_Filter::postInteger('block_id');
				if ($block_id) {
					KT_DB::prepare(
						"UPDATE `##block` SET gedcom_id=? WHERE block_id=?"
					)->execute(array(KT_Filter::post('gedcom_id'), $block_id));
				} else {
					KT_DB::prepare(
						"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (?, ?, ?)"
					)->execute(array(
						KT_Filter::post('gedcom_id'),
						$this->getName(),
						0
					));
					$block_id = KT_DB::getInstance()->lastInsertId();
				}
				$xref = array();
				foreach (KT_Filter::post('xref') as $indi_ref => $name) {
					$xref[] = $name;
				}
				set_block_setting($block_id, 'xref', implode(',', $xref));
				set_block_setting($block_id, 'title', KT_Filter::post('title', KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'story_body',  KT_Filter::post('story_body', KT_REGEX_UNSAFE)); // allow html
				$languages = array();
				foreach (KT_I18N::used_languages() as $code => $name) {
					if (KT_Filter::postBool('lang_' . $code)) {
						$languages[] = $code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$controller = new KT_Controller_Page();
				$controller
					->pageHeader()
					->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
					->addInlineJavascript('
						autocomplete();

						jQuery("#addField").click(function(){
							jQuery(".add_indi:last").clone().insertAfter(".add_indi:last");
							jQuery(".add_indi:last>input").attr("value", "");
						});
					');

				$block_id	= KT_Filter::get('block_id');
				if ($block_id) {
					$controller->setPageTitle(KT_I18N::translate('Edit story'));
					$title		= get_block_setting($block_id, 'title');
					$story_body	= get_block_setting($block_id, 'story_body');
					$xref		= explode(",", get_block_setting($block_id, 'xref'));
					$count_xref	= count($xref);
					$gedcom_id	= KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(KT_I18N::translate('Add story'));
					$title		= '';
					$story_body	= '';
					$gedcom_id	= KT_GED_ID;
					$xref		= KT_Filter::get('xref', KT_REGEX_XREF);
					$count_xref	= 1;
				}

				if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
					ckeditor_KT_Module::enableEditor($controller);
				}
				?>

				<div id="<?php echo $this->getName(); ?>" class="cell">
					<div class="grid-x grid-margin-x grid-margin-y">
						<div class="cell">
							<h4><?php echo $this->getTitle(); ?></h4>
							<h5  class="subheader"><?php echo KT_I18N::translate('Add / edit'); ?></h5>
							<form id="story" name="story" method="post">
								<?php echo KT_Filter::getCsrf(); ?>
								<input type="hidden" name="save" value="1">
								<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
								<input type="hidden" name="gedcom_id" value="<?php echo KT_GED_ID; ?>">
								<div class="grid-x grid-margin-x grid-margin-y">
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Title'); ?>
										</label>
										<input type="text" name="title" size="90" value="<?php echo htmlspecialchars($title); ?>">
									</div>
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Content'); ?>
										</label>
										<textarea name="story_body" class="html-edit" rows="10" cols="90"><?php echo htmlspecialchars($story_body); ?></textarea>
									</div>
									<div class="cell">
										<label class="h5">
											<?php echo KT_I18N::translate('Linked individuals'); ?>
										</label>
										<div class="grid-x">
											<?php if (!$block_id) { ?>
												<div class="cell medium-3 indi_find">
													<?php echo KT_I18N::translate('None'); ?>
												</div>
											<?php } else {
												for ($x = 0; $x < $count_xref; $x++) { ?>
													<div class="cell medium-3">
														<?php $person = KT_Person::getInstance($xref[$x]);
														if ($person) { ?>
															<p><?php echo $person->getLifespanName() ?></p>
															<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=remove_indi&amp;indi_ref=<?php echo $xref[$x]; ?>&amp;block_id=<?php echo $block_id; ?>" class="current" onclick="return confirm(\'<?php echo KT_I18N::translate('Are you sure you want to remove this?'); ?>\');">
																<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
																<?php echo KT_I18N::translate('Remove'); ?>
															</a>
														<?php } ?>
														<hr>
													</div>
												<?php }
											} ?>
										</div>
									</div>
									<?php for ($x = 0; $x < $count_xref; $x++) { ?>
										<div class="cell medium-6 large-3 add_indi">
											<?php echo autocompleteHtml(
		 										'number' . $x, // id
		 										'INDI', // TYPE
		 										'', // autocomplete-ged
		 										'', // input value
		 										KT_I18N::translate('Add an individual'), // placeholder
		 										'xref[]', // hidden input name
		 										'', // hidden input value
		 									); ?>
										</div>
										<br>
									<?php } ?>
									<div class="cell">
										<button class="button margin-0" type="button" id="addField">
											<?php echo KT_I18N::translate('Add another individual'); ?>
										</button>
									</div>
									<div class="cell">
										<hr>
										<label class="h5">
											<?php echo KT_I18N::translate('Show this pages for which languages?'); ?>
										</label>
										<span class="help-text">
											<?php echo KT_I18N::translate('Either leave all languages un-ticked to display the page contents in every language, or tick the specific languages you want to display it for.<br>To create translated pages for different languages create multiple copies setting the appropriate language only for each version.'); ?>
										</span>
										<?php echo $languages = get_block_setting($block_id, 'languages');
										echo edit_language_checkboxes('lang_', $languages); ?>
									</div>
								</div>

								<?php echo submitButtons('window.location=\'' . $this->getConfigLink() . '\''); ?>

							</form>
						</div>
					</div>
				</div>
				<?php
				exit;
			}
		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	private function config() {
		global $iconStyle;
		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
			    jQuery("#story_table").sortable({items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});
			    //-- update the order numbers after drag-n-drop sorting is complete
			    jQuery("#story_table").bind("sortupdate", function(event, ui) {
					jQuery("#"+jQuery(this).attr("id")+" input").each(
						function (index, value) {
							value.value = index+1;
						}
					);
				});
			');

		$stories = KT_DB::prepare("
			SELECT block_id, xref, block_order
			 FROM ##block
			 WHERE module_name=?
			 AND gedcom_id=?
		")->execute(array($this->getName(), KT_GED_ID))->fetchAll();

		$new_xref = KT_Filter::get('xref', KT_REGEX_XREF);

		//transfer old xref in ##block to new xref in ##block_setting
		foreach ($stories as $story) {
			if ($story->xref != NULL) {
				set_block_setting($story->block_id, 'xref', $story->xref);
				KT_DB::prepare(
					"UPDATE `##block` SET xref = NULL WHERE block_id=?"
				)->execute(array($story->block_id));
			}
		}

		foreach ($stories as $this->getName=>$story) {
			$order = KT_Filter::post('taborder-'. $story->block_id);
			if ($order) {
				KT_DB::prepare(
					"UPDATE `##block` SET block_order=? WHERE block_id=?"
				)->execute(array($order, $story->block_id));
				$story->block_order = $order; // Make the new order take effect immediately
			}
		}
		uasort($stories, function ($x, $y) {
			return $x->block_order > $y->block_order;
		});
		?>

		<div id="<?php echo $this->getName(); ?>" class="cell">
			<div class="grid-x grid-margin-x grid-margin-y">
				<div class="cell">
					<h4 class="inline"><?php echo $this->getTitle(); ?></h4>
<!--				<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/modules-faqs/pages/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="<?php echo $iconStyle; ?> fa-comments"></i></a>-->
					<h5  class="subheader"><?php echo KT_I18N::translate('Configuration'); ?></h5>
					<div class="grid-x grid-margin-y">
						<div class="cell medium-6">
							<form method="get" action="<?php echo KT_SCRIPT_NAME; ?>">
								<label><?php echo KT_I18N::translate('Family tree'); ?></label>
								<input type="hidden" name="mod", value="<?php echo $this->getName(); ?>">
								<input type="hidden" name="mod_action", value="admin_config">
								<div class="input-group">
									<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
									<div class="input-group-button">
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-eye"></i>
											<?php echo KT_I18N::translate('Show'); ?>
										</button>
									</div>
								</div>
							</form>
						</div>
						<div class="cell">
							<button class="button margin-0" onclick="window.location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit'">
								<i class="<?php echo $iconStyle; ?> fa-plus"></i>
								<?php echo KT_I18N::translate('Add story'); ?>
							</button>
							<hr>
						</div>
						<?php if (count($stories) > 0) { ?>
							<div class="cell">
								<form name="story_list" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
									<table id="story_table">
										<thead>
											<tr>
												<th></th>
												<th><?php echo KT_I18N::translate('Story title'); ?></th>
												<th><?php echo KT_I18N::translate('Individual'); ?></th>
												<th><?php echo KT_I18N::translate('Order'); ?></th>
												<th><?php echo KT_I18N::translate('Edit'); ?></th>
												<th><?php echo KT_I18N::translate('Delete'); ?></th>
												<?php if ($new_xref) { ?>
													<th><?php echo KT_I18N::translate('Link'); ?></th>
												<?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											$order = 1;
											foreach ($stories as $story) {
												$story_title	= get_block_setting($story->block_id, 'title');
												$xref			= explode(",", get_block_setting($story->block_id, 'xref'));
												$count_xref		= count($xref); ?>
												<tr class="sortme">
													<td>
														<i class="<?php echo $iconStyle; ?> fa-bars"></i>
													</td>
													<td><?php echo $story_title; ?></td>
													<td>
														<?php for ($x = 0; $x < $count_xref; $x++) {
															$indi[$x] = KT_Person::getInstance($xref[$x]);
															if ($indi[$x]) { ?>
																<a href="<?php echo $indi[$x]->getHtmlUrl(); ?>#stories" class="current">
																	<?php echo $indi[$x]->getFullName(); ?>
																</a>
															<?php } else { ?>
																<span class="error"><?php echo $xref[$x]; ?></span>
															<?php }
														} ?>
													</td>
													<td>
														<input type="text" value="<?php echo $order; ?>" name="taborder-<?php echo $story->block_id; ?>">
													</td>
													<td class="text-center">
														<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $story->block_id; ?>">
															<i class="<?php echo $iconStyle; ?> fa-edit"></i>
														</a>
													</td>
													<td class="text-center">
														<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $story->block_id; ?>" onclick="return confirm(\'<?php echo KT_I18N::translate('Are you sure you want to delete this story?'); ?>\');">
															<i class="<?php echo $iconStyle; ?> fa-trash-can"></i>
														</a>
													</td>
													<?php if ($new_xref) { ?>
														<td class="text-center">
															<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=story_link&amp;block_id=<?php echo $story->block_id; ?>&amp;xref=<?php echo $new_xref; ?>" onclick="return confirm(\'<?php echo KT_I18N::translate('Are you sure you want to link to this story?'); ?>\');">
																<i class="<?php echo $iconStyle; ?> fa-link"></i>
															</a>
														</td>
													<?php } ?>
												</tr>
												<?php
												$order++; ?>
											<?php } ?>
										</tbody>
									</table>
									<button class="button margin-0" type="submit">
										<i class="<?php echo $iconStyle; ?> fa-save"></i>
										<?php echo KT_I18N::translate('Save'); ?>
									</button>
								</form>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php }

	private function show_list() {
		global $controller;
		$controller = new KT_Controller_Page();
		$controller->addExternalJavascript(KT_DATATABLES_JS);
		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_DATATABLES_HTML5)
				->addExternalJavascript(KT_JQUERY_DT_BUTTONS);
		}
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery("#story_table").dataTable({
					dom: \'<"H"pBf<"clear">irl>t<"F"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csv"}],
					jQueryUI: true,
					autoWidth: false,
					paging: true,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					sorting: [[0,"asc"]],
					displayLength: 20,
					columns: [
						/* 0-name */ null,
						/* 1-NAME */ null
					]
				});
			');

		$stories = KT_DB::prepare("
			SELECT block_id
			 FROM `##block`
			 WHERE module_name=?
			 AND gedcom_id=?
		")->execute(array($this->getName(), KT_GED_ID))->fetchAll(); ?>

		<h4><?php echo KT_I18N::translate('Stories'); ?></h4>
		<?php if (count($stories) > 0) { ?>
			<table id="story_table" class="width100">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Story title'); ?></th>
						<th><?php echo KT_I18N::translate('Individual'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($stories as $story) {
						$story_title = get_block_setting($story->block_id, 'title');
						$xref = explode(",", get_block_setting($story->block_id, 'xref'));
						$count_xref = count($xref);
						// if one indi is private, the whole story is private.
							$private = 0;
							for ($x = 0; $x < $count_xref; $x++) {
								$indi[$x] = KT_Person::getInstance($xref[$x]);
								if ($indi[$x] && !$indi[$x]->canDisplayDetails()) {
									$private = $x+1;
								}
							}
						if ($private == 0) {
							$languages=get_block_setting($story->block_id, 'languages');
							if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
								<tr>
									<td><?php echo $story_title; ?></td>
									<td>
										<?php for ($x = 0; $x < $count_xref; $x++) {
											$indi[$x] = KT_Person::getInstance($xref[$x]);
											if (!$indi[$x]){ ?>
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
		<?php }
	}

	// Delete a story from the database
	private function delete() {
		if (KT_USER_CAN_EDIT) {
			$block_id = KT_Filter::get('block_id');

			$block_order=KT_DB::prepare("
				SELECT block_order FROM `##block` WHERE block_id=?
			")->execute(array($block_id))->fetchOne();

			KT_DB::prepare("
				DELETE FROM `##block_setting` WHERE block_id=?
			")->execute(array($block_id));

			KT_DB::prepare("
				DELETE FROM `##block` WHERE block_id=?
			")->execute(array($block_id));

		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	// Link an individual to an existing story directly
	private function story_link() {
		if (KT_USER_GEDCOM_ADMIN) {
			$block_id = KT_Filter::get('block_id');
			$new_xref = KT_Filter::get('xref', KT_REGEX_XREF);
			$xref = explode(",", get_block_setting($block_id, 'xref'));
			$xref[] = $new_xref;
			set_block_setting($block_id, 'xref', implode(',', $xref));
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH. 'individual.php?pid=' . $new_xref);
		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	// Delete an individual linked to a story, from the database
	private function removeIndi($indi, $block_id) {
		$xref = explode(",", get_block_setting($block_id, 'xref'));
		$xref = array_diff($xref, array($indi));
		set_block_setting($block_id, 'xref', implode(',', $xref));
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH. 'module.php?mod=' . $this->getName() . '&mod_action=admin_edit&block_id=' . $block_id);
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		//-- Stories menu item
		$menu = new KT_Menu($this->getTitle(), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show_list', 'menu-story');
		return $menu;
	}

}
