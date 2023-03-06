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

 class KT_Controller_Pedigreechart extends KT_Controller_Chart {

    /**
     * Minimum number of displayable generations.
     *
     * @var int
     */
    const MIN_GENERATIONS = 2;

    /**
     * Maximum number of displayable generations.
     *
     * @var int
     */
    const MAX_GENERATIONS = 11;

    /**
     * Number of generations to display.
     *
     * @var int
     */
    public $generations = 6;

    /**
     * Style of fan chart. (2 = full circle, 3, three-quarter circle, 4 = half circle)
     *
     * @var int
     */
    public $fanDegree = 270;

    /**
     * Font size scaling factor in percent.
     *
     * @var int
     */
    public $fontScale = 100;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        // Get default number of generations to display
        $defaultGenerations = get_gedcom_setting(KT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS');

        // Extract the request parameters
        $this->generations = KT_Filter::getInteger('generations', self::MIN_GENERATIONS, self::MAX_GENERATIONS, $defaultGenerations);

		// Create page title
        $title = KT_I18N::translate('Pedigree chart');
        if ($this->root && $this->root->canDisplayName()) {
            $title = KT_I18N::translate('Pedigree chart of %s', $this->root->getFullName());
		}

		$this->setPageTitle($title);
    }

    /**
     * Get the default colors based on the gender of an individual.
     *
     * @param KT_Person $person Individual instance
     *
     * @return string HTML color code
     */
    public function getColor(KT_Person $person = null) {
		global $fanChart;
        if ($person instanceof KT_Person) {
	        if ($person->getSex() === 'M') {
	            return $fanChart['bgMColor'];
	        } elseif ($person->getSex() === 'F') {
	            return $fanChart['bgFColor'];
	        }
		}
        return $fanChart['bgColor'];
    }

	/**
     * Returns the URL of the highlight image of an individual.
     *
     * @param KT_Person $person The current individual
     *
     * @return string
     */
    private function getIndividualImage(KT_Person $person): string {
		global $SHOW_HIGHLIGHT_IMAGES;
        if ($person->canDisplayName() && $SHOW_HIGHLIGHT_IMAGES) {
            $mediaFile = $person->findHighlightedMedia();

            if ($mediaFile !== null) {
                return $mediaFile->getHtmlUrlDirect('thumb');
            }
        }

        return '';
    }

    /**
    * Get the individual data required for display the chart.
    *
    * @param KT_Person $person Start person
    * @param int    $generation Generation the person belongs to
    *
    * @return array
    */
    private function getIndividualData(KT_Person $person, int $generation) {
        return array(
			'id'			=> $person->getXref(),
			'generation'	=> $generation,
			'name'			=> $this->getName($person),
			'thumbnail'		=> $this->getIndividualImage($person),
			'sex'			=> $person->getSex(),
			'lifespan'		=> strip_tags($person->getLifespan()),
			'bplace1'		=> $this->splitPlace1($person, 27),
			'bplace2'		=> $this->splitPlace2($person, 27),
			'color'			=> $this->getColor($person),
        );
    }

	public function getName(KT_Person $person = null) {
		if (!$person instanceof KT_Person) {
			return array();
		}

		$shortName	= KT_Filter::unescapeHtml($person->getShortName());
		$parts		= explode(" ", $shortName);

		if (count($parts) > 2) {
			for ($i = 1; $i < count($parts) - 1; $i++) {
				$parts[$i] = substr($parts[$i], 0, 1);
			}

			return implode(' ', $parts);

		} else {

			return $shortName;

		}
	}


	public function splitPlace1(KT_Person $person = null, int $maxLength) {
		if (!$person instanceof KT_Person) {
			return array();
		}

		$tmp = new KT_Place($person->getBirthPlace(), KT_GED_ID);
		$placeName = strip_tags($tmp->getShortName());

		if (strlen($placeName) > $maxLength) {
			$parts = explode(", ", $placeName);
			$lastComma = strrpos(substr($placeName, 0, $maxLength), ",");
			$parts1 = substr_count($placeName, ",", 0, $lastComma + 1);
			$newPlaceName = array();
			for ($i = 0; $i < $parts1; $i ++) {
				$newPlaceName[] = $parts[$i];
			}

			return implode(', ', $newPlaceName) . ',';

		} else {

			return $placeName;

		}
	}

	public function splitPlace2(KT_Person $person = null, int $maxLength) {
		if (!$person instanceof KT_Person) {
            return array();
        }

		$tmp = new KT_Place($person->getBirthPlace(), KT_GED_ID);
		$placeName = strip_tags($tmp->getShortName());

		if (strlen($placeName) > $maxLength) {
			$parts = explode(", ", $placeName);
			$lastComma = strrpos(substr($placeName, 0, $maxLength), ",");
			$parts1 = substr_count($placeName, ",", 0, $lastComma + 1);
			$newPlaceName = array();
			for ($i = $parts1; $i < count($parts); $i ++) {
				$newPlaceName[] = $parts[$i];
			}

			return implode(', ', $newPlaceName);

		} else {

			return '';

		}
	}




    /**
     * Recursively build the data array of the individual ancestors.
     *
     * @param KT_Person $person     Start person
     * @param int        $generation Current generation
     *
     * @return array
     *
     * @todo Rebuild this to a iterative method
     */
    public function buildJsonTree( KT_Person $person = null, $generation = 1 ) {
		// Maximum generation reached
		if (($generation > $this->generations) || !($person instanceof KT_Person)) {
            return array();
        }

		$data   = $this->getIndividualData($person, $generation);
        $family = $person->getPrimaryChildFamily();

		if (!($family instanceof KT_Family)) {
            return $data;
        }

        // Recursively call the method for the parents of the individual
		$fatherTree = $this->buildJsonTree($family->getHusband(), $generation + 1);
        $motherTree = $this->buildJsonTree($family->getWife(), $generation + 1);

		// Add array of child nodes
        if ($fatherTree) {
            $data['children'][] = $fatherTree;
        }

        if ($motherTree) {
            $data['children'][] = $motherTree;
        }

        return $data;
    }

	/**
     * Get the theme defined chart font color.
     *
     * @return string HTML color code
     */
    public function getChartFontColor() {
		global $fanChart;
        return $fanChart['color'];
    }

	/**
     * Get the raw update url. The "rootid" parameter must be the last one as
     * the url gets appended with the clicked individual id in order to load
     * the required chart data.
     *
     * @return string
     */
    public function getUpdateUrl() {
        $queryData = array(
            'mod'         => 'chart_fanchart',
            'mod_action'  => 'update',
            'ged'         => KT_GEDURL,
            'generations' => $this->generations,
            'rootid'      => '',
        );

        return 'module.php?' . http_build_query($queryData);
    }

	/**
     * Get the raw individual url. The "pid" parameter must be the last one as
     * the url gets appended with the clicked individual id in order to link
     * to the right individual page.
     *
     * @return string
     */
    public function getIndividualUrl() {
		$queryData = array(
			'ged' => KT_GEDURL,
			'pid' => '',
		);

		return 'individual.php?' . http_build_query($queryData);
    }

	/**
     * Returns whether to show empty boxes or not.
     *
     * @return bool
     */
//    public function getShowEmptyBoxes(): bool
//    {
//        return (bool) ($this->request->getQueryParams()['showEmptyBoxes'] ?? false);
//    }

}
