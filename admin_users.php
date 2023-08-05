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

define('KT_SCRIPT_NAME', 'admin_users.php');
require './includes/session.php';
include KT_THEME_URL . 'templates/adminData.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('User administration'));

require_once KT_ROOT.'includes/functions/functions_edit.php';

global $iconStyle;

// Valid values for form variables
$ALL_EDIT_OPTIONS = array(
	'access'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Member'),
	'edit'  => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Editor'),
	'accept'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Moderator'),
	'admin' => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Manager')
);

// convert days to seconds
$days = (KT_Site::preference('VERIFY_DAYS') ? KT_Site::preference('VERIFY_DAYS') : 7);
$time = $days * 60 * 60 * 24;

// Form actions
switch (KT_Filter::post('action')) {
	case 'save':
		if (KT_Filter::checkCsrf()) {
			$user_id			= KT_Filter::postInteger('user_id');
			$username			= KT_Filter::post('username');
			$realname			= KT_Filter::post('real_name');
			$email				= KT_Filter::postEmail('email');
			$password			= KT_Filter::post('password', KT_REGEX_PASSWORD);
			$language			= KT_Filter::post('language');
			$contact_method		= KT_Filter::post('contact_method');
			$comment			= KT_Filter::post('comment');
			$auto_accept		= KT_Filter::postBool('auto_accept');
			$canadmin			= KT_Filter::postBool('canadmin');
			$visible_online		= KT_Filter::postBool('visible_online');
			$verified			= KT_Filter::postBool('verified');
			$verified_by_admin	= KT_Filter::postBool('verified_by_admin');
			$notify_clipping	= KT_Filter::postBool('notify_clipping');

			if ($user_id === 0) {
				// Create a new user
				if (get_user_id($username)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate username. A user with that username already exists. Please choose another username.'));
				} elseif (findByEmail($email)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
				} else {
					$user_id = create_user($username, $realname, $email, $password);
					set_user_setting($user_id, 'reg_timestamp', date('U'));
					set_user_setting($user_id, 'sessiontime', '0');
					AddToLog('User ->' . $username . '<- created', 'auth');
				}
			} else {
				if ($user_id && $username && $realname) {
					setUserFullName ($user_id, $realname);
					setUserName	($user_id, $username);
                    if (findByEmail($email) && findByEmail($email) != $user_id) {
    					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
    				} else {
					    setUserEmail ($user_id, $email);
                    }
					if ($password) {
						set_user_password($user_id, $password);
					}
				}
			}

			if ($user_id > 0) {
				$tree_link = '<a href="' . KT_SERVER_NAME . KT_SCRIPT_PATH . '?ged=' . KT_GEDCOM . '"><strong>' . strip_tags(KT_TREE_TITLE) . '</strong></a>';
				// Approving for the first time? Send a confirmation email
				if ($verified_by_admin && !get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'sessiontime') == 0) {
					KT_I18N::init(get_user_setting($user_id, 'language'));
					KT_Mail::systemMessage(
						$KT_TREE,
						$user_id,
						KT_I18N::translate('Approval of account at %s', strip_tags(KT_TREE_TITLE)),
						KT_I18N::translate('The administrator at %1s has approved your application for an account.<br><br>You may now login by accessing the following link: %2s', strip_tags(KT_TREE_TITLE), $tree_link)
					);
				}

				set_user_setting($user_id, 'language', $language);
				set_user_setting($user_id, 'contactmethod', $contact_method);
				set_user_setting($user_id, 'comment', $comment);
				set_user_setting($user_id, 'auto_accept', $auto_accept);
				set_user_setting($user_id, 'visibleonline', $visible_online);
				set_user_setting($user_id, 'verified', $verified);
				set_user_setting($user_id, 'verified_by_admin',	$verified_by_admin);
				set_user_setting($user_id, 'notify_clipping', $notify_clipping);

				// We cannot change our own admin status. Another admin will need to do it.
				if ($user_id !== KT_USER_ID) {
					set_user_setting($user_id, 'canadmin', $canadmin);
				}

				// Set tree based user settings
				foreach (KT_Tree::getAll() as $tree) {
					$tree->userPreference($user_id, 'rootid', KT_Filter::post('rootid' . $tree->tree_id, KT_REGEX_XREF));
					$tree->userPreference($user_id, 'gedcomid', KT_Filter::post('gedcomid' . $tree->tree_id, KT_REGEX_XREF));
					$tree->userPreference($user_id, 'canedit', KT_Filter::post('canedit' . $tree->tree_id, implode('|', array_keys($ALL_EDIT_OPTIONS))));
					if (KT_Filter::post('gedcomid' . $tree->tree_id, KT_REGEX_XREF)) {
						$tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH', KT_Filter::postInteger('RELATIONSHIP_PATH_LENGTH' . $tree->tree_id, 0, 10, 0));
					} else {
						// Do not allow a path length to be set if the individual ID is not
						$tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH', null);
					}
				}
			}
		}

		if ($user_id > 0) {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME . '?action=edit&user_id=' . $user_id);
		} else {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME);
		}

		return;
}

switch (KT_Filter::get('action')) {

	case 'deleteuser':
		// Delete a user - but don't delete ourselves!
		$username	= KT_Filter::get('username');
		$user_id	= get_user_id($username);
		if ($user_id && $user_id != KT_USER_ID) {
			delete_user($user_id);
			AddToLog("deleted user ->{$username}<-", 'auth');
		}

		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME);

		return;

	case 'masquerade_user':
		// Masquerade as a user.
		$username	= KT_Filter::get('username');
		$user_id	= get_user_id($username);
		$KT_SESSION->kt_user = $user_id;
		Zend_Session::regenerateId();
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'index.php');
		break;

	case 'loadrows':
		$search    = KT_Filter::get('sSearch', '');
		$start     = KT_Filter::getInteger('iDisplayStart');
		$length    = KT_Filter::getInteger('iDisplayLength');
		$isort     = KT_Filter::getInteger('iSortingCols');
		$draw      = KT_Filter::getInteger('sEcho');
		$colsort   = [];
		$sortdir   = [];
		for ($i = 0; $i < $isort; ++$i) {
			$colsort[$i] = KT_Filter::getInteger('iSortCol_' . $i);
			$sortdir[$i] = KT_Filter::get('sSortDir_' . $i);
		}

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode(KT_DataTables_AdminUsers::userList($search, $start, $length, $isort, $draw, $colsort, $sortdir));
		exit;

	case 'edit':
		$user_id	= KT_Filter::getInteger('user_id');
		$username	= get_user_name($user_id);
		$realname	= getUserFullName($user_id);
		$email		= getUserEmail($user_id);

		if ($user_id === 0) {
			$controller->setPageTitle(KT_I18N::translate('Add a new user'));
			$user_id	= '';
			$username	= '';
			$realname	= '';
			$email		= '';
		} else {
			$controller->setPageTitle(KT_I18N::translate('Edit user') . ' - ' . $realname);
		}

		$url = KT_SCRIPT_NAME . '?action=' . KT_Filter::post('page') . '&amp;user_id=' . $user_id;

		$controller
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_PASSWORDSCHECK)
			->addInlineJavascript('
				autocomplete();

				jQuery(".relpath").change(function() {
					var fieldIDx = jQuery(this).attr("id");
					var idNum = fieldIDx.replace("RELATIONSHIP_PATH_LENGTH","");
					var newIDx = "gedcomid"+idNum;
					if (jQuery("#"+newIDx).val() === "" && jQuery("#".fieldIDx).val() !== "0") {
						alert("' . KT_I18N::translate('You must specify an individual record for this user before you can restrict the user to their close family.') . '");
						jQuery(this).val("0");
					}
				});

				// Code for password strength check
				// Based on code from "Coding Beast" on CodePen
				// https://codepen.io/coding_beast/pen/LYGrXde
				// converted to jquery and for fontawesom svg icons
				jQuery("#password").keyup(function() {
					var strength         = 0;
					var password         = jQuery(this).val();
					var passwordStrength = jQuery("#password-strength");
					var lowuppercase     = jQuery(".low-upper-case");
					var number           = jQuery(".one-number");
					var specialChar      = jQuery(".one-special-char");
					var multiChar        = jQuery(".multi-character");

				  //If password contains both lower and uppercase characters
				  if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
					  strength += 1;
					  lowuppercase.attr("data-icon", "check");
				  } else {
					  lowuppercase.attr("data-icon", "caret-right");
				  }

				  //If it has numbers and characters
				  if (password.match(/([0-9])/)) {
					  strength += 1;
					  number.attr("data-icon", "check");
				  } else {
					  number.attr("data-icon", "caret-right");
				  }

				  //If it has one special character
				  if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
					  strength += 1;
					  specialChar.attr("data-icon", "check");
				  } else {
					  specialChar.attr("data-icon", "caret-right");
				  }

				  //If password is greater than configured minimum
				  if (password.length >= ' . KT_MINIMUM_PASSWORD_LENGTH . ') {
					  strength += 1;
					  multiChar.attr("data-icon", "check");
				  } else {
					  multiChar.attr("data-icon", "caret-right");
				  }

				  // If value is less than 2
				  if (strength < 2) {
					  passwordStrength.removeClass("progress-bar-warning");
					  passwordStrength.removeClass("progress-bar-success")
					  passwordStrength.addClass("progress-bar-danger");
					  passwordStrength.css("width", "10%");
				  } else if (strength == 3) {
					  passwordStrength.removeClass("progress-bar-success");
					  passwordStrength.removeClass("progress-bar-danger");
					  passwordStrength.addClass("progress-bar-warning");
					  passwordStrength.css("width", "60%");
				  } else if (strength == 4) {
					  passwordStrength.removeClass("progress-bar-warning");
					  passwordStrength.removeClass("progress-bar-danger");
					  passwordStrength.addClass("progress-bar-success");
					  passwordStrength.css("width", "100%");
				  }

			   });



			');

		echo relatedPages($users);

		echo pageStart('user_details', $controller->getPageTitle()); ?>
			<form class="cell" name="newform" method="post" role="form" autocomplete="off">
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
				<input type="hidden" name="page" value="edit">

				<div class="grid-x grid-margin-x">
					<!-- REAL NAME -->
					<div class="cell large-3">
						<label for="real_name">
							<?php echo KT_I18N::translate('Real name'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<input type="text" id="real_name" name="real_name" required maxlength="64" value="<?php echo KT_Filter::escapeHtml($realname); ?>" dir="auto" autocomplete="off">
							<div class="callout info-help">
								<?php echo KT_I18N::translate('This is your real name, as you would like it displayed on screen.'); ?>
							</div>
						</div>
					</div>

					<!-- USER NAME -->
					<div class="cell large-3">
						<label for="username">
							<?php echo KT_I18N::translate('Username'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<input type="text" id="username" name="username" required maxlength="32" value="<?php echo KT_Filter::escapeHtml($username); ?>" dir="auto" autocomplete="off">
							<div class="callout info-help">
								<?php echo KT_I18N::translate('Usernames are case-insensitive and ignore accented letters, so that “chloe”, “chloë”, and “Chloe” are considered to be the same.'); ?>
							</div>
						</div>
					</div>

					<!-- PASSWORD -->
					<div class="cell large-3">
						<label for="password">
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
								<?php if ($user_id) { ?>
									placeholder="<?php echo KT_I18N::translate('Leave password blank if you want to keep the current password.'); ?>"
								<?php } ?>
								<?php echo $user_id ? '' : 'required'; ?>
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
							<?php if ($user_id > 0) { ?>
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
						<label for="email">
							<?php echo KT_I18N::translate('Email address'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<input type="email" id="email" name="email" required maxlength="64" value="<?php echo KT_Filter::escapeHtml($email); ?>">
							<div class="callout info-help">
								<?php echo KT_I18N::translate('This email address will be used to send password reminders, website notifications, and messages from other family members who are registered on the website.'); ?>
							</div>
						</div>
					</div>

					<!-- EMAIL VERIFIED and ACCOUNT APPROVED -->
					<div class="cell large-3">
						<label for="verified">
							<?php echo KT_I18N::translate('Account approval and verification'); ?>
						</label>
					</div>
					<div class="cell small-9">
						<div class="grid-x">
							<div class="small-6 medium-3">
								<label for"userVerified">
									<?php echo KT_I18N::translate('Email verified'); ?>
									<?php echo simple_switch(
										'verified',
										true,
										get_user_setting($user_id, 'verified'),
										'',
										KT_I18N::translate('Yes'),
			                            KT_I18N::translate('No'),
										'small'
									); ?>
								</label>
							</div>
							<div class="small-6 medium-3">
								<label for"adminVerified">
									<?php echo KT_I18N::translate('Approved by administrator'); ?>
									<?php echo simple_switch(
										'verified_by_admin',
										true,
										get_user_setting($user_id, 'verified_by_admin'),
										'',
										KT_I18N::translate('Yes'),
			                            KT_I18N::translate('No'),
										'small'
									); ?>
								</label>
							</div>
						</div>
						<div class="callout info-help">
							<?php echo KT_I18N::translate('
								When a user registers for an account, an email is sent to their email address with a verification link. When they follow this link, we know the email address is correct, and the “email verified” option is selected automatically.
								<br>
								If an administrator creates a user account, the verification email is not sent, and the email must be verified manually.
								<br>
								You should not approve an account unless you know that the email address is correct.
								<br>
								Approval by an administrator can only be done manually.
								<br>
								A user will not be able to sign in until both “email verified” and “approved by administrator” are selected.
							'); ?>
						</div>
					</div>

					<!-- LANGUAGE -->
					<div class="cell large-3">
						<label for="language">
							<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Language'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<select id="language" name="language">
								<?php foreach (KT_I18N::used_languages() as $code=>$name) { ?>
									<option value="<?php echo $code; ?>" dir="auto" <?php echo get_user_setting($user_id, 'language') === $code ? 'selected' : ''; ?>>
										<?php echo KT_I18N::translate($name); ?>
									</option>
								<?php } ?>
							</select>
						</div>
					</div>

					<!-- AUTO ACCEPT -->
					<div class="cell large-3">
						<label for="auto_accept">
							<?php echo KT_I18N::translate('Accept changes'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="grid-x">
							<?php echo simple_switch(
								'auto_accept',
								true,
								get_user_setting($user_id, 'auto_accept'),
								'',
								KT_I18N::translate('Yes'),
	                            KT_I18N::translate('No'),
								'small'
							); ?>
						</div>
						<div class="callout info-help">
							<?php echo KT_I18N::translate('
								Automatically accept changes and additions to family tree data by this user.
							'); ?>
						</div>
					</div>

					<!-- VISIBLE ONLINE -->
					<div class="cell large-3">
						<label for="visible_online">
							<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Visible online'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="grid-x">
							<?php echo simple_switch(
								'visible_online',
								true,
								get_user_setting($user_id, 'visible_online'),
								'',
								KT_I18N::translate('Yes'),
	                            KT_I18N::translate('No'),
								'small'
							); ?>
						</div>
						<div class="callout info-help">
							<?php /* I18N: A configuration setting */ echo KT_I18N::translate('
								You can choose whether this user appears in the list of users who are currently signed-in.
							'); ?>
						</div>
					</div>

					<!-- CONTACT METHOD -->
					<div class="cell large-3">
						<label for="contactmethod">
							<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Preferred contact method'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<?php echo edit_field_contact('contact_method', get_user_setting($user_id, 'contactmethod')); ?>
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

					<!-- COMMENTS -->
					<div class="cell large-3">
						<label for="comment">
							<?php echo KT_I18N::translate('Administrator comments on user'); ?>
						</label>
					</div>
					<div class="cell large-9">
						<div class="input_group">
							<textarea id="comment" name="comment" rows="5" maxlength="255"><?php echo KT_Filter::escapeHtml(get_user_setting($user_id, 'comment')); ?></textarea>
						</div>
					</div>

					<!-- ADMIN NOTIFICATION OPTIONS -->
					<?php if (KT_USER_IS_ADMIN) { ?>
						<div class="cell large-3">
							<label for="notify_clipping">
								<?php echo KT_I18N::translate('Notification options'); ?>
							</label>
						</div>
						<div class="cell large-9">
							<div class="grid-x">
								<div class="small-6 medium-3 auto">
									<label for="notify_clipping" class="cell medium-10 offset">
										<?php echo KT_I18N::translate('Clippings cart'); ?>
										<?php echo simple_switch(
											'notify_clipping',
											true,
											get_user_setting($user_id, 'notify_clipping'),
											'',
											KT_I18N::translate('Yes'),
				                            KT_I18N::translate('No'),
											'small'
										); ?>
									</label>
								</div>
							</div>
							<div class="cell callout info-help ">
								<?php echo KT_I18N::translate('
									When a user downloads a GEDCOM file created in the Clippings cart the
									site administrator will be notified by mail if this option is selected.
								'); ?>
							</div>
						</div>

					<?php } ?>

					<hr class="cell">

					<!-- FAMILY TREEs - ACCESS and SETTINGS -->
					<div id="access" class="cell">
						<h4><?php echo KT_I18N::translate('Family tree roles and settings'); ?></h4>
						<div class="grid-x">
							<div class="cell callout info-help shortenMedium">
								<h5><?php echo KT_I18N::translate('Help for family tree access settings'); ?></h5>
								<dl>
									<dt><?php echo KT_I18N::translate('Default individual'); ?></dt>
									<dd>
									<?php echo KT_I18N::translate('
										This individual will be selected by default when viewing charts and reports.
									'); ?>
									</dd>
									<dt><?php echo KT_I18N::translate('Individual record'); ?></dt>
									<dd>
									<?php echo KT_I18N::translate('Link this user to an individual in the family tree.'); ?>
									</dd>
									<dt><?php echo KT_I18N::translate('Roles'); ?></dt>
									<dd>
									<?php echo KT_I18N::translate('
										A role is a set of access rights, which give permission to view data,
										change preferences, etc. Access rights are assigned to roles,
										and roles are granted to users. Each family tree can assign different access to each role,
										and users can have a different role in each family tree.
									'); ?>
									</dd>
										<dl class="offset">
											<dt><?php echo KT_I18N::translate('Member'); ?></dt>
											<dd>
												<?php echo KT_I18N::translate('
												This role has permissions to view but not edit the full tree,
												subject to any additional limits set in the family tree configuration.
											'); ?>
											</dd>
											<dt><?php echo KT_I18N::translate('Editor'); ?></dt>
											<dd>
												<?php echo KT_I18N::translate('
													This role has all the permissions of the member role,
													plus permission to add/change/delete data.
													Any changes will need to be reviewed by a moderator,
													unless the user has the “automatically accept changes” option enabled.
												'); ?>
											</dd>
											<dt><?php echo KT_I18N::translate('Moderator'); ?></dt>
											<dd>
												<?php echo KT_I18N::translate('
													This role has all the permissions of the editor role,
													plus permission to accept/reject changes made by other users.
												'); ?>
											</dd>
											<dt><?php echo KT_I18N::translate('Manager'); ?></dt>
											<dd>
												<?php echo KT_I18N::translate('
													This role has all the permissions of the moderator role,
													plus any additional access granted by the family tree configuration,
													plus permission to change the settings/configuration of a family tree.
												'); ?>
											</dd>
											<dt><?php echo KT_I18N::translate('Administrator'); ?></dt>
											<dd>
												<?php echo KT_I18N::translate('
													This role has all the permissions of the manager role in all family trees,
													plus permission to change the settings/configuration of the website, users, and modules.
												'); ?>
											</dd>
										</dl>
									<dt><?php echo KT_I18N::translate('Restrict to close family'); ?></dt>
									<dd>
										<?php echo KT_I18N::translate('
											Where a user is associated with an individual record in a family tree
											and has a role of member, editor, or moderator, you can prevent them from accessing the details of distant,
											living relations. You specify the number of relationship steps that the user is allowed to see.
										'); ?>
										<?php echo KT_I18N::translate('
											For example, if you specify a path length of 2, the individual will be able
											to see their grandson (child, child), their aunt (parent, sibling),
											their step-daughter (spouse, child), but not their first cousin (parent, sibling, child).
										'); ?>
										<?php echo KT_I18N::translate('
											Note: longer path lengths require a lot of calculation,
											which can make your website run slowly for these users.
										'); ?>
									</dd>
								</dl>
							</div>

							<!-- ADMINISTRATOR -->
							<div class="cell large-2">
								<label for="admin" class="admin">
									<?php echo KT_I18N::translate('Administration role'); ?>
								</label>
							</div>
							<div class="cell large-10">
								<?php $user_id === KT_USER_ID ? $disabled = 'disabled' : $disabled = ''; ?>
								<?php echo simple_switch(
									'canadmin',
									true,
									get_user_setting($user_id, 'canadmin'),
									$disabled,
									KT_I18N::translate('Yes'),
									KT_I18N::translate('No'),
									'small'
								); ?>
							</div>

							<!-- FAMILY TREE SETTINGS -->
							<div class="cell">
								<table>
									<thead>
										<tr>
											<th><?php echo KT_I18N::translate('Family tree'); ?></th>
											<th><?php echo KT_I18N::translate('Default individual'); ?></th>
											<th><?php echo KT_I18N::translate('Individual record'); ?></th>
											<th><?php echo KT_I18N::translate('Role'); ?></th>
											<th><?php echo KT_I18N::translate('Restrict to close family'); ?></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="5">
												<div class="callout info-help">
													<?php echo KT_I18N::translate('For more imformation about these items, see the help content above.'); ?>
												</div>
											</td>
										</tr>
										<?php foreach (KT_Tree::getAll() as $tree) { ?>
											<tr>
												<td>
													<?php echo $tree->tree_title_html . '&nbsp;' . KT_I18N::translate('(ID: %s)', $tree->tree_id); ?>
												</td>
												<!-- PEDIGREE ROOT PERSON -->
												<td>
													<?php
													$varname	= 'rootid' . $tree->tree_id;
													$xref		= $tree->userPreference($user_id, 'rootid');
													$rootID		= new KT_Person(find_gedcom_record($xref, $tree->tree_id, true));
													if ($xref) {
														$rootName = strip_tags($rootID->getLifespanName());
													} else {
														$rootName = '';
													}
													echo autocompleteHtml(
														$varname,
														'INDI',
														$tree->tree_name_html,
														$rootName,
														KT_I18N::translate('Individual name'),
														$varname,
														''
													);?>
												</td>

												<!-- GEDCOM INDI Record ID -->
												<td>
													<?php
													$varname	= 'gedcomid' . $tree->tree_id;
													$xref		= $tree->userPreference($user_id, 'gedcomid');
													$gedcomID	= new KT_Person(find_gedcom_record($xref, $tree->tree_id, true));
													if ($xref) {
														$gedcomName = strip_tags($gedcomID->getLifespanName());
													} else {
														$gedcomName = '';
													}
													echo autocompleteHtml(
														$varname,
														'INDI',
														$tree->tree_name_html,
														$gedcomName,
														KT_I18N::translate('Individual name'),
														$varname,
														''
													); ?>
												</td>

												<!-- ROLE -->
												<td>
													<?php $varname = 'canedit' . $tree->tree_id; ?>
													<select name="<?php echo $varname; ?>">
														<?php foreach ($ALL_EDIT_OPTIONS as $EDIT_OPTION => $desc) { ?>
															<option value="<?php echo $EDIT_OPTION; ?>"
																<?php echo $EDIT_OPTION === $tree->userPreference($user_id, 'canedit') ? 'selected' : ''; ?>
															><?php echo $desc; ?></option>
														<?php } ?>
													</select>
												</td>
												<!-- RELATIONSHIP PATH -->
												<td>
													<?php $varname = 'RELATIONSHIP_PATH_LENGTH' . $tree->tree_id; ?>
													<select name="<?php echo $varname; ?>" id="<?php echo $varname; ?>" class="relpath">
														<?php for ($n = 0; $n <= 10; ++$n) { ?>
															<option
																value="<?php echo $n; ?>"
																<?php echo $tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH') == $n ? ' selected' : ''; ?>
															>
															<?php
																echo $n ?  /* I18N: setting privacy for relationship steps */ KT_I18N::plural('
																	%s step away', '%s steps away', $n, $n) : KT_I18N::translate('No')
																; ?>
														<?php } ?>
													</select>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="cell large-10 align-left button-group">
					<button class="button primary" type="submit">
						<i class="<?php echo $iconStyle; ?> fa-save"></i>
						 <?php echo KT_I18N::translate('Save'); ?>
					</button>
					<button class="button hollow" type="button" onclick="window.location.href='<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME; ?>'">
						<i class="<?php echo  $iconStyle; ?> fa-xmark"></i>
						<?php echo KT_I18N::translate('Close'); ?>
					</button>
				</div>
			</form>

		<?php echo pageClose();
		return;

	case 'cleanup':
		$controller
			->pageHeader()
			->setPageTitle(KT_I18N::translate('Delete inactive users'))
			->addInlineJavascript('
				jQuery("#selectMonths").change(function() {
				    window.location = "admin_users.php?action=cleanup&month=" + jQuery(this).val();
				});
			');

		$month		= KT_Filter::getInteger('month', 1, 60, 6);
		$range		= "1,2,3,4,5,6,7,8,9,10,11,12,18,24,36,48,60";
		$monthRange	= explode( ',', $range );

		echo relatedPages($users, 'admin_users.php?action=cleanup');

		echo pageStart('admin_users_cleanup', $controller->getPageTitle()); ?>

			<form class="cell" name="cleanupform" method="post" action="admin_users.php?action=cleanup2">
				<table id="clean" class="unstriped">
					<tbody>
						<tr>
							<td>
								<?php echo KT_I18N::translate('Number of months since the last login for a user\'s account to be considered inactive: '); ?>
							</td>
							<td>
								<select id="selectMonths">
									<?php foreach( $monthRange as $i ) { ?>
										<option value="<?php echo $i; ?>"
											<?php if ($i == $month) { ?>
												selected="selected"
											<?php } ?>
											> <?php echo $i; ?>
										</option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<!-- Check users inactive too long -->
						<tr>
							<th colspan="2">
								<?php echo KT_I18N::plural(
									'These users have not logged in for at least %s month',
									'These users have not logged in for at least %s months',
									$month, $month
								); ?>
							</th>
						</tr>
						<?php
						$ucnt = 0;
						foreach (get_all_users() as $user_id=>$username) {
							$userName = getUserFullName($user_id);
							if ((int)get_user_setting($user_id, 'sessiontime') == "0")
								$datelogin = (int)get_user_setting($user_id, 'reg_timestamp');
							else
								$datelogin = (int)get_user_setting($user_id, 'sessiontime');
							if ((mktime(0, 0, 0, (int)date("m")-$month, (int)date("d"), (int)date("Y")) > $datelogin) && get_user_setting($user_id, 'verified') && get_user_setting($user_id, 'verified_by_admin')) { ?>
								<tr>
									<td>
										<?php echo $username; ?>
										 - <span>
											 <?php echo $userName; ?>
										 </span>
										  - <span>
											<?php echo KT_I18N::translate('Not logged in since: %s', timestamp_to_gedcom_date($datelogin)->Display(false));
											$ucnt++; ?>
										</span>
									</td>
									<td>
										<input type="checkbox" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_", "_", "_"), $username); ?>" value="1">
									</td>
								</tr>
							<?php }
						}
						if ($ucnt == 0) { ?>
							<tr>
								<td class="success" colspan="2">
									<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
								</td>
							</tr>
						<?php } ?>

						<!-- Check unverified users -->
						<tr>
							<th colspan="2">
								<?php echo KT_I18N::plural(
									'These users have not verified their email address for %s day or more',
									'These users have not verified their email address for %s days or more',
									$days, $days
								); ?>
							</th>
						</tr>
						<?php
						$vcnt = 0;
						foreach (get_all_users() as $user_id => $username) {
							if (((date("U") - (int)get_user_setting($user_id, 'reg_timestamp')) > $time) && !get_user_setting($user_id, 'verified')) {
								$userName = getUserFullName($user_id); ?>
								<tr>
									<td>
										<?php echo $username, " - ", $userName;
										$vcnt++; ?>
									</td>
									<td>
										<input type="checkbox" checked="checked" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_",  "_", "_"), $username); ?>" value="1">
									</td>
								</tr>
							<?php }
						}
						if ($vcnt == 0) { ?>
							<tr>
								<td class="success" colspan="2">
									<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
								</td>
							</tr>
						<?php } ?>

						<!-- Check users not verified by admin -->
						<tr>
							<th colspan="2">
								<?php echo KT_I18N::translate('These users have not been verified by admin'); ?>
							</th>
						</tr>
						<?php
						$acnt = 0;
						foreach (get_all_users() as $user_id => $username) {
							if (!get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'verified')) {
								$userName = getUserFullName($user_id); ?>
								<hr>
								<tr>
									<td>
										<?php echo $username, " - ", $userName, ":&nbsp;&nbsp;", KT_I18N::translate('User not verified by administrator.'); ?>
									</td>
									<td>
										<input type="checkbox" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_", "_", "_"), $username); ?>" value="1">
									</td>
								</tr>
								<?php
								$acnt++;
							}
						}
						if ($acnt == 0) { ?>
							<tr>
								<td class="success" colspan="2">
									<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php if (($vcnt + $acnt + $ucnt) > 0) { ?>
					<button type="submit" class="button">
						<i class="<?php echo $iconStyle; ?> fa-user-times"></i>
						<?php echo KT_I18N::translate('Continue'); ?>
					</button>
				<?php } ?>
			</form>

		<?php echo pageClose();
		break;

	case 'cleanup2':
		foreach (get_all_users() as $user_id => $username) {
			$var = "del_" . str_replace(array(".", "-", " "), array("_", "_", "_"), $username);
			if (KT_Filter::post($var) && KT_Filter::post($var) == '1') {
				delete_user($user_id);
				AddToLog('Deleted user ->' . $username . '<-', 'auth');
				echo KT_I18N::translate('Deleted user: '); echo $username, "<br>";
			}
		}
		break;

	case 'messaging':
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle(KT_I18N::translate('Broadcast messages'))
			->pageHeader();

		echo relatedPages($users, 'admin_users.php?action=messaging');

		echo pageStart('admin_users_bulk', $controller->getPageTitle()); ?>

			<div class="cell">
				<div class="grid-x grid-margin-y">
					<div class="cell">
						<a href="admin_message.php?to=all&amp;method=messaging" class="button">
							<i class="<?php echo $iconStyle; ?> fa-paper-plane"></i>
							&nbsp;&nbsp;
							<?php echo KT_I18N::translate('To all users'); ?>
						</a>
					</div>
					<div class="cell">
						<a href="admin_message.php?to=never_logged&amp;method=messaging" target="_blank" class="button">
							<i class="<?php echo $iconStyle; ?> fa-paper-plane"></i>
							&nbsp;&nbsp;
							<?php echo KT_I18N::translate('To users who have never logged in'); ?>
						</a>
					</div>
					<div class="cell">
						<a href="admin_message.php?to=last_6mo&amp;method=messaging" target="_blank" class="button">
							<i class="<?php echo $iconStyle; ?> fa-paper-plane"></i>
							&nbsp;&nbsp;
							<?php echo KT_I18N::translate('To users who have not logged in for 6 months'); ?>
						</a>
					</div>
				</div>
			</div>

		<?php echo pageClose();
		break;

	default:
		$controller
			->pageHeader()
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS)
			->addExternalJavascript(KT_DATATABLES_BUTTONS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_BUTTONS)
			->addExternalJavascript(KT_DATATABLES_HTML5)
			->addInlineJavascript('
				jQuery("#list").dataTable({
					dom: \'<"top"pBf<"clear">irl>t<"bottom"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csvHtml5", exportOptions: {columns: [1,2,3,5,7] }}],
					autoWidth: false,
					processing: true,
					serverSide: true,
					sAjaxSource: "' . KT_SCRIPT_NAME . '?action=loadrows",
					pagingType: "full_numbers",
					stateSave: true,
					stateSaveParams: function (settings, data) {
						data.columns.forEach(function(column) {
							delete column.sSearch;
						});
					},
					stateDuration: -1,
					sorting: [[2,"asc"]],
					columns: [
						/*  0 edit          	*/ { sortable:false },
						/*  1 user-id           */ { visible:false },
						/*  2 user_name         */ null,
						/*  3 real_name         */ null,
						/*  4 email             */ null,
						/*  5 language          */ null,
						/*  6 registered (sort) */ { visible:false },
						/*  7 registered        */ { orderData: 6 },
						/*  8 last_login (sort) */ { visible:false },
						/*  9 last_login        */ { orderData: 8 },
						/* 10 verified          */ { className:"text-center" },
						/* 11 verified_by_admin */ { className:"text-center" },
						/* 12 delete            */ { sortable:false },
						/* 13 masquerade        */ { sortable:false }
					],
				})
				.fnFilter("' . KT_Filter::get('filter') . '"); // View the details of a newly created user
			');

		echo relatedPages($users, KT_SCRIPT_NAME);

		echo pageStart('admin_users_list', $controller->getPageTitle()); ?>

			<div class="cell">
				<table id="list" >
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Edit'); ?></th>
							<th>user-id</th>
							<th><?php echo KT_I18N::translate('Username'); ?></th>
							<th><?php echo KT_I18N::translate('Real name'); ?></th>
							<th><?php echo KT_I18N::translate('Email'); ?></th>
							<th><?php echo KT_I18N::translate('Language'); ?></th>
							<th>date_registered</th>
							<th><?php echo KT_I18N::translate('Date registered'); ?></th>
							<th>last_login</th>
							<th><?php echo KT_I18N::translate('Last logged in'); ?></th>
							<th><?php echo KT_I18N::translate('Verified'); ?></th>
							<th><?php echo KT_I18N::translate('Approved'); ?></th>
							<th colspan="2"><?php echo KT_I18N::translate('Options'); ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

		<?php echo pageClose();
		break;

}
