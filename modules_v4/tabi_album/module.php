<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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

class tabi_album_KT_Module extends KT_Module implements KT_Module_IndiTab, KT_Module_Config
{
	protected $mediaCount;

	// Extend KT_Module
	public function getTitle()
	{
		return /* I18N: Name of a module */ KT_I18N::translate('Album');
	}

	// Extend KT_Module
	public function getDescription()
	{
		return /* I18N: Description of the “Album” module */ KT_I18N::translate('A tab showing the media objects linked to an individual.');
	}

	// Implement KT_Module_IndiTab
	public function defaultAccessLevel()
	{
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder()
	{
		return 90;
	}

	// Extend KT_Module
	public function modAction($mod_action)
	{
		switch($mod_action) {
			case 'admin_config':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';

				break;

			case 'admin_reset':
				$this->album_reset();

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

	// Implement KT_Module_IndiTab
	public function hasTabContent()
	{
		return KT_USER_CAN_EDIT || $this->get_media_count() > 0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut()
	{
		return 0 == $this->get_media_count();
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax()
	{
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent()
	{
		return '';
	}

		// Implement KT_Module_IndiTab
	public function getTabContent()
	{
		global $SHOW_RELATIVES_EVENTS, $controller, $iconStyle;

		require_once KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/album_print_media.php';

		$media_found = false;

		$ALBUM_GROUPS = get_module_setting($this->getName(), 'ALBUM_GROUPS');
		if (!isset($ALBUM_GROUPS)) {
			$ALBUM_GROUPS = 4;
		}

		ob_start(); ?>	
		<div id="<?php echo $this->getName(); ?>_content" class="grid-x">
			<?php if (KT_USER_CAN_EDIT) { ?>
				<div class="cell tabHeader">
					<?php if (get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') >= KT_USER_ACCESS_LEVEL) { ?>
						<div class="grid-x">
							<?php if ($SHOW_RELATIVES_EVENTS) { ?>
								<div class="cell shrink">
									<a 
										href="addmedia.php?action=showmediaform&amp;linktoid=<?php echo $controller->record->getXref(); ?>" 
										target="_blank" 
										rel="noopener noreferrer"
									>
										<i class="<?php echo $iconStyle; ?> fa fa-camera-retro"></i>
										<?php echo KT_I18N::translate('Add a media object'); ?>
									</a>
								</div>
							<?php }
							if (file_exists(KT_Site::preference('INDEX_DIRECTORY') . 'histo.' . KT_LOCALE . '.php')) { ?>
								<div class="cell shrink">
									<a 
										href="inverselink.php?linktoid=<?php echo $controller->record->getXref(); ?>&amp;linkto=person" 
										target="_blank"
									>
										<i class="<?php echo $iconStyle; ?> fa fa-link"></i>
										<?php echo KT_I18N::translate('Link to an existing media object'); ?>
									</a>
								</div>
							<?php }

							if (KT_USER_GEDCOM_ADMIN && $this->get_media_count() > 1) { ?>
								<div class="cell auto">
									<a 
										href="#" onclick="reorder_media('<?php echo $controller->record->getXref(); ?>')" 
										target="_blank"
									>
										<i class="<?php echo $iconStyle; ?> fa fa-shuffle"></i>
										<?php echo KT_I18N::translate('Re-order media'); ?>
									</a>
								</div>
							<?php } ?>

						</div>
					<?php } ?>
				</div>
			<?php } ?>
			<div class="cell">
				<?php if (0 == $ALBUM_GROUPS) {
					album_print_media($controller->record->getXref(), 0, true);
				} else {
					for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
						ob_start();
							album_print_media($controller->record->getXref(), 0, true, $i);
							$print_row = ob_get_contents();
							$check = mb_strrpos($print_row, 'class="pic"');
						if(!$check) {
							ob_end_clean();
						} else {
							ob_end_flush();
						}
					}
				} ?>
			</div>
		</div>

		<?php return ob_get_clean();
	}

	// Reset all settings to default
	private function album_reset()
	{
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'ALBUM%'")->execute();
		AddToLog($this->getTitle() . ' reset to default values', 'config');
	}

	private function get_media_count()
	{
		global $controller;

		if (null === $this->mediaCount) {
			$this->mediaCount = 0;
			preg_match_all('/\d OBJE @(' . KT_REGEX_XREF . ')@/', $controller->record->getGedcomRecord(), $matches);
			foreach ($matches[1] as $match) {
				$obje = KT_Media::getInstance($match);
				if ($obje && $obje->canDisplayDetails()) {
					$this->mediaCount++;
				}
			}
			foreach ($controller->record->getSpouseFamilies() as $sfam) {
				preg_match_all('/\d OBJE @(' . KT_REGEX_XREF . ')@/', $sfam->getGedcomRecord(), $matches);
				foreach ($matches[1] as $match) {
					$obje = KT_Media::getInstance($match);
					if ($obje && $obje->canDisplayDetails()) {
						$this->mediaCount++;
					}
				}
			}
		}

		return $this->mediaCount;
	}

	private function find_no_type()
	{
		$medialist = KT_Query_Media::medialist('', 'include', 'title', '', 'blank');
		$ct = count($medialist);
		if ($medialist) {
			$html = '
				<p>' . KT_I18N::translate('%s media objects', $ct) . '</p>
				<table>
					<tr>
						<th>' . KT_I18N::translate('Media object') . '</th>
						<th>' . KT_I18N::translate('Media title') . '</th>
					</tr>';
					for ($i = 0; $i < $ct; $i++) {
						$mediaobject = $medialist[$i];
						$html .= '<tr>
							<td>' . $mediaobject->displayImage() . '</td>
							<td>
								<a href="addmedia.php?action=editmedia&pid=' . $mediaobject->getXref() . '" target="_blank">' . $mediaobject->getFullName() . '</a>
							</td>
						</tr>';
					}
				$html .= '</table>';
		} else {
			$html = '<p>' . KT_I18N::translate('No media objects found') . '</p>';
		}

		return $html;
	}

	private function getJS()
	{
		return '';
	}
}
