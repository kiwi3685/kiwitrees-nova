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

class KT_Controller_Base {
	// The controller accumulates Javascript (inline and external), and renders it in the footer
	const JS_PRIORITY_HIGH   = 0;
	const JS_PRIORITY_NORMAL = 1;
	const JS_PRIORITY_LOW    = 2;
	private $inline_javascript = array(
		self::JS_PRIORITY_HIGH   => array(),
		self::JS_PRIORITY_NORMAL => array(),
		self::JS_PRIORITY_LOW    => array(),
	);
	private $external_javascript = array();
	private $external_stylesheet = array();

	protected $page_header = false; // Have we printed a page header?

	// Startup activity
	public function __construct() {
	}

	// Shutdown activity
	public function __destruct() {
		// If we printed a header, automatically print a footer
		if ($this->page_header) {
			$this->pageFooter();
		}
	}

	// Make a list of external Javascript, so we can render them in the footer
	public function addExternalJavascript($script_name) {
		$this->external_javascript[$script_name] = true;
		return $this;
	}

	// Make a list of external stylesheets, so we can render them in the header
	public function addExternalStylesheet($stylesheet, $attributes = array()) {
		$this->external_stylesheet[$stylesheet] = $attributes;
		return $this;
	}

	// Make a list of inline Javascript, so we can render them in the footer
	// NOTE: there is no need to use "jQuery(document).ready(function(){...})", etc.
	// as this Javascript won't be inserted until the very end of the page.
	public function addInlineJavascript($script, $priority = self::JS_PRIORITY_NORMAL) {
		$tmp	= &$this->inline_javascript[$priority];
		$tmp[]	= $script;
		return $this;
	}

	// We've collected up Javascript fragments while rendering the page.
	// Now display them in order.
	public function getJavascript() {
		$noscript = '
			<noscript>
				<input type="checkbox" class="modal-closing-trick" id="modal-closing-trick">
			    <div class="noscript-overlay"></div>
			    <div class="noscript_modal">' .
					/* I18N: Warning about missing JavaScript. Note capitalisation of JavaScript */ KT_I18N::translate('<h2>Kiwitrees doesn\'t work well without JavaScript, and in your browser it is missing, broken, or disabled.</h2>
					<p>If you want to know how to enable it click <a href="http://www.enable-javascript.com/" target="_blank">here</a></p>') . '
					<label class="button" for="modal-closing-trick">' .
						KT_I18N::translate('Close this message') . '
					</label>
				</div>
			</noscript>
			<script>
				window.FontAwesomeConfig = {
					searchPseudoElements: true
				}
			</script>
		';
		$javascript1 = '';
		$javascript2 = '';
		$javascript3 = '';

		// Inline (high priority) javascript
		foreach ($this->inline_javascript[self::JS_PRIORITY_HIGH] as $script) {
			$javascript1 .= $script;
		}

		// External javascript
		foreach (array_keys($this->external_javascript) as $script_name) {
			$javascript2 .= '<script src="' . $script_name . '"></script>';
		}

		// Inline (lower priority) javascript
		if ($this->inline_javascript) {
			foreach ($this->inline_javascript as $priority => $scripts) {
				if ($priority !== self::JS_PRIORITY_HIGH) {
					foreach ($scripts as $script) {
						$javascript3 .= $script;
					}
				}
			}
		}

		// We could, in theory, inject JS at any point in the page (not just the bottom) - prepare for next time
		$this->inline_javascript = array(
			self::JS_PRIORITY_HIGH  => array(),
			self::JS_PRIORITY_NORMAL=> array(),
			self::JS_PRIORITY_LOW   => array(),
		);
		$this->external_javascript = array();

		return $noscript . '<script>' . $javascript1 . '</script>' . $javascript2 . '<script>' . $javascript3 . '</script>';
	}

	// Print the page header, using the theme
	public function pageHeader() {
		// Once we've displayed the header, we should no longer write session data.
		Zend_Session::writeClose();

		// We've displayed the header - display the footer automatically
		$this->page_header = true;
		return $this;
	}

	// Print the page footer, using the theme
	protected function pageFooter() {
		if (KT_DEBUG_SQL) {
			echo KT_DB::getQueryLog();
		}
		echo $this->getJavascript();
	}
}
