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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class block_statistics_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Statistics');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Statistics” module */ KT_I18N::translate('The size of the family tree, earliest and latest events, common names, etc.');
	}

	// Extend class KT_Module_Block
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $KT_TREE, $iconStyle;

		$show_last_update    = get_block_setting($block_id, 'show_last_update',     true);
		$show_common_surnames= get_block_setting($block_id, 'show_common_surnames', true);
		$stat_indi           = get_block_setting($block_id, 'stat_indi',            true);
		$stat_fam            = get_block_setting($block_id, 'stat_fam',             true);
		$stat_sour           = get_block_setting($block_id, 'stat_sour',            true);
		$stat_media          = get_block_setting($block_id, 'stat_media',           true);
		$stat_repo           = get_block_setting($block_id, 'stat_repo',            true);
		$stat_surname        = get_block_setting($block_id, 'stat_surname',         true);
		$stat_events         = get_block_setting($block_id, 'stat_events',          true);
		$stat_users          = get_block_setting($block_id, 'stat_users',           true);
		$stat_first_birth    = get_block_setting($block_id, 'stat_first_birth',     true);
		$stat_last_birth     = get_block_setting($block_id, 'stat_last_birth',      true);
		$stat_first_death    = get_block_setting($block_id, 'stat_first_death',     true);
		$stat_last_death     = get_block_setting($block_id, 'stat_last_death',      true);
		$stat_long_life      = get_block_setting($block_id, 'stat_long_life',       true);
		$stat_avg_life       = get_block_setting($block_id, 'stat_avg_life',        true);
		$stat_most_chil      = get_block_setting($block_id, 'stat_most_chil',       true);
		$stat_avg_chil       = get_block_setting($block_id, 'stat_avg_chil',        true);
		$stat_link           = get_block_setting($block_id, 'stat_link',            true);
		$block               = get_block_setting($block_id, 'block',                false);

		if ($cfg) {
			foreach (array('show_common_surnames', 'stat_indi', 'stat_fam', 'stat_sour', 'stat_media', 'stat_surname', 'stat_events', 'stat_users', 'stat_first_birth', 'stat_last_birth', 'stat_first_death', 'stat_last_death', 'stat_long_life', 'stat_avg_life', 'stat_most_chil', 'stat_avg_chil', 'stat_link', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$name = $cfg[$name];
				}
			}
		}

		$id			= $this->getName() . $block_id;
		$class		= $this->getName();
		$title 		= $this->getTitle();
		$config		= true;
		$content	= '';

		$stats		= new KT_Stats(KT_GEDCOM);

		if ($show_last_update) {
			$content .= '<div class="callout secondary small">' .
				/* I18N: %s is a date */ KT_I18N::translate('This family tree was last updated on %s.', strip_tags($stats->gedcomUpdated())) . '
			</div>';
		}

		$content .= '<div class="grid-x">
			<div class="cell large-3">
				<div class="grid-x grid-padding-x grid-padding-y">';
					if ($stat_indi) {
						$content.='<div class="cell small-6">' . KT_I18N::translate('Individuals') . '</div>
						<div class="cell small-6"><a href="indilist.php?surname_sublist=no&amp;ged="' . KT_GEDURL . '">' . $stats->totalIndividuals() . '</a></div>
						<div class="cell small-6"><i class="' . $iconStyle . ' fa-male"></i>' . KT_I18N::translate('Males') . '</div>
						<div class="cell small-6">' . $stats->totalSexMales() . '<br>' . $stats->totalSexMalesPercentage() . '</div>
						<div class="cell small-6"><i class="' . $iconStyle . ' fa-female"></i>' . KT_I18N::translate('Females') . '</div>
						<div class="cell small-6">' . $stats->totalSexFemales() . '<br>' . $stats->totalSexFemalesPercentage() . '</div>';
					}
					if ($stat_surname) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Total surnames') . '</div>
						<div class="cell small-6"><a href="indilist.php?show_all=yes&amp;surname_sublist=yes&amp;ged=' . KT_GEDURL . '">' . $stats->totalSurnames() . '</a></div>';
					}
					if ($stat_fam) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Families') . '</div>
						<div class="cell small-6"><a href="famlist.php?ged=' . KT_GEDURL . '">' . $stats->totalFamilies() . '</a></div>';
					}
					if ($stat_sour) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Sources') . '</div>
						<div class="cell small-6"><a href="sourcelist.php?ged=' . KT_GEDURL . '">' . $stats->totalSources() . '</a></div>';
					}
					if ($stat_media) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Media objects') . '</div>
						<div class="cell small-6"><a href="medialist.php?ged=' . KT_GEDURL . '">' . $stats->totalMedia() . '</a></div>';
					}
					if ($stat_repo) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Repositories') . '</div>
						<div class="cell small-6"><a href="repolist.php?ged=' . KT_GEDURL . '">' . $stats->totalRepositories() . '</a></div>';
					}
					if ($stat_events) {
						$content .= '<div class="cell small-6">' . KT_I18N::translate('Total events') . '</div>
						<div class="cell small-6">' . $stats->totalEvents() . '</div>';
					}
					if ($stat_users) {
						$content .= '
						<div class="cell small-6">' . KT_I18N::translate('Total users') . '</div>
						<div class="cell small-6">';
							if (KT_USER_GEDCOM_ADMIN) {
								$content .= '<a href="admin_users.php">'. $stats->totalUsers() . '</a>';
							} else {
								$content .= $stats->totalUsers();
							}
						$content .= '</div>';
					}
				$content .= '</div>
			</div>
			<div class="cell large-9">
				<div class="grid-x">';
					if ($stat_first_birth) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Earliest birth year') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->firstBirthYear() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->firstBirth() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_last_birth) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Latest birth year') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->lastBirthYear() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->lastBirth() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_first_death) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Earliest death year') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->firstDeathYear() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->firstDeath() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_last_death) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Latest death year') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->lastDeathYear() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->lastDeath() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_long_life) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Person who lived the longest') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->LongestLifeAge() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->LongestLife() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_avg_life) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Average age at death') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->averageLifespan() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . '
								<div><i class="' . $iconStyle . ' fa-male"></i>' . KT_I18N::translate('Males') . ':&nbsp;' . $stats->averageLifespanMale() . '</div>
								<div><i class="' . $iconStyle . ' fa-female"></i>' . KT_I18N::translate('Females') . ':&nbsp;' . $stats->averageLifespanFemale() . '</div>
							</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_most_chil && !$block) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Family with the most children') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->largestFamilySize() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6">' . $stats->largestFamily() . '</div>';
						}
						$content .= '<hr>';
					}
					if ($stat_avg_chil) {
						$content .= '<div class="statistics cell small-4 medium-5">' . KT_I18N::translate('Average number of children per family') . '</div>
						<div class="statistics cell small-2 medium-1">' . $stats->averageChildren() . '</div>';
						if (!$block) {
							$content .= '<div class="statistics cell small-6"></div>';
						}
						$content .= '<hr>';
					}
				$content .= '</div>
			</div>
		</div>';
		if ($stat_link) {
			$content .= '<div class="callout secondary small">
				<a href="statistics.php?ged=' . KT_GEDURL . '">
					<i class="' . $iconStyle . ' fa-sitemap"></i>
					<span>' . KT_I18N::translate('View statistics as graphs') . '</span>
				</a>
			</div>';
		}
		// NOTE: Print the most common surnames
		if ($show_common_surnames) {
			$surnames = get_common_surnames(get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_THRESHOLD'));
			if (count($surnames)>0) {
				$content .= '<div class="callout secondary small">
					<h6 class="font-bold">' . KT_I18N::translate('Most Common Surnames') . '</h6>';
					$i = 0;
					foreach ($surnames as $indexval => $surname) {
						if (stristr($surname['name'], '@N.N') === false) {
							if ($i > 0) {
								$content .= ', ';
							}
							$content .= '<a href="module.php?mod=list_individuals&amp;mod_action=show&amp;surname=' . rawurlencode($surname['name']) . '&amp;ged=' . KT_GEDURL . '">' . $surname['name'] . '</a>';
							$i++;
						}
					}
				$content .= '</div>';
			}
		}

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
		return true;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'show_last_update', KT_Filter::postBool('show_last_update'));
			set_block_setting($block_id, 'show_common_surnames', KT_Filter::postBool('show_common_surnames'));
			set_block_setting($block_id, 'number_of_surnames', KT_Filter::postInteger('number_of_surnames'));
			set_block_setting($block_id, 'stat_indi', KT_Filter::postBool('stat_indi'));
			set_block_setting($block_id, 'stat_fam', KT_Filter::postBool('stat_fam'));
			set_block_setting($block_id, 'stat_sour', KT_Filter::postBool('stat_sour'));
			set_block_setting($block_id, 'stat_media', KT_Filter::postBool('stat_media'));
			set_block_setting($block_id, 'stat_repo', KT_Filter::postBool('stat_repo'));
			set_block_setting($block_id, 'stat_surname', KT_Filter::postBool('stat_surname'));
			set_block_setting($block_id, 'stat_events', KT_Filter::postBool('stat_events'));
			set_block_setting($block_id, 'stat_users', KT_Filter::postBool('stat_users'));
			set_block_setting($block_id, 'stat_first_birth', KT_Filter::postBool('stat_first_birth'));
			set_block_setting($block_id, 'stat_last_birth', KT_Filter::postBool('stat_last_birth'));
			set_block_setting($block_id, 'stat_first_death', KT_Filter::postBool('stat_first_death'));
			set_block_setting($block_id, 'stat_last_death', KT_Filter::postBool('stat_last_death'));
			set_block_setting($block_id, 'stat_long_life', KT_Filter::postBool('stat_long_life'));
			set_block_setting($block_id, 'stat_avg_life', KT_Filter::postBool('stat_avg_life'));
			set_block_setting($block_id, 'stat_most_chil', KT_Filter::postBool('stat_most_chil'));
			set_block_setting($block_id, 'stat_avg_chil', KT_Filter::postBool('stat_avg_chil'));
		}

		$show_last_update     = get_block_setting($block_id, 'show_last_update', '1');
		$show_common_surnames = get_block_setting($block_id, 'show_common_surnames', '1');
		$stat_indi            = get_block_setting($block_id, 'stat_indi', '1');
		$stat_fam             = get_block_setting($block_id, 'stat_fam', '1');
		$stat_sour            = get_block_setting($block_id, 'stat_sour', '1');
		$stat_media           = get_block_setting($block_id, 'stat_media', '1');
		$stat_repo            = get_block_setting($block_id, 'stat_repo', '1');
		$stat_surname         = get_block_setting($block_id, 'stat_surname', '1');
		$stat_events          = get_block_setting($block_id, 'stat_events', '1');
		$stat_users           = get_block_setting($block_id, 'stat_users', '1');
		$stat_first_birth     = get_block_setting($block_id, 'stat_first_birth', '1');
		$stat_last_birth      = get_block_setting($block_id, 'stat_last_birth', '1');
		$stat_first_death     = get_block_setting($block_id, 'stat_first_death', '1');
		$stat_last_death      = get_block_setting($block_id, 'stat_last_death', '1');
		$stat_long_life       = get_block_setting($block_id, 'stat_long_life', '1');
		$stat_avg_life        = get_block_setting($block_id, 'stat_avg_life', '1');
		$stat_most_chil       = get_block_setting($block_id, 'stat_most_chil', '1');
		$stat_avg_chil        = get_block_setting($block_id, 'stat_avg_chil', '1');
		$stat_link			  = get_block_setting($block_id, 'stat_link', '1');

		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		?>

		<div class="cell medium-5">
			 <label><?php echo /* I18N: label for yes/no option */ KT_I18N::translate('Show date of last update?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('show_last_update', $show_last_update); ?>
		</div>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show common surnames?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('show_common_surnames', $show_common_surnames); ?>
		</div>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Select the statistics to show in this block'); ?></label>
		</div>
		<div class="cell medium-7">
			<div class="grid-x grid-padding-x">
				<?php $options = array (
					array('stat_indi', $stat_indi, KT_I18N::translate('Individuals')),
					array('stat_first_birth', $stat_first_birth, KT_I18N::translate('Earliest birth year')),
					array('stat_surname', $stat_surname, KT_I18N::translate('Total surnames')),
					array('stat_last_birth', $stat_last_birth, KT_I18N::translate('Latest birth year')),
					array('stat_fam', $stat_fam, KT_I18N::translate('Families')),
					array('stat_first_death', $stat_first_death, KT_I18N::translate('Earliest death year')),
					array('stat_sour', $stat_sour, KT_I18N::translate('Sources')),
					array('stat_last_death', $stat_last_death, KT_I18N::translate('Latest death year')),
					array('stat_media', $stat_media, KT_I18N::translate('Media objects')),
					array('stat_long_life', $stat_long_life, KT_I18N::translate('Person who lived the longest')),
					array('stat_repo', $stat_repo, KT_I18N::translate('Repositories')),
					array('stat_avg_life', $stat_avg_life, KT_I18N::translate('Average age at death')),
					array('stat_most_chil', $stat_most_chil, KT_I18N::translate('Family with the most children')),
					array('stat_events', $stat_events, KT_I18N::translate('Total events')),
					array('stat_avg_chil', $stat_avg_chil, KT_I18N::translate('Average number of children per family')),
					array('stat_users', $stat_users, KT_I18N::translate('Total users')),
				);
				for ($i = 0; $i < count($options); $i++) {
					echo '
						<div class="cell medium-6">
							<input id="' . $options[$i][0] . '" type="checkbox" value="yes"  name="' . $options[$i][0] . '"'; if ($options[$i][1]) echo ' checked="checked"'; echo '><label for="' . $options[$i][0] . '">' . $options[$i][2] . '</label>
						</div>';
				} ?>
			</div>
		</div>

		<div class="cell medium-5">
			 <label><?php echo KT_I18N::translate('Show link to Statistics charts?'); ?></label>
		</div>
		<div class="cell medium-7">
			<?php echo edit_field_yes_no('stat_link', $stat_link); ?>
		</div>

	<?php }
}
