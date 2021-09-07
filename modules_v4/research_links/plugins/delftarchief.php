<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class delftarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Delft Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'https://zoeken.stadsarchiefdelft.nl/';

		$collection = array(
		"Personen"             => "zoeken/groep=Personen/Voornaam=$givn/Achternaam=$surn/aantalpp=50/?nav_id=2-1",
	    "Akten"                => "zoeken/groep=Akten/Voornaam=$givn/Achternaam=$surn/aantalpp=50/?nav_id=2-1",
	    		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
				'link'  => $base_url . $value
			);
		}

		return $link;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
