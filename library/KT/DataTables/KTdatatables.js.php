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

if (KT_USER_CAN_EDIT) {
	$buttons = 'B';
} else {
	$buttons = '';
}
?>

<script>
	/**
	 * Initialise DataTables with standardKTN defaults
	 * Individual pages add button, sorting, and column definitions as required
	 * The following options can also be over-ridden as necessary
	 */
	function datables_defaults()
	{
		jQuery.extend(jQuery.fn.dataTable.defaults,
		{
			dom: "<'top'p<?php echo $buttons; ?>f<'clear'>irl>t<'bottom'pl>",
			<?php echo KT_I18N::datatablesI18N(array(5, 10, 20, 50, 100, 500, 1000, -1)); ?> ,
			buttons: [{extend: "csvHtml5", exportOptions: {columns: ":visible"}}],
			autoWidth: false,
			processing: true,
			serverSide: true,
			sAjaxSource: "<?php echo KT_SCRIPT_NAME; ?>?action=loadrows",
			deferRender: true,
			pagingType: "full_numbers",
			pageLength: 20,
			stateSave: true,
			stateSaveParams: function (settings, data)
			{
				data.columns.forEach(function (column)
				{
					delete column.sSearch;
				});
			},
			stateDuration: -1,
		});
	};
</script>
