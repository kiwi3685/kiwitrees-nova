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

class chart_relationship_KT_Module extends KT_Module implements KT_Module_Chart, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Relationship');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Relationship chart” module */ KT_I18N::translate('An individual\'s relationship chart');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_config':
				$this->config();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Chart
	public function getChartMobile() {
		// exclude this module from mobile displays
		return false;
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName().'&amp;mod_action=admin_config';
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref			= $controller->getSignificantIndividual()->getXref();
		$PEDIGREE_ROOT_ID 	= get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');
		$menus				= array();
		if ($indi_xref) {
			// Pages focused on a specific person - from the person, to me
			$pid1 = KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : KT_USER_ROOT_ID;
			if (!$pid1 && $PEDIGREE_ROOT_ID) {
				$pid1 = $PEDIGREE_ROOT_ID;
			};
			$pid2 = $indi_xref;
			if ($pid1 == $pid2) {
				$pid2 = $PEDIGREE_ROOT_ID ? $PEDIGREE_ROOT_ID : '';
			}
			$menu = new KT_Menu(
				KT_USER_GEDCOM_ID ? KT_I18N::translate('Relationship to me') : $this->getTitle(),
				'relationship.php?pid1=' . $pid1 .'&amp;pid2=' . $pid2 .'&amp;ged=' . KT_GEDURL,
				'menu-chart-relationship'
			);
			$menus[] = $menu;
		} else {
			// Regular pages - from me, to somebody
			$pid1 = KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : KT_USER_ROOT_ID;
			$pid2 = $PEDIGREE_ROOT_ID ? $PEDIGREE_ROOT_ID : '';
			$menu = new KT_Menu(
				KT_USER_GEDCOM_ID ? KT_I18N::translate('Relationship to me') : $this->getTitle(),
				'relationship.php?pid1=' . $pid1 .'&amp;pid2=' . $pid2 .'&amp;ged=' . KT_GEDURL,
				'menu-chart-relationship'
			);
			$menus[] = $menu;
		}
		return $menus;
	}

	private function config() {
		require KT_ROOT . 'includes/functions/functions_edit.php';
		include KT_THEME_URL . 'templates/adminData.php';

		global $iconStyle;

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery(function() {
					jQuery("div.config_options:odd").addClass("odd");
					jQuery("div.config_options:even").addClass("even");
				});
			');

		$gedID 	= KT_Filter::post('gedID') ? KT_Filter::post('gedID') : KT_GED_ID;
		$tree 	= KT_Tree::getNameFromId($gedID);


		// Possible options for the recursion option
		$recursionOptions = array(
			0	=> KT_I18N::translate('none'),
			1	=> KT_I18N::number(1),
			2	=> KT_I18N::number(2),
			3	=> KT_I18N::number(3),
			99	=> KT_I18N::translate('unlimited'),
		);

		// defaults
		$chart1		 = 1;
		$chart2		 = 0;
		$chart3		 = 1;
		$chart4		 = 1;
		$chart5		 = 0;
		$chart6		 = 1;
		$chart7		 = 0;
		$rec_options = 99;
		$rel1		 = '1';
		$rel2		 = '1';
		$rel3		 = '1';
		$rel1_ca	 = '1';
		$rel2_ca	 = '1';
		$rel3_ca	 = '1';

		if (KT_Filter::postBool('reset')) {
			set_gedcom_setting($gedID, 'CHART_1',							1);
			set_gedcom_setting($gedID, 'CHART_2',							0);
			set_gedcom_setting($gedID, 'CHART_3',							1);
			set_gedcom_setting($gedID, 'CHART_4',							1);
			set_gedcom_setting($gedID, 'CHART_5',							0);
			set_gedcom_setting($gedID, 'CHART_6',							1);
			set_gedcom_setting($gedID, 'CHART_7',							0);
			set_gedcom_setting($gedID, 'RELATIONSHIP_RECURSION', 			99);
			set_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI',			'1');
			set_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS',					'1');
			set_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE',					'1');
			set_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA',	'1');
			set_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS_SHOW_CA',			'1');
			set_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE_SHOW_CA',			'1');

			AddToLog($this->getTitle().' set to default values', 'config');
		}

		if (KT_Filter::postBool('save')) {
			set_gedcom_setting($gedID, 'CHART_1',							KT_Filter::postBool('NEW_CHART_1', $chart1));
			set_gedcom_setting($gedID, 'CHART_2',							KT_Filter::postBool('NEW_CHART_2', $chart2));
			set_gedcom_setting($gedID, 'CHART_3',							KT_Filter::postBool('NEW_CHART_3', $chart3));
			set_gedcom_setting($gedID, 'CHART_4',							KT_Filter::postBool('NEW_CHART_4', $chart4));
			set_gedcom_setting($gedID, 'CHART_5',							KT_Filter::postBool('NEW_CHART_5', $chart5));
			set_gedcom_setting($gedID, 'CHART_6',							KT_Filter::postBool('NEW_CHART_6', $chart6));
			set_gedcom_setting($gedID, 'CHART_7',							KT_Filter::postBool('NEW_CHART_7', $chart7));
			set_gedcom_setting($gedID, 'RELATIONSHIP_RECURSION', 			KT_Filter::post('NEW_RELATIONSHIP_RECURSION', KT_REGEX_INTEGER, $rec_options));
			set_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI',			KT_Filter::post('NEW_TAB_REL_TO_DEFAULT_INDI', KT_REGEX_INTEGER, $rel1));
			set_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS',					KT_Filter::post('NEW_TAB_REL_OF_PARENTS', KT_REGEX_INTEGER, $rel2));
			set_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE',					KT_Filter::post('NEW_TAB_REL_TO_SPOUSE', KT_REGEX_INTEGER, $rel3));
			set_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA',	KT_Filter::post('NEW_TAB_REL_TO_DEFAULT_INDI_SHOW_CA', KT_REGEX_INTEGER, $rel1_ca));
			set_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS_SHOW_CA',			KT_Filter::post('NEW_TAB_REL_OF_PARENTS_SHOW_CA', KT_REGEX_INTEGER, $rel2_ca));
			set_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE_SHOW_CA',			KT_Filter::post('NEW_TAB_REL_TO_SPOUSE_SHOW_CA', KT_REGEX_INTEGER, $rel3_ca));

			AddToLog($this->getTitle().' set to new values', 'config');
		}

		$chart1		 = get_gedcom_setting($gedID, 'CHART_1');
		$chart2		 = get_gedcom_setting($gedID, 'CHART_2');
		$chart3		 = get_gedcom_setting($gedID, 'CHART_3');
		$chart4		 = get_gedcom_setting($gedID, 'CHART_4');
		$chart5		 = get_gedcom_setting($gedID, 'CHART_5');
		$chart6		 = get_gedcom_setting($gedID, 'CHART_6');
		$chart7		 = get_gedcom_setting($gedID, 'CHART_7');
		$rec_options = get_gedcom_setting($gedID, 'RELATIONSHIP_RECURSION');
		$rel1		 = get_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI');
		$rel2		 = get_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS');
		$rel3		 = get_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE');
		$rel1_ca	 = get_gedcom_setting($gedID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA');
		$rel2_ca	 = get_gedcom_setting($gedID, 'TAB_REL_OF_PARENTS_SHOW_CA');
		$rel3_ca	 = get_gedcom_setting($gedID, 'TAB_REL_TO_SPOUSE_SHOW_CA');


		echo relatedPages($moduleTools, $this->getConfigLink());

		echo pageStart('relations_config', KT_I18N::translate('Relationship calculation options'), '', '', '/faqs/general-topics/displaying-relationships/'); ?>

			<div class="grid-x grid-margin-x">
				<div class="cell medium-3">
					<label for="ged" style="padding: 0 2rem;"><?php echo KT_I18N::translate('Family tree'); ?></label>
				</div>
				<div class="cell medium-4 auto">
					<form method="post" action="#" name="tree">
						<?php echo select_edit_control('ged', KT_Tree::getIdList(), KT_I18N::translate('All'), $gedID, ' onchange="tree.submit();"'); ?>
					</form>
				</div>

				<form class="cell" method="post" name="rela_form" action="<?php echo $this->getConfigLink(); ?>">
					<input type="hidden" name="save" value="1">

					<fieldset class="fieldset" id="config-chart">
						<legend class="h5">
							<?php echo /* I18N: Configuration option */ KT_I18N::translate('Chart settings'); ?>
						</legend>
						<div class="grid-x grid-margin-x">
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('Options related to the the relationship chart'); ?>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_1', 1, $chart1, '', 'Yes', 'No', 'small'); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option */ KT_I18N::translate('Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once.') ?>
									</div>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_2', 1, $chart2, '', 'Yes', 'No', 'small'); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option */ KT_I18N::translate('Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs).') ?>
									</div>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find all relationships via lowest common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_3', 1, $chart3, '', 'Yes', 'No', 'small'); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option */ KT_I18N::translate('All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: <a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank" rel="noopener noreferrer">Coefficient of Relationship</a>'); ?>
									</div>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_4', 1, $chart4, '', 'Yes', 'No', 'small'); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option */ KT_I18N::translate('Prefers partial paths via common ancestors, even if there is no direct common ancestor.') ?>
									</div>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_7', 1, $chart7, '', 'Yes', 'No', 'small'); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option */ KT_I18N::translate('For close relationships similar to the previous option, but faster. Internally just a combination of two other methods.') ?>
									</div>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('Find the closest overall connections'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_5', 1, $chart5, '', 'Yes', 'No', 'small'); ?>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Find other/all overall connections'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_CHART_6', 1, $chart6, '', 'Yes', 'No', 'small'); ?>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo KT_I18N::translate('How much recursion to use when searching for relationships'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo radio_buttons(
										'NEW_RELATIONSHIP_RECURSION',
										$recursionOptions,
										$rec_options,
										'class="radio_inline"'
									); ?>
									<div class="cell callout info-help">
										<?php echo /* I18N: Configuration option for relationship chart */ KT_I18N::translate('Searching for all possible relationships can take a lot of time in complex trees, This option can help limit the extent of relationships included in the relationship chart.'); ?>
									</div>
								 </div>
							</div>
						</div>
					</fieldset>

					<fieldset class="fieldset" id="config-tab">
						<legend class="h5">
							<?php echo /* I18N: Configuration option */ KT_I18N::translate('Families tab settings'); ?>
						</legend>
						<div class="grid-x grid-margin-x">
							<!-- RELATIONS TO DEFAULT INDIVIDUAL -->
							<div class="cell callout info-help">
								<strong>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('How to determine relationships to the default individual'); ?>
								</strong>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Do not show any relationship'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="0" <?php echo ($rel1 === '0') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('
									The following options refer to the same algorithms used in the relationships chart. Choose any one of these.
								') ?>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="1" <?php echo ($rel1 === '1') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="2" <?php echo ($rel1 === '2') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all relationships via lowest common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="3" <?php echo ($rel1 === '3') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="4" <?php echo ($rel1 === '4') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="7" <?php echo ($rel1 === '7') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find the closest overall connections') ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="5" <?php echo ($rel1 === '5') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find other/all overall connections') ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="6" <?php echo ($rel1 === '6') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Show common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_TAB_REL_TO_DEFAULT_INDI_SHOW_CA', $rel1_ca, '', '', 'Yes', 'No', 'small'); ?>
								</div>
							</div>
							<hr class="cell">
							<!-- RELATIONS BETWEEN PARENTS -->
							<div class="cell callout info-help">
								<strong>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('How to determine relationships between parents'); ?>
								</strong>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Do not show any relationship'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="0" <?php echo ($rel2 === '0') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('The following options refer to the same algorithms used in the relationships chart. Choose any one of these.') ?>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="1" <?php echo ($rel2 === '1') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="2" <?php echo ($rel2 === '2') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all relationships via lowest common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="3" <?php echo ($rel2 === '3') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('Searching for overall connections is not included here because there is always a trivial HUSB - WIFE connection.') ?>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Show common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_TAB_REL_OF_PARENTS_SHOW_CA', $rel2_ca, '', '', 'Yes', 'No', 'small'); ?>
								</div>
							</div>
							<hr class="cell">
							<!-- RELATIONS TO SPOUSES -->
							<div class="cell callout info-help">
								<strong>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('How to determine relationships to spouses'); ?>
								</strong>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Do not show any relationship'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="0" <?php echo ($rel3 === '0') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('The following options refer to the same algorithms used in the relationships chart. Choose any one of these.') ?>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find a closest relationship via common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="1" <?php echo ($rel3 === '1') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="2" <?php echo ($rel3 === '2') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell medium-4">
								<label class="indent">
									<?php echo KT_I18N::translate('Find all relationships via lowest common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="3" <?php echo ($rel3 === '3') ? 'checked' : ''; ?>>
								</div>
							</div>
							<div class="cell callout info-help">
								<?php echo /* I18N: Configuration option */ KT_I18N::translate('Searching for overall connections is not included here because there is always a trivial HUSB - WIFE connection.') ?>
							</div>
							<div class="cell medium-4">
								<label>
									<?php echo /* I18N: Configuration option */ KT_I18N::translate('Show common ancestors'); ?>
								</label>
							</div>
							<div class="cell medium-8">
								<div class="input_group">
									<?php echo simple_switch('NEW_TAB_REL_TO_SPOUSE_SHOW_CA', $rel3_ca, '', '', 'Yes', 'No', 'small'); ?>
								</div>
							</div>
						</div>
					</fieldset>

					<?php echo resetButtons('Save', ''); ?>

				</form>
			</div>
		<?php pageClose();
	}

}
