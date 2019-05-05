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

define('KT_SCRIPT_NAME', 'login.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

// If we are already logged in, then go to the home page
if (KT_USER_ID && KT_GED_ID) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
	exit;
}

$controller = new KT_Controller_Page();

$action				= KT_Filter::post('action');
$user_realname		= KT_Filter::post('user_realname');
$user_name			= KT_Filter::post('user_name', KT_REGEX_USERNAME);
$user_email			= KT_Filter::post('user_email', KT_REGEX_EMAIL);
$user_password01	= KT_Filter::post('user_password01', KT_REGEX_PASSWORD);
$user_password02	= KT_Filter::post('user_password02', KT_REGEX_PASSWORD);
$user_comments		= KT_Filter::post('user_comments');
$user_password		= KT_Filter::post('user_password', KT_REGEX_UNSAFE); // Can use any password that was previously stored
$user_hashcode		= KT_Filter::post('user_hashcode');
$url				= KT_Filter::post('url', KT_REGEX_URL);
$username			= KT_Filter::post('username', KT_REGEX_USERNAME);
$password			= KT_Filter::post('password',KT_REGEX_UNSAFE); // Can use any password that was previously stored
$usertime			= KT_Filter::post('usertime');

// These parameters may come from the URL which is emailed to users.
if (empty($action)) $action 				= KT_Filter::get('action');
if (empty($user_name)) $user_name			= KT_Filter::get('user_name',KT_REGEX_USERNAME);
if (empty($user_hashcode)) $user_hashcod	= KT_Filter::get('user_hashcode');

// This parameter may come from generated login links
if (!$url) {
	$url = KT_Filter::get('url', KT_REGEX_URL);
}

$message='';

switch ($action) {
case 'login':
default:
	if ($action == 'login') {
		$user_id = authenticateUser($username, $password);
		switch ($user_id) {
		case -1: // not validated
			$message = KT_I18N::translate('This account has not been verified. Please check your email for a verification message.');
			break;
		case -2: // not approved
			$message = KT_I18N::translate('This account has not been approved. Please wait for an administrator to approve it.');
			break;
		case -3: // bad password
		case -4: // bad username
			$message = KT_I18N::translate('The username or password is incorrect.');
			break;
		case -5: // no cookies
			$message = KT_I18N::translate('You cannot login because your browser does not accept cookies.');
			break;
		default: // Success
			if ($usertime) {
				$KT_SESSION->timediff = KT_TIMESTAMP - strtotime($usertime);
			} else {
				$KT_SESSION->timediff = 0;
			}
			$KT_SESSION->locale		= get_user_setting($user_id, 'language');
			$KT_SESSION->theme_dir 	= get_user_setting($user_id, 'theme');
			$KT_SESSION->gedcomid 	= get_gedcomid($user_id, KT_GED_ID);
			if (KT_GED_ID == "") {
				$KT_SESSION->rootid 	= $KT_SESSION->gedcomid;
				$PEDIGREE_ROOT_ID 	= $KT_SESSION->gedcomid;
			} else {
				$KT_SESSION->rootid	= $KT_TREE->userPreference($user_id, 'rootid');
				$PEDIGREE_ROOT_ID	= get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');
			}

			// If we’ve clicked login from the login page, we don’t want to go back there.
			if (strpos('index.php', $url) === 0) {
				if ($KT_SESSION->gedcomid) {
					$url = 'individual.php?pid=' . $KT_SESSION->gedcomid . '&amp;ged=' . KT_GEDURL;
				} elseif ($KT_SESSION->rootid) {
					$url = 'individual.php?pid=' . $KT_SESSION->rootid . '&amp;ged=' . KT_GEDURL;
				} elseif ($PEDIGREE_ROOT_ID) {
					$url = 'individual.php?pid=' . $PEDIGREE_ROOT_ID . '&amp;ged=' . KT_GEDURL;
				} else {
					$url = 'index.php?ged=' . KT_GEDURL;
				}
			}

			// Redirect to the target URL
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . $url);
			// Explicitly write the session data before we exit,
			// as it doesn’t always happen when using APC.
			Zend_Session::writeClose();
			exit;
		}
	}

	$controller
		->setPageTitle(KT_I18N::translate('Login'))
		->pageHeader(true);
	?>

	<div id="login-page" class="grid-x grid-margin-x grid-margin-y">
		<div id="login-text" class="cell medium-10 medium-offset-1 large-4 large-offset-4">
			<?php switch (KT_Site::preference('WELCOME_TEXT_AUTH_MODE')) {
			case 1:
				echo KT_I18N::translate('
					<h4 class="text-center">Welcome to this Genealogy website</h4>
					<p>Access to this site is permitted to every visitor who has a user account.</p>
					<p>If you have a user account, you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.</p>
					<p>After verifying your application, the site administrator will activate your account. You will receive an email when your application has been approved.</p>
				');
				break;
			case 2:
				echo KT_I18N::translate('
					<h4 class="text-center">Welcome to this Genealogy website</h4>
					<p>Access to this site is permitted to <u>authorized</u> users only.</p>
					<p>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.</p>
					<p>After verifying your information, the administrator will either approve or decline your account application. You will receive an email message when your application has been approved.</p>
				');
				break;
			case 3:
				echo KT_I18N::translate('
					<h4 class="text-center">Welcome to this Genealogy website</h4>
					<p>Access to this site is permitted to <u>family members only</u>.</p>
					<p>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.</p>
					<p>After verifying the information you provide, the administrator will either approve or decline your request for an account. You will receive an email when your request is approved.</p>
				');
				break;
			case 4:
				echo '<div style="white-space: pre-wrap;">' . KT_Site::preference('WELCOME_TEXT_AUTH_MODE_', LOCALE) . '</div';
				break;
			} ?>
			<form id="login-form"
				name="login-form"
				method="post"
				action="<?php echo KT_LOGIN_URL; ?>"
				onsubmit="t = new Date();this.usertime.value=t.getFullYear()+'-'+(t.getMonth()+1)+'-'+t.getDate()+' '+t.getHours()+':'+t.getMinutes()+':'+t.getSeconds();return true;"
			>
				<input type="hidden" name="action" value="login">
				<input type="hidden" name="url" value="<?php echo htmlspecialchars($url); ?>">
				<input type="hidden" name="usertime" value="">
				<?php if (!empty($message)) { ?>
					<div class="callout alert">
						<h6><?php echo $message; ?></h6>
					</div>
				<?php } ?>
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell medium-8 medium-offset-2">
						<label for="username" class="h6"><?php echo KT_I18N::translate('User name'); ?>
							<input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>" autofocus>
						</label>
					</div>
					<div class="cell medium-8 medium-offset-2">
						<label for="password" class="h6"><?php echo KT_I18N::translate('Password'); ?></label>
						<div class="input-group">
							<input class="input-group-field" type="password" id="password" name="password" placeholder="<?php echo KT_I18N::translate('Password'); ?>" required value="<?php echo htmlspecialchars($username); ?>">
							<span class="input-group-label unmask" title="<?php echo KT_I18N::translate('Show/Hide password to check content'); ?>">
								<i class="<?php echo $iconStyle; ?> fa-eye"></i>
							</span>
						</div>
					</div>
					<div class="cell medium-8 medium-offset-2">
						<button class="button expanded h6" type="submit" >
							<i class="<?php echo $iconStyle; ?> fa-sign-in"></i>
							<?php echo KT_I18N::translate('Login'); ?>
						</button>
					</div>
					<div class="cell medium-8 medium-offset-2 text-center h6">
						<a href="<?php echo KT_LOGIN_URL; ?>?action=requestpw"><?php echo KT_I18N::translate('Request new password'); ?></a>
					</div>
					<?php if (KT_Site::preference('USE_REGISTRATION_MODULE')) { ?>
						<div class="cell medium-8 medium-offset-2 text-center h6">
							<a href="<?php echo KT_LOGIN_URL; ?>?action=register"><?php echo KT_I18N::translate('Request new user account'); ?></a>
						</div>
					<?php } ?>
				</div>
			</form>
		</div>
	</div>
	<?php break;

case 'requestpw':
	$controller
		->setPageTitle(KT_I18N::translate('Lost password request'))
		->pageHeader()
		->addInlineJavascript('
			display_help();
			function regex_quote(str) {return str.replace(/[\\\\.?+*()[\](){}|]/g, "\\\\$&");}
		');
	?>

	<div id="login-passwd-page" class="row align-center">
		<?php $user_name = KT_Filter::post('new_passwd_username', KT_REGEX_USERNAME);
		if ($user_name) {
			$user_id = KT_DB::prepare(
				"SELECT user_id FROM `##user` WHERE ? IN (user_name, email)"
			)->execute(array($user_name))->fetchOne();
			if ($user_id) {
				$passchars = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				$user_new_pw = '';
				$max = strlen($passchars)-1;
				for ($i=0; $i<8; $i++) {
					$index = rand(0,$max);
					$user_new_pw .= $passchars{$index};
				}

				set_user_password($user_id, $user_new_pw);
				set_user_setting($user_id, 'pwrequested', 1);
				$user_name = get_user_name($user_id);

				$mail_body = '';
				$mail_body .= KT_I18N::translate('Hello %s ...', getUserFullName($user_id)) . "\r\n\r\n";
				$mail_body .= KT_I18N::translate('A new password was requested for your user name.') . "\r\n\r\n";
				$mail_body .= KT_I18N::translate('Username') . ": " . $user_name . "\r\n";

				$mail_body .= KT_I18N::translate('Password') . ": " . $user_new_pw . "\r\n\r\n";
				$mail_body .= KT_I18N::translate('Recommendation:') . "\r\n";
				$mail_body .= KT_I18N::translate('Please click on the link below or paste it into your browser, login with the new password, and change it immediately to keep the integrity of your data secure.') . "\r\n\r\n";
				$mail_body .= KT_I18N::translate('After you have logged in, select the «My Account» link under the your name in the menu and fill in the password fields to change your password.') . "\r\n\r\n";

				if ($TEXT_DIRECTION == 'rtl') {
					$mail_body .= "<a href=\"". KT_SERVER_NAME . KT_SCRIPT_PATH."\">". KT_SERVER_NAME . KT_SCRIPT_PATH."</a>";
				} else {
					$mail_body .= KT_SERVER_NAME . KT_SCRIPT_PATH;
				}

				require_once KT_ROOT . 'includes/functions/functions_mail.php';
				kiwiMail(getUserEmail($user_id), $KIWITREES_EMAIL, KT_I18N::translate('Lost password request'), $mail_body);
			}
			// Show a success message, even if the user account does not exist.
			// Otherwise this page can be used to guess/test usernames.
			// A genuine user will hopefully always know their own email address.
			?>
			<div class="confirm">
				<p> <?php echo /* I18N: %s is a username */ KT_I18N::translate('A new password has been created and emailed to %s. You can change this password after you login.', $user_name); ?></p>
			</div>
			<?php echo AddToLog('Password request was sent to user: '.$user_name, 'auth');
		} else { ?>
			<div id="login-box"class="large-3 large-centered small-12 columns">
				<h4 class="text-center"><?php echo $controller->getPageTitle(); ?></h4>
				<div id="new_passwd">
					<form class="new_passwd_form" name="new_passwd_form" action="<?php echo KT_LOGIN_URL; ?>" method="post">
						<input type="hidden" name="action" value="requestpw">
						<div class="row column log-in-form">
							<label><?php echo KT_I18N::translate('Username or email address'); ?>
								<input type="text" class="new_passwd_username" name="new_passwd_username" value="" required aria-describedby="password_lost">
								<p id="password_lost" class="help-text"></p>
						</label>
						</div>
						<p class="text-center"><input type="submit" class="button expanded" value="<?php echo /* I18N: button label */ KT_I18N::translate('Submit'); ?>"></div>
					</form>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php break;

case 'register':
	if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
		header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
		exit;
	}

	$controller->setPageTitle(KT_I18N::translate('Request new user account'));

	// The form parameters are mandatory, and the validation errors are shown in the client.
	if ($KT_SESSION->good_to_send && $user_name && $user_password01 && $user_password01 == $user_password02 && $user_realname && $user_email && $user_comments) {

		// These validation errors cannot be shown in the client.
		if (get_user_id($user_name)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate user name. A user with that user name already exists. Please choose another user name.'));
		} elseif (get_user_by_email($user_email)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
		} elseif (preg_match('/(?!'.preg_quote(SERVER_NAME, '/').')(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $user_comments, $match)) {
			KT_FlashMessages::addMessage(
				KT_I18N::translate('You are not allowed to send messages that contain external links.') . ' ' .
				KT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.', $match[2], $match[1])
			);
			AddToLog('Possible spam registration from "' . $user_name . '"/"' . $user_email . '", IP="' . $REQUEST->getClientIp() . '", comments="' . $user_comments . '"', 'auth');
		} else {
			// Everything looks good - create the user
			$controller->pageHeader();
			AddToLog('User registration requested for: ' . $user_name, 'auth');

			$user_id = create_user($user_name, $user_realname, $user_email, $user_password01);

			set_user_setting($user_id, 'language',			KT_LOCALE);
			set_user_setting($user_id, 'verified',			0);
			set_user_setting($user_id, 'verified_by_admin',	0);
			set_user_setting($user_id, 'reg_timestamp',		date('U'));
			set_user_setting($user_id, 'reg_hashcode',		md5(uniqid(rand(), true)));
			set_user_setting($user_id, 'contactmethod',		'messaging2');
			set_user_setting($user_id, 'visibleonline',		1);
			set_user_setting($user_id, 'auto_accept',		0);
			set_user_setting($user_id, 'canadmin',			0);
			set_user_setting($user_id, 'sessiontime', 		0);

			// Generate an email in the admin’s language
			$webmaster_user_id = get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
			KT_I18N::init(get_user_setting($webmaster_user_id, 'language'));

			$mail1_body =
				KT_I18N::translate('Hello Administrator ...')."\r\n\r\n".
				/* I18N: %s is a server name/URL */
				KT_I18N::translate('A prospective user has registered at %s.', KT_SERVER_NAME . KT_SCRIPT_PATH . ' ' . strip_tags(KT_TREE_TITLE)) . "\r\n\r\n".
				KT_I18N::translate('Username')      . ' ' .$user_name     . "\r\n".
				KT_I18N::translate('Real name')     . ' ' .$user_realname . "\r\n".
				KT_I18N::translate('Email Address:'). ' ' .$user_email    . "\r\n\r\n".
				KT_I18N::translate('Comments')      . ' ' .$user_comments . "\r\n\r\n".
				KT_I18N::translate('The user has been sent an e-mail with the information necessary to confirm the access request') . "\r\n\r\n";

			$mail1_body .= KT_I18N::translate('You will be informed by e-mail when this prospective user has confirmed the request. You can then complete the process by activating the user name. The new user will not be able to login until you activate the account.') . "\r\n";
			$mail1_body .=
				"\r\n" .
				"=--------------------------------------=\r\nIP ADDRESS: ".$REQUEST->getClientIp()."\r\n" .
				"DNS LOOKUP: ". gethostbyaddr($REQUEST->getClientIp()) . "\r\n" .
				"LANGUAGE: " . KT_LOCALE."\r\n";

			$mail1_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('New registration at %s', KT_SERVER_NAME . KT_SCRIPT_PATH . ' ' . strip_tags(KT_TREE_TITLE));
			$mail1_to      = $KIWITREES_EMAIL;
			$mail1_from    = $user_email;
			$mail1_method  = get_user_setting($webmaster_user_id, 'contact_method');
			KT_I18N::init(KT_LOCALE);

			echo '<div id="login-register-page">';

			require_once KT_ROOT . 'includes/functions/functions_mail.php';

			// Generate an email in the user’s language
			$mail2_body =
				KT_I18N::translate('Hello %s ...', $user_realname) . "\r\n\r\n" .
				/* I18N: %1$s is the site URL and %2$s is an email address */
				KT_I18N::translate('You (or someone claiming to be you) has requested an account at %1$s using the email address %2$s.', KT_SERVER_NAME . KT_SCRIPT_PATH . ' ' . strip_tags(KT_TREE_TITLE), $user_email) . ' '.
				KT_I18N::translate('Information about the request is shown under the link below.') . "\r\n\r\n".
				KT_I18N::translate('Please click on the following link and fill in the requested data to confirm your request and email address.') . "\r\n\r\n";
			if ($TEXT_DIRECTION == 'rtl') {
				$mail2_body .= "<a href=\"";
				$mail2_body .= KT_LOGIN_URL . "?user_name=" . urlencode($user_name)."&user_hashcode=".urlencode(get_user_setting($user_id, 'reg_hashcode')) . "&action=userverify\">";
			}
			$mail2_body .= KT_LOGIN_URL . "?user_name=". urlencode($user_name)."&user_hashcode=".urlencode(get_user_setting($user_id, 'reg_hashcode')) . "&action=userverify";
			if ($TEXT_DIRECTION == 'rtl') {
				$mail2_body .= "</a>";
			}
			$mail2_body.=
				"\r\n".
				KT_I18N::translate('Username') . " " . $user_name . "\r\n" .
				KT_I18N::translate('Verification code:') . " " . get_user_setting($user_id, 'reg_hashcode') . "\r\n\r\n" .
				KT_I18N::translate('Comments').": " . $user_comments . "\r\n\r\n".
				KT_I18N::translate('If you didn\'t request an account, you can just delete this message.') . " " .
				KT_I18N::translate('You won\'t get any more email from this site, because the account request will be deleted automatically after seven days.') . "\r\n";
			$mail2_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('Your registration at %s', KT_SERVER_NAME . KT_SCRIPT_PATH);
			$mail2_to      = $user_email;
			$mail2_from    = $KIWITREES_EMAIL;

			// Send user message by email only
			kiwiMail($mail2_to, $mail2_from, $mail2_subject, $mail2_body);

			// Send admin message by email and/or internal messaging
			kiwiMail($mail1_to, $mail1_from, $mail1_subject, $mail1_body);
			if ($mail1_method != 'messaging3' && $mail1_method != 'mailto' && $mail1_method != 'none') {
				DB::prepare("INSERT INTO `##message` (sender, ip_address, user_id, subject, body) VALUES (? ,? ,? ,? ,?)")
					->execute(array($user_email, $REQUEST->getClientIp(), $webmaster_user_id, $mail1_subject, $mail1_body));
			}
			?>
			<div class="confirm">
				<p><?php echo KT_I18N::translate('Hello %s ...<br />Thank you for your registration.', $user_realname); ?></p>
				<p><?php echo KT_I18N::translate('We will now send a confirmation email to the address <b>%s</b>. You must verify your account request by following instructions in the confirmation email. If you do not confirm your account request within seven days, your application will be rejected automatically. You will have to apply again.<br /><br />After you have followed the instructions in the confirmation email, the administrator still has to approve your request before your account can be used.<br /><br />To login to this site, you will need to know your user name and password.', $user_email); ?></p>
			</div>
			<?php exit;
		}
	}

	$KT_SESSION->good_to_send = true;
	$controller
		->pageHeader()
		->addInlineJavascript('
			display_help();
			function regex_quote(str) {return str.replace(/[\\\\.?+*()[\](){}|]/g, "\\\\$&");}'
		);?>

	<div id="login-register-page" class="row align-center">
		<div id="login-box" class="large-4 large-centered small-12 columns">
			<h4 class="text-center"><?php echo $controller->getPageTitle(); ?></h4>
			<?php if (KT_Site::preference('SHOW_REGISTER_CAUTION')) { ?>
				<div id="register-text">
				<?php echo KT_I18N::translate('<div class="largeError">Notice:</div><div class="error">By completing and submitting this form, you agree:<ul><li>to protect the privacy of living people listed on our site;</li><li>and in the text box below, to explain to whom you are related, or to provide us with information on someone who should be listed on our site.</li></ul></div>'); ?>
				</div>
			<?php } ?>
			<div id="register-box">
				<form id="register-form" name="register-form" method="post" action="<?php echo KT_LOGIN_URL; ?>" onsubmit="return checkform(this);" autocomplete="off">
					<input type="hidden" name="action" value="register">
					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-user"></i>
						</span>
						<input class="input-group-field" type="text" id="user_realname" name="user_realname" placeholder="<?php echo KT_I18N::translate('Real name'); ?>" required maxlength="64" value="<?php echo htmlspecialchars($user_realname); ?>" aria-describedby="real_name" autofocus>
				    </div>
					<p id="real_name" class="help-text"></p>
					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-envelope"></i>
						</span>
						<input class="input-group-field" type="email" id="user_email" name="user_email" placeholder="<?php echo KT_I18N::translate('Email address'); ?>" required maxlength="64" value="<?php echo htmlspecialchars($user_email); ?>" aria-describedby="email">
				    </div>
					<p id="email" class="help-text"></p>

					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-user-secret"></i>
						</span>
						<input class="input-group-field" type="text" id="user_name" name="user_name" placeholder="<?php echo KT_I18N::translate('User name'); ?>" required  value="<?php echo htmlspecialchars($user_name); ?>" aria-describedby="username01">
				    </div>
					<p id="username01" class="help-text"></p>

					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-key"></i>
						</span>
						<input
							class="input-group-field"
							type="password"
							id="user_password01"
							name="user_password01"
							value="<?php echo htmlspecialchars($user_password01); ?>"
							placeholder="<?php echo /* I18N: placeholder text for new-password field */ KT_I18N::plural('Password - Use at least %s character.', 'Password - Use at least %s characters.', KT_I18N::number(KT_MINIMUM_PASSWORD_LENGTH), KT_I18N::number(KT_MINIMUM_PASSWORD_LENGTH)); ?>"
							required
							pattern="<?php echo KT_REGEX_PASSWORD; ?>"
							onchange="form.user_password02.pattern = regex_quote(this.value);"
							aria-describedby="password01"
						>
					</div>
					<p id="password01" class="help-text"></p>
					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-key"></i>
						</span>
						<input class="input-group-field" type="password" id="user_password02" name="user_password02" placeholder="<?php echo KT_I18N::translate('Confirm password'); ?>" required aria-describedby="password_confirm" value="<?php echo htmlspecialchars($user_password02); ?>" pattern="<?php echo KT_REGEX_PASSWORD; ?>">
				    </div>
					<p id="password_confirm" class="help-text"></p>
					<div class="input-group">
						<span class="input-group-label">
							<i class="fas fa-comment"></i>
						</span>
						<textarea cols="50" rows="5" id="user_comments" name="user_comments" placeholder="<?php echo /* I18N: placeholder text for registration-comments field */ KT_I18N::translate('Explain why you are requesting an account.'); ?>" required aria-describedby="register_comments"><?php echo htmlspecialchars($user_comments); ?></textarea>
					</div>
					<p id="register_comments" class="help-text"></p>
					<p><input type="submit" class="button expanded" value="<?php echo KT_I18N::translate('Register'); ?>"></p>
				</form>
			</div>
		</div>
	</div>
	<?php break;

case 'userverify':
	if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
		exit;
	}

	// Change to the new user’s language
	$user_id = get_user_id($user_name);
	KT_I18N::init(get_user_setting($user_id, 'language'));

	$controller->setPageTitle(KT_I18N::translate('User verification'));
	$controller->pageHeader();
	?>

	<div id="login-register-page" class="row align-center">
		<form id="verify-form" name="verify-form" method="post" action="<?php echo KT_LOGIN_URL; ?>">
			<input type="hidden" name="action" value="verify_hash">
			<h4><?php echo KT_I18N::translate('User verification'); ?></h4>
			<div>
				<label for="username"><?php echo KT_I18N::translate('Username'); ?></label>
				<input type="text" id="username" name="user_name" value="<?php echo $user_name; ?>">
			</div>
			<div>
			<label for="user_password"><?php echo KT_I18N::translate('Password'); ?></label>
			<input type="password" id="user_password" name="user_password" value="" autofocus>
			</div>
			<div>
			<label for="user_hashcode"><?php echo KT_I18N::translate('Verification code:'); ?></label>
			<input type="text" id="user_hashcode" name="user_hashcode" value="<?php echo $user_hashcode; ?>">
			</div>
			<div>
				<input type="submit" value="<?php echo KT_I18N::translate('Send'); ?>">
			</div>
		</form>
	</div>
	<?php break;

case 'verify_hash':
	if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
		exit;
	}
	AddToLog('User attempted to verify hashcode: ', $user_name, 'auth');

	// switch language to webmaster settings
	$webmaster_user_id = get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
	KT_I18N::init(get_user_setting($webmaster_user_id, 'language'));

	$user_id = get_user_id($user_name);
	$mail1_body =
		KT_I18N::translate('Hello Administrator ...') . "\r\n\r\n".
		/* I18N: %1$s is a real-name, %2$s is a username, %3$s is an email address */
		KT_I18N::translate('A new user (%1$s) has requested an account (%2$s) and verified an email address (%3$s).', getUserFullName($user_id), $user_name, getUserEmail($user_id))."\r\n\r\n";
	if (!get_user_setting($user_id, 'verified_by_admin')) {
		$mail1_body .= KT_I18N::translate('You now need to review the account details, and set the “approved” status to “yes”.') . "\r\n";
	} else {
		$mail1_body .= KT_I18N::translate('You do not have to take any action; the user can now login.') . "\r\n";
	}
	if ($TEXT_DIRECTION == 'rtl') {
		$mail1_body .= "<a href=\"";
		$mail1_body .= KT_SERVER_NAME . KT_SCRIPT_PATH . "admin_users.php?filter=" . rawurlencode($user_name) . "\">";
	}
	$mail1_body .= KT_SERVER_NAME . KT_SCRIPT_PATH . "admin_users.php?filter=" . rawurlencode($user_name);
	if ($TEXT_DIRECTION == "rtl") {
		$mail1_body .= "</a>";
	}
	$mail1_body.=
		"\r\n\r\n".
		"=--------------------------------------=\r\n".
		"IP ADDRESS: ".$REQUEST->getClientIp()."\r\n".
		"DNS LOOKUP: ".gethostbyaddr($REQUEST->getClientIp())."\r\n".
		"LANGUAGE: ".LOCALE."\r\n";

	$mail1_to = $KIWITREES_EMAIL;
	$mail1_from = getUserEmail($user_id);
	$mail1_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('New user at %s', KT_SERVER_NAME . KT_SCRIPT_PATH . ' ' . strip_tags(KT_TREE_TITLE));
	$mail1_method = get_user_setting($webmaster_user_id, 'CONTACT_METHOD');

	// Change to the new user’s language
	KT_I18N::init(get_user_setting($user_id, 'language'));

	$controller->setPageTitle(KT_I18N::translate('User verification'));
	$controller->pageHeader();
	?>

	<div id="login-register-page" class="row align-center">
		<h4 class="text-center"><?php echo KT_I18N::translate('User verification'); ?></h4>
		<div id="user-verify">
			<?php echo KT_I18N::translate('The data for the user <b>%s</b> was checked.', $user_name);
			if ($user_id) {
				$pw_ok = check_user_password($user_id, $user_password);
				$hc_ok = get_user_setting($user_id, 'reg_hashcode') == $user_hashcode;
				if ($pw_ok && $hc_ok) {
					require_once KT_ROOT.'includes/functions/functions_mail.php';
					kiwiMail($mail1_to, $mail1_from, $mail1_subject, $mail1_body);
					if ($mail1_method != 'messaging3' && $mail1_method != 'mailto' && $mail1_method != 'none') {
						KT_DB::prepare("INSERT INTO `##message` (sender, ip_address, user_id, subject, body) VALUES (? ,? ,? ,? ,?)")
							->execute(array($user_name, $REQUEST->getClientIp(), $webmaster_user_id, $mail1_subject, $mail1_body));
					}

					set_user_setting($user_id, 'verified', 1);
					set_user_setting($user_id, 'pwrequested', null);
					set_user_setting($user_id, 'reg_timestamp', date("U"));
					set_user_setting($user_id, 'reg_hashcode', null);
					AddToLog('User verified: '.$user_name, 'auth');
					?>

					<p><?php echo KT_I18N::translate('You have confirmed your request to become a registered user.'); ?></p>
					<?php if (!get_user_setting($user_id, 'verified_by_admin')) {
						echo KT_I18N::translate('The Administrator has been informed. As soon as he gives you permission to login, you can login with your user name and password.');
					}
				} else { ?>
					<p><span class="warning"><?php echo KT_I18N::translate('Data was not correct, please try again'); ?></span></p>
				<?php }
			} else { ?>
				<p><span class="warning"><?php echo KT_I18N::translate('Could not verify the information you entered. Please try again or contact the site administrator for more information.'); ?></p>
			<?php } ?>
		</div>
	</div>
	<?php break;
}
