<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_custom_theme.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
?>
	<!-- Load codemirror library files - only used on this page, so not added to session.php -->
	<!-- The CodeMirror 5.51.0 (2020-01-20) -->
    <script src="library/codemirror/lib/codemirror.js" type="text/javascript"></script>
    <script src="library/codemirror/mode/css/css.js" type="text/javascript"></script>
    <script src="library/codemirror/mode/php/php.js" type="text/javascript"></script>
    <script src="library/codemirror/mode/htmlmixed/htmlmixed.js" type="text/javascript"></script>
    <!-- CodeMirror Addons-->
    <script src="library/codemirror/addon/selection/active-line.js"></script>
    <!-- CodeMirror Style & Theme -->
    <link href="library/codemirror/lib/codemirror.css" rel="stylesheet" type="text/css" />
    <link href="library/codemirror/theme/mdn-like.css" rel="stylesheet" type="text/css" />
<?php

global $iconstyles;
$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Edit custom theme files'))
	->pageHeader()
;

$existsIcon = '<i class="' . $iconStyle . ' fa-certificate></i>';

$otherFiles	= array(
	'robots.txt'
);

$nonThemeNames = array (
	'Other'
);

foreach (get_theme_names() as $themename) {
	$themeNames[] = $themename;
}

$allFiles 	= array_merge($otherFiles, $customFiles);
$themeNames	= array_merge(get_theme_names(), $nonThemeNames);

$action		= KT_Filter::post('action');
$theme		= KT_Filter::post('theme');
$editfile	= KT_Filter::post('editfile');
$delete		= KT_Filter::get('delete');

if (in_array($editfile, $otherFiles)) {
	$path	= KT_ROOT . $editfile;
} else {
	$path	= KT_ROOT . KT_THEMES_DIR . $theme . '/' . $editfile;
}

KT_Filter::post('code') ? $content = KT_Filter::post('code') : $content = '';

if ($delete == 'delete_file') {
	$deleteFile	= KT_Filter::get('filename');
	fclose($deleteFile);
	unlink($deleteFile);
}

if ($editfile && $action != 'save') {
	switch ($editfile) {
		case 'robots.txt':
			$filename = KT_ROOT . $editfile;

			if (file_exists($filename)) {
				$content	= file_get_contents($filename);
			} elseif (file_exists(KT_ROOT . 'robots-example.txt')) {
				$content	= file_get_contents(KT_ROOT . 'robots-example.txt');
			} else { ?>
				<div class="callout error medium-8 medium-offset-2">
					<?php echo KT_I18N::translate('Your system is missing both robots.txt and robots-example.txt. You need to create one of these before you can proceed.'); ?>
				</div>
				<?php
				exit;
			}
			break;
		case 'myhome_page_template.php':
			$filename = KT_THEMES_DIR . $theme . '/' . $editfile;

			if (file_exists($filename)) {
				$content	= file_get_contents($filename);
			} else {
				$basefile = ltrim($editfile,"my");
				$content= file_get_contents(KT_THEMES_DIR . $theme . '/' . 'templates/' . $basefile);

				file_put_contents($filename, $content);
				$content = file_get_contents($filename);
			}
			break;
		case 'myheader.php':
		case 'myfooter.php':
			$filename = KT_THEMES_DIR . $theme . '/' . $editfile;

			if (file_exists($filename)) {
				$content	= file_get_contents($filename);
			} else {
				$basefile = ltrim($editfile,"my");
				$content= file_get_contents(KT_THEMES_DIR . $theme . '/' . $basefile);

				file_put_contents($filename, $content);
				$content = file_get_contents($filename);
			}
			break;
		default:
			$filename = KT_THEMES_DIR . $theme . '/' . $editfile;

			if (file_exists($filename)) {
				$content	= file_get_contents($filename);
			} else {
				$content	= "/* CUSTOM THEME FILE */\n/* " . $editfile . " */\n";
				file_put_contents($filename, $content);
			}
			break;
	}
}

if ($action == 'save') {
	switch ($editfile) {
		case 'robots.txt':
			$filename = KT_ROOT . 'robots.txt';
			break;
		default:
			$filename = KT_THEMES_DIR . $theme . '/' . $editfile;
			break;
	}
	file_put_contents($filename, $content);
	$content = file_get_contents($filename);
}

?>
<div id="custom_theme-page" class="cell">
	<div class="grid-x grid-margin-x grid-margin-y">
		<div class="cell">
			<?php //echo faqLink('customisation/custom-translations/'); ?>
			<h4 class="inline"><?php echo $controller->getPageTitle(); ?></h4>
		</div>
		<div class="cell">
			<form method="post" action="">
				<input type="hidden" name="action" value="files">
				<div class="grid-x">
					<div class="cell medium-2">
						<label><?php echo KT_I18N::translate('Select theme or other files'); ?></label>
					</div>
					<select class="cell medium-4" id="theme-select" name="theme" onchange="this.form.submit();">
						<option value=''></option>
						<?php foreach ($themeNames as $themedir) {
							$name		= (in_array($themedir, $nonThemeNames) ? KT_I18N::translate($nonThemeNames) : get_theme_display($themedir));
							$selected	= ($theme == $themedir ? ' selected ' : ''); ?>
							<option <?php echo $selected; ?> value="<?php echo $themedir; ?>"><?php echo $name; ?></option>
						<?php } ?>
					</select>
					<div class="cell medium-6"></div>
				</div>
			</form>
		</div>
		<div class="cell">
			<?php if ($action == 'files') { ?>
				<!-- Select files for chosen theme or other files-->
				<form method="post" action="">
					<input type="hidden" name="action" value="files">
					<input type="hidden" name="theme" value=<?php echo $theme; ?>>
					<div class="grid-x">
						<div class="cell medium-2">
							<label><?php echo KT_I18N::translate('Select file'); ?></label>
						</div>
						<select class="cell medium-4" id="file-select" name="editfile" onchange="this.form.submit();">
							<option value=''></option>
							<?php if (in_array($theme, get_theme_names())) {
								foreach ($customFiles as $file) {
									$selected = ($file == $editfile ? ' selected ' : '');
									$new = (file_exists(KT_ROOT . KT_THEMES_DIR . $theme . '/' . $file) ? 'new' : ''); ?>
									<option class="<?php echo $new; ?>" <?php echo $selected; ?> value="<?php echo $file; ?>"><?php echo $file; ?></option>
								<?php }
							} else {
								foreach ($otherFiles as $file) {
									$selected = ($file == $editfile ? ' selected ' : '');
									$new = (file_exists(KT_ROOT . KT_THEMES_DIR . $theme . '/' . $file) ? 'new' : ''); ?>
									<option class="<?php echo $new; ?>" <?php echo $selected; ?> value="<?php echo $file; ?>"><?php echo $file; ?></option>
								<?php }
							} ?>
						</select>
						<div class="cell medium 4 medium-offset-2 helpcontent">
							<?php /*I18N: Help text for custom theme page */ echo KT_I18N::translate('File names displayed in red indicate these files already exist. Others will be created when you click "Save".'); ?>
						</div>
					</div>
				</form>
			<?php } ?>
		</div>
		<div class="cell">
			<?php if ($content) {
				$controller->addInlineJavascript('
					var editor = CodeMirror.fromTextArea(code, {
						theme: "mdn-like",
						lineNumbers: true,
						styleActiveLine: true,
						matchBrackets: true,
						viewportMargin: Infinity
					});
				'); ?>

				<form class="cell" method="post" action="">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="editfile" value="<?php echo $editfile; ?>">
					<div class="grid-x">
						<div class="cell large-2 large-offset-1">
							<h5><?php echo $editfile; ?></h5>
						</div>
						<div class="cell large-8 text-right">
							<a href="#" <?php echo 'onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete this translation?')) . '\')) { document.location=\'' . KT_SCRIPT_NAME . '?delete=delete_file&amp;filename=' . $path . '\'; }"'; ?>>
								<?php echo KT_I18N::translate('Delete this custom file'); ?>
								&nbsp;
								<i class="<?php echo $iconStyle; ?> fa-trash-alt" ></i>
							</a>
						</div>
						<div class="cell large-10 large-offset-1" id="textarea">
							<textarea id="code" name="code"><?php echo $content; ?></textarea>
						</div>
						<div class="cell">
							<button type="submit" class="button">
								<i class="<?php echo $iconStyle; ?> fa-save"></i>
								<?php echo KT_I18N::translate('Save'); ?>
							</button>
							<a class="button secondary" href="<?php echo KT_SCRIPT_NAME; ?>">
								<i class="<?php echo $iconStyle; ?> fa-times"></i>
								<?php echo KT_I18N::translate('Cancel'); ?>
							</a>
						</div>
					</div>
				</form>
			<?php } ?>
		</div>
	</div>
</div>
<?php
