<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

class block_charts_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/block */ KT_I18N::translate('Charts');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Charts” module */ KT_I18N::translate('An alternative way to display charts.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $config = null) {
		global $KT_TREE, $PEDIGREE_FULL_DETAILS, $show_full, $bwidth, $bheight, $iconStyle;

		$PEDIGREE_ROOT_ID = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');

		$type		= get_block_setting($block_id, 'type', 'pedigree');
		$pid		= get_block_setting($block_id, 'pid', KT_USER_ID ? (KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);
		$block		= get_block_setting($block_id, 'block');

		if ($config) {
			foreach (array('details', 'type', 'pid', 'block') as $name) {
				if (array_key_exists($name, $config)) {
					$name = $config[$name];
				}
			}
		}

		// Override the request
		$_GET['rootid'] = $pid;

		$savePedigreeFullDetails = $PEDIGREE_FULL_DETAILS;
		$show_full = 0;
		$PEDIGREE_FULL_DETAILS = $show_full;

		$person = KT_Person::getInstance($pid);
		if (!$person) {
			$pid = $PEDIGREE_ROOT_ID;
			set_block_setting($block_id, 'pid', $pid);
			$person = KT_Person::getInstance($pid);
		}

		if ($type != 'treenav' && $person) {
			$controller = new KT_Controller_Hourglass($person->getXref(), 0, 3);
			$controller->setupJavascript();
		}

		$id		= $this->getName() . $block_id;
		$class	= $this->getName();
		$config	= true;
		$title	= KT_I18N::translate('Title required');

		if ($person) {
			switch($type) {
				case 'pedigree':
					$title = KT_I18N::translate('Pedigree of %s', $person->getFullName());
					break;
				case 'descendants':
					$title = KT_I18N::translate('Descendants of %s', $person->getFullName());
					break;
				case 'hourglass':
					$title = KT_I18N::translate('Hourglass chart of %s', $person->getFullName());
					break;
				case 'treenav':
					$title = KT_I18N::translate('Interactive tree of %s', $person->getFullName());
					break;
			}

			$content = '<div class="grid-x">
				<div class="cell">
					<table>
						<tr>';
							if ($type=='descendants' || $type=='hourglass') {
								$content .= '<td valign="middle">';
								ob_start();
								$controller->print_descendency($person, 1, false);
								$content .= ob_get_clean();
								$content .= '</td>';
							}
							if ($type=='pedigree' || $type=='hourglass') {
								//-- print out the root person
								if ($type != 'hourglass') {
									$content .= '<td valign="middle">';
									ob_start();
									print_pedigree_person($person);
									$content .= ob_get_clean();
									$content .= '</td>';
								}
								$content .= '<td valign="middle">';
								ob_start();
								$controller->print_person_pedigree($person, 1);
								$content .= ob_get_clean();
								$content .= '<td>';
							}
							if ($type == 'treenav') {
								require_once KT_MODULES_DIR . 'tree/module.php';
								require_once KT_MODULES_DIR . 'tree/class_treeview.php';
								$mod		= new tree_KT_Module;
								$tv			= new TreeView;
								$content	.= '<td>';

								$content .= '<script>jQuery("head").append(\'<link rel="stylesheet" href="'.$mod->css().'" type="text/css" />\');</script>';
								$content .= '<script src="' . $mod->js() . '"></script>';
						    	list($html, $js) = $tv->drawViewport($person, 2);
								$content .= $html.'<script>'.$js.'</script>';
								$content .= '</td>';
							}
						$content .= '</tr>
					</table>
				</div>
			</div>';
		} else {
			$content =
				'<div class="callout alert">' .
					KT_I18N::translate('You must select an individual and chart type in the block configuration settings.') .
				'</div>';
		}

		if ($template) {
			if (get_block_location($block_id) === 'side') {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}

		// Restore GEDCOM configuration
		$PEDIGREE_FULL_DETAILS = $savePedigreeFullDetails;
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		global $controller, $iconStyle;
		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$PEDIGREE_ROOT_ID = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'details', KT_Filter::postBool('details'));
			set_block_setting($block_id, 'type',    KT_Filter::post('type', 'pedigree|descendants|hourglass|treenav', 'pedigree'));
			set_block_setting($block_id, 'pid',     KT_Filter::post('pid', KT_REGEX_XREF));
			exit;
		}

		$type	= get_block_setting($block_id, 'type',    'pedigree');
		$pid	= get_block_setting($block_id, 'pid', KT_USER_ID ? (KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);
		$root 	= KT_Person::getInstance($pid);
		$controller
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();
		');
		?>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Chart type'); ?></label>
		</div>
		<div class="cell medium-7">
			<select name="type">
				<option value="pedigree"<?php if ($type=="pedigree") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Pedigree'); ?></option>
				<option value="descendants"<?php if ($type=="descendants") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Descendants'); ?></option>
				<option value="hourglass"<?php if ($type=="hourglass") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Hourglass chart'); ?></option>
				<option value="treenav"<?php if ($type=="treenav") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Interactive tree'); ?></option>
			</select>
		</div>
		<hr>
		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Individual'); ?></label>
		</div>
		<div class="cell medium-7">
			<div class="input-group autocomplete_container">
				<input data-autocomplete-type="INDI" type="text" id="autocompleteInput-favIndi" value="<?php echo strip_tags($root->getLifespanName()); ?>" placeholder="<?php echo KT_I18N::translate('Individual name'); ?>">
				<span class="input-group-label">
					<button class="clearAutocomplete autocomplete_icon">
						<i class="<?php echo $iconStyle; ?> fa-times"></i>
					</button>
				</span>
			</div>
			<input type="hidden" id="selectedValue-indi" name="pid">
		</div>
		<hr>

	<?php }
}
