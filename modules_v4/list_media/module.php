<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class list_media_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Media objects');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the media list module */ KT_I18N::translate('A list of media objects');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_List
	public function getListMenus() {
		global $controller, $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		// Do not show empty lists
		$row = KT_DB::prepare(
			"SELECT EXISTS(SELECT 1 FROM `##media` WHERE m_file=?)"
		)->execute(array(KT_GED_ID))->fetchOneRow();
		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-obje'
			);
			$menus[] = $menu;
			return $menus;
		} else {
			return false;
		}
	}

	// Display list
	public function show() {
		global $controller, $TEXT_DIRECTION, $iconStyle;
		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		require_once KT_ROOT . 'includes/functions/functions_print_facts.php';

		$search = KT_Filter::get('search');
		$sortby = KT_Filter::get('sortby' , 'file|title' , 'title');
		if (!KT_USER_CAN_EDIT && !KT_USER_CAN_ACCEPT) {
			$sortby = 'title';
		}

		$per_page = array(9, 18, 30, 42, 51, 78, 99, 123, 150, 198);
		$start          = KT_Filter::getInteger('start');
		$max            = KT_Filter::get('max' , implode("|", $per_page), '18');
		$folder         = KT_Filter::get('folder' , null, ''); // MySQL needs an empty string, not NULL
		$reset          = KT_Filter::get('reset');
		$apply_filter   = KT_Filter::get('apply_filter');
		$filter         = KT_Filter::get('filter' , null, ''); // MySQL needs an empty string, not NULL
		$subdirs        = KT_Filter::get('subdirs' , 'on');
		$subdirs		= KT_Filter::getBool('subdirs');
		$form_type      = KT_Filter::get('form_type');

		// reset all variables
		if ($reset == 'reset') {
			$sortby		= 'title';
			$max		= '18';
			$folder		= '';
			$subdirs	= 0;
			$filter		= '';
			$form_type	= '';
		}

		$url = 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL;

		// A list of all subfolders used by this tree
		$folders = KT_Query_Media::folderList();

		// A list of all media objects matching the search criteria
		$medialist = KT_Query_Media::mediaList(
			$folder,
			$subdirs ? 'include' : 'exclude' ,
			$sortby,
			$filter,
			$form_type
		);

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_media', KT_USER_ACCESS_LEVEL))
			->setPageTitle(KT_I18N::translate('Media objects'))
			->pageHeader();
		?>

		<div id="medialist-page" class="grid-x grid-padding-x">
			<div class="cell large-10 large-offset-1">
				<h3><?php echo $controller->getPageTitle(); ?></h3>
				<form method="get" action="?">
					<input type="hidden" name="action" value="filter">
					<input type="hidden" name="search" value="yes">
					<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
					<input type="hidden" name="mod_action" value="show">
					<div class="grid-x grid-margin-x">
						<div class="cell medium-2">
							<label class="h6" for="folder"><?php echo KT_I18N::translate('Folder'); ?></label>
							<?php echo select_edit_control('folder' , $folders, null, $folder); ?>
						</div>
						<div class="cell medium-2">
							<label class="h6" for="subdirs"><?php echo KT_I18N::translate('Include subfolders'); ?></label>
							<div class="switch">
								<input class="switch-input" id="subdirs" type="checkbox" name="subdirs" <?php echo $subdirs ?  'checked="checked"' : ''; ?>>
								<label class="switch-paddle" for="subdirs">
									<span class="show-for-sr"><?php echo KT_I18N::translate('Include subfolders'); ?></span>
									<span class="switch-active" aria-hidden="true"><?php echo KT_I18N::translate('on'); ?></span>
									<span class="switch-inactive" aria-hidden="true"><?php echo KT_I18N::translate('off'); ?></span>
								</label>
							</div>
						</div>
						<?php if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) { ?>
							<div class="cell medium-2 show-for-medium">
								<label class="h6" for="folder"><?php echo KT_I18N::translate('Sort order'); ?></label>
								<select name="sortby" id="sortby">
									<option value="title" <?php echo ($sortby == 'title') ? 'selected="selected"' : ''; ?>>
										<?php echo /* I18N: An option in a list-box */ KT_I18N::translate('sort by title'); ?>
									</option>
									<option value="file" <?php echo ($sortby == 'file') ? 'selected="selected"' : ''; ?>>
										<?php echo /* I18N: An option in a list-box */ KT_I18N::translate('sort by filename'); ?>
									</option>
								</select>
							</div>
						<?php } ?>
						<div class="cell medium-2">
							<label class="h6" for="form-type"><?php echo KT_I18N::translate('Type'); ?></label>
							<select name="form_type" id="form-type">
								<option value=""></option>
								<option value="blank" <?php echo $form_type == 'blank' ? 'selected' : ''; ?>>
									<?php echo /* I18N: A filter on the media list for items with no TYPE tag set */ KT_I18N::translate('No type'); ?>
								</option>
								<?php foreach (KT_Gedcom_Tag::getFileFormTypes() as $value => $label) { ?>
									<option value="<?php echo $value; ?>" <?php echo $value === $form_type ? 'selected' : ''; ?>>
										<?php echo $label; ?>
									</option>
								<?php } ?>
							</select>
						</div>
						<div class="cell medium-2 show-for-medium">
							<label class="h6" for="max"><?php echo KT_I18N::translate('Media objects per page'); ?></label>
							<select name="max" id="max">
								<?php foreach ($per_page as $selectEntry) {
									echo '<option value="' , $selectEntry, '"';
									if ($selectEntry == $max) echo ' selected="selected"';
									echo '>' , $selectEntry, '</option>';
								} ?>
							</select>
						</div>
						<div class="cell medium-2">
							<label class="h6" for="filter"><?php echo KT_I18N::translate('Search filters'); ?></label>
							<input id="filter" name="filter" value="<?php echo KT_Filter::escapeHtml($filter); ?>">
						</div>
					</div>
					<div class="grid-x grid-margin-x">
						<div class="cell medium-3">
							<button class="button" type="submit">
								<i class="<?php echo $iconStyle; ?> fa-search"></i>
								<?php echo KT_I18N::translate('Search'); ?>
							</button>
							<button class="button" type="submit" name="reset" value="reset">
								<i class="<?php echo $iconStyle; ?> fa-sync"></i>
								<?php echo KT_I18N::translate('Reset'); ?>
							</button>
						</div>
					</div>
				</form>
				<hr>
				<!-- end of form -->

				<?php
				if ($search) {
					if (!empty($medialist)) {
						// Count the number of items in the medialist
						$ct		= count($medialist);
						$start	= 0;
						if (isset($_GET['start'])) {
							$start = $_GET['start'];
						}
						$count = $max;
						if ($start + $count > $ct) {
							$count = $ct - $start;
						}
					} else {
						$ct = '0';
					}

					if ($ct > 0) {
						echo '<div class="grid-x">';
							// Prepare pagination details
							$currentPage	= ((int) ($start / $max)) + 1;
							$lastPage		= (int) (($ct + $max - 1) / $max);

							$pagination = '<p class="text-left">';
								if ($TEXT_DIRECTION == 'ltr') {
									if ($ct > $max) {
										if ($currentPage > 1) {
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="fa fa-lg fa-angle-double-left"></a>';
										}
										if ($start>0) {
											$newstart = $start-$max;
											if ($start<0) $start = 0;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-left"></a>';
										}
									}
								} else {
									if ($ct > $max) {
										if ($currentPage < $lastPage) {
											$lastStart = ((int) ($ct / $max)) * $max;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-double-right"></a>';
										}
										if ($start+$max < $ct) {
											$newstart = $start+$count;
											if ($start<0) $start = 0;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-right"></a>';
										}
									}
								}
							$pagination .= '</p>
							<p class="text-center">' . KT_I18N::translate('Page %s of %s' , $currentPage, $lastPage). '</p>
							<p class="text-right">';
								if ($TEXT_DIRECTION == 'ltr') {
									if ($ct>$max) {
										if ($start + $max < $ct) {
											$newstart = $start+$count;
											if ($start < 0) $start = 0;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-right"></a>';
										}
										if ($currentPage < $lastPage) {
											$lastStart = ((int) ($ct / $max)) * $max;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-double-right"></a>';
										}
									}
								} else {
									if ($ct > $max) {
										if ($start>0) {
											$newstart = $start-$max;
											if ($start < 0) $start = 0;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="fa fa-lg fa-angle-left"></a>';
										}
										if ($currentPage > 1) {
											$lastStart = ((int) ($ct / $max)) * $max;
											$pagination .= '<a href="' . $url . '&amp;action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="fa fa-lg fa-angle-double-left"></a>';
										}
									}
								}
							$pagination .= '</p>'; ?>

							<!-- Output display -->
							<h4><?php echo KT_I18N::translate('%s media objects found', KT_I18N::number($ct)); ?></h4>
							<div class="cell">
								<?php echo $pagination; ?>
							</div>
							<div class="cell">
								<div class="grid-x grid-margin-x grid-margin-y">
									<?php for ($i = $start, $n = 0; $i < $start + $count; $i ++) {
										$mediaobject = $medialist[$i]; ?>
										<div class="cell medium-4">
											<div class="card">
												<div class="card-divider">
													<div class="cell">
													<div class="grid-x grid-padding-x">
														<?php if (KT_USER_CAN_EDIT) { ?>
															<div class="cell medialist_menu">
																<?php echo KT_Controller_Media::getMediaListMenu($mediaobject); ?>
															</div>
														<?php } ?>
														<div class="cell small-3 medialist_image">
															<?php echo $mediaobject->displayImage(); ?>
														</div>
														<div class="cell auto medialist_title">
															<?php // If sorting by title, highlight the title. If sorting by filename, highlight the filename
															if ($sortby == 'title') { ?>
																<a href="<?php echo $mediaobject->getHtmlUrl(); ?>">
																	<?php echo $mediaobject->getFullName(); ?>
																</a>
															<?php } else { ?>
																<b>
																	<a href="<?php echo $mediaobject->getHtmlUrl(); ?>">
																		<?php echo basename($mediaobject->getFilename()); ?>
																	</a>
																</b>
																<?php echo KT_Gedcom_Tag::getLabelValue('TITL' , $mediaobject->getFullName());
															} ?>
														</div>
														</div>
													</div>
												</div>
												<div class="card-section">
													<?php if ($mediaobject->isExternal()) {
														echo KT_Gedcom_Tag::getLabelValue('URL' , $mediaobject->getFilename());
													} else {
														if ($mediaobject->fileExists()) {
															if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) {
																echo KT_Gedcom_Tag::getLabelValue('FILE' , $mediaobject->getFilename());
															}
															echo KT_Gedcom_Tag::getLabelValue('FORM' , $mediaobject->mimeType());
															echo KT_Gedcom_Tag::getLabelValue('TYPE' , KT_Gedcom_Tag::getFileFormTypeValue($mediaobject->getMediaType()));
															switch ($mediaobject->isPrimary()) {
																case 'Y':
																	echo KT_Gedcom_Tag::getLabelValue('_PRIM', KT_I18N::translate('yes'));
																	break;
																case 'N':
																	echo KT_Gedcom_Tag::getLabelValue('_PRIM', KT_I18N::translate('no'));
																	break;
															}
															echo KT_Gedcom_Tag::getLabelValue('__FILE_SIZE__' , $mediaobject->getFilesize());
															$imgsize = $mediaobject->getImageAttributes();
															if ($imgsize['WxH']) {
																echo KT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__' , $imgsize['WxH']);
															}
														} else { ?>
															<p class="ui-state-error">
																<?php echo /* I18N: %s is a filename */ KT_I18N::translate('The file “%s” does not exist.' , $mediaobject->getFilename()); ?>
															</p>
														<?php }
													}
													if (is_null(print_fact_sources($mediaobject->getGedcomRecord(), 1)) && is_null(print_fact_notes($mediaobject->getGedcomRecord(), 1)) ) { ?>
														<div class="media-list-sources" style="display:none">
													<?php } else { ?>
														<div class="media-list-sources">
													<?php }
														echo print_fact_sources($mediaobject->getGedcomRecord(), 1),
														print_fact_notes($mediaobject->getGedcomRecord(), 1); ?>
													</div>
													<?php foreach ($mediaobject->fetchLinkedIndividuals('OBJE') as $individual) { ?>
														<a class="media-list-link" href="<?php echo $individual->getHtmlUrl(); ?>">
															<?php echo KT_I18N::translate('View person'); ?> — <?php echo $individual->getFullname(); ?>
														</a>
														<br>
													<?php }
													foreach ($mediaobject->fetchLinkedFamilies('OBJE') as $family) { ?>
														<a class="media-list-link" href="<?php echo $family->getHtmlUrl(); ?>">
															<?php echo KT_I18N::translate('View family'); ?> — <?php echo $family->getFullname(); ?>
														</a>
														<br>
													<?php }
													foreach ($mediaobject->fetchLinkedSources('OBJE') as $source) { ?>
														<a class="media-list-link" href="<?php echo $source->getHtmlUrl(); ?>">
															<?php echo KT_I18N::translate('View source'); ?> — <?php echo $source->getFullname(); ?>
														</a>
														<br>
													<?php } ?>
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
							<div class="cell">
								<?php echo $pagination; ?>
							</div>
						</div>
					<?php }
				} ?>
			</div>
		</div>

	<?php }

}
