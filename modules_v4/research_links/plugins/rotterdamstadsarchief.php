<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rotterdamstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Rotterdam Stadsarchief';
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
		$base_url = 'http://www.stadsarchief.rotterdam.nl/';

		$collection = array(
			"Begraafinschrijving"			=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=158",
			"Doopinschrijving"				=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=156",
			"Echtscheidingsakte"			=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=140",
			"Geboorteakte"					=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=113",
			"Huwelijksakte"					=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=109",
			"Overlijdensakte"				=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=114",
			"Pagina miltaire zaken"		    => "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=765",
			"Pagina politieregister"		=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=852",
			"Passagierslijst"				=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=387",
			"Persoon in bevolkingsregister"	=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=112",
			"Persoonskaart"					=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=455",
			"Trouwinschrijving"				=> "zoek-en-ontdek/stamboom/zoeken-op-personen/?mivast=184&miadt=184&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mib1=157",
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
