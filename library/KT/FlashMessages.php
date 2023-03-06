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

class KT_FlashMessages {
	public static function addMessage($text, $status = 'secondary') {
		$flash_messenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$message			= new \stdClass;
		$message->text		= $text;
		$message->status	= $status;

		$flash_messenger->addMessage($message);
	}

	public static function getMessages() {
		$flash_messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

		$messages = array();

		// Get messages from previous requests
		foreach ($flash_messenger->getMessages() as $message) {
			$messages[] = $message;
		}

		// Get messages from the current request
		foreach ($flash_messenger->getCurrentMessages() as $message) {
			$messages[] = $message;
		}
    	$flash_messenger->clearCurrentMessages();

		return $messages;
	}

	// Most themes will want a simple block of HTML to display
	public static function getHtmlMessages() {
		$html = '';

		foreach (self::getMessages() as $message) {
			$html .= '
				<div class="callout ' . $message->status . '"  data-closable>' .
					$message->text . '
					<button class="close-button" aria-label="Dismiss alert" type="button" data-close>
						<span aria-hidden="true">&times;</span>
					</button>
				</div>';
		}

		if ($html) {
			$html = '<div id="flash-messages">' . $html . '</div>';
		}

		return $html;
	}


}
