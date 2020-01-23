<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_site_clean.php');
require './includes/session.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(/* I18N: The “Data folder” is a configuration setting */ KT_I18N::translate('Clean up data folder'))
	->pageHeader();

require KT_ROOT . 'includes/functions/functions_edit.php';

// Vars
$ajaxdeleted		= false;
$locked_by_context	= array('index.php', 'config.ini.php', 'language');

// If we are storing the media in the data folder (this is the
// default), then don’t delete it.
// Need to consider the settings for all gedcoms
foreach (KT_Tree::getAll() as $tree) {
	$MEDIA_DIRECTORY = $tree->preference('MEDIA_DIRECTORY');

	if (substr($MEDIA_DIRECTORY, 0, 3) !='../') {
		// Just need to add the first part of the path
		$tmp = explode('/', $MEDIA_DIRECTORY);
		$locked_by_context[] = $tmp[0];
	}
}
?>
<div id="server-info" class="cell">
	<h4><?php echo $controller->getPageTitle(); ?></h4>
	<div class="callout alert">
		<?php echo KT_I18N::translate('Files marked with %s are required for proper operation and cannot be removed.', '<i class="alert ' . $iconStyle . ' fa-ban"></i>'); ?>
	</div>

	<?php
	//post back
	if (isset($_REQUEST['to_delete'])) { ?>
		<div class="callout success">
			<h5><?php echo KT_I18N::translate('Deleted files:'); ?></h5>
			<?php foreach ($_REQUEST['to_delete'] as $k => $v) {
				if (is_dir(KT_DATA_DIR . $v)) {
					full_rmdir(KT_DATA_DIR . $v);
				} elseif (file_exists(KT_DATA_DIR . $v)) {
					unlink(KT_DATA_DIR . $v);
				} ?>
				<p><?php echo $v; ?></p>
			<?php } ?>
		</div>
	<?php } ?>

	<form name="delete_form" method="post" action="">
		<div id="cleanup" class="grid-x">
			<ul class="cell medium-offset-1">
				<?php
				$dir	 = dir(KT_DATA_DIR);
				$entries = array();
				while (false !== ($entry = $dir->read())) {
					$entries[] = $entry;
				}
				sort($entries);
				foreach ($entries as $entry) {
					if ($entry[0] != '.') {
						$file_path = KT_DATA_DIR . $entry;
						$icon = '<i class="' . $iconStyle . ' ' . (is_dir($file_path)? 'fa-folder-open' : 'fa-folder') . '"></i>';
						if (in_array($entry, $locked_by_context)) { ?>
							<li class="facts_value" name="<?php echo $entry; ?>" id="lock_<?php echo $entry; ?>" >
								<i class="alert <?php echo $iconStyle; ?> fa-ban fa-fw"></i>
								<?php echo $icon; ?>
								<span><?php echo $entry; ?></span>
						<?php } else { ?>
							<li class="facts_value" name="<?php echo $entry; ?>" id="li_<?php echo $entry; ?>" >
								<input type="checkbox" name="to_delete[]" value="<?php echo $entry; ?>">
								<?php echo $icon; ?>
								<?php echo $entry; ?>
								<?php $element[] = 'li_' . $entry; ?>
						<?php } ?>
						</li>
					<?php }
				}
				$dir->close(); ?>
			</ul>
			<button class="button" type="submit">
				<i class="<?php echo $iconStyle; ?> fa-trash-alt"></i>
				<?php echo KT_I18N::translate('Delete'); ?>
			</button>
		</div>
	</form>
</div>
<?php
