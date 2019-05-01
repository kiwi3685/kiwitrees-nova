<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_trees_config.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Family tree configuration'))
	->addInlineJavascript('
/*
		if(jQuery("#theme input:radio[id=radio_colors]").is(":checked")) {
			jQuery("#colors_palette").show();
		} else {
			jQuery("#colors_palette").hide();
		}
		jQuery("#theme input:radio[id^=radio_]").click(function(){
			var div = "#radio_" + jQuery(this).val();
			if (div == "#radio_colors") {
				jQuery("#colors_palette").show();
			} else {
				jQuery("#colors_palette").hide();
			}
		});
		jQuery(function() {
			jQuery("div.config_options:odd").addClass("odd");
			jQuery("div.config_options:even").addClass("even");
		});
*/
	');

$PRIVACY_CONSTANTS = array(
	'none'			=> KT_I18N::translate('Show to visitors'),
	'privacy'		=> KT_I18N::translate('Show to members'),
	'confidential'	=> KT_I18N::translate('Show to managers'),
	'hidden'		=> KT_I18N::translate('Hide from everyone')
);

$privacy = array(
	KT_PRIV_PUBLIC => KT_I18N::translate('Show to visitors'),
	KT_PRIV_USER   => KT_I18N::translate('Show to members'),
	KT_PRIV_NONE   => KT_I18N::translate('Show to managers'),
	KT_PRIV_HIDE   => KT_I18N::translate('Hide from everyone')
);

// List custom theme files that might exist
$custom_files = array(
		'mystyle.css',
		'mytheme.php',
		'myheader.php',
		'myfooter.php'
	);

// Set active tab based on view parameter from url
$view = KT_Filter::get('view');
switch ($view) {
	case 'file-options':	$active = 0; break;
	case 'contact':			$active = 1; break;
	case 'website':			$active = 2; break;
	case 'privacy':			$active = 3; break;
	case 'config-media':	$active = 4; break;
	case 'layout-options':	$active = 5; break;
	case 'hide-show':		$active = 6; break;
	case 'edit-options':	$active = 7; break;
	case 'theme':			$active = 8; break;
	default:				$active = 0; break;
}

switch (KT_Filter::post('action')) {
	case 'delete':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		KT_DB::prepare(
			"DELETE FROM `##default_resn` WHERE default_resn_id=?"
		)->execute(array(KT_Filter::post('default_resn_id')));
		// Reload the page, so that the new privacy restrictions are reflected in the header
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?view=privacy');
		exit;
	case 'add':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		if ((KT_Filter::post('xref') || KT_Filter::post('tag_type')) && KT_Filter::post('resn')) {
			if (KT_Filter::post('xref') === '') {
				KT_DB::prepare(
					"DELETE FROM `##default_resn` WHERE gedcom_id=? AND tag_type=? AND xref IS NULL"
				)->execute(array(KT_GED_ID, KT_Filter::post('tag_type')));
			}
			if (KT_Filter::post('tag_type') === '') {
				KT_DB::prepare(
					"DELETE FROM `##default_resn` WHERE gedcom_id=? AND xref=? AND tag_type IS NULL"
				)->execute(array(KT_GED_ID, KT_Filter::post('xref')));
			}
			KT_DB::prepare(
				"REPLACE INTO `##default_resn` (gedcom_id, xref, tag_type, resn) VALUES (?, NULLIF(?, ''), NULLIF(?, ''), ?)"
			)->execute(array(KT_GED_ID, KT_Filter::post('xref'), KT_Filter::post('tag_type'), KT_Filter::post('resn')));
		}
		// Reload the page, so that the new privacy restrictions are reflected in the header
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?view=privacy');
		exit;
	case 'update-general':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		if (KT_Filter::post('gedcom_title')) {
			set_gedcom_setting(KT_GED_ID, 'title', KT_Filter::post('gedcom_title'));
		}
		set_gedcom_setting(KT_GED_ID, 'subtitle',						KT_Filter::post('new_subtitle', KT_REGEX_UNSAFE));
		$gedcom = KT_Filter::post('gedcom');
		if ($gedcom && $gedcom != KT_GEDCOM) {
			try {
				KT_DB::prepare("UPDATE `##gedcom` SET gedcom_name = ? WHERE gedcom_id = ?")->execute(array($gedcom, KT_GED_ID));
				KT_DB::prepare("UPDATE `##site_setting` SET setting_value = ? WHERE setting_name='DEFAULT_GEDCOM' AND setting_value = ?")->execute(array($gedcom, KT_GEDCOM));
			} catch (Exception $ex) {
				// Probably a duplicate name.
				$gedcom = KT_GEDCOM;
			}
		}
		set_gedcom_setting(KT_GED_ID, 'LANGUAGE',						KT_Filter::post('GEDCOMLANG'));
		set_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID',				KT_Filter::post('NEW_PEDIGREE_ROOT_ID', KT_REGEX_XREF));
		// For backwards compatibility with we store the two calendar formats in one variable
		// e.g. "gregorian_and_jewish"
		set_gedcom_setting(KT_GED_ID, 'CALENDAR_FORMAT', implode('_and_', array_unique(array(
			KT_Filter::post('NEW_CALENDAR_FORMAT0', 'gregorian|julian|french|jewish|hijri|jalali', 'none'),
			KT_Filter::post('NEW_CALENDAR_FORMAT1', 'gregorian|julian|french|jewish|hijri|jalali', 'none')
		))));
		set_gedcom_setting(KT_GED_ID, 'USE_RIN',						KT_Filter::postBool('NEW_USE_RIN'));
		set_gedcom_setting(KT_GED_ID, 'GENERATE_UIDS',					KT_Filter::postBool('NEW_GENERATE_UIDS'));
		set_gedcom_setting(KT_GED_ID, 'GEDCOM_ID_PREFIX',				KT_Filter::post('NEW_GEDCOM_ID_PREFIX'));
		set_gedcom_setting(KT_GED_ID, 'FAM_ID_PREFIX',					KT_Filter::post('NEW_FAM_ID_PREFIX'));
		set_gedcom_setting(KT_GED_ID, 'SOURCE_ID_PREFIX',				KT_Filter::post('NEW_SOURCE_ID_PREFIX'));
		set_gedcom_setting(KT_GED_ID, 'REPO_ID_PREFIX',					KT_Filter::post('NEW_REPO_ID_PREFIX'));
		set_gedcom_setting(KT_GED_ID, 'MEDIA_ID_PREFIX',				KT_Filter::post('NEW_MEDIA_ID_PREFIX'));
		set_gedcom_setting(KT_GED_ID, 'NOTE_ID_PREFIX',					KT_Filter::post('NEW_NOTE_ID_PREFIX'));

		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?ged=' . $gedcom);
		exit;
	case 'update-contact':
		if (!KT_Filter::checkCsrf()) {
			break;
		}



		set_gedcom_setting(KT_GED_ID, 'ABBREVIATE_CHART_LABELS',		KT_Filter::postBool('NEW_ABBREVIATE_CHART_LABELS'));
		set_gedcom_setting(KT_GED_ID, 'ADVANCED_NAME_FACTS',			KT_Filter::post('NEW_ADVANCED_NAME_FACTS'));
		set_gedcom_setting(KT_GED_ID, 'ADVANCED_PLAC_FACTS',			KT_Filter::post('NEW_ADVANCED_PLAC_FACTS'));
		set_gedcom_setting(KT_GED_ID, 'ALL_CAPS',						KT_Filter::postBool('NEW_ALL_CAPS'));
		set_gedcom_setting(KT_GED_ID, 'CHART_BOX_TAGS',					KT_Filter::post('NEW_CHART_BOX_TAGS'));
		set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_ADD',				str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_ADD')));
		set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_REMOVE',			str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_REMOVE')));
		set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_THRESHOLD',			KT_Filter::post('NEW_COMMON_NAMES_THRESHOLD', KT_REGEX_INTEGER, 40));
		set_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID',				KT_Filter::post('NEW_CONTACT_USER_ID'));
		set_gedcom_setting(KT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS',	KT_Filter::post('NEW_DEFAULT_PEDIGREE_GENERATIONS'));
		set_gedcom_setting(KT_GED_ID, 'EXPAND_NOTES',					KT_Filter::postBool('NEW_EXPAND_NOTES'));
		set_gedcom_setting(KT_GED_ID, 'EXPAND_SOURCES',					KT_Filter::postBool('NEW_EXPAND_SOURCES'));
		set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_ADD')));
		set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_QUICK')));
		set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_UNIQUE')));
		set_gedcom_setting(KT_GED_ID, 'FULL_SOURCES',					KT_Filter::postBool('NEW_FULL_SOURCES'));
		set_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH',				KT_Filter::post('NEW_GEDCOM_MEDIA_PATH'));
		set_gedcom_setting(KT_GED_ID, 'HIDE_GEDCOM_ERRORS',				KT_Filter::postBool('NEW_HIDE_GEDCOM_ERRORS'));
		set_gedcom_setting(KT_GED_ID, 'HIDE_LIVE_PEOPLE',				KT_Filter::postBool('NEW_HIDE_LIVE_PEOPLE'));
		set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_ADD')));
		set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_QUICK')));
		set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_UNIQUE')));
		set_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_BIRTH',			KT_Filter::post('KEEP_ALIVE_YEARS_BIRTH', KT_REGEX_INTEGER, 0));
		set_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_DEATH',			KT_Filter::post('KEEP_ALIVE_YEARS_DEATH', KT_REGEX_INTEGER, 0));
		set_gedcom_setting(KT_GED_ID, 'KIWITREES_EMAIL',				KT_Filter::post('NEW_KIWITREES_EMAIL'));
		set_gedcom_setting(KT_GED_ID, 'MAX_ALIVE_AGE',					KT_Filter::post('MAX_ALIVE_AGE', KT_REGEX_INTEGER, 100));
		set_gedcom_setting(KT_GED_ID, 'MAX_DESCENDANCY_GENERATIONS',	KT_Filter::post('NEW_MAX_DESCENDANCY_GENERATIONS'));
		set_gedcom_setting(KT_GED_ID, 'MAX_PEDIGREE_GENERATIONS',		KT_Filter::post('NEW_MAX_PEDIGREE_GENERATIONS'));
		set_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD',					KT_Filter::post('NEW_MEDIA_UPLOAD'));
		set_gedcom_setting(KT_GED_ID, 'META_DESCRIPTION',				KT_Filter::post('NEW_META_DESCRIPTION'));
		set_gedcom_setting(KT_GED_ID, 'META_TITLE',						KT_Filter::post('NEW_META_TITLE'));
		set_gedcom_setting(KT_GED_ID, 'NO_UPDATE_CHAN',					KT_Filter::postBool('NEW_NO_UPDATE_CHAN'));
		set_gedcom_setting(KT_GED_ID, 'PEDIGREE_FULL_DETAILS',			KT_Filter::postBool('NEW_PEDIGREE_FULL_DETAILS'));
		set_gedcom_setting(KT_GED_ID, 'PEDIGREE_LAYOUT',				KT_Filter::postBool('NEW_PEDIGREE_LAYOUT'));
		set_gedcom_setting(KT_GED_ID, 'PEDIGREE_SHOW_GENDER',			KT_Filter::postBool('NEW_PEDIGREE_SHOW_GENDER'));
		set_gedcom_setting(KT_GED_ID, 'PREFER_LEVEL2_SOURCES',			KT_Filter::post('NEW_PREFER_LEVEL2_SOURCES'));
		set_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FACTS',			KT_Filter::post('NEW_QUICK_REQUIRED_FACTS'));
		set_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FAMFACTS',		KT_Filter::post('NEW_QUICK_REQUIRED_FAMFACTS'));
		set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_ADD')));
		set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_QUICK')));
		set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_UNIQUE')));
		set_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_IMAGE',			KT_Filter::postBool('NEW_SAVE_WATERMARK_IMAGE'));
		set_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_THUMB',			KT_Filter::postBool('NEW_SAVE_WATERMARK_THUMB'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_COUNTER',					KT_Filter::postBool('NEW_SHOW_COUNTER'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_DEAD_PEOPLE',				KT_Filter::post('SHOW_DEAD_PEOPLE'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES',			KT_Filter::postBool('NEW_SHOW_EST_LIST_DATES'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_FACT_ICONS',				KT_Filter::postBool('NEW_SHOW_FACT_ICONS'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_GEDCOM_RECORD',				KT_Filter::postBool('NEW_SHOW_GEDCOM_RECORD'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_HIGHLIGHT_IMAGES',			KT_Filter::postBool('NEW_SHOW_HIGHLIGHT_IMAGES'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_LAST_CHANGE',				KT_Filter::postBool('NEW_SHOW_LAST_CHANGE'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_LDS_AT_GLANCE',				KT_Filter::postBool('NEW_SHOW_LDS_AT_GLANCE'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_LIVING_NAMES',				KT_Filter::post('SHOW_LIVING_NAMES'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_MEDIA_DOWNLOAD',			KT_Filter::postBool('NEW_SHOW_MEDIA_DOWNLOAD'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_NO_WATERMARK',				KT_Filter::post('NEW_SHOW_NO_WATERMARK'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_PARENTS_AGE',				KT_Filter::postBool('NEW_SHOW_PARENTS_AGE'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES',			KT_Filter::post('NEW_SHOW_PEDIGREE_PLACES'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES_SUFFIX',	KT_Filter::postBool('NEW_SHOW_PEDIGREE_PLACES_SUFFIX'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_PRIVATE_RELATIONSHIPS',		KT_Filter::post('SHOW_PRIVATE_RELATIONSHIPS'));
		set_gedcom_setting(KT_GED_ID, 'SHOW_RELATIVES_EVENTS',			KT_Filter::post('NEW_SHOW_RELATIVES_EVENTS'));
		set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_ADD')));
		set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_QUICK')));
		set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_UNIQUE')));
		set_gedcom_setting(KT_GED_ID, 'SUBLIST_TRIGGER_I',				KT_Filter::post('NEW_SUBLIST_TRIGGER_I', KT_REGEX_INTEGER, 200));
		set_gedcom_setting(KT_GED_ID, 'SURNAME_LIST_STYLE',				KT_Filter::post('NEW_SURNAME_LIST_STYLE'));
		set_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION',				KT_Filter::post('NEW_SURNAME_TRADITION'));
		set_gedcom_setting(KT_GED_ID, 'THEME_DIR',						KT_Filter::post('NEW_THEME_DIR'));
		set_gedcom_setting(KT_GED_ID, 'COLOR_PALETTE',					KT_Filter::post('NEW_COLOR_PALETTE'));
		set_gedcom_setting(KT_GED_ID, 'THUMBNAIL_WIDTH',				KT_Filter::post('NEW_THUMBNAIL_WIDTH'));
		set_gedcom_setting(KT_GED_ID, 'USE_GEONAMES',					KT_Filter::postBool('NEW_USE_GEONAMES'));
		set_gedcom_setting(KT_GED_ID, 'USE_SILHOUETTE',					KT_Filter::postBool('NEW_USE_SILHOUETTE'));
		set_gedcom_setting(KT_GED_ID, 'WATERMARK_THUMB',				KT_Filter::postBool('NEW_WATERMARK_THUMB'));
		set_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID',				KT_Filter::post('NEW_WEBMASTER_USER_ID'));

		// Only accept valid folders for NEW_MEDIA_DIRECTORY
		$NEW_MEDIA_DIRECTORY = preg_replace('/[\/\\\\]+/', '/', KT_Filter::post('NEW_MEDIA_DIRECTORY') . '/');
		if (substr($NEW_MEDIA_DIRECTORY, 0, 1) == '/') {
			$NEW_MEDIA_DIRECTORY = substr($NEW_MEDIA_DIRECTORY, 1);
		}

		if ($NEW_MEDIA_DIRECTORY) {
			if (is_dir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY)) {
				set_gedcom_setting(KT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
			} elseif (@mkdir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY, 0755, true)) {
				set_gedcom_setting(KT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
			}
		}


		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?ged=' . $gedcom);
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');
//	->addInlineJavascript('
/*
	 	// run test on initial page load
		 checkSize();
		 // run test on resize of the window
		 jQuery(window).resize(checkSize);
		//Function to the css rule
		function checkSize(){
			 if (jQuery("h3.accordion").css("display") == "block" ){
				jQuery("#accordion").accordion({event: "click", collapsible: true, heightStyle: "content"});
			 } else {
				jQuery("#tabs").tabs({ active: ' . $active . ' });
			}
		}
		if(jQuery("input[name=\'NEW_WATERMARK_THUMB\']:checked").val() != 1){
			 jQuery("#watermarks").hide()
		 }
		 jQuery("input[name=\'NEW_WATERMARK_THUMB\']").on("change",function(){
			 var showOrHide = (jQuery(this).val() == 1) ? true : false;
			 jQuery("#watermarks").toggle(showOrHide);
		 })
*/
//	');
global $iconstyles;
?>

<div id="family_tree_config" class="cell">
	<div class="grid-x grid-margin-y">
		<div class="cell">
			<h4 class="inline"><?php echo KT_I18N::translate('Family tree configuration'); ?></h4>
			<?php echo faqLink('administration/family_tree_config'); ?>
			<h5><?php echo $tree->tree_title_html; ?></h5>
		</div>
		<div class="cell">
			<ul id="tree_config_tabs" class="tabs" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" data-deep-link="true">
				<li class="tabs-title is-active">
					<a href="#general" aria-selected="true"><?php echo KT_I18N::translate('General'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#contact"><?php echo KT_I18N::translate('Contact information'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#website"><?php echo KT_I18N::translate('Metadata'); ?></a>
				</li>
				<li class="tabs-title">
					<a href="#privacy"><?php echo KT_I18N::translate('Privacy'); ?></a>
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
					<form method="post" id="configform" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#general" data-abide novalidate>
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
								<input type="text" id="tree_title" name="gedcom_title" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'title')); ?>" required maxlength="255">
							</div>
							<div class="cell large-3">
								<label for="tree_subtitle"><?php echo KT_I18N::translate('Family tree subtitle'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_subtitle" name="new_subtitle"value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'subtitle')); ?>" maxlength="255">
							</div>
							<div class="cell large-3">
								<label for="tree_url"><?php echo KT_I18N::translate('Website URL'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="input-group">
									<span class="input-group-label"><?php echo KT_SERVER_NAME, KT_SCRIPT_PATH ?>index.php?ged=</span>
									<input class="input-group-field" id="tree_url" type="text" name="gedcom" value="<?php echo KT_Filter::escapeHtml(KT_GEDCOM); ?>" required maxlength="255">
								</div>
								<div class="cell helpcontent">
									<?php /*I18N: Help text for family tree URL */ echo KT_I18N::translate('Avoid spaces and punctuation. A family name might be a good choice.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_lang"><?php echo KT_I18N::translate('Language'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_language('GEDCOMLANG', $LANGUAGE); ?>
								<div class="cell helpcontent">
									<?php echo KT_I18N::translate('If a visitor to the site has not specified a preferred language in their browser configuration, or they have specified an unsupported language, then this language will be used. Typically, this setting applies to search engines.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="autocompleteInput"><?php echo KT_I18N::translate('Default individual'); ?></label>
							</div>
							<div class="cell large-9">
								<div class="input-group autocomplete_container">
									<?php $person = KT_Person::getInstance(get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID')); ?>
									<input data-autocomplete-type="INDI" type="text" id="autocompleteInput" value="<?php echo strip_tags($person->getLifespanName()); ?>">
									<span class="input-group-label">
										<button class="clearAutocomplete autocomplete_icon">
											<i class="<?php echo $iconStyle; ?> fa-times"></i>
										</button>
									</span>
								</div>
								<input type="hidden" id="selectedValue" name="rootid">
								<div class="cell helpcontent">
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
									<div class="cell helpcontent">
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
								<?php echo edit_field_yes_no('NEW_USE_RIN', get_gedcom_setting(KT_GED_ID, 'USE_RIN')); ?>
								<div class="cell helpcontent space">
									<?php echo KT_I18N::translate('Set to <b>Yes</b> to use the RIN number instead of the GEDCOM ID when asked for Individual IDs in configuration files, user settings, and charts. This is useful for genealogy programs that do not consistently export GEDCOMs with the same ID assigned to each individual but always use the same RIN.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="tree_subtitle"><?php echo KT_I18N::translate('Automatically create globally unique IDs'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_yes_no('NEW_GENERATE_UIDS', get_gedcom_setting(KT_GED_ID, 'GENERATE_UIDS')); ?>
								<div class="cell helpcontent space">
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
									<div class="cell helpcontent">
										<?php echo KT_I18N::translate('In a family tree, each record has an internal reference number (called an “XREF”) such as “F123” or “R14”.	You can choose the prefix that will be used whenever <b>new</b> XREFs are created.'); ?>
									</div>
								</div>
							</div>
						</div>
						<button type="submit" class="button">
							<i class="<?php echo $iconStyle; ?> fa-save"></i>
							<?php echo KT_I18N::translate('Save'); ?>
						</button>
					</form>
				</div>
				<!-- Contact tab -->
				<div class="tabs-panel is-active" id="contact">
					<form method="post" id="configform" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#contact" data-abide novalidate>
						<?php echo KT_Filter::getCsrf(); ?>
						<input type="hidden" name="action" value="update-contact">
						<div class="grid-x grid-margin-x">
							<div data-abide-error class="alert callout" style="display: none;">
								<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
							</div>
							<div class="cell large-3">
								<label for="tree_title"><?php echo KT_I18N::translate('Family tree title'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="tree_title" name="gedcom_title" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'title')); ?>" required maxlength="255">
							</div>
						</div>
					</form>
				</div>




			</div>
		</div>
	</div>
</div>
