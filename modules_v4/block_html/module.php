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

class block_html_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('HTML');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “HTML” module */ KT_I18N::translate('Add your own text and graphics.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $GEDCOM, $iconStyle;

		// Only show this block for certain languages
		$languages = get_block_setting($block_id, 'languages');
		if ($languages && !in_array(KT_LOCALE, explode(',', $languages))) {
			return;
		}

		/*
		* Select GEDCOM
		*/
		$gedcom = get_block_setting($block_id, 'gedcom');
		switch ($gedcom) {
		case '__current__':
			break;
		case '':
			break;
		case '__default__':
			$GEDCOM = KT_Site::preference('DEFAULT_GEDCOM');
			if (!$GEDCOM) {
				foreach (KT_Tree::getAll() as $tree) {
					$GEDCOM = $tree->tree_name;
					break;
				}
			}
			break;
		default:
			$GEDCOM = $gedcom;
			break;
		}

		/*
		* Retrieve text, process embedded variables
		*/
		$title_tmp	= get_block_setting($block_id, 'title');
		$html		= get_block_setting($block_id, 'html');

		if ( (strpos($title_tmp, '#') !== false) || (strpos($html, '#') !== false) ) {
			$stats		= new KT_Stats($GEDCOM);
			$title_tmp	= $stats->embedTags($title_tmp);
			$html		= $stats->embedTags($html);
		}

		/*
		* Restore Current GEDCOM
		*/
		$GEDCOM = KT_GEDCOM;

		/*
		* Start Of Output
		*/
		$id			= $this->getName() . $block_id;
		$class		= $this->getName();
		$title		= $title_tmp;
		$config		= true;

		if (get_block_setting($block_id, 'show_timestamp', false)) {
			'<p class="timestamp">' . format_timestamp(get_block_setting($block_id, 'timestamp', KT_TIMESTAMP)) . '</p>';
		}

		$content = '
			<div class="grid-x grid-padding-x">
				<div class="cell">' .
					$html . '
				</div>
			</div>
		';

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

	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'gedcom',         KT_Filter::post('gedcom'));
			set_block_setting($block_id, 'title',          KT_Filter::post('title'));
			set_block_setting($block_id, 'html',           KT_Filter::post('html'));
			set_block_setting($block_id, 'show_timestamp', KT_Filter::postBool('show_timestamp'));
			set_block_setting($block_id, 'timestamp',      KT_Filter::post('timestamp'));
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

		// The CK editor needs lots of help to load/save data :-(
		if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
			$ckeditor_onchange='CKEDITOR.instances.html.setData(document.block.html.value);';
		} else {
			$ckeditor_onchange='';
		}

		$templates = array(
			KT_I18N::translate('Keyword examples') =>
			'#getAllTagsTable#',

			KT_I18N::translate('Narrative description') =>
			/* I18N: do not translate the #keywords# */ KT_I18N::translate('This GEDCOM (family tree) was last updated on #gedcomUpdated#. There are #totalSurnames# surnames in this family tree. The earliest recorded event is the #firstEventType# of #firstEventName# in #firstEventYear#. The most recent event is the #lastEventType# of #lastEventName# in #lastEventYear#.<br /><br />If you have any comments or feedback please contact #contactWebmaster#.'),

			KT_I18N::translate('Statistics') =>
			'<div class="gedcom_stats grid-x">
				<h5><a href="index.php?command=gedcom">#gedcomTitle#</a></h5>
				<div class="small-12 callout secondary small">' .
					KT_I18N::translate('This family tree was last updated on %s.', '#gedcomUpdated#') . '
				</div>
				<table id="keywords">
					<tr>
						<td valign="top">
							<table>
								<tr>
									<td>' . KT_I18N::translate('Individuals') . '</td>
									<td><a href="indilist.php?surname_sublist=no">#totalIndividuals#</a></td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Males') . '</td>
									<td>#totalSexMales#<br>#totalSexMalesPercentage#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Females') . '</td>
									<td>#totalSexFemales#<br>#totalSexFemalesPercentage#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Total surnames') . '</td>
									<td><a href="indilist.php?show_all=yes&amp;surname_sublist=yes&amp;ged=' . KT_GEDURL . '">#totalSurnames#</a></td>
								</tr>
								<tr>
									<td>'. KT_I18N::translate('Families') . '</td>
									<td><a href="famlist.php?ged=' . KT_GEDURL . '">#totalFamilies#</a></td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Sources') . '</td>
									<td><a href="sourcelist.php?ged=' . KT_GEDURL . '">#totalSources#</a></td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Media objects') . '</td>
									<td><a href="medialist.php?ged=' . KT_GEDURL . '">#totalMedia#</a></td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Repositories') . '</td>
									<td><a href="repolist.php?ged=' . KT_GEDURL . '">#totalRepositories#</a></td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Total events') . '</td>
									<td>#totalEvents#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Total users') . '</td>
									<td>#totalUsers#</td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<table>
								<tr>
									<td>' . KT_I18N::translate('Earliest birth year') . '</td>
									<td>#firstBirthYear#</td>
									<td>#firstBirth#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Latest birth year') . '</td>
									<td>#lastBirthYear#</td>
									<td>#lastBirth#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Earliest death year') . '</td>
									<td>#firstDeathYear#</td>
									<td>#firstDeath#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Latest death year') . '</td>
									<td>#lastDeathYear#</td>
									<td>#lastDeath#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Person who lived the longest') . '</td>
									<td>#longestLifeAge#</td>
									<td>#longestLife#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Average age at death') . '</td>
									<td>#averageLifespan#</td>
									<td>
										<div>' . KT_I18N::translate('Males') . ':&nbsp;#averageLifespanMale#</div>
										<div>' . KT_I18N::translate('Females') . ':&nbsp;#averageLifespanFemale#</div>
									</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Family with the most children') . '</td>
									<td>#largestFamilySize#</td>
									<td>#largestFamily#</td>
								</tr>
								<tr>
									<td>' . KT_I18N::translate('Average number of children per family') . '</td>
									<td>#averageChildren#</td>
									<td></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<div class="small-12 callout secondary small">
					<h6 class="font-bold">' . KT_I18N::translate('Most Common Surnames') . '</h6>
					#commonSurnames#
				</div>
			</div>'
		);

		$title			= get_block_setting($block_id, 'title');
		$html			= get_block_setting($block_id, 'html');
		$gedcom			= get_block_setting($block_id, 'gedcom');
		$show_timestamp = get_block_setting($block_id, 'show_timestamp', 0);
		$languages		= get_block_setting($block_id, 'languages');
		?>

		<div class="cell medium-3">
			<label class="h6"><?php echo KT_Gedcom_Tag::getLabel('TITL'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
		</div>
		<div class="cell medium-3">
			<label class="h6"><?php echo KT_I18N::translate('Templates'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<select name="template" onchange="document.block.html.value=document.block.template.options[document.block.template.selectedIndex].value;<?php echo $ckeditor_onchange; ?>">
				<option value="<?php echo htmlspecialchars($html); ?>"><?php echo KT_I18N::translate('Custom'); ?></option>
				<?php foreach ($templates as $title => $template) { ?>
					<option value="<?php echo htmlspecialchars($template); ?>"><?php echo $title; ?></option>
				<?php } ?>
			</select>
		</div>
		<?php
		if (count(KT_Tree::getAll()) > 1) {
			if ($gedcom == '__current__') {
				$sel_current = ' selected="selected"';
			} else {
				$sel_current = '';
			}
			if ($gedcom == '__default__') {
				$sel_default = ' selected="selected"';
			} else {
				$sel_default = '';
			} ?>
			<div class="cell medium-3">
				<label class="h6"><?php echo KT_I18N::translate('Family tree'); ?></label>
			</div>
			<div class="cell medium-7 auto">
				<select name="gedcom">
					<option value="__current__"<?php echo  $sel_current; ?>><?php echo KT_I18N::translate('Current'); ?></option>
					<option value="__default__"<?php echo  $sel_default; ?>><?php echo KT_I18N::translate('Default'); ?></option>
					<?php foreach (KT_Tree::getAll() as $tree) {
						if ($tree->tree_name == $gedcom) {
							$sel = ' selected="selected"';
						} else {
							$sel = '';
						} ?>
						<option value="<?php echo $tree->tree_name; ?>"<?php echo $sel; ?>><?php echo $tree->tree_title_html; ?></option>
					<?php } ?>
				</select>
			</div>
		<?php } ?>
		<div class="cell">
			<label class="h6"><?php echo KT_I18N::translate('Content'); ?></label>
			<textarea name="html" class="html-edit" rows="10"><?php echo htmlspecialchars($html); ?></textarea>
		</div>
		<div class="cell medium-3">
			<label class="h6"><?php echo KT_I18N::translate('Show the date and time of update'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<?php echo edit_field_yes_no('show_timestamp', $show_timestamp); ?>
			<input type="hidden" name="timestamp" value="<?php echo KT_TIMESTAMP; ?>">
		</div>
		<div class="cell medium-3">
			<label class="h6"><?php echo KT_I18N::translate('Show this block for which languages?'); ?></label>
		</div>
		<div class="cell medium-7 auto">
			<?php echo edit_language_checkboxes('lang_', $languages); ?>
		</div>
		<?php
	}
}
