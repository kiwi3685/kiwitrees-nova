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

		$items      = $this->getItemList();
		$minBlockId = $items ? min(array_column($items, 'block_id')) : '';

		// -- main GALLERIES menu item
		$menu = new KT_Menu(
			'<span>' . $this->getMenuTitle() . '</span>', 
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;gallery_id=' . $minBlockId, 'menu-my_gallery', 'down'
		);
		$menu->addClass('', '', $this->getMenuIcon());

		foreach ($items as $item) {
			$submenu = new KT_Menu(
				KT_I18N::translate($item->gallery_title), 
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;gallery_id=' . $item->block_id, 
				'menu-my_gallery-' . $item->block_id
			);
			$menu->addSubmenu($submenu);
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

		$item_id    = KT_Filter::get('gallery_id');
		$items      = $this->getItemList();

		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader()
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/galleria.min.js')
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/plugins/flickr/galleria.flickr.min.js')
			->addInlineJavaScript($this->getJavaScript($item_id))
		;

		echo pageStart('gallery', $controller->getPageTitle());
			$item_gallery = ''; ?>

			<div class="grid-x">
				<div class="cell">
					<?php echo $this->getSummaryDescription(); ?>
				</div>

				<ul class="cell tabs">
					<?php foreach ($items as $item) {
						($item_id == $item->block_id) ? $class = ' is-active' : $class = ''; ?>
						<li class="tabs-title<?php echo $class; ?>">
							<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;gallery_id=<?php echo $item->block_id; ?>">
								<span title="<?php echo KT_I18N::translate($item->gallery_title); ?>">
									<?php echo KT_I18N::translate($item->gallery_title); ?>
								</span>
							</a>
						</li>
					<?php } ?>
				</ul>

				<div class="cell tabs-content">
					<?php  foreach ($items as $item) {
						if ($items && $item_id == $item->block_id) {
							$item_gallery = '
								<div class="cell gallery_content">' . 
									KT_I18N::translate($item->gallery_content) . '
								</div>' .
								$this->mediaDisplay($item->gallery_folder, $item_id);
						}
					}
					if (is_null($item_gallery)) {
						$this->mediaDisplay('//', $item_id, $version);
					} else {
						echo $item_gallery;
					} ?>

				</div>

			</div>

		<?php echo pageClose();

	}

	private function getJavaScript($item_id)
	{

		$theme = str_replace('/', '', str_replace(KT_THEMES_DIR, '', KT_THEME_DIR));
		$js    = 'Galleria.loadTheme("' . KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/themes/' . $theme . '/galleria.' . $theme . '.min.js");';

		switch (get_block_setting($item_id, 'gallery_plugin')) {
			case 'flickr':
				$js .= '
					Galleria.run("#galleria", {
						flickr: "set:' . get_block_setting($item_id, 'gallery_folder') . '",
						flickrOptions: {
							sort: "date-posted-asc",
							description: true,
							imageSize: "original"
						},
						_showCaption: false,
					});
				';

				break;

			case 'kiwitrees':
			default:
				$js .= '
					Galleria.ready(function(options) {
						this.bind("image", function(e) {
							data = e.galleriaData;
							jQuery("#links_bar").html(data.layer);
						});
					});
					Galleria.run("#galleria", {
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

	// Print the gallery display
	private function mediaDisplay($sub_folder, $item_id) {
		global $MEDIA_DIRECTORY;
		$version     = 'ver: 1.6.1'; // CURRENT GALLERIA VEERSION
		$images      = '';
		$media_links = '';

		switch (get_block_setting($item_id, 'gallery_plugin')) {
			case 'kiwitrees':
				// Get the related media items
				$sub_folder	= str_replace($MEDIA_DIRECTORY, "", $sub_folder);
				$sql		= " SELECT * 
								FROM ##media 
								WHERE `m_filename` LIKE '" . $sub_folder . "%'
								ORDER BY `m_filename`
							  ";
				$rows 		= KT_DB::prepare($sql)->execute()->fetchAll(PDO::FETCH_ASSOC);

				foreach ($rows as $rowm) {
					// Get info on how to handle this media file
					$media	= KT_Media::getInstance($rowm['m_id']);
					if ($media && $media->canDisplayDetails()) {

						$rawTitle		= $rowm['m_titl'];
						if (empty($rawTitle)) $rawTitle = get_gedcom_value('TITL', 2, $rowm['m_gedcom']);
						if (empty($rawTitle)) $rawTitle = basename($rowm['m_filename']);
						$mediaTitle		= htmlspecialchars(strip_tags($rawTitle));

						$rawUrl			= $media->getHtmlUrlDirect();
						$thumbUrl		= $media->getHtmlUrlDirect('thumb');
						$media_notes	= $this->FormatGalleryNotes($rowm['m_gedcom']);
						$mime_type		= $media->mimeType();

						$links = array_merge(
							$media->fetchLinkedIndividuals(),
							$media->fetchLinkedFamilies(),
							$media->fetchLinkedSources()
						);

						$gallery_links	= '';

						if (KT_USER_CAN_EDIT) {
							$gallery_links .= '
								<div class="edit_links">' . 
									KT_Controller_Media::getMediaListMenu($media, true) . '
								</div>
								<hr>';
						}

						if ($links) {
							$gallery_links .= '
								<div id="image_links">
									<h5>' . KT_I18N::translate('Linked to:') .'</h5>';
									foreach ($links as $record) {
										$gallery_links .= '
											<a href="' . $record->getHtmlUrl() . '">' . 
												$record->getFullname() .'
											</a>
											<br>
										';
									}
							$gallery_links .= '</div>';
						}

						$gallery_links ? $media_links = htmlspecialchars((string) $gallery_links) : $media_links = '';

						if ($mime_type == 'application/pdf'){
							$images .= '
								<a href="' . $rawUrl . '">
									<img 
										class="iframe" 
										src="' . $thumbUrl . '" 
										data-title="' . $mediaTitle.'" 
										data-layer="' . $media_links.'" 
										data-description="' . $media_notes . '"
									>
								</a>
							';
						} else {							
							$images .= '
								<a href="' . $rawUrl . '">
									<img 
										src="' . $thumbUrl . '" 
										data-title="' .$mediaTitle . '" 
										data-layer="' . $media_links . '" 
										data-description="' . $media_notes . '"
									>
								</a>
							';
						}
					}
				}
				if (KT_USER_CAN_ACCESS || $media_links !== '') {
					$html = '
						<div id="links_bar"></div>
						<div id="galleria" class="kiwitreesLinked">
					';
				} else {
					$html = '
						<div id="galleria" class="kiwitrees">
					';
				}

				break;
			case 'uploads':
				$images    = '';
				$subfolder = get_block_setting($item_id, 'gallery_folder');
				$html      = '<div id="galleria" class="uploads">';
				$dir_name  = KT_STATIC_URL . 'uploads/' . $sub_folder;			
				$files     = glob($dir_name . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE );
				
				foreach ($files as $file) {
					$images .= '
						<a href="' . $file . '">
							<img 
								src="'. $file .'" 
							>
						</a>
					';
				}

				break;

			case 'flickr':
			default:
				$html   = '<div id="galleria" class="other">';
				$images .= '&nbsp;';

				break;
		}

		if ($images) {
			$html .= $images . '
				<a class="cell" id="copy" href="https://galleriajs.github.io/" target="_blank" rel="noopener noreferrer">
					' . /* I18N: Copyright statement in gallery module */ KT_I18N::translate('Display by Galleria (%1s)', $version) . '
				</a>
				</div>
			';
		} else {
			$html .= '
				</div>
				<div class="callout warning">' . 
					KT_I18N::translate('This gallery is either empty or cannot be displayed here. Please choose another gallery.') . '
				</div>
			';
		}

		return $html;

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

	private function checkUploadsDir()
	{
		if (!is_dir(KT_ROOT . 'uploads/')) {
			@mkdir(KT_ROOT . 'uploads/', KT_PERM_EXE, true);
 		}
		
	}

}
