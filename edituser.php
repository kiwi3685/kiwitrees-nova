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

define('KT_SCRIPT_NAME', 'edituser.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_print_lists.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

// Extract form variables
$form_action         = KT_Filter::post('form_action');
$form_username       = KT_Filter::post('form_username', KT_REGEX_USERNAME);
$form_realname       = KT_Filter::post('form_realname' );
$form_pass1          = KT_Filter::post('form_pass1', KT_REGEX_PASSWORD);
$form_pass2          = KT_Filter::post('form_pass2', KT_REGEX_PASSWORD);
$form_email          = KT_Filter::post('form_email', KT_REGEX_EMAIL, 'email@example.com');
$form_rootid         = KT_Filter::post('form_rootid', KT_REGEX_XREF, KT_USER_ROOT_ID   );
$form_language       = KT_Filter::post('form_language', array_keys(KT_I18N::used_languages()), KT_LOCALE );
$form_contact_method = KT_Filter::post('form_contact_method');
$form_visible_online = KT_Filter::postBool('form_visible_online');

// Respond to form action
if ($form_action && KT_Filter::checkCsrf()) {
	switch ($form_action) {
		case 'update':
			if ($form_username != KT_USER_NAME && get_user_id($form_username)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate user name.  A user with that user name already exists.  Please choose another user name.'));
			} elseif ($form_email!=getUserEmail(KT_USER_ID) && findByEmail($form_email)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address.  A user with that email already exists.'));
			} else {
				// Change username
				if ($form_username != KT_USER_NAME) {
					AddToLog('User renamed to ->'  .$form_username . '<-', 'auth');
					rename_user(KT_USER_ID, $form_username);
				}

				// Change password
				if ($form_pass1 && $form_pass1 == $form_pass2) {
					set_user_password(KT_USER_ID, $form_pass1);
				}

				// Change other settings
				setUserFullName(KT_USER_ID, $form_realname);
				setUserEmail   (KT_USER_ID, $form_email);
				set_user_setting(KT_USER_ID, 'language',       $form_language);
				set_user_setting(KT_USER_ID, 'contactmethod',  $form_contact_method);
				set_user_setting(KT_USER_ID, 'visibleonline',  $form_visible_online);
				$KT_TREE->userPreference(KT_USER_ID, 'rootid', $form_rootid);
			}
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
		break;

		case 'delete':
			// An administrator can only be deleted by another administrator
			if (!KT_USER_IS_ADMIN) {
				userLogout(KT_USER_ID);
				delete_user(KT_USER_ID);
			}
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
		break;
	}

	return;
}

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('User administration'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		display_help(false);
	');

// Form validation
?>
<script>
function checkform(frm) {
	if (frm.form_username.value=="") {
		alert("<?php echo KT_I18N::translate('You must enter a user name.'); ?>");
		frm.form_username.focus();
		return false;
	}
	if (frm.form_realname.value=="") {
		alert("<?php echo KT_I18N::translate('You must enter a real name.'); ?>");
		frm.form_realname.focus();
		return false;
	}
	if (frm.form_pass1.value!=frm.form_pass2.value) {
		alert("<?php echo KT_I18N::translate('Passwords do not match.'); ?>");
		frm.form_pass1.focus();
		return false;
	}
	if (frm.form_pass1.value.length > 0 && frm.form_pass1.value.length < 6) {
		alert("<?php echo KT_I18N::translate('Passwords must contain at least 6 characters.'); ?>");
		frm.form_pass1.focus();
		return false;
	}
	return true;
}
</script>

<div id="edituser-page" class="grid-x grid-padding-x">
	<div class="cell large-6 large-offset-3" id="research_links-page">
		<h3><?php echo KT_I18N::translate('My account'); ?></h3>
		<form name="editform" method="post" action="" onsubmit="return checkform(this);">
			<input type="hidden" id="form_action" name="form_action" value="update">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="grid-x grid-margin-x grid-margin-y">
				<div class="cell medium-3">
					<label for="form_username" class="text-left middle"><?php echo KT_I18N::translate('Username'); ?></label>
				</div>
				<div class="cell medium-9">
					<input type="text" id="form_username" name="form_username" value="<?php echo KT_USER_NAME; ?>" autofocus required>
					<span id="username" class="help-text"></span>
				</div>
				<div class="cell medium-3">
					<label for="form_realname" class="text-left middle"><?php echo KT_I18N::translate('Real name'); ?></label>
				</div>
				<div class="cell medium-9">
					<input type="text" id="form_realname" name="form_realname" value="<?php echo getUserFullName(KT_USER_ID); ?>">
					<span id="real_name" class="help-text"></span>
				</div>
				<?php $person = KT_Person::getInstance(KT_USER_GEDCOM_ID); ?>
				<div class="cell medium-3">
					<label for="gedcom_user" class="text-left middle"><?php echo KT_I18N::translate('Individual record'); ?></label>
				</div>
				<div class="cell medium-9">
					<?php if ($person) { ?>
						<div id="gedcom_user"><?php echo $person->format_list('span'); ?></div>
					<?php } else { ?>
						<div id="gedcom_user"><?php echo KT_I18N::translate('Unknown'); ?></div>
					<?php } ?>
					<span id="edituser_gedcomid" class="help-text"></span>
				</div>
				<?php $person = KT_Person::getInstance(KT_USER_ROOT_ID); ?>
				<div class="cell medium-3">
					<label for="rootid" class="text-left middle"><?php echo KT_I18N::translate('Default individual'); ?></label>
				</div>
				<div class="cell medium-9">
					<input data-autocomplete-type="INDI" type="text" name="form_rootid" id="rootid" value="<?php echo KT_USER_ROOT_ID; ?>">
						<?php echo print_findindi_link('rootid'); ?>
						<br>
						<?php if ($person) {
							echo $person->format_list('span');
						} ?>
					<span id="default_individual" class="help-text"></span>
				</div>
				<div class="cell medium-3">
					<label for="form_password" class="text-left middle"><?php echo KT_I18N::translate('Password'); ?></label>
				</div>
				<div class="cell medium-9">
					<input id="form_password" type="password" name="form_pass1">
					<span id="password" class="help-text"></span>
				</div>
				<div class="cell medium-3">
					<label for="form_password2" class="text-left middle"><?php echo KT_I18N::translate('Confirm password'); ?></label>
				</div>
				<div class="cell medium-9">
					<input id="form_password2" type="password" name="form_pass2">
					<span id="password_confirm" class="help-text"></span>
				</div>
				<div class="cell medium-3">
					<label for="form_language" class="text-left middle"><?php echo KT_I18N::translate('Language'); ?></label>
				</div>
				<div class="cell medium-9">
					<select id="form_language" name="form_language">
						<?php foreach (KT_I18N::used_languages() as $code=>$name) { ?>
							<option value="<?php echo $code; ?>" dir="auto" <?php echo get_user_setting(KT_USER_ID, 'language') === $code ? 'selected' : ''; ?>>
								<?php echo KT_I18N::translate($name); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="cell medium-3">
					<label for="form_email" class="text-left middle"><?php echo KT_I18N::translate('Email address'); ?></label>
				</div>
				<div class="cell medium-9">
					<input id="form_email" type="email" name="form_email" value="<?php echo getUserEmail(KT_USER_ID); ?>">
					<span id="email" class="help-text"></span>
				</div>

				<div class="cell medium-3">
					<label for="form_contact_method" class="text-left middle"><?php echo KT_I18N::translate('Preferred contact method'); ?></label>
				</div>
				<div class="cell medium-9">
					<div><?php echo edit_field_contact('form_contact_method', get_user_setting(KT_USER_ID, 'contactmethod')); ?></div>
					<span id="edituser_contact_meth_short" class="help-text"></span>
				</div>
				<button class="button" type="submit" value="<?php echo KT_I18N::translate('Save') ?>">
					<i class="fas fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</div>
		</form>
		<?php if (!KT_USER_IS_ADMIN) { ?>
			<button class="button" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', KT_USER_NAME)); ?>')) {jQuery('#form_action').val('delete'); document.editform.submit(); }">
				<i class="fas fa-trash-alt"></i>
		<?php echo KT_I18N::translate('Delete your account'); ?>
			</button>
		<?php } ?>
	</div>
</div>
<?php
