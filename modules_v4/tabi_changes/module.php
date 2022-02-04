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

class tabi_changes_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Changes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab summarising changes to an individual\'s record');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 30;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
        return KT_PRIV_NONE; // Access to GEDCOM manager only
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
        global $controller;
        require_once KT_ROOT.'library/php-diff/lib/Diff.php';
        require_once KT_ROOT.'library/php-diff/lib/Diff/Renderer/Html/SideBySide.php';

        $controller
			->addExternalJavascript(KT_DATATABLES_JS)
			->addExternalJavascript(KT_DATATABLES_FOUNDATION_JS);

		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_DATATABLES_BUTTONS)
				->addExternalJavascript(KT_DATATABLES_HTML5);
			$buttons = 'B';
		} else {
			$buttons = '';
		}

        $controller->addInlineJavascript('
            jQuery("#change_list").dataTable({
                dom: \'<"top"p' . $buttons . 'f<"clear">irl>t<"bottom"pl>\',
                ' . KT_I18N::datatablesI18N() . ',
                buttons: [{extend: "csvHtml5", exportOptions: {}}],
                autoWidth: false,
                processing: true,
                retrieve: true,
				displayLength: 10,
                pagingType: "full_numbers",
                stateSave: true,
                stateDuration: -1,
                columns: [
					/* 0-Timestamp */   { },
					/* 1-User */        { },
					/* 2-GEDCOM Data */ { },
                    /* 3-Status */      { },
				],
			});
    	');

        switch (KT_SCRIPT_NAME) {
            //Note: for "family" changes see separate module tabf_changes
            case 'individual.php':
            	$item  = $controller->getSignificantIndividual();
            break;
			case 'note.php':
            case 'source.php':
            case 'repo.php':
            case 'mediaviewer.php':
                $item  = $controller->record;
            break;
		}
        $xref		= $item->getXref();
        $rows       = $this->getChangeList($xref);

        if ($rows) {
            foreach ($rows as $row) {
                $a = explode("\n", htmlspecialchars($row->old_gedcom));
                $b = explode("\n", htmlspecialchars($row->new_gedcom));
                // Generate a side by side diff
                $renderer = new Diff_Renderer_Html_SideBySide;
                // Options for generating the diff
                $options = array();
                // Initialize the diff class
                $diff = new Diff($a, $b, $options);
                $row->old_gedcom = $diff->Render($renderer);
                $row->new_gedcom = '';
            }
    		?>

    		<div id="tabi_changes_content">
    			<?php if ($item && $item->canDisplayDetails()) { ?>
    				<table id="change_list">
    					<thead>
    						<tr>
    							<th><?php echo KT_I18N::translate('Timestamp'); ?></th>
    							<th><?php echo KT_I18N::translate('User'); ?></th>
    							<th class="text-center"><?php echo KT_I18N::translate('GEDCOM Data'); ?></th>
    							<th><?php echo KT_I18N::translate('Status'); ?></th>
    						</tr>
    					</thead>
    					<tbody>
                            <?php foreach($rows as $row) { ?>
						        <tr>
        							<td><?php echo $row->change_time; ?></td>
        							<td><?php echo $row->user_name; ?></td>
        							<td><?php echo $row->old_gedcom; ?></td>
        							<td><?php echo $row->status; ?></td>
						        </tr>
                            <?php } ?>
    					</tbody>
    				</table>
    			<?php } ?>
    		</div>
		<?php } else { ?>
            <div> <?php echo KT_I18N::translate('No change data available'); ?></div>
        <?php }
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->getChangeList();
	}

    // Implement KT_Module_Tab
	public function isGrayedOut() {
        return count($this->getChangeList()) == 0;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	private function getChangeList($xref = '') {
        $sql =
        	"SELECT *, `user_name` FROM `##change`
             LEFT JOIN `##user` USING (user_id)
             WHERE `xref` LIKE ?
             AND `gedcom_id` = ?
             ORDER BY `change_id` DESC";
         $rows = KT_DB::prepare($sql)->execute(array($xref, KT_GED_ID))->fetchAll();

		return $rows;
	}

}
