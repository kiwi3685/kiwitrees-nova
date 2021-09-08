<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class block_welcome_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Welcome');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Home” module */ KT_I18N::translate('A greeting message and key links for site visitors.');
	}

	// Extend class KT_Module_Block
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $controller, $iconStyle;

		$indi_xref		= $controller->getSignificantIndividual()->getXref();
		$id				= $this->getName() . $block_id;
		$class			= $this->getName();
		$config			= true;
		$title			= '<span dir="auto">' . KT_TREE_TITLE . '</span>';
		$default_text	= '<strong>' . KT_I18N::translate('Welcome to our family tree.') . '</strong>';

		$content	= '
			<div class="grid-x">
				<div class="cell">
					<p>' .
						get_block_setting($block_id, 'text', $default_text) . '
					</p>
				</div>
				<div class="cell small-4 text-center">
					<a href="pedigree.php?rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL . '">
						<i class="' . $iconStyle . ' fa-sitemap fa-2x"></i>
						<p>' . KT_I18N::translate('Default chart') . '</p>
					</a>
				</div>
				<div class="cell small-4 text-center">
					<a href="individual.php?pid=' . $indi_xref . '&amp;ged=' . KT_GEDURL . '">
						<i class="' . $iconStyle . ' fa-street-view fa-2x"></i>
						<p>' . KT_I18N::translate('Default individual') . '</p>
					</a>
				</div>';
				if (KT_Site::preference('USE_REGISTRATION_MODULE') && !KT_USER_ID) {
					$content .= '
						<div class="cell small-4 text-center">
							<a href="' . KT_LOGIN_URL . '?action=register">
								<i class="' . $iconStyle . ' fa-user-plus fa-2x"></i>
								<p>' . KT_I18N::translate('Request new user account') . '</p>
							</a>
						</div>';
				}
			$content .= '</div>';

			if ($template) {
				if (get_block_location($block_id) === 'side') {
					require KT_THEME_DIR . 'templates/block_small_temp.php';
				} else {
					require KT_THEME_DIR . 'templates/block_main_temp.php';
				}
			} else {
				return $content;
			}

	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		global $controller;

		$default_text = '<strong>' . KT_I18N::translate('Welcome to our family tree.') . '</strong>';

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'num', KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($block_id, 'text', KT_Filter::post('text', '', ''));
			set_block_setting($block_id, 'block', KT_Filter::postBool('block'));
			$languages = array();
			foreach (KT_I18N::used_languages('name') as $code=>$name) {
				if (KT_Filter::postBool('lang_' . $code)) {
					$languages[] = $code;
				}
			}
			set_block_setting($block_id, 'languages', implode(',', $languages));
			exit;
		}

		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$num		= get_block_setting($block_id, 'num', 10);
		$text		= get_block_setting($block_id, 'text', $default_text);
		$block		= get_block_setting($block_id, 'block', false);
		$languages	= get_block_setting($block_id, 'languages');

		?>

		<div class="cell">
			 <label><?php echo KT_I18N::translate('Optional welcome text for this family tree'); ?></label>
		</div>
		<div class="cell">
			<textarea name="text" class="html-edit" rows="5"><?php echo htmlspecialchars($text); ?></textarea>
		</div>
		<div class="cell medium-3">
			<label class="h6"><?php echo KT_I18N::translate('Show this block for which languages?'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<?php echo edit_language_checkboxes('lang_', $languages); ?>
		</div>

	<?php }
}
