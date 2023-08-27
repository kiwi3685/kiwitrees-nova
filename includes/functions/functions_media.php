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

/**
 * Create media info display
 *
 * @param object media
 * @param $options true-false: Add links to view/edit/delete the object
 * @param $links true-false: Add links to other linked records
 *
 * @return string
 */

function media_object_info(KT_Media $media, $showname = true, $shownote = true, $options = true, $optionsArray = [1,2,3,4], $links = true) {
	$xref   = $media->getXref();
	$gedcom = KT_Tree::getNameFromId($media->getGedId());
	$name   = $media->getFullName();
	$conf   = KT_Filter::escapeJS(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($name)));
	$html   = '';

	if ($showname) {
		$html .= '
			<div class="strong">' .
				$name . '
			</div>';
	}

	if ($shownote && !is_null($media->getNote())) {
		$html .= '
			<div>
				<i>' . htmlspecialchars((string) $media->getNote()) . '</i>
			</div>';
		($options || $links) ? $html .= '<br>' : '';
	}

	if ($options && !is_null($optionsArray)) {
		$html .= '<div class="editLinks">';
			foreach($optionsArray as $option) {
				switch ($option) {
					case 1:
						$html .= '
							<a href="' . $media->getHtmlUrl() . '">' .
								KT_I18N::translate('View') . '
							</a>';
							count($optionsArray) <= 1 ? '' : $html .= ' - ';
						break;
					case 2:
						$html .= '
							<a href="addmedia.php?action=editmedia&amp;pid=' . $xref . '&ged=' . $gedcom . '" target="_blank" >' .
								KT_I18N::Translate('Edit') . '
							</a>';
							count($optionsArray) <= 2 ? '' : $html .= ' - ';
						break;
					case 3:
						$html .= '
							<a
								onclick="if (confirm(\'' . $conf . '\')) jQuery.post(\'action.php\',{action:\'delete-media\',xref:\'' . $xref . '\',ged:\'' . $gedcom . '\'},function(){location.reload();})"
								href="#"
							>' .
								KT_I18N::Translate('Delete') . '
							</a>';
							count($optionsArray) <= 3 ? '' : $html .= ' - ';
						break;
					case 4:
						$html .= '
							<a href="inverselink.php?mediaid=' . $xref . '&amp;linkto=manage" target="_blank">' .
								KT_I18N::Translate('Manage links') . '
							</a>';
							count($optionsArray) <= 4 ? '' : $html .= ' - ';
						break;
				}
			}
		$html .= '</div>';
	}

	if ($links) {
		$linked = array();
		foreach ($media->fetchLinkedIndividuals() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() .' <i>'.$link->getLifeSpan().'</i>'. '</a>';
		}
		foreach ($media->fetchLinkedFamilies() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
		}
		foreach ($media->fetchLinkedNotes() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
		}
		foreach ($media->fetchLinkedSources() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
		}
		foreach ($media->fetchLinkedRepositories() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
		}
		foreach ($media->fetchLinkedMedia() as $link) {
			$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
		}

		if ($linked) {
			$html .= '<ul>';
			foreach ($linked as $link) {
				$html .= '<li>' . $link . '</li>';
			}
			$html .= '</ul>';
		} else {
			$html .= '<div class="error">' . KT_I18N::translate('This media object is not linked to any other record.') . '</div>';
		}
	}

	return $html;
}
