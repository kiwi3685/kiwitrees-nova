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

class block_media_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */KT_I18N::translate('Slide show');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Slide show” module */ KT_I18N::translate('Random images from the current family tree.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $iconStyle;

		$filter			= get_block_setting($block_id, 'filter',   'all');
		$controls		= get_block_setting($block_id, 'controls', true);
		$start			= get_block_setting($block_id, 'start',    false) || KT_Filter::getBool('start');
		$config			= true;

		// We can apply the filters using SQL
		// Do not use "ORDER BY RAND()" - it is very slow on large tables.  Use PHP::array_rand() instead.
		$all_media = KT_DB::prepare("
			SELECT m_id FROM `##media`
			 WHERE m_file = ?
			 AND m_ext  IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')
			 AND m_type IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')
		")->execute(array(
			KT_GED_ID,
			get_block_setting($block_id, 'filter_avi',         false) ? 'avi'         : NULL,
			get_block_setting($block_id, 'filter_bmp',         true ) ? 'bmp'         : NULL,
			get_block_setting($block_id, 'filter_gif',         true ) ? 'gif'         : NULL,
			get_block_setting($block_id, 'filter_jpeg',        true ) ? 'jpg'         : NULL,
			get_block_setting($block_id, 'filter_jpeg',        true ) ? 'jpeg'        : NULL,
			get_block_setting($block_id, 'filter_mp3',         false) ? 'mp3'         : NULL,
			get_block_setting($block_id, 'filter_ole',         true ) ? 'ole'         : NULL,
			get_block_setting($block_id, 'filter_pcx',         true ) ? 'pcx'         : NULL,
			get_block_setting($block_id, 'filter_pdf',         false) ? 'pdf'         : NULL,
			get_block_setting($block_id, 'filter_png',         true ) ? 'png'         : NULL,
			get_block_setting($block_id, 'filter_tiff',        true ) ? 'tiff'        : NULL,
			get_block_setting($block_id, 'filter_wav',         false) ? 'wav'         : NULL,
			get_block_setting($block_id, 'filter_audio',       false) ? 'audio'       : NULL,
			get_block_setting($block_id, 'filter_book',        false) ? 'book'        : NULL,
			get_block_setting($block_id, 'filter_card',        false) ? 'card'        : NULL,
			get_block_setting($block_id, 'filter_certificate', false) ? 'certificate' : NULL,
			get_block_setting($block_id, 'filter_coat',        false) ? 'coat'        : NULL,
			get_block_setting($block_id, 'filter_document',    false) ? 'document'    : NULL,
			get_block_setting($block_id, 'filter_electronic',  false) ? 'electronic'  : NULL,
			get_block_setting($block_id, 'filter_fiche',       false) ? 'fiche'       : NULL,
			get_block_setting($block_id, 'filter_film',        false) ? 'film'        : NULL,
			get_block_setting($block_id, 'filter_magazine',    false) ? 'magazine'    : NULL,
			get_block_setting($block_id, 'filter_manuscript',  false) ? 'manuscript'  : NULL,
			get_block_setting($block_id, 'filter_map',         false) ? 'map'         : NULL,
			get_block_setting($block_id, 'filter_newspaper',   false) ? 'newspaper'   : NULL,
			get_block_setting($block_id, 'filter_other',       false) ? 'other'       : NULL,
			get_block_setting($block_id, 'filter_painting',    false) ? 'painting'    : NULL,
			get_block_setting($block_id, 'filter_photo',       true ) ? 'photo'       : NULL,
			get_block_setting($block_id, 'filter_tombstone',   true ) ? 'tombstone'   : NULL,
			get_block_setting($block_id, 'filter_video',       false) ? 'video'       : NULL,
		))->fetchOneColumn();

		// Keep looking through the media until a suitable one is found.
		$block_media = null;
		while ($all_media) {
			$n		= array_rand($all_media);
			$media	= KT_Media::getInstance($all_media[$n]);
			if ($media->canDisplayDetails() && !$media->isExternal()) {
				// Check if it is linked to a suitable individual
				foreach ($media->fetchLinkedIndividuals() as $indi) {
					if (
						$filter == 'all' ||
						$filter == 'indi'  && strpos($indi->getGedcomRecord(), "\n1 OBJE @" . $media->getXref() . '@') !==false ||
						$filter == 'event' && strpos($indi->getGedcomRecord(), "\n2 OBJE @" . $media->getXref() . '@') !==false
					) {
						// Found one :-)
						$block_media = $media;
						break 2;
					}
				}
			}
			unset($all_media[$n]);
		};

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();

		if ($start) {
			$title = KT_I18N::translate('Slide Show');
		} else {
			$title = KT_I18N::translate('Random image');
		}

		if ($block_media) {
			$content = '<div id="block_media_container' . $block_id . '" class="grid-x">
				<div class="cell text-center">';
					if ($controls) {
						if ($start) {
							$icon_class = 'fa-pause';
						} else {
							$icon_class = 'fa-play';
						}
						$content .= '<script>
							var play = false;
							function togglePlay() {
								if (play) {
									play = false;
									jQuery("#play_stop").removeClass("fa-pause").addClass("fa-play");
								} else {
									play = true;
									playSlideShow();
									jQuery("#play_stop").removeClass("fa-play").addClass("fa-pause");
								}
							};
							function playSlideShow() {
								if (play) {
									window.setTimeout("reload_image()", 5000);
								}
							};
							function reload_image() {
								if (play) {
									jQuery("#' . $id .'").load("index.php?action=ajax&block_id=' . $block_id . '&start=1");
								}
							};
						</script>

						<div id="cell block_media_controls' . $block_id . '">
							<a href="#" onclick="togglePlay(); return false;" id="play_stop" class="' . $iconStyle  . ' ' . $icon_class . '" title="' . KT_I18N::translate('Play') . '/' . KT_I18N::translate('Stop') . '"></a>
							<a href="#" onclick="jQuery(\'#'. $id . '\').load(\'index.php?action=ajax&amp;block_id=' . $block_id . '\');return false;" title="' . KT_I18N::translate('Next image') . '" class="' . $iconStyle  . ' fa-angle-double-right"></a>
						</div>';

						if ($start) {
							$content .= '<script>togglePlay();</script>';
						}
					}
					$content .= '<div class="block_media_content">
						<div class="callout secondary small">
							<a href="' . $block_media->getHtmlUrl() . '" title="' . strip_tags($block_media->getFullName()) . '">' . $block_media->getFullName() . '</a>
						</div>' .
						$block_media->displayImage() . '
					</div>
				</div>
			</div>';
		} else {
			$content = '<div class="callout alert">' .
				KT_I18N::translate('This family tree has no images to display.') . '
			</div>';
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
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'filter',					KT_Filter::post('filter', 'indi|event|all', 'all'));
			set_block_setting($block_id, 'controls',				KT_Filter::postBool('controls'));
			set_block_setting($block_id, 'start',					KT_Filter::postBool('start'));
			set_block_setting($block_id, 'filter_avi',				KT_Filter::postBool('filter_avi'));
			set_block_setting($block_id, 'filter_bmp',				KT_Filter::postBool('filter_bmp'));
			set_block_setting($block_id, 'filter_gif',				KT_Filter::postBool('filter_gif'));
			set_block_setting($block_id, 'filter_jpeg',				KT_Filter::postBool('filter_jpeg'));
			set_block_setting($block_id, 'filter_mp3',				KT_Filter::postBool('filter_mp3'));
			set_block_setting($block_id, 'filter_ole',				KT_Filter::postBool('filter_ole'));
			set_block_setting($block_id, 'filter_pcx',				KT_Filter::postBool('filter_pcx'));
			set_block_setting($block_id, 'filter_pdf',				KT_Filter::postBool('filter_pdf'));
			set_block_setting($block_id, 'filter_png',				KT_Filter::postBool('filter_png'));
			set_block_setting($block_id, 'filter_tiff',				KT_Filter::postBool('filter_tiff'));
			set_block_setting($block_id, 'filter_wav',				KT_Filter::postBool('filter_wav'));
			set_block_setting($block_id, 'filter_audio',			KT_Filter::postBool('filter_audio'));
			set_block_setting($block_id, 'filter_book',				KT_Filter::postBool('filter_book'));
			set_block_setting($block_id, 'filter_card',				KT_Filter::postBool('filter_card'));
			set_block_setting($block_id, 'filter_certificate',		KT_Filter::postBool('filter_certificate'));
			set_block_setting($block_id, 'filter_coat',				KT_Filter::postBool('filter_coat'));
			set_block_setting($block_id, 'filter_document',			KT_Filter::postBool('filter_document'));
			set_block_setting($block_id, 'filter_electronic',		KT_Filter::postBool('filter_electronic'));
			set_block_setting($block_id, 'filter_fiche',			KT_Filter::postBool('filter_fiche'));
			set_block_setting($block_id, 'filter_film',				KT_Filter::postBool('filter_film'));
			set_block_setting($block_id, 'filter_magazine',			KT_Filter::postBool('filter_magazine'));
			set_block_setting($block_id, 'filter_manuscript',		KT_Filter::postBool('filter_manuscript'));
			set_block_setting($block_id, 'filter_map',				KT_Filter::postBool('filter_map'));
			set_block_setting($block_id, 'filter_newspaper',		KT_Filter::postBool('filter_newspaper'));
			set_block_setting($block_id, 'filter_other',			KT_Filter::postBool('filter_other'));
			set_block_setting($block_id, 'filter_painting',			KT_Filter::postBool('filter_painting'));
			set_block_setting($block_id, 'filter_photo',			KT_Filter::postBool('filter_photo'));
			set_block_setting($block_id, 'filter_tombstone',		KT_Filter::postBool('filter_tombstone'));
			set_block_setting($block_id, 'filter_video',			KT_Filter::postBool('filter_video'));
			exit;
		}

		$filter		= get_block_setting($block_id, 'filter', 'all');
		$controls	= get_block_setting($block_id, 'controls', true);
		$start		= get_block_setting($block_id, 'start', false);
		$filters	= array(
			'avi'        =>get_block_setting($block_id, 'filter_avi', false),
			'bmp'        =>get_block_setting($block_id, 'filter_bmp', true),
			'gif'        =>get_block_setting($block_id, 'filter_gif', true),
			'jpeg'       =>get_block_setting($block_id, 'filter_jpeg', true),
			'mp3'        =>get_block_setting($block_id, 'filter_mp3', false),
			'ole'        =>get_block_setting($block_id, 'filter_ole', true),
			'pcx'        =>get_block_setting($block_id, 'filter_pcx', true),
			'pdf'        =>get_block_setting($block_id, 'filter_pdf', false),
			'png'        =>get_block_setting($block_id, 'filter_png', true),
			'tiff'       =>get_block_setting($block_id, 'filter_tiff', true),
			'wav'        =>get_block_setting($block_id, 'filter_wav', false),
			'audio'      =>get_block_setting($block_id, 'filter_audio', false),
			'book'       =>get_block_setting($block_id, 'filter_book', true),
			'card'       =>get_block_setting($block_id, 'filter_card', true),
			'certificate'=>get_block_setting($block_id, 'filter_certificate', true),
			'coat'       =>get_block_setting($block_id, 'filter_coat', true),
			'document'   =>get_block_setting($block_id, 'filter_document', true),
			'electronic' =>get_block_setting($block_id, 'filter_electronic', true),
			'fiche'      =>get_block_setting($block_id, 'filter_fiche', true),
			'film'       =>get_block_setting($block_id, 'filter_film', true),
			'magazine'   =>get_block_setting($block_id, 'filter_magazine', true),
			'manuscript' =>get_block_setting($block_id, 'filter_manuscript', true),
			'map'        =>get_block_setting($block_id, 'filter_map', true),
			'newspaper'  =>get_block_setting($block_id, 'filter_newspaper', true),
			'other'      =>get_block_setting($block_id, 'filter_other', true),
			'painting'   =>get_block_setting($block_id, 'filter_painting', true),
			'photo'      =>get_block_setting($block_id, 'filter_photo', true),
			'tombstone'  =>get_block_setting($block_id, 'filter_tombstone', true),
			'video'      =>get_block_setting($block_id, 'filter_video', false),
		);

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show only individuals, events, or all?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo select_edit_control('filter', array('indi'=>KT_I18N::translate('Individuals'), 'event'=>KT_I18N::translate('Facts and events'), 'all'=>KT_I18N::translate('All')), null, $filter, ''); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Filter'); ?></label>
		</div>
		<div class="cell medium-7">
			<div class="grid-x grid-padding-x">
				<label class="font-bold"><?php echo KT_Gedcom_Tag::getLabel('FORM'); ?></label>
				<div class="cell text-center">
					<input type="checkbox" class="parent" data-group=".group1">
					<label><?php echo KT_I18N::translate('Select all'); ?></label>
				</div>
				<div class="grid-x grid-padding-x">
					<?php $options = array ('avi', 'bmp', 'gif', 'jpeg', 'mp3', 'ole', 'pcx', 'png', 'tiff', 'wav');
					for ($i = 0; $i < count($options); $i++) {
						echo '
							<div class="checkboxes cell medium-4">
								<input class="group1" type="checkbox" value="yes"  name="filter_' . $options[$i] . '"';
									if ($filters[$options[$i]]) echo ' checked="checked"'; echo '>
								<label for="filter_' . $options[$i] . '">' . $options[$i] . '</label>
							</div>';
					} ?>
				</div>
				<label class="font-bold"><?php echo KT_Gedcom_Tag::getLabel('TYPE'); ?></label>
				<div class="cell text-center">
					<input type="checkbox" class="parent" data-group=".group2">
					<label><?php echo KT_I18N::translate('Select all'); ?></label>
				</div>
				<div class="grid-x grid-padding-x">
					<?php $i = 0;
					foreach (KT_Gedcom_Tag::getFileFormTypes() as $typeName => $typeValue) {
						echo '
							<div class="checkboxes cell medium-4">
								<input class="group2" type="checkbox" value="yes" name="filter_' . $typeName . '"';
									if ($filters[$typeName]) echo ' checked="checked" '; echo '>
								<label for="filter_' . $typeName . '">' . $typeValue .'</label>
							</div>';
						$i++;
					} ?>
				</div>
			</div>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show slide show controls?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('controls', $controls); ?>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Start slide show on page load?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('start', $start); ?>
		</div>
		<hr>
	<?php }
}
