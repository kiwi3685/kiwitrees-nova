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

/**
 * Definitions for a census
 */
class KT_Census_CensusOfWales1901 extends KT_Census_CensusOfWales implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '31 MAR 1901';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name and surname'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new KT_Census_CensusColumnConditionEnglish($this, 'Condition', 'Marital status'),
			new KT_Census_CensusColumnAgeMale($this, 'AgeM', 'Age (males)'),
			new KT_Census_CensusColumnAgeFemale($this, 'AgeF', 'Age (females)'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Rank, profession or occupation'),
			new KT_Census_CensusColumnNull($this, 'Emp', 'Employer, worker or own account'),
			new KT_Census_CensusColumnNull($this, 'Home', 'Working at home'),
			new KT_Census_CensusColumnBirthPlace($this, 'Birthplace', 'Where born'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'Whether deaf-and-dumb, blind, lunatic, imbecile, feeble-minded'),
			new KT_Census_CensusColumnNull($this, 'Lang', 'Language spoken'),
		);
	}
}
