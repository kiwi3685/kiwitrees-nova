<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijckheyt_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijckheyt';
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
		$base_url = 'http://www.rijckheyt.nl/';

		$collection = array(
		    "Akte van wettiging"   => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=141",
		    "Bidprentje"           => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=111",
		    "Echtscheidingakte"    => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=140",
		    "Geboorteakte"         => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=113",
		    "Huwelijksakte"        => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=109",
		    "Naamswijziging"       => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=389",
		    "Notariele akte"       => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=224",
		    "Overlijdensakte"      => "archief/resultaat?mivast=62&miadt=62&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=114",
		    
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
