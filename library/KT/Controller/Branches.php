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

class KT_Controller_Branches extends KT_Controller_Page {

	/**
	 * Create a branches list controller
	 */
	public function __construct() {
		global $KT_TREE;

		parent::__construct();

		$this->surn			= KT_Filter::get('surname');
		$this->soundex_std	= KT_Filter::getBool('soundex_std');
		$this->soundex_dm	= KT_Filter::getBool('soundex_dm');
		$this->ged			= KT_Filter::get('ged');
		if (empty($this->ged)) {
			$this->ged = $KT_TREE;
		}

		$this->user_ancestors = array();
		if (KT_USER_GEDCOM_ID) {
			$this->load_ancestors_array(KT_Person::getInstance(KT_USER_GEDCOM_ID), 1);
		}

		if ($this->surn) {
			$this->setPageTitle(/* I18N: %s is a surname */ KT_I18N::translate('Branches of the %s family', htmlspecialchars($this->surn)));
		} else {
			$this->setPageTitle(KT_I18N::translate('Branches'));
		}

	}

	function print_fams($person, $famid=null) {
		// select person name according to searched surname
		$person_name = "";
		foreach ($person->getAllNames() as $name) {
			list($this->surn1) = explode(",", $name['sort']);
			if (
				// one name is a substring of the other
				stripos($this->surn1, $this->surn)!==false ||
				stripos($this->surn, $this->surn1)!==false ||
				// one name sounds like the other
				$this->soundex_std && KT_Soundex::compare(KT_Soundex::soundex_std($this->surn1), KT_Soundex::soundex_std($this->surn)) ||
				$this->soundex_dm  && KT_Soundex::compare(KT_Soundex::soundex_dm ($this->surn1), KT_Soundex::soundex_dm ($this->surn))
			) {
				$person_name = $name['full'];
				break;
			}
		}
		if (empty($person_name)) {
			echo '<li title="', strip_tags($person->getFullName()), '">', $person->getSexImage(), '…</li>';
			return;
		}
		// current indi
		echo '<li>';
		$class	= '';
		$sosa	= array_search($person->getXref(), $this->user_ancestors, true);
		if ($sosa) {
			$class	= 'search_hit';
			$sosa	= '<a target="_blank" rel="noopener noreferrer" dir="ltr" class="details1 ' . $person->getBoxStyle() . '" title="' . KT_I18N::translate('Sosa') . '" href="relationship.php?pid2=' . KT_USER_ROOT_ID . '&amp;pid1=' . $person->getXref() . '">&nbsp;' . $sosa . '&nbsp;</a>' . $this->sosa_gen($sosa);
		}
		$current = $person->getSexImage().
			'<a target="_blank" rel="noopener noreferrer" class="' . $class . '" href="' . $person->getHtmlUrl() . '">' . $person_name . '</a> '.
			$person->getLifeSpan() . ' ' . $sosa;
		if ($famid && $person->getChildFamilyPedigree($famid)) {
			$sex		= $person->getSex();
			$famcrec	= get_sub_record(1, '1 FAMC @'.$famid.'@', $person->getGedcomRecord());
			$pedi		= get_gedcom_value('PEDI', 2, $famcrec);
			if ($pedi) {
				$label = KT_Gedcom_Code_Pedi::getValue($pedi, $person);
			}
			$current = '<span class="red">' . $label . '</span> ' . $current;
		}
		// spouses and children
		if (count($person->getSpouseFamilies())<1) {
			echo $current;
		}
		foreach ($person->getSpouseFamilies() as $family) {
			$txt	= $current;
			$spouse	= $family->getSpouse($person);
			if ($spouse) {
				$class = '';
				$sosa2 = array_search($spouse->getXref(), $this->user_ancestors, true);
				if ($sosa2) {
					$class = 'search_hit';
					$sosa2 = '<a target="_blank" rel="noopener noreferrer" dir="ltr" class="details1 ' . $spouse->getBoxStyle().'" title="' . KT_I18N::translate('Sosa') . '" href="relationship.php?pid2=' . KT_USER_ROOT_ID . '&amp;pid1=' . $spouse->getXref() . '">&nbsp;' . $sosa2 . '&nbsp;</a>' . $this->sosa_gen($sosa2);
				}
				$marriage_year=$family->getMarriageYear();
				if ($marriage_year) {
					$txt .= ' <a href="'.$family->getHtmlUrl().'">';
					$txt .= '<span class="details1" title="' . strip_tags($family->getMarriageDate()->Display()) . '"><i class="icon-rings"></i>' . $marriage_year . '</span></a>';
				}
				else if ($family->getMarriage()) {
					$txt .= ' <a href="'.$family->getHtmlUrl().'">';
					$txt .= '<span class="details1" title="' . KT_I18N::translate('yes') . '"><i class="icon-rings"></i></span></a>';
				}
			$txt .=
				$spouse->getSexImage().
				' <a class="' . $class . '" href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a> ' . $spouse->getLifeSpan() . ' ' . $sosa2;
			}
			echo $txt;
			echo '<ul>';
				foreach ($family->getChildren() as $c => $child) {
					$this->print_fams($child, $family->getXref());
				}
			echo '</ul>';
		}
		echo '</li>';
	}

	function load_ancestors_array($person, $sosa=1) {
		if ($person) {
			$this->user_ancestors[$sosa] = $person->getXref();
			foreach ($person->getChildFamilies() as $family) {
				foreach ($family->getSpouses() as $parent) {
					$this->load_ancestors_array($parent, $sosa * 2 + ($parent->getSex() == 'F'));
				}
			}
		}
	}

	function indisArray() {
		$sql =
			"SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
			 FROM `##individuals`
			 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
			 WHERE n_file=?
			 AND n_type!=?
			 AND (n_surn=? OR n_surname=?";
		$args = array(KT_GED_ID, '_MARNM', $this->surn, $this->surn);
		if ($this->soundex_std) {
			foreach (explode(':', KT_Soundex::soundex_std($this->surn)) as $value) {
				$sql .= " OR n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
				$args[] = $value;
			}
		}
		if ($this->soundex_dm) {
			foreach (explode(':', KT_Soundex::soundex_dm($this->surn)) as $value) {
				$sql .= " OR n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
				$args[] = $value;
			}
		}
		$sql .= ')';
		$rows =
			KT_DB::prepare($sql)
			->execute($args)
			->fetchAll(PDO::FETCH_ASSOC);
		$data = array();
		foreach ($rows as $row) {
			$data[] = KT_Person::getInstance($row);
		}
		return $data;
	}

	function sosa_gen($sosa) {
		$gen = (int)log($sosa, 2) + 1;
		return '<sup title="' . KT_I18N::translate('Generation').'">' . $gen . '</sup>';
	}

	function getBranchList() {
		$html = '';
		$indis = $this->indisArray($this->surn, $this->soundex_std, $this->soundex_dm);
		usort($indis, array('KT_Person', 'CompareBirtDate'));
			foreach ($indis as $person) {
				$famc = $person->getPrimaryChildFamily();
				// Don't show INDIs with parents in the list, as they will be shown twice.
				if ($famc) {
					foreach ($famc->getSpouses() as $parent) {
						if (in_array($parent, $indis, true)) {
							continue 2;
						}
					}
				}
				$html .= $this->print_fams($person);
			}

		return $html;

	}

}
