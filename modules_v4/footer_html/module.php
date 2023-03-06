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

class footer_html_KT_Module extends KT_Module implements KT_Module_Footer {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Footer HTML');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Home” module */ KT_I18N::translate('Create your own text in a footer block');
	}

	// Implement KT_Module_Footer
	public function defaultFooterOrder() {
		return 20;
	}

	// Extend class KT_Module_Footer
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Footer
	public function getFooter($block_id) {
		global $KT_TREE, $controller, $iconStyle;

		$id					= $this->getName() . $block_id;
		$class				= $this->getName();
		$config				= true;
		$title				= '<span dir="auto">' . KT_TREE_TITLE . '</span>';
		$default_header		= 'Footer block title';
		$default_content	= 'Footer block content';

		$content	= '
			<div id="footer-center-divider" class="card-divider">
				<h5>' . get_block_setting($block_id, 'header', $default_header) . '</h5>';
				if (KT_USER_GEDCOM_ADMIN && $config) {
					$content .= '<a class="configure" href="block_edit.php?block_id=' . $block_id . '&amp;ged=' . $KT_TREE->tree_name_url . '" title="' . KT_I18N::translate('Configure') . '">
						<i class="' . $iconStyle . ' fa-gears"></i>
					</a>';
				}
			$content .= '</div>
			<div id="footer-center-section" class="card-section">
				<p>' .
					get_block_setting($block_id, 'content', $default_content) . '
				</p>
			</div>
		';

		return $content;

	}

	// Implement class KT_Module_Footer
	public function loadAjax() {
		return false;
	}


	// Implement class KT_Module_Footer
	public function configureBlock($block_id) {
		global $controller;

		$default_header = 'Footer block title';
		$default_content = 'Footer block content';

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'num', KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($block_id, 'header', KT_Filter::post('header', '', ''));
			set_block_setting($block_id, 'content', KT_Filter::post('content', '', ''));
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

		$header		= get_block_setting($block_id, 'header', $default_header);
		$content	= get_block_setting($block_id, 'content', $default_content);
		$languages	= get_block_setting($block_id, 'languages');

		?>

		<div class="cell">
			 <label><?php echo KT_I18N::translate('Title for this footer block'); ?></label>
		</div>
		<div class="cell">
			<input type="text" name="header" value="<?php echo htmlspecialchars((string) $header); ?>">
		</div>
		<div class="cell">
			 <label><?php echo KT_I18N::translate('Content for this footer block'); ?></label>
		</div>
		<div class="cell">
			<textarea name="content" class="html-edit" rows="5"><?php echo htmlspecialchars((string) $content); ?></textarea>
		</div>
		<div class="cell medium-3">
			<label class="h6"><?php echo KT_I18N::translate('Show this block for which languages?'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<?php echo edit_language_checkboxes('lang_', $languages); ?>
		</div>

	<?php }
}
