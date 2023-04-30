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

define('KT_SCRIPT_NAME', 'admin_message.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_mail.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller->setPageTitle(KT_I18N::translate('Broadcast message'));

$controller->addExternalJavascript(KT_CKEDITOR_CLASSIC);
if (KT_Site::preference('MAIL_FORMAT') == "1") {
	$controller->addInlineJavascript('ckeditorBasic();');
}

$Xurl = 'admin_users.php?action=messaging#';

// Send the message.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$to					= KT_Filter::post('to', null, KT_Filter::get('to'));
	$from_name			= KT_Filter::post('from_name');
	$from_email			= KT_Filter::post('from_email');
	$subject			= KT_Filter::post('subject', null, KT_Filter::get('subject'));
	$body				= KT_Filter::post('body');
	$url				= KT_Filter::postUrl('url', 'admin_users.php?action=messaging#');

	// Only an administration can use the distribution lists.
	$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);

	$recipients = recipients($to);
	$from_name  = getUserFullName(KT_USER_ID);
	$from_email = getUserEmail(KT_USER_ID);

	// No errors.  Send the message.
	foreach ($recipients as $recipient) {
		$message         		= array();
		$message['to']   		= $recipient;
		$message['from_name']	= $from_name;
		$message['from_email']	= $from_email;
		$message['subject']		= $subject;
		$message['body']		= nl2br($body, false);
		$message['url']			= $url;

		if (addMessage($message)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('The message was successfully sent to %s.', KT_Filter::escapeHtml($to)));
			AddToLog('Message sent FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'auth');
		} else {
			KT_FlashMessages::addMessage(KT_I18N::translate('The message was not sent.'));
			AddToLog('Unable to send a message. FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'error');
		}
	}

	return;

}

$to			= KT_Filter::post('to', null, KT_Filter::get('to'));
$from_name 	= KT_Filter::post('from_name');
$from_email	= KT_Filter::post('from_email');
$subject	= KT_Filter::post('subject', null, KT_Filter::get('subject'));
$body		= KT_Filter::post('body');
$url		= 'admin_users.php?action=messaging';


// Only an administrator can use the distribution lists.
$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);
$controller->pageHeader();

$to_names = implode(KT_I18N::$list_separator, array_map(function($user) { return getUserFullName($user); }, recipients($to))); ?>

<!-- Start page layout  -->
<?php echo pageStart('contact', $controller->getPageTitle()); ?>
	<?php echo messageForm ($to, $from_name, $from_email, $subject, $body, $url, $to_names); ?>
</div>

<?php
