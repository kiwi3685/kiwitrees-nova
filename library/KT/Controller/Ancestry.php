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

class KT_Controller_Ancestry extends KT_Controller_Chart {
	var $pid = '';
	var $user = false;
	var $show_cousins;
	var $rootid;
	var $name;
	var $addname;
	var $chart_style;
	var $cellwidth;

	/**
	 * Startup activity
	 */
	public function __construct() {
		global $USE_RIN, $MAX_ALIVE_AGE, $GEDCOM, $bwidth, $bheight, $cbwidth, $cbheight, $pbwidth, $pbheight, $PEDIGREE_FULL_DETAILS, $MAX_DESCENDANCY_GENERATIONS;
		global $DEFAULT_PEDIGREE_GENERATIONS, $PEDIGREE_GENERATIONS, $MAX_PEDIGREE_GENERATIONS, $Dbwidth, $Dbheight;
		global $KT_TREE;

		parent::__construct();

		// Extract form parameters
		$this->show_cousins	= KT_Filter::get('show_cousins', 0, 1);
		$this->chart_style	= KT_Filter::getInteger('chart_style', 0, 3);
		$this->generations	= KT_Filter::getInteger('generations', 2, $MAX_PEDIGREE_GENERATIONS, $DEFAULT_PEDIGREE_GENERATIONS);

		// -- size of the detailed boxes based upon optional width parameter set in each theme.php
		$Dbwidth	= ($bwidth)/100;
		$Dbheight	= ($bheight)/100;
		$bwidth		= $Dbwidth;
		$bheight	= $Dbheight;

		// -- adjust size of the compact box
		if (!$this->show_full) {
			$bwidth		= $cbwidth;
			$bheight	= $cbheight;
		}

		$pbwidth	= $bwidth+12;
		$pbheight	= $bheight+14;

		if ($this->root && $this->root->canDisplayName()) {
			$this->setPageTitle(
				/* I18N: %s is an individual’s name */
				KT_I18N::translate('Ancestors of %s', $this->root->getFullName())
			);
		} else {
			$this->setPageTitle(KT_I18N::translate('Ancestors'));
		}

		if (strlen($this->name) < 30) {
			$this->cellwidth = "420";
		} else {
			$this->cellwidth = (strlen($this->name) * 14);
		}
	}

	/**
	 * print a child ascendancy
	 *
	 * @param string $pid individual Gedcom Id
	 * @param int $sosa child sosa number
	 * @param int $depth the ascendancy depth to show
	 */
	function print_child_ascendancy($person, $sosa, $depth) {
		global $KT_IMAGES, $Dindent, $pidarr, $iconStyle;

		if ($person) {
			$pid	= $person->getXref();
			$label	= KT_I18N::translate('Ancestors of %s', $person->getFullName());
		} else {
			$pid	= '';
			$label	= '';
		} ?>

		<li>
			<div class="grid-x grid-padding-x">
				<div class="cell shrink ancestry-details-1">
					<?php print_pedigree_person($person, 1); ?>
				</div>
				<div class="auto cell medium-6 ancestry-details-2">
					<?php if ($sosa > 1) {
						print_url_arrow($pid, '?mod=chart_ancestry&mod_action=show&rootid=' . $pid . '&amp;generations=' . $this->generations . '&amp;show_full=' . $this->show_full . '&amp;chart_style=' . $this->chart_style . '&amp;ged=' . KT_GEDURL, $label, 3);
					} else {
						echo '&nbsp;';
					} ?>
					<?php
						$sosa == 1 ? $sex = 'U' : (($sosa%2) ? $sex = 'F' : $sex = 'M');
						print_sosa_number($sosa, $person, 'blank', $sex);
						$relation	= '';
						$new		= ($pid == '' or !isset($pidarr[$pid]));
						if (!$new) {
							$relation = '<br>[=<a href="#sosa' . $pidarr[$pid] . '">' . $pidarr[$pid] . '</a> - ' . get_sosa_name($pidarr[$pid]) . ']';
						} else {
							$pidarr[$pid] = $sosa;
						}
						echo get_sosa_name($sosa) . $relation;
					?>
				</div>
				<?php

				if (is_null($person)) {
					echo '</div></li>';
					return;
				}
				// parents
				$family = $person->getPrimaryChildFamily();

				if ($family && $new && $depth > 0) {
					// print marriage info ?>

					<div class="auto cell small-12 ancestry-details-3">
						<button type="button" class="clear button show-for-medium has-tip top" data-tooltip aria-haspopup="true" data-disable-hover="false" title="<?php echo KT_I18N::translate('Hide or show ancestors'); ?>" data-toggle="sosa_<?php echo $sosa; ?>" >
							<i class="<?php echo $iconStyle; ?> fa-minimize fa-lg"></i>
						</button>
						<?php
						print_sosa_number($sosa * 2, $person, 'blank', 'M');
						echo KT_I18N::translate('and');
						print_sosa_number($sosa * 2 + 1, $person, 'blank', 'F');
						$marriage = $family->getMarriage();
						if ($marriage->canShow()) { ?>
							<a href="<?php echo $family->getHtmlUrl(); ?>" class="details">
								<?php echo KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family: ') : KT_I18N::translate('View family: '); ?>
								<?php echo $marriage->print_simple_fact(); ?>
							</a>
						<?php } ?>
					</div>
					<!-- display parents recursively - or show empty boxes -->
					<ul class="auto cell" id="sosa_<?php echo $sosa; ?>" data-toggler=".hide">
						<?php echo
							$this->print_child_ascendancy($family->getHusband(), $sosa * 2, $depth - 1);
							$this->print_child_ascendancy($family->getWife(), $sosa * 2 + 1, $depth - 1);
						?>
					</ul>
				<?php } ?>
			</div>
		</li>
	<?php }

}
