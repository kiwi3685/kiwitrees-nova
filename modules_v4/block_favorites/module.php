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

class block_favorites_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Favorites');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Favorites” module */ KT_I18N::translate('Display and manage a family tree’s favorite pages.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $show_full, $PEDIGREE_FULL_DETAILS, $controller, $iconStyle;

		self::updateSchema(); // make sure the favorites table has been created

		$action = KT_Filter::get('action');
		switch ($action) {
		case 'deletefav':
			$favorite_id = KT_Filter::get('favorite_id');
			if ($favorite_id) {
				self::deleteFavorite($favorite_id);
			}
			unset($_GET['action']);
			break;
		case 'addfav':
			$gid		= KT_Filter::get('gid');
			$favnote	= KT_Filter::get('favnote');
			$url		= KT_Filter::get('url', KT_REGEX_URL);
			$favtitle	= KT_Filter::get('favtitle');

			if ($gid) {
				$record = KT_GedcomRecord::getInstance($gid);
				if ($record && $record->canDisplayDetails()) {
					self::addFavorite(array(
						'user_id'	=> KT_USER_ID ? KT_USER_ID : null,
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
					'user_id'	=> KT_USER_ID ? KT_USER_ID : null,
					'gedcom_id'	=> KT_GED_ID,
					'gid'		=> null,
					'type'		=> 'URL',
					'url'		=> $url,
					'note'		=> $favnote,
					'title'		=> $favtitle ? $favtitle : $url,
				));
			}
			unset($_GET['action']);
			break;
		}

		$block = get_block_setting($block_id, 'block', false);
		if ($cfg) {
			foreach (array('block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		// Override GEDCOM configuration temporarily
		if (isset($show_full)) {
			$saveShowFull = $show_full;
		}
		$savePedigreeFullDetails	= $PEDIGREE_FULL_DETAILS;
		$show_full					= 1;
		$PEDIGREE_FULL_DETAILS		= 1;

		$favorites = $this->getFavorites(KT_GED_ID);
		if (!is_array($favorites)) {
			$favorites = array();
		}

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$config		= true;
		$title	= $this->getTitle();

		if (KT_USER_ID) {
			$controller
				->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
				->addInlineJavascript('autocomplete();');
		}

		$content = '';

		if (get_block_location($block_id) === 'side') {
			$style = 1;
		} else {
			$style = 2; // 1 means "regular box", 2 means "wide box", 3 means "vertical box", 4 means "card"
		}

		if ($favorites) {
			foreach ($favorites as $key => $favorite) {
				if (isset($favorite['id'])) {
					$key = $favorite['id'];
				}
				$removeFavourite = '<a href="index.php?action=deletefav&amp;favorite_id=' . $key . '"
					onclick="return confirm(\'' . KT_I18N::translate('Are you sure you want to remove this?') . '\');">
					<small>
						<i class="' . $iconStyle . ' fa-trash-alt"></i>' . KT_I18N::translate('Remove') . '
					</small>
				</a>';

				if ($favorite['type'] == 'URL') {
					$content .= '<div id="boxurl' . $key . '.0" class="grid-x">
						<div class="cell">';
							if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) $content .= $removeFavourite;
							$content .= '<a href="' . $favorite['url'] . '">
								<b>' . $favorite['title'] . '</b>
							</a>
							<br> ' .$favorite['note'] . '
						</div>
					</div>';
				} else {
					$record = KT_GedcomRecord::getInstance($favorite['gid']);
					if ($record && $record->canDisplayDetails()) {
						if ($record->getType() == 'INDI') {
							$content .= '<div id="box' . $favorite["gid"] . '.0" class="grid-x">
								<div class="cell">';
									if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) {
										$content .= $removeFavourite;
									}
									ob_start();
									print_pedigree_person($record, $style, 1, $key);
									$content .= ob_get_clean() . $favorite['note'] . '
								</div>
							</div>';
						} else {
							$content .= '<div id="box' . $favorite['gid'] . '.0" class="grid-x">
								<div class="cell">';
									if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) {
										$content .= $removeFavourite;
									}
									$content .= $record->format_list('span') . '
									<br>' . $favorite['note'] . '
								</div>
							</div>';
						}
					}
				}
			}
		}
		if (KT_USER_ID || KT_USER_GEDCOM_ADMIN) {
			$uniqueID = (int)(microtime(true) * 1000000); // This block can theoretically appear multiple times, so use a unique ID.

			$content .= '
				<button class="button" type="button" data-toggle="add_fav' . $uniqueID . '">
					<i class="' . $iconStyle . ' fa-plus"></i>' .
					KT_I18N::translate('Add a favorite') . '
				</button>
				<div class="dropdown-pane large" data-position="right" data-alignment="top" id="add_fav' . $uniqueID . '" data-dropdown data-auto-focus="true">
					<form name="addfavform" method="get" action="index.php">
						<input type="hidden" name="action" value="addfav">
						<input type="hidden" name="ged" value="' . KT_GEDCOM . '">
						<div class="add_fav_ref">
							<input
								type="radio"
								name="fav_category"
								value="record"
								checked="checked"
								onclick="jQuery(\'#gid' . $uniqueID . '\').removeAttr(\'disabled\'); jQuery(\'#url, #favtitle\').attr(\'disabled\',\'disabled\').val(\'\');"
							>
							<label for="gid' . $uniqueID . '">' . KT_I18N::translate('Select a Person, Family, or Source') . '</label>
							<input data-autocomplete-type="IFSRO" type="text" name="gid" id="gid' . $uniqueID . '" value="">
						</div>
						<div class="add_fav_url">
							<input
								type="radio"
								name="fav_category"
								value="url"
								onclick="jQuery(\'#url, #favtitle\').removeAttr(\'disabled\'); jQuery(\'#gid' . $uniqueID . '\').attr(\'disabled\',\'disabled\').val(\'\');"
							>
							<input
								type="text"
								name="url"
								id="url"
								size="20"
								value=""
								placeholder="' . KT_Gedcom_Tag::getLabel('URL') . '" disabled="disabled"
							>
							<input
								type="text"
								name="favtitle"
								id="favtitle"
								size="20"
								value=""
								placeholder="' . KT_I18N::translate('Title') . '"
								disabled="disabled"
							>
							<p>' .
								KT_I18N::translate('Enter an optional note about this favorite') . '
							</p>
							<textarea name="favnote" rows="6" cols="50"></textarea>
						</div>
						<button class="button" type="submit">
							<i class="' . $iconStyle . ' fa-save"></i>' .
							KT_I18N::translate('Save') . '
						</button>
					</form>
				</div>
			';
		}

		if ($template) {
			if (get_block_location($block_id) === 'side') {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}

		// Restore GEDCOM configuration
		unset($show_full);
		if (isset($saveShowFull)) {
			$show_full = $saveShowFull;
		}
		$PEDIGREE_FULL_DETAILS = $savePedigreeFullDetails;

	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'block',  KT_Filter::postBool('block'));
			exit;
		}

		$block = get_block_setting($block_id, 'block', false);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Add a scrollbar when block contents grow'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('block', $block); ?>
		</div>
		<hr>

	<?php }

	// Delete a favorite from the database
	public static function deleteFavorite($favorite_id) {
		return (bool)
			KT_DB::prepare("DELETE FROM `##favorites` WHERE favorite_id=?")
			->execute(array($favorite_id));
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
	public static function getFavorites($gedcom_id) {
		self::updateSchema(); // make sure the favorites table has been created

		return
			KT_DB::prepare(
				"SELECT SQL_CACHE favorite_id AS id, user_id, gedcom_id, xref AS gid, favorite_type AS type, title, note, url".
				" FROM `##favorites` WHERE gedcom_id=? AND user_id IS NULL")
			->execute(array($gedcom_id))
			->fetchAll(PDO::FETCH_ASSOC);
	}

	protected static function updateSchema() {
		// Create tables, if not already present
		try {
			KT_DB::updateSchema(KT_ROOT.KT_MODULES_DIR . 'block_favorites/db_schema/', 'GF_SCHEMA_VERSION', 4);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			die($ex);
		}
	}
}
