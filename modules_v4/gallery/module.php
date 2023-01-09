<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net.
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

class gallery_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config
{
	// Extend class KT_Module
	public function getTitle()
	{
		return KT_I18N::translate('Gallery');
	}

	// Extend class KT_Module
	public function getDescription()
	{
		return KT_I18N::translate('Display image galleries.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder()
	{
		return 100;
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

			case 'admin_delete':
				$this->delete();

				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/admin_config.php';

				break;

			case 'admin_movedown':
				$this->movedown();

				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/admin_config.php';

				break;

			case 'admin_moveup':
				$this->moveup();

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
	public function getConfigLink()
	{
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function loadAjax()
	{
		return false;
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null)
	{
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
		$default_title = KT_I18N::translate('Gallery');

		return KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
	}

	public function getMenuIcon()
	{
		$default_icon = 'fa-images';

		return KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_ICON', $default_icon));
	}

	public function getSummaryDescription()
	{
		$default_description = KT_I18N::translate('These are galleries');

		return get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
	}

	// Implement KT_Module_Menu
	public function getMenu()
	{
		global $controller, $SEARCH_SPIDER;

		$block_id = safe_GET('block_id');
		$default_block = KT_DB::prepare(
			'SELECT block_id FROM `##block` WHERE block_order=? AND module_name=?'
		)->execute([0, $this->getName()])->fetchOne();

		if ($SEARCH_SPIDER) {
			return null;
		}

		// -- main GALLERIES menu item
		$menu = new KT_Menu($this->getMenuTitle(), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;gallery_id=' . $default_block, 'menu-my_gallery', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', 'fa-images');
		foreach ($this->getMenuGalleryList() as $item) {
			$languages = get_block_setting($item->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item->gallery_access >= KT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;gallery_id=' . $item->block_id;
				$submenu = new KT_Menu(KT_I18N::translate($item->gallery_title), $path, 'menu-my_gallery-' . $item->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit gallerys'), $this->getConfigLink(), 'menu-my_gallery-edit');
			$menu->addSubmenu($submenu);
		}

		return $menu;
	}

	// Print the Notes for each media item
	public static function FormatGalleryNotes($haystack)
	{
		$needle = '1 NOTE';
		$before = substr($haystack, 0, strpos($haystack, $needle));
		$after = substr(strstr($haystack, $needle), strlen($needle));
		$final = $before . $needle . $after;
		$notes = print_fact_notes($final, 1, true, true);
		if ('' != $notes && '<br>' != $notes) {
			return htmlspecialchars((string) $notes);
		}

		return false;
	}

	// Start to show the gallery display with the parts common to all galleries
	private function show()
	{
		global $MEDIA_DIRECTORY, $controller;

		$item_id = KT_Filter::get('gallery_id');
		$version = 'ver: 1.6.1';
		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader()
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/galleria.min.js')
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/plugins/flickr/galleria.flickr.min.js')
			->addInlineJavaScript($this->getJavaScript($item_id))
		;

		echo pageStart('gallery', $controller->getPageTitle()); ?>

			<div class="grid-x">
				<div class="cell">
					<?php echo $this->getSummaryDescription(); ?>
				</div>

				<ul class="cell tabs" data-tabs id="gallery-tabs">
					<?php $item_list = $this->getGalleryList();
					foreach ($item_list as $item) {
						$languages = get_block_setting($item->block_id, 'languages');
						if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item->gallery_access >= KT_USER_ACCESS_LEVEL) {
							 $class = ($item_id == $item->block_id ? 'is-active' : ''); ?>
							<li class="tabs-title <?php echo $class; ?>">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;gallery_id=<?php echo $item->block_id; ?>">
									<span title="<?php echo KT_I18N::translate($item->gallery_title); ?>"><?php echo KT_I18N::translate($item->gallery_title); ?>
									</span>
								</a>
							</li>
						<?php }
					} ?>
				</ul>

				<div class="cell tabs-content" data-tabs-content="gallery-tabs">
					<div class="grid-x">
						<?php foreach ($item_list as $item) {
							if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item_id == $item->block_id && $item->gallery_access >= KT_USER_ACCESS_LEVEL) {
								$item_gallery = '<h4 class="cell">' . KT_I18N::translate($item->gallery_description) . '</h4>' .
									$this->mediaDisplay($item->gallery_folder_w, $item_id, $version);
							}
						}
						if (!isset($item_gallery)) {
							echo '<h4 class="cell">' . KT_I18N::translate('Image collections related to our family') . '</h4>' .
								$this->mediaDisplay('//', $item_id, $version);
						} else {
							echo $item_gallery;
						} ?>
					</div>
				</div>

			</div>
		</div>
	<?php }

	private function delete()
	{
		$block_id = safe_GET('block_id');

		KT_DB::prepare(
			'DELETE FROM `##block_setting` WHERE block_id=?'
		)->execute([$block_id]);

		KT_DB::prepare(
			'DELETE FROM `##block` WHERE block_id=?'
		)->execute([$block_id]);
	}

	private function moveup()
	{
		$block_id = safe_GET('block_id');

		$block_order = KT_DB::prepare(
			'SELECT block_order FROM `##block` WHERE block_id=?'
		)->execute([$block_id])->fetchOne();

		$swap_block = KT_DB::prepare(
			'SELECT block_order, block_id
			FROM `##block`
			WHERE block_order=(
			 SELECT MAX(block_order) FROM `##block` WHERE block_order < ? AND module_name=?
			) AND module_name=?
			LIMIT 1'
		)->execute([$block_order, $this->getName(), $this->getName()])->fetchOneRow();
		if ($swap_block) {
			KT_DB::prepare(
				'UPDATE `##block` SET block_order=? WHERE block_id=?'
			)->execute([$swap_block->block_order, $block_id]);
			KT_DB::prepare(
				'UPDATE `##block` SET block_order=? WHERE block_id=?'
			)->execute([$block_order, $swap_block->block_id]);
		}
	}

	private function movedown()
	{
		$block_id = safe_GET('block_id');

		$block_order = KT_DB::prepare(
			'SELECT block_order FROM `##block` WHERE block_id=?'
		)->execute([$block_id])->fetchOne();

		$swap_block = KT_DB::prepare(
			'SELECT block_order, block_id
			FROM `##block`
			WHERE block_order=(
			 SELECT MIN(block_order) FROM `##block` WHERE block_order>? AND module_name=?
			) AND module_name=?
			LIMIT 1'
		)->execute([$block_order, $this->getName(), $this->getName()])->fetchOneRow();
		if ($swap_block) {
			KT_DB::prepare(
				'UPDATE `##block` SET block_order=? WHERE block_id=?'
			)->execute([$swap_block->block_order, $block_id]);
			KT_DB::prepare(
				'UPDATE `##block` SET block_order=? WHERE block_id=?'
			)->execute([$block_order, $swap_block->block_id]);
		}
	}

	private function getJavaScript($item_id)
	{
		$theme = 'classic';
		$plugin = get_block_setting($item_id, 'plugin');

		$js = 'Galleria.loadTheme("' . KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/themes/' . $theme . '/galleria.' . $theme . '.min.js");';

		switch ($plugin) {
			case 'flickr':
				$flickr_set = get_block_setting($item_id, 'gallery_folder_f');
				$js .= '
					Galleria.run("#galleria", {
						flickr: "set:' . $flickr_set . '",
						flickrOptions: {
							sort: "date-posted-asc",
							description: true,
							imageSize: "original"
						},
						_showCaption: false,
						imageCrop: false
					});
				';

				break;

			default:
				$js .= '
					Galleria.ready(function(options) {
						this.bind("image", function(e) {
							data = e.galleriaData;
							jQuery("#links_bar").html(data.layer);
						});
					});
					Galleria.run("#galleria", {
						imageCrop: false,
						_showCaption: false,
						_locale: {
							show_captions:		"' . KT_I18N::translate('Show descriptions') . '",
							hide_captions:		"' . KT_I18N::translate('Hide descriptions') . '",
							play:				"' . KT_I18N::translate('Play slideshow') . '",
							pause:				"' . KT_I18N::translate('Pause slideshow') . '",
							enter_fullscreen:	"' . KT_I18N::translate('Enter fullscreen') . '",
							exit_fullscreen:	"' . KT_I18N::translate('Exit fullscreen') . '",
							next:				"' . KT_I18N::translate('Next image') . '",
							prev:				"' . KT_I18N::translate('Previous image') . '",
							showing_image:		"" // counter not compatible with I18N of kiwitrees
						}
					});
				';

				break;
		}

		return $js;
	}

	// Return the list of gallerys
	private function getGalleryList()
	{
		return KT_DB::prepare(
			"SELECT block_id,
				bs1.setting_value AS gallery_title,
				bs2.setting_value AS gallery_access,
				bs3.setting_value AS gallery_description,
				bs4.setting_value AS gallery_folder_w,
				bs5.setting_value AS gallery_folder_f
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			JOIN `##block_setting` bs3 USING (block_id)
			JOIN `##block_setting` bs4 USING (block_id)
			JOIN `##block_setting` bs5 USING (block_id)
			WHERE module_name=?
			AND bs1.setting_name='gallery_title'
			AND bs2.setting_name='gallery_access'
			AND bs3.setting_name='gallery_description'
			AND bs4.setting_name='gallery_folder_w'
			AND bs5.setting_name='gallery_folder_f'
			AND (gedcom_id IS NULL OR gedcom_id=?)
			ORDER BY block_order"
		)->execute([$this->getName(), KT_GED_ID])->fetchAll();
	}

	// Return the list of gallerys for menu
	private function getMenuGalleryList()
	{
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_access
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			WHERE module_name=?
			AND bs1.setting_name='gallery_title'
			AND bs2.setting_name='gallery_access'
			AND (gedcom_id IS NULL OR gedcom_id=?)
			ORDER BY block_order"
		)->execute([$this->getName(), KT_GED_ID])->fetchAll();
	}

	// Print the gallery display
	private function mediaDisplay($sub_folder, $item_id, $version) {
		global $MEDIA_DIRECTORY;
		$plugin			= get_block_setting($item_id, 'plugin');
		$images			= '';
		$media_links 	= '';

		// Get the related media items
		$sub_folder	= str_replace($MEDIA_DIRECTORY, "",$sub_folder);
		$sql		= "SELECT * FROM ##media WHERE m_filename LIKE '" . $sub_folder . "%' ORDER BY m_filename";
		$rows		= KT_DB::prepare($sql)->execute()->fetchAll(PDO::FETCH_ASSOC);

		if ($plugin == 'kiwitrees') {
			foreach ($rows as $rowm) {
				// Get info on how to handle this media file
				$media	= KT_Media::getInstance($rowm['m_id']);
				if ($media && $media->canDisplayDetails()) {
					$links = array_merge(
						$media->fetchLinkedIndividuals(),
						$media->fetchLinkedFamilies(),
						$media->fetchLinkedSources()
					);
					$rawTitle = $rowm['m_titl'];
					if (empty($rawTitle)) $rawTitle = get_gedcom_value('TITL', 2, $rowm['m_gedcom']);
					if (empty($rawTitle)) $rawTitle = basename($rowm['m_filename']);
					$mediaTitle		= htmlspecialchars(strip_tags($rawTitle));
					$rawUrl			= $media->getHtmlUrlDirect();
					$thumbUrl		= $media->getHtmlUrlDirect('thumb');
					$media_notes	= $this->FormatGalleryNotes($rowm['m_gedcom']);
					$mime_type		= $media->mimeType();
					$gallery_links	= '';

					if (KT_USER_CAN_EDIT) {
						$gallery_links .= '<div class="edit_links">' . KT_Controller_Media::getMediaListMenu($media, true) . '</div><hr>';
					}

					if ($links) {
						$gallery_links .= '<h5>' . KT_I18N::translate('Linked to:') .'</h5>';
						$gallery_links .= '<div id="image_links">';
							foreach ($links as $record) {
									$gallery_links .= '<a href="' . $record->getHtmlUrl() . '">' . $record->getFullname().'</a><br>';
							}
						$gallery_links .= '</div>';
					}

					$media_links = htmlspecialchars((string) $gallery_links);
					if ($mime_type == 'application/pdf'){
						$images .= '<a href="' . $rawUrl . '"><img class="iframe" src="' . $thumbUrl . '" data-title="' . $mediaTitle.'" data-layer="' . $media_links.'" data-description="' . $media_notes . '"></a>';
					} else {
						$images .= '<a href="' . $rawUrl . '"><img src="'.$thumbUrl.'" data-title="' .$mediaTitle . '" data-layer="' . $media_links . '" data-description="' . $media_notes . '"></a>';
					}
				}
			}
			if (KT_USER_CAN_ACCESS || !is_null($media_links)) {
				$html = '
					<div id="links_bar"></div>
					<div id="galleria">
				';
			} else {
				$html =
					'<div id="galleria">';
			}
		} else {
			$html = '<div id="galleria">';
			$images .= '&nbsp;';
		}
		if ($images) {
			$html .= $images . '
				</div>
				<a class="cell" id="copy" href="https://galleriajs.github.io/" target="_blank" rel="noopener noreferrer">
			 		' . /* I18N: Copyright statement in gallery module */ KT_I18N::translate('Display by Galleria (%1s)', $version) . '
			 	</a>
			';
		} else {
			$html .= KT_I18N::translate('Gallery is empty. Please choose other gallery.').
				'</div>
				</div>';
		}
		return $html;
	}

	// Get galleria themes list
	private function galleria_theme_names()
	{
		$themes = [];
		$d = dir(KT_MODULES_DIR . $this->getName() . '/galleria/themes/');

		while (false !== ($folder = $d->read())) {
			if ('.' != $folder[0] && '_' != $folder[0] && is_dir(KT_MODULES_DIR . $this->getName() . '/galleria/themes/' . $folder)) {
				$themes[] = $folder;
			}
		}

		$d->close();

		return $themes;
	}
}
