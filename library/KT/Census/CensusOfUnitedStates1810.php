<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 */


/**
 * Definitions for a census
 */
class KT_Census_CensusOfUnitedStates1810 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '06 AUG 1810';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name of head of family'),
			new KT_Census_CensusColumnNull($this, 'M0-10', 'Free white males 0-10 years'),
			new KT_Census_CensusColumnNull($this, 'M10-16', 'Free white males 10-16 years'),
			new KT_Census_CensusColumnNull($this, 'M16-26', 'Free white males 16-26 years'),
			new KT_Census_CensusColumnNull($this, 'M26-45', 'Free white males 26-45 years'),
			new KT_Census_CensusColumnNull($this, 'M45+', 'Free white males 45+ years'),
			new KT_Census_CensusColumnNull($this, 'F0-10', 'Free white females 0-10 years'),
			new KT_Census_CensusColumnNull($this, 'F10-16', 'Free white females 10-16 years'),
			new KT_Census_CensusColumnNull($this, 'F16-26', 'Free white females 16-26 years'),
			new KT_Census_CensusColumnNull($this, 'F26-45', 'Free white females 26-45 years'),
			new KT_Census_CensusColumnNull($this, 'F45+', 'Free white females 45+ years'),
			new KT_Census_CensusColumnNull($this, 'Free', 'All other free persons, except Indians not taxed'),
			new KT_Census_CensusColumnNull($this, 'Slaves', 'Number of slaves'),
			new KT_Census_CensusColumnNull($this, 'Total', 'Total number of individuals'),

		);
	}
}
