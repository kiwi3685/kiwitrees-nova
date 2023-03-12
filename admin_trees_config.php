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

define('KT_SCRIPT_NAME', 'admin_trees_config.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
include KT_THEME_URL . 'templates/adminData.php';

global $iconStyle;
$gedID 	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Family tree configuration'));

$privacyConstants = array(
	'privacy'		=> KT_I18N::translate('Show to members'),
	'confidential'	=> KT_I18N::translate('Show to managers'),
	'hidden'		=> KT_I18N::translate('Hide from everyone')
);

$privacy = array(
	KT_PRIV_USER	=> KT_I18N::translate('Show to members'),
	KT_PRIV_NONE	=> KT_I18N::translate('Show to managers'),
	KT_PRIV_HIDE	=> KT_I18N::translate('Hide from everyone')
);

$surnameListStyles = array (
	'style1'		=> KT_I18N::translate('list'),
	'style2'		=> KT_I18N::translate('table'),
	'style3'		=> KT_I18N::translate('tag cloud')
);

$pedigreeLayoutOptions = array (
	'yes'		=> KT_I18N::translate('Landscape'),
	'no'		=> KT_I18N::translate('Portrait')
);

$relativeEvents = array (
	'_BIRT_GCHI' => KT_Gedcom_Tag::getLabel('_BIRT_GCHI'),
	'_MARR_GCHI' => KT_Gedcom_Tag::getLabel('_MARR_GCHI'),
	'_DEAT_GCHI' => KT_Gedcom_Tag::getLabel('_DEAT_GCHI'),
	'_BIRT_CHIL' => KT_Gedcom_Tag::getLabel('_BIRT_CHIL'),
	'_MARR_CHIL' => KT_Gedcom_Tag::getLabel('_MARR_CHIL'),
	'_DEAT_CHIL' => KT_Gedcom_Tag::getLabel('_DEAT_CHIL'),
	'_BIRT_SIBL' => KT_Gedcom_Tag::getLabel('_BIRT_SIBL'),
	'_MARR_SIBL' => KT_Gedcom_Tag::getLabel('_MARR_SIBL'),
	'_DEAT_SIBL' => KT_Gedcom_Tag::getLabel('_DEAT_SIBL'),
	'_DEAT_SPOU' => KT_Gedcom_Tag::getLabel('_DEAT_SPOU'),
	'_MARR_PARE' => KT_Gedcom_Tag::getLabel('_MARR_PARE'),
	'_DEAT_PARE' => KT_Gedcom_Tag::getLabel('_DEAT_PARE'),
	'_DEAT_GPAR' => KT_Gedcom_Tag::getLabel('_DEAT_GPAR')
);

switch (KT_Filter::post('action')) {
	case 'update-general':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		if (KT_Filter::post('gedcom_title')) {
			set_gedcom_setting($gedID, 'title', KT_Filter::post('gedcom_title'));
		}
		set_gedcom_setting($gedID, 'subtitle', KT_Filter::post('new_subtitle', KT_REGEX_UNSAFE));
		$gedcom = KT_Filter::post('gedcom');
		if ($gedcom && $gedcom != KT_GEDCOM) {
			try {
				KT_DB::prepare("UPDATE `##gedcom` SET gedcom_name = ? WHERE gedcom_id = ?")->execute(array($gedcom, $gedID));
				KT_DB::prepare("UPDATE `##site_setting` SET setting_value = ? WHERE setting_name='DEFAULT_GEDCOM' AND setting_value = ?")->execute(array($gedcom, KT_GEDCOM));
			} catch (Exception $ex) {
				// Probably a duplicate name.
				$gedcom = KT_GEDCOM;
			}
		}
		// For backwards compatibility with we store the two calendar formats in one variable
		// e.g. "gregorian_and_jewish"
		set_gedcom_setting($gedID, 'CALENDAR_FORMAT', implode('_and_', array_unique(array(
			KT_Filter::post('NEW_CALENDAR_FORMAT0', 'gregorian|julian|french|jewish|hijri|jalali', 'none'),
			KT_Filter::post('NEW_CALENDAR_FORMAT1', 'gregorian|julian|french|jewish|hijri|jalali', 'none')
		))));
		set_gedcom_setting($gedID, 'FAM_ID_PREFIX',					KT_Filter::post('NEW_FAM_ID_PREFIX'));
		set_gedcom_setting($gedID, 'GEDCOM_ID_PREFIX',				KT_Filter::post('NEW_GEDCOM_ID_PREFIX'));
		set_gedcom_setting($gedID, 'GENERATE_UIDS',					KT_Filter::postBool('NEW_GENERATE_UIDS'));
		set_gedcom_setting($gedID, 'LANGUAGE',						KT_Filter::post('GEDCOMLANG'));
		set_gedcom_setting($gedID, 'MEDIA_ID_PREFIX',				KT_Filter::post('NEW_MEDIA_ID_PREFIX'));
		set_gedcom_setting($gedID, 'NOTE_ID_PREFIX',				KT_Filter::post('NEW_NOTE_ID_PREFIX'));
		set_gedcom_setting($gedID, 'PEDIGREE_ROOT_ID',				KT_Filter::post('NEW_PEDIGREE_ROOT_ID', KT_REGEX_XREF));
		set_gedcom_setting($gedID, 'REPO_ID_PREFIX',				KT_Filter::post('NEW_REPO_ID_PREFIX'));
		set_gedcom_setting($gedID, 'SOURCE_ID_PREFIX',				KT_Filter::post('NEW_SOURCE_ID_PREFIX'));
		set_gedcom_setting($gedID, 'USE_RIN',						KT_Filter::postBool('NEW_USE_RIN'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#general');
		exit;
	case 'update-contact':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'CONTACT_USER_ID',				KT_Filter::post('NEW_CONTACT_USER_ID'));
		set_gedcom_setting($gedID, 'KIWITREES_EMAIL',				KT_Filter::post('NEW_KIWITREES_EMAIL'));
		set_gedcom_setting($gedID, 'WEBMASTER_USER_ID',				KT_Filter::post('NEW_WEBMASTER_USER_ID'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#contact');
		exit;
	case 'update-meta':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'META_DESCRIPTION',				KT_Filter::post('NEW_META_DESCRIPTION'));
		set_gedcom_setting($gedID, 'META_TITLE',					KT_Filter::post('NEW_META_TITLE'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#meta');
		exit;
	case 'update-privacy':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'HIDE_LIVE_PEOPLE',				KT_Filter::postBool('NEW_HIDE_LIVE_PEOPLE'));
		set_gedcom_setting($gedID, 'KEEP_ALIVE_YEARS_BIRTH',		KT_Filter::post('KEEP_ALIVE_YEARS_BIRTH', KT_REGEX_INTEGER, 0));
		set_gedcom_setting($gedID, 'KEEP_ALIVE_YEARS_DEATH',		KT_Filter::post('KEEP_ALIVE_YEARS_DEATH', KT_REGEX_INTEGER, 0));
		set_gedcom_setting($gedID, 'MAX_ALIVE_AGE',					KT_Filter::post('MAX_ALIVE_AGE', KT_REGEX_INTEGER, 100));
		set_gedcom_setting($gedID, 'SHOW_DEAD_PEOPLE',				KT_Filter::post('SHOW_DEAD_PEOPLE'));
		set_gedcom_setting($gedID, 'SHOW_LIVING_NAMES',				KT_Filter::post('SHOW_LIVING_NAMES'));
		set_gedcom_setting($gedID, 'SHOW_PRIVATE_RELATIONSHIPS',	KT_Filter::post('SHOW_PRIVATE_RELATIONSHIPS'));

		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#privacy');
		exit;

	case 'update-resn':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		// Find which record type has been selected
		$x = 1;
		for ($x; $x <= 6; $x++) {
			$select = KT_Filter::post('xref' . $x);
			if ($select) {
				$xref = $select;
				break;
			}
		}

		if (($xref || KT_Filter::post('tag_type')) && KT_Filter::post('resn')) {
			if (KT_Filter::post('xref') === '') {
				KT_DB::prepare(
					"DELETE FROM `##default_resn` WHERE gedcom_id=? AND tag_type=? AND xref IS NULL"
				)->execute(array($gedID, KT_Filter::post('tag_type')));
			}
			if (KT_Filter::post('tag_type') === '') {
				KT_DB::prepare(
					"DELETE FROM `##default_resn` WHERE gedcom_id=? AND xref=? AND tag_type IS NULL"
				)->execute(array($gedID, $xref));
			}
			KT_DB::prepare(
				"REPLACE INTO `##default_resn` (gedcom_id, xref, tag_type, resn) VALUES (?, NULLIF(?, ''), NULLIF(?, ''), ?)"
			)->execute(array($gedID, $xref, KT_Filter::post('tag_type'), KT_Filter::post('resn')));
		}

		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#restriction');
		exit;
	case 'update-media':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		// Only accept valid folders for NEW_MEDIA_DIRECTORY
		$NEW_MEDIA_DIRECTORY = preg_replace('/[\/\\\\]+/', '/', KT_Filter::post('NEW_MEDIA_DIRECTORY') . '/');
		if (substr($NEW_MEDIA_DIRECTORY, 0, 1) == '/') {
			$NEW_MEDIA_DIRECTORY = substr($NEW_MEDIA_DIRECTORY, 1);
		}

		if ($NEW_MEDIA_DIRECTORY) {
			if (is_dir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY)) {
				set_gedcom_setting($gedID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
			} elseif (@mkdir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY, 0755, true)) {
				set_gedcom_setting($gedID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
			}
		}

		set_gedcom_setting($gedID, 'MEDIA_UPLOAD',					KT_Filter::post('NEW_MEDIA_UPLOAD'));
		set_gedcom_setting($gedID, 'SAVE_WATERMARK_IMAGE',			KT_Filter::postBool('NEW_SAVE_WATERMARK_IMAGE'));
		set_gedcom_setting($gedID, 'SAVE_WATERMARK_THUMB',			KT_Filter::postBool('NEW_SAVE_WATERMARK_THUMB'));
		set_gedcom_setting($gedID, 'SHOW_HIGHLIGHT_IMAGES',			KT_Filter::postBool('NEW_SHOW_HIGHLIGHT_IMAGES'));
		set_gedcom_setting($gedID, 'SHOW_MEDIA_DOWNLOAD',			KT_Filter::postBool('NEW_SHOW_MEDIA_DOWNLOAD'));
		set_gedcom_setting($gedID, 'SHOW_NO_WATERMARK',				KT_Filter::post('NEW_SHOW_NO_WATERMARK'));
		set_gedcom_setting($gedID, 'THUMBNAIL_WIDTH',				KT_Filter::post('NEW_THUMBNAIL_WIDTH'));
		set_gedcom_setting($gedID, 'USE_SILHOUETTE',					KT_Filter::postBool('NEW_USE_SILHOUETTE'));
		set_gedcom_setting($gedID, 'WATERMARK_THUMB',				KT_Filter::postBool('NEW_WATERMARK_THUMB'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#media');
		exit;
	case 'update-layout':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'ALL_CAPS',						KT_Filter::postBool('NEW_ALL_CAPS'));
		set_gedcom_setting($gedID, 'COMMON_NAMES_ADD',				str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_ADD')));
		set_gedcom_setting($gedID, 'COMMON_NAMES_REMOVE',			str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_REMOVE')));
		set_gedcom_setting($gedID, 'COMMON_NAMES_THRESHOLD',			KT_Filter::post('NEW_COMMON_NAMES_THRESHOLD', KT_REGEX_INTEGER, 40));
		set_gedcom_setting($gedID, 'DEFAULT_PEDIGREE_GENERATIONS',	KT_Filter::post('NEW_DEFAULT_PEDIGREE_GENERATIONS'));
		set_gedcom_setting($gedID, 'MAX_DESCENDANCY_GENERATIONS',	KT_Filter::post('NEW_MAX_DESCENDANCY_GENERATIONS'));
		set_gedcom_setting($gedID, 'MAX_PEDIGREE_GENERATIONS',		KT_Filter::post('NEW_MAX_PEDIGREE_GENERATIONS'));
		set_gedcom_setting($gedID, 'PEDIGREE_LAYOUT',				KT_Filter::postBool('NEW_PEDIGREE_LAYOUT'));
		set_gedcom_setting($gedID, 'SHOW_EST_LIST_DATES',			KT_Filter::postBool('NEW_SHOW_EST_LIST_DATES'));
		set_gedcom_setting($gedID, 'SHOW_LAST_CHANGE',				KT_Filter::postBool('NEW_SHOW_LAST_CHANGE'));
		set_gedcom_setting($gedID, 'SHOW_PEDIGREE_PLACES',			KT_Filter::post('NEW_SHOW_PEDIGREE_PLACES'));
		set_gedcom_setting($gedID, 'SHOW_PEDIGREE_PLACES_SUFFIX',	KT_Filter::postBool('NEW_SHOW_PEDIGREE_PLACES_SUFFIX'));
		set_gedcom_setting($gedID, 'SHOW_RELATIVES_EVENTS',			KT_Filter::post('NEW_SHOW_RELATIVES_EVENTS'));
		set_gedcom_setting($gedID, 'SUBLIST_TRIGGER_I',				KT_Filter::post('NEW_SUBLIST_TRIGGER_I', KT_REGEX_INTEGER, 200));
		set_gedcom_setting($gedID, 'SURNAME_LIST_STYLE',				KT_Filter::post('NEW_SURNAME_LIST_STYLE'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#layout');
		exit;
	case 'update-hide':
		if (!KT_Filter::checkCsrf()) {
			break;
		}

		set_gedcom_setting($gedID, 'ABBREVIATE_CHART_LABELS',		KT_Filter::postBool('NEW_ABBREVIATE_CHART_LABELS'));
		set_gedcom_setting($gedID, 'CHART_BOX_TAGS',					implode(",", KT_Filter::post('NEW_CHART_BOX_TAGS')));
		set_gedcom_setting($gedID, 'EXPAND_NOTES',					KT_Filter::postBool('NEW_EXPAND_NOTES'));
		set_gedcom_setting($gedID, 'EXPAND_SOURCES',					KT_Filter::postBool('NEW_EXPAND_SOURCES'));
		set_gedcom_setting($gedID, 'HIDE_GEDCOM_ERRORS',				KT_Filter::postBool('NEW_HIDE_GEDCOM_ERRORS'));
		set_gedcom_setting($gedID, 'PEDIGREE_FULL_DETAILS',			KT_Filter::postBool('NEW_PEDIGREE_FULL_DETAILS'));
		set_gedcom_setting($gedID, 'PEDIGREE_SHOW_GENDER',			KT_Filter::postBool('NEW_PEDIGREE_SHOW_GENDER'));
		set_gedcom_setting($gedID, 'SHOW_COUNTER',					KT_Filter::postBool('NEW_SHOW_COUNTER'));
		set_gedcom_setting($gedID, 'SHOW_FACT_ICONS',				KT_Filter::postBool('NEW_SHOW_FACT_ICONS'));
		set_gedcom_setting($gedID, 'SHOW_GEDCOM_RECORD',				KT_Filter::postBool('NEW_SHOW_GEDCOM_RECORD'));
		set_gedcom_setting($gedID, 'SHOW_PARENTS_AGE',				KT_Filter::postBool('NEW_SHOW_PARENTS_AGE'));
		set_gedcom_setting($gedID, 'SHOW_LDS_AT_GLANCE',				KT_Filter::postBool('NEW_SHOW_LDS_AT_GLANCE'));

		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#hide');
		exit;
	case 'update-edit':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'INDI_FACTS_ADD',					implode(",", KT_Filter::post('NEW_INDI_FACTS_ADD')));


		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#edit');
		exit;



	case 'update-theme':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		set_gedcom_setting($gedID, 'THEME_DIR',						KT_Filter::post('NEW_THEME_DIR'));


		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#theme');
		exit;

/*
			set_gedcom_setting($gedID, 'ADVANCED_NAME_FACTS',			KT_Filter::post('NEW_ADVANCED_NAME_FACTS'));
			set_gedcom_setting($gedID, 'ADVANCED_PLAC_FACTS',			KT_Filter::post('NEW_ADVANCED_PLAC_FACTS'));
			set_gedcom_setting($gedID, 'FAM_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_ADD')));
			set_gedcom_setting($gedID, 'FAM_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_QUICK')));
			set_gedcom_setting($gedID, 'FAM_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_UNIQUE')));
			set_gedcom_setting($gedID, 'FULL_SOURCES',					KT_Filter::postBool('NEW_FULL_SOURCES'));
			set_gedcom_setting($gedID, 'GEDCOM_MEDIA_PATH',				KT_Filter::post('NEW_GEDCOM_MEDIA_PATH'));
			set_gedcom_setting($gedID, 'INDI_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_QUICK')));
			set_gedcom_setting($gedID, 'INDI_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_UNIQUE')));
			set_gedcom_setting($gedID, 'NO_UPDATE_CHAN',					KT_Filter::postBool('NEW_NO_UPDATE_CHAN'));
			set_gedcom_setting($gedID, 'PREFER_LEVEL2_SOURCES',			KT_Filter::post('NEW_PREFER_LEVEL2_SOURCES'));
			set_gedcom_setting($gedID, 'QUICK_REQUIRED_FACTS',			KT_Filter::post('NEW_QUICK_REQUIRED_FACTS'));
			set_gedcom_setting($gedID, 'QUICK_REQUIRED_FAMFACTS',		KT_Filter::post('NEW_QUICK_REQUIRED_FAMFACTS'));
			set_gedcom_setting($gedID, 'REPO_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_ADD')));
			set_gedcom_setting($gedID, 'REPO_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_QUICK')));
			set_gedcom_setting($gedID, 'REPO_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_UNIQUE')));
			set_gedcom_setting($gedID, 'SOUR_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_ADD')));
			set_gedcom_setting($gedID, 'SOUR_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_QUICK')));
			set_gedcom_setting($gedID, 'SOUR_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_UNIQUE')));
			set_gedcom_setting($gedID, 'SURNAME_TRADITION',				KT_Filter::post('NEW_SURNAME_TRADITION'));
			set_gedcom_setting($gedID, 'COLOR_PALETTE',					KT_Filter::post('NEW_COLOR_PALETTE'));
			set_gedcom_setting($gedID, 'USE_GEONAMES',					KT_Filter::postBool('NEW_USE_GEONAMES'));




		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
	exit;
	*/
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addExternalJavascript(KT_CHOSEN_JS)
	->addExternalJavascript(KT_CONFIRM_JS)
	->addInlineJavascript('
		autocomplete();

		jQuery(".chosen_select").chosen({width: "100%"});

		jQuery("#record-type-selector").change(function() {
			jQuery(".autocomplete_container").addClass("hidden");

			var selectVal = jQuery(this).children("option:selected").val();

			jQuery("#select-" + selectVal).removeClass("hidden");
			jQuery("#autocompleteInput-" + selectVal).focus();
		});

	');

echo relatedPages($trees, KT_SCRIPT_NAME);

echo pageStart('family_tree_config', $controller->getPageTitle(), 'y', '', 'administration/family_tree_config'); ?>

		<div class="cell medium-4">
			<form method="post" action="#" name="tree">
				<?php echo select_ged_control('gedID', KT_Tree::getIdList(), null, $gedID, ' onchange="tree.submit();"'); ?>
			</form>
		</div>
		<div class="cell">
			<ul id="tree_config_tabs" class="tabs" data-responsive-accordion-tabs="tabs small-accordion large-tabs" data-allow-all-closed="true" data-deep-link="true">
				<li class="tabs-title is-active">
					<a href="#general" aria-selected="true"><?php echo KT_I18N::translate('General'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#contact"><?php echo KT_I18N::translate('Contact information'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#meta"><?php echo KT_I18N::translate('Metadata'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#privacy"><?php echo KT_I18N::translate('Privacy'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#restriction"><?php echo KT_I18N::translate('Restrictions'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#media"><?php echo KT_I18N::translate('Media'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#layout"><?php echo KT_I18N::translate('Layout'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#hide"><?php echo KT_I18N::translate('Hide &amp; show'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#edit"><?php echo KT_I18N::translate('Edit options'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#theme"><?php echo KT_I18N::translate('Theme'); ?></a>
				</li>
			</ul>
			<div class="tabs-content" data-tabs-content="tree_config_tabs">

				<!-- General tab -->
				<div class="tabs-panel is-active" id="general">
					<form method="post" name="configform-general" action="<?php echo KT_SCRIPT_NAME . '#general'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-general">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<div class="cell large-3">
								<label for="tree_title"><?php echo KT_I18N::translate('Family tree title'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_title" name="gedcom_title" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting($gedID, 'title')); ?>" required maxlength="255">
							</div>
							<div class="cell large-3">
								<label for="tree_subtitle"><?php echo KT_I18N::translate('Family tree subtitle'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_subtitle" name="new_subtitle"value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting($gedID, 'subtitle')); ?>" maxlength="255">
							</div>
							<div class="cell large-3">
								<label for="tree_url"><?php echo KT_I18N::translate('Website URL'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="input-group">
									<span class="input-group-label"><span class="show-for-medium"><?php echo KT_SERVER_NAME, KT_SCRIPT_PATH ?></span>index.php?ged=</span>
									<input class="input-group-field" id="tree_url" type="text" name="gedcom" value="<?php echo KT_Filter::escapeHtml(KT_GEDCOM); ?>" required maxlength="255">
								</div>
								<div class="cell callout info-help ">
									<?php /*I18N: Help text for family tree URL */ echo KT_I18N::translate('Avoid spaces and punctuation. A family name might be a good choice.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_lang"><?php echo KT_I18N::translate('Language'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_language('GEDCOMLANG', get_gedcom_setting($gedID, 'LANGUAGE')); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('If a visitor to the site has not specified a preferred language in their browser configuration, or they have specified an unsupported language, then this language will be used. Typically, this setting applies to search engines.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="autocompleteInput-default"><?php echo KT_I18N::translate('Default individual'); ?></label>
							</div>
							<div class="cell large-9">
								<?php
									$person   = KT_Person::getInstance(get_gedcom_setting($gedID, 'PEDIGREE_ROOT_ID'));
									$lifeSpan = $person ? strip_tags($person->getLifespanName()) : '';
									echo autocompleteHtml(
										'default',
										'INDI',
										'',
										$lifeSpan,
										'',
										'NEW_PEDIGREE_ROOT_ID',
										''
									);
								?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This individual will be selected by default when viewing charts and reports.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_CALENDAR_FORMAT0"><?php echo KT_I18N::translate('Calendar conversion'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="grid-x grid-margin-x">
									<div class="cell large-5">
										<select id="NEW_CALENDAR_FORMAT0" name="NEW_CALENDAR_FORMAT0">
													<?php
													$CALENDAR_FORMATS=explode('_and_', $CALENDAR_FORMAT);
													if (count($CALENDAR_FORMATS)==1) {
														$CALENDAR_FORMATS[]='none';
													}
													foreach (array(
														'none'		=> KT_I18N::translate('No calendar conversion'),
														'gregorian'	=> KT_Date_Gregorian::calendarName(),
														'julian'	=> KT_Date_Julian::calendarName(),
														'french'	=> KT_Date_French::calendarName(),
														'jewish'	=> KT_Date_Jewish::calendarName(),
														'hijri'		=> KT_Date_Hijri::calendarName(),
														'jalali'	=> KT_Date_Jalali::calendarName(),
													) as $cal=>$name) {
														echo '<option value="', $cal, '"';
														if ($CALENDAR_FORMATS[0]==$cal) {
															echo ' selected="selected"';
														}
														echo '>', $name, '</option>';
													}
													?>
												</select>
									</div>
									<div class="cell large-5">
										<select id="NEW_CALENDAR_FORMAT1" name="NEW_CALENDAR_FORMAT1">
													<?php
													foreach (array(
														'none'		=> KT_I18N::translate('No calendar conversion'),
														'gregorian'	=> KT_Date_Gregorian::calendarName(),
														'julian'	=> KT_Date_Julian::calendarName(),
														'french'	=> KT_Date_French::calendarName(),
														'jewish'	=> KT_Date_Jewish::calendarName(),
														'hijri'		=> KT_Date_Hijri::calendarName(),
														'jalali'	=> KT_Date_Jalali::calendarName(),
													) as $cal=>$name) {
														echo '<option value="', $cal, '"';
														if ($CALENDAR_FORMATS[1]==$cal) {
															echo ' selected="selected"';
														}
														echo '>', $name, '</option>';
													}
													?>
												</select>
									</div>
									<div class="cell callout info-help ">
										<?php
											$d1 = new KT_Date('22 SEP 1792'); $d1 = $d1->Display(false, null, array());
											$d2 = new KT_Date('31 DEC 1805'); $d2 = $d2->Display(false, null, array());
											$d3 = new KT_Date('15 OCT 1582'); $d3 = $d3->Display(false, null, array());
											echo KT_I18N::translate('Different calendar systems are used in different parts of the world and many other calendar systems have been used in the past. Where possible you should enter dates using the calendar in which the event was originally recorded. You can then specify a conversion to show these dates in a more familiar calendar. If you regularly use two calendars you can specify two conversions and dates will be converted to both the selected calendars.');
											echo '<br>';
											echo /* I18N: The three place holders are all dates. */ KT_I18N::translate('Dates are only converted if they are valid for the calendar. For example, only dates between %1$s and %2$s will be converted to the French calendar and only dates after %3$s will be converted to the Gregorian calendar.', $d1, $d2, $d3);
											echo '<br>';
											echo KT_I18N::translate('In some calendars days start at midnight. In other calendars days start at sunset. The conversion process does not take account of the time so for any event that occurs between sunset and midnight, the conversion between these types of calendar will be one day out.');
										?>
									</div>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_USE_RIN"><?php echo KT_I18N::translate('Use RIN number instead of GEDCOM ID'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo simple_switch(
									'NEW_USE_RIN',
									true,
									get_gedcom_setting($gedID, 'USE_RIN'),
								); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('Set to <b>Yes</b> to use the RIN number instead of the GEDCOM ID when asked for Individual IDs in configuration files, user settings, and charts. This is useful for genealogy programs that do not consistently export GEDCOMs with the same ID assigned to each individual but always use the same RIN.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_subtitle"><?php echo KT_I18N::translate('Automatically create globally unique IDs'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo simple_switch(
									'NEW_GENERATE_UIDS',
									true,
									get_gedcom_setting($gedID, 'USE_RIN'),
								); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('<b>GUID</b> in this context is an acronym for «Globally Unique ID».<br>GUIDs are intended to help identify each individual in a manner that is repeatable, so that central organizations such as the Family History Center of the LDS Church in Salt Lake City, or even compatible programs running on your own server, can determine whether they are dealing with the same person no matter where the GEDCOM originates. The goal of the Family History Center is to have a central repository of genealogical data and expose it through web services. This will enable any program to access the data and update their data within it.<br><br>If you do not intend to share this GEDCOM with anyone else, you do not need to let kiwitrees create these GUIDs; however, doing so will do no harm other than increasing the size of your GEDCOM.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_xref"><?php echo KT_I18N::translate('XREF prefixes'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="grid-x grid-margin-x">
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Individual'); ?></span>
											<input type="text" name="NEW_GEDCOM_ID_PREFIX" value="<?php echo $GEDCOM_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Family'); ?></span>
											<input type="text" name="NEW_FAM_ID_PREFIX" value="<?php echo $FAM_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Source'); ?></span>
											<input type="text" name="NEW_SOURCE_ID_PREFIX" value="<?php echo $SOURCE_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
								</div>
								<div class="grid-x grid-margin-x">
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Repository'); ?></span>
											<input type="text" name="NEW_REPO_ID_PREFIX" value="<?php echo $REPO_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Media'); ?></span>
											<input type="text" name="NEW_MEDIA_ID_PREFIX" value="<?php echo $MEDIA_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
									<div class="cell large-3">
										<div class="input-group tree_xref">
											<span class="input-group-label"><?php echo KT_I18N::translate('Note'); ?></span>
											<input type="text" name="NEW_NOTE_ID_PREFIX" value="<?php echo $NOTE_ID_PREFIX; ?>" size="5" maxlength="20">
										</div>
									</div>
									<div class="cell callout info-help ">
										<?php echo KT_I18N::translate('In a family tree, each record has an internal reference number (called an “XREF”) such as “F123” or “R14”.	You can choose the prefix that will be used whenever <b>new</b> XREFs are created.'); ?>
									</div>
								</div>
							</div>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Contact tab -->
				<div class="tabs-panel" id="contact">
					<form method="post" name="configform-contact" action="<?php echo KT_SCRIPT_NAME . '#contact'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-contact">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<?php if (empty($KIWITREES_EMAIL)) {
								$KIWITREES_EMAIL = "kiwitrees-noreply@".preg_replace("/^www\./i", "", $_SERVER["SERVER_NAME"]);
							} ?>
							<div class="cell large-3">
								<label for="tree_email"><?php echo KT_I18N::translate('Kiwitrees reply address'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_email" name="NEW_KIWITREES_EMAIL" required value="<?php echo $KIWITREES_EMAIL; ?>" size="50" maxlength="255">
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('Email address to be used in the “From:” field of emails that kiwitrees creates automatically.<br>Kiwitrees can automatically create emails to notify administrators of changes that need to be reviewed. Kiwitrees also sends notification emails to users who have requested an account.<br>Usually, the “From:” field of these automatically created emails is something like From: kiwitrees-noreply@yoursite to show that no response to the email is required. To guard against spam or other email abuse, some email systems require each message’s “From:” field to reflect a valid email account and will not accept messages that are apparently from account kiwitrees-noreply.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_gene"><?php echo KT_I18N::translate('Genealogy contact'); ?></label>
							</div>
							<div class="cell large-9">
								<select id="tree_gene" name="NEW_CONTACT_USER_ID">
									<?php $CONTACT_USER_ID = get_gedcom_setting($gedID, 'CONTACT_USER_ID');
									echo '<option value="" ';
										if ($CONTACT_USER_ID == '') echo ' selected="selected"';
									echo '>' . KT_I18N::translate('none') . '</option>';
									foreach (get_all_users() as $user_id => $user_name) {
										if (get_user_setting($user_id, 'verified_by_admin')) {
											echo '<option value="' . $user_id . '"';
											if ($CONTACT_USER_ID == $user_id) echo ' selected="selected"';
											echo '>' . getUserFullName($user_id) . ' - ' . $user_name . '</option>';
										}
									} ?>
								</select>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('The person to contact about the genealogical data on this site.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_tech"><?php echo KT_I18N::translate('Technical help contact'); ?></label>
							</div>
							<div class="cell large-9">
								<select id="tree_tech" name="NEW_WEBMASTER_USER_ID">
									<?php $WEBMASTER_USER_ID=get_gedcom_setting($gedID, 'WEBMASTER_USER_ID');
									echo '<option value="" ';
									if ($WEBMASTER_USER_ID == '') echo ' selected="selected"';
									echo '>' . KT_I18N::translate('none') . '</option>';
									foreach (get_all_users() as $user_id => $user_name) {
										if (userIsAdmin($user_id)) {
											echo '<option value="' . $user_id . ' "';
											if ($WEBMASTER_USER_ID == $user_id) echo ' selected="selected"';
											echo '>' . getUserFullName($user_id) . ' - ' . $user_name . '</option>';
										}
									} ?>
								</select>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('The person to be contacted about technical questions or errors encountered on your site.'); ?>
								</div>
							</div>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Metadata tab -->
				<div class="tabs-panel" id="meta">
					<form method="post" name="configform-meta" action="<?php echo KT_SCRIPT_NAME . '#meta'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-meta">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<div class="cell large-3">
								<label for="tree_head"><?php echo KT_I18N::translate('Add to TITLE header tag'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_head" name="NEW_META_TITLE" value="<?php echo htmlspecialchars(get_gedcom_setting($gedID, 'META_TITLE')); ?>" size="40" maxlength="255">
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This text will be appended to each page title. It will be shown in the browser’s title bar, bookmarks, etc.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_desc"><?php echo KT_I18N::translate('Description META tag'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_desc" name="NEW_META_DESCRIPTION" value="<?php echo get_gedcom_setting($gedID, 'META_DESCRIPTION'); ?>" size="40" maxlength="255">
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('The value to place in the “meta description” tag in the HTML page header. Leave this field empty to use the name of the currently active family tree.'); ?>
								</div>
							</div>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Privacy tab -->
				<div class="tabs-panel" id="privacy">
					<form method="post" name="configform-privacy" action="<?php echo KT_SCRIPT_NAME . '#privacy'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="default_resn_id">
						<input type="hidden" name="action" value="update-privacy">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="calloutnalert" style="display: none;">
								<p>
									<i class="<?php echo $iconStyle; ?> fa-triangle-exclamation"></i>
									<?php echo
										/* I18N: A general error message for forms */
										KT_I18N::translate('There are some errors in your form.')
									; ?>
								</p>
							</div>
							<div class="cell large-3">
								<label for="NEW_HIDE_LIVE_PEOPLE"><?php echo KT_I18N::translate('Enable privacy'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo simple_switch(
								  'NEW_HIDE_LIVE_PEOPLE',
								  true,
								  $HIDE_LIVE_PEOPLE,
								); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This option will enable all privacy settings and hide the details of living people, as defined or modified below. If privacy is not enabled kiwitrees will ignore all the other settings on this page.'); ?>
									<?php echo KT_I18N::plural('<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d year after birth or estimated birth.','<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d years after birth or estimated birth.', get_gedcom_setting($gedID, 'MAX_ALIVE_AGE'), get_gedcom_setting($gedID, 'MAX_ALIVE_AGE')); ?>
									<br>
									<?php echo KT_I18N::translate('The length of time after birth can be set using the option "Age at which to assume a person is dead".'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="SHOW_DEAD_PEOPLE"><?php echo KT_I18N::translate('Show dead people'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_access_level("SHOW_DEAD_PEOPLE", get_gedcom_setting($gedID, 'SHOW_DEAD_PEOPLE')); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('Set the privacy access level for all dead people.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_maxage"><?php echo KT_I18N::translate('Age at which to assume a person is dead'); ?></label>
							</div>
							<div class="cell large-2">
								<div class="input-group">
										<input type="number" id="tree_maxage" name="MAX_ALIVE_AGE" value="<?php echo get_gedcom_setting($gedID, 'MAX_ALIVE_AGE'); ?>" max="9999" min="1" required>
									<span class="input-group-label"><?php echo KT_I18N::translate('years'); ?></span>
								</div>
							</div>
							<div class="cell large-7"></div>
							<div class="cell large-9 large-offset-3">
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('If this person has any events other than death, burial, or cremation more recent than this number of years, they are considered to be "alive". Children\'s birth dates are considered to be such events for this purpose.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_extend"><?php /* I18N: ... [who were] born in the last XX years or died in the last YY years */ echo KT_I18N::translate('Extend privacy of dead people'); ?></label>
							</div>
							<div class="cell large-9" id="tree_extend">
								<?php echo /* I18N: ... Extend privacy to dead people [who were] ... */ KT_I18N::translate(
										'Born in the last %1$s years or died in the last %2$s years',
										'<input type="text" class="tree_extend" name="KEEP_ALIVE_YEARS_BIRTH" value="'.get_gedcom_setting($gedID, 'KEEP_ALIVE_YEARS_BIRTH').'" size="5" maxlength="3">',
										'<input type="text" class="tree_extend" name="KEEP_ALIVE_YEARS_DEATH" value="'.get_gedcom_setting($gedID, 'KEEP_ALIVE_YEARS_DEATH').'" size="5" maxlength="3">'
									); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('In some countries privacy laws apply not only to living people but also to those who have died recently. This option allows you to extend the privacy rules for living people to those who were born or died within a specified number of years. Leave these values at zero to disable this feature.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="SHOW_LIVING_NAMES"><?php echo KT_I18N::translate('Names of private individuals'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_access_level("SHOW_LIVING_NAMES", get_gedcom_setting($gedID, 'SHOW_LIVING_NAMES')); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This option will show the names (but no other details) of private individuals. Individuals are private if they are still alive or if a privacy restriction has been added to their individual record. To hide a specific name, add a privacy restriction to that name record.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="SHOW_PRIVATE_RELATIONSHIPS"><?php echo KT_I18N::translate('Show private relationships'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo radio_switch_group(
									'SHOW_PRIVATE_RELATIONSHIPS',
									$privacy,
									get_gedcom_setting($gedID, 'SHOW_PRIVATE_RELATIONSHIPS')
								); ?>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This option will retain family links in private records. This means you will see empty "private" boxes on the pedigree chart and on other charts with private people.'); ?>
								</div>
							</div>
						</div>

						<?php echo singleButton(); ?>

					</form>
				</div>

				<!-- Restriction tab -->
				<?php
				$all_tags	= array();
				$tags		= array_unique(array_merge(
					explode(',', get_gedcom_setting($gedID, 'INDI_FACTS_ADD')), explode(',', get_gedcom_setting($gedID, 'INDI_FACTS_UNIQUE')),
					explode(',', get_gedcom_setting($gedID, 'FAM_FACTS_ADD' )), explode(',', get_gedcom_setting($gedID, 'FAM_FACTS_UNIQUE' )),
					explode(',', get_gedcom_setting($gedID, 'NOTE_FACTS_ADD')), explode(',', get_gedcom_setting($gedID, 'NOTE_FACTS_UNIQUE')),
					explode(',', get_gedcom_setting($gedID, 'SOUR_FACTS_ADD')), explode(',', get_gedcom_setting($gedID, 'SOUR_FACTS_UNIQUE')),
					explode(',', get_gedcom_setting($gedID, 'REPO_FACTS_ADD')), explode(',', get_gedcom_setting($gedID, 'REPO_FACTS_UNIQUE')),
					array('SOUR', 'REPO', 'OBJE', '_PRIM', 'NOTE', 'SUBM', 'SUBN', '_UID', 'CHAN')
				));
				foreach ($tags as $tag) {
					if ($tag) {
						$all_tags[$tag] = KT_Gedcom_Tag::getLabel($tag);
					}
				}
				uasort($all_tags, 'utf8_strcasecmp');

				$controller
					->addExternalJavascript(KT_DATATABLES_JS)
					->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS);

				if (KT_USER_CAN_EDIT) {
					$controller
						->addExternalJavascript(KT_DATATABLES_BUTTONS)
						->addExternalJavascript(KT_DATATABLES_HTML5);
					$buttons = 'B';
				} else {
					$buttons = '';
				}

				$controller
					->addInlineJavascript('
						jQuery("#existing-default-resn").dataTable({
							dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
							' . KT_I18N::datatablesI18N() . ',
							buttons: [{extend: "csvHtml5", exportOptions: {}}],
							autoWidth: false,
							processing: true,
							retrieve: true,
							displayLength: 15,
							pagingType: "full_numbers",
							stateSave: true,
							stateDuration: -1,
							columns: [
								/*  0 record	*/ { },
								/*  1 event		*/ { },
								/*  2 access	*/ { },
								/*  3 delete	*/ {sortable: false, class: "center delete"},
							],
							sorting: [[0,"desc"]],
						});
					');

				?>
				<div class="tabs-panel" id="restriction">
					<form method="post" name="configform-resn" action="<?php echo KT_SCRIPT_NAME . '#restriction'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="default_resn_id">
						<input type="hidden" name="action" value="update-resn">
						<div class="grid-x grid-margin-x">
							<div class="cell callout info-help">
								<?php echo KT_I18N::translate('You can set access restrictions for a specific record, event, or attribute by adding it below. These settings will be applied where other general restrictions do not exist.'); ?>
							</div>
							<div class="cell">
								<label class="h5"><?php echo KT_I18N::translate('Add a new restriction'); ?></label>
							</div>
							<div class="cell">
								<table id="new-default-resn">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Record'); ?></th>
											<th><?php echo KT_I18N::translate('Event or attribute'); ?></th>
											<th><?php echo KT_I18N::translate('Access level'); ?></th>
										</tr>
									</thead>
									<tbody>
										<tr class="vertAlign">
											<td>
												<select id="record-type-selector">
													<option value=""><?php echo KT_I18N::translate('Select record type'); ?></option>
													<option value="INDI"><?php echo KT_I18N::translate('Individual'); ?></option>
													<option value="FAM" ><?php echo KT_I18N::translate('Family'); ?></option>
													<option value="SOUR"><?php echo KT_I18N::translate('Source'); ?></option>
													<option value="REPO"><?php echo KT_I18N::translate('Repository'); ?></option>
													<option value="OBJE"><?php echo KT_I18N::translate('Media object'); ?></option>
													<option value="NOTE"><?php echo KT_I18N::translate('Shared note'); ?></option>
												</select>

												<?php echo autocompleteHtml('INDI', 'INDI', '', '', KT_I18N::translate('Individual name'), 'xref1', ''); ?>

												<?php echo autocompleteHtml('FAM', 'FAM', '', '', KT_I18N::translate('Names of husband & wife'), 'xref2', ''); ?>

												<?php echo autocompleteHtml('SOUR', 'SOUR', '', '', KT_I18N::translate('Source title'), 'xref3', ''); ?>

												<?php echo autocompleteHtml('REPO', 'REPO', '', '', KT_I18N::translate('Repository name'), 'xref4', ''); ?>

												<?php echo autocompleteHtml('OBJE', 'OBJE', '', '', KT_I18N::translate('Media object title'), 'xref5', ''); ?>

												<?php echo autocompleteHtml('NOTE', 'NOTE', '', '', KT_I18N::translate('Shared note title'), 'xref6', ''); ?>

											</td>
											<td><?php echo select_edit_control('tag_type', $all_tags, '', null, null); ?></td>
											<td><?php echo select_edit_control('resn', $privacyConstants, null, 'privacy', null); ?></td>
										</tr>
									</tbody>
								</table>
							</div>

							<?php echo submitButtons("jQuery('.autocomplete_container').addClass('hidden')"); ?>

							<hr class="cell">
							<!-- Existing restrictions table -->
							<div class="cell">
								<label class="h5"><?php echo KT_I18N::translate('Existing restrictions'); ?></label>
							</div>
							<div class="cell">
								<table id="existing-default-resn">
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Record'); ?></th>
											<th><?php echo KT_I18N::translate('Fact or event'); ?></th>
											<th><?php echo KT_I18N::translate('Access level'); ?></th>
											<th>
												<div class="text-center delete_resn">
													<button type="submit" class="button small primary" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>')) {return checkbox_delete('resn');} else {return false;}">
														<?php echo KT_I18N::translate('Delete'); ?>
													</button>
													<input type="checkbox" onclick="toggle_select(this)">
												</div>
											</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$rows = KT_DB::prepare(
											"SELECT default_resn_id, tag_type, xref, resn".
											" FROM `##default_resn`".
											" LEFT JOIN `##name` ON (gedcom_id=n_file AND xref=n_id AND n_num=0)".
											" WHERE gedcom_id=?".
											" ORDER BY xref IS NULL, n_sort, xref, tag_type"
										)->execute(array($gedID))->fetchAll();
										$n = 1; ?>

										<?php foreach ($rows as $row) { ?>
											<tr>
												<td>
													<?php
													$n++;
													if ($row->xref) {
														$record = KT_GedcomRecord::getInstance($row->xref);
														if ($record) {
															echo '<a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a>';
														} else {
															echo KT_I18N::translate('this record does not exist');
														}
													} else {
														echo '&nbsp;';
													} ?>
												</td>
												<td>
													<?php if ($row->tag_type) {
														// I18N: e.g. Marriage (MARR)
														echo KT_Gedcom_Tag::getLabel($row->tag_type);
													} else {
														echo '&nbsp;';
													} ?>
												</td>
												<td>
													<?php echo $privacyConstants[$row->resn]; ?>
												</td>
												<td>
													<div class="text-center">
														<input
															type="checkbox"
															name="del_resn[]"
															class="check"
															value="<?php echo $row->default_resn_id; ?>"
															title="<?php echo KT_I18N::translate('Remove'); ?>"
														>
													</div>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</form>
				</div>

				<!-- Media tab -->
				<div class="tabs-panel" id="media">
					<form method="post" name="configform-media" action="<?php echo KT_SCRIPT_NAME . '#media'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-media">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<div class="cell large-3">
								<label for="tree_media"><?php echo KT_I18N::translate('Media folder'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="input-group">
									<span class="input-group-label"><?php echo KT_DATA_DIR; ?></span>
									<input class="input-group-field" id="tree_media" type="text" name="NEW_MEDIA_DIRECTORY" value="<?php echo $MEDIA_DIRECTORY; ?>" dir="ltr" maxlength="255">
								</div>
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This folder will be used to store the media files for this family tree. If you select a different folder you must also move any media files from the existing folder to the new one. If two family trees use the same media folder they will be able to share media files. If they use different media folders their media files will be kept separate.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_MEDIA_UPLOAD"><?php echo KT_I18N::translate('Option to upload new media files'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo radio_switch_group(
									'NEW_MEDIA_UPLOAD',
									$privacy,
									get_gedcom_setting($gedID, 'MEDIA_UPLOAD')
								); ?>
								 <div class="cell callout info-help ">
									<?php echo KT_I18N::translate('If you are concerned that users might upload inappropriate images, you can restrict media uploads to managers only.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_SHOW_MEDIA_DOWNLOAD"><?php echo KT_I18N::translate('Download link in media viewer'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo radio_switch_group(
									'NEW_SHOW_MEDIA_DOWNLOAD',
									$privacy,
									get_gedcom_setting($gedID, 'SHOW_MEDIA_DOWNLOAD')
								); ?>
								 <div class="cell callout info-help ">
									 <?php echo KT_I18N::translate('The media viewer can show a link which when clicked will download the media file to the local PC.<br>You may want to hide the download link for security reasons.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_thumb"><?php echo KT_I18N::translate('Width of generated thumbnails'); ?></label>
							</div>
							<div class="cell large-2">
								<div class="input-group">
									<input class="input-group-field" type="text" id="tree_thumb" name="NEW_THUMBNAIL_WIDTH" value="<?php echo $THUMBNAIL_WIDTH; ?>" maxlength="4" required>
									<span class="input-group-label"><?php echo /* I18N: the suffix to a media size */ KT_I18N::translate('pixels'); ?></span>
								</div>
							</div>
							<div class="cell large-7"></div>
							<div class="cell large-offset-3">
								<div class="cell callout info-help ">
									<?php echo KT_I18N::translate('This is the width (in pixels) that the program will use when automatically generating thumbnails. The default setting is 100.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_SHOW_HIGHLIGHT_IMAGES"><?php echo KT_I18N::translate('Show highlight images in people boxes'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo simple_switch(
									'NEW_SHOW_HIGHLIGHT_IMAGES',
									true,
									get_gedcom_setting($gedID, 'SHOW_HIGHLIGHT_IMAGES')
								); ?>
								 <div class="cell callout info-help ">
									 <a href="<?php echo KT_KIWITREES_URL; ?>/faqs/general-topics/highlighted-images/" target="_blank" rel="noopener noreferrer">
 										<?php echo KT_I18N::translate('Click here to view more information about highlight images on the kiwitrees.net website FAQs'); ?>
 									</a>
								</div>
							</div>
							<div class="cell large-3">
								<label for="NEW_USE_SILHOUETTE"><?php echo KT_I18N::translate('Use silhouettes'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo simple_switch(
									'NEW_USE_SILHOUETTE',
									true,
									get_gedcom_setting($gedID, 'USE_SILHOUETTE')
								); ?>
								 <div class="cell callout info-help ">
									 <?php echo KT_I18N::translate('Use silhouette images when no highlighted image for that individual has been specified. The images used are specific to the gender of the individual in question and may also vary according to the theme you use.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_editor"><?php echo KT_I18N::translate('External image editor'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="url" id="tree_editor" name="NEW_IMAGE_EDITOR" required value="<?php echo $IMAGE_EDITOR; ?>" size="50" maxlength="255" dir="ltr">
								 <div class="cell callout info-help ">
									 <?php echo KT_I18N::translate('Preferred URL link to an external image editor provided for members use when uploading media images. The default link is %s>', $IMAGE_EDITOR); ?>
								</div>
							</div>
							<div class="cell padding-bottom padding-left">
								<button type="button" class="button hollow" data-toggle="watermarkOptions">
									<?php echo KT_I18N::translate('Show image watermarking options'); ?>
									<i class="<?php echo $iconStyle; ?> fa-eye"></i>
								</button>
							</div>
							<div class="cell callout secondary hide" id="watermarkOptions" data-toggler=".hide">
								<div>
									<?php echo KT_I18N::translate('If you set the visibility of watermarked images to any group above "visitors" then anyone below will see a watermark text over every full size image. You can then select from the other options to include a watermark on thumbnail images and to save these watermarked images and / or thumbnails on your server'); ?>
							   </div>
								<div class="grid-x grid-margin-x">
									<div class="cell large-3">
										<label for="NEW_SHOW_NO_WATERMARK"><?php echo KT_I18N::translate('Full size images without watermarks'); ?></label>
									</div>
									<div class="cell large-9">
										<?php echo radio_switch_group(
											'NEW_SHOW_NO_WATERMARK',
											$privacy,
											get_gedcom_setting($gedID, 'SHOW_NO_WATERMARK')
										); ?>
										 <div class="cell callout info-help ">
											 <?php echo KT_I18N::translate('Watermarks are optional and normally shown just to visitors.'); ?>
										</div>
									</div>
									<div class="cell large-3">
										<label for="NEW_WATERMARK_THUMB"><?php echo KT_I18N::translate('Add watermarks to thumbnails'); ?></label>
									</div>
									<div class="cell large-9">
										<?php echo simple_switch(
											'NEW_WATERMARK_THUMB',
											true,
											get_gedcom_setting($gedID, 'WATERMARK_THUMB')
										); ?>
										 <div class="cell callout info-help ">
											 <?php echo KT_I18N::translate('A watermark is text that is added to an image to discourage others from copying it without permission. If you select yes further options will be available.'); ?>
										</div>
									</div>
									<div class="cell large-3">
										<label for="NEW_SAVE_WATERMARK_IMAGE"><?php echo KT_I18N::translate('Store watermarked full size images on server'); ?></label>
									</div>
									<div class="cell large-9">
										<?php echo simple_switch(
											'NEW_SAVE_WATERMARK_IMAGE',
											true,
											get_gedcom_setting($gedID, 'SAVE_WATERMARK_IMAGE')
										); ?>
										 <div class="cell callout info-help ">
											 <?php echo KT_I18N::translate('Watermarks can be slow to generate for large images. Busy sites may prefer to generate them once and store the watermarked image on the server.'); ?>
										</div>
									</div>
									<div class="cell large-3">
										<label for="NEW_SAVE_WATERMARK_THUMB"><?php echo KT_I18N::translate('Store watermarked thumbnails on server'); ?></label>
									</div>
									<div class="cell large-9">
										<?php echo simple_switch(
											'NEW_SAVE_WATERMARK_THUMB',
											true,
											get_gedcom_setting($gedID, 'SAVE_WATERMARK_THUMB')
										); ?>
										 <div class="cell callout info-help ">
											 <?php echo KT_I18N::translate('Busy sites may prefer to generate them once and store the watermarked thumbnails on the server.'); ?>
										</div>
									</div>
								</div>
							</div>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Layout tab -->
				<div class="tabs-panel" id="layout">
					<form method="post" name="configform-layout" action="<?php echo KT_SCRIPT_NAME . '#layout'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-layout">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<ul class="accordion" data-accordion data-allow-all-closed="true">
								<li class="accordion-item is-active" data-accordion-item> <!--  Name settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Name settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="tree_surnames"><?php echo KT_I18N::translate('Minimum number of occurrences to be a "common surname"'); ?></label>
											</div>
											<div class="cell large-9">
												<input type="text" id="tree_surnames" name="NEW_COMMON_NAMES_THRESHOLD" value="<?php echo get_gedcom_setting($gedID, 'COMMON_NAMES_THRESHOLD'); ?>" maxlength="5" required>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This is the number of times a surname must occur before it shows up in the Common Surname list on the "Statistics block".'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_addnames"><?php echo KT_I18N::translate('Names to add to common surnames list'); ?></label>
											</div>
											<div class="cell large-9">
												<input type="text" id="tree_addnames" name="NEW_COMMON_NAMES_ADD" value="<?php echo get_gedcom_setting($gedID, 'COMMON_NAMES_ADD'); ?>" maxlength="255">
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('If the number of times that a certain surname occurs is lower than the threshold, it will not appear in the list. It can be added here manually. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b>'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_minusnames"><?php echo KT_I18N::translate('Names to exclude from common surnames list'); ?></label>
											</div>
											<div class="cell large-9">
												<input type="text" id="tree_minusnames" name="NEW_COMMON_NAMES_REMOVE" value="<?php echo get_gedcom_setting($gedID, 'COMMON_NAMES_REMOVE'); ?>" maxlength="255">
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('If you want to remove a surname from the Common Surname list without increasing the threshold value, you can do that by entering the surname here. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b> Surnames entered here will also be removed from the Top-10 list on the Home Page.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_ALL_CAPS"><?php echo KT_I18N::translate('Display surnames in all CAPS'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_ALL_CAPS',
													true,
													get_gedcom_setting($gedID, 'ALL_CAPS')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Always display surnames in CAPITAL letters'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  List settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('List settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_SURNAME_LIST_STYLE"><?php echo KT_I18N::translate('Surname list style'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo radio_switch_group(
													'NEW_SURNAME_LIST_STYLE',
													$surnameListStyles,
													get_gedcom_setting($gedID, 'SURNAME_LIST_STYLE')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('The display style used for lists of surnames, such as on the <a href="module.php?mod=list_individuals&mod_action=show&show_all=yes" target="_blank">Individual List</a> page'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_maxnames"><?php echo KT_I18N::translate('Names to exclude from common surnames list'); ?></label>
											</div>
											<div class="cell large-9">
												<input type="text" id="tree_maxnames" name="NEW_SUBLIST_TRIGGER_I" value="<?php echo get_gedcom_setting($gedID, 'SUBLIST_TRIGGER_I'); ?>" maxlength="5" required>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Long lists of people with the same surname can be broken into smaller sub-lists according to the first letter of the individual\'s given name. This option determines when sub-listing of surnames will occur. To disable sub-listing completely, set this option to zero.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_SHOW_EST_LIST_DATES"><?php echo KT_I18N::translate('Show estimated dates for birth and death'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_EST_LIST_DATES',
													true, get_gedcom_setting($gedID, 'SHOW_EST_LIST_DATES')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to show estimated dates for birth and death instead of leaving blanks on individual lists and charts for individuals whose dates are not known.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_SHOW_LAST_CHANGE"><?php echo KT_I18N::translate('Show the date and time of the last update'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_LAST_CHANGE',
													true,
													get_gedcom_setting($gedID, 'SHOW_LAST_CHANGE')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Include in lists the last date a record was changed'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  Chart settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Chart settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_PEDIGREE_LAYOUT"><?php echo KT_I18N::translate('Default pedigree chart layout'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo radio_switch_group('NEW_PEDIGREE_LAYOUT', $pedigreeLayoutOptions, get_gedcom_setting($gedID, 'PEDIGREE_LAYOUT')); ?>
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ KT_I18N::translate('This option indicates whether the Pedigree chart should be generated in landscape or portrait mode.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_defgen"><?php echo KT_I18N::translate('Default pedigree generations'); ?></label>
											</div>
											<div class="cell large-9">
												<input id="tree_defgen" type="text" name="NEW_DEFAULT_PEDIGREE_GENERATIONS" value="<?php echo $DEFAULT_PEDIGREE_GENERATIONS; ?>" maxlength="3">
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ KT_I18N::translate('Set the default number of generations to display on Descendancy and Pedigree charts.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_maxgen"><?php echo KT_I18N::translate('Maximum pedigree generations'); ?></label>
											</div>
											<div class="cell large-9">
												<input id="tree_maxgen" type="text" name="NEW_MAX_PEDIGREE_GENERATIONS" value="<?php echo $MAX_PEDIGREE_GENERATIONS; ?>" maxlength="3">
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help text for the “Maximum pedigree generations” tree configuration setting */ KT_I18N::translate('Set the maximum number of generations to display on Pedigree charts.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="tree_decgen"><?php echo KT_I18N::translate('Maximum descendancy generations'); ?></label>
											</div>
											<div class="cell large-9">
												<input id="tree_decgen" type="text" name="NEW_MAX_DESCENDANCY_GENERATIONS" value="<?php echo $MAX_DESCENDANCY_GENERATIONS; ?>" maxlength="3">
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help text for the “Maximum descendancy generations” tree configuration setting */ KT_I18N::translate('Set the maximum number of generations to display on Descendancy charts.'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  Individual page settings  -->
									<a href="#" class="accordion-title clear"><?php echo KT_I18N::translate('Individual page settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_SHOW_NO_WATERMARK"><?php echo KT_I18N::translate('Show events of close relatives on individual page'); ?></label>
											</div>
											<div class="cell large-9">
												<input type="hidden" name="NEW_SHOW_RELATIVES_EVENTS" value="<?php echo $SHOW_RELATIVES_EVENTS; ?>">
												<?php echo checkbox_switch_group('NEW_SHOW_RELATIVES_EVENTS', $relativeEvents, get_gedcom_setting($gedID, 'SHOW_RELATIVES_EVENTS')); ?>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  Place settings  -->
									<a href="#" class="accordion-title clear"><?php echo KT_I18N::translate('Place settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_SHOW_PEDIGREE_PLACES_SUFFIX"><?php echo KT_I18N::translate('Abbreviate place names'); ?></label>
											</div>
											<div class="cell large-9">
												<div class="grid-x grid-margin-x" id="tree_places">
													<?php echo /* I18N: The placeholders are edit controls. Show the [first/last] [1/2/3/4/5] parts of a place name */ KT_I18N::translate(
														'Show the %1$s %2$s parts of a place name.',
														'<div class="cell medium-4 large-2">' .
															select_edit_control('NEW_SHOW_PEDIGREE_PLACES_SUFFIX',
																array(
																	false=>KT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'first'),
																	true =>KT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'last')
																),
																null,
																get_gedcom_setting($gedID, 'SHOW_PEDIGREE_PLACES_SUFFIX')
															) .
														'</div>',
														'<div class="cell medium-4 large-2">' .
															select_edit_control('NEW_SHOW_PEDIGREE_PLACES',
																array(
																	1 => KT_I18N::number(1),
																	2 => KT_I18N::number(2),
																	3 => KT_I18N::number(3),
																	4 => KT_I18N::number(4),
																	5 => KT_I18N::number(5),
																	6 => KT_I18N::number(6),
																	7 => KT_I18N::number(7),
																	8 => KT_I18N::number(8),
																	9 => KT_I18N::number(9),
																),
																null,
																get_gedcom_setting($gedID, 'SHOW_PEDIGREE_PLACES')
															) .
														'</div>'
													); ?>
													<div class="cell medium-4 large-8"></div>
												</div>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Place names are frequently too long to fit on charts, lists, etc. They can be abbreviated by showing just the first few parts of the name, such as <strong>village, county</strong>, or the last few parts of it, such as <strong>region, country</strong>.'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
							</ul>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Hide & Show tab -->
				<div class="tabs-panel" id="hide">
					<form method="post" name="configform-hide" action="<?php echo KT_SCRIPT_NAME . '#hide'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-hide">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<ul class="cell accordion" data-accordion data-allow-all-closed="true">
								<li class="accordion-item" data-accordion-item> <!--  Chart settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Chart settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_ABBREVIATE_CHART_LABELS"><?php echo KT_I18N::translate('Abbreviate chart labels'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_ABBREVIATE_CHART_LABELS',
													true,
													get_gedcom_setting($gedID, 'ABBREVIATE_CHART_LABELS')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to abbreviate labels like <b>Birth</b> on charts with just the first letter like <b>B</b>.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_PEDIGREE_FULL_DETAILS"><?php echo KT_I18N::translate('Show chart details by default'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_PEDIGREE_FULL_DETAILS',
													true,
													get_gedcom_setting($gedID, 'PEDIGREE_FULL_DETAILS')
												); ?>
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help text for the “Show chart details by default” tree configuration setting */ KT_I18N::translate('This is the initial setting for the “show details” option on the charts.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_PEDIGREE_SHOW_GENDER"><?php echo KT_I18N::translate('Show Gender icon on charts'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_PEDIGREE_SHOW_GENDER',
													true,
													get_gedcom_setting($gedID, 'PEDIGREE_SHOW_GENDER')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to show the individual\'s gender icon on charts. Since the gender is also indicated by the color of the box, this option doesn\'t conceal the gender. The option simply removes some duplicate information from the box.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_SHOW_PARENTS_AGE"><?php echo KT_I18N::translate('Age of parents next to child\'s birth date'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_PARENTS_AGE',
													true,
													get_gedcom_setting($gedID, 'SHOW_PARENTS_AGE')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to show age of father and mother next to child\'s birth date on charts.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_SHOW_LDS_AT_GLANCE"><?php echo KT_I18N::translate('LDS ordinance codes in chart boxes'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_LDS_AT_GLANCE',
													true,
													get_gedcom_setting($gedID, 'SHOW_LDS_AT_GLANCE')
												); ?>
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help for LDS ordinances show/hide option */ KT_I18N::translate('Setting this option to <b>Yes</b> will show status codes for LDS ordinances in all chart boxes.<ul><li><b>B</b> - Baptism</li><li><b>E</b> - Endowed</li><li><b>S</b> - Sealed to spouse</li><li><b>P</b> - Sealed to parents</li></ul>A person who has all of the ordinances done will have <b>BESP</b> printed after their name. Missing ordinances are indicated by <b>_</b> in place of the corresponding letter code. For example, <b>BE__</b> indicates missing <b>S</b> and <b>P</b> ordinances.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_CHART_BOX_TAGS"><?php echo KT_I18N::translate('Other facts to show in charts'); ?></label>
											</div>
											<div class="cell large-9">
												<select id="NEW_CHART_BOX_TAGS" data-placeholder="Select facts..." multiple class="chosen_select" name="NEW_CHART_BOX_TAGS[]">
													<?php $chartBoxTags = explode(",", get_gedcom_setting($gedID, 'CHART_BOX_TAGS'));
													foreach (KT_Gedcom_Tag::getPicklistFacts('ALL') as $factId => $factName) {
														$selected = in_array($factId, $chartBoxTags) ? ' selected=selected ' : ' ';
														echo '<option' . $selected . 'value="' . $factId . '">' . $factName . '&nbsp;(' . $factId . ')&nbsp;</option>';
													} ?>
												</select>
												<div class="cell callout info-help ">
													<?php echo /* I18N: Help for Other facts to show in charts */ KT_I18N::translate('A list of facts, in addition to Birth and Death, that you want to appear in chart boxes such as the Pedigree chart.'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  Individual page settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Individual page settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_SHOW_FACT_ICONS"><?php echo KT_I18N::translate('Display fact icons'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_FACT_ICONS',
													true,
													get_gedcom_setting($gedID, 'SHOW_FACT_ICONS')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Set this on to display icons near fact names on the "Facts and Events" and "Events" tab. Fact icons will be displayed only if they exist in the <i>images/facts</i> directory of the current theme.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_EXPAND_NOTES"><?php echo KT_I18N::translate('Automatically expand notes'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_EXPAND_NOTES',
													true,
													get_gedcom_setting($gedID, 'EXPAND_NOTES')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to automatically display content of a <i>Note</i> record on the Individual page.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_EXPAND_SOURCES"><?php echo KT_I18N::translate('Automatically expand sources'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_EXPAND_SOURCES',
													true,
													get_gedcom_setting($gedID, 'EXPAND_SOURCES')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This option controls whether or not to automatically display content of a <i>Source</i> record on the Individual page.'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="accordion-item" data-accordion-item> <!--  General settings  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('General settings'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_SHOW_GEDCOM_RECORD"><?php echo KT_I18N::translate('Allow users to see raw GEDCOM records'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_GEDCOM_RECORD',
													true,
													get_gedcom_setting($gedID, 'SHOW_GEDCOM_RECORD')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Setting this to <b>Yes</b> will place links on individuals, sources, and families page menus to let users bring up another window containing the raw data in GEDCOM file format.<br>Administrators always see these links regardless of this setting.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_HIDE_GEDCOM_ERRORS"><?php echo KT_I18N::translate('GEDCOM errors'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_HIDE_GEDCOM_ERRORS',
													true,
													get_gedcom_setting($gedID, 'HIDE_GEDCOM_ERRORS')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Many genealogy programs create GEDCOM files with custom tags, and kiwitrees understands most of them. When unrecognised tags are found, this option lets you choose whether to ignore them or display a warning message.'); ?>
												</div>
											</div>
											<div class="cell large-3">
												<label for="NEW_SHOW_COUNTER"><?php echo KT_I18N::translate('Hit counters'); ?></label>
											</div>
											<div class="cell large-9">
												<?php echo simple_switch(
													'NEW_SHOW_COUNTER',
													true,
													get_gedcom_setting($gedID, 'SHOW_COUNTER')
												); ?>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('Show hit counters on the Home and Individual pages.'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
							</ul>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Edit tab -->
				<div class="tabs-panel" id="edit">
					<form method="post" name="configform-edit" action="<?php echo KT_SCRIPT_NAME . '#hide'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-edit">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<ul class="cell accordion" data-accordion data-allow-all-closed="true">
								<li class="accordion-item is-active" data-accordion-item> <!--  Facts for Individual records  -->
									<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Facts for Individual records'); ?></a>
									<div class="accordion-content" data-tab-content>
										<div class="grid-x grid-margin-x">
											<div class="cell large-3">
												<label for="NEW_INDI_FACTS_ADD"><?php echo KT_I18N::translate('All individual facts'); ?></label>
											</div>
											<div class="cell large-9">
												<select id="NEW_INDI_FACTS_ADD" data-placeholder="Select facts..." multiple class="chosen_select" name="NEW_INDI_FACTS_ADD[]">
													<?php $allIndiTags = explode(",", get_gedcom_setting($gedID, 'INDI_FACTS_ADD'));
													foreach (KT_Gedcom_Tag::getPicklistFacts('ALL') as $factId => $factName) {
														$selected = in_array($factId, $allIndiTags) ? ' selected=selected ' : ' ';
														echo '<option' . $selected . 'value="' . $factId . '">' . $factName . '&nbsp;(' . $factId . ')&nbsp;</option>';
													} ?>
												</select>
												<div class="cell callout info-help ">
													<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can add to individuals. You can modify this list by removing or adding fact names, even custom ones, as necessary. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <b>Unique individual facts</b> list.</span>'); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
							</ul>

							<?php echo singleButton(); ?>

						</div>
					</form>
				</div>

				<!-- Theme tab -->
				<div class="tabs-panel" id="theme">
					<form method="post" name="configform-hide" action="<?php echo KT_SCRIPT_NAME . '#theme'; ?>" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-theme">
						<div class="grid-x grid-margin-x grid-margin-y">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<?php $current_themedir = get_gedcom_setting($gedID, 'THEME_DIR');
							foreach (get_theme_names() as $themename => $themedir) {
								$selectClass = ($current_themedir == $themedir ? 'current_theme' : ''); ?>
								<div class="cell card large-3 theme_box <?php echo $selectClass; ?>">
									<div class="card-divider">
										<p class="h5"><?php echo get_theme_display($themename); ?></p>
										<p class="select text-right">
											<input
												type="radio"
												id="radio_<?php echo $themedir; ?>"
												name="NEW_THEME_DIR"
												value="<?php echo $themedir; ?>"
												<?php echo ($current_themedir == $themedir ? ' checked="checked"' : ''); ?>
												data-tooltip
												title="<?php echo ($current_themedir == $themedir ? KT_I18N::translate('Current theme') : KT_I18N::translate('Click here to select this theme')); ?>"
											>
										</p>
									</div>
									<img src="themes/<?php echo $themedir; ?>/images/screenshot_<?php echo $themedir; ?>.png" alt="<?php echo $themename; ?>" title="<?php echo $themename; ?>">
									<div class="card-section">
										<h5><?php echo KT_I18N::translate('Customized files'); ?></h5>
											<?php $html = '';
											foreach ($customFiles as $file) {
												$path = KT_ROOT . KT_THEMES_DIR . $themedir . '/' . $file;
												if (file_exists($path)) {
													$html .= '<p>' . $file . '</p>';
												}
											}
											$html === '' ? $html = KT_I18N::translate('No customizations') : $html = $html; ?>
										<p><?php echo $html; ?></p>
									</div>
								</div>

							<?php } ?>

						</div>
						<div class="grid-x grid-margin-x grid-margin-y">
							<div class="cell">
								<?php echo singleButton(); ?>
							</div>
						</div>

					</form>
				</div>

			</div>
		</div>
	</div>
</div>
