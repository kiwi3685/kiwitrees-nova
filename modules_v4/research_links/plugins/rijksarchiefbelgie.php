<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijksarchiefbelgie_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijksarchief België';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'BEL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = array(
			array(
				'title' => KT_I18N::translate('birth'),
				'link' => 'http://search.arch.be/nl/zoeken-naar-personen/zoekresultaat/q/persoon_achternaam_t_0/' . $surname .'/q/persoon_voornaam_t_0/' . $givn . '/q/zoekwijze/s?M=0&amp;V=0&amp;O=1&amp;persoon_0_periode_soort=geboorte&persoon_0_periode_geen=0',
			),
			array(
				'title' => KT_I18N::translate('death'),
				'link' => 'http://search.arch.be/nl/zoeken-naar-personen/zoekresultaat/q/persoon_achternaam_t_0/' . $surname .'/q/persoon_voornaam_t_0/' . $givn . '/q/zoekwijze/s?M=0&amp;V=0&amp;O=1&amp;persoon_0_periode_soort=overlijden&persoon_0_periode_geen=0',
			)
		);
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
