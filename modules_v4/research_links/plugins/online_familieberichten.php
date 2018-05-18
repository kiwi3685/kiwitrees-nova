<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class online_familieberichten_plugin extends research_base_plugin {
	static function getName() {
		return 'Online Familieberichten';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://www.online-familieberichten.nl/zoeken.asp?sortpers=naam&voornaam=' . $givn . '&tussenvoegsel=' . $prefix . '&achternaam=' . $surn . '&command=zoekformres';
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
