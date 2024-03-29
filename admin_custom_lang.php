<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net.
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
define('KT_SCRIPT_NAME', 'admin_custom_lang.php');

require './includes/session.php';

require KT_ROOT.'includes/functions/functions_edit.php';

include KT_THEME_URL.'templates/adminData.php';

global $iconStyle;

$controller = new KT_Controller_Page();
$controller
    ->restrictAccess(KT_USER_IS_ADMIN)
    ->setPageTitle(KT_I18N::translate('Custom translations'))
    ->pageHeader()
;

$action = KT_Filter::post('action');
$language = KT_Filter::post('language');
$custom_text_edits = KT_Filter::postArray('custom_text_edit');
$new_standard_text = KT_Filter::post('new_standard_text');
$new_custom_text = KT_Filter::post('new_custom_text');
$delete = KT_Filter::get('delete');

if ($custom_text_edits) {
    foreach ($custom_text_edits as $key => $value) {
        KT_DB::exec("UPDATE `##custom_lang` SET `custom_text` = '{$value}' WHERE `custom_lang_id` = {$key}");
    }
}

if ($new_standard_text || $new_custom_text) {
    KT_DB::exec("INSERT INTO `##custom_lang` (`language`, `standard_text`, `custom_text`) VALUES ('{$language}','{$new_standard_text}','{$new_custom_text}')");
}

if ('delete_item' == $delete) {
    $custom_lang_id = KT_Filter::get('custom_lang_id');
    $action = KT_Filter::get('action');
    $language = KT_Filter::get('language');
    KT_DB::exec("DELETE FROM `##custom_lang` WHERE `custom_lang_id` = {$custom_lang_id}");
}

// clear the language cache (delete the /data/cache folder) so the text change will be seen on page refresh.
if ($action) {
    if (is_dir(KT_DATA_DIR.'cache')) {
        full_rmdir(KT_DATA_DIR.'cache');
    }
}

$code_list = KT_Site::preference('LANGUAGES');
if ($code_list) {
    $languages = explode(',', $code_list);
} else {
    $languages = [
        'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
        'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
        'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
    ];
}

function custom_texts($language)
{
    return KT_DB::prepare('SELECT * FROM `##custom_lang` WHERE language = ?')
        ->execute([$language])
        ->fetchAll()
    ;
}

$custom_lang = custom_texts($language);

echo relatedPages($custom, KT_SCRIPT_NAME);

echo pageStart('custom_language', $controller->getPageTitle()); ?>

<?php echo faqLink('customisation/custom-translations/'); ?>
<div class="cell">
    <!-- SELECT LANGUAGE -->
    <form method="post" action="">
        <input type="hidden" name="action" value="translate">
        <div class="grid-x">
            <div class="cell medium-2 h5">
                <?php echo KT_I18N::translate('Select language'); ?>
            </div>
            <div class="cell medium-9">
                <div class="grid-x">
                    <select id="nav-select" class="cell medium-4" name="language" onchange="this.form.submit();">
                        <option value=''></option>
                        <?php
                            foreach (KT_I18N::installed_languages() as $code => $name) {
                                $style = ($code == $language ? ' selected=selected ' : '');
                                if (in_array($code, $languages)) {
                                    echo '<option'.$style.' value="'.$code.'">'.KT_I18N::translate($name).'</option>';
                                }
                            }
?>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="cell">
    <?php if ('translate' == $action) { ?>
    <!-- ADD NEW TRANSLATION -->
    <form method="post" action="">
        <input type="hidden" name="action" value="translate">
        <input type="hidden" name="language" value=<?php echo $language; ?>>
        <div class="grid-x">
            <div class="cell h5">
                <?php echo KT_I18N::translate('Add a new translation'); ?>
            </div>
            <div class="cell cell medium-5">
                <div class="card">
                    <div class="card-divider">
                        <?php echo KT_I18N::translate('Standard text'); ?>
                    </div>
                    <div class="card-section">
                        <textarea name="new_standard_text" placeholder="<?php echo KT_I18N::translate('Paste the standard text (US  English) here'); ?>"></textarea>
                    </div>
                </div>
            </div>
            <div class="cell cell small-1"></div>
            <div class="cell cell medium-5">
                <div class="card">
                    <div class="card-divider">
                        <?php echo $controller->getPageTitle(); ?>
                    </div>
                    <div class="card-section">
                        <textarea name="new_custom_text" placeholder="<?php echo KT_I18N::translate('Add your custom translation here'); ?>"></textarea>
                    </div>
                </div>
            </div>
            <div class="cell cell small-1"></div>
        </div>
        <button class="button" type="submit">
            <i class="<?php echo $iconStyle; ?> fa-save"></i>
            <?php echo KT_I18N::translate('Save'); ?>
        </button>
    </form>
    <?php if ($custom_lang) { ?>
    <hr>
    <!-- EDIT TRANSLATIONS -->
    <form method="post" action="">
        <input type="hidden" name="action" value="translate">
        <input type="hidden" name="language" value=<?php echo $language; ?>>
        <div class="grid-x">
            <div class="cell h5">
                <?php echo KT_I18N::translate('Edit existing translations'); ?>
            </div>
            <div class="cell cell medium-5">
                <div class="card">
                    <div class="card-divider">
                        <?php echo KT_I18N::translate('Standard text'); ?>
                    </div>
                    <?php foreach ($custom_lang as $key => $value) { ?>
                    <div class="card-section">
                        <div class="update"></div>
                        <textarea readonly><?php echo htmlspecialchars((string) $value->standard_text); ?></textarea>
                        <hr>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="cell cell small-1"></div>
            <div class="cell cell medium-5">
                <div class="card">
                    <div class="card-divider">
                        <?php echo $controller->getPageTitle(); ?>
                    </div>
                    <?php foreach ($custom_lang as $key => $value) { ?>
                    <div class="card-section">
                        <div class="update">
                            <?php echo KT_I18N::translate('Last updated ').htmlspecialchars((string) $value->updated); ?>
                            <div class="trash">
                                <?php echo '<i class="'.$iconStyle.' fa-trash-can" onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete this translation?')).'\')) { document.location=\''.KT_SCRIPT_NAME.'?delete=delete_item&amp;custom_lang_id='.$value->custom_lang_id.'&amp;action=translate&amp;language='.$language.'\'; }"></i>'; ?>
                            </div>
                        </div>
                        <textarea name="custom_text_edit[<?php echo $value->custom_lang_id; ?>]"><?php echo htmlspecialchars((string) $value->custom_text); ?></textarea>
                        <hr>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="cell cell small-1"></div>
        </div>
        <button class="button" type="submit">
            <i class="<?php echo $iconStyle; ?> fa-save"></i>
            <?php echo KT_I18N::translate('Save'); ?>
        </button>
    </form>
    <?php }
            } ?>
</div>

<?php echo pageClose();
