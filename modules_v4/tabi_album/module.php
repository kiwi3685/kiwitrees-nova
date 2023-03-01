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

class tabi_album_KT_Module extends KT_Module implements KT_Module_IndiTab, KT_Module_Config {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Album');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Album” module */ KT_I18N::translate('A tab showing the media objects linked to an individual.');
	}

	// Implement KT_Module_IndiTab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 90;
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_config':
				require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/' . $mod_action . '.php';
				break;
			case 'admin_reset':
				$this->album_reset();
				$this->config();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->get_media_count()>0;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return $this->get_media_count()==0;
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $controller;

		$ALBUM_GROUPS = get_module_setting($this->getName(), 'ALBUM_GROUPS');
		if (!isset($ALBUM_GROUPS)) {
			$ALBUM_GROUPS = 4;
		}

		require_once KT_ROOT . KT_MODULES_DIR . $this->getName() . '/administration/album_print_media.php';
		$html='<div id="'.$this->getName().'_content">';
			//Show Album header Links
			if (KT_USER_CAN_EDIT) {
				$html.='<div class="descriptionbox rela">';
				// Add a media object
				if (get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') >= KT_USER_ACCESS_LEVEL) {
					$html.='<span><a href="addmedia.php?action=showmediaform&amp;linktoid=' . $controller->record->getXref() . '" target="_blank" rel="noopener noreferrer"><i style="margin: 0 3px 0 10px;" class="icon-image_add">&nbsp;</i>' .KT_I18N::translate('Add a media object'). '</a></span>';
					// Link to an existing item
					$html.='<span><a href="inverselink.php?linktoid=' . $controller->record->getXref() . '&amp;linkto=person" target="_blank"><i style="margin: 0 3px 0 10px;" class="icon-image_link">&nbsp;</i>' .KT_I18N::translate('Link to an existing media object'). '</a></span>';
				}
				if (KT_USER_GEDCOM_ADMIN && $this->get_media_count()>1) {
					// Popup Reorder Media
					$html.='<span><a href="#" onclick="reorder_media(\''.$controller->record->getXref().'\')"><i style="margin: 0 3px 0 10px;" class="icon-image_sort">&nbsp;</i>' .KT_I18N::translate('Re-order media'). '</a></span>';
				}
				$html.='</div>';
			}
		$media_found = false;

		$html .= '<div style="width:100%; vertical-align:top;">';
		ob_start();
		if ($ALBUM_GROUPS == 0) {
			album_print_media($controller->record->getXref(), 0, true);
		} else {
			for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
				ob_start();
				album_print_media($controller->record->getXref(), 0, true, $i);
				$print_row = ob_get_contents();
				$check = strrpos($print_row, "class=\"pic\"");
				if(!$check) {
					ob_end_clean();
				} else {
					ob_end_flush();
				}
			}
		}
		return
			$html.
			ob_get_clean().
			'</div>';
	}


	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	// Reset all settings to default
	private function album_reset() {
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'ALBUM%'")->execute();
		AddToLog($this->getTitle().' reset to default values', 'config');
	}

	protected $mediaCount = null;

	private function get_media_count() {
		global $controller;

		if ($this->mediaCount===null) {
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

	private function find_no_type() {
		$medialist = KT_Query_Media::medialist('', 'include', 'title', '', 'blank');
		$ct = count($medialist);
		if ($medialist) {
			$html = '
				<p>' .KT_I18N::translate('%s media objects', $ct). '</p>
				<table>
					<tr>
						<th>' . KT_I18N::translate('Media object') . '</th>
						<th>' . KT_I18N::translate('Media title') . '</th>
					</tr>';
					for ($i=0; $i<$ct; ++$i) {
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
			$html = '<p>' .KT_I18N::translate('No media objects found'). '</p>';
		}
		return $html;
	}

	private function getJS() {
		return '';
	}


}
