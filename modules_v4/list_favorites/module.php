<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class list_favorites_KT_Module extends KT_Module implements KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Favorites');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the sources list module */ KT_I18N::translate('Display and manage all your personal and family tree favorites on a single page.');
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
			"SELECT SQL_CACHE EXISTS(SELECT 1 FROM `##favorites` WHERE gedcom_id=? )"
		)->execute(array(KT_GED_ID))->fetchOneRow();

		if ($row) {
			$menus = array();
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
				'menu-list-fav'
			);
			$menus[] = $menu;
			return $menus;
		} else {
			return false;
		}
	}

	// Display list
	public function show() {
		global $KT_TREE, $show_full, $PEDIGREE_FULL_DETAILS, $PEDIGREE_SHOW_GENDER, $controller, $iconStyle;
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_favorites', KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader();

		$action		= KT_Filter::post('action');
		$favType	= false;
		$record		= false;
		switch ($action) {
			case 'deletefav':
				$favorite_id = KT_Filter::post('favorite_id');
				if ($favorite_id) {
					self::deleteFavorite($favorite_id);
				}
				unset($_POST['action']);
				break;
			case 'addfav':
				$gid		= KT_Filter::post('gid');
				$favnote	= KT_Filter::post('favnote');
				$url		= KT_Filter::post('listurl', KT_REGEX_URL);
				$favtitle	= KT_Filter::post('listfavtitle');
				$favType	= KT_Filter::postBool('favType');

				if ($gid) {
					$record = KT_GedcomRecord::getInstance($gid);
					if ($record && $record->canDisplayDetails()) {
						self::addFavorite(array(
							'user_id'	=> (KT_USER_ID && $favType) ? KT_USER_ID : null,
							'gedcom_id'	=> KT_GED_ID,
							'gid'		=> $record->getXref(),
							'type'		=> $record->getType(),
							'url'		=> null,
							'note'		=> $favnote,
							'title'		=> $favtitle,
						));
					}
				} elseif ($url) {
					self::addFavorite(array(
						'user_id'	=> (KT_USER_ID && $favType) ? KT_USER_ID : null,
						'gedcom_id'	=> KT_GED_ID,
						'gid'		=> null,
						'type'		=> 'URL',
						'url'		=> $url,
						'note'		=> $favnote,
						'title'		=> $favtitle ? $favtitle : $url,
					));
				}
				unset($_POST['action']);
				break;
		}

		// Override GEDCOM configuration temporarily
		if (isset($show_full)) {
			$saveShowFull = $show_full;
		}
		$savePedigreeFullDetails	= $PEDIGREE_FULL_DETAILS;
		$show_full					= 1;
		$PEDIGREE_FULL_DETAILS		= 1;
		$PEDIGREE_SHOW_GENDER		= 1;

		$favorites = $this->getFavorites(KT_USER_ID);
		if (!is_array($favorites)) {
			$favorites = array();
		}

		$id			= $this->getName();
		$class		= $this->getName();
		$config		= true;
		$title		= $this->getTitle();
		$style		= 4; // 1 means "regular box", 2 means "wide box", 3 means "vertical box", 4 means "card"
		$content	= '';
		$favType ? $favtype	= true : $favType = false;

		if (KT_USER_ID) {
			$controller
				->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
				->addInlineJavascript('
					autocomplete();
					// Open add favorite forms on selection
					jQuery(".switch input[name=\'favSwitchGroup\']").click(function() {
			            if (this.checked) {
							jQuery("div.radioCheck").addClass("is-hidden");
			                jQuery(this).parent().siblings("div.radioCheck").removeClass("is-hidden");
			            } else if (!this.checked){
			                jQuery("div.radioCheck").addClass("is-hidden");
			            }

			        });

				');
		}

		echo pageStart('favoriteslist', $controller->getPageTitle());
			if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) {
				$uniqueID = (int)(microtime(true) * 1000000); // This block can theoretically appear multiple times, so use a unique ID.?>

				<button class="button" data-toggle="add_favorite display_favorites">
					<i class="<?php echo $iconStyle; ?> fa-plus"></i>
					<?php echo KT_I18N::translate('Add a favorite'); ?>
				</button>

				<div class="grid-x is-hidden" id="add_favorite" data-toggler=".is-hidden">
					<div class="cell">
						<div class="grid-x grid-margin-x">
							<div class="cell medium-3">
								<form method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
									<input type="hidden" name="action" value="addfav">
									<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
									<div class="switch">
										<input class="switch-input" id="switchIndi" type="radio" checked name="favSwitchGroup">
										<label class="switch-paddle" for="switchIndi"><span class="show-for-sr"><?php echo KT_I18N::translate('Add an individual'); ?></span></label>
									</div>
									<label class="h6"><?php echo KT_I18N::translate('Add an individual'); ?></label>
									<div class="grid-x radioCheck" id="indi">
										<div class="input-group autocomplete_container">
											<?php $record ? $person = KT_Person::getInstance($record->getXref()) : $person = ''; ?>
											<input
												data-autocomplete-type="INDI"
												type="text"
												id="autocompleteInput-favIndi"
											>
											<span class="input-group-label">
												<button class="clearAutocomplete autocomplete_icon">
													<i class="<?php echo $iconStyle; ?> fa-times"></i>
												</button>
											</span>
										</div>
										<input type="hidden" id="selectedValue-indi" name="gid">
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Select type of favorite'); ?></label>
											<input type="radio" name="indi" value=1 id="favTypeFamily1" required<?php echo $favType ? ' checked' : ''; ?><?php echo $favType ? ' checked' : ''; ?>><label for="favTypeFamily1"><?php echo KT_I18N::translate('Family tree favorite'); ?></label>
											<input type="radio" name="indi" value=0 id="favTypePersonal1"<?php echo !$favType ? ' checked' : ''; ?>><label for="favTypePersonal1"><?php echo KT_I18N::translate('My favorite'); ?></label>
										</div>
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Enter an optional note about this favorite'); ?></label>
											<textarea name="favnote" rows="6" cols="50"></textarea>
										</div>
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-save"></i>
											<?php echo KT_I18N::translate('Save'); ?>
										</button>
									</div>
								</form>
							</div>
							<div class="cell medium-3">
								<form method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
									<input type="hidden" name="action" value="addfav">
									<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
									<div class="switch">
										<input class="switch-input" id="switchFam" type="radio" name="favSwitchGroup">
										<label class="switch-paddle" for="switchFam"><span class="show-for-sr"><?php echo KT_I18N::translate('Add a family'); ?></span></label>
									</div>
									<label class="h6"><?php echo KT_I18N::translate('Add a family'); ?></label>
									<div class="grid-x radioCheck is-hidden" id="fam">
										<div class="input-group autocomplete_container">
											<?php $record ? $family = KT_Family::getInstance($record->getXref()) : $family = ''; ?>
											<input
												data-autocomplete-type="FAM"
												type="text"
												id="autocompleteInput-favFam"
											>
											<span class="input-group-label">
												<button class="clearAutocomplete autocomplete_icon">
													<i class="<?php echo $iconStyle; ?> fa-times"></i>
												</button>
											</span>
										</div>
										<input type="hidden" id="selectedValue-fam" name="gid">
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Select type of favorite'); ?></label>
											<input type="radio" name="fam" value=1 id="favTypeFamily2" required<?php echo $favType ? ' checked' : ''; ?>><label for="favTypeFamily2"><?php echo KT_I18N::translate('Family tree favorite'); ?></label>
											<input type="radio" name="fam" value=0 id="favTypePersonal2" <?php echo !$favType ? ' checked' : ''; ?>><label for="favTypePersonal2"><?php echo KT_I18N::translate('My favorite'); ?></label>
										</div>
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Enter an optional note about this favorite'); ?></label>
											<textarea name="favnote" rows="6" cols="50"></textarea>
										</div>
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-save"></i>
											<?php echo KT_I18N::translate('Save'); ?>
										</button>
									</div>
								</form>
							</div>
							<div class="cell medium-3">
								<form method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
									<input type="hidden" name="action" value="addfav">
									<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
									<div class="switch">
										<input class="switch-input" id="switchSour" type="radio" name="favSwitchGroup">
										<label class="switch-paddle" for="switchSour"><span class="show-for-sr"><?php echo KT_I18N::translate('Add a source'); ?></span></label>
									</div>
									<label class="h6"><?php echo KT_I18N::translate('Add a source'); ?></label>
									<div class="grid-x radioCheck is-hidden" id="sour">
										<div class="input-group autocomplete_container">
											<?php $record ? $source = KT_Source::getInstance($record->getXref()) : $source = ''; ?>
											<input
												data-autocomplete-type="SOUR"
												type="text"
												id="autocompleteInput-favSOUR"
											>
											<span class="input-group-label">
												<button class="clearAutocomplete autocomplete_icon">
													<i class="<?php echo $iconStyle; ?> fa-times"></i>
												</button>
											</span>
										</div>
										<input type="hidden" id="selectedValue-sour" name="gid">
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Select type of favorite'); ?></label>
											<input type="radio" name="sour" value=1 id="favTypeFamily3" required<?php echo $favType ? ' checked' : ''; ?>><label for="favTypeFamily3"><?php echo KT_I18N::translate('Family tree favorite'); ?></label>
											<input type="radio" name="sour" value=0 id="favTypePersonal3"<?php echo !$favType ? ' checked' : ''; ?>><label for="favTypePersonal3"><?php echo KT_I18N::translate('My favorite'); ?></label>
										</div>
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Enter an optional note about this favorite'); ?></label>
											<textarea name="favnote" rows="6" cols="50"></textarea>
										</div>
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-save"></i>
											<?php echo KT_I18N::translate('Save'); ?>
										</button>
									</div>
								</form>
							</div>
							<div class="cell medium-3">
								<form method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
									<input type="hidden" name="action" value="addfav">
									<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
									<div class="switch">
										<input class="switch-input" id="switchUrl" type="radio" name="favSwitchGroup">
										<label class="switch-paddle" for="switchUrl"><span class="show-for-sr"><?php echo KT_I18N::translate('Add a URL'); ?></span></label>
									</div>
									<label class="h6"><?php echo KT_I18N::translate('Add a URL'); ?></label>
									<div class="grid-x radioCheck is-hidden" id="favurl">
										<input
											type="text"
											name="listurl"
											id="listurl"
											value=""
											placeholder="<?php echo KT_Gedcom_Tag::getLabel('URL'); ?>"
										>
										<input
											type="text"
											name="listurltitle"
											id="listurltitle"
											value=""
											placeholder="<?php echo KT_I18N::translate('Title'); ?>"
										>
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Select type of favorite'); ?></label>
											<input type="radio" name="url" value=1 id="favTypeFamily4" required<?php echo $favType ? ' checked' : ''; ?>><label for="favTypeFamily4"><?php echo KT_I18N::translate('Family tree favorite'); ?></label>
											<input type="radio" name="url" value=0 id="favTypePersonal4"<?php echo !$favType ? ' checked' : ''; ?>><label for="favTypePersonal4"><?php echo KT_I18N::translate('My favorite'); ?></label>
										</div>
										<div class="cell">
											<label class="h6"><?php echo KT_I18N::translate('Enter an optional note about this favorite'); ?></label>
											<textarea name="favnote" rows="6" cols="50"></textarea>
										</div>
										<button class="button" type="submit">
											<i class="<?php echo $iconStyle; ?> fa-save"></i>
											<?php echo KT_I18N::translate('Save'); ?>
										</button>
									</div>
								</form>
							</div>
						</div>
						</form>
					</div>
				</div>
			<?php }
			if ($favorites) {
				$content .= '<div class="grid-x grid-margin-x grid-margin-y" id="display_favorites" data-toggler=".is-hidden">';
					foreach ($favorites as $key => $favorite) {
						if (isset($favorite['id'])) {
							$key = $favorite['id'];
						}
						$favType	= '';
						$remove		= '';
						if ($favorite['user_id']) {
							$favType	= '<span class="float-right">' . KT_I18N::translate('My favorite') . '</span>';
						} else {
							$favType	= '<span class="float-right">' . KT_I18N::translate('Family tree favorite') . '</span>';
						}
						if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) {
							$remove		= '<span class="float-left">' . $this->removeFavourite($key) . '</span>';
						}
						if ($favorite['type'] == 'URL') {
							$content .= '<div class="cell medium-4 large-3">
								<div class="clearfix">' . $remove . $favType . '</div>';
								ob_start();
								$this->print_url($favorite);
								$content .=  ob_get_clean() . '
							</div>';
						} else {
							$record = KT_GedcomRecord::getInstance($favorite['gid']);
							if ($record && $record->canDisplayDetails()) {
								if ($record->getType() == 'INDI') {
									$content .= '<div class="cell medium-4 large-3">
										<div class="clearfix">' . $remove . $favType . '</div>';
										ob_start();
										print_pedigree_person($record, $style, 1, $key, $favorite['note']);
										$content .= ob_get_clean() . '
									</div>';
								} else {
									$content .= '<div class="cell medium-4 large-3">
										<div class="clearfix">' . $remove . $favType . '</div>';
										ob_start();
										$this->print_other($record, $favorite);
										$content .=  ob_get_clean() . '
									</div>';
								}
							}
						}
					}
				$content .= '</div>';
			}
			echo $content;
		pageClose();

		// Restore GEDCOM configuration
		unset($show_full);
		if (isset($saveShowFull)) {
			$show_full = $saveShowFull;
		}
		$PEDIGREE_FULL_DETAILS = $savePedigreeFullDetails;

	}

	// Create a "remove favourite" option
	public static function removeFavourite($key) {
		global $iconStyle;
		$removeFavourite = '<a href="index.php?action=deletefav&amp;favorite_id=' . $key . '"
			onclick="return confirm(\'' . KT_I18N::translate('Are you sure you want to remove this?') . '\');">
			<span>
				<i class="' . $iconStyle . ' fa-trash-alt"></i>' . KT_I18N::translate('Remove') . '
			</span>
		</a>';
		return $removeFavourite;
	}

	public static function print_other($record, $favorite) {
		global $iconStyle;

		$pid			= $record->getXref();
		$tag			= 'span';
		$name			= '<a class="h6" href="' . $record->getHtmlUrl() . '" target="_blank">' . $record->getFullName() . '</a>';
		$addname		= '';
		$thumbnail		= $record->displayImage(true);
		$recordYear		= $record->getType() == 'FAM' ? KT_I18N::translate('Marriage year %s', $record->getMarriageYear()) : '';
		$displayNote	= $favorite['note'];
		$detailedView	= $record->format_list_details();
		$uniqueID		= (int)(microtime(true) * 1000000);
		$dataToggle		= $pid . '-' . $uniqueID;

		require KT_THEME_DIR . 'templates/person_card_template.php';

	}

	public static function print_url($favorite) {
		global $iconStyle;

		$name			= '<a class="h6" href="' . $favorite['url'] . '" target="_blank"><span dir="auto">' . $favorite['title'] . '</span></a>';
		$thumbnail		= '<a href="' . $favorite['url'] . '"><i class="' . $iconStyle . ' fa-external-link-alt fa-8x"></i></a>';;
		$displayNote	= $favorite['note'];
		$detailedView	= '';
		$recordYear		= '';

		require KT_THEME_DIR . 'templates/person_card_template.php';

	}

	// Store a new favorite in the database
	public static function addFavorite($favorite) {
		// -- make sure a favorite is added
		if (empty($favorite['gid']) && empty($favorite['url'])) {
			return false;
		}

		//-- make sure this is not a duplicate entry
		$sql = "SELECT SQL_NO_CACHE 1 FROM `##favorites` WHERE";
		if (!empty($favorite['gid'])) {
			$sql	.= " xref=?";
			$vars	= array($favorite['gid']);
		} else {
			$sql	.= " url=?";
			$vars	 = array($favorite['url']);
		}
		$sql	.= " AND gedcom_id=?";
		$vars[]	 = $favorite['gedcom_id'];

		if (KT_DB::prepare($sql)->execute($vars)->fetchOne()) {
			return false;
		}

		//-- add the favorite to the database
		return (bool)
			KT_DB::prepare("INSERT INTO `##favorites` (user_id, gedcom_id, xref, favorite_type, url, title, note) VALUES (? ,? ,? ,? ,? ,? ,?)")
				->execute(array(null, $favorite['gedcom_id'], $favorite['gid'], $favorite['type'], $favorite['url'], $favorite['title'], $favorite['note']));
	}

	// Get favorites for a user or family tree
	public static function getFavorites($user_id) {
		self::updateSchema(); // make sure the favorites table has been created

		return
			KT_DB::prepare(
				"SELECT SQL_CACHE favorite_id AS id, user_id, gedcom_id, xref AS gid, favorite_type AS type, title, note, url".
				" FROM `##favorites` WHERE gedcom_id=? AND (user_id IS NULL OR user_id = ? )")
			->execute(array(KT_GED_ID, $user_id))
			->fetchAll(PDO::FETCH_ASSOC);
	}

	protected static function updateSchema() {
		// Create tables, if not already present
		try {
			KT_DB::updateSchema(KT_ROOT . KT_MODULES_DIR . 'block_favorites/db_schema/', 'LF_SCHEMA_VERSION', 4);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			die($ex);
		}
	}

}
