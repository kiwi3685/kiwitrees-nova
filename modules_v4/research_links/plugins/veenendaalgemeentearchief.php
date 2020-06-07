<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class veenendaalgemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Veenendaal Gemeentearchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://onderzoek.veenendaal.nl/alles/?mode=gallery&view=list&q=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&reverse=0&oldView=horizontal';
	}
	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
