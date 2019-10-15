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

define('KT_SCRIPT_NAME', 'admin_custom_theme.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
?>
	<!-- The CodeMirror -->
    <script src="library/codemirror/lib/codemirror.js" type="text/javascript"></script>
    <!-- The CodeMirror Modes - note: for HTML rendering required: xml, css, javasript -->
<!--     <script src="library/codemirror/mode/xml/xml.js" type="text/javascript"></script>-->
<!--     <script src="library/codemirror/mode/clike/clike.js" type="text/javascript"></script>-->
<!--     <script src="library/codemirror/mode/javascript/javascript.js" type="text/javascript"></script>-->
    <script src="library/codemirror/mode/css/css.js" type="text/javascript"></script>
    <script src="library/codemirror/mode/php/php.js" type="text/javascript"></script>
    <script src="library/codemirror/mode/htmlmixed/htmlmixed.js" type="text/javascript"></script>
    <!-- CodeMirror Addons-->
    <script src="library/codemirror/addon/selection/active-line.js"></script>
<!--     <script src="library/codemirror/addon/lint/lint.js"></script>-->
<!-- 	<link href="library/codemirror/addon/lint/lint.css" rel="stylesheet" type="text/css" />-->
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
$current_themedir = get_gedcom_setting(KT_GED_ID, 'THEME_DIR');

$action		= KT_Filter::post('action');
$theme		= KT_Filter::post('theme');
$editfile	= KT_Filter::post('fileOld');
$addfile	= KT_Filter::post('fileAdd');
$delete		= KT_Filter::get('delete');

KT_Filter::post('code') ? $content = KT_Filter::post('code') : $content = '';

if ($delete == 'delete_file') {
	$deleteFile	= KT_Filter::get('filename');
	fclose($deleteFile);
	unlink($deleteFile);
}

if ($editfile) {
	$filename	= KT_THEMES_DIR . $theme . '/' . $editfile;
	$content	= file_get_contents($filename);
}

if ($addfile) {
	$filename	= KT_THEMES_DIR . $theme . '/' . $addfile;
	$content	= "/* CUSTOM THEME FILE */\n' . $addfile . '\n";
	file_put_contents($filename, $content);
}

if ($action == 'save') {
	$filename = KT_THEMES_DIR . $theme . '/' . $editfile;
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
				<div class="grid-x grid-margin-x">
					<div class="cell medium-2">
						<label><?php echo KT_I18N::translate('Select theme'); ?></label>
					</div>
					<select class="cell medium-4" id="theme-select" name="theme" onchange="this.form.submit();">
						<option value=''></option>
						<?php foreach (get_theme_names() as $themename => $themedir) {
							$style = ($themename == $theme ? ' selected=selected ' : '');
							echo '<option' . $style . ' value="' . $themename . '">' . get_theme_display($themename) . '</option>';
						} ?>
					</select>
					<div class="cell medium-6"></div>
				</div>
			</form>
		</div>
		<div class="cell">
			<?php if ($action == 'files') { ?>
				<!-- Select files for chosen theme -->
				<form method="post" action="">
					<input type="hidden" name="action" value="files">
					<input type="hidden" name="theme" value=<?php echo $theme; ?>>
					<div class="grid-x grid-margin-x">
						<div class="cell medium-2">
							<label><?php echo KT_I18N::translate('Select existing file to edit'); ?></label>
						</div>
						<select class="cell medium-4" id="file-select" name="fileOld" onchange="this.form.submit();">
							<option value=''></option>
							<?php foreach ($customFiles as $file) {
								$path	= KT_ROOT . KT_THEMES_DIR . $theme . '/' . $file;
								$style	= ($file == $editfile ? ' selected=selected ' : '');
								if (file_exists($path)) { ?>
									<option <?php echo $style; ?> value="<?php echo $file; ?>"><?php echo $file; ?></option>
								<?php }
							} ?>
						</select>
						<div class="cell medium-2">
							<label><?php echo KT_I18N::translate('Select new file to add'); ?></label>
						</div>
						<select class="cell medium-4" id="file-select" name="fileAdd" onchange="this.form.submit();">
							<option value=''></option>
							<?php foreach ($customFiles as $file) {
								$path = KT_ROOT . KT_THEMES_DIR . $theme . '/' . $file;
								if (!file_exists($path)) { ?>
									<option value="<?php echo $file; ?>"><?php echo $file; ?></option>
								<?php }
							} ?>
						</select>
					</div>
			<?php } ?>
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
				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell">
						<form method="post" action="">
							<input type="hidden" name="action" value="save">
							<input type="hidden" name="file" value="<?php echo $editfile; ?>">
							<div class="grid-x">
								<div class="cell large-2 large-offset-1">
									<h5><?php echo $editfile; ?></h5>
								</div>
								<div class="cell large-8 text-right">
									<a href="#" <?php echo 'onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete this translation?')) . '\')) { document.location=\'' . KT_SCRIPT_NAME . '?delete=delete_file&amp;filename=' . KT_THEMES_DIR . $theme . '/' . $editfile . '\'; }"'; ?>>
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
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php
