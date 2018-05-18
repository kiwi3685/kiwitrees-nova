<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rootsweb_plugin extends research_base_plugin {
	static function getName() {
		return 'Rootsweb';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://worldconnect.rootsweb.ancestry.com/cgi-bin/igm.cgi?op=Search&lang=en&surname=' .$surname . '&stype=Exact&given=' . $givn . '&brange=0&drange=0&mrange=0&period=All&submit.x=Search';
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
