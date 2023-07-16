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

define('KT_SCRIPT_NAME', 'edituser.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_print_lists.php';
require KT_ROOT . 'includes/functions/functions_edit.php';


// Extract form variables
$form_action         = KT_Filter::post('form_action');
$form_username       = KT_Filter::post('form_username', KT_REGEX_USERNAME);
$form_realname       = KT_Filter::post('form_realname' );
$password            = KT_Filter::post('password', KT_REGEX_PASSWORD);
$form_email          = KT_Filter::post('form_email', KT_REGEX_EMAIL, 'email@example.com');
$form_rootid         = KT_Filter::post('form_rootid', KT_REGEX_XREF, KT_USER_ROOT_ID);
$form_language       = KT_Filter::post('form_language', implode(", ", array_keys(KT_I18N::used_languages())), KT_LOCALE);
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

				// Change other settings
				setUserFullName(KT_USER_ID, $form_realname);
				setUserEmail   (KT_USER_ID, $form_email);
				set_user_password(KT_USER_ID, $password);
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
 global $iconStyle;
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
	if (frm.password.value.length > 0 && frm.password.value.length < 6) {
		alert("<?php echo KT_I18N::translate('Passwords must contain at least 6 characters.'); ?>");
		frm.password.focus();
		return false;
	}
	return true;
}
</script>

<div id="edituser-page" class="grid-x">
	<div class="cell medium-10 medium-offset-1">
		<h3><?php echo KT_I18N::translate('My account'); ?></h3>
		<form name="editform" method="post" action="" onsubmit="return checkform(this);">
			<div class="grid-x grid-margin-x grid-margin-y">
				<input type="hidden" id="form_action" name="form_action" value="update">
				<?php echo KT_Filter::getCsrf(); ?>

				<!-- REAL NAME -->
				<div class="cell large-3">
					<label for="form_realname" class="middle">
						<?php echo KT_I18N::translate('Real name'); ?>
					</label>
				</div>
				<div class="cell large-9">
					<div class="input_group">
						<input type="text" id="form_realname" name="form_realname" value="<?php echo getUserFullName(KT_USER_ID); ?>">
						<div class="callout info-help">
							<?php echo KT_I18N::translate('This is your real name, as you would like it displayed on screen.'); ?>
						</div>
					</div>
				</div>

				<!-- USER NAME -->
				<div class="cell medium-3">
					<label for="form_username" class="middle"><?php echo KT_I18N::translate('Username'); ?></label>
				</div>
				<div class="cell medium-9">
					<input type="text" id="form_username" name="form_username" value="<?php echo KT_USER_NAME; ?>" autofocus required>
					<div class="callout info-help">
						<?php echo KT_I18N::translate('Usernames are case-insensitive and ignore accented letters, so that “chloe”, “chloë”, and “Chloe” are considered to be the same.'); ?>
					</div>
				</div>

				<!-- PASSWORD -->
				<div class="cell large-3">
					<label for="password" class="middle">
						<?php echo KT_I18N::translate('Password'); ?>
					</label>
				</div>
				<div class="cell large-9">
					<div class="input-group">
						<input
							class="input-group-field"
							type="password"
							id="password"
							name="password"
							<?php if (KT_USER_ID) { ?>
								placeholder="<?php echo KT_I18N::translate('Leave password blank if you want to keep the current password.'); ?>"
							<?php } ?>
							<?php echo KT_USER_ID ? '' : 'required'; ?>
							autocomplete="off"
						>
						<span class="input-group-label unmask" title="<?php echo KT_I18N::translate('Show/Hide password to check content'); ?>">
							<i class="close-eye <?php echo $iconStyle; ?> fa-eye"></i>
						</span>
						<span id="result" class="input_label right">&nbsp;</span>
					</div>
					<div class="grid-x" id="password-checker">
						<div class="cell progress">
							<div
								id="password-strength"
								class="progress-bar"
								role="progressbar"
								aria-valuenow="40"
								aria-valuemin="0"
								aria-valuemax="100"
								style="width:0%"
							>
							</div>
						</div>
						<?php if (KT_Site::preference('PASSWORD_ALPHA')) { ?>
							<div class="medium-3">
								<i class="low-upper-case <?php echo $iconStyle; ?> fa-caret-right fa-fw" aria-hidden="true"></i>
								<span><?php echo KT_I18N::translate('Lowercase &amp; uppercase'); ?><span>
							</div>
						<?php } ?>
						<?php if (KT_Site::preference('PASSWORD_NUMBERS')) { ?>
							<div class="medium-3">
								<i class="one-number <?php echo $iconStyle; ?> fa-caret-right fa-fw" aria-hidden="true"></i>
							   <span> <?php echo KT_I18N::translate('Number (0-9)'); ?><span>
							</div>
						<?php } ?>
						<?php if (KT_Site::preference('PASSWORD_SPECIAL')) { ?>
							<div class="medium-3">
								<?php $chars = '!@#$%^&*'; ?>
								<i class="one-special-char <?php echo $iconStyle; ?> fa-caret-right fa-fw" aria-hidden="true"></i>
								<span><?php echo KT_I18N::translate('Special character (%s)', $chars); ?><span>
							</div>
						<?php } ?>
						<div class="medium-3 auto">
							<i class="multi-character <?php echo $iconStyle; ?> fa-caret-right fa-fw" aria-hidden="true"></i>
							<span><?php echo KT_I18N::translate('At least %d characters', KT_MINIMUM_PASSWORD_LENGTH); ?><span>
						</div>
					</div>
					<div class="callout info-help">
						<?php if (KT_USER_ID > 0) { ?>
							<?php echo '<b>' . KT_I18N::translate('Leave password blank if you want to keep the current password.') . '</b>'; ?>
							<br>
						<?php } ?>
						<?php echo KT_I18N::translate('
							Passwords are case-sensitive, so that “secret” is different from “SECRET”.
							<br>
							This site requires that all passwords include, AS A MINIMUM, each of the items listed below the password entry field.
							The colored bar (grey until you start typing) denotes the strength of your chosen password.
							<span class="alert strong">Red</span> = weak password;
							<span class="warning strong">Orange</span> = medium;
							<span class="success strong">Green</span> = strong.
							<br>
							Use the "Show or hide password" icon (eye) to check your password before saving it.
							<br>
						', KT_MINIMUM_PASSWORD_LENGTH, KT_MINIMUM_PASSWORD_LENGTH); ?>
					</div>
				</div>

				<!-- EMAIL ADDRESS -->
				<div class="cell large-3">
					<label for="form_email" class="middle">
						<?php echo KT_I18N::translate('Email address'); ?>
					</label>
				</div>
				<div class="cell large-9">
					<div class="input_group">
						<input type="email" id="form_email" name="form_email" required maxlength="64" value="<?php echo getUserEmail(KT_USER_ID); ?>">
						<div class="callout info-help">
							<?php echo KT_I18N::translate('This email address will be used to send password reminders, website notifications, and messages from other family members who are registered on the website.'); ?>
						</div>
					</div>
				</div>

				<!-- LANGUAGE -->
				<div class="cell large-3">
					<label for="form_language" class="middle">
						<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Language'); ?>
					</label>
				</div>
				<div class="cell large-9">
					<div class="input_group">
						<select id="form_language" name="form_language">
							<?php foreach (KT_I18N::used_languages() as $code=>$name) { ?>
								<option value="<?php echo $code; ?>" dir="auto" <?php echo get_user_setting(KT_USER_ID, 'language') === $code ? 'selected' : ''; ?>>
									<?php echo KT_I18N::translate($name); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<!-- CONTACT METHOD -->
				<div class="cell large-3">
					<label for="form_contact_method" class="middle">
						<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Preferred contact method'); ?>
					</label>
				</div>
				<div class="cell large-9">
					<div class="input_group">
						<?php echo edit_field_contact('form_contact_method', get_user_setting(KT_USER_ID, 'contactmethod')); ?>
						<div class="callout info-help">
							<?php echo /* I18N: Help text for the “Preferred contact method” configuration setting */
							KT_I18N::translate('
								Site members can send each other messages.
								Use this setting to choose how these messages are sent to this user,
								or not sent to them at all.
							'); ?>
						</div>
					</div>
				</div>

				<!-- GEDCOM INDI Record ID -->
				<?php $person = KT_Person::getInstance(KT_USER_GEDCOM_ID); ?>
				<div class="cell medium-3">
					<label for="gedcom_user" class="middle">
						<?php echo KT_I18N::translate('Individual record'); ?>
					</label>
				</div>
				<div class="cell medium-9">
					<?php if ($person) { ?>
						<div id="gedcom_user"><?php echo $person->format_list('span'); ?></div>
					<?php } else { ?>
						<div id="gedcom_user"><?php echo KT_I18N::translate('Unknown'); ?></div>
					<?php } ?>
					<div class="callout info-help">
						<?php echo KT_I18N::translate('
							This is a link to your own record in the family tree.
							If this is the wrong person please contact the site administrator.
						'); ?>
					</div>
				</div>

				<!-- PEDIGREE ROOT PERSON -->
				<?php $person = KT_Person::getInstance(KT_USER_ROOT_ID); ?>
				<div class="cell medium-3">
					<label for="rootid" class="middle"><?php echo KT_I18N::translate('Default individual'); ?></label>
				</div>
				<div class="cell medium-9">
					<?php
					if (KT_USER_ROOT_ID) {
						$rootName = strip_tags($person->getLifespanName());
					} else {
						$rootName = '';
					}
					echo autocompleteHtml(
						'rootid',
						'INDI',
						'',
						$rootName,
						KT_I18N::translate('Individual name'),
						'rootid',
						KT_USER_ROOT_ID,
					);?>
					<div class="callout info-help">
						<?php echo KT_I18N::translate('
							This individual will be selected by default when viewing charts and reports.
						'); ?>
					</div>
				</div>

					<?php echo singleButton(); ?>

			</div>
		</form>
		<?php if (!KT_USER_IS_ADMIN) { ?>
			<div class="cell small-offset-3 medium-offset-1 auto">
				<button
					class="button delete secondary"
					onclick="if (confirm('<?php echo htmlspecialchars(
						KT_I18N::translate('
							Are you sure you want to delete “%s”?
							This action cannot be undone.
						', KT_USER_NAME)); ?>')) {jQuery('#form_action').val('delete'); document.editform.submit(); }
					"
				>
					<i class="fas fa-trash-can"></i>
					<?php echo KT_I18N::translate('Delete your account'); ?>
				</button>
			</div>
		<?php } ?>
	</div>
</div>
<?php
