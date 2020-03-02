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

define('KT_SCRIPT_NAME', 'admin_site_config.php');

global $iconStyle;

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Site configuration'));

// Lists of options for <select> controls.
$SMTP_SSL_OPTIONS = array(
	'none' => KT_I18N::translate('none'),
	/* I18N: Secure Sockets Layer - a secure communications protocol*/ 'ssl' => KT_I18N::translate('ssl'),
	/* I18N: Transport Layer Security - a secure communications protocol */ 'tls' => KT_I18N::translate('tls'),
);
$SMTP_ACTIVE_OPTIONS = array(
	'internal'=>KT_I18N::translate('Use PHP mail to send messages'),
	'external'=>KT_I18N::translate('Use SMTP to send messages'),
);
$WELCOME_TEXT_AUTH_MODE_OPTIONS = array(
	0 => KT_I18N::translate('No predefined text'),
	1 => KT_I18N::translate('Predefined text that states all users can request a user account'),
	2 => KT_I18N::translate('Predefined text that states admin will decide on each request for a user account'),
	3 => KT_I18N::translate('Predefined text that states only family members can request a user account'),
	4 => KT_I18N::translate('Choose user defined welcome text typed below'),
);

switch (KT_Filter::post('action')) {
	case 'update-site':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		$INDEX_DIRECTORY = KT_Filter::post('INDEX_DIRECTORY');
		if (substr($INDEX_DIRECTORY, -1) !== '/') {
			$INDEX_DIRECTORY .= '/';
		}
		if (KT_File::mkdir($INDEX_DIRECTORY)) {
			KT_Site::preference('INDEX_DIRECTORY', $INDEX_DIRECTORY);
		} else {
			KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', KT_Filter::escapeHtml($INDEX_DIRECTORY)));
		}
		KT_Site::preference('MEMORY_LIMIT',					KT_Filter::post('MEMORY_LIMIT'));
		KT_Site::preference('MAX_EXECUTION_TIME',			KT_Filter::post('MAX_EXECUTION_TIME'));
		KT_Site::preference('ALLOW_CHANGE_GEDCOM',			KT_Filter::postBool('ALLOW_CHANGE_GEDCOM'));
		KT_Site::preference('SESSION_TIME',					KT_Filter::post('SESSION_TIME'));
		KT_Site::preference('SERVER_URL',					KT_Filter::post('SERVER_URL'));
		KT_Site::preference('MAINTENANCE',					KT_Filter::postBool('MAINTENANCE'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#site');
		exit;
	case 'update-mail':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		KT_Site::preference('SMTP_ACTIVE',					KT_Filter::post('SMTP_ACTIVE'));
		KT_Site::preference('MAIL_FORMAT',					KT_Filter::postBool('MAIL_FORMAT'));
		KT_Site::preference('SMTP_FROM_NAME',				KT_Filter::post('SMTP_FROM_NAME'));
		KT_Site::preference('SMTP_HOST',					KT_Filter::post('SMTP_HOST'));
		KT_Site::preference('SMTP_PORT',					KT_Filter::post('SMTP_PORT'));
		KT_Site::preference('SMTP_AUTH',					KT_Filter::postBool('SMTP_AUTH'));
		KT_Site::preference('SMTP_AUTH_USER',				KT_Filter::post('SMTP_AUTH_USER'));
		KT_Site::preference('SMTP_SSL',						KT_Filter::post('SMTP_SSL'));
		KT_Site::preference('SMTP_HELO',					KT_Filter::post('SMTP_HELO'));
		if (KT_Filter::post('SMTP_AUTH_PASS')) {
			KT_Site::preference('SMTP_AUTH_PASS',			KT_Filter::post('SMTP_AUTH_PASS'));
		}
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#mail');
		exit;
		case 'update-login':
			if (!KT_Filter::checkCsrf()) {
				break;
			}
		KT_Site::preference('LOGIN_URL',					KT_Filter::post('LOGIN_URL'));
		KT_Site::preference('WELCOME_TEXT_AUTH_MODE',		KT_Filter::post('WELCOME_TEXT_AUTH_MODE'));
		KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' .		KT_LOCALE, KT_Filter::post('WELCOME_TEXT_AUTH_MODE_4'));
		KT_Site::preference('USE_REGISTRATION_MODULE',		KT_Filter::postBool('USE_REGISTRATION_MODULE'));
		KT_Site::preference('SHOW_REGISTER_CAUTION',		KT_Filter::postBool('SHOW_REGISTER_CAUTION'));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#login');
		exit;
	case 'update-spam':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		KT_Site::preference('USE_HONEYPOT',					KT_Filter::postBool('USE_HONEYPOT'));
		KT_Site::preference('USE_RECAPTCHA',				KT_Filter::postBool('USE_RECAPTCHA'));
		KT_Site::preference('RECAPTCHA_SITE_KEY',			KT_Filter::post('RECAPTCHA_SITE_KEY'));
		KT_Site::preference('RECAPTCHA_SECRET_KEY',			KT_Filter::post('RECAPTCHA_SECRET_KEY'));
		KT_Site::preference('VERIFY_DAYS',					KT_Filter::post('VERIFY_DAYS'));
		KT_Site::preference('REQUIRE_COMMENT',				KT_Filter::postBool('REQUIRE_COMMENT'));
		if (KT_Filter::post('BLOCKED_EMAIL_ADDRESS_LIST')) {
			$emails = explode(',', str_replace(array(' ', "\n", "\r"), '', KT_Filter::post('BLOCKED_EMAIL_ADDRESS_LIST')));
			foreach ($emails as $email) {
				if (!preg_match('/@(.+)/', $email, $match) || function_exists('checkdnsrr') && !checkdnsrr($match[1])) { ?>
					<script>
						var emailerror = "<?php echo KT_I18N::translate('You included one or more invalid email addresses.'); ?>";
						alert(emailerror);
					</script>
					<?php break 2;
				}
			}
			KT_Site::preference('BLOCKED_EMAIL_ADDRESS_LIST', str_replace(array(' ', "\n", "\r"), '', KT_Filter::post('BLOCKED_EMAIL_ADDRESS_LIST')));
		}
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#spam');
		exit;
	case 'update-lang':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		KT_Site::preference('LANGUAGES', implode(',',		KT_Filter::postArray('LANGUAGES')));
		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '#lang');
		exit;
}

$controller
	->pageHeader()
	->addInlineJavascript('
		var selectVal = jQuery("#smtp_select option:selected").val();
		if (selectVal == "external"){
			jQuery("#smtp_options").css({"display":"block"});
		} else {
			jQuery("#smtp_options").css({"display":"none"});
		};

		jQuery("#smtp_select").click("option", function() {
			var clickedOption = jQuery(this).val();
			if (clickedOption == "external") {
				jQuery("#smtp_options").css({"display":"block"});
			} else {
				jQuery("#smtp_options").css({"display":"none"});
			};
		});

		// set on load
		var selectRadio = jQuery("[id^=USE_RECAPTCHA]:checked").val();
		if (selectRadio == "1"){
			jQuery("#google_recaptcha_details").css({"display":"block"});
		} else {
			jQuery("#google_recaptcha_details").css({"display":"none"});
		};
		// reset on change
		jQuery("[id^=USE_RECAPTCHA]").on("change", function() {
			var clickedRadio = jQuery(this).val();
			if (clickedRadio == "1") {
				jQuery("#google_recaptcha_details").css({"display":"block"});
			} else {
				jQuery("#google_recaptcha_details").css({"display":"none"});
			};
		});
	');
?>

<div id="site_config" class="cell">
	<h4><?php echo KT_I18N::translate('Site configuration'); ?></h4>
	<ul id="site_admin_tabs" class="tabs" data-responsive-accordion-tabs="tabs small-accordion medium-tabs" data-deep-link="true">
		<li class="tabs-title is-active">
			<a href="#site" aria-selected="true"><?php echo KT_I18N::translate('Website settings'); ?></a>
		</li>
		<li class="tabs-title">
			<a href="#mail"><?php echo KT_I18N::translate('Mail configuration'); ?></a>
		</li>
		<li class="tabs-title">
			<a href="#login"><?php echo KT_I18N::translate('Login & registration'); ?></a>
		</li>
		<li class="tabs-title">
			<a href="#spam"><?php echo KT_I18N::translate('Anti-spam'); ?></a>
		</li>
		<li class="tabs-title">
			<a href="#lang"><?php echo KT_I18N::translate('Languages'); ?></a>
		</li>
	</ul>
	<div class="tabs-content" data-tabs-content="site_admin_tabs">
		<!-- Site configuration tab -->
		<div class="tabs-panel is-active" id="site">
			<form method="post" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#site" data-abide novalidate>
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="update-site">
				<div class="grid-x grid-margin-x">
					<div data-abide-error class="alert callout" style="display: none;">
						<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
					</div>
					<div class="cell large-3">
						<label for="data"><?php echo KT_I18N::translate('Data folder'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="text" id="data" name="INDEX_DIRECTORY" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('INDEX_DIRECTORY')); ?>" placeholder="data/" required>
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the "Data folder" site configuration setting. “Apache” is a software program. */ KT_I18N::translate('This folder will be used by kiwitrees to store media files, GEDCOM files, temporary files, etc. The default setting is “data/”.<br>These files may contain private data and should not be made available over the internet. To protect this private data kiwitrees uses an Apache configuration file (.htaccess) which blocks all access to this folder.<br>If your web-server does not support .htaccess files and you cannot restrict access to this folder then you can select another folder away from your web documents. If you select a different folder you must also move all files (except config.ini.php, index.php and .htaccess) from the existing folder to the new folder. The folder can be specified here in full (e.g. /home/user_name/kiwitrees_data/) or relative to the installation folder (e.g. ../../kiwitrees_data/).'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="memory"><?php echo KT_I18N::translate('Memory limit'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="text" id="memory" name="MEMORY_LIMIT" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('MEMORY_LIMIT')); ?>" pattern="[0-9]+[KMG]" placeholder="<?php echo get_cfg_var('memory_limit'); ?>" maxlength="255">
						<div class="cell helpcontent">
							<?php echo /* I18N: %s is an amount of memory, such as 32MB */ KT_I18N::translate('By default, your server allows scripts to use %s of memory.', get_cfg_var('memory_limit')); ?>
							<p>
								<?php echo KT_I18N::translate('You can request a higher or lower limit here, although the server may ignore this request.<br>If you leave this setting empty the default value will be used.'); ?>
							</p>
						</div>
					</div>
					<div class="cell large-3">
						<label for="time"><?php echo KT_I18N::translate('PHP time limit'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="text" id="time" name="MAX_EXECUTION_TIME" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('MAX_EXECUTION_TIME')); ?>" pattern="[0-9]*" placeholder="<?php echo get_cfg_var('max_execution_time') ?>" maxlength="255">
						<div class="cell helpcontent">
								<?php echo KT_I18N::plural(
									'By default, your server allows scripts to run for %s second.',
									'By default, your server allows scripts to run for %s seconds.',
									get_cfg_var('max_execution_time'), KT_I18N::number(get_cfg_var('max_execution_time'))
								); ?>
								<p>
									<?php echo KT_I18N::translate('You can request a higher or lower limit here, although the server may ignore this request.<br>If you leave this setting empty the default value will be used.'); ?>
								</p>
						</div>
					</div>
					<div class="cell large-3">
						<label for="gedcom"><?php echo KT_I18N::translate('Show list of family trees'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('ALLOW_CHANGE_GEDCOM', KT_Site::preference('ALLOW_CHANGE_GEDCOM')); ?>
						<div class="cell helpcontent space">
							<?php echo /* I18N: Help text for the “Show list of family trees” site configuration setting */ KT_I18N::translate('For sites with more than one family tree, this option will show the list of family trees in the main menu, the search pages, etc.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="session"><?php echo KT_I18N::translate('Session timeout'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="text" id="session" name="SESSION_TIME" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SESSION_TIME')); ?>" pattern="[0-9]*" placeholder="7200" maxlength="255">
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the “Session timeout” site configuration setting */ KT_I18N::translate('The time in seconds that a kiwitrees session remains active before requiring a login. The default is 7200, which is 2 hours.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="website"><?php echo KT_I18N::translate('Website URL'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo select_edit_control('SERVER_URL', array(KT_SERVER_NAME.KT_SCRIPT_PATH=>KT_SERVER_NAME.KT_SCRIPT_PATH), '', KT_Site::preference('SERVER_URL')); ?>
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the "Website URL" site configuration setting */ KT_I18N::translate('If your site can be reached using more than one URL such as <b>http://www.example.com/kiwitrees/</b> and <b>http://kiwitrees.example.com/</b> you can specify the preferred URL here. Requests for the other URLs will be redirected to the preferred one. <span class="warning">If not required, leave this field blank.</span>'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="maintenance"><?php echo KT_I18N::translate('Site maintenance'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('MAINTENANCE', KT_Site::preference('MAINTENANCE')); ?>
						<div class="cell helpcontent space">
							<?php echo KT_I18N::translate('Set this to <b>yes</b> to temporarily prevent anyone <u>except the site administrator</u> from accessing your site.'); ?>
						</div>
					</div>
				</div>
				<button type="submit" class="button">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</form>
		</div>
		<!-- Mail configuration tab -->
		<div class="tabs-panel" id="mail">
			<form method="post" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#mail" data-abide novalidate>
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="update-mail">
				<div class="grid-x grid-margin-x">
					<div data-abide-error class="cell callout alert " style="display: none;">
						<p><i class="<?php echo $iconStyle; ?> fa-exclamation-triangle"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are errors in your form.'); ?></p>
					</div>
					<div class="cell large-3">
						<label for="smtp"><?php echo KT_I18N::translate('Messages'); ?></label>
					</div>
					<div class="cell large-9">
						<select id="smtp_select" name="SMTP_ACTIVE">
							<?php foreach ($SMTP_ACTIVE_OPTIONS as $key=>$value) {
								echo '<option value="' . $key . '"';
								if (KT_Site::preference('SMTP_ACTIVE') == $key) echo ' selected="selected"';
								echo '>' . $value . '</option>';
							} ?>
						</select>
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the “Messages” site configuration setting */ KT_I18N::translate('Kiwitrees needs to send emails such as password reminders and site notifications. To do this it can use this server\'s built in PHP mail facility (which is not always available) or an external SMTP (mail-relay) service, for which you will need to provide the connection details.<br>Selecting SMTP will display additional configuration options below.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="smtp"><?php echo KT_I18N::translate('Send mail in HTML format'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('MAIL_FORMAT', KT_Site::preference('MAIL_FORMAT')); ?>
						<div class="cell helpcontent space">
							<?php echo /* I18N: Help text for the “Messages” site configuration setting */ KT_I18N::translate('By default kiwitrees sends emails in plain text format. Setting this option to <b>yes</b> will change that to the multipart format. This allows the use of HTML formatting, but also includes a plain text version for recipients that do not allow HTML formatted emails.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="sender"><?php echo KT_I18N::translate('Sender email'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="email" id="sender" name="SMTP_FROM_NAME" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_FROM_NAME')); ?>" placeholder="admin@mydomain.com" maxlength="255" required pattern="email">
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the “Sender name” site configuration setting */ KT_I18N::translate('This name is used in the “From” field, when sending automatic emails from this server. It must be a valid email address.'); ?>
						</div>
					</div>
					<!-- SMTP SECTION -->
					<div id="smtp_options" class="cell" style="display:none;">
						<h5 class="accepted"><?php echo KT_I18N::translate('SMTP mail server settings'); ?></h5>
						<div class="grid-x grid-margin-x">
							<div class="cell large-3">
								<label for="server"><?php echo KT_I18N::translate('Server name'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="server" name="SMTP_HOST" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_HOST')); ?>" placeholder="smtp.example.com" pattern="[a-z0-9-]+(\.[a-z0-9-]+)*" maxlength="255">
								<div class="cell helpcontent">
									<?php echo /* I18N: Help text for the “Server name” site configuration setting */ KT_I18N::translate('This is the name of the SMTP server. \'localhost\' means that the mail service is running on the same computer as your web server.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="port"><?php echo KT_I18N::translate('Port number'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="port" name="SMTP_PORT" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_PORT')); ?>" placeholder="25" maxlength="5" required pattern="number">
								<div class="cell helpcontent">
									<?php echo /* I18N: Help text for the "Port number" site configuration setting */ KT_I18N::translate('By default SMTP works on port 25.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="use_password"><?php echo KT_I18N::translate('Use password'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo edit_field_yes_no('SMTP_AUTH', KT_Site::preference('SMTP_AUTH')); ?>
								<div class="cell helpcontent space">
									<?php echo /* I18N: Help text for the “Use password” site configuration setting */ KT_I18N::translate('Most SMTP servers require a password.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="username"><?php echo KT_I18N::translate('Username'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="username" name="SMTP_AUTH_USER" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_AUTH_USER')); ?>">
								<div class="cell helpcontent">
									<?php echo KT_I18N::translate('The user name required for authentication with the SMTP server.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="password"><?php echo KT_I18N::translate('Password'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="password" name="SMTP_AUTH_PASS" value="">
								<div class="cell helpcontent">
									<?php echo KT_I18N::translate('The password required for authentication with the SMTP server.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="secure"><?php echo KT_I18N::translate('Secure connection'); ?></label>
							</div>
							<div class="cell large-9">
								<?php echo select_edit_control('SMTP_SSL', $SMTP_SSL_OPTIONS, null, KT_Site::preference('SMTP_SSL')); ?>
								<div class="cell helpcontent">
									<?php echo /* I18N: Help text for the "Secure connection" site configuration setting */ KT_I18N::translate('Most servers do not use secure connections.'); ?>
								</div>
							</div>
							<div class="cell large-3">
								<label for="sending"><?php echo KT_I18N::translate('Sending server name'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" id="sending" name="SMTP_HELO" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_HELO')); ?>" placeholder="abc.com" required pattern="domain" maxlength="255">
								<div class="cell helpcontent">
									<?php echo /* I18N: Help text for the “Sending server name” site configuration setting */ KT_I18N::translate('Many mail servers require that the sending server identifies itself correctly, using a valid domain name.'); ?>
								</div>
							</div>
							<div class="callout medium-offset-3 medium-8 primary">
								<i class="fab fa-google" style="color: #2790fd; font-size: 1rem; font-weight: 900;"></i>
								<?php echo KT_I18N::translate('To use a Google mail account, use the following settings: server=smtp.gmail.com, port=587, security=tls, username=xxxxx@gmail.com, password=[your gmail password]. You must also enable “less secure applications” in your Google account. <a href="https://support.google.com/a/answer/6260879">https://support.google.com/a/answer/6260879</a>'); ?>
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
		<!-- Login configuration tab -->
		<div class="tabs-panel" id="login">
			<form method="post" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#login" data-abide novalidate>
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="update-login">
				<div class="grid-x grid-margin-x">
					<div data-abide-error class="alert callout" style="display: none;">
						<p><i class="fi-alert"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are some errors in your form.'); ?></p>
					</div>
					<div class="cell large-3">
						<label for="loginurl"><?php echo KT_I18N::translate('Login URL'); ?></label>
					</div>
					<div class="cell large-9">
						<input type="text" id="loginurl" name="LOGIN_URL" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('LOGIN_URL')); ?>" maxlength="255">
						<div class="cell helpcontent">
							<?php echo /* I18N: Help text for the “Login URL” site configuration setting */ KT_I18N::translate('You only need to enter a Login URL if you want to redirect to a different site or location when your users login. This is very useful if you need to switch from http to https when your users login. Include the full URL to <i>login.php</i>. For example, https://www.yourserver.com/kiwitrees/login.php .'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="welcome"><?php echo KT_I18N::translate('Welcome text on login page'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo select_edit_control('WELCOME_TEXT_AUTH_MODE', $WELCOME_TEXT_AUTH_MODE_OPTIONS, null, KT_Site::preference('WELCOME_TEXT_AUTH_MODE')); ?>
						<div class="cell helpcontent">
							<?php echo /* I18N: Explanation for custom welcome text (1) */ KT_I18N::translate('Here you can choose text to appear on the login page. You must determine which predefined text is most appropriate. You can also choose to enter your own custom welcome text.<br><br>Please refer to the Help text associated with the <b>Custom welcome text</b> field for more information.<br>The predefined texts are below.'); ?>
							<br><br>
							<?php echo /* I18N: Explanation for custom welcome text (2) */ KT_I18N::translate('<b>Predefined text that states all users can request a user account:</b><div class="callout secondary"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to every visitor who has a user account.<br>If you have a user account, you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your application, the site administrator will activate your account. You will receive an email when your application has been approved.</center></div>.'); ?>
							<br><br>
							<?php echo /* I18N: Explanation for custom welcome text (2) */ KT_I18N::translate('<b>Predefined text that states admin will decide on each request for a user account:</b><div class="callout secondary"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>authorized</u> users only.<br>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your information, the administrator will either approve or decline your account application. You will receive an email message when your application has been approved.</center></div>.'); ?>
							<br><br>
							<?php echo /* I18N: Explanation for custom welcome text (2) */ KT_I18N::translate('<b>Predefined text that states only family members can request a user account:</b><div class="callout secondary"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>family members only</u>.<br>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying the information you provide, the administrator will either approve or decline your request for an account. You will receive an email when your request is approved.</center></div>'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="custom"><?php echo KT_I18N::translate('Custom welcome text'); ?></label>
					</div>
					<div class="cell large-9">
						<textarea maxlength="2000" id="custom" name="WELCOME_TEXT_AUTH_MODE_4" rows="4"><?php echo KT_Filter::escapeHtml(KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE)) ?></textarea>
						<div class="cell helpcontent">
							<?php echo KT_I18N::translate('If you have opted for custom welcome text, you can type that text here. To set this text for other languages you must switch to that language and visit this page again.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="USE_REGISTRATION_MODULE"><?php echo KT_I18N::translate('Allow visitors to request account registration'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('USE_REGISTRATION_MODULE', KT_Site::preference('USE_REGISTRATION_MODULE')); ?>
						<div class="cell helpcontent space">
							<?php echo KT_I18N::translate('Gives visitors the option of registering themselves for an account on the site. The visitor will receive an email message with a code to verify their application for an account. After verification the Administrator will have to approve the registration before it becomes active.'); ?>
						</div>
					</div>
					<div class="cell large-3">
						<label for="SHOW_REGISTER_CAUTION"><?php echo KT_I18N::translate('Show acceptable use agreement<br>on "Request new user account" page'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('SHOW_REGISTER_CAUTION', KT_Site::preference('SHOW_REGISTER_CAUTION')); ?>
						<div class="cell helpcontent shortcontent space">
							<?php echo KT_I18N::translate('When set to <b>Yes</b>, the following message will appear above the input fields on the "Request new user account" page:<div class="callout secondary"><b>Notice:</b><br>By completing and submitting this form, you agree:</p><ul><li>to protect the privacy of living people listed on our site;</li><li>and in the text box below, to explain  who you are related to, or to provide us with information on someone who should be listed on our site.</li></ul></div>'); ?>
						</div>
					</div>
				</div>
				<button type="submit" class="button">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</form>
		</div>
		<!-- Anti spam configuration tab -->
		<div class="tabs-panel" id="spam">
			<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/administration/site_admin/anti-spam/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>">
				<?php echo KT_I18N::translate('View FAQ for this page.'); ?>
				<i class="fa fa-comments"></i>
			</a>
			<form method="post" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#spam" data-abide novalidate>
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="update-spam">
				<div class="grid-x grid-margin-x">
					<div data-abide-error class="cell callout alert " style="display: none;">
						<p><i class="<?php echo $iconStyle; ?> fa-exclamation-triangle"></i><?php echo /* I18N: A general error message for forms */ KT_I18N::translate('There are errors in your form.'); ?></p>
					</div>
					<div class="cell large-3">
						<label for="USE_HONEYPOT"><?php echo KT_I18N::translate('Use secret field'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('USE_HONEYPOT', KT_Site::preference('USE_HONEYPOT')); ?>
						<div class="cell helpcontent">
							<?php echo  /* I18N: Help text for the “honeypot” site configuration setting */ KT_I18N::translate('This will create a secret field that only internet robots will see and complete. If they do, then their entry will be ignored.'); ?>
						</div>
					</div>
					<div id="recaptcha_select" class="cell large-3">
						<label for="USE_RECAPTCHA"><?php echo KT_I18N::translate('Use Google reCAPTCHA v2'); ?></label>
					</div>
					<div class="cell large-9">
						<?php echo edit_field_yes_no('USE_RECAPTCHA', KT_Site::preference('USE_RECAPTCHA')); ?>
						<div class="cell helpcontent">
							<?php echo  /* I18N: Help text for the recaptcha site configuration setting */ KT_I18N::translate('This can help limit the number of spam attempts to register on your site.<br>It requires a pair of Google reCaptcha v2 API keys. Help to obtain this can be found on this kiwitrees FAQ page: <a href="<>php echo KT_KIWITREES_URL; ?>/faqs/administration/site_admin/" target="_blank">Google reCaptcha v2</a>'); ?>
						</div>
					</div>
					<div id="google_recaptcha_details"  class="cell" style="display:none;">
						<div class="grid-x grid-margin-x">
							<div class="cell large-3">
								<label><?php echo KT_I18N::translate('Google reCAPTCHA Site Key'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" name="RECAPTCHA_SITE_KEY" value="<?php echo KT_Site::preference('RECAPTCHA_SITE_KEY'); ?>" size="50">
							</div>
							<div class="cell large-3">
								<label><?php echo KT_I18N::translate('Google reCAPTCHA Secret Key'); ?></label>
							</div>
							<div class="cell large-9">
								<input type="text" name="RECAPTCHA_SECRET_KEY" value="<?php echo KT_Site::preference('RECAPTCHA_SECRET_KEY'); ?>" size="50">
							</div>
						</div>
					</div>
					<div class="cell large-3">
						<label><?php echo KT_I18N::translate('Days allowed for new user to verify email address'); ?></label>
					</div>
					<div class="cell large-9">
							<input type="text" name="VERIFY_DAYS" value="<?php echo KT_Site::preference('VERIFY_DAYS'); ?>" pattern="[0-9]*" placeholder="7" maxlength="3">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Days allowed to verify” site configuration setting */ KT_I18N::translate('The number of days a new user has to verify their email address before their request to register is highlighted as an error'); ?>
							</div>
					</div>
					<div class="cell large-3">
						<label><?php echo KT_I18N::translate('Require comment on registration form entries'); ?></label>
					</div>
					<div class="cell large-9">
							<?php echo edit_field_yes_no('REQUIRE_COMMENT', KT_Site::preference('REQUIRE_COMMENT')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Require all new registrations to enter a comment in the "Comments" field'); ?>
							</div>
					</div>
					<div class="cell large-3">
						<label for="blocked"><?php echo KT_I18N::translate('Blocked email address list'); ?></label>
					</div>
					<div class="cell large-9">
						<?php
							$blockedEmails = KT_Site::preference('BLOCKED_EMAIL_ADDRESS_LIST');
							if (!$blockedEmails) {
								$blockedEmails = 'youremail@gmail.com';
							}
						?>
						<textarea id="BLOCKED_EMAIL_ADDRESS_LIST" name="BLOCKED_EMAIL_ADDRESS_LIST" rows="3"><?php echo $blockedEmails; ?></textarea>
						<div class="cell helpcontent">
							<?php echo KT_I18N::translate('Add email addresses to this list to prevent them being used to register on this site. Separate each address with a comma. Whenever a visitor tries to use one of these addresses to register, their attempt will be ignored and a message added to the site error log.'); ?>
						</div>
					</div>
				</div>
				<button type="submit" class="button">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</form>
		</div>
		<!-- Languages configuration tab -->
		<div class="tabs-panel" id="lang">
			<form method="post" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>#lang">
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="update-lang">
				<div class="grid-x grid-margin-x">
					<div class="cell">
						<h4><?php echo KT_I18N::translate('Select the languages your site will use'); ?></h4>
						<h5>
							<?php echo KT_I18N::translate('Select all'); ?>
							<input type="checkbox" onclick="toggle_select(this)" >
						</h5>
						<?php
						$code_list = KT_Site::preference('LANGUAGES');
						if ($code_list) {
							$languages = explode(',', $code_list);
						} else {
							$languages = array(
								'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
								'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
								'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
							);
						}
						$installed = KT_I18N::installed_languages();
						// sort by localised name
						foreach ($installed as $code => $name) {
							$installed[$code] = KT_I18N::translate($name);
						}
						asort($installed);
						echo '<ul class="vertList">';
							foreach ($installed as $code=>$name) {
								echo '
									<li>
										<input class="check" type="checkbox" name="LANGUAGES[]" id="lang_' . $code . '"';
											if (in_array($code, $languages)) {
												echo 'checked="checked"';
											}
										echo ' value="' . $code . '">
										<label for="lang_' . $code . '"> '. KT_I18N::translate($name) . '</label>
									</li>
								';
							}
						echo '</ul>'
						?>
					</div>
				</div>
				<button type="submit" class="button">
					<i class="<?php echo $iconStyle; ?> fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</form>
		</div>
	</div>
</div>
