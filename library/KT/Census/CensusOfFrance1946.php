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

/**
 * Definitions for a census
 */
class KT_Census_CensusOfFrance1946 extends KT_Census_CensusOfFrance implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '17 JAN 1946';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnSurname($this, 'Nom', 'Nom de famille'),
			new KT_Census_CensusColumnGivenNames($this, 'Prénom', 'Prénom usuel'),
			new KT_Census_CensusColumnRelationToHead($this, 'Parenté', 'Parenté avec le chef de ménage ou situation dans le ménage'),
			new KT_Census_CensusColumnBirthYear($this, 'Année', 'Année de naissance'),
			new KT_Census_CensusColumnNationality($this, 'Nationalité', ''),
			new KT_Census_CensusColumnOccupation($this, 'Profession', ''),
		);
	}
}
