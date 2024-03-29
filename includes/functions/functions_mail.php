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
 * Create message form.
 *
 * @param string $to
 * @param string $from_name
 * @param string $from_email
 * @param string $subject
 * @param string $body
 * @param string $url
 * @param string $to_names
 *
 * @return string
 */
function messageForm ($to, $from_name, $from_email, $subject, $body, $url, $to_names) {
	global $controller, $iconStyle;

	$controller->addInlineJavascript('
		jQuery("label[for=termsConditions]").parent().css({
			"opacity": "0",
			"position": "absolute",
			"left": "-2000px",
		});
	');


	$contact_user_id	= get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
	$webmaster_user_id	= get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
	$supportLink		= user_contact_link($webmaster_user_id);

	if ($webmaster_user_id == $contact_user_id) {
		$contactLink = $supportLink;
	} else {
		$contactLink = user_contact_link($contact_user_id);
	}

	if ((!$contact_user_id && !$webmaster_user_id) || (!$supportLink && !$contactLink) || $to) {
		$style = 0;
		$spacing = 'medium-6 medium-offset-3';
		$form_title_1 = '';
		$form_title_2 = '';
		$to_user_id_1 = get_user_id($to);
		$to_user_id_2 = '';
		$to_user_name_1 = $to;
		$to_user_name_2 = '';
		$to_user_fullname_1 = getUserFullName(get_user_id($to));
		$to_user_fullname_2 = '';
	} elseif (($supportLink == $contactLink) || ($contact_user_id == '') || ($webmaster_user_id == '')) {
		$style = 1;
		$spacing = 'medium-6 medium-offset-3';
		$to = ($contact_user_id == '' ? $webmaster_user_id : $contact_user_id);
		$form_title_1 = '<h4>' . KT_I18N::translate('For further information') . '</h4>';
		$form_title_2 = '';
		$to_user_id_1 = $to;
		$to_user_id_2 = '';
		$to_user_name_1 = get_user_name($to);
		$to_user_name_2 = '';
		$to_user_fullname_1 = KT_I18N::translate('Support');
		$to_user_fullname_2 = '';
	} else {
		$style = 2;
		$spacing = 'medium-5 medium-offset-1';
		$to_user_name = '';
		$form_title_1 = '<h5>' . KT_I18N::translate('For technical support and information') . '</h5>';
		$form_title_2 = '<h5>' . KT_I18N::translate('For help with genealogy questions') . '</h5>';
		$to_user_id_1 = $webmaster_user_id;
		$to_user_id_2 = $contact_user_id;
		$to_user_name_1 = get_user_name(get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID'));
		$to_user_name_2 = get_user_name($contact_user_id);
		$to_user_fullname_1 = KT_I18N::translate('Technical help');
		$to_user_fullname_2 = KT_I18N::translate('Genealogy help');
	}

	$sendTo = '';
	if (in_array($to, ['all', 'never_logged', 'last_6mo']) && KT_USER_IS_ADMIN) {
		$userGroup = $to;
		switch ($userGroup) {
			case 'all':
				$sendTo = KT_I18N::translate('To all users');
				break;
			case 'never_logged':
				$sendTo = KT_I18N::translate('To users who have never logged in');
				break;
			case 'last_6mo':
				$sendTo = KT_I18N::translate('To users who have not logged in for 6 months');
				break;
		}
	}

	if (KT_Site::preference('USE_RECAPTCHA') && !KT_USER_ID) { ?>
		<script src="https://www.google.com/recaptcha/api.js" async defer ></script>
	<?php } ?>

	<form name="messageform" method="post">
		<input type="hidden" name="url" value="<?php echo KT_Filter::escapeHtml($url); ?>">

		<div class="grid-x grid-margin-x" id="contact_header">
			<?php if (!KT_USER_ID) { ?>
				<div class="cell medium-10 medium-offset-1">
					<div class="callout small warning subheader">
						<?php echo KT_I18N::translate('<b>Please Note:</b> Private information of living individuals will only be given to family relatives and close friends. You will be asked to verify your relationship before you will receive any private data. Sometimes information of dead persons may also be private. If this is the case, it is because there is not enough information known about the person to determine whether they are alive or not and we probably do not have more information on this person.<br /><br />Before asking a question, please verify that you are inquiring about the correct person by checking dates, places, and close relatives. If you are submitting changes to the genealogical data, please include the sources where you obtained the data.'); ?>
					</div>
				</div>
				<div class="cell medium-6 medium-offset-3">
					<div class="grid-x">
						<div class="cell medium-2">
							<label for="from_name" class="h6"><?php echo KT_I18N::translate('Your name'); ?></label>
						</div>
						<div class="cell medium-10">
							<input type="text" name="from_name" id="from_name" value="<?php echo KT_Filter::escapeHtml($from_name); ?>" required>
						</div>
						<div class="cell medium-2">
							<label for="from_email" class="h6"><?php echo KT_I18N::translate('Your email address'); ?></label>
						</div>
						<div class="cell medium-10">
							<input type="email" name="from_email" id="from_email" value="<?php echo $from_email; ?>" required >
						</div>
						<div class="callout help-text">
							<span><?php echo KT_I18N::translate('Please provide your email address so that we may contact you in response to this message. If you do not provide your email address we will not be able to respond to your inquiry. Your email address will not be used in any other way besides responding to this inquiry.'); ?></span>
						</div>
					</div>
					<hr>
				</div>
			<?php }

			if ($style == 2) { ?>
				<div class="cell medium-8 medium-offset-2">
					<div class="cell callout info-help">
						<?php echo KT_I18N::translate('Please use the appropriate form from the two below. That way your query will reach the best person to reply.'); ?>
					</div>
					<hr>
				</div>
			<?php } ?>
		</div>

		<div class="grid-x grid-margin-x grid-margin-y" id="contact_body">
			<?php for ($i = 1; $i <= 2; $i++) { ?>
				<div class="cell <?php echo $spacing; ?>">
					<?php echo ${'form_title_' . $i}; ?>
					<div class="grid-x grid-margin-x grid-margin-y">
						<div class="cell medium-2">
							<label for="to_name" class="h6 middle"><?php echo KT_I18N::translate('To'); ?></label>
						</div>
						<div class="cell medium-10">
							<input type="text" name="to_name" id="to_name" value="<?php echo $sendTo ? $sendTo : ${'to_user_fullname_' . $i}; ?>">
							<input type="hidden" name="to" value="<?php echo KT_Filter::escapeHtml(${'to_user_name_' . $i}); ?>">
						</div>
						<div class="cell medium-2">
							<label for="subject" class="h6 middle"><?php echo KT_I18N::translate('Subject'); ?></label>
						</div>
						<div class="cell medium-10">
							<input type="text" name="subject" id="subject" value="<?php echo KT_Filter::escapeHtml($subject); ?>">
						</div>
						<div class="cell medium-2">
							<label for="body" class="h6 middle"><?php echo KT_I18N::translate('Body'); ?></label>
						</div>
						<div class="cell medium-10">
							<textarea class="html-edit" name="body" id="body"><?php echo KT_Filter::escapeHtml($body); ?></textarea>
						</div>

						<?php echo honeypot(); ?>
						<?php echo recaptcha(); ?>

						<div class="cell">
							<button class="button primary" type="submit">
								<i class="<?php echo $iconStyle; ?> fa-envelope"></i>
								<?php echo KT_I18N::translate('Send'); ?>
							</button>
							<button class="button secondary" type="button" onclick="window.location='<?php echo $url; ?>';">
								<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
								<?php echo KT_I18N::translate('Cancel'); ?>
							</button>
						</div>
						<?php if (in_array($to, ['all', 'never_logged', 'last_6mo']) && KT_USER_IS_ADMIN) { ?>
							<div class="cell userList">
								<div class="callout warning"><?php echo KT_I18N::translate('This message will be sent to the following users'); ?></div>
									<?php if (recipients($to)) {
										foreach (recipients($to) as $user_id => $user_name) { ?>
											<span><?php echo getUserFullName($user_id); ?></span>
										<?php }
									} else { ?>
										<span><?php echo KT_I18N::translate('No users'); ?></span>
									<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>

				<?php if ($style <= 1) {
					exit;
				}
			}

			if (KT_USER_ID && get_user_setting(KT_USER_ID, 'contactmethod') === 'messaging') { ?>
				<div class="cell medium-8 medium-offset-2">
					<div class="cell callout warning">
						<?php echo KT_I18N::translate('When you send this message you will receive a copy sent via email to the address you provided.'); ?>
					</div>
				</div>
			<?php } ?>

		</div>

	</form>

<?php }

function honeypot() {
	if (KT_Site::preference('USE_HONEYPOT') && !KT_USER_ID) {
		return '<div class="cell">
			<label for="termsConditions">' .
				/* I18N: for security protection only */ KT_I18N::translate('Confirm your agreement to our <a href="https://www.pandadoc.com/website-standard-terms-and-conditions-template/" >Terms and Conditions.') . '</a>
			</label>' .
			checkbox("termsConditions") .
		'</div>';
	}
}

function recaptcha() {
	if (KT_Site::preference('USE_RECAPTCHA') && !KT_USER_ID) {
		return '<div class="cell">
			<label>
				<div class="g-recaptcha" data-sitekey="' . KT_Site::preference('RECAPTCHA_SITE_KEY') . '" data-callback="recaptcha_callback"></div>
			</label>
		</div>';
	}
}

/**
 * Convert a username (or mailing list name) into an array of recipients.
 *
 * @param $to
 *
 * @return $recipients[]
 */
function recipients($to) {
	$recipients = [];
	if ($to === 'all') {
		$recipients = [];
		foreach (get_all_users() as $user_id=>$user_name) {
			$recipients[$user_id] = $user_name;
		}
	} elseif ($to === 'last_6mo') {
		$recipients = [];
		$sixmos  = 60 * 60 * 24 * 30 * 6; //-- timestamp for six months
		foreach (get_all_users() as $user_id=>$user_name) {
			if (get_user_setting($user_id,'sessiontime') > 0 && (KT_TIMESTAMP - get_user_setting($user_id,'sessiontime') > $sixmos)) {
				$recipients[$user_id] = $user_name;
			} elseif (!get_user_setting($user_id,'verified_by_admin') && (KT_TIMESTAMP - get_user_setting($user_id,'reg_timestamp') > $sixmos)) {
				//-- not verified by registration past 6 months
				$recipients[$user_id] = $user_name;
			}
		}
	} elseif ($to === 'never_logged') {
		$recipients = [];
		foreach (get_all_users() as $user_id=>$user_name) {
			if (get_user_setting($user_id,'verified_by_admin') && get_user_setting($user_id,'reg_timestamp') > get_user_setting($user_id,'sessiontime')) {
				$recipients[$user_id] = $user_name;
			}
		}
	} else {
		$recipients = array_filter(array(get_user_id($to)));
	}

	return $recipients;
}

/**
 * Send a message to a user's inbox via email.
 *
 * @param string[] $message
 *
 * @return bool
 */
function addMessage($message) {
	global $KT_TREE;

	//Set html formatting
	if (KT_Site::preference('MAIL_FORMAT')) {
		$bold_on	= '<strong>';
		$bold_off	= '</strong>';
		$line		= '<hr>';
		$page		= '<a href="' . $message['url'] . '">' . $message['url'] . '</a>';
	} else {
		$bold_on 	= '';
		$bold_off 	= '';
		$line		= '--------------------------------';
		$page		= $message['url'];
	}

	$success = true;

	$recipient = get_user_id($message['to']);

	// Sender may not be a Kiwitrees user
	if (KT_USER_ID) {
		$sender_email     = getUserEmail(KT_USER_ID);
		$sender_real_name = getUserFullName(KT_USER_ID);
	} else {
		$sender_email     = $message['from_email'];
		$sender_real_name = $message['from_name'];
	}

	// Send a copy of the message back to the sender.
		if (KT_USER_ID) {
			// Switch to the sender's language.
			KT_I18N::init(get_user_setting(KT_USER_ID, 'language'));
			// Message from a signed-in user
			$copy_email = KT_I18N::translate('You sent the following message to <b>%1$s</b> at %2$s:', getUserFullName($recipient), strip_tags(KT_TREE_TITLE));
		} else {
			// Message from a visitor
			$copy_email = KT_I18N::translate('You sent the following message to an administrator at %1$s:', strip_tags(KT_TREE_TITLE));
		}

		$copy_email .=
			KT_Mail::EOL .
			KT_Mail::EOL .
			$bold_on . KT_I18N::translate('From') . ':  ' . $bold_off . $sender_real_name . ' (' . $sender_email . ')' . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Subject') . ':  ' . $bold_off . $message['subject'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Content') . ':  ' . $bold_off . KT_Mail::EOL .
			$message['body'] . KT_Mail::EOL .
			$line . KT_Mail::EOL;

		if (!empty($message['url'])) {
			$copy_email .=  $bold_on . KT_I18N::translate('This message was sent while viewing the following URL: '). $bold_off . $page . KT_Mail::EOL;
		}

		$success = $success && KT_Mail::send(
			// From: header
			$KT_TREE,
			// To: header
			$sender_email,
			$sender_real_name,
			// Reply-To: header
			KT_Site::preference('SMTP_FROM_NAME'),
			$KT_TREE->tree_title,
			// Message subject
			KT_I18N::translate('%1$s message', strip_tags(KT_TREE_TITLE)) . ' - ' . $message['subject'],
			// Message content
			$copy_email
		);

	// Send the message to the recipient.
		// Switch to the recipient's language.
		KT_I18N::init(get_user_setting($recipient, 'language'));
		if (KT_USER_ID) {
			$original_email = /* I18N: %s is the family tree title */ KT_I18N::translate('%s sent you the following message.', $sender_real_name);
		} else {
			$original_email = /* I18N: %s is a person's name */ KT_I18N::translate('%s sent you the following message.', $message['from_name']);
		}

		$original_email .=
			KT_Mail::EOL .
			KT_Mail::EOL;
		if (KT_USER_ID) {
			$original_email .= $bold_on . KT_I18N::translate('From') . ':  ' . $bold_off . $sender_real_name . ' (' . $sender_email . ')' . KT_Mail::EOL;
		} else {
			$original_email .= $bold_on . KT_I18N::translate('From') . ':  ' . $bold_off . $sender_email . KT_Mail::EOL;
		}
		$original_email .=
			$bold_on . KT_I18N::translate('Subject') . ':  ' . $bold_off . $message['subject'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Content') . ':  ' . $bold_off . KT_Mail::EOL .
			$message['body'] . KT_Mail::EOL .
			$line . KT_Mail::EOL;

		// Add another footer - unless we are an admin
		if (!KT_USER_IS_ADMIN && !empty($message['url'])) {
			$original_email .= $bold_on . KT_I18N::translate('This message was sent while viewing the following URL: ') . $bold_off . $page . KT_Mail::EOL;
		}

		$success = $success && KT_Mail::send(
			// From: header
			$KT_TREE,
			// To: header
			getUserEmail($recipient),
			getUserFullName($recipient),
			// Reply-To: header
			$sender_email,
			$sender_real_name,
			// Message subject
			KT_I18N::translate('%1$s message', strip_tags(KT_TREE_TITLE)) . ' - ' . $message['subject'],
			// Message content
			$original_email
		);

	KT_I18N::init(KT_LOCALE); // restore language settings if needed

	return $success;
}
