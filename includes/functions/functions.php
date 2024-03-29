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

// //////////////////////////////////////////////////////////////////////////////
// Extract, sanitise and validate FORM (POST), URL (GET) and COOKIE variables.
//
// Request variables should ALWAYS be accessed through these functions, to
// protect against XSS (cross-site-scripting) attacks.
//
// $var     - The variable to check
// $regex   - Regular expression to validate the variable (or an array of
//            regular expressions).  A number of common regexes are defined in
//            session.php as constants KT_REGEX_*.  If no value is specified,
//            the default blocks all characters that could introduce scripts.
// $default - A value to use if $var is undefined or invalid.
//
// You should always know whether your variables are coming from GET or POST,
// and always use the correct function.
//
// NOTE: when using checkboxes, $var is either set (checked) or unset (not
// checked).  This lets us use the syntax safe_GET('my_checkbox', 'yes', 'no')
//
// NOTE: when using listboxes, $regex can be an array of valid values.  For
// example, you can use KT_Filter::post('lang', array_keys($pgv_language), KT_LOCALE)
// to validate against a list of valid languages and supply a sensible default.
//
// If the values are plain text, pass them through preg_quote_array() to
// escape any regex special characters:
// $export = safe_GET('export', preg_quote_array($gedcoms));
// //////////////////////////////////////////////////////////////////////////////

function safe_POST($var, $regex = KT_REGEX_NOSCRIPT, $default = null)
{
	return safe_REQUEST($_POST, $var, $regex, $default);
}
function safe_GET($var, $regex = KT_REGEX_NOSCRIPT, $default = null)
{
	return safe_REQUEST($_GET, $var, $regex, $default);
}
function safe_COOKIE($var, $regex = KT_REGEX_NOSCRIPT, $default = null)
{
	return safe_REQUEST($_COOKIE, $var, $regex, $default);
}

function safe_GET_integer($var, $min, $max, $default)
{
	$num = safe_GET($var, KT_REGEX_INTEGER, $default);
	$num = max($num, $min);
	$num = min($num, $max);

	return (int) $num;
}
function safe_POST_integer($var, $min, $max, $default)
{
	$num = KT_Filter::post($var, KT_REGEX_INTEGER, $default);
	$num = max($num, $min);
	$num = min($num, $max);

	return (int) $num;
}

function safe_GET_bool($var, $true = '(y|Y|1|yes|YES|Yes|true|TRUE|True|on)')
{
	return !is_null(safe_GET($var, $true));
}

function safe_POST_bool($var, $true = '(y|Y|1|yes|YES|Yes|true|TRUE|True|on)')
{
	return !is_null(KT_Filter::post($var, $true));
}

function safe_GET_xref($var, $default = null)
{
	return safe_GET($var, KT_REGEX_XREF, $default);
}

function safe_POST_xref($var, $default = null)
{
	return KT_Filter::post($var, KT_REGEX_XREF, $default);
}

function safe_REQUEST($arr, $var, $regex = KT_REGEX_NOSCRIPT, $default = null)
{
	if (is_array($regex)) {
		$regex = '(?:'.join('|', $regex).')';
	}
	if (array_key_exists($var, $arr) && preg_match_recursive('~^'.addcslashes($regex, '~').'$~', $arr[$var])) {
		return $arr[$var];
	}

	return $default;
}

function preg_quote_array($var)
{
	if (is_scalar($var)) {
		return preg_quote($var);
	}
	if (is_array($var)) {
		foreach ($var as &$v) {
			$v = preg_quote($v);
		}

		return $var;
	}
	// Neither scalar nor array.  Object?
	return false;
}

function preg_match_recursive($regex, $var)
{
	if (is_scalar($var)) {
		return preg_match($regex, $var);
	}
	if (is_array($var)) {
		foreach ($var as $k => $v) {
			if (!preg_match_recursive($regex, $v)) {
				return false;
			}
		}

		return true;
	}
	// Neither scalar nor array.  Object?
	return false;
}

// Fetch a remote file.  Stream wrappers are disabled on
// many hosts, and do not allow the detection of timeout.
function fetch_remote_file($host, $path, $timeout = 3)
{
	$fp = @fsockopen($host, '80', $errno, $errstr, $timeout);
	if (!$fp) {
		return null;
	}

	fputs($fp, "GET {$path} HTTP/1.0\r\nHost: {$host}\r\nConnection: Close\r\n\r\n");

	$response = '';
	while ($data = fread($fp, 65536)) {
		$response .= $data;
	}
	fclose($fp);

	// Take account of a “moved” response.
	if ('HTTP/1.1 303' == substr($response, 0, 12) && preg_match('/\nLocation: http:\/\/([a-z0-9.-]+)(.+)/', $response, $match)) {
		return fetch_remote_file($match[1], $match[2]);
	}
	// The response includes headers, a blank line, then the content
	return substr($response, strpos($response, "\r\n\r\n") + 4);
}

// Check with the kiwitrees.net server for the latest version of kiwitrees.
// Fetching the remote file can be slow, so check infrequently, and cache the result.
function fetch_latest_version()
{
	$last_update_timestamp = KT_Site::preference('LATEST_KT_VERSION_TIMESTAMP');
	if ($last_update_timestamp < KT_TIMESTAMP - 24 * 60 * 60) {
		$row = KT_DB::prepare("SHOW VARIABLES LIKE 'version'")->fetchOneRow();
		$latest_version_txt = fetch_remote_file('www.kiwitrees.net', '/latest_ktn_version.txt');
		if ($latest_version_txt) {
			KT_Site::preference('LATEST_KT_VERSION', $latest_version_txt);
			KT_Site::preference('LATEST_KT_VERSION_TIMESTAMP', KT_TIMESTAMP);

			return $latest_version_txt;
		}
		// Cannot connect to server - use cached version (if we have one)
		return KT_Site::preference('LATEST_KT_VERSION');
	}

	return KT_Site::preference('LATEST_KT_VERSION');
}

// Convert a file upload PHP error code into user-friendly text
function file_upload_error_text($error_code)
{
	switch ($error_code) {
		case UPLOAD_ERR_OK:
			return KT_I18N::translate('File successfully uploaded');

		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('Uploaded file exceeds the allowed size');

		case UPLOAD_ERR_PARTIAL:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('File was only partially uploaded, please try again');

		case UPLOAD_ERR_NO_FILE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('No file was received. Please upload again.');

		case UPLOAD_ERR_NO_TMP_DIR:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('Missing PHP temporary directory');

		case UPLOAD_ERR_CANT_WRITE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('PHP failed to write to disk');

		case UPLOAD_ERR_EXTENSION:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('PHP blocked file by extension');
	}
}

function load_gedcom_settings($ged_id = KT_GED_ID)
{
	// Load the configuration settings into global scope
	// TODO: some of these are used infrequently - just load them when we need them
	global $ABBREVIATE_CHART_LABELS;
	$ABBREVIATE_CHART_LABELS = get_gedcom_setting($ged_id, 'ABBREVIATE_CHART_LABELS');
	global $ADVANCED_NAME_FACTS;
	$ADVANCED_NAME_FACTS = get_gedcom_setting($ged_id, 'ADVANCED_NAME_FACTS');
	global $ADVANCED_PLAC_FACTS;
	$ADVANCED_PLAC_FACTS = get_gedcom_setting($ged_id, 'ADVANCED_PLAC_FACTS');
	global $ALL_CAPS;
	$ALL_CAPS = get_gedcom_setting($ged_id, 'ALL_CAPS');
	global $CALENDAR_FORMAT;
	$CALENDAR_FORMAT = get_gedcom_setting($ged_id, 'CALENDAR_FORMAT');
	global $CHART_BOX_TAGS;
	$CHART_BOX_TAGS = get_gedcom_setting($ged_id, 'CHART_BOX_TAGS');
	global $CONTACT_USER_ID;
	$CONTACT_USER_ID = get_gedcom_setting($ged_id, 'CONTACT_USER_ID');
	global $DEFAULT_PEDIGREE_GENERATIONS;
	$DEFAULT_PEDIGREE_GENERATIONS = get_gedcom_setting($ged_id, 'DEFAULT_PEDIGREE_GENERATIONS');
	global $EXPAND_NOTES;
	$EXPAND_NOTES = get_gedcom_setting($ged_id, 'EXPAND_NOTES');
	global $EXPAND_SOURCES;
	$EXPAND_SOURCES = get_gedcom_setting($ged_id, 'EXPAND_SOURCES');
	global $FAM_ID_PREFIX;
	$FAM_ID_PREFIX = get_gedcom_setting($ged_id, 'FAM_ID_PREFIX');
	global $FULL_SOURCES;
	$FULL_SOURCES = get_gedcom_setting($ged_id, 'FULL_SOURCES');
	global $GEDCOM_ID_PREFIX;
	$GEDCOM_ID_PREFIX = get_gedcom_setting($ged_id, 'GEDCOM_ID_PREFIX');
	global $GEDCOM_MEDIA_PATH;
	$GEDCOM_MEDIA_PATH = get_gedcom_setting($ged_id, 'GEDCOM_MEDIA_PATH');
	global $GENERATE_UIDS;
	$GENERATE_UIDS = get_gedcom_setting($ged_id, 'GENERATE_UIDS');
	global $HIDE_GEDCOM_ERRORS;
	$HIDE_GEDCOM_ERRORS = get_gedcom_setting($ged_id, 'HIDE_GEDCOM_ERRORS');
	global $HIDE_LIVE_PEOPLE;
	$HIDE_LIVE_PEOPLE = get_gedcom_setting($ged_id, 'HIDE_LIVE_PEOPLE');
	global $IMAGE_EDITOR;
	$IMAGE_EDITOR = get_gedcom_setting($ged_id, 'IMAGE_EDITOR');
	global $KEEP_ALIVE_YEARS_BIRTH;
	$KEEP_ALIVE_YEARS_BIRTH = get_gedcom_setting($ged_id, 'KEEP_ALIVE_YEARS_BIRTH');
	global $KEEP_ALIVE_YEARS_DEATH;
	$KEEP_ALIVE_YEARS_DEATH = get_gedcom_setting($ged_id, 'KEEP_ALIVE_YEARS_DEATH');
	global $LANGUAGE;
	$LANGUAGE = get_gedcom_setting($ged_id, 'LANGUAGE');
	global $MAX_ALIVE_AGE;
	$MAX_ALIVE_AGE = get_gedcom_setting($ged_id, 'MAX_ALIVE_AGE');
	global $MAX_DESCENDANCY_GENERATIONS;
	$MAX_DESCENDANCY_GENERATIONS = get_gedcom_setting($ged_id, 'MAX_DESCENDANCY_GENERATIONS');
	global $MAX_PEDIGREE_GENERATIONS;
	$MAX_PEDIGREE_GENERATIONS = get_gedcom_setting($ged_id, 'MAX_PEDIGREE_GENERATIONS');
	global $MEDIA_DIRECTORY;
	$MEDIA_DIRECTORY = get_gedcom_setting($ged_id, 'MEDIA_DIRECTORY');
	global $MEDIA_ID_PREFIX;
	$MEDIA_ID_PREFIX = get_gedcom_setting($ged_id, 'MEDIA_ID_PREFIX');
	global $NOTE_ID_PREFIX;
	$NOTE_ID_PREFIX = get_gedcom_setting($ged_id, 'NOTE_ID_PREFIX');
	global $NO_UPDATE_CHAN;
	$NO_UPDATE_CHAN = get_gedcom_setting($ged_id, 'NO_UPDATE_CHAN');
	global $PEDIGREE_FULL_DETAILS;
	$PEDIGREE_FULL_DETAILS = get_gedcom_setting($ged_id, 'PEDIGREE_FULL_DETAILS');
	global $PEDIGREE_LAYOUT;
	$PEDIGREE_LAYOUT = get_gedcom_setting($ged_id, 'PEDIGREE_LAYOUT');
	global $PEDIGREE_SHOW_GENDER;
	$PEDIGREE_SHOW_GENDER = get_gedcom_setting($ged_id, 'PEDIGREE_SHOW_GENDER');
	global $QUICK_REQUIRED_FACTS;
	$QUICK_REQUIRED_FACTS = get_gedcom_setting($ged_id, 'QUICK_REQUIRED_FACTS');
	global $QUICK_REQUIRED_FAMFACTS;
	$QUICK_REQUIRED_FAMFACTS = get_gedcom_setting($ged_id, 'QUICK_REQUIRED_FAMFACTS');
	global $REPO_ID_PREFIX;
	$REPO_ID_PREFIX = get_gedcom_setting($ged_id, 'REPO_ID_PREFIX');
	global $SAVE_WATERMARK_IMAGE;
	$SAVE_WATERMARK_IMAGE = get_gedcom_setting($ged_id, 'SAVE_WATERMARK_IMAGE');
	global $SAVE_WATERMARK_THUMB;
	$SAVE_WATERMARK_THUMB = get_gedcom_setting($ged_id, 'SAVE_WATERMARK_THUMB');
	global $SHOW_COUNTER;
	$SHOW_COUNTER = get_gedcom_setting($ged_id, 'SHOW_COUNTER');
	global $SHOW_DEAD_PEOPLE;
	$SHOW_DEAD_PEOPLE = get_gedcom_setting($ged_id, 'SHOW_DEAD_PEOPLE');
	global $SHOW_FACT_ICONS;
	$SHOW_FACT_ICONS = get_gedcom_setting($ged_id, 'SHOW_FACT_ICONS');
	global $SHOW_GEDCOM_RECORD;
	$SHOW_GEDCOM_RECORD = get_gedcom_setting($ged_id, 'SHOW_GEDCOM_RECORD');
	global $SHOW_HIGHLIGHT_IMAGES;
	$SHOW_HIGHLIGHT_IMAGES = get_gedcom_setting($ged_id, 'SHOW_HIGHLIGHT_IMAGES');
	global $SHOW_LAST_CHANGE;
	$SHOW_LAST_CHANGE = get_gedcom_setting($ged_id, 'SHOW_LAST_CHANGE');
	global $SHOW_LDS_AT_GLANCE;
	$SHOW_LDS_AT_GLANCE = get_gedcom_setting($ged_id, 'SHOW_LDS_AT_GLANCE');
	global $SHOW_LIVING_NAMES;
	$SHOW_LIVING_NAMES = get_gedcom_setting($ged_id, 'SHOW_LIVING_NAMES');
	global $SHOW_MEDIA_DOWNLOAD;
	$SHOW_MEDIA_DOWNLOAD = get_gedcom_setting($ged_id, 'SHOW_MEDIA_DOWNLOAD');
	global $SHOW_NO_WATERMARK;
	$SHOW_NO_WATERMARK = get_gedcom_setting($ged_id, 'SHOW_NO_WATERMARK');
	global $SHOW_PARENTS_AGE;
	$SHOW_PARENTS_AGE = get_gedcom_setting($ged_id, 'SHOW_PARENTS_AGE');
	global $SHOW_PEDIGREE_PLACES;
	$SHOW_PEDIGREE_PLACES = get_gedcom_setting($ged_id, 'SHOW_PEDIGREE_PLACES');
	global $SHOW_PEDIGREE_PLACES_SUFFIX;
	$SHOW_PEDIGREE_PLACES_SUFFIX = get_gedcom_setting($ged_id, 'SHOW_PEDIGREE_PLACES_SUFFIX');
	global $SHOW_PRIVATE_RELATIONSHIPS;
	$SHOW_PRIVATE_RELATIONSHIPS = get_gedcom_setting($ged_id, 'SHOW_PRIVATE_RELATIONSHIPS');
	global $SHOW_RELATIVES_EVENTS;
	$SHOW_RELATIVES_EVENTS = get_gedcom_setting($ged_id, 'SHOW_RELATIVES_EVENTS');
	global $SOURCE_ID_PREFIX;
	$SOURCE_ID_PREFIX = get_gedcom_setting($ged_id, 'SOURCE_ID_PREFIX');
	global $SURNAME_LIST_STYLE;
	$SURNAME_LIST_STYLE = get_gedcom_setting($ged_id, 'SURNAME_LIST_STYLE');
	global $THUMBNAIL_WIDTH;
	$THUMBNAIL_WIDTH = get_gedcom_setting($ged_id, 'THUMBNAIL_WIDTH');
	global $USE_RIN;
	$USE_RIN = get_gedcom_setting($ged_id, 'USE_RIN');
	global $USE_SILHOUETTE;
	$USE_SILHOUETTE = get_gedcom_setting($ged_id, 'USE_SILHOUETTE');
	global $WATERMARK_THUMB;
	$WATERMARK_THUMB = get_gedcom_setting($ged_id, 'WATERMARK_THUMB');
	global $WEBMASTER_USER_ID;
	$WEBMASTER_USER_ID = get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID');
	global $KIWITREES_EMAIL;
	$KIWITREES_EMAIL = get_gedcom_setting($ged_id, 'KIWITREES_EMAIL');
	global $WORD_WRAPPED_NOTES;
	$WORD_WRAPPED_NOTES = get_gedcom_setting($ged_id, 'WORD_WRAPPED_NOTES');

	global $person_privacy;
	$person_privacy = [];
	global $person_facts;
	$person_facts = [];
	global $global_facts;
	$global_facts = [];

	$rows = KT_DB::prepare(
		"SELECT xref, tag_type, CASE resn WHEN 'none' THEN ? WHEN 'privacy' THEN ? WHEN 'confidential' THEN ? WHEN 'hidden' THEN ? END AS resn FROM `##default_resn` WHERE gedcom_id=?"
	)->execute([KT_PRIV_PUBLIC, KT_PRIV_USER, KT_PRIV_NONE, KT_PRIV_HIDE, $ged_id])->fetchAll();

	foreach ($rows as $row) {
		if (null !== $row->xref) {
			if (null !== $row->tag_type) {
				$person_facts[$row->xref][$row->tag_type] = (int) $row->resn;
			} else {
				$person_privacy[$row->xref] = (int) $row->resn;
			}
		} else {
			$global_facts[$row->tag_type] = (int) $row->resn;
		}
	}
}

/**
 * Kiwitrees Error Handling function.
 *
 * This function will be called by PHP whenever an error occurs.  The error handling
 * is set in the session.php
 *
 * @see http://us2.php.net/manual/en/function.set-error-handler.php
 *
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 */
function kt_error_handler($errno, $errstr, $errfile, $errline)
{
	if ((error_reporting() > 0) && ($errno < 2048)) {
		if (KT_ERROR_LEVEL == 0) {
			return;
		}
		$fmt_msg = '<br>ERROR ' . $errno . ' : ' . $errstr . '<br>';
		$log_msg = 'ERROR  ' . $errno . ' : ' . $errstr;
		// Although debug_backtrace should always exist in PHP5, without this check, PHP sometimes crashes.
		// Possibly calling it generates an error, which causes infinite recursion??
		if ($errno < 16 && function_exists('debug_backtrace') && false === strstr($errstr, 'headers already sent by')) {
			$backtrace = debug_backtrace();
			$num = count($backtrace);
			if (KT_ERROR_LEVEL == 1) {
				$num = 1;
			}
			for ($i = 0; $i < $num; ++$i) {
				if (0 == $i) {
					$fmt_msg .= '0 Error occurred on ';
					$log_msg .= "\n0 Error occurred on ";
				} else {
					$fmt_msg .= "{$i} called from ";
					$log_msg .= "\n{$i} called from ";
				}
				if (isset($backtrace[$i]['line'], $backtrace[$i]['file'])) {
					$fmt_msg .= "line <b>{$backtrace[$i]['line']}</b> of file <b>".basename($backtrace[$i]['file']).'</b>';
					$log_msg .= "line {$backtrace[$i]['line']} of file ".basename($backtrace[$i]['file']);
				}
				if ($i < $num - 1) {
					$fmt_msg .= ' in function <b>'.$backtrace[$i + 1]['function'].'</b>';
					$log_msg .= ' in function '.$backtrace[$i + 1]['function'];
				}
				$fmt_msg .= '<br>';
			}
		}
		echo $fmt_msg;
		if (function_exists('AddToLog')) {
			AddToLog($log_msg, 'error');
		}
		if (1 == $errno) {
			exit;
		}
	}

	return false;
}

// ************************************************* START OF GEDCOM FUNCTIONS ********************************* //

/**
 * Get first tag in GEDCOM sub-record.
 *
 * This routine uses function get_sub_record to retrieve the specified sub-record
 * and then returns the first tag.
 *
 * @param mixed $level
 * @param mixed $tag
 * @param mixed $gedrec
 * @param mixed $num
 */
function get_first_tag($level, $tag, $gedrec, $num = 1)
{
	$temp = get_sub_record($level, $level.' '.$tag, $gedrec, $num)."\n";
	$length = strpos($temp, "\n");
	if (false === $length) {
		$length = strlen($temp);
	}

	return substr($temp, 2, $length - 2);
}

/**
 * get a gedcom subrecord.
 *
 * searches a gedcom record and returns a subrecord of it.  A subrecord is defined starting at a
 * line with level N and all subsequent lines greater than N until the next N level is reached.
 * For example, the following is a BIRT subrecord:
 * <code>1 BIRT
 * 2 DATE 1 JAN 1900
 * 2 PLAC Phoenix, Maricopa, Arizona</code>
 * The following example is the DATE subrecord of the above BIRT subrecord:
 * <code>2 DATE 1 JAN 1900</code>
 *
 * @author John Finlay (yalnifj)
 * @author Roland Dalmulder (roland-d)
 *
 * @param int    $level  the N level of the subrecord to get
 * @param string $tag    a gedcom tag or string to search for in the record (ie 1 BIRT or 2 DATE)
 * @param string $gedrec the parent gedcom record to search in
 * @param int    $num    this allows you to specify which matching <var>$tag</var> to get.  Oftentimes a
 *                       gedcom record will have more that 1 of the same type of subrecord.  An individual may have
 *                       multiple events for example.  Passing $num = 1 would get the first 1.  Passing $num = 2 would get the
 *                       second one, etc.
 *
 * @return string the subrecord that was found or an empty string "" if not found
 */
function get_sub_record($level, $tag, $gedrec, $num = 1)
{
	if (empty($gedrec)) {
		return '';
	}
	// -- adding \n before and after gedrec
	$gedrec = "\n".$gedrec."\n";
	$pos1 = 0;
	$subrec = '';
	$tag = trim($tag);
	$searchTarget = "~[\n]".$tag.'[\\s]~';
	$ct = preg_match_all($searchTarget, $gedrec, $match, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	if (0 == $ct) {
		$tag = preg_replace('/(\w+)/', '_$1', $tag);
		$ct = preg_match_all($searchTarget, $gedrec, $match, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		if (0 == $ct) {
			return '';
		}
	}
	if ($ct < $num) {
		return '';
	}
	$pos1 = $match[$num - 1][0][1];
	$pos2 = strpos($gedrec, "\n{$level}", $pos1 + 1);
	if (!$pos2) {
		$pos2 = strpos($gedrec, "\n1", $pos1 + 1);
	}
	if (!$pos2) {
		$pos2 = strpos($gedrec, "\nKT_", $pos1 + 1); // KT_SPOUSE, KT_FAMILY_ID ...
	}
	if (!$pos2) {
		return ltrim(substr($gedrec, $pos1));
	}
	$subrec = substr($gedrec, $pos1, $pos2 - $pos1);

	return ltrim($subrec);
}

/**
 * get gedcom tag value.
 *
 * returns the value of a gedcom tag from the given gedcom record
 *
 * @param string $tag      The tag to find, use : to delineate subtags
 * @param int    $level    The gedcom line level of the first tag to find, setting level to 0 will cause it to use 1+ the level of the incoming record
 * @param string $gedrec   The gedcom record to get the value from
 * @param int    $truncate Should the value be truncated to a certain number of characters
 *
 * @return string
 */
function get_gedcom_value($tag, $level, $gedrec, $truncate = '')
{
	global $GEDCOM;
	$ged_id = get_id_from_gedcom($GEDCOM);

	if (empty($gedrec)) {
		return '';
	}
	$tags = explode(':', $tag);
	$origlevel = $level;
	if (0 == $level) {
		$level = (int) $gedrec[0] + 1;
	}

	$subrec = $gedrec;
	foreach ($tags as $indexval => $t) {
		$lastsubrec = $subrec;
		$subrec = get_sub_record($level, "{$level} {$t}", $subrec);
		if (empty($subrec) && 0 == $origlevel) {
			--$level;
			$subrec = get_sub_record($level, "{$level} {$t}", $lastsubrec);
		}
		if (empty($subrec)) {
			if ('TITL' == $t) {
				$subrec = get_sub_record($level, "{$level} ABBR", $lastsubrec);
				if (!empty($subrec)) {
					$t = 'ABBR';
				}
			}
			if (empty($subrec)) {
				if ($level > 0) {
					--$level;
				}
				$subrec = get_sub_record($level, "@ {$t}", $gedrec);
				if (empty($subrec)) {
					return;
				}
			}
		}
		++$level;
	}
	--$level;
	$ct = preg_match("/{$level} {$t}(.*)/", $subrec, $match);
	if (0 == $ct) {
		$ct = preg_match("/{$level} @.+@ (.+)/", $subrec, $match);
	}
	if (0 == $ct) {
		$ct = preg_match("/@ {$t} (.+)/", $subrec, $match);
	}
	if ($ct > 0) {
		$value = trim($match[1]);
		if ('NOTE' == $t && preg_match('/^@(.+)@$/', $value, $match)) {
			$oldsub = $subrec;
			$subrec = find_other_record($match[1], $ged_id);
			if ($subrec) {
				$value = $match[1];
				$ct = preg_match("/0 @{$match[1]}@ {$t} (.+)/", $subrec, $match);
				if ($ct > 0) {
					$value = $match[1];
					$level = 0;
				} else {
					$subrec = $oldsub;
				}
			} else {
				// -- set the value to the id without the @
				$value = $match[1];
			}
		}
		if (0 != $level || 'NOTE' != $t) {
			$value .= get_cont($level + 1, $subrec);
		}
		$value = preg_replace("'\n'", '', $value);
		$value = preg_replace("'<br>'", "\n", $value);

		return $value;
	}

	return '';
}

/**
 * create CONT lines.
 *
 * Break input GEDCOM subrecord into pieces not more than 255 chars long,
 * with CONC and CONT lines as needed.  Routine also pays attention to the
 * word wrapped Notes option.  Routine also avoids splitting UTF-8 encoded
 * characters between lines.
 *
 * @param string $newline Input GEDCOM subrecord to be worked on
 *
 * @return string $newged Output string with all necessary CONC and CONT lines
 */
function breakConts($newline)
{
	global $WORD_WRAPPED_NOTES;

	// Determine level number of CONC and CONT lines
	$level = substr($newline, 0, 1);
	$tag = substr($newline, 1, 6);
	if (' CONC ' != $tag && ' CONT ' != $tag) {
		++$level;
	}

	$newged = '';
	$newlines = preg_split("/\n/", rtrim($newline));
	for ($k = 0; $k < count($newlines); ++$k) {
		if ($k > 0) {
			$newlines[$k] = "{$level} CONT ".$newlines[$k];
		}
		$newged .= trim($newlines[$k])."\n";
	}

	return $newged;
}

/**
 * get CONT lines.
 *
 * get the N+1 CONT or CONC lines of a gedcom subrecord
 *
 * @param int    $nlevel the level of the CONT lines to get
 * @param string $nrec   the gedcom subrecord to search in
 *
 * @return string a string with all CONT or CONC lines merged
 */
function get_cont($nlevel, $nrec)
{
	global $WORD_WRAPPED_NOTES;
	$text = '';

	$subrecords = explode("\n", $nrec);
	foreach ($subrecords as $thisSubrecord) {
		if (substr($thisSubrecord, 0, 2) != $nlevel.' ') {
			continue;
		}
		$subrecordType = substr($thisSubrecord, 2, 4);
		if ('CONT' == $subrecordType) {
			$text .= "\n";
		}
		if ('CONC' == $subrecordType && $WORD_WRAPPED_NOTES) {
			$text .= ' ';
		}
		if ('CONT' == $subrecordType || 'CONC' == $subrecordType) {
			$text .= rtrim(substr($thisSubrecord, 7));
		}
	}

	return rtrim($text, ' ');
}

/**
 * find the parents in a family.
 *
 * find and return a two element array containing the parents of the given family record
 *
 * @author John Finlay (yalnifj)
 *
 * @param string $famid the gedcom xref id for the family
 *
 * @return array returns a two element array with indexes HUSB and WIFE for the parent ids
 */
function find_parents($famid)
{
	$famrec = find_gedcom_record($famid, KT_GED_ID, KT_USER_CAN_EDIT);
	if (empty($famrec)) {
		return false;
	}

	return find_parents_in_record($famrec);
}

/**
 * find the parents in a family record.
 *
 * find and return a two element array containing the parents of the given family record
 *
 * @author John Finlay (yalnifj)
 *
 * @param string $famrec the gedcom record of the family to search in
 *
 * @return array returns a two element array with indexes HUSB and WIFE for the parent ids
 */
function find_parents_in_record($famrec)
{
	if (empty($famrec)) {
		return false;
	}
	$parents = [];
	$ct = preg_match('/1 HUSB @('.KT_REGEX_XREF.')@/', $famrec, $match);
	if ($ct > 0) {
		$parents['HUSB'] = $match[1];
	} else {
		$parents['HUSB'] = '';
	}
	$ct = preg_match('/1 WIFE @('.KT_REGEX_XREF.')@/', $famrec, $match);
	if ($ct > 0) {
		$parents['WIFE'] = $match[1];
	} else {
		$parents['WIFE'] = '';
	}

	return $parents;
}

// ************************************************* START OF SORTING FUNCTIONS ********************************* //
// Function to sort GEDCOM fact tags based on their tanslations
function factsort($a, $b)
{
	return utf8_strcasecmp(KT_I18N::translate($a), KT_I18N::translate($b));
}

// //////////////////////////////////////////////////////////////////////////////
// Sort a list events for the today/upcoming blocks
// //////////////////////////////////////////////////////////////////////////////
function event_sort($a, $b)
{
	if ($a['jd'] == $b['jd']) {
		if ($a['anniv'] == $b['anniv']) {
			return utf8_strcasecmp($a['fact'], $b['fact']);
		}

		return $a['anniv'] - $b['anniv'];
	}

	return $a['jd'] - $b['jd'];
}

function event_sort_name($a, $b)
{
	if ($a['jd'] == $b['jd']) {
		return KT_GedcomRecord::compare($a['record'], $b['record']);
	}

	return $a['jd'] - $b['jd'];
}

// Helper function to sort facts.
function compare_facts_date($arec, $brec)
{
	if (is_array($arec)) {
		$arec = $arec[1];
	}
	if (is_array($brec)) {
		$brec = $brec[1];
	}

	// If either fact is undated, the facts sort equally.
	if (!preg_match('/2 _?DATE (.*)/', $arec, $amatch) || !preg_match('/2 _?DATE (.*)/', $brec, $bmatch)) {
		if (preg_match('/2 _SORT (\d+)/', $arec, $match1) && preg_match('/2 _SORT (\d+)/', $brec, $match2)) {
			return $match1[1] - $match2[1];
		}

		return 0;
	}

	$adate = new KT_Date($amatch[1]);
	$bdate = new KT_Date($bmatch[1]);
	// If either date can’t be parsed, don’t sort.
	if (!$adate->isOK() || !$bdate->isOK()) {
		if (preg_match('/2 _SORT (\d+)/', $arec, $match1) && preg_match('/2 _SORT (\d+)/', $brec, $match2)) {
			return $match1[1] - $match2[1];
		}

		return 0;
	}

	// Remember that dates can be ranges and overlapping ranges sort equally.
	$amin = $adate->MinJD();
	$bmin = $bdate->MinJD();
	$amax = $adate->MaxJD();
	$bmax = $bdate->MaxJD();

	// BEF/AFT XXX sort as the day before/after XXX
	if ('BEF' == $adate->qual1) {
		$amin = $amin - 1;
		$amax = $amin;
	} elseif ('AFT' == $adate->qual1) {
		$amax = $amax + 1;
		$amin = $amax;
	}
	if ('BEF' == $bdate->qual1) {
		$bmin = $bmin - 1;
		$bmax = $bmin;
	} elseif ('AFT' == $bdate->qual1) {
		$bmax = $bmax + 1;
		$bmin = $bmax;
	}

	if ($amax < $bmin) {
		return -1;
	}
	if ($amin > $bmax) {
		return 1;
	}
	// -- ranged date... take the type of fact sorting into account
	$factWeight = 0;
	if (preg_match('/2 _SORT (\d+)/', $arec, $match1) && preg_match('/2 _SORT (\d+)/', $brec, $match2)) {
		$factWeight = $match1[1] - $match2[1];
	}
	// -- fact is prefered to come before, so compare using the minimum ranges
	if ($factWeight < 0 && $amin != $bmin) {
		return $amin - $bmin;
	}
	if ($factWeight > 0 && $bmax != $amax) {
		// -- fact is prefered to come after, so compare using the max of the ranges
		return $bmax - $amax;
	}
	// -- facts are the same or the ranges don’t give enough info, so use the average of the range
	$aavg = ($amin + $amax) / 2;
	$bavg = ($bmin + $bmax) / 2;
	if ($aavg < $bavg) {
		return -1;
	}
	if ($aavg > $bavg) {
		return 1;
	}

	return $factWeight;

	return 0;
}

/**
 * A multi-key sort
 * 1. First divide the facts into two arrays one set with dates and one set without dates
 * 2. Sort each of the two new arrays, the date using the compare date function, the non-dated
 * using the compare type function
 * 3. Then merge the arrays back into the original array using the compare type function.
 *
 * @param unknown_type $arr
 */
function sort_facts(&$arr)
{
	$dated = [];
	$nondated = [];
	// -- split the array into dated and non-dated arrays
	$order = 0;
	foreach ($arr as $event) {
		$event->sortOrder = $order;
		++$order;
		if (null == $event->getValue('DATE') || !$event->getDate()->isOk()) {
			$nondated[] = $event;
		} else {
			$dated[] = $event;
		}
	}

	// -- sort each type of array
	usort($dated, ['KT_Event', 'CompareDate']);
	usort($nondated, ['KT_Event', 'CompareType']);

	// -- merge the arrays back together comparing by Facts
	$dc = count($dated);
	$nc = count($nondated);
	$i = 0;
	$j = 0;
	$k = 0;
	// while there is anything in the dated array continue merging
	while ($i < $dc) {
		// compare each fact by type to merge them in order
		if ($j < $nc && KT_Event::CompareType($dated[$i], $nondated[$j]) > 0) {
			$arr[$k] = $nondated[$j];
			++$j;
		} else {
			$arr[$k] = $dated[$i];
			++$i;
		}
		++$k;
	}

	// get anything that might be left in the nondated array
	while ($j < $nc) {
		$arr[$k] = $nondated[$j];
		++$j;
		++$k;
	}
}

/**
 * Get relationship between two individuals in the gedcom.
 *
 * @param string $pid1         - the ID of the first person to compute the relationship from
 * @param string $pid2         - the ID of the second person to compute the relatiohip to
 * @param bool   $followspouse = whether to add spouses to the path
 * @param int    $maxlength    - the maximum length of path
 * @param int    $path_to_find - which path in the relationship to find, 0 is the shortest path, 1 is the next shortest path, etc
 */
function get_relationship(KT_Person $person1, KT_Person $person2, $followspouse = true, $maxlength = 0, $path_to_find = 0)
{
	if (!$person1 || !$person2 || $person1->equals($person2)) {
		return false;
	}

	// -- current path nodes
	$p1nodes = [];
	// -- ids visited
	$visited = [];

	// -- set up first node for person1
	$node1 = [
		'path' => [$person1],
		'length' => 0,
		'indi' => $person1,
		'relations' => ['self'],
	];
	$p1nodes[] = $node1;

	$visited[$person1->getXref()] = true;

	$found = false;
	while (!$found) {
		// -- search the node list for the shortest path length
		$shortest = -1;
		foreach ($p1nodes as $index => $node) {
			if (-1 == $shortest) {
				$shortest = $index;
			} else {
				$node1 = $p1nodes[$shortest];
				if ($node1['length'] > $node['length']) {
					$shortest = $index;
				}
			}
		}
		if (-1 == $shortest) {
			return false;
		}
		$node = $p1nodes[$shortest];
		if (0 == $maxlength || count($node['path']) <= $maxlength) {
			$indi = $node['indi'];
			// -- check all parents and siblings of this node
			foreach ($indi->getChildFamilies(KT_PRIV_HIDE) as $family) {
				$visited[$family->getXref()] = true;
				foreach ($family->getSpouses(KT_PRIV_HIDE) as $spouse) {
					if (!isset($visited[$spouse->getXref()])) {
						$node1 = $node;
						++$node1['length'];
						$node1['path'][] = $spouse;
						$node1['indi'] = $spouse;
						$node1['relations'][] = 'parent';
						$p1nodes[] = $node1;
						if ($spouse->equals($person2)) {
							if ($path_to_find > 0) {
								--$path_to_find;
							} else {
								$found = true;
								$resnode = $node1;
							}
						} else {
							$visited[$spouse->getXref()] = true;
						}
					}
				}
				foreach ($family->getChildren(KT_PRIV_HIDE) as $child) {
					if (!isset($visited[$child->getXref()])) {
						$node1 = $node;
						++$node1['length'];
						$node1['path'][] = $child;
						$node1['indi'] = $child;
						$node1['relations'][] = 'sibling';
						$p1nodes[] = $node1;
						if ($child->equals($person2)) {
							if ($path_to_find > 0) {
								--$path_to_find;
							} else {
								$found = true;
								$resnode = $node1;
							}
						} else {
							$visited[$child->getXref()] = true;
						}
					}
				}
			}
			// -- check all spouses and children of this node
			foreach ($indi->getSpouseFamilies(KT_PRIV_HIDE) as $family) {
				$visited[$family->getXref()] = true;
				if ($followspouse) {
					foreach ($family->getSpouses(KT_PRIV_HIDE) as $spouse) {
						if (!in_array($spouse->getXref(), $node1) || !isset($visited[$spouse->getXref()])) {
							$node1 = $node;
							++$node1['length'];
							$node1['path'][] = $spouse;
							$node1['indi'] = $spouse;
							$node1['relations'][] = 'spouse';
							$p1nodes[] = $node1;
							if ($spouse->equals($person2)) {
								if ($path_to_find > 0) {
									--$path_to_find;
								} else {
									$found = true;
									$resnode = $node1;
								}
							} else {
								$visited[$spouse->getXref()] = true;
							}
						}
					}
				}
				foreach ($family->getChildren(KT_PRIV_HIDE) as $child) {
					if (!isset($visited[$child->getXref()])) {
						$node1 = $node;
						++$node1['length'];
						$node1['path'][] = $child;
						$node1['indi'] = $child;
						$node1['relations'][] = 'child';
						$p1nodes[] = $node1;
						if ($child->equals($person2)) {
							if ($path_to_find > 0) {
								--$path_to_find;
							} else {
								$found = true;
								$resnode = $node1;
							}
						} else {
							$visited[$child->getXref()] = true;
						}
					}
				}
			}
		}
		unset($p1nodes[$shortest]);
	}

	// Convert generic relationships into sex-specific ones.
	foreach ($resnode['path'] as $n => $indi) {
		switch ($resnode['relations'][$n]) {
			case 'parent':
				switch ($indi->getSex()) {
					case 'M': $resnode['relations'][$n] = 'father';

					break;

					case 'F': $resnode['relations'][$n] = 'mother';

					break;
				}

				break;

			case 'child':
				switch ($indi->getSex()) {
					case 'M': $resnode['relations'][$n] = 'son';

					break;

					case 'F': $resnode['relations'][$n] = 'daughter';

					break;
				}

				break;

			case 'spouse':
				switch ($indi->getSex()) {
					case 'M': $resnode['relations'][$n] = 'husband';

					break;

					case 'F': $resnode['relations'][$n] = 'wife';

					break;
				}

				break;

			case 'sibling':
				switch ($indi->getSex()) {
					case 'M': $resnode['relations'][$n] = 'brother';

					break;

					case 'F': $resnode['relations'][$n] = 'sister';

					break;
				}

				break;
		}
	}

	return $resnode;
}

// Convert the result of get_relationship() into a relationship name.
function get_relationship_name($nodes)
{
	if (!is_array($nodes)) {
		return '';
	}
	$person1 = $nodes['path'][0];
	$person2 = $nodes['path'][count($nodes['path']) - 1];
	$path = array_slice($nodes['relations'], 1);
	// Look for paths with *specific* names first.
	// Note that every combination must be listed separately, as the same English
	// name can be used for many different relationships.  e.g.
	// brother’s wife & husband’s sister = sister-in-law.
	//
	// $path is an array of the 12 possible gedcom family relationships:
	// mother/father/parent
	// brother/sister/sibling
	// husband/wife/spouse
	// son/daughter/child
	//
	// This is always the shortest path, so “father, daughter” is “half-sister”, not “sister”.
	//
	// This is very repetitive in English, but necessary in order to handle the
	// complexities of other languages.

	// Make each relationship parts the same length, for simpler matching.
	$combined_path = '';
	foreach ($path as $rel) {
		$combined_path .= substr($rel, 0, 3);
	}

	return get_relationship_name_from_path($combined_path, $person1, $person2);
}

/**
 * For close family relationships, such as the families tab and the family navigator
 * Display a tick if both individuals are the same.
 *
 * @param Individual $individual1
 * @param Individual $individual2
 * @param mixed      $family
 *
 * @return string
 */
function getCloseRelationshipName(KT_Person $individual1, KT_Person $individual2, $family = '')
{
	global $iconStyle;
	$family ? $include_pedi = true : $include_pedi = false;

	if ($individual1->getXref() === $individual2->getXref()) {
		$label = '<span class="success strong">' . reflexivePronoun($individual1) . '</span>';
	} else {
		$label = get_relationship_name(get_relationship($individual1, $individual2));
	}

	if ($family) {
		$gedrec = $individual2->getGedcomRecord();
		preg_match('/\n1 ADOP(\n[2-9].*)*\n2 FAMC @(.+)@/', $gedrec, $match);
		if ($match) {
			preg_match('~2 FAMC @(.*?)@~', $match[0], $output);
			$famcrec = get_sub_record(1, '1 FAMC @'.$output[1].'@', $gedrec);
			$pedi = get_gedcom_value('PEDI', 2, $famcrec);
			if ($pedi && $output[1] == $family) {
				$label .= '<br>('.KT_Gedcom_Code_Pedi::getValue($pedi, $individual2).')';
			}
		}
	}

	return $label;
}

/**
 * Generate a reflexive pronoun for an individual.
 *
 * @param Individual $individual
 *
 * @return string
 */
function reflexivePronoun(KT_Person $individual)
{
	switch ($individual->getSex()) {
		case 'M':
			return /* I18N: reflexive pronoun */ KT_I18N::translate('himself');

		case 'F':
			return /* I18N: reflexive pronoun */ KT_I18N::translate('herself');

		default:
			return /* I18N: reflexive pronoun - gender neutral version of himself/herself */ KT_I18N::translate('themself');
	}
}

function cousin_name($n, $sex)
{
	switch ($sex) {
		case 'M':
			switch ($n) {
				case 1:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'first cousin');

				case 2:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'second cousin');

				case 3:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'third cousin');

				case 4:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'fourth cousin');

				case 5:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'fifth cousin');

				case 6:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'sixth cousin');

				case 7:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'seventh cousin');

				case 8:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'eighth cousin');

				case 9:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'ninth cousin');

				case 10:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'tenth cousin');

				case 11:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'eleventh cousin');

				case 12:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'twelfth cousin');

				case 13:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'thirteenth cousin');

				case 14:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'fourteenth cousin');

				case 15:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', 'fifteenth cousin');

				default:
					// I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers.
					return KT_I18N::translate_c('MALE', '%d x cousin', $n);
			}
			// no break
		case 'F':
			switch ($n) {
				case 1: return KT_I18N::translate_c('FEMALE', 'first cousin');

				case 2: return KT_I18N::translate_c('FEMALE', 'second cousin');

				case 3: return KT_I18N::translate_c('FEMALE', 'third cousin');

				case 4: return KT_I18N::translate_c('FEMALE', 'fourth cousin');

				case 5: return KT_I18N::translate_c('FEMALE', 'fifth cousin');

				case 6: return KT_I18N::translate_c('FEMALE', 'sixth cousin');

				case 7: return KT_I18N::translate_c('FEMALE', 'seventh cousin');

				case 8: return KT_I18N::translate_c('FEMALE', 'eighth cousin');

				case 9: return KT_I18N::translate_c('FEMALE', 'ninth cousin');

				case 10: return KT_I18N::translate_c('FEMALE', 'tenth cousin');

				case 11: return KT_I18N::translate_c('FEMALE', 'eleventh cousin');

				case 12: return KT_I18N::translate_c('FEMALE', 'twelfth cousin');

				case 13: return KT_I18N::translate_c('FEMALE', 'thirteenth cousin');

				case 14: return KT_I18N::translate_c('FEMALE', 'fourteenth cousin');

				case 15: return KT_I18N::translate_c('FEMALE', 'fifteenth cousin');

				default: return KT_I18N::translate_c('FEMALE', '%d x cousin', $n);
			}
			// no break
		case 'U':
			switch ($n) {
				case 1: return KT_I18N::translate_c('MALE/FEMALE', 'first cousin');

				case 2: return KT_I18N::translate_c('MALE/FEMALE', 'second cousin');

				case 3: return KT_I18N::translate_c('MALE/FEMALE', 'third cousin');

				case 4: return KT_I18N::translate_c('MALE/FEMALE', 'fourth cousin');

				case 5: return KT_I18N::translate_c('MALE/FEMALE', 'fifth cousin');

				case 6: return KT_I18N::translate_c('MALE/FEMALE', 'sixth cousin');

				case 7: return KT_I18N::translate_c('MALE/FEMALE', 'seventh cousin');

				case 8: return KT_I18N::translate_c('MALE/FEMALE', 'eighth cousin');

				case 9: return KT_I18N::translate_c('MALE/FEMALE', 'ninth cousin');

				case 10: return KT_I18N::translate_c('MALE/FEMALE', 'tenth cousin');

				case 11: return KT_I18N::translate_c('MALE/FEMALE', 'eleventh cousin');

				case 12: return KT_I18N::translate_c('MALE/FEMALE', 'twelfth cousin');

				case 13: return KT_I18N::translate_c('MALE/FEMALE', 'thirteenth cousin');

				case 14: return KT_I18N::translate_c('MALE/FEMALE', 'fourteenth cousin');

				case 15: return KT_I18N::translate_c('MALE/FEMALE', 'fifteenth cousin');

				default: return KT_I18N::translate_c('MALE/FEMALE', '%d x cousin', $n);
			}
	}
}

// A variation on cousin_name(), for constructs such as “sixth great-nephew”
// Currently used only by Spanish relationship names.
function cousin_name2($n, $sex, $relation)
{
	switch ($sex) {
		case 'M':
			switch ($n) {
				case 1: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('MALE', 'first %s', $relation);

				case 2: return KT_I18N::translate_c('MALE', 'second %s', $relation);

				case 3: return KT_I18N::translate_c('MALE', 'third %s', $relation);

				case 4: return KT_I18N::translate_c('MALE', 'fourth %s', $relation);

				case 5: return KT_I18N::translate_c('MALE', 'fifth %s', $relation);

				default: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('MALE', '%1$d x %2$s', $n, $relation);
			}
			// no break
		case 'F':
			switch ($n) {
				case 1: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('FEMALE', 'first %s', $relation);

				case 2: return KT_I18N::translate_c('FEMALE', 'second %s', $relation);

				case 3: return KT_I18N::translate_c('FEMALE', 'third %s', $relation);

				case 4: return KT_I18N::translate_c('FEMALE', 'fourth %s', $relation);

				case 5: return KT_I18N::translate_c('FEMALE', 'fifth %s', $relation);

				default: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('FEMALE', '%1$d x %2$s', $n, $relation);
			}
			// no break
		case 'U':
			switch ($n) {
				case 1: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('MALE/FEMALE', 'first %s', $relation);

				case 2: return KT_I18N::translate_c('MALE/FEMALE', 'second %s', $relation);

				case 3: return KT_I18N::translate_c('MALE/FEMALE', 'third %s', $relation);

				case 4: return KT_I18N::translate_c('MALE/FEMALE', 'fourth %s', $relation);

				case 5: return KT_I18N::translate_c('MALE/FEMALE', 'fifth %s', $relation);

				default: // I18N: A Spanish relationship name, such as third great-nephew
					return KT_I18N::translate_c('MALE/FEMALE', '%1$d x %2$s', $n, $relation);
			}
	}
}

function get_relationship_name_from_path($path, KT_Person $person1 = null, KT_Person $person2 = null)
{
	if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)*$/', $path)) {
		// TODO: Update all the “3 RELA ” values in class_person
		return '<span class="error">'.$path.'</span>';
	}
	// The path does not include the starting person.  In some languages, the
	// translation for a man’s (relative) is different to a woman’s (relative),
	// due to inflection.
	$sex1 = $person1 ? $person1->getSex() : 'U';

	// The sex of the last person in the relationship determines the name in
	// many cases.  e.g. great-aunt / great-uncle
	if (preg_match('/(fat|hus|son|bro)$/', $path)) {
		$sex2 = 'M';
	} elseif (preg_match('/(mot|wif|dau|sis)$/', $path)) {
		$sex2 = 'F';
	} else {
		$sex2 = 'U';
	}

	switch ($path) {
		case '': return KT_I18N::translate('self');
			//  Level One relationships
		case 'mot': return KT_I18N::translate('mother');

		case 'fat': return KT_I18N::translate('father');

		case 'par': return KT_I18N::translate('parent');

		case 'hus':
			if ($person1 && $person2) {
				foreach ($person1->getSpouseFamilies() as $family) {
					if ($person2 === $family->getSpouse($person1)) {
						if ($family->isNotMarried()) {
							if ($family->isDivorced()) {
								return KT_I18N::translate_c('MALE', 'ex-partner');
							}

							return KT_I18N::translate_c('MALE', 'partner');
						} elseif ($family->isDivorced()) {
							return KT_I18N::translate('ex-husband');
						}
					}
				}
			}

			return KT_I18N::translate('husband');

		case 'wif':
			if ($person1 && $person2) {
				foreach ($person1->getSpouseFamilies() as $family) {
					if ($person2 === $family->getSpouse($person1)) {
						if ($family->isNotMarried()) {
							if ($family->isDivorced()) {
								return KT_I18N::translate_c('FEMALE', 'ex-partner');
							}

							return KT_I18N::translate_c('FEMALE', 'partner');
						} elseif ($family->isDivorced()) {
							return KT_I18N::translate('ex-wife');
						}
					}
				}
			}

			return KT_I18N::translate('wife');

		case 'spo':
			if ($person1 && $person2) {
				foreach ($person1->getSpouseFamilies() as $family) {
					if ($person2 === $family->getSpouse($person1)) {
						if ($family->isNotMarried()) {
							if ($family->isDivorced()) {
								return KT_I18N::translate('ex-partner');
							}

							return KT_I18N::translate('partner');
						} elseif ($family->isDivorced()) {
							return KT_I18N::translate('ex-spouse');
						}
					}
				}
			}

			return KT_I18N::translate('spouse');

		case 'son': return KT_I18N::translate('son');

		case 'dau': return KT_I18N::translate('daughter');

		case 'chi': return KT_I18N::translate('child');

		case 'bro':
			if ($person1 && $person2) {
				$dob1 = $person1->getBirthDate();
				$dob2 = $person2->getBirthDate();
				if ($dob1->isOK() && $dob2->isOK()) {
					if (abs($dob1->JD() - $dob2->JD()) < 2 && 0 !== !$dob1->MinDate()->d && 0 !== !$dob2->MinDate()->d) { // Exclude BEF, AFT, etc.
						return KT_I18N::translate('twin brother');
					}
					if ($dob1->MaxJD() < $dob2->MinJD()) {
						return KT_I18N::translate('younger brother');
					}
					if ($dob1->MinJD() > $dob2->MaxJD()) {
						return KT_I18N::translate('elder brother');
					}
				}
			}

			return KT_I18N::translate('brother');

		case 'sis':
			if ($person1 && $person2) {
				$dob1 = $person1->getBirthDate();
				$dob2 = $person2->getBirthDate();
				if ($dob1->isOK() && $dob2->isOK()) {
					if (abs($dob1->JD() - $dob2->JD()) < 2 && 0 !== !$dob1->MinDate()->d && 0 !== !$dob2->MinDate()->d) { // Exclude BEF, AFT, etc.
						return KT_I18N::translate('twin sister');
					}
					if ($dob1->MaxJD() < $dob2->MinJD()) {
						return KT_I18N::translate('younger sister');
					}
					if ($dob1->MinJD() > $dob2->MaxJD()) {
						return KT_I18N::translate('elder sister');
					}
				}
			}

			return KT_I18N::translate('sister');

		case 'sib':
			if ($person1 && $person2) {
				$dob1 = $person1->getBirthDate();
				$dob2 = $person2->getBirthDate();
				if ($dob1->isOK() && $dob2->isOK()) {
					if (abs($dob1->JD() - $dob2->JD()) < 2 && 0 !== !$dob1->MinDate()->d && 0 !== !$dob2->MinDate()->d) { // Exclude BEF, AFT, etc.
						return KT_I18N::translate('twin sibling');
					}
					if ($dob1->MaxJD() < $dob2->MinJD()) {
						return KT_I18N::translate('younger sibling');
					}
					if ($dob1->MinJD() > $dob2->MaxJD()) {
						return KT_I18N::translate('elder sibling');
					}
				}
			}

			return KT_I18N::translate('sibling');
			// Level Two relationships
		case 'brochi': return KT_I18N::translate_c('brother\'s child', 'nephew/niece');

		case 'brodau': return KT_I18N::translate_c('brother\'s daughter', 'niece');

		case 'broson': return KT_I18N::translate_c('brother\'s son', 'nephew');

		case 'browif': return KT_I18N::translate_c('brother\'s wife', 'sister-in-law');

		case 'chichi': return KT_I18N::translate_c('child\'s child', 'grandchild');

		case 'chidau': return KT_I18N::translate_c('child\'s daughter', 'granddaughter');

		case 'chihus': return KT_I18N::translate_c('child\'s husband', 'son-in-law');

		case 'chison': return KT_I18N::translate_c('child\'s son', 'grandson');

		case 'chispo': return KT_I18N::translate_c('child\'s spouse', 'son/daughter-in-law');

		case 'chiwif': return KT_I18N::translate_c('child\'s wife', 'daughter-in-law');

		case 'dauchi': return KT_I18N::translate_c('daughter\'s child', 'grandchild');

		case 'daudau': return KT_I18N::translate_c('daughter\'s daughter', 'granddaughter');

		case 'dauhus': return KT_I18N::translate_c('daughter\'s husband', 'son-in-law');

		case 'dauson': return KT_I18N::translate_c('daughter\'s son', 'grandson');

		case 'fatbro': return KT_I18N::translate_c('father\'s brother', 'uncle');

		case 'fatchi': return KT_I18N::translate_c('father\'s child', 'half-sibling');

		case 'fatdau': return KT_I18N::translate_c('father\'s daughter', 'half-sister');

		case 'fatfat': return KT_I18N::translate_c('father\'s father', 'paternal grandfather');

		case 'fatmot': return KT_I18N::translate_c('father\'s mother', 'paternal grandmother');

		case 'fatpar': return KT_I18N::translate_c('father\'s parent', 'paternal grandparent');

		case 'fatsib': return KT_I18N::translate_c('father\'s sibling', 'aunt/uncle');

		case 'fatsis': return KT_I18N::translate_c('father\'s sister', 'aunt');

		case 'fatson': return KT_I18N::translate_c('father\'s son', 'half-brother');

		case 'fatwif': return KT_I18N::translate_c('father\'s wife', 'step-mother');

		case 'husbro': return KT_I18N::translate_c('husband\'s brother', 'brother-in-law');

		case 'huschi': return KT_I18N::translate_c('husband\'s child', 'step-child');

		case 'husdau': return KT_I18N::translate_c('husband\'s daughter', 'step-daughter');

		case 'husfat': return KT_I18N::translate_c('husband\'s father', 'father-in-law');

		case 'husmot': return KT_I18N::translate_c('husband\'s mother', 'mother-in-law');

		case 'hussib': return KT_I18N::translate_c('husband\'s sibling', 'brother/sister-in-law');

		case 'hussis': return KT_I18N::translate_c('husband\'s sister', 'sister-in-law');

		case 'husson': return KT_I18N::translate_c('husband\'s son', 'step-son');

		case 'motbro': return KT_I18N::translate_c('mother\'s brother', 'uncle');

		case 'motchi': return KT_I18N::translate_c('mother\'s child', 'half-sibling');

		case 'motdau': return KT_I18N::translate_c('mother\'s daughter', 'half-sister');

		case 'motfat': return KT_I18N::translate_c('mother\'s father', 'maternal grandfather');

		case 'mothus': return KT_I18N::translate_c('mother\'s husband', 'step-father');

		case 'motmot': return KT_I18N::translate_c('mother\'s mother', 'maternal grandmother');

		case 'motpar': return KT_I18N::translate_c('mother\'s parent', 'maternal grandparent');

		case 'motsib': return KT_I18N::translate_c('mother\'s sibling', 'aunt/uncle');

		case 'motsis': return KT_I18N::translate_c('mother\'s sister', 'aunt');

		case 'motson': return KT_I18N::translate_c('mother\'s son', 'half-brother');

		case 'parbro': return KT_I18N::translate_c('parent\'s brother', 'uncle');

		case 'parchi': return KT_I18N::translate_c('parent\'s child', 'half-sibling');

		case 'pardau': return KT_I18N::translate_c('parent\'s daughter', 'half-sister');

		case 'parfat': return KT_I18N::translate_c('parent\'s father', 'grandfather');

		case 'parmot': return KT_I18N::translate_c('parent\'s mother', 'grandmother');

		case 'parpar': return KT_I18N::translate_c('parent\'s parent', 'grandparent');

		case 'parsib': return KT_I18N::translate_c('parent\'s sibling', 'aunt/uncle');

		case 'parsis': return KT_I18N::translate_c('parent\'s sister', 'aunt');

		case 'parson': return KT_I18N::translate_c('parent\'s son', 'half-brother');

		case 'parspo': return KT_I18N::translate_c('parent\'s spouse', 'step-parent');

		case 'sibchi': return KT_I18N::translate_c('sibling\'s child', 'nephew/niece');

		case 'sibdau': return KT_I18N::translate_c('sibling\'s daughter', 'niece');

		case 'sibson': return KT_I18N::translate_c('sibling\'s son', 'nephew');

		case 'sibspo': return KT_I18N::translate_c('sibling\'s spouse', 'brother/sister-in-law');

		case 'sischi': return KT_I18N::translate_c('sister\'s child', 'nephew/niece');

		case 'sisdau': return KT_I18N::translate_c('sister\'s daughter', 'niece');

		case 'sishus': return KT_I18N::translate_c('sister\'s husband', 'brother-in-law');

		case 'sisson': return KT_I18N::translate_c('sister\'s son', 'nephew');

		case 'sonchi': return KT_I18N::translate_c('son\'s child', 'grandchild');

		case 'sondau': return KT_I18N::translate_c('son\'s daughter', 'granddaughter');

		case 'sonson': return KT_I18N::translate_c('son\'s son', 'grandson');

		case 'sonwif': return KT_I18N::translate_c('son\'s wife', 'daughter-in-law');

		case 'spobro': return KT_I18N::translate_c('spouses\'s brother', 'brother-in-law');

		case 'spochi': return KT_I18N::translate_c('spouses\'s child', 'step-child');

		case 'spodau': return KT_I18N::translate_c('spouses\'s daughter', 'step-daughter');

		case 'spofat': return KT_I18N::translate_c('spouses\'s father', 'father-in-law');

		case 'spomot': return KT_I18N::translate_c('spouses\'s mother', 'mother-in-law');

		case 'sposis': return KT_I18N::translate_c('spouses\'s sister', 'sister-in-law');

		case 'sposon': return KT_I18N::translate_c('spouses\'s son', 'step-son');

		case 'spopar': return KT_I18N::translate_c('spouses\'s parent', 'mother/father-in-law');

		case 'sposib': return KT_I18N::translate_c('spouses\'s sibling', 'brother/sister-in-law');

		case 'wifbro': return KT_I18N::translate_c('wife\'s brother', 'brother-in-law');

		case 'wifchi': return KT_I18N::translate_c('wife\'s child', 'step-child');

		case 'wifdau': return KT_I18N::translate_c('wife\'s daughter', 'step-daughter');

		case 'wiffat': return KT_I18N::translate_c('wife\'s father', 'father-in-law');

		case 'wifmot': return KT_I18N::translate_c('wife\'s mother', 'mother-in-law');

		case 'wifsib': return KT_I18N::translate_c('wife\'s sibling', 'brother/sister-in-law');

		case 'wifsis': return KT_I18N::translate_c('wife\'s sister', 'sister-in-law');

		case 'wifson': return KT_I18N::translate_c('wife\'s son', 'step-son');
			// Level Three relationships
			// I have commented out some of the unknown-sex relationships that are unlikely to to occur.
			// Feel free to add them in, if you think they might be needed
		case 'brochichi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s child\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s child\'s child', 'great-nephew/niece');

		case 'brochidau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s child\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s child\'s daughter', 'great-niece');

		case 'brochison': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s child\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s child\'s son', 'great-nephew');

		case 'brodauchi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s daughter\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s daughter\'s child', 'great-nephew/niece');

		case 'brodaudau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s daughter\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s daughter\'s daughter', 'great-niece');

		case 'brodauhus': return KT_I18N::translate_c('brother\'s daughter\'s husband', 'nephew-in-law');

		case 'brodauson': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s daughter\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s daughter\'s son', 'great-nephew');

		case 'brosonchi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s son\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s son\'s child', 'great-nephew/niece');

		case 'brosondau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s son\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s son\'s daughter', 'great-niece');

		case 'brosonson': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) brother\'s son\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) brother\'s son\'s son', 'great-nephew');

		case 'brosonwif': return KT_I18N::translate_c('brother\'s son\'s wife', 'niece-in-law');

		case 'browifbro': return KT_I18N::translate_c('brother\'s wife\'s brother', 'brother-in-law');

		case 'browifsib': return KT_I18N::translate_c('brother\'s wife\'s sibling', 'brother/sister-in-law');

		case 'browifsis': return KT_I18N::translate_c('brother\'s wife\'s sister', 'sister-in-law');

		case 'chichichi': return KT_I18N::translate_c('child\'s child\'s child', 'great-grandchild');

		case 'chichidau': return KT_I18N::translate_c('child\'s child\'s daughter', 'great-granddaughter');

		case 'chichison': return KT_I18N::translate_c('child\'s child\'s son', 'great-grandson');

		case 'chidauchi': return KT_I18N::translate_c('child\'s daughter\'s child', 'great-grandchild');

		case 'chidaudau': return KT_I18N::translate_c('child\'s daughter\'s daughter', 'great-granddaughter');

		case 'chidauhus': return KT_I18N::translate_c('child\'s daughter\'s husband', 'granddaughter\'s husband');

		case 'chidauson': return KT_I18N::translate_c('child\'s daughter\'s son', 'great-grandson');

		case 'chisonchi': return KT_I18N::translate_c('child\'s son\'s child', 'great-grandchild');

		case 'chisondau': return KT_I18N::translate_c('child\'s son\'s daughter', 'great-granddaughter');

		case 'chisonson': return KT_I18N::translate_c('child\'s son\'s son', 'great-grandson');

		case 'chisonwif': return KT_I18N::translate_c('child\'s son\'s wife', 'grandson\'s wife');

		case 'dauchichi': return KT_I18N::translate_c('daughter\'s child\'s child', 'great-grandchild');

		case 'dauchidau': return KT_I18N::translate_c('daughter\'s child\'s daughter', 'great-granddaughter');

		case 'dauchison': return KT_I18N::translate_c('daughter\'s child\'s son', 'great-grandson');

		case 'daudauchi': return KT_I18N::translate_c('daughter\'s daughter\'s child', 'great-grandchild');

		case 'daudaudau': return KT_I18N::translate_c('daughter\'s daughter\'s daughter', 'great-granddaughter');

		case 'daudauhus': return KT_I18N::translate_c('daughter\'s daughter\'s husband', 'granddaughter\'s husband');

		case 'daudauson': return KT_I18N::translate_c('daughter\'s daughter\'s son', 'great-grandson');

		case 'dauhusfat': return KT_I18N::translate_c('daughter\'s husband\'s father', 'son-in-law\'s father');

		case 'dauhusmot': return KT_I18N::translate_c('daughter\'s husband\'s mother', 'son-in-law\'s mother');

		case 'dauhuspar': return KT_I18N::translate_c('daughter\'s husband\'s parent', 'son-in-law\'s parent');

		case 'dausonchi': return KT_I18N::translate_c('daughter\'s son\'s child', 'great-grandchild');

		case 'dausondau': return KT_I18N::translate_c('daughter\'s son\'s daughter', 'great-granddaughter');

		case 'dausonson': return KT_I18N::translate_c('daughter\'s son\'s son', 'great-grandson');

		case 'dausonwif': return KT_I18N::translate_c('daughter\'s son\'s wife', 'grandson\'s wife');

		case 'fatbrochi': return KT_I18N::translate_c('father\'s brother\'s child', 'first cousin');

		case 'fatbrodau': return KT_I18N::translate_c('father\'s brother\'s daughter', 'first cousin');

		case 'fatbroson': return KT_I18N::translate_c('father\'s brother\'s son', 'first cousin');

		case 'fatbrowif': return KT_I18N::translate_c('father\'s brother\'s wife', 'aunt');

		case 'fatfatbro': return KT_I18N::translate_c('father\'s father\'s brother', 'great-uncle');

		case 'fatfatfat': return KT_I18N::translate_c('father\'s father\'s father', 'great-grandfather');

		case 'fatfatmot': return KT_I18N::translate_c('father\'s father\'s mother', 'great-grandmother');

		case 'fatfatpar': return KT_I18N::translate_c('father\'s father\'s parent', 'great-grandparent');

		case 'fatfatsib': return KT_I18N::translate_c('father\'s father\'s sibling', 'great-aunt/uncle');

		case 'fatfatsis': return KT_I18N::translate_c('father\'s father\'s sister', 'great-aunt');

		case 'fatmotbro': return KT_I18N::translate_c('father\'s mother\'s brother', 'great-uncle');

		case 'fatmotfat': return KT_I18N::translate_c('father\'s mother\'s father', 'great-grandfather');

		case 'fatmotmot': return KT_I18N::translate_c('father\'s mother\'s mother', 'great-grandmother');

		case 'fatmotpar': return KT_I18N::translate_c('father\'s mother\'s parent', 'great-grandparent');

		case 'fatmotsib': return KT_I18N::translate_c('father\'s mother\'s sibling', 'great-aunt/uncle');

		case 'fatmotsis': return KT_I18N::translate_c('father\'s mother\'s sister', 'great-aunt');

		case 'fatparbro': return KT_I18N::translate_c('father\'s parent\'s brother', 'great-uncle');

		case 'fatparfat': return KT_I18N::translate_c('father\'s parent\'s father', 'great-grandfather');

		case 'fatparmot': return KT_I18N::translate_c('father\'s parent\'s mother', 'great-grandmother');

		case 'fatparpar': return KT_I18N::translate_c('father\'s parent\'s parent', 'great-grandparent');

		case 'fatparsib': return KT_I18N::translate_c('father\'s parent\'s sibling', 'great-aunt/uncle');

		case 'fatparsis': return KT_I18N::translate_c('father\'s parent\'s sister', 'great-aunt');

		case 'fatsischi': return KT_I18N::translate_c('father\'s sister\'s child', 'first cousin');

		case 'fatsisdau': return KT_I18N::translate_c('father\'s sister\'s daughter', 'first cousin');

		case 'fatsishus': return KT_I18N::translate_c('father\'s sister\'s husband', 'uncle');

		case 'fatsisson': return KT_I18N::translate_c('father\'s sister\'s son', 'first cousin');

		case 'fatwifchi': return KT_I18N::translate_c('father\'s wife\'s child', 'step-sibling');

		case 'fatwifdau': return KT_I18N::translate_c('father\'s wife\'s daughter', 'step-sister');

		case 'fatwifson': return KT_I18N::translate_c('father\'s wife\'s son', 'step-brother');

		case 'husbrowif': return KT_I18N::translate_c('husband\'s brother\'s wife', 'sister-in-law');

		case 'hussishus': return KT_I18N::translate_c('husband\'s sister\'s husband', 'brother-in-law');

		case 'motbrochi': return KT_I18N::translate_c('mother\'s brother\'s child', 'first cousin');

		case 'motbrodau': return KT_I18N::translate_c('mother\'s brother\'s daughter', 'first cousin');

		case 'motbroson': return KT_I18N::translate_c('mother\'s brother\'s son', 'first cousin');

		case 'motbrowif': return KT_I18N::translate_c('mother\'s brother\'s wife', 'aunt');

		case 'motfatbro': return KT_I18N::translate_c('mother\'s father\'s brother', 'great-uncle');

		case 'motfatfat': return KT_I18N::translate_c('mother\'s father\'s father', 'great-grandfather');

		case 'motfatmot': return KT_I18N::translate_c('mother\'s father\'s mother', 'great-grandmother');

		case 'motfatpar': return KT_I18N::translate_c('mother\'s father\'s parent', 'great-grandparent');

		case 'motfatsib': return KT_I18N::translate_c('mother\'s father\'s sibling', 'great-aunt/uncle');

		case 'motfatsis': return KT_I18N::translate_c('mother\'s father\'s sister', 'great-aunt');

		case 'mothuschi': return KT_I18N::translate_c('mother\'s husband\'s child', 'step-sibling');

		case 'mothusdau': return KT_I18N::translate_c('mother\'s husband\'s daughter', 'step-sister');

		case 'mothusson': return KT_I18N::translate_c('mother\'s husband\'s son', 'step-brother');

		case 'motmotbro': return KT_I18N::translate_c('mother\'s mother\'s brother', 'great-uncle');

		case 'motmotfat': return KT_I18N::translate_c('mother\'s mother\'s father', 'great-grandfather');

		case 'motmotmot': return KT_I18N::translate_c('mother\'s mother\'s mother', 'great-grandmother');

		case 'motmotpar': return KT_I18N::translate_c('mother\'s mother\'s parent', 'great-grandparent');

		case 'motmotsib': return KT_I18N::translate_c('mother\'s mother\'s sibling', 'great-aunt/uncle');

		case 'motmotsis': return KT_I18N::translate_c('mother\'s mother\'s sister', 'great-aunt');

		case 'motparbro': return KT_I18N::translate_c('mother\'s parent\'s brother', 'great-uncle');

		case 'motparfat': return KT_I18N::translate_c('mother\'s parent\'s father', 'great-grandfather');

		case 'motparmot': return KT_I18N::translate_c('mother\'s parent\'s mother', 'great-grandmother');

		case 'motparpar': return KT_I18N::translate_c('mother\'s parent\'s parent', 'great-grandparent');

		case 'motparsib': return KT_I18N::translate_c('mother\'s parent\'s sibling', 'great-aunt/uncle');

		case 'motparsis': return KT_I18N::translate_c('mother\'s parent\'s sister', 'great-aunt');

		case 'motsischi': return KT_I18N::translate_c('mother\'s sister\'s child', 'first cousin');

		case 'motsisdau': return KT_I18N::translate_c('mother\'s sister\'s daughter', 'first cousin');

		case 'motsishus': return KT_I18N::translate_c('mother\'s sister\'s husband', 'uncle');

		case 'motsisson': return KT_I18N::translate_c('mother\'s sister\'s son', 'first cousin');

		case 'parbrowif': return KT_I18N::translate_c('parent\'s brother\'s wife', 'aunt');

		case 'parfatbro': return KT_I18N::translate_c('parent\'s father\'s brother', 'great-uncle');

		case 'parfatfat': return KT_I18N::translate_c('parent\'s father\'s father', 'great-grandfather');

		case 'parfatmot': return KT_I18N::translate_c('parent\'s father\'s mother', 'great-grandmother');

		case 'parfatpar': return KT_I18N::translate_c('parent\'s father\'s parent', 'great-grandparent');

		case 'parfatsib': return KT_I18N::translate_c('parent\'s father\'s sibling', 'great-aunt/uncle');

		case 'parfatsis': return KT_I18N::translate_c('parent\'s father\'s sister', 'great-aunt');

		case 'parmotbro': return KT_I18N::translate_c('parent\'s mother\'s brother', 'great-uncle');

		case 'parmotfat': return KT_I18N::translate_c('parent\'s mother\'s father', 'great-grandfather');

		case 'parmotmot': return KT_I18N::translate_c('parent\'s mother\'s mother', 'great-grandmother');

		case 'parmotpar': return KT_I18N::translate_c('parent\'s mother\'s parent', 'great-grandparent');

		case 'parmotsib': return KT_I18N::translate_c('parent\'s mother\'s sibling', 'great-aunt/uncle');

		case 'parmotsis': return KT_I18N::translate_c('parent\'s mother\'s sister', 'great-aunt');

		case 'parparbro': return KT_I18N::translate_c('parent\'s parent\'s brother', 'great-uncle');

		case 'parparfat': return KT_I18N::translate_c('parent\'s parent\'s father', 'great-grandfather');

		case 'parparmot': return KT_I18N::translate_c('parent\'s parent\'s mother', 'great-grandmother');

		case 'parparpar': return KT_I18N::translate_c('parent\'s parent\'s parent', 'great-grandparent');

		case 'parparsib': return KT_I18N::translate_c('parent\'s parent\'s sibling', 'great-aunt/uncle');

		case 'parparsis': return KT_I18N::translate_c('parent\'s parent\'s sister', 'great-aunt');

		case 'parsishus': return KT_I18N::translate_c('parent\'s sister\'s husband', 'uncle');

		case 'parspochi': return KT_I18N::translate_c('parent\'s spouse\'s child', 'step-sibling');

		case 'parspodau': return KT_I18N::translate_c('parent\'s spouse\'s daughter', 'step-sister');

		case 'parsposon': return KT_I18N::translate_c('parent\'s spouse\'s son', 'step-brother');

		case 'sibchichi': return KT_I18N::translate_c('sibling\'s child\'s child', 'great-nephew/niece');

		case 'sibchidau': return KT_I18N::translate_c('sibling\'s child\'s daughter', 'great-niece');

		case 'sibchison': return KT_I18N::translate_c('sibling\'s child\'s son', 'great-nephew');

		case 'sibdauchi': return KT_I18N::translate_c('sibling\'s daughter\'s child', 'great-nephew/niece');

		case 'sibdaudau': return KT_I18N::translate_c('sibling\'s daughter\'s daughter', 'great-niece');

		case 'sibdauhus': return KT_I18N::translate_c('sibling\'s daughter\'s husband', 'nephew-in-law');

		case 'sibdauson': return KT_I18N::translate_c('sibling\'s daughter\'s son', 'great-nephew');

		case 'sibsonchi': return KT_I18N::translate_c('sibling\'s son\'s child', 'great-nephew/niece');

		case 'sibsondau': return KT_I18N::translate_c('sibling\'s son\'s daughter', 'great-niece');

		case 'sibsonson': return KT_I18N::translate_c('sibling\'s son\'s son', 'great-nephew');

		case 'sibsonwif': return KT_I18N::translate_c('sibling\'s son\'s wife', 'niece-in-law');

		case 'sischichi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s child\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s child\'s child', 'great-nephew/niece');

		case 'sischidau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s child\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s child\'s daughter', 'great-niece');

		case 'sischison': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s child\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s child\'s son', 'great-nephew');

		case 'sisdauchi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s daughter\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s daughter\'s child', 'great-nephew/niece');

		case 'sisdaudau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s daughter\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s daughter\'s daughter', 'great-niece');

		case 'sisdauhus': return KT_I18N::translate_c('sisters\'s daughter\'s husband', 'nephew-in-law');

		case 'sisdauson': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s daughter\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s daughter\'s son', 'great-nephew');

		case 'sishusbro': return KT_I18N::translate_c('sister\'s husband\'s brother', 'brother-in-law');

		case 'sishussib': return KT_I18N::translate_c('sister\'s husband\'s sibling', 'brother/sister-in-law');

		case 'sishussis': return KT_I18N::translate_c('sister\'s husband\'s sister', 'sister-in-law');

		case 'sissonchi': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s son\'s child', 'great-nephew/niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s son\'s child', 'great-nephew/niece');

		case 'sissondau': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s son\'s daughter', 'great-niece');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s son\'s daughter', 'great-niece');

		case 'sissonson': if ('M' == $sex1) {
			return KT_I18N::translate_c('(a man\'s) sister\'s son\'s son', 'great-nephew');
		}

			return KT_I18N::translate_c('(a woman\'s) sister\'s son\'s son', 'great-nephew');

		case 'sissonwif': return KT_I18N::translate_c('sisters\'s son\'s wife', 'niece-in-law');

		case 'sonchichi': return KT_I18N::translate_c('son\'s child\'s child', 'great-grandchild');

		case 'sonchidau': return KT_I18N::translate_c('son\'s child\'s daughter', 'great-granddaughter');

		case 'sonchison': return KT_I18N::translate_c('son\'s child\'s son', 'great-grandson');

		case 'sondauchi': return KT_I18N::translate_c('son\'s daughter\'s child', 'great-grandchild');

		case 'sondaudau': return KT_I18N::translate_c('son\'s daughter\'s daughter', 'great-granddaughter');

		case 'sondauhus': return KT_I18N::translate_c('son\'s daughter\'s husband', 'granddaughter\'s husband');

		case 'sondauson': return KT_I18N::translate_c('son\'s daughter\'s son', 'great-grandson');

		case 'sonsonchi': return KT_I18N::translate_c('son\'s son\'s child', 'great-grandchild');

		case 'sonsondau': return KT_I18N::translate_c('son\'s son\'s daughter', 'great-granddaughter');

		case 'sonsonson': return KT_I18N::translate_c('son\'s son\'s son', 'great-grandson');

		case 'sonsonwif': return KT_I18N::translate_c('son\'s son\'s wife', 'grandson\'s wife');

		case 'sonwiffat': return KT_I18N::translate_c('son\'s wife\'s father', 'daughter-in-law\'s father');

		case 'sonwifmot': return KT_I18N::translate_c('son\'s wife\'s mother', 'daughter-in-law\'s mother');

		case 'sonwifpar': return KT_I18N::translate_c('son\'s wife\'s parent', 'daughter-in-law\'s parent');

		case 'wifbrowif': return KT_I18N::translate_c('wife\'s brother\'s wife', 'sister-in-law');

		case 'wifsishus': return KT_I18N::translate_c('wife\'s sister\'s husband', 'brother-in-law');
			// Some “special case” level four relationships that have specific names in certain languages
		case 'fatfatbrowif': return KT_I18N::translate_c('father\'s father\'s brother\'s wife', 'great-aunt');

		case 'fatfatsibspo': return KT_I18N::translate_c('father\'s father\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'fatfatsishus': return KT_I18N::translate_c('father\'s father\'s sister\'s husband', 'great-uncle');

		case 'fatmotbrowif': return KT_I18N::translate_c('father\'s mother\'s brother\'s wife', 'great-aunt');

		case 'fatmotsibspo': return KT_I18N::translate_c('father\'s mother\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'fatmotsishus': return KT_I18N::translate_c('father\'s mother\'s sister\'s husband', 'great-uncle');

		case 'fatparbrowif': return KT_I18N::translate_c('father\'s parent\'s brother\'s wife', 'great-aunt');

		case 'fatparsibspo': return KT_I18N::translate_c('father\'s parent\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'fatparsishus': return KT_I18N::translate_c('father\'s parent\'s sister\'s husband', 'great-uncle');

		case 'motfatbrowif': return KT_I18N::translate_c('mother\'s father\'s brother\'s wife', 'great-aunt');

		case 'motfatsibspo': return KT_I18N::translate_c('mother\'s father\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'motfatsishus': return KT_I18N::translate_c('mother\'s father\'s sister\'s husband', 'great-uncle');

		case 'motmotbrowif': return KT_I18N::translate_c('mother\'s mother\'s brother\'s wife', 'great-aunt');

		case 'motmotsibspo': return KT_I18N::translate_c('mother\'s mother\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'motmotsishus': return KT_I18N::translate_c('mother\'s mother\'s sister\'s husband', 'great-uncle');

		case 'motparbrowif': return KT_I18N::translate_c('mother\'s parent\'s brother\'s wife', 'great-aunt');

		case 'motparsibspo': return KT_I18N::translate_c('mother\'s parent\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'motparsishus': return KT_I18N::translate_c('mother\'s parent\'s sister\'s husband', 'great-uncle');

		case 'parfatbrowif': return KT_I18N::translate_c('parent\'s father\'s brother\'s wife', 'great-aunt');

		case 'parfatsibspo': return KT_I18N::translate_c('parent\'s father\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'parfatsishus': return KT_I18N::translate_c('parent\'s father\'s sister\'s husband', 'great-uncle');

		case 'parmotbrowif': return KT_I18N::translate_c('parent\'s mother\'s brother\'s wife', 'great-aunt');

		case 'parmotsibspo': return KT_I18N::translate_c('parent\'s mother\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'parmotsishus': return KT_I18N::translate_c('parent\'s mother\'s sister\'s husband', 'great-uncle');

		case 'parparbrowif': return KT_I18N::translate_c('parent\'s parent\'s brother\'s wife', 'great-aunt');

		case 'parparsibspo': return KT_I18N::translate_c('parent\'s parent\'s sibling\'s spouse', 'great-aunt/uncle');

		case 'parparsishus': return KT_I18N::translate_c('parent\'s parent\'s sister\'s husband', 'great-uncle');

		case 'fatfatbrodau': return KT_I18N::translate_c('father\'s father\'s brother\'s daughter', 'first cousin once removed ascending');

		case 'fatfatbroson': return KT_I18N::translate_c('father\'s father\'s brother\'s son', 'first cousin once removed ascending');

		case 'fatfatbrochi': return KT_I18N::translate_c('father\'s father\'s brother\'s child', 'first cousin once removed ascending');

		case 'fatfatsisdau': return KT_I18N::translate_c('father\'s father\'s sister\'s daughter', 'first cousin once removed ascending');

		case 'fatfatsisson': return KT_I18N::translate_c('father\'s father\'s sister\'s son', 'first cousin once removed ascending');

		case 'fatfatsischi': return KT_I18N::translate_c('father\'s father\'s sister\'s child', 'first cousin once removed ascending');

		case 'fatmotbrodau': return KT_I18N::translate_c('father\'s mother\'s brother\'s daughter', 'first cousin once removed ascending');

		case 'fatmotbroson': return KT_I18N::translate_c('father\'s mother\'s brother\'s son', 'first cousin once removed ascending');

		case 'fatmotbrochi': return KT_I18N::translate_c('father\'s mother\'s brother\'s child', 'first cousin once removed ascending');

		case 'fatmotsisdau': return KT_I18N::translate_c('father\'s mother\'s sister\'s daughter', 'first cousin once removed ascending');

		case 'fatmotsisson': return KT_I18N::translate_c('father\'s mother\'s sister\'s son', 'first cousin once removed ascending');

		case 'fatmotsischi': return KT_I18N::translate_c('father\'s mother\'s sister\'s child', 'first cousin once removed ascending');

		case 'motfatbrodau': return KT_I18N::translate_c('mother\'s father\'s brother\'s daughter', 'first cousin once removed ascending');

		case 'motfatbroson': return KT_I18N::translate_c('mother\'s father\'s brother\'s son', 'first cousin once removed ascending');

		case 'motfatbrochi': return KT_I18N::translate_c('mother\'s father\'s brother\'s child', 'first cousin once removed ascending');

		case 'motfatsisdau': return KT_I18N::translate_c('mother\'s father\'s sister\'s daughter', 'first cousin once removed ascending');

		case 'motfatsisson': return KT_I18N::translate_c('mother\'s father\'s sister\'s son', 'first cousin once removed ascending');

		case 'motfatsischi': return KT_I18N::translate_c('mother\'s father\'s sister\'s child', 'first cousin once removed ascending');

		case 'motmotbrodau': return KT_I18N::translate_c('mother\'s mother\'s brother\'s daughter', 'first cousin once removed ascending');

		case 'motmotbroson': return KT_I18N::translate_c('mother\'s mother\'s brother\'s son', 'first cousin once removed ascending');

		case 'motmotbrochi': return KT_I18N::translate_c('mother\'s mother\'s brother\'s child', 'first cousin once removed ascending');

		case 'motmotsisdau': return KT_I18N::translate_c('mother\'s mother\'s sister\'s daughter', 'first cousin once removed ascending');

		case 'motmotsisson': return KT_I18N::translate_c('mother\'s mother\'s sister\'s son', 'first cousin once removed ascending');

		case 'motmotsischi': return KT_I18N::translate_c('mother\'s mother\'s sister\'s child', 'first cousin once removed ascending');
	}

	// Some “special case” level five relationships that have specific names in certain languages
	if (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s brother\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s brother\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s brother\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sister\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sister\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sister\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sibling\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sibling\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandfather\'s sibling\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motbro(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s brother\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motbro(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s brother\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motbro(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s brother\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsis(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sister\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsis(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sister\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsis(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sister\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsib(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sibling\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsib(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sibling\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)motsib(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandmother\'s sibling\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parbro(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s brother\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parbro(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s brother\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parbro(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s brother\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsis(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sister\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsis(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sister\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsis(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sister\'s grandchild', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsib(son|dau|chi)dau$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sibling\'s granddaughter', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsib(son|dau|chi)son$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sibling\'s grandson', 'second cousin');
	}
	if (preg_match('/^(mot|fat|par)parsib(son|dau|chi)chi$/', $path)) {
		return KT_I18N::translate_c('grandparent\'s sibling\'s grandchild', 'second cousin');
	}

	// Look for generic/pattern relationships.
	// TODO: these are heavily based on English relationship names.
	// We need feedback from other languages to improve this.
	// Dutch has special names for 8 generations of great-great-..., so these need explicit naming
	// Spanish has special names for four but also has two different numbering patterns

	if (preg_match('/^((?:mot|fat|par)+)(bro|sis|sib)$/', $path, $match)) {
		// siblings of direct ancestors
		$up = strlen($match[1]) / 3;
		$bef_last = substr($path, -6, 3);

		switch ($up) {
			case 3:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great-grandfather\'s brother', 'great-great-uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great-grandmother\'s brother', 'great-great-uncle');
						}

						return KT_I18N::translate_c('great-grandparent\'s brother', 'great-great-uncle');

					case 'F': return KT_I18N::translate('great-great-aunt');

					case 'U': return KT_I18N::translate('great-great-aunt/uncle');
				}

				break;

			case 4:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great-great-grandfather\'s brother', 'great-great-great-uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great-great-grandmother\'s brother', 'great-great-great-uncle');
						}

						return KT_I18N::translate_c('great-great-grandparent\'s brother', 'great-great-great-uncle');

					case 'F': return KT_I18N::translate('great-great-great-aunt');

					case 'U': return KT_I18N::translate('great-great-great-aunt/uncle');
				}

				break;

			case 5:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great-great-great-grandfather\'s brother', 'great x4 uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great-great-great-grandmother\'s brother', 'great x4 uncle');
						}

						return KT_I18N::translate_c('great-great-great-grandparent\'s brother', 'great x4 uncle');

					case 'F': return KT_I18N::translate('great x4 aunt');

					case 'U': return KT_I18N::translate('great x4 aunt/uncle');
				}

				break;

			case 6:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great x4 grandfather\'s brother', 'great x5 uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great x4 grandmother\'s brother', 'great x5 uncle');
						}

						return KT_I18N::translate_c('great x4 grandparent\'s brother', 'great x5 uncle');

					case 'F': return KT_I18N::translate('great x5 aunt');

					case 'U': return KT_I18N::translate('great x5 aunt/uncle');
				}

				break;

			case 7:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great x5 grandfather\'s brother', 'great x6 uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great x5 grandmother\'s brother', 'great x6 uncle');
						}

						return KT_I18N::translate_c('great x5 grandparent\'s brother', 'great x6 uncle');

					case 'F': return KT_I18N::translate('great x6 aunt');

					case 'U': return KT_I18N::translate('great x6 aunt/uncle');
				}

				break;

			case 8:
				switch ($sex2) {
					case 'M':
						if ('fat' == $bef_last) {
							return KT_I18N::translate_c('great x6 grandfather\'s brother', 'great x7 uncle');
						}
						if ('mot' == $bef_last) {
							return KT_I18N::translate_c('great x6 grandmother\'s brother', 'great x7 uncle');
						}

						return KT_I18N::translate_c('great x6 grandparent\'s brother', 'great x7 uncle');

					case 'F': return KT_I18N::translate('great x7 aunt');

					case 'U': return KT_I18N::translate('great x7 aunt/uncle');
				}

				break;

			default:
				// Different languages have different rules for naming generations.
				// An English great x12 uncle is a Danish great x10 uncle.
			//
				// Need to find out which languages use which rules.
				switch (KT_LOCALE) {
					case 'da':
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d uncle', $up - 4);

							case 'F': return KT_I18N::translate('great x%d aunt', $up - 4);

							case 'U': return KT_I18N::translate('great x%d aunt/uncle', $up - 4);
						}
						// no break
					case 'pl':
						switch ($sex2) {
							case 'M':
								if ('fat' == $bef_last) {
									return KT_I18N::translate_c('great x(%d-1) grandfather\'s brother', 'great x%d uncle', $up - 2);
								}
								if ('mot' == $bef_last) {
									return KT_I18N::translate_c('great x(%d-1) grandmother\'s brother', 'great x%d uncle', $up - 2);
								}

								return KT_I18N::translate_c('great x(%d-1) grandparent\'s brother', 'great x%d uncle', $up - 2);

							case 'F': return KT_I18N::translate('great x%d aunt', $up - 2);

							case 'U': return KT_I18N::translate('great x%d aunt/uncle', $up - 2);
						}
						// no break
					case 'it': // Source: Michele Locati
					case 'en_AU':
					case 'en_GB':
					case 'en_US':
					default:
						switch ($sex2) {
							case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
								return KT_I18N::translate('great x%d uncle', $up - 1);

							case 'F': return KT_I18N::translate('great x%d aunt', $up - 1);

							case 'U': return KT_I18N::translate('great x%d aunt/uncle', $up - 1);
						}
				}
		}
	}
	if (preg_match('/^(?:bro|sis|sib)((?:son|dau|chi)+)$/', $path, $match)) {
		// direct descendants of siblings
		$down = strlen($match[1]) / 3 + 1; // Add one, as we count generations from the common ancestor
		$first = substr($path, 0, 3);

		switch ($down) {
			case 4:
				switch ($sex2) {
					case 'M':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-grandson', 'great-great-nephew');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-grandson', 'great-great-nephew');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-nephew', 'great-great-nephew');

					case 'F':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-granddaughter', 'great-great-niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-granddaughter', 'great-great-niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-niece', 'great-great-niece');

					case 'U':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-grandchild', 'great-great-nephew/niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-grandchild', 'great-great-nephew/niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-nephew/niece', 'great-great-nephew/niece');
				}
				// no break
			case 5:
				switch ($sex2) {
					case 'M':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-grandson', 'great-great-great-nephew');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-grandson', 'great-great-great-nephew');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-great-nephew', 'great-great-great-nephew');

					case 'F':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-granddaughter', 'great-great-great-niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-granddaughter', 'great-great-great-niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-great-niece', 'great-great-great-niece');

					case 'U':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-grandchild', 'great-great-great-nephew/niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-grandchild', 'great-great-great-nephew/niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great-great-great-nephew/niece', 'great-great-great-nephew/niece');
				}
				// no break
			case 6:
				switch ($sex2) {
					case 'M':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-great-grandson', 'great x4 nephew');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-great-grandson', 'great x4 nephew');
						}

						return KT_I18N::translate_c('(a woman\'s) great x4 nephew', 'great x4 nephew');

					case 'F':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-great-granddaughter', 'great x4 niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-great-granddaughter', 'great x4 niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great x4 niece', 'great x4 niece');

					case 'U':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great-great-great-grandchild', 'great x4 nephew/niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great-great-great-grandchild', 'great x4 nephew/niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great x4 nephew/niece', 'great x4 nephew/niece');
				}
				// no break
			case 7:
				switch ($sex2) {
					case 'M':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great x4 grandson', 'great x5 nephew');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great x4 grandson', 'great x5 nephew');
						}

						return KT_I18N::translate_c('(a woman\'s) great x5 nephew', 'great x5 nephew');

					case 'F':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great x4 granddaughter', 'great x5 niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great x4 granddaughter', 'great x5 niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great x5 niece', 'great x5 niece');

					case 'U':
						if ('bro' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) brother\'s great x4 grandchild', 'great x5 nephew/niece');
						}
						if ('sis' == $first && 'M' == $sex1) {
							return KT_I18N::translate_c('(a man\'s) sister\'s great x4 grandchild', 'great x5 nephew/niece');
						}

						return KT_I18N::translate_c('(a woman\'s) great x5 nephew/niece', 'great x5 nephew/niece');
				}
				// no break
			default:
				// Different languages have different rules for naming generations.
				// An English great x12 nephew is a Polish great x11 nephew.
			//
				// Need to find out which languages use which rules.
				switch (KT_LOCALE) {
					case 'pl': // Source: Lukasz Wilenski
						switch ($sex2) {
							case 'M':
								if ('bro' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) brother\'s great x(%d-1) grandson', 'great x%d nephew', $down - 3);
								}
								if ('sis' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) sister\'s great x(%d-1) grandson', 'great x%d nephew', $down - 3);
								}

								return KT_I18N::translate_c('(a woman\'s) great x%d nephew', 'great x%d nephew', $down - 3);

							case 'F':
								if ('bro' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) brother\'s great x(%d-1) granddaughter', 'great x%d niece', $down - 3);
								}
								if ('sis' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) sister\'s great x(%d-1) granddaughter', 'great x%d niece', $down - 3);
								}

								return KT_I18N::translate_c('(a woman\'s) great x%d niece', 'great x%d niece', $down - 3);

							case 'U':
								if ('bro' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) brother\'s great x(%d-1) grandchild', 'great x%d nephew/niece', $down - 3);
								}
								if ('sis' == $first && 'M' == $sex1) {
									return KT_I18N::translate_c('(a man\'s) sister\'s great x(%d-1) grandchild', 'great x%d nephew/niece', $down - 3);
								}

								return KT_I18N::translate_c('(a woman\'s) great x%d nephew/niece', 'great x%d nephew/niece', $down - 3);
						}
						// no break
					case 'he': // Source: Meliza Amity
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d nephew', $down - 1);

							case 'F': return KT_I18N::translate('great x%d niece', $down - 1);

							case 'U': return KT_I18N::translate('great x%d nephew/niece', $down - 1);
						}
						// no break
					case 'it': // Source: Michele Locati.
					case 'en_AU':
					case 'en_GB':
					case 'en_US':
					default:
						switch ($sex2) {
							case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
								return KT_I18N::translate('great x%d nephew', $down - 2);

							case 'F': return KT_I18N::translate('great x%d niece', $down - 2);

							case 'U': return KT_I18N::translate('great x%d nephew/niece', $down - 2);
						}
				}
		}
	}
	if (preg_match('/^((?:mot|fat|par)*)$/', $path, $match)) {
		// direct ancestors
		$up = strlen($match[1]) / 3;

		switch ($up) {
			case 4:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great-great-grandfather');

					case 'F': return KT_I18N::translate('great-great-grandmother');

					case 'U': return KT_I18N::translate('great-great-grandparent');
				}

				break;

			case 5:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great-great-great-grandfather');

					case 'F': return KT_I18N::translate('great-great-great-grandmother');

					case 'U': return KT_I18N::translate('great-great-great-grandparent');
				}

				break;

			case 6:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x4 grandfather');

					case 'F': return KT_I18N::translate('great x4 grandmother');

					case 'U': return KT_I18N::translate('great x4 grandparent');
				}

				break;

			case 7:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x5 grandfather');

					case 'F': return KT_I18N::translate('great x5 grandmother');

					case 'U': return KT_I18N::translate('great x5 grandparent');
				}

				break;

			case 8:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x6 grandfather');

					case 'F': return KT_I18N::translate('great x6 grandmother');

					case 'U': return KT_I18N::translate('great x6 grandparent');
				}

				break;

			case 9:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x7 grandfather');

					case 'F': return KT_I18N::translate('great x7 grandmother');

					case 'U': return KT_I18N::translate('great x7 grandparent');
				}

				break;

			default:
				// Different languages have different rules for naming generations.
				// An English great x12 grandfather is a Danish great x11 grandfather.
			//
				// Need to find out which languages use which rules.
				switch (KT_LOCALE) {
					case 'da': // Source: Patrick Sorensen
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d grandfather', $up - 3);

							case 'F': return KT_I18N::translate('great x%d grandmother', $up - 3);

							case 'U': return KT_I18N::translate('great x%d grandparent', $up - 3);
						}
						// no break
					case 'it': // Source: Michele Locati
					case 'es': // Source: Wes Groleau
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d grandfather', $up);

							case 'F': return KT_I18N::translate('great x%d grandmother', $up);

							case 'U': return KT_I18N::translate('great x%d grandparent', $up);
						}
						// no break
					case 'fr': // Source: Jacqueline Tetreault
					case 'fr_CA':
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d grandfather', $up - 1);

							case 'F': return KT_I18N::translate('great x%d grandmother', $up - 1);

							case 'U': return KT_I18N::translate('great x%d grandparent', $up - 1);
						}
						// no break
					case 'nn':
					case 'nb':
						switch ($sex2) {
							case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
								return KT_I18N::translate('great x%d grandfather', $up - 3);

							case 'F': return KT_I18N::translate('great x%d grandmother', $up - 3);

							case 'U': return KT_I18N::translate('great x%d grandparent', $up - 3);
						}
						// no break
					case 'en_AU':
					case 'en_GB':
					case 'en_US':
					default:
						switch ($sex2) {
							case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
								return KT_I18N::translate('great x%d grandfather', $up - 2);

							case 'F': return KT_I18N::translate('great x%d grandmother', $up - 2);

							case 'U': return KT_I18N::translate('great x%d grandparent', $up - 2);
						}
				}
		}
	}
	if (preg_match('/^((?:son|dau|chi)*)$/', $path, $match)) {
		// direct descendants
		$up = strlen($match[1]) / 3;

		switch ($up) {
			case 4:
				switch ($sex2) {
					case 'son': return KT_I18N::translate('great-great-grandson');

					case 'dau': return KT_I18N::translate('great-great-granddaughter');

					case 'chi': return KT_I18N::translate('great-great-grandchild');
				}

				break;

			case 5:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great-great-great-grandson');

					case 'F': return KT_I18N::translate('great-great-great-granddaughter');

					case 'U': return KT_I18N::translate('great-great-great-grandchild');
				}

				break;

			case 6:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x4 grandson');

					case 'F': return KT_I18N::translate('great x4 granddaughter');

					case 'U': return KT_I18N::translate('great x4 grandchild');
				}

				break;

			case 7:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x5 grandson');

					case 'F': return KT_I18N::translate('great x5 granddaughter');

					case 'U': return KT_I18N::translate('great x5 grandchild');
				}

				break;

			case 8:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x6 grandson');

					case 'F': return KT_I18N::translate('great x6 granddaughter');

					case 'U': return KT_I18N::translate('great x6 grandchild');
				}

				break;

			case 9:
				switch ($sex2) {
					case 'M': return KT_I18N::translate('great x7 grandson');

					case 'F': return KT_I18N::translate('great x7 granddaughter');

					case 'U': return KT_I18N::translate('great x7 grandchild');
				}

				break;

			default:
				// Different languages have different rules for naming generations.
				// An English great x12 grandson is a Danish great x11 grandson.
			//
				// Need to find out which languages use which rules.
				switch (KT_LOCALE) {
					case 'nn': // Source: Hogne Røed Nilsen
					case 'nb':
					case 'da': // Source: Patrick Sorensen
						switch ($sex2) {
							case 'M': return KT_I18N::translate('great x%d grandson', $up - 3);

							case 'F': return KT_I18N::translate('great x%d granddaughter', $up - 3);

							case 'U': return KT_I18N::translate('great x%d grandchild', $up - 3);
						}
						// no break
					case 'it': // Source: Michele Locati
					case 'es': // Source: Wes Groleau (adding doesn’t change behavior, but needs to be better researched)
					case 'en_AU':
					case 'en_GB':
					case 'en_US':
					default:
						switch ($sex2) {
							case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
								return KT_I18N::translate('great x%d grandson', $up - 2);

							case 'F': return KT_I18N::translate('great x%d granddaughter', $up - 2);

							case 'U': return KT_I18N::translate('great x%d grandchild', $up - 2);
						}
				}
		}
	}
	if (preg_match('/^((?:mot|fat|par)+)(?:bro|sis|sib)((?:son|dau|chi)+)$/', $path, $match)) {
		// cousins in English
		$ascent = $match[1];
		$descent = $match[2];
		$up = strlen($ascent) / 3;
		$down = strlen($descent) / 3;
		$cousin = min($up, $down);  // Moved out of switch (en/default case) so that
		$removed = abs($down - $up);  // Spanish (and other languages) can use it, too.

		// Different languages have different rules for naming cousins.  For example,
		// an English “second cousin once removed” is a Polish “cousin of 7th degree”.
		//
		// Need to find out which languages use which rules.
		switch (KT_LOCALE) {
			case 'pl': // Source: Lukasz Wilenski
				return cousin_name($up + $down + 2, $sex2);

			case 'it':
				return cousin_name($up + $down - 3, $sex2);

			case 'es':
				// Source: Wes Groleau.  See http://UniGen.us/Parentesco.html & http://UniGen.us/Parentesco-D.html
				if ($down == $up) {
					return cousin_name($cousin, $sex2);
				}
				if ($down < $up) {
					return cousin_name2($cousin + 1, $sex2, get_relationship_name_from_path('sib'.$descent, null, null));
				}

				switch ($sex2) {
					case 'M': return cousin_name2($cousin + 1, $sex2, get_relationship_name_from_path('bro'.$descent, null, null));

					case 'F': return cousin_name2($cousin + 1, $sex2, get_relationship_name_from_path('sis'.$descent, null, null));

					case 'U': return cousin_name2($cousin + 1, $sex2, get_relationship_name_from_path('sib'.$descent, null, null));
				}

				// no break
			case 'en_AU': // See: http://en.wikipedia.org/wiki/File:CousinTree.svg
			case 'en_GB':
			case 'en_US':
			default:
				switch ($removed) {
					case 0:
						return cousin_name($cousin, $sex2);

					case 1:
						if ($up > $down) {
							// I18N: %s=“fifth cousin”, etc. http://www.ancestry.com/learn/library/article.aspx?article=2856
							return KT_I18N::translate('%s once removed ascending', cousin_name($cousin, $sex2));
						}
						// I18N: %s=“fifth cousin”, etc. http://www.ancestry.com/learn/library/article.aspx?article=2856
						return KT_I18N::translate('%s once removed descending', cousin_name($cousin, $sex2));

					case 2:
						if ($up > $down) {
							// I18N: %s=“fifth cousin”, etc.
							return KT_I18N::translate('%s twice removed ascending', cousin_name($cousin, $sex2));
						}
						// I18N: %s=“fifth cousin”, etc.
						return KT_I18N::translate('%s twice removed descending', cousin_name($cousin, $sex2));

					case 3:
						if ($up > $down) {
							// I18N: %s=“fifth cousin”, etc.
							return KT_I18N::translate('%s three times removed ascending', cousin_name($cousin, $sex2));
						}
						// I18N: %s=“fifth cousin”, etc.
						return KT_I18N::translate('%s three times removed descending', cousin_name($cousin, $sex2));

					default:
						if ($up > $down) {
							// I18N: %1$s=“fifth cousin”, etc., %2$d>=4
							return KT_I18N::translate('%1$s %2$d times removed ascending', cousin_name($cousin, $sex2), $removed);
						}
						// I18N: %1$s=“fifth cousin”, etc., %2$d>=4
						return KT_I18N::translate('%1$s %2$d times removed descending', cousin_name($cousin, $sex2), $removed);
				}
		}
	}

	// Split the relationship into sub-relationships, e.g., third-cousin’s great-uncle.
	// Try splitting at every point, and choose the path with the shorted translated name.
	// But before starting to recursively go through all combinations, do a cache look-up
	if (array_key_exists($path, Functions::$relationshipsCache)) {
		return Functions::$relationshipsCache[$path];
	}

	$relationship = null;
	$path1 = substr($path, 0, 3);
	$path2 = substr($path, 3);
	while ($path2) {
		$tmp = KT_I18N::translate(
			// I18N: A complex relationship, such as “third-cousin’s great-uncle”
			'%1$s\'s %2$s',
			get_relationship_name_from_path($path1, null, null), // TODO: need the actual people
			get_relationship_name_from_path($path2, null, null)
		);
		if (!$relationship || strlen($tmp) < strlen($relationship)) {
			$relationship = $tmp;
		}
		$path1 .= substr($path2, 0, 3);
		$path2 = substr($path2, 3);
	}
	// and store the result in the cache
	Functions::$relationshipsCache[$path] = $relationship;

	return $relationship;
}

class Functions
{
	/** @var string[] Cache for generic relationships (key stores the path, and value represents the relationship name) */
	public static $relationshipsCache = [];
}

/**
 * Relationship names for generations.
 *
 * function to get the names of each generation of ancestors from parents upwards
 * Used in the "Tree completness report / tab"
 *
 * @param mixed $generation
 */
function get_generation_names($generation)
{
	switch ($generation) {
		case 1:
			return KT_I18N::translate('self');

		case 2:
			return KT_I18N::translate('parents');

		case 3:
			return KT_I18N::translate('grandparents');

		case 4:
			return KT_I18N::translate('great grandparents');

		case $generation:
			return KT_I18N::translate('%dx great grandparents', $generation - 2);
	}
}

/**
 * get theme names.
 *
 * function to get the names of all of the themes as an array
 * it searches the themes folder and reads the name from the theme_name variable
 * in the theme.php file.
 *
 * @return array and array of theme names and their corresponding folder
 */
function get_theme_names()
{
	static $themes;

	if (null === $themes) {
		$themes = [];
		$d = dir(KT_ROOT.KT_THEMES_DIR);
		while (false !== ($folder = $d->read())) {
			if ('.' != $folder[0] && '_' != $folder[0] && is_dir(KT_ROOT.KT_THEMES_DIR.$folder) && file_exists(KT_ROOT.KT_THEMES_DIR.$folder.'/theme.php')) {
				$themefile = implode('', file(KT_ROOT.KT_THEMES_DIR.$folder.'/theme.php'));
				if (preg_match('/theme_name\s*=\s*"(.*)";/', $themefile, $match)) {
					$theme_name = KT_I18N::translate($match[1]);
					if (array_key_exists($theme_name, $themes)) {
						throw new Exception('More than one theme with the same name: '.$theme_name);
					}
					$themes[$theme_name] = $folder;
				}
			}
		}
		$d->close();
		uksort($themes, 'utf8_strcasecmp');
	}

	return $themes;
}

/**
 * get theme display names from theme name.
 *
 * @param mixed $folder
 *
 * @return string
 */
function get_theme_display($folder)
{
	$themefile = implode('', file(KT_ROOT.KT_THEMES_DIR.$folder.'/theme.php'));
	if (preg_match('/theme_display\s*=\s*"(.*)";/', $themefile, $match)) {
		$theme_display = KT_I18N::translate($match[1]);
	} else {
		$theme_display = $folder;
	}

	return $theme_display;
}

// Function to build an URL querystring from GET variables
// Optionally, add/replace specified values
function get_query_url($overwrite = null, $separator = '&')
{
	if (empty($_GET)) {
		$get = [];
	} else {
		$get = $_GET;
	}
	if (is_array($overwrite)) {
		foreach ($overwrite as $key => $value) {
			$get[$key] = $value;
		}
	}

	$query_string = '';
	foreach ($get as $key => $value) {
		if (!is_array($value)) {
			$query_string .= $separator.rawurlencode((string) $key).'='.rawurlencode((string) $value);
		} else {
			foreach ($value as $k => $v) {
				$query_string .= $separator.rawurlencode((string) $key).'%5B'.rawurlencode((string) $k).'%5D='.rawurlencode((string) $v);
			}
		}
	}
	$query_string = substr($query_string, strlen($separator)); // Remove leading “&amp;”
	if ($query_string) {
		return KT_SCRIPT_NAME.'?'.$query_string;
	}

	return KT_SCRIPT_NAME;
}

// This function works with a specified generation limit.  It will completely fill
// the PDF without regard to whether a known person exists in each generation.
// TODO: If a known individual is found in a generation, add prior empty positions
// and add remaining empty spots automatically.
function add_ancestors(&$list, $pid, $children = false, $generations = -1, $show_empty = false)
{
	$total_num_skipped = 0;
	$skipped_gen = 0;
	$num_skipped = 0;
	$genlist = [$pid];
	$list[$pid]->generation = 1;
	while (count($genlist) > 0) {
		$id = array_shift($genlist);
		if (0 === strpos($id, 'empty')) {
			continue;
		} // id can be something like “empty7”
		$person = KT_Person::getInstance($id);
		$famids = $person->getChildFamilies();
		if (count($famids) > 0) {
			if ($show_empty) {
				for ($i = 0; $i < $num_skipped; ++$i) {
					$list['empty'.$total_num_skipped] = new KT_Person('');
					$list['empty'.$total_num_skipped]->generation = $list[$id]->generation + 1;
					array_push($genlist, 'empty'.$total_num_skipped);
					++$total_num_skipped;
				}
			}
			$num_skipped = 0;
			foreach ($famids as $famid => $family) {
				$husband = $family->getHusband();
				$wife = $family->getWife();
				if ($husband) {
					$list[$husband->getXref()] = $husband;
					$list[$husband->getXref()]->generation = $list[$id]->generation + 1;
				} elseif ($show_empty) {
					$list['empty'.$total_num_skipped] = new KT_Person('');
					$list['empty'.$total_num_skipped]->generation = $list[$id]->generation + 1;
				}
				if ($wife) {
					$list[$wife->getXref()] = $wife;
					$list[$wife->getXref()]->generation = $list[$id]->generation + 1;
				} elseif ($show_empty) {
					$list['empty'.$total_num_skipped] = new KT_Person('');
					$list['empty'.$total_num_skipped]->generation = $list[$id]->generation + 1;
				}
				if (-1 == $generations || $list[$id]->generation + 1 < $generations) {
					$skipped_gen = $list[$id]->generation + 1;
					if ($husband) {
						array_push($genlist, $husband->getXref());
					} elseif ($show_empty) {
						array_push($genlist, 'empty'.$total_num_skipped);
					}
					if ($wife) {
						array_push($genlist, $wife->getXref());
					} elseif ($show_empty) {
						array_push($genlist, 'empty'.$total_num_skipped);
					}
				}
				++$total_num_skipped;
				if ($children) {
					$childs = $family->getChildren();
					foreach ($childs as $child) {
						$list[$child->getXref()] = $child;
						if (isset($list[$id]->generation)) {
							$list[$child->getXref()]->generation = $list[$id]->generation;
						} else {
							$list[$child->getXref()]->generation = 1;
						}
					}
				}
			}
		} elseif ($show_empty) {
			if ($skipped_gen > $list[$id]->generation) {
				$list['empty'.$total_num_skipped] = new KT_Person('');
				$list['empty'.$total_num_skipped]->generation = $list[$id]->generation + 1;
				++$total_num_skipped;
				$list['empty'.$total_num_skipped] = new KT_Person('');
				$list['empty'.$total_num_skipped]->generation = $list[$id]->generation + 1;
				array_push($genlist, 'empty'.($total_num_skipped - 1));
				array_push($genlist, 'empty'.$total_num_skipped);
				++$total_num_skipped;
			} else {
				$num_skipped += 2;
			}
		}
	}
}

// --- copied from class_reportpdf.php
function add_descendancy(&$list, $pid, $parents = false, $generations = -1)
{
	$person = KT_Person::getInstance($pid);
	if (null == $person) {
		return;
	}
	if (!isset($list[$pid])) {
		$list[$pid] = $person;
	}
	if (!isset($list[$pid]->generation)) {
		$list[$pid]->generation = 0;
	}
	foreach ($person->getSpouseFamilies() as $family) {
		if ($parents) {
			$husband = $family->getHusband();
			$wife = $family->getWife();
			if ($husband) {
				$list[$husband->getXref()] = $husband;
				if (isset($list[$pid]->generation)) {
					$list[$husband->getXref()]->generation = $list[$pid]->generation - 1;
				} else {
					$list[$husband->getXref()]->generation = 1;
				}
			}
			if ($wife) {
				$list[$wife->getXref()] = $wife;
				if (isset($list[$pid]->generation)) {
					$list[$wife->getXref()]->generation = $list[$pid]->generation - 1;
				} else {
					$list[$wife->getXref()]->generation = 1;
				}
			}
		}
		$children = $family->getChildren();
		foreach ($children as $child) {
			if ($child) {
				$list[$child->getXref()] = $child;
				if (isset($list[$pid]->generation)) {
					$list[$child->getXref()]->generation = $list[$pid]->generation + 1;
				} else {
					$list[$child->getXref()]->generation = 2;
				}
			}
		}
		if (-1 == $generations || $list[$pid]->generation + 1 < $generations) {
			foreach ($children as $child) {
				add_descendancy($list, $child->getXref(), $parents, $generations); // recurse on the childs family
			}
		}
	}
}

// Generate a new XREF, unique across all family trees
function get_new_xref($type = 'INDI', $ged_id = KT_GED_ID)
{
	global $SOURCE_ID_PREFIX, $REPO_ID_PREFIX, $MEDIA_ID_PREFIX, $FAM_ID_PREFIX, $GEDCOM_ID_PREFIX, $NOTE_ID_PREFIX;

	switch ($type) {
		case 'INDI':
			$prefix = $GEDCOM_ID_PREFIX;

			break;

		case 'FAM':
			$prefix = $FAM_ID_PREFIX;

			break;

		case 'OBJE':
			$prefix = $MEDIA_ID_PREFIX;

			break;

		case 'SOUR':
			$prefix = $SOURCE_ID_PREFIX;

			break;

		case 'REPO':
			$prefix = $REPO_ID_PREFIX;

			break;

		case 'NOTE':
			$prefix = $NOTE_ID_PREFIX;

			break;

		default:
			$prefix = $type[0];

			break;
	}

	$num = KT_DB::prepare('SELECT next_id FROM `##next_id` WHERE record_type=? AND gedcom_id=?')
		->execute([$type, $ged_id])
		->fetchOne()
	;

	// TODO?  If a gedcom file contains *both* inline and object based media, then
	// we could be generating an XREF that we will find later.  Need to scan the
	// entire gedcom for them?

	if (is_null($num)) {
		$num = 1;
		KT_DB::prepare('INSERT INTO `##next_id` (gedcom_id, record_type, next_id) VALUES(?, ?, 1)')
			->execute([$ged_id, $type])
		;
	}

	$statement = KT_DB::prepare(
		'SELECT i_id FROM `##individuals` WHERE i_id = ?'.
		' UNION ALL '.
		'SELECT f_id FROM `##families` WHERE f_id = ?'.
		' UNION ALL '.
		'SELECT s_id FROM `##sources` WHERE s_id = ?'.
		' UNION ALL '.
		'SELECT m_id FROM `##media` WHERE m_id = ?'.
		' UNION ALL '.
		'SELECT o_id FROM `##other` WHERE o_id = ?'.
		' UNION ALL '.
		'SELECT xref FROM `##change` WHERE xref = ?'
	);

	while ($statement->execute(array_fill(0, 6, $prefix.$num))->fetchOne()) {
		// Applications such as ancestry.com generate XREFs with numbers larger than
		// PHP’s signed integer.  MySQL can handle large integers.
		$num = KT_DB::prepare('SELECT 1+?')->execute([$num])->fetchOne();
	}

	// -- update the next id number in the DB table
	KT_DB::prepare('UPDATE `##next_id` SET next_id=? WHERE record_type=? AND gedcom_id=?')
		->execute([$num + 1, $type, $ged_id])
	;

	return $prefix.$num;
}

/**
 * check if the given string has UTF-8 characters.
 *
 * @param mixed $string
 */
function has_utf8($string)
{
	$len = strlen($string);
	for ($i = 0; $i < $len; ++$i) {
		$letter = substr($string, $i, 1);
		$ord = ord($letter);
		if (95 == $ord || $ord >= 195) {
			return true;
		}
	}

	return false;
}

/**
 * determines whether the passed in filename is a link to an external source (i.e. contains “://”).
 *
 * @param mixed $file
 */
function isFileExternal($file)
{
	return false !== strpos($file, '://');
}

// Turn URLs in text into HTML links.  Insert breaks into long URLs
// so that the browser can word-wrap.
function expand_urls($text)
{
	// Some versions of RFC3987 have an appendix B which gives the following regex
	// (([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
	// This matches far too much while a “precise” regex is several pages long.
	// This is a compromise.
	$URL_REGEX = '((https?|ftp]):)(//([^\s/?#<>]*))?([^\s?#<>]*)(\?([^\s#<>]*))?(#[^\s?#<>]+)?';

	return preg_replace_callback(
		'/'.addcslashes('(?!>)'.$URL_REGEX.'(?!</a>)', '/').'/i',
		function ($m) {
			return '<a href="'.$m[0].'" target="blank">'.preg_replace('/\\b/', '&shy;', $m[0]).'</a>';
		},
		preg_replace('/<(?!br)/i', '&lt;', $text) // no html except br
	);
}

// Returns the part of the haystack before the first occurrence of the needle.
// Use it to emulate the before_needle php 5.3.0 strstr function
function strstrb($haystack, $needle)
{
	return substr($haystack, 0, strpos($haystack, $needle));
}

/**
 * Detects max size of file that can be uploaded to server.
 *
 * Based on php.ini parameters “upload_max_filesize”, “post_max_size” &
 * “memory_limit”. Valid for single file upload form. May be used
 * as MAX_FILE_SIZE hidden input or to inform user about max allowed file size.
 * RULE memory_limit > post_max_size > upload_max_filesize
 * http://php.net/manual/en/ini.core.php : 128M > 8M > 2M
 * Sets max size of post data allowed. This setting also affects file upload.
 * To upload large files, this value must be larger than upload_max_filesize.
 * If memory limit is enabled by your configure script, memory_limit also
 * affects file uploading. Generally speaking, memory_limit should be larger
 * than post_max_size. When an integer is used, the value is measured in bytes.
 * Shorthand notation, as described in this FAQ, may also be used. If the size
 * of post data is greater than post_max_size, the $_POST and $_FILES
 * superglobals are empty. This can be tracked in various ways, e.g. by passing
 * the $_GET variable to the script processing the data, i.e., and then checking if $_GET['processed'] is set.
 * memory_limit > post_max_size > upload_max_filesize
 *
 * @author Paul Melekhov edited by lostinscope http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
 *
 * @return int Max file size in bytes
 */
function detectMaxUploadFileSize()
{
	/**
	 * Converts shorthands like "2M" or "512K" to bytes.
	 *
	 * @param $size
	 *
	 * @return mixed
	 */
	$normalize = function ($size) {
		if (preg_match('/^([\d\.]+)([KMG])$/i', $size, $match)) {
			$pos = array_search($match[2], ['K', 'M', 'G']);
			if ($pos) {
				$size = $match[1] * pow(1024, $pos + 1);
			}
		}

		return $size;
	};

	$max_upload = $normalize(ini_get('upload_max_filesize'));
	$max_post = (0 == ini_get('post_max_size')) ? function () {throw new Exception('Check Your php.ini settings'); } : $normalize(ini_get('post_max_size'));
	$memory_limit = (-1 == ini_get('memory_limit')) ? $max_post : $normalize(ini_get('memory_limit'));

	if ($memory_limit < $max_post || $memory_limit < $max_upload) {
		return $memory_limit;
	}

	if ($max_post < $max_upload) {
		return $max_post;
	}

	$maxFileSize = min($max_upload, $max_post, $memory_limit);

	$precision = 2;
	$base = log($maxFileSize) / log(1024);
	$suffixes = ['', 'k', 'M', 'G', 'T'];

//	$display_maxsize = round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

	return $maxFileSize;
}

function int_from_bytestring($byteString)
{
	preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $byteString, $matches);
	if ($matches) {
		$num = (float) $matches[1];

		switch (strtoupper($matches[2])) {
			case 'E':
				$num = $num * 1024;
				// no break
			case 'P':
				$num = $num * 1024;
				// no break
			case 'T':
				$num = $num * 1024;
				// no break
			case 'G':
				$num = $num * 1024;
				// no break
			case 'M':
				$num = $num * 1024;
				// no break
			case 'K':
				$num = $num * 1024;
		}

		return intval($num);
	}

	return $byteString;
}

// family navigator
function FamilyNavigator($pid)
{
	$controller = new KT_Controller_Individual();
	global $spouselinks, $parentlinks, $DeathYr, $BirthYr, $censyear, $censdate;

//	if (KT_Family::getInstance($pid)) {
//		$record	= KT_Family::getInstance($pid);
//		if ($record->getHusband()->getXref()) {
//			$pid = $record->getHusband()->getXref();
//		} elseif ($record->getWife()->getXref()) {
//			$pid = $record->getWife()->getXref();
//		}
//	}

	$person = KT_Person::getInstance($pid);
	$currpid = $pid;
	0 == $person->getDeathYear() ? $DeathYr = '' : $DeathYr = $person->getDeathYear();
	0 == $person->getBirthYear() ? $BirthYr = '' : $BirthYr = $person->getBirthYear();
	?>
	<div id="media-links">
		<table>
			<tr>
				<th colspan="2">
					<?php echo KT_I18N::translate('Family navigator'); ?>
				</th>
			<tr>
				<td colspan="2" class="descriptionbox wrap center">
					<?php echo KT_I18N::translate('Click name to add person to list of links.'); ?>
				</td>
			</tr>
			<?php
			// -- Build Parent Family -------------
			$personcount = 0;
	$families = $person->getChildFamilies();
	foreach ($families as $family) {
		$label = $person->getChildFamilyLabel($family);
		$people = $controller->buildFamilyList($family, 'parents');
		$marrdate = $family->getMarriageDate();
		// Parents - husband
		if (isset($people['husb'])) {
			$fulln = strip_tags($people['husb']->getFullName());
			$menu = new KT_Menu(getCloseRelationshipName($person, $people['husb']));
			$slabel = print_pedigree_person_nav2($people['husb']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($people['husb']->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $people['husb']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $people['husb']->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
				<?php }
		// Parents - wife
		if (isset($people['wife'])) {
			$fulln = strip_tags($people['wife']->getFullName());
			$menu = new KT_Menu(getCloseRelationshipName($person, $people['wife']));
			$slabel = print_pedigree_person_nav2($people['wife']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($people['wife']->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $people['wife']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $people['wife']->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
				<?php }
		// Parents - siblings
		if (isset($people['children'])) {
			$elderdate = $family->getMarriageDate();
			foreach ($people['children'] as $key => $child) {
				$fulln = strip_tags($child->getFullName());
				$menu = new KT_Menu(getCloseRelationshipName($person, $child));
				$slabel = print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel .= $spouselinks;
				$submenu = new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				// Only print current person in immediate family group
				if ($child->getXref() != $pid) { ?>
							<tr>
								<td>
									<?php if ($child->getXref() == $pid) {
										echo $child->getLabel();
									} else {
										echo $menu->getMenu();
									} ?>
								</td>
								<td align="left">
									<?php if ($child->canDisplayDetails()) { ?>
										<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
											<?php echo $child->getFullName(); ?>
										</a>
										<?php
									} else {
										echo KT_I18N::translate('Private');
									} ?>
								</td>
							</tr>
						<?php }
				}
			$elderdate = $child->getBirthDate(false);
		}
	}
	// -- Build step families -------
	foreach ($person->getChildStepFamilies() as $family) {
		$label = $person->getStepFamilyLabel($family);
		$people = $controller->buildFamilyList($family, 'step-parents');
		if ($people) { ?>
					<!-- blank row 1-->
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				<?php }
		$marrdate = $family->getMarriageDate();
		// Husband ----------
		$elderdate = '';
		if (isset($people['husb'])) {
			$fulln = strip_tags($people['husb']->getFullName());
			$menu = new KT_Menu();
			$menu->addLabel(getCloseRelationshipName($person, $people['husb']));
			$slabel = print_pedigree_person_nav2($people['husb']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu);
			0 == $people['husb']->getDeathYear() ? $DeathYr = '' : $DeathYr = $people['husb']->getDeathYear();
			0 == $people['husb']->getBirthYear() ? $BirthYr = '' : $BirthYr = $people['husb']->getBirthYear(); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($people['husb']->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $people['husb']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $people['husb']->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
					<?php $elderdate = $people['husb']->getBirthDate(false);
		}
		// Wife
		if (isset($people['wife'])) {
			$fulln = strip_tags($people['wife']->getFullName());
			$menu = new KT_Menu();
			$menu->addLabel(getCloseRelationshipName($person, $people['wife']));
			$slabel = print_pedigree_person_nav2($people['wife']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu);
			0 == $people['wife']->getDeathYear() ? $DeathYr = '' : $DeathYr = $people['wife']->getDeathYear();
			0 == $people['wife']->getBirthYear() ? $BirthYr = '' : $BirthYr = $people['wife']->getBirthYear(); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($people['wife']->canDisplayDetails()) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people['wife']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people['wife']->getFullName(); ?>
							</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
				<?php }
		// Children --
		if (isset($people['children'])) {
			$elderdate = $family->getMarriageDate();
			foreach ($people['children'] as $key => $child) {
				$fulln = strip_tags($child->getFullName());
				$menu = new KT_Menu(getCloseRelationshipName($person, $child));
				$slabel = print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel .= $spouselinks;
				$submenu = new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				0 == $child->getDeathYear() ? $DeathYr = '' : $DeathYr = $child->getDeathYear();
				0 == $child->getBirthYear() ? $BirthYr = '' : $BirthYr = $child->getBirthYear(); ?>
						<tr>
							<td>
								<?php echo $menu->getMenu(); ?>
							</td>
							<td align="left">
								<?php if ($child->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $child->getFullName(); ?>
								</a>
								<?php } else {
									echo KT_I18N::translate('Private');
								} ?>
							</td>
						</tr>
					<?php }
			}
	} ?>
			<?php // -- Build Spouse Family -------------
	$families = $person->getSpouseFamilies();
	foreach ($families as $family) {
		$people = $controller->buildFamilyList($family, 'spouse');
		if ($people) { ?>
					<!-- blank row 2-->
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				<?php }
		$marrdate = $family->getMarriageDate();
		// Husband
		if (isset($people['husb'])) {
			$fulln = strip_tags($people['husb']->getFullName());
			$menu = new KT_Menu(getCloseRelationshipName($person, $people['husb']));
			$slabel = print_pedigree_person_nav2($people['husb']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu);
			0 == $people['husb']->getDeathYear() ? $DeathYr = '' : $DeathYr = $people['husb']->getDeathYear();
			0 == $people['husb']->getBirthYear() ? $BirthYr = '' : $BirthYr = $people['husb']->getBirthYear(); ?>
					<tr class="fact_value">
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left" >
							<?php if ($people['husb']->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $people['husb']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $people['husb']->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					<tr>
				<?php }
		// Wife
		if (isset($people['wife'])) {
			$fulln = strip_tags($people['wife']->getFullName());
			$menu = new KT_Menu(getCloseRelationshipName($person, $people['wife']));
			$slabel = print_pedigree_person_nav2($people['wife']->getXref(), 2, 0, $personcount++, $currpid, $censyear);
			$slabel .= $parentlinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubMenu($submenu);
			0 == $people['wife']->getDeathYear() ? $DeathYr = '' : $DeathYr = $people['wife']->getDeathYear();
			0 == $people['wife']->getBirthYear() ? $BirthYr = '' : $BirthYr = $people['wife']->getBirthYear(); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($people['wife']->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $people['wife']->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $people['wife']->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					<tr>
				<?php }
		// Children
		foreach ($people['children'] as $key => $child) {
			$fulln = strip_tags($child->getFullName());
			$menu = new KT_Menu(getCloseRelationshipName($person, $child));
			$slabel = print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $child->getLabel(), $censyear);
			$slabel .= $spouselinks;
			$submenu = new KT_Menu($slabel);
			$menu->addSubmenu($submenu); ?>
					<tr>
						<td >
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($child->canDisplayDetails()) { ?>
								<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
									<?php echo $child->getFullName(); ?>
								</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
				<?php }
		} ?>
			<tr>
				<td colspan="2">
					<a class="error" href="#" onclick="fam_nav_close();"><?php echo KT_I18N::translate('Close'); ?></a>
				</td>
			<tr>
		</table>
	</div> <!-- close "media-links" -->
	<?php
}

/**
 * Convert a file upload PHP error code into user-friendly text.
 *
 * @param int $error_code
 *
 * @return string
 */
function fileUploadErrorText($error_code)
{
	switch ($error_code) {
		case UPLOAD_ERR_OK:
			return KT_I18N::translate('File successfully uploaded');

		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('The uploaded file exceeds the allowed size.');

		case UPLOAD_ERR_PARTIAL:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('The file was only partially uploaded. Please try again.');

		case UPLOAD_ERR_NO_FILE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('No file was received. Please try again.');

		case UPLOAD_ERR_NO_TMP_DIR:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('The PHP temporary folder is missing.');

		case UPLOAD_ERR_CANT_WRITE:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('PHP failed to write to disk.');

		case UPLOAD_ERR_EXTENSION:
			// I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
			return KT_I18N::translate('PHP blocked the file because of its extension.');

		default:
			return 'Error: '.$error_code;
	}
}
