<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net.
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

if (!KT_USER_GEDCOM_ADMIN) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'module.php?mod=batch_update');

	exit;
}

require KT_ROOT . 'includes/functions/functions_edit.php';

class batch_update
{
	public $plugin; // Form parameter: chosen plugin
	public $xref; // Form parameter: record to update
	public $action; // Form parameter: how to update record
	public $data; // Form parameter: additional details for $action
	public $plugins; // Array of available plugins
	public $PLUGIN; // An instance of a plugin object
	public $all_xrefs; // An array of all xrefs that might need to be updated
	public $prev_xref; // The previous xref to process
	public $curr_xref; // The xref to process
	public $next_xref; // The next xref to process
	public $record; // A GedcomRecord object corresponding to $curr_xref

	// Constructor - initialise variables and validate user-input
	public function __construct()
	{
		$this->plugins = self::getPluginList(); // List of available plugins
		$this->plugin  = KT_Filter::get('plugin'); // User parameters
		$this->xref    = KT_Filter::get('xref', KT_REGEX_XREF);
		$this->action  = KT_Filter::get('action');
		$this->data    = KT_Filter::get('data');

		// Don't do any processing until a plugin is chosen.
		if ($this->plugin && array_key_exists($this->plugin, $this->plugins)) {
			$this->PLUGIN = new $this->plugin();
			$this->PLUGIN->getOptions();
			$this->getAllXrefs();

			switch ($this->action) {
				case '':
					break;

				case 'update':
					$record = self::getLatestRecord($this->xref, $this->all_xrefs[$this->xref]);
					if ($this->PLUGIN->doesRecordNeedUpdate($this->xref, $record)) {
						$newrecord = $this->PLUGIN->updateRecord($this->xref, $record);
						if ($newrecord != $record) {
							if ($newrecord) {
								replace_gedrec($this->xref, KT_GED_ID, $newrecord, $this->PLUGIN->chan);
							} else {
								delete_gedrec($this->xref, KT_GED_ID);
							}
						}
					}
					$this->xref = $this->findNextXref($this->xref);

					break;

				case 'update_all':
					foreach ($this->all_xrefs as $xref => $type) {
						$record = self::getLatestRecord($xref, $type);
						if ($this->PLUGIN->doesRecordNeedUpdate($xref, $record)) {
							$newrecord = $this->PLUGIN->updateRecord($xref, $record);
							if ($newrecord != $record) {
								if ($newrecord) {
									replace_gedrec($xref, KT_GED_ID, $newrecord, $this->PLUGIN->chan);
								} else {
									delete_gedrec($xref, KT_GED_ID);
								}
							}
						}
					}
					$this->xref = '';

					return;

				case 'delete':
					$record = self::getLatestRecord($this->xref, $this->all_xrefs[$this->xref]);
					if ($this->PLUGIN->doesRecordNeedUpdate($this->xref, $record)) {
						delete_gedrec($this->xref, KT_GED_ID);
					}
					$this->xref = $this->findNextXref($this->xref);

					break;

				case 'delete_all':
					foreach ($this->all_xrefs as $xref => $type) {
						$record = self::getLatestRecord($xref, $type);
						if ($this->PLUGIN->doesRecordNeedUpdate($xref, $record)) {
							delete_gedrec($xref, KT_GED_ID);
						}
					}
					$xref->xref = '';

					return;

				default:
					// Anything else will be handled by the plugin
					$this->PLUGIN->performAction($this->xref, $this->record, $this->action, $this->data);

					break;
			}

			// Make sure that our requested record really does need updating.
			// It may have been updated in another session, or may not have
			// been specified at all.
			if (array_key_exists($this->xref, $this->all_xrefs)
				&& $this->PLUGIN->doesRecordNeedUpdate($this->xref, self::getLatestRecord($this->xref, $this->all_xrefs[$this->xref]))
			) {
				$this->curr_xref = $this->xref;
			}
			// The requested record doesn't need updating - find one that does
			if (!$this->curr_xref) {
				$this->curr_xref = $this->findNextXref($this->xref);
			}
			if (!$this->curr_xref) {
				$this->curr_xref = $this->findPrevXref($this->xref);
			}
			// If we've found a record to update, get details and look for the next/prev
			if ($this->curr_xref) {
				$this->record    = self::getLatestRecord($this->curr_xref, $this->all_xrefs[$this->curr_xref]);
				$this->prev_xref = $this->findPrevXref($this->curr_xref);
				$this->next_xref = $this->findNextXref($this->curr_xref);
			}
		}
	}

	// Main entry point - called by kiwitrees in response to module.php?mod=batch_update
	public function main()
	{
		// HTML common to all pages
		echo self::getJavascript();
		include KT_THEME_URL . 'templates/adminData.php';
		$start = KT_Filter::get('start') ? KT_Filter::get('start') : '';

		echo relatedPages($moduleTools, 'module.php?mod=batch_update&amp;mod_action=admin_batch_update');

		echo pageStart('batch_update', KT_I18N::translate('Batch update')); ?>

		<div class="cell callout info-help">
			<?php echo /* I18N: Help text for Batch update tools. */ KT_I18N::translate('These tools can help fix common issues in GEDCOM data, if used with caution.'); ?>
		</div>

		<form class="cell" id="batch_update_form" action="module.php" method="get">
			<input type="hidden" name="mod" value="batch_update">
			<input type="hidden" name="mod_action" value="admin_batch_update">
			<input type="hidden" name="xref"   value="<?php echo $this->xref; ?>">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="data"   value="">

			<div class="grid-x grid-margin-x">
				<div class="cell medium-2">
					<label for="gedID"><?php echo KT_I18N::translate('Family tree'); ?></label>
				</div>
				<div class="cell medium-4">
					<form method="post" action="#" name="tree">
						<?php echo select_edit_control('ged', KT_Tree::getNameList(), '', KT_GEDCOM, 'onchange="reset_reload();"'); ?>
					</form>
				</div>
				<div class="cell callout medium-6 info-help">
					<?php echo KT_I18N::translate('If you have multiple family trees, select the one you want to edit here'); ?>
				</div>

				<div class="cell medium-2">
					<label>
						<?php echo KT_I18N::translate('Select a tool'); ?>
					</label>
				</div>
				<div class="cell medium-4">
					<select name="plugin" onchange="reset_reload();">
						<?php  if (!$this->plugin) { ?>
							<option value="" selected="selected"></option>
						<?php }
						foreach ($this->plugins as $class => $plugin) { ?>
							<option 
								value="<?php echo $class; ?>"
								<?php echo ($this->plugin == $class ? ' selected="selected"' : ''); ?>
							>
								<?php echo $plugin->getName(); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<?php if ($this->PLUGIN) { ?>
					<div class="cell callout medium-6 info-help">
						<?php echo $this->PLUGIN->getDescription(); ?>
					</div>
				<?php } else { ?>
					<div class="cell medium-6"></div>
				<?php }

				if (!get_user_setting(KT_USER_ID, 'auto_accept')) { ?>
					<div class="cell callout alert">
						<?php echo KT_I18N::translate('
							Your user account does not have "automatically approve changes" enabled.  
							You will only be able to change one record at a time.
						'); ?>
					</div>
				<?php }

				// If a plugin is selected, display the details
				if ($this->PLUGIN) {
					$this->PLUGIN->getOptionsForm();

					if ('_all' == substr($this->action, -4)) {
						// Reset - otherwise we might "undo all changes", which refreshes the
						// page, which makes them all again! ?>
						<script>
							reset_reload();
						</script>
					<?php } else {

						if ($start) {
							if ($this->curr_xref) {
								// Create an object, so we can get the latest version of the name.
								$object = KT_GedcomRecord::getInstance($this->curr_xref);
								$object->setGedcomRecord($this->record); ?>

								<div class="grid-x grid-margin-y" id="batch_update-results">
									<div class="cell">
										<?php self::createSubmitButton(KT_I18N::translate('Previous'), $this->prev_xref); ?>
										<?php self::createSubmitButton(KT_I18N::translate('Next'), $this->next_xref); ?>

										<a href="<?php echo $object->getHtmlUrl(); ?>">
											<span class="bu_name"><?php echo $object->getFullName(); ?></span>
										</a>
									</div>

									<div class="cell">
										<?php echo $this->PLUGIN->getActionPreview($this->curr_xref, $this->record); ?>
									</div>

									<?php if (get_user_setting(KT_USER_ID, 'auto_accept')) { ?>
										<div class="cell callout alert">
											<?php echo KT_I18N::translate('
												You should create a backup GEDCOM file before using the <strong>Update all</strong> option.
											'); ?>
										</div>
									<?php }
									echo implode('', $this->PLUGIN->getActionButtons($this->curr_xref, $this->record)); ?>

								</div>

							<?php } else { ?>
								<div id="batch_update-results" class="cell callout warning">
									<?php echo KT_I18N::translate('Nothing found'); ?>
								</div>
							<?php }
						}
					}
				} ?>
			</form>
		</div>

	<?php }

	// Find the next record that needs to be updated
	public function findNextXref($xref)
	{
		foreach (array_keys($this->all_xrefs) as $key) {
			if ($key > $xref) {
				$record = self::getLatestRecord($key, $this->all_xrefs[$key]);
				if ($this->PLUGIN->doesRecordNeedUpdate($key, $record)) {
					return $key;
				}
			}
		}

		return null;
	}

	// Find the previous record that needs to be updated
	public function findPrevXref($xref)
	{
		foreach (array_reverse(array_keys($this->all_xrefs)) as $key) {
			if ($key < $xref) {
				$record = self::getLatestRecord($key, $this->all_xrefs[$key]);
				if ($this->PLUGIN->doesRecordNeedUpdate($key, $record)) {
					return $key;
				}
			}
		}

		return null;
	}

	public function getAllXrefs()
	{
		$sql = [];
		$vars = [];
		foreach ($this->PLUGIN->getRecordTypesToUpdate() as $type) {
			switch ($type) {
				case 'INDI':
					$sql[] = "SELECT i_id, 'INDI' FROM `##individuals` WHERE i_file=?";
					$vars[] = KT_GED_ID;

					break;

				case 'FAM':
					$sql[] = "SELECT f_id, 'FAM' FROM `##families` WHERE f_file=?";
					$vars[] = KT_GED_ID;

					break;

				case 'SOUR':
					$sql[] = "SELECT s_id, 'SOUR' FROM `##sources` WHERE s_file=?";
					$vars[] = KT_GED_ID;

					break;

				case 'OBJE':
					$sql[] = "SELECT m_id, 'OBJE' FROM `##media` WHERE m_file=?";
					$vars[] = KT_GED_ID;

					break;

				default:
					$sql[] = 'SELECT o_id, ? FROM `##other` WHERE o_type=? AND o_file=?';
					$vars[] = $type;
					$vars[] = $type;
					$vars[] = KT_GED_ID;

					break;
			}
		}
		$this->all_xrefs =
			KT_DB::prepare(implode(' UNION ', $sql) . ' ORDER BY 1 ASC')
				->execute($vars)
				->fetchAssoc()
			;
	}

	// Scan the plugin folder for a list of plugins
	public static function getPluginList()
	{
		$array = [];
		$dir = dirname(__FILE__) . '/plugins/';
		$dir_handle = opendir($dir);
		while ($file = readdir($dir_handle)) {
			if ('.php' == substr($file, -4)) {
				require dirname(__FILE__) . '/plugins/' . $file;
				$class = basename($file, '.php') . '_bu_plugin';
				$array[$class] = new $class();
			}
		}
		closedir($dir_handle);

		return $array;
	}

	// Javascript that gets included on every page
	public static function getJavascript()
	{
		return '
			<script>
				function reset_reload() {
					var bu_form=document.getElementById("batch_update_form");
					bu_form.xref.value="";
					bu_form.action.value="";
					bu_form.data.value="";
					bu_form.submit();
				}
			</script>
		';
	}

	// Create a submit button for our form
	public static function createSubmitButton($text, $xref, $action = '', $data = '')
	{
		global $iconStyle;

		$button_icon = '';

		switch ($text) {
			case 'Previous':
				$button_icon = 'fa-backward-step';
				break;
			case 'Next':
				$button_icon = 'fa-forward-step';
				break;
			case 'Update':
				$button_icon = 'fa-floppy-disk';
				break;
			case 'Update all':
				$button_icon = 'fa-floppy-disk';
				break;
		} ?>

		<button 
			class="button primary" 
			type="submit" 
			onclick="
				this.form.xref.value = '<?php echo KT_Filter::escapeHtml($xref); ?>';
				this.form.action.value = '<?php echo KT_Filter::escapeHtml($action); ?>';
				this.form.data.value = '<?php echo KT_Filter::escapeHtml($data); ?>';
				return true;
			"
			<?php echo ($xref ? '' : ' disabled'); ?> 
		>
			<i class="<?php echo $iconStyle . ' ' . $button_icon; ?>"></i>
			<?php echo KT_I18N::translate($text); ?>
		</button>

	<?php }

	// Get the current view of a record, allowing for pending changes
	public static function getLatestRecord($xref, $type)
	{
		return find_gedcom_record($xref, KT_GED_ID, true);
	}
}

/**
* Each plugin should extend the base_plugin class, and implement these
* two functions:
* 
* bool doesRecordNeedUpdate($xref, $gedrec)
* 
* string updateRecord($xref, $gedrec)
* *
*/
class base_plugin
{
	public $chan = false; // User option; update change record

	// Default is to operate on INDI records
	public function getRecordTypesToUpdate()
	{
		return ['INDI'];
	}

	// Default option is just the "don't update CHAN record"
	public function getOptions()
	{
		$this->chan = KT_Filter::getBool('chan');
	}

	// Default option is just the "don't update CHAN record"
	public function getOptionsForm()
	{
		?>
		<div class="cell medium-2">
			<label>
				<?php echo KT_I18N::translate('Update the CHAN record'); ?>
			</label>
		</div>
		<div class="cell medium-4">
			<select name="chan" onchange="this.form.submit();">
				<option value="no"<?php echo ($this->chan ? '' : ' selected="selected"'); ?>><?php echo KT_I18N::translate('No'); ?></option>
				<option value="yes"<?php echo ($this->chan ? ' selected="selected"' : ''); ?>><?php echo KT_I18N::translate('Yes'); ?></option>
			</select>
		</div>
		<div class="cell medium-6"></div>

	<?php }

	// Default buttons are update and update_all
	public function getActionButtons($xref)
	{
		if (get_user_setting(KT_USER_ID, 'auto_accept')) {
			return [
				batch_update::createSubmitButton(KT_I18N::translate('Update'), $xref, 'update'),
				batch_update::createSubmitButton(KT_I18N::translate('Update all'), $xref, 'update_all'),
			];
		} else {
			return [
				batch_update::createSubmitButton(KT_I18N::translate('Update'), $xref, 'update'),
			];
		}
	}

	// Default previewer for plugins with no custom preview.
	public function getActionPreview($xref, $gedrec)
	{
		$old_lines = preg_split('/[\n]+/', $gedrec);
		$new_lines = preg_split('/[\n]+/', $this->updateRecord($xref, $gedrec));
		// Find matching lines using longest-common-subsequence algorithm.
		$lcs = self::LCS($old_lines, $new_lines, 0, count($old_lines) - 1, 0, count($new_lines) - 1);

		$diff_lines = [];
		$last_old = -1;
		$last_new = -1;
		while ($lcs) {
			[$old, $new] = array_shift($lcs);
			while ($last_old < $old - 1) {
				$diff_lines[] = self::decorateDeletedText($old_lines[++$last_old]);
			}
			while ($last_new < $new - 1) {
				$diff_lines[] = self::decorateInsertedText($new_lines[++$last_new]);
			}
			$diff_lines[] = $new_lines[$new];
			$last_old = $old;
			$last_new = $new;
		}
		while ($last_old < count($old_lines) - 1) {
			$diff_lines[] = self::decorateDeletedText($old_lines[++$last_old]);
		}
		while ($last_new < count($new_lines) - 1) {
			$diff_lines[] = self::decorateInsertedText($new_lines[++$last_new]);
		}

		return '<pre>' . self::createEditLinks(implode("\n", $diff_lines)) . '</pre>';
	}

	// Longest Common Subsequence.
	public static function LCS($X, $Y, $x1, $x2, $y1, $y2)
	{
		if ($x2 - $x1 >= 0 && $y2 - $y1 >= 0) {
			if ($X[$x1] == $Y[$y1]) {
				// Match at start of sequence
				$tmp = self::LCS($X, $Y, $x1 + 1, $x2, $y1 + 1, $y2);
				array_unshift($tmp, [$x1, $y1]);

				return $tmp;
			}
			if ($X[$x2] == $Y[$y2]) {
				// Match at end of sequence
				$tmp = self::LCS($X, $Y, $x1, $x2 - 1, $y1, $y2 - 1);
				array_push($tmp, [$x2, $y2]);

				return $tmp;
			}
			// No match.  Look for subsequences
			$tmp1 = self::LCS($X, $Y, $x1, $x2, $y1, $y2 - 1);
			$tmp2 = self::LCS($X, $Y, $x1, $x2 - 1, $y1, $y2);

			return count($tmp1) > count($tmp2) ? $tmp1 : $tmp2;
		}
		// One array is empty - end recursion
		return [];
	}

	// Default handler for plugin with no custom actions.
	public function performAction($xref, $gedrec, $action, $data)
	{
	}

	// Decorate inserted/deleted text
	public static function decorateInsertedText($text)
	{
		return '<span class="added_text">' . $text . '</span>';
	}

	public static function decorateDeletedText($text)
	{
		return '<span class="deleted_text">' . $text . '</span>';
	}

	// Converted gedcom links into editable links
	public static function createEditLinks($gedrec)
	{
		return preg_replace(
			"/@([^#@\n]+)@/m",
			'<a href="#" onclick="return edit_raw(\'\\1\');">@\\1@</a>',
			$gedrec
		);
	}
}
