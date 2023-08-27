<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once KT_ROOT.'includes/functions/functions_import.php';
require_once KT_ROOT.'includes/functions/functions_edit_addsimpletags.php';


// Invoke the Carbon Autoloader, to make any Carbon date class available
require KT_ROOT . 'library/Carbon/autoload.php';
use Carbon\Carbon;

// Print an edit control for a ADOP field
function edit_field_adop_u($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Adop::getValues(), null, $selected, $extra);
}

// Print an edit control for a ADOP female field
function edit_field_adop_f($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Adop::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX F")), null, $selected, $extra);
}

// Print an edit control for a ADOP male field
function edit_field_adop_m($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Adop::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX M")), null, $selected, $extra);
}

// Print an edit control for a PEDI field
function edit_field_pedi_u($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Pedi::getValues(), '', $selected, $extra);
}

// Print an edit control for a PEDI female field
function edit_field_pedi_f($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Pedi::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX F")), '', $selected, $extra);
}

// Print an edit control for a PEDI male field
function edit_field_pedi_m($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Pedi::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX M")), '', $selected, $extra);
}

// Print an edit control for a NAME TYPE field
function edit_field_name_type_u($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Name::getValues(), '', $selected, $extra);
}

// Print an edit control for a female NAME TYPE field
function edit_field_name_type_f($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Name::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX F")), '', $selected, $extra);
}

// Print an edit control for a male NAME TYPE field
function edit_field_name_type_m($name, $selected = '', $extra = '')
{
	return select_edit_control($name, KT_Gedcom_Code_Name::getValues(new KT_Person("0 @XXX@ INDI\n1 SEX M")), '', $selected, $extra);
}

// Print an edit control for a RELA field
function edit_field_rela($name, $selected = '', $extra = '')
{
	$rela_codes = KT_Gedcom_Code_Rela::getValues();
	// The user is allowed to specify values that aren't in the list.
	if (!array_key_exists($selected, $rela_codes)) {
		$rela_codes[$selected] = $selected;
	}

	return select_edit_control($name, $rela_codes, '', $selected, $extra);
}

/**
 * Check if the given gedcom record has changed since the last session access
 * This is used to check if the gedcom record changed between the time the user
 * loaded the individual page and the time they clicked on a link to edit
 * the data.
 *
 * @param string $pid      The gedcom id of the record to check
 * @param mixed  $gedrec
 * @param mixed  $lastTime
 */
function checkChangeTime($pid, $gedrec, $lastTime)
{
	$lastTime = Carbon::createFromTimestamp($lastTime)->toDateTimeString();

	$change = KT_DB::prepare("
		SELECT UNIX_TIMESTAMP(change_time) AS change_time, user_name
		FROM `##change`
		JOIN `##user` USING (user_id)
		WHERE status<>'rejected' AND gedcom_id=? AND xref=? AND change_time>?
		ORDER BY change_id DESC
		LIMIT 1
	")->execute(array(KT_GED_ID, $pid, $lastTime))->fetchOneRow();

	if ($change) {
		$changeTime = $change->change_time;
		$changeUser = $change->user_name;
	} else {
		$changeTime = 0;
		$changeUser = '';
	}

	if (isset($_REQUEST['linenum']) && $changeTime != 0 && $lastTime && $changeTime > $lastTime) {
		global $controller;
		$controller->pageHeader();
		echo '<p class="error">', KT_I18N::translate('The record with id %s was changed by another user since you last accessed it.', $pid) . '</p>';
		if (!empty($changeUser)) {
			echo '<p>' . KT_I18N::translate('This record was last changed by <i>%s</i> at %s', $changeUser, $changeTime), '</p>';
			echo '<p>' . KT_I18N::translate('Current time is %s', $lastTime) . '</p>';
		}
		echo '<p>' . KT_I18N::translate('Please reload the previous page to make sure you are working with the most recent record.') . "</p>";
		exit;
	}
}

// Replace an updated record with a newer version
// $xref/$ged_id - the record to update
// $gedrec       - the new gedcom record
// $chan         - whether or not to update the CHAN record
function replace_gedrec($xref, $ged_id, $gedrec, $chan = true)
{
	if (($gedrec = check_gedcom($gedrec, $chan)) !== false) {
		$old_gedrec = find_gedcom_record($xref, $ged_id, true);
		if ($old_gedrec != $gedrec) {
			KT_DB::prepare(
				'INSERT INTO `##change` (gedcom_id, xref, old_gedcom, new_gedcom, user_id) VALUES (?, ?, ?, ?, ?)'
			)->execute([
				$ged_id,
				$xref,
				$old_gedrec,
				$gedrec,
				KT_USER_ID,
			]);
		}

		if (get_user_setting(KT_USER_ID, 'auto_accept')) {
			accept_all_changes($xref, $ged_id);
		}

		return true;
	}

	return false;
}

// -- this function will append a new gedcom record at
// -- the end of the gedcom file.
function append_gedrec($gedrec, $ged_id)
{
	if (($gedrec = check_gedcom($gedrec, true)) !== false && preg_match('/0 @(' . KT_REGEX_XREF . ')@ (' . KT_REGEX_TAG . ')/', $gedrec, $match)) {
		$gid = $match[1];
		$type = $match[2];

		if (0 == preg_match('/\\d/', $gid)) {
			$xref = get_new_xref($type);
		} else {
			$xref = $gid;
		}
		$gedrec = preg_replace('/^0 @(.*)@/', "0 @{$xref}@", $gedrec);

		KT_DB::prepare(
			'INSERT INTO `##change` (gedcom_id, xref, old_gedcom, new_gedcom, user_id) VALUES (?, ?, ?, ?, ?)'
		)->execute([
			$ged_id,
			$xref,
			'',
			$gedrec,
			KT_USER_ID,
		]);

		AddToLog("Appending new {$type} record {$xref}", 'edit');

		if (get_user_setting(KT_USER_ID, 'auto_accept')) {
			accept_all_changes($xref, KT_GED_ID);
		}

		return $xref;
	}

	return false;
}

//-- this function will delete the gedcom record with
//-- the given $xref
function delete_gedrec($xref, $ged_id) {
	KT_DB::prepare(
		"INSERT INTO `##change` (gedcom_id, xref, old_gedcom, new_gedcom, user_id) VALUES (?, ?, ?, ?, ?)"
	)->execute(array(
		$ged_id,
		$xref,
		find_gedcom_record($xref, $ged_id, true),
		'',
		KT_USER_ID
	));

	AddToLog("Deleting gedcom record $xref", 'edit');

	if (get_user_setting(KT_USER_ID, 'auto_accept')) {
		accept_all_changes($xref, KT_GED_ID);
	}
}

//-- this function will check a GEDCOM record for valid gedcom format
function check_gedcom($gedrec, $chan=true) {
	$ct = preg_match("/0 @(.*)@ (.*)/", $gedrec, $match);

	if ($ct == 0) {
		echo "ERROR 20: Invalid GEDCOM format";
		AddToLog("ERROR 20: Invalid GEDCOM format:\n" . $gedrec, 'edit');
		if (KT_DEBUG) {
			echo "<pre>$gedrec</pre>";
			echo debug_print_backtrace();
		}
		return false;
	}

	// MSDOS line endings will break things in horrible ways
	$gedrec = preg_replace('/[\r\n]+/', "\n", $gedrec);

	$gedrec = trim($gedrec);
	if ($chan) {
		$pos1 = strpos($gedrec, "1 CHAN");
		if ($pos1 !== false) {
			$pos2 = strpos($gedrec, "\n1", $pos1+4);
			if ($pos2 === false) $pos2 = strlen($gedrec);
			$newgedrec = substr($gedrec, 0, $pos1);
			$newgedrec .= "1 CHAN\n2 DATE ".strtoupper(date("d M Y"))."\n";
			$newgedrec .= "3 TIME ".date("H:i:s")."\n";
			$newgedrec .= "2 _KT_USER ".KT_USER_NAME."\n";
			$newgedrec .= substr($gedrec, $pos2);
			$gedrec = $newgedrec;
		}
		else {
			$newgedrec = "\n1 CHAN\n2 DATE ".strtoupper(date("d M Y"))."\n";
			$newgedrec .= "3 TIME ".date("H:i:s")."\n";
			$newgedrec .= "2 _KT_USER ".KT_USER_NAME;
			$gedrec .= $newgedrec;
		}
	}
	$gedrec = preg_replace('/\\\+/', "\\", $gedrec);

	//-- remove any empty lines
	$lines = explode("\n", $gedrec);
	$newrec = '';
	foreach ($lines as $ind=>$line) {
		//-- remove any whitespace
		$line = trim($line);
		if (!empty($line)) $newrec .= $line."\n";
	}

	$newrec = html_entity_decode($newrec, ENT_COMPAT, 'UTF-8');
	return $newrec;
}

// Remove all links from $gedrec to $xref, and any sub-tags.
function remove_links($gedrec, $xref) {
	$gedrec = preg_replace('/\n1 '.KT_REGEX_TAG.' @'.$xref.'@(\n[2-9].*)*/', '', $gedrec);
	$gedrec = preg_replace('/\n2 '.KT_REGEX_TAG.' @'.$xref.'@(\n[3-9].*)*/', '', $gedrec);
	$gedrec = preg_replace('/\n3 '.KT_REGEX_TAG.' @'.$xref.'@(\n[4-9].*)*/', '', $gedrec);
	$gedrec = preg_replace('/\n4 '.KT_REGEX_TAG.' @'.$xref.'@(\n[5-9].*)*/', '', $gedrec);
	$gedrec = preg_replace('/\n5 '.KT_REGEX_TAG.' @'.$xref.'@(\n[6-9].*)*/', '', $gedrec);
	return $gedrec;
}

// Remove a link to a media object from a GEDCOM record
function remove_media_subrecord($oldrecord, $gid) {
	$newrec = '';
	$gedlines = explode("\n", $oldrecord);

	for ($i=0; $i<count($gedlines); $i++) {
		if (preg_match('/^\d (?:OBJE|_KT_OBJE_SORT) @' . $gid . '@$/', $gedlines[$i])) {
			$glevel = $gedlines[$i][0];
			$i++;
			while ((isset($gedlines[$i]))&&(strlen($gedlines[$i])<4 || $gedlines[$i][0]>$glevel)) {
				$i++;
			}
			$i--;
		} else {
			$newrec .= $gedlines[$i]."\n";
		}
	}

	return trim($newrec);
}

/**
* delete a subrecord from a parent record using the linenumber
*
* @param string $oldrecord parent record to delete from
* @param int $linenum linenumber where the subrecord to delete starts
* @return string the new record
*/
function remove_subline($oldrecord, $linenum) {
	$newrec = '';
	$gedlines = explode("\n", $oldrecord);

	for ($i=0; $i<$linenum; $i++) {
		if (trim($gedlines[$i])!='') $newrec .= $gedlines[$i]."\n";
	}
	if (isset($gedlines[$linenum])) {
		$fields = explode(' ', $gedlines[$linenum]);
		$glevel = $fields[0];
		$i++;
		if ($i<count($gedlines)) {
			//-- don't put empty lines in the record
			while ((isset($gedlines[$i]))&&(strlen($gedlines[$i])<4 || $gedlines[$i][0]>$glevel)) $i++;
			while ($i<count($gedlines)) {
				if (trim($gedlines[$i])!='') $newrec .= $gedlines[$i]."\n";
				$i++;
			}
		}
	}
	else return $oldrecord;

	$newrec = trim($newrec);
	return $newrec;
}

/**
 * prints a form to add an individual or edit an individual's name.
 *
 * @param string $nextaction the next action the edit_interface.php file should take after the form is submitted
 * @param string $famid      the family that the new person should be added to
 * @param string $namerec    the name subrecord when editing a name
 * @param string $famtag     how the new person is added to the family
 * @param mixed  $linenum
 * @param mixed  $sextag
 */
function print_indi_form($nextaction, $famid, $linenum = '', $namerec = '', $famtag = 'CHIL', $sextag = '')
{
	global $pid, $WORD_WRAPPED_NOTES;
	global $NPFX_accept, $SPFX_accept, $NSFX_accept, $FILE_FORM_accept;
	global $bdm, $STANDARD_NAME_FACTS, $REVERSED_NAME_FACTS, $ADVANCED_NAME_FACTS, $ADVANCED_PLAC_FACTS;
	global $QUICK_REQUIRED_FACTS, $QUICK_REQUIRED_FAMFACTS, $NO_UPDATE_CHAN, $controller, $iconStyle;

	$SURNAME_TRADITION = get_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION');
	$UNLINKED          = 'no';

	$bdm = ''; // used to copy '1 SOUR' to '2 SOUR' for BIRT DEAT MARR
	init_calendar_popup(); ?>
	<form class="cell" method="post" name="addchildform" onsubmit="return checkform();">
		<input type="hidden" name="action" value="<?php echo $nextaction; ?>">
		<input type="hidden" name="linenum" value="<?php echo $linenum; ?>">
		<input type="hidden" name="famid" value="<?php echo $famid; ?>">
		<input type="hidden" name="pid" value="<?php echo $pid; ?>">
		<input type="hidden" name="famtag" value="<?php echo $famtag; ?>">
		<input type="hidden" name="goto" value="">
		<div id="add_name_details" class="grid-x">
			<?php
			// When adding a new child, specify the pedigree
			if (('addchildaction' == $nextaction || 'addopfchildaction' == $nextaction) && KT_SCRIPT_NAME !== 'admin_trees_addunlinked.php') {
				add_simple_tag('0 PEDI');
			}
			// Add TYPE option on updateSOUR
			if ('update' == $nextaction) {
				$name_type = get_gedcom_value('TYPE', 2, $namerec);
				add_simple_tag('0 TYPE ' . $name_type);
			}
			// Populate the standard NAME field and subfields
			$name_fields = [];
			foreach ($STANDARD_NAME_FACTS as $tag) {
				$name_fields[$tag] = get_gedcom_value($tag, 0, $namerec);
			}

			$new_marnm = '';
			// Inherit surname from parents, spouse or child
			if (empty($namerec)) {
				// We'll need the parent's name to set the child's surname
				$family = KT_Family::getInstance($famid);
				if ($family && $family->getHusband()) {
					$father_name = get_gedcom_value('NAME', 0, $family->getHusband()->getGedcomRecord());
				} else {
					$father_name = '';
				}
				if ($family && $family->getWife()) {
					$mother_name = get_gedcom_value('NAME', 0, $family->getWife()->getGedcomRecord());
				} else {
					$mother_name = '';
				}
				// We'll need the spouse/child's name to set the spouse/parent's surname
				$prec = find_gedcom_record($pid, KT_GED_ID, true);
				$indi_name = get_gedcom_value('NAME', 0, $prec);
				// Different cultures do surnames differently
				switch ($SURNAME_TRADITION) {
					case 'spanish':
						// Mother: Maria /AAAA BBBB/
						// Father: Jose  /CCCC DDDD/
						// Child:  Pablo /CCCC AAAA/
						switch ($nextaction) {
							case 'addchildaction':
								if (preg_match('/\/(\S+)\s+\S+\//', $mother_name, $matchm)
										&& preg_match('/\/(\S+)\s+\S+\//', $father_name, $matchf)) {
									$name_fields['SURN'] = $matchf[1] . ' ' . $matchm[1];
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}

								break;

							case 'addnewparentaction':
								if ('HUSB' == $famtag && preg_match('/\/(\S+)\s+\S+\//', $indi_name, $match)) {
									$name_fields['SURN'] = $match[1] . ' ';
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}
								if ('WIFE' == $famtag && preg_match('/\/\S+\s+(\S+)\//', $indi_name, $match)) {
									$name_fields['SURN'] = $match[1] . ' ';
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}

								break;
						}

						break;

					case 'portuguese':
						// Mother: Maria /AAAA BBBB/
						// Father: Jose  /CCCC DDDD/
						// Child:  Pablo /BBBB DDDD/
						switch ($nextaction) {
							case 'addchildaction':
								if (preg_match('/\/\S+\s+(\S+)\//', $mother_name, $matchm)
										&& preg_match('/\/\S+\s+(\S+)\//', $father_name, $matchf)) {
									$name_fields['SURN'] = $matchf[1] . ' ' . $matchm[1];
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}

								break;

							case 'addnewparentaction':
								if ('HUSB' == $famtag && preg_match('/\/\S+\s+(\S+)\//', $indi_name, $match)) {
									$name_fields['SURN'] = ' ' . $match[1];
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}
								if ('WIFE' == $famtag && preg_match('/\/(\S+)\s+\S+\//', $indi_name, $match)) {
									$name_fields['SURN'] = ' ' . $match[1];
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}

								break;
						}

						break;

					case 'icelandic':
						// Sons get their father's given name plus "sson"
						// Daughters get their father's given name plus "sdottir"
						switch ($nextaction) {
							case 'addchildaction':
								if ('M' == $sextag && preg_match('/(\S+)\s+\/.*\//', $father_name, $match)) {
									$name_fields['SURN'] = preg_replace('/s$/', '', $match[1]) . 'sson';
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}
								if ('F' == $sextag && preg_match('/(\S+)\s+\/.*\//', $father_name, $match)) {
									$name_fields['SURN'] = preg_replace('/s$/', '', $match[1]) . 'sdottir';
									$name_fields['NAME'] = '/' . $name_fields['SURN'] . '/';
								}

								break;

							case 'addnewparentaction':
								if ('HUSB' == $famtag && preg_match('/(\S+)sson\s+\/.*\//i', $indi_name, $match)) {
									$name_fields['GIVN'] = $match[1];
									$name_fields['NAME'] = $name_fields['GIVN'] . ' //';
								}
								if ('WIFE' == $famtag && preg_match('/(\S+)sdottir\s+\/.*\//i', $indi_name, $match)) {
									$name_fields['GIVN'] = $match[1];
									$name_fields['NAME'] = $name_fields['GIVN'] . ' //';
								}

								break;
						}

						break;

					case 'patrilineal':
						// Father gives his surname to his children
						switch ($nextaction) {
							case 'addchildaction':
								if (preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $father_name, $match)) {
									$name_fields['SURN'] = $match[2];
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}

								break;

							case 'addnewparentaction':
								if ('HUSB' == $famtag && preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $indi_name, $match)) {
									$name_fields['SURN'] = $match[2];
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}

								break;
						}

						break;

					case 'matrilineal':
						// Mother gives her surname to her children
						switch ($nextaction) {
							case 'addchildaction':
								if (preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $mother, $match)) {
									$name_fields['SURN'] = $match[2];
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}

								break;

							case 'addnewparentaction':
								if ('WIFE' == $famtag && preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $indi_name, $match)) {
									$name_fields['SURN'] = $match[2];
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}

								break;
						}

						break;

					case 'paternal':
					case 'polish':
					case 'lithuanian':
						// Father gives his surname to his wife and children
						switch ($nextaction) {
							case 'addspouseaction':
								if ('WIFE' == $famtag && preg_match('/\/(.*)\//', $indi_name, $match)) {
									if ('polish' == $SURNAME_TRADITION) {
										$match[1] = preg_replace(['/ski$/', '/cki$/', '/dzki$/', '/żki$/'], ['ska', 'cka', 'dzka', 'żka'], $match[1]);
									} elseif ('lithuanian' == $SURNAME_TRADITION) {
										$match[1] = preg_replace(['/as$/', '/is$/', '/ys$/', '/us$/'], ['ienė', 'ienė', 'ienė', 'ienė'], $match[1]);
									}
									$new_marnm = $match[1];
								}

								break;

							case 'addchildaction':
								if (preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $father_name, $match)) {
									$name_fields['SURN'] = $match[2];
									if ('polish' == $SURNAME_TRADITION && 'F' == $sextag) {
										$match[2] = preg_replace(['/ski$/', '/cki$/', '/dzki$/', '/żki$/'], ['ska', 'cka', 'dzka', 'żka'], $match[2]);
									} elseif ('lithuanian' == $SURNAME_TRADITION && 'F' == $sextag) {
										$match[2] = preg_replace(['/as$/', '/a$/', '/is$/', '/ys$/', '/ius$/', '/us$/'], ['aitė', 'aitė', 'ytė', 'ytė', 'iūtė', 'utė'], $match[2]);
									}
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}

								break;

							case 'addnewparentaction':
								if ('HUSB' == $famtag && preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $indi_name, $match)) {
									if ('polish' == $SURNAME_TRADITION && 'M' == $sextag) {
										$match[2] = preg_replace(['/ska$/', '/cka$/', '/dzka$/', '/żka$/'], ['ski', 'cki', 'dzki', 'żki'], $match[2]);
									} elseif ('lithuanian' == $SURNAME_TRADITION) {
										// not a complete list as the rules are somewhat complicated but will do 95% correctly
										$match[2] = preg_replace(['/aitė$/', '/ytė$/', '/iūtė$/', '/utė$/'], ['as', 'is', 'ius', 'us'], $match[2]);
									}
									$name_fields['SPFX'] = trim($match[1]);
									$name_fields['SURN'] = $match[2];
									$name_fields['NAME'] = "/{$match[1]}{$match[2]}/";
								}
								if ('WIFE' == $famtag && preg_match('/\/((?:[a-z]{2,3} )*)(.*)\//i', $indi_name, $match)) {
									if ('lithuanian' == $SURNAME_TRADITION) {
										$match[2] = preg_replace(['/as$/', '/is$/', '/ys$/', '/us$/'], ['ienė', 'ienė', 'ienė', 'ienė'], $match[2]);
										$match[2] = preg_replace(['/aitė$/', '/ytė$/', '/iūtė$/', '/utė$/'], ['ienė', 'ienė', 'ienė', 'ienė'], $match[2]);
										$new_marnm = $match[2];
									}
								}

								break;
						}

						break;
				}
			}

			// Make sure there are two slashes in the name
			if ($name_fields['NAME']) {
				if (!preg_match('/\//', $name_fields['NAME'])) {
					$name_fields['NAME'] .= ' /';
				}
				if (!preg_match('/\/.*\//', $name_fields['NAME'])) {
					$name_fields['NAME'] .= '/';
				}

				// Populate any missing 2 XXXX fields from the 1 NAME field
				$npfx_accept = implode('|', $NPFX_accept);
				if (preg_match("/((({$npfx_accept})\\.? +)*)([^\n\\/\"]*)(\"(.*)\")? *\\/(([a-z]{2,3} +)*)(.*)\\/ *(.*)/i", $name_fields['NAME'], $name_bits)) {
					if (empty($name_fields['NPFX'])) {
						$name_fields['NPFX'] = $name_bits[1];
					}
					if (empty($name_fields['SPFX']) && empty($name_fields['SURN'])) {
						$name_fields['SPFX'] = trim($name_bits[7]);
						// For names with two surnames, there will be four slashes.
						// Turn them into a list
						$name_fields['SURN'] = preg_replace('~/[^/]*/~', ',', $name_bits[9]);
					}
					if (empty($name_fields['GIVN'])) {
						$name_fields['GIVN'] = $name_bits[4];
					}
					// Don't automatically create an empty NICK - it is an "advanced" field.
					if (empty($name_fields['NICK']) && !empty($name_bits[6]) && !preg_match('/^2 NICK/m', $namerec)) {
						$name_fields['NICK'] = $name_bits[6];
					}
				}
			}

			// Edit the standard name fields
			foreach ($name_fields as $tag => $value) {
				add_simple_tag("0 {$tag} {$value}");
			}

			// Get the advanced name fields
			$adv_name_fields = [];
			if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_NAME_FACTS, $match)) {
				foreach ($match[1] as $tag) {
					$adv_name_fields[$tag] = '';
				}
			}
			// This is a custom tag, but kiwitrees uses it extensively.
			if ('paternal' == $SURNAME_TRADITION || 'polish' == $SURNAME_TRADITION || 'lithuanian' == $SURNAME_TRADITION || (false !== strpos($namerec, '2 _MARNM'))) {
				$adv_name_fields['_MARNM'] = '';
			}
			$person = KT_Person::getInstance($pid);
			if (isset($adv_name_fields['TYPE'])) {
				unset($adv_name_fields['TYPE']);
			}
			foreach ($adv_name_fields as $tag => $dummy) {
				// Edit existing tags
				if (preg_match_all("/2 {$tag} (.+)/", $namerec, $match)) {
					foreach ($match[1] as $value) {
						if ('_MARNM' == $tag) {
							$mnsct = preg_match('/\/(.+)\//', $value, $match2);
							$marnm_surn = '';
							if ($mnsct > 0) {
								$marnm_surn = $match2[1];
							}
							add_simple_tag('2 _MARNM ' . $value);
							add_simple_tag('2 _MARNM_SURN ' . $marnm_surn);
						} else {
							add_simple_tag("2 {$tag} {$value}", '', KT_Gedcom_Tag::getLabel("NAME:{$tag}", $person));
						}
					}
				}
				// Allow a new row to be entered if there was no row provided
				if (0 == count($match[1]) && empty($name_fields[$tag]) || '_HEB' != $tag && 'NICK' != $tag) {
					if ('_MARNM' == $tag) {
						if (false == strstr($ADVANCED_NAME_FACTS, '_MARNM')) {
							add_simple_tag('0 _MARNM');
							add_simple_tag("0 _MARNM_SURN {$new_marnm}");
						}
					} else {
						add_simple_tag("0 {$tag}", '', KT_Gedcom_Tag::getLabel("NAME:{$tag}", $person));
					}
				}
			}

			// Handle any other NAME subfields that aren't included above (SOUR, NOTE, _CUSTOM, etc)
			if ($namerec != '' && 'NEW' != $namerec) {
				$gedlines = explode("\n", $namerec); // -- find the number of lines in the record
				$fields = explode(' ', $gedlines[0]);
				$glevel = $fields[0];
				$level = $glevel;
				$type = trim($fields[1]);
				$level1type = $type;
				$tags = [];
				$i = 0;
				do {
					if ('TYPE' != $type && !isset($name_fields[$type]) && !isset($adv_name_fields[$type])) {
						$text = '';
						for ($j = 2; $j < count($fields); $j++) {
							if ($j > 2) {
								$text .= ' ';
							}
							$text .= $fields[$j];
						}
						$iscont = false;
						while (($i + 1 < count($gedlines)) && (preg_match('/' . ($level + 1) . ' (CON[CT]) ?(.*)/', $gedlines[$i + 1], $cmatch) > 0)) {
							$iscont = true;
							if ('CONT' == $cmatch[1]) {
								$text .= "\n";
							}
							if ($WORD_WRAPPED_NOTES) {
								$text .= ' ';
							}
							$text .= $cmatch[2];
							$i++;
						}
						add_simple_tag($level . ' ' . $type . ' ' . $text);
					}
					$tags[] = $type;
					$i++;
					if (isset($gedlines[$i])) {
						$fields = explode(' ', $gedlines[$i]);
						$level = $fields[0];
						if (isset($fields[1])) {
							$type = $fields[1];
						}
					}
				} while (($level > $glevel) && ($i < count($gedlines)));
			}
			?>
		</div>

		<?php // If we are adding a new individual, add the basic details
		if ('update' != $nextaction) { ?>
			<div id="add_other_details" class="grid-x">
				<?php // 1 SEX
				if ('HUSB' == $famtag || 'M' == $sextag) {
					add_simple_tag('0 SEX M');
				} elseif ('WIFE' == $famtag || 'F' == $sextag) {
					add_simple_tag('0 SEX F');
				} else {
					add_simple_tag('0 SEX');
				}
				$bdm = 'BD';
				if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FACTS, $matches)) {
					foreach ($matches[1] as $match) {
						if (!in_array($match, explode('|', KT_EVENTS_DEAT))) {
							addSimpleTags($match);
						}
					}
				}
				// -- if adding a spouse add the option to add a marriage fact to the new family
				if ('addspouseaction' == $nextaction || ('addnewparentaction' == $nextaction && 'new' != $famid)) {
					$bdm .= 'M';
					if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FAMFACTS, $matches)) {
						foreach ($matches[1] as $match) {
							addSimpleTags($match);
						}
					}
				}
				if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FACTS, $matches)) {
					foreach ($matches[1] as $match) {
						if (in_array($match, explode('|', KT_EVENTS_DEAT))) {
							addSimpleTags($match);
						}
					}
				} ?>
			</div>
		<?php } ?>

		<div id="additional_facts" class="grid-x">
			<ul class="cell accordion" data-accordion data-multi-expand="true" data-allow-all-closed="true">
				<?php if ('update' == $nextaction) { ?>
					<?php echo additionalFacts('NAME_update'); ?>
				<?php } else { ?>
					<?php echo additionalFacts('NAME'); ?>
				<?php } ?>
			</ul>
		</div>

		<?php echo no_update_chan(); ?>

		<div class="cell align-left button-group">
			<button class="button primary" type="submit">
				<i class="<?php echo $iconStyle; ?> fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
			<?php if (preg_match('/^add(child|spouse|newparent)/', $nextaction)) { ?>
				<button class="button primary" type="submit" onclick="document.addchildform.goto.value='new';">
					<i class="<?php echo $iconStyle; ?> fa-mail-forward"></i>
					<?php echo KT_I18N::translate('Save and go to new individual'); ?>
				</button>
			<?php } ?>
			<?php if ('no' === $UNLINKED) { ?>
				<button class="button secondary" type="button"  onclick="window.close();">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			<?php } else { ?>
				<button class="button secondary" type="button" data-toggle="<?php echo $UNLINKED; ?>">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
					<?php echo KT_I18N::translate('Cancel'); ?>
				</button>
			<?php } ?>
			<button
				class="button secondary"
				type="button"
				onclick="check_duplicates();"
				title="<?php /* I18N: button hover title */ KT_I18N::translate('Check for possible duplicates'); ?>"
			>
				<i class="<?php echo $iconStyle; ?> fa-eye"></i>
				<?php echo KT_I18N::translate('Check'); ?>
			</button>
		</div>
	</form>

	<?php
	$controller->addInlineJavascript('
		SURNAME_TRADITION="' . $SURNAME_TRADITION . '";
		sextag="' . $sextag . '";
		famtag="' . $famtag . '";
		function trim(str) {
			str=str.replace(/\s\s+/g, " ");
			return str.replace(/(^\s+)|(\s+$)/g, "");
		}

		function lang_class(str) {
			if (str.match(/[\u0370-\u03FF]/)) return "greek";
			if (str.match(/[\u0400-\u04FF]/)) return "cyrillic";
			if (str.match(/[\u0590-\u05FF]/)) return "hebrew";
			if (str.match(/[\u0600-\u06FF]/)) return "arabic";
			return "latin"; // No matched text implies latin :-)
		}

		// Generate a full name from the name components
		function generate_name() {
			var npfx = jQuery("#autocompleteInput-NPFX").val();
			var givn = jQuery("#autocompleteInput-GIVN").val();
			var spfx = jQuery("#autocompleteInput-SPFX").val();
			var surn = jQuery("#autocompleteInput-SURN").val();
			var nsfx = jQuery("#autocompleteInput-NSFX").val();
			if (SURNAME_TRADITION === "polish" && (gender === "F" || famtag === "WIFE")) {
				surn = surn.replace(/ski$/, "ska");
				surn = surn.replace(/cki$/, "cka");
				surn = surn.replace(/dzki$/, "dzka");
				surn = surn.replace(/żki$/, "żka");
			}
			// Commas are used in the GIVN and SURN field to separate lists of surnames.
			// For example, to differentiate the two Spanish surnames from an English
			// double-barred name.
			// Commas *may* be used in other fields, and will form part of the NAME.
			if (KT_LOCALE === "vi" || KT_LOCALE === "hu") {
				// Default format: /SURN/ GIVN
				return trim(npfx+" /"+trim(spfx+" "+surn).replace(/ *, */g, " ")+"/ "+givn.replace(/ *, */g, " ")+" "+nsfx);
			} else if (KT_LOCALE === "zh-Hans" || KT_LOCALE === "zh-Hant") {
				// Default format: /SURN/GIVN
				return npfx+"/"+spfx+surn+"/"+givn+nsfx;
			} else {
				// Default format: GIVN /SURN/
				return trim(npfx+" "+givn.replace(/ *, */g, " ")+" /"+trim(spfx+" "+surn).replace(/ *, */g, " ")+"/ "+nsfx);
			}
		}

		// Update the NAME and _MARNM fields from the name components
		// and also display the value in read-only "gedcom" format.
		function updatewholename() {
			// Don’t update the name if the user manually changed it
			if (manualChange) {
				return;
			}
			var npfx = jQuery("#autocompleteInput-NPFX").val();
			var givn = jQuery("#autocompleteInput-GIVN").val();
			var spfx = jQuery("#autocompleteInput-SPFX").val();
			var surn = jQuery("#autocompleteInput-SURN").val();
			var nsfx = jQuery("#autocompleteInput-NSFX").val();
			var name = generate_name();
			jQuery("#NAME").val(name);

			// Married names inherit some NSFX values, but not these
			nsfx = nsfx.replace(/^(I|II|III|IV|V|VI|Junior|Jr\.?|Senior|Sr\.?)$/i, "");

			// Update _MARNM field from autocompleteInput-_MARNM_SURN field and display it
			// Be careful of mixing latin/hebrew/etc. character sets.
			var ip = document.getElementsByTagName("input");
			var marnm_id = "";
			var romn = "";
			var heb = "";
			for (var i = 0; i < ip.length; i++) {
				var val = trim(ip[i].value);
				if (ip[i].id.indexOf("_HEB") === 0) {
					heb = val;
				}
				if (ip[i].id.indexOf("ROMN") === 0) {
					romn = val;
				}
				if (ip[i].id.indexOf("autocompleteInput-_MARNM_SURN") === 0) {
					var msurn = "";
					if (val !== "") {
						var lc = lang_class(document.getElementById(ip[i].id).value);
						if (lang_class(name) === lc)
							msurn = trim(npfx + " " + givn + " /" + val + "/ " + nsfx);
						else if (lc === "hebrew")
							msurn = heb.replace(/\/.*\//, "/" + val + "/");
						else if (lang_class(romn) === lc)
							msurn = romn.replace(/\/.*\//, "/" + val + "/");
					}
                    document.getElementById(marnm_id).value = msurn;
                    document.getElementById("_MARNM").innerHTML = msurn;
                } else {
                    marnm_id = ip[i].id;
				}
			}

		}

		// Toggle the name editor fields between to add or remove readonly attribute
		// eid = elementid
		var oldName = "";
		var manualChange = false;

		function convertReadOnly(eid) {
			if(jQuery("input#" + eid).prop("readonly")){
				jQuery("input#" + eid).prop("readonly",false);
				jQuery("input#" + eid).removeClass("readonly");
			} else {
				jQuery("input#" + eid).prop("readonly",true);
				jQuery("input#" + eid).addClass("readonly");
			}
		}

		/**
		* if the user manually changed the NAME field, then update the textual
		* HTML representation of it
		* If the value changed, set manualChange to true so that changing
		* the other fields doesn’t change the NAME line
		*/
		function updateTextName(eid) {
			var element = document.getElementById(eid);
			if (element) {
				if (element.value != oldName) {
					manualChange = true;
				}
			}
		}

		function checkform() {
			var ip=document.getElementsByTagName("input");
			for (var i=0; i<ip.length; i++) {
				// ADD slashes to _HEB and _AKA names
				if (ip[i].id.indexOf("_AKA") == 0 || ip[i].id.indexOf("_HEB") == 0 || ip[i].id.indexOf("ROMN") == 0)
					if (ip[i].value.indexOf("/")<0 && ip[i].value!="")
						ip[i].value=ip[i].value.replace(/([^\s]+)\s*$/, "/$1/");

				// Blank out temporary _MARNM_SURN
				if (ip[i].id.indexOf("_MARNM_SURN") == 0)
						ip[i].value="";

				// Convert "xxx yyy" and "xxx y yyy" surnames to "xxx,yyy"
				if ((SURNAME_TRADITION == "spanish" || "SURNAME_TRADITION" == "portuguese") && ip[i].id.indexOf("SURN") == 0) {
					ip[i].value=document.forms[0].SURN.value.replace(/^\s*([^\s,]{2,})\s+([iIyY] +)?([^\s,]{2,})\s*$/, "$1,$3");
				}
			}
		}

		// If the name isn’t initially formed from the components in a standard way,
		// then don’t automatically update it.
		if (document.getElementById("NAME").value!=generate_name() && document.getElementById("NAME").value!="//") {
			convertReadOnly("NAME");
		}

		// optional check for possible duplicate person
		function check_duplicates() {
			var frm  = document.forms[0];
			var surn = jQuery("#SURN").val();
			var givn = jQuery("#autocompleteInput-GIVN").val().split(/\s+/)[0]; // uses the first given name only
			return edit_interface({
				"action": "checkduplicates",
				"surname": surn,
				"given": givn
			});
		}
	');
}

/**
 * prints collapsable fields to add ASSO/RELA, SOUR, OBJE ...
 *
 * @param string $tag   Gedcom tag name
 * @param mixed  $level
 */
function print_add_layer($tag, $level = 2)
{
	global $MEDIA_DIRECTORY, $TEXT_DIRECTION;
	global $gedrec, $FULL_SOURCES, $islink, $bdm, $iconStyle;

	if ('OBJE' == $tag && get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') < KT_USER_ACCESS_LEVEL) {
		return;
	}

	if ('SOUR' == $tag) { ?>
		<li class="accordion-item" data-accordion-item>
			<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Add source citation'); ?></a>
			<div id="newsource" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php
						// 2 SOUR
						$source = 'SOUR @';
						add_simple_tag("{$level} {$source}");
						// Checkboxes to apply '1 SOUR' to BIRT/MARR/DEAT as '2 SOUR' ?>
						<div class="grid-x sourceLinks">
							<div class="cell small-12 medium-3">
								<label style="text-indent: 3rem;">
									<?php echo KT_I18N::translate('Link this source to these records'); ?>
								</label>
							</div>
							<div class="cell small-10 medium-7">
								 <?php echo sourceLinks($bdm); ?>
							</div>
						</div>
						<?php // 3 PAGE
						$page = 'PAGE';
						add_simple_tag(($level + 1) . " {$page}");
						// 3 DATA
						// 4 TEXT
						$text = 'TEXT';
						add_simple_tag(($level + 2) . " {$text}");
						if ($FULL_SOURCES) {
							// 4 DATE
							add_simple_tag(($level + 2) . ' DATE', '', KT_Gedcom_Tag::getLabel('DATA:DATE'));
							// 3 QUAY
							add_simple_tag(($level + 1) . ' QUAY');
						}
						// 3 OBJE
						add_simple_tag(($level + 1) . ' OBJE');
						// 3 SHARED_NOTE
						add_simple_tag(($level + 1) . ' SHARED_NOTE');
						?>
					</div>
				</div>
			</div>
		</li>
	<?php }

	if ('ASSO' == $tag || 'ASSO2' == $tag) { ?>
		<li class="accordion-item" data-accordion-item>
			<?php if ('ASSO' == $tag) { ?>
				<a href="#" class="accordion-title">
					<?php echo KT_I18N::translate('Add an associate'); ?>
				</a>

				<?php $id = 'newasso';
			} else { ?>
				<a href="#" class="accordion-title">
					<?php echo KT_I18N::translate('Add another associate'); ?>
				</a>

				<?php $id = 'newasso2';
			} ?>
			<div id="<?php echo $id; ?>" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php
						// 2 ASSO
						add_simple_tag($level . ' ASSO @');
						// 3 RELA
						add_simple_tag(($level + 1) . ' RELA');
						// 3 NOTE
						add_simple_tag(($level + 1) . ' NOTE');
						// 3 SHARED_NOTE
						add_simple_tag(($level + 1) . ' SHARED_NOTE');
						?>
					</div>
				</div>
			</div>
		</li>
	<?php }

	if ('NOTE' == $tag) { ?>
		<?php $text = ''; ?>
		<li class="accordion-item" data-accordion-item>
			<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Add inline note'); ?></a>
			<div id="newnote" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php add_simple_tag($level . ' NOTE ' . $text); ?>
					</div>
				</div>
			</div>
		</li>
	<?php }

	if ('SHARED_NOTE' == $tag) { ?>
		<?php $text = ''; ?>
		<li class="accordion-item" data-accordion-item>
			<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Add shared note'); ?></a>
			<div id="newshared_note" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php add_simple_tag($level . ' SHARED_NOTE '); ?>
					</div>
				</div>
			</div>
		</li>
	<?php }

	if ('OBJE' == $tag) { ?>
		<li class="accordion-item" data-accordion-item>
			<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Add media object'); ?></a>
			<div id="newobje" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php add_simple_tag($level . ' OBJE'); ?>
					</div>
				</div>
			</div>
		</li>
	<?php }

	if ('RESN' == $tag) { ?>
		<?php $text = ''; ?>
		<li class="accordion-item" data-accordion-item>
			<a href="#" class="accordion-title"><?php echo KT_I18N::translate('Add restriction'); ?></a>
			<div id="newresn" class="accordion-content" data-tab-content>
				<div class="grid-x">
					<div class="cell">
						<?php add_simple_tag($level . ' RESN ' . $text); ?>
					</div>
				</div>
			</div>
		</li>
	<?php }
}

// Add some empty tags to create a new fact
function addSimpleTags($fact)
{
	global $ADVANCED_PLAC_FACTS;

	// For new individuals, these facts default to "Y"
	if ('MARR' == $fact /* || $fact == 'BIRT' */) {
		add_simple_tag("0 {$fact} Y");
	} else {
		add_simple_tag("0 {$fact}");
	}
	add_simple_tag('0 DATE', $fact, KT_Gedcom_Tag::getLabel("{$fact}:DATE"));
	add_simple_tag('0 PLAC', $fact, KT_Gedcom_Tag::getLabel("{$fact}:PLAC"));

	if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_PLAC_FACTS, $match)) {
		foreach ($match[1] as $tag) {
			add_simple_tag("0 {$tag}", $fact, KT_Gedcom_Tag::getLabel("{$fact}:PLAC:{$tag}"));
		}
	}
	add_simple_tag('0 MAP', $fact);
	add_simple_tag('0 LATI', $fact);
	add_simple_tag('0 LONG', $fact);
}

// Assemble the pieces of a newly created record into gedcom
function addNewName()
{
	global $ADVANCED_NAME_FACTS;

	$gedrec = "\n1 NAME " . KT_Filter::post('NAME', KT_REGEX_UNSAFE, '//');

	$tags = ['NPFX', 'GIVN', 'SPFX', 'SURN', 'NSFX'];

	if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_NAME_FACTS, $match)) {
		$tags = array_merge($tags, $match[1]);
	}

	// Paternal and Polish and Lithuanian surname traditions can also create a _MARNM
	$SURNAME_TRADITION = get_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION');
	if ('paternal' == $SURNAME_TRADITION || 'polish' == $SURNAME_TRADITION || 'lithuanian' == $SURNAME_TRADITION) {
		$tags[] = '_MARNM';
	}

	foreach (array_unique($tags) as $tag) {
		$TAG = KT_Filter::post($tag, KT_REGEX_UNSAFE);
		if ($TAG) {
			$gedrec .= "\n2 {$tag} {$TAG}";
		}
	}

	return $gedrec;
}

function addNewSex()
{
	switch (KT_Filter::post('SEX', '[MF]', 'U')) {
		case 'M':
			return "\n1 SEX M";

		case 'F':
			return "\n1 SEX F";

		default:
			return "\n1 SEX U";
	}
}

function addNewFact($fact)
{
	global $tagSOUR, $ADVANCED_PLAC_FACTS;

	$FACT = KT_Filter::post($fact, KT_REGEX_UNSAFE);
	$DATE = KT_Filter::post("{$fact}_DATE", KT_REGEX_UNSAFE);
	$PLAC = KT_Filter::post("{$fact}_PLAC", KT_REGEX_UNSAFE);
	if ($DATE || $PLAC || $FACT && 'Y' != $FACT) {
		if ($FACT && 'Y' != $FACT) {
			$gedrec = "\n1 {$fact} {$FACT}";
		} else {
			$gedrec = "\n1 {$fact}";
		}
		if ($DATE) {
			$gedrec .= "\n2 DATE {$DATE}";
		}
		if ($PLAC) {
			$gedrec .= "\n2 PLAC {$PLAC}";

			if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_PLAC_FACTS, $match)) {
				foreach ($match[1] as $tag) {
					$TAG = KT_Filter::post("{$fact}_{$tag}", KT_REGEX_UNSAFE);
					if ($TAG) {
						$gedrec .= "\n3 {$tag} {$TAG}";
					}
				}
			}
			$LATI = KT_Filter::post("{$fact}_LATI", KT_REGEX_UNSAFE);
			$LONG = KT_Filter::post("{$fact}_LONG", KT_REGEX_UNSAFE);
			if ($LATI || $LONG) {
				$gedrec .= "\n3 MAP\n4 LATI {$LATI}\n4 LONG {$LONG}";
			}
		}
		if (KT_Filter::postBool("SOUR_{$fact}")) {
			return updateSOUR($gedrec, 2);
		}

		return $gedrec;
	}
	if ('Y' == $FACT) {
		if (KT_Filter::postBool("SOUR_{$fact}")) {
			return updateSOUR("\n1 {$fact} Y", 2);
		}

		return "\n1 {$fact} Y";
	}

	return '';
}

/**
 * Add new gedcom lines from interface update arrays
 * The edit_interface and add_simple_tag function produce the following
 * arrays incoming from the $_POST form
 * - $glevels[] - an array of the gedcom level for each line that was edited
 * - $tag[] - an array of the tags for each gedcom line that was edited
 * - $islink[] - an array of 1 or 0 values to tell whether the text is a link element and should be surrounded by @@
 * - $text[] - an array of the text data for each line
 * With these arrays you can recreate the gedcom lines like this
 * <code>$glevel[0].' '.$tag[0].' '.$text[0]</code>
 * There will be an index in each of these arrays for each line of the gedcom
 * fact that is being edited.
 * If the $text[] array is empty for the given line, then it means that the
 * user removed that line during editing or that the line is supposed to be
 * empty (1 DEAT, 1 BIRT) for example.  To know if the line should be removed
 * there is a section of code that looks ahead to the next lines to see if there
 * are sub lines.  For example we don't want to remove the 1 DEAT line if it has
 * a 2 PLAC or 2 DATE line following it.  If there are no sub lines, then the line
 * can be safely removed.
 *
 * @param string $newged        the new gedcom record to add the lines to
 * @param int    $levelOverride Override GEDCOM level specified in $glevels[0]
 *
 * @return string The updated gedcom record
 */
function handle_updates($newged, $levelOverride = "no") {
	 global $glevels, $islink, $tag, $uploaded_files, $text;

	 if ($levelOverride == "no" || count($glevels) == 0) {
		 $levelAdjust = 0;
	 } else {
		 $levelAdjust = $levelOverride - $glevels[0];
	 }

	 // Assume all arrays are the same size.
	 $count = count($glevels);

	 for ($j = 0; $j < $count; $j++) {
		 // Look for empty SOUR reference with non-empty sub-records.
		 // This can happen when the SOUR entry is deleted but its sub-records
		 // were incorrectly left intact.
		 // The sub-records should be deleted.
		 if ($tag[$j] === "SOUR" && ($text[$j] === "@@" || $text[$j] === '')) {
			 $text[$j] = '';
			 $k        = $j + 1;
			 while ($k < $count && $glevels[$k] > $glevels[$j]) {
				 $text[$k] = '';
				 $k++;
			 }
		 }

		 if (trim((string) $text[$j]) != '') {
			 $pass = true;
		 } else {
			 //-- for facts with empty values they must have sub records
			 //-- this section checks if they have sub-records
			 $k    = $j + 1;
			 $pass = false;
			 while ($k < $count && $glevels[$k] > $glevels[$j]) {
				 if ($text[$k] !== '') {
					 if (($tag[$j] !== "OBJE") || ($tag[$k] === "FILE")) {
						 $pass = true;
						 break;
					 }
				 }
				 $k++;
			 }
		 }

		 //-- if the value is not empty or it has sub lines
		 //--- then write the line to the gedcom record
		 //-- we have to let some emtpy text lines pass through... (DEAT, BIRT, etc)
		 if ($pass) {
			 $newline = (int) $glevels[$j] + $levelAdjust . ' ' . $tag[$j];
			 if ($text[$j] !== '') {
				 if ($islink[$j]) {
					 $newline .= ' @' . $text[$j] . '@';
				 } else {
					 $newline .= ' ' . $text[$j];
				 }
			 }
			 $newged .= "\n" . str_replace("\n", "\n" . (1 + substr($newline, 0, 1)) . ' CONT ', $newline);

		 }
	 }

	 return $newged;
 }

/**
* Add new GEDCOM lines from the $xxxSOUR interface update arrays, which
* were produced by the splitSOUR() function.
*
* See the handle_updates() function for details.
*
*/
function updateSOUR($inputRec, $levelOverride="no") {
	global $glevels, $tag, $islink, $text;
	global $glevelsSOUR, $tagSOUR, $islinkSOUR, $textSOUR;
	global $glevelsRest, $tagRest, $islinkRest, $textRest;

	if (count($tagSOUR) == 0) return $inputRec; // No update required

	// Save original interface update arrays before replacing them with the xxxSOUR ones
	$glevelsSave = $glevels;
	$tagSave = $tag;
	$islinkSave = $islink;
	$textSave = $text;

	$glevels = $glevelsSOUR;
	$tag = $tagSOUR;
	$islink = $islinkSOUR;
	$text = $textSOUR;

	$myRecord = handle_updates($inputRec, $levelOverride); // Now do the update

	// Restore the original interface update arrays (just in case ...)
	$glevels = $glevelsSave;
	$tag = $tagSave;
	$islink = $islinkSave;
	$text = $textSave;

	return $myRecord;
}

/**
* Add new GEDCOM lines from the $xxxRest interface update arrays, which
* were produced by the splitSOUR() function.
*
* See the handle_updates() function for details.
*
*/
function updateRest($inputRec, $levelOverride="no") {
	global $glevels, $tag, $islink, $text;
	global $glevelsSOUR, $tagSOUR, $islinkSOUR, $textSOUR;
	global $glevelsRest, $tagRest, $islinkRest, $textRest;

	if (count($tagRest) == 0) return $inputRec; // No update required

	// Save original interface update arrays before replacing them with the xxxRest ones
	$glevelsSave = $glevels;
	$tagSave = $tag;
	$islinkSave = $islink;
	$textSave = $text;

	$glevels = $glevelsRest;
	$tag = $tagRest;
	$islink = $islinkRest;
	$text = $textRest;

	$myRecord = handle_updates($inputRec, $levelOverride); // Now do the update

	// Restore the original interface update arrays (just in case ...)
	$glevels = $glevelsSave;
	$tag = $tagSave;
	$islink = $islinkSave;
	$text = $textSave;

	return $myRecord;
}

/**
 * Link Media ID to Indi, Family, or Source ID.
 *
 * Code was removed from inverselink.php to become a callable function
 *
 * @param string $mediaid  Media ID to be linked
 * @param string $linktoid Indi, Family, or Source ID that the Media ID should link to
 * @param int    $level    Level where the Media Object reference should be created
 * @param bool   $chan     Whether or not to update/add the CHAN record
 *
 * @return bool success or failure
 */
function linkMedia($mediaid, $linktoid, $level = 1, $chan = true)
{
	if (empty($level)) {
		$level = 1;
	}
	if (1 != $level) {
		return false;
	} // Level 2 items get linked elsewhere
	// find Indi, Family, or Source record to link to
	$gedrec = find_gedcom_record($linktoid, KT_GED_ID, true);

	// -- check if we are re-editing an unaccepted link that is not already in the DB
	if ($gedrec && false !== strpos($gedrec, "1 OBJE @{$mediaid}@")) {
		return false;
	}

	if ($gedrec) {
		$newrec = $gedrec . "\n1 OBJE @" . $mediaid . '@';
		replace_gedrec($linktoid, KT_GED_ID, $newrec, $chan);

		return true;
	}
	// Record not found?  Maybe deleted since we started this action?
	return false;
}

/**
 * builds the form for adding new facts.
 *
 * @param string $fact the new fact we are adding
 */
function create_add_form($fact)
{
	global $tags, $FULL_SOURCES, $emptyfacts;

	$tags = [];

	$label = strtolower(KT_Gedcom_Tag::getLabel($fact));
	?>
	<!-- Sub-heading for edit_interface page -->
	<h4>
		<?php echo KT_I18N::translate('Adding new %s data', $label); ?>
	</h4>
	<hr>
	<!-- end heading -->
	<?php

	// handle MARRiage TYPE
	if ('MARR_' == substr($fact, 0, 5)) {
		$tags[0] = 'MARR';
		add_simple_tag('1 MARR');
		insert_missing_subtags($fact);
	} else {
		$tags[0] = $fact;
		if ('_UID' == $fact) {
			$fact .= ' ' . uuid();
		}
		// These new level 1 tags need to be turned into links
		if (in_array($fact, ['ASSO'])) {
			$fact .= ' @';
		}
		if (in_array($fact, $emptyfacts)) {
			add_simple_tag('1 ' . $fact . ' Y');
		} else {
			add_simple_tag('1 ' . $fact);
		}
		insert_missing_subtags($tags[0]);
		// -- handle the special SOURce case for level 1 sources [ 1759246 ]
		if ('SOUR' == $fact) {
			add_simple_tag('2 PAGE');
			add_simple_tag('3 TEXT');
			add_simple_tag('2 OBJE');
			if ($FULL_SOURCES) {
				add_simple_tag('3 DATE', '', KT_Gedcom_Tag::getLabel('DATA:DATE'));
				add_simple_tag('2 QUAY');
			}
		}
	}
}

/**
 * creates the form for editing the fact within the given gedcom record at the
 * given line number.
 *
 * @param string $gedrec     the level 0 gedcom record
 * @param int    $linenum    the line number of the fact to edit within $gedrec
 * @param string $level0type the type of the level 0 gedcom record
 */
function create_edit_form($gedrec, $linenum, $level0type)
{
	global $pid, $tags, $ADVANCED_PLAC_FACTS, $date_and_time;
	global $WORD_WRAPPED_NOTES;
	global $FULL_SOURCES;

	$tags = [];
	$gedlines = explode("\n", $gedrec); // -- find the number of lines in the record
	if (!isset($gedlines[$linenum])) {
		echo '<span class="error">', KT_I18N::translate('An error occurred while creating the Edit form.  Another user may have changed this record since you previously viewed it.'), '<br><br>';
		echo KT_I18N::translate('Please reload the previous page to make sure you are working with the most recent record.'), '</span>';

		return;
	}
	$fields = explode(' ', $gedlines[$linenum]);
	$glevel = $fields[0];
	$level = $glevel;

	if (1 != $level && preg_match('~/@.*/@~i', trim($fields[1]))) {
		echo '<span class="error">', KT_I18N::translate('An error occurred while creating the Edit form.  Another user may have changed this record since you previously viewed it.'), '<br><br>';
		echo KT_I18N::translate('Please reload the previous page to make sure you are working with the most recent record.'), '</span>';

		return;
	}

	$type = trim($fields[1]);
	$level1type = $type;
	$level1typeLabel = KT_Gedcom_Tag::getLabel($level1type);

	if (!in_array($level0type, ['REPO', 'SOUR'])) { ?>
		<!-- Sub-heading for edit_interface page -->
		<h4>
			<?php echo KT_I18N::translate('Editing %s data', $level1typeLabel); ?>
		</h4>
		<hr>
		<!-- end heading -->
	<?php } ?>

	<?php
	if (count($fields) > 2) {
		$ct = preg_match('/@.*@/', $fields[2]);
		$levellink = $ct > 0;
	} else {
		$levellink = false;
	}
	$i = $linenum;
	$inSource = false;
	$levelSource = 0;
	$add_date = true;

	// List of tags we would expect at the next level
	// NB insert_missing_subtags() already takes care of the simple cases
	// where a level 1 tag is missing a level 2 tag.  Here we only need to
	// handle the more complicated cases.
	$expected_subtags = [
		'SOUR' => ['PAGE', 'DATA', 'OBJE'],
		'DATA' => ['TEXT'],
		'PLAC' => ['MAP'],
		'MAP'  => ['LATI', 'LONG'],
	];

	if ($FULL_SOURCES) {
		$expected_subtags['SOUR'][] = 'QUAY';
		$expected_subtags['DATA'][] = 'DATE';
	}

	if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_PLAC_FACTS, $match)) {
		$expected_subtags['PLAC'] = array_merge($match[1], $expected_subtags['PLAC']);
	}

	$stack = [0 => $level0type];

	// Loop on existing tags :
	while (true) {
		// Keep track of our hierarchy, e.g. 1=>BIRT, 2=>PLAC, 3=>FONE
		$stack[(int) $level] = $type;

		// Merge them together, e.g. BIRT:PLAC:FONE
		$label = implode(':', array_slice($stack, 1, $level));

		$text = '';
		for ($j = 2; $j < count($fields); $j++) {
			if ($j > 2) {
				$text .= ' ';
			}
			$text .= $fields[$j];
		}

		$text = rtrim($text);

		while (($i + 1 < count($gedlines)) && (preg_match('/' . ($level + 1) . ' CONT ?(.*)/', $gedlines[$i + 1], $cmatch) > 0)) {
			$text .= "\n" . $cmatch[1];
			$i++;
		}

		if ('SOUR' == $type) {
			$inSource = true;
			$levelSource = $level;
		} elseif ($levelSource >= $level) {
			$inSource = false;
		}

		if ('DATA' != $type && 'CONT' != $type) {
			$tags[] = $type;
			$person = KT_Person::getInstance($pid);
			$subrecord = $level . ' ' . $type . ' ' . $text;

			if ($inSource && 'DATE' === $type) {
				add_simple_tag($subrecord, '', KT_Gedcom_Tag::getLabel($label, $person));
			} elseif (!$inSource && 'DATE' === $type) {
				add_simple_tag($subrecord, $level1type, KT_Gedcom_Tag::getLabel($label, $person));
				if ('2' === $level) {
					// We already have a date - no need to add one.
					$add_date = false;
				}
			} elseif ('STAT' == $type) {
				add_simple_tag($subrecord, $level1type, KT_Gedcom_Tag::getLabel($label, $person));
			} elseif ('REPO' == $level0type) {
				$repo = KT_Repository::getInstance($pid);
				add_simple_tag($subrecord, $level0type, KT_Gedcom_Tag::getLabel($label, $repo));
			} else {
				add_simple_tag($subrecord, $level0type, KT_Gedcom_Tag::getLabel($label, $person));
			}
		}

		// Get a list of tags present at the next level
		$subtags = [];
		for ($ii = $i + 1; isset($gedlines[$ii]) && preg_match('/^\s*(\d+)\s+(\S+)/', $gedlines[$ii], $mm) && $mm[1] > $level; $ii++) {
			if ($mm[1] == $level + 1) {
				$subtags[] = $mm[2];
			}
		}

		// Insert missing tags
		if (!empty($expected_subtags[$type])) {
			foreach ($expected_subtags[$type] as $subtag) {
				if (!in_array($subtag, $subtags)) {
					if (!$inSource || 'DATA' != $subtag) {
						add_simple_tag(($level + 1) . ' ' . $subtag, '', KT_Gedcom_Tag::getLabel("{$label}:{$subtag}"));
					}
					if (!empty($expected_subtags[$subtag])) {
						foreach ($expected_subtags[$subtag] as $subsubtag) {
							add_simple_tag(($level + 2) . ' ' . $subsubtag, '', KT_Gedcom_Tag::getLabel("{$label}:{$subtag}:{$subsubtag}"));
						}
					}
				}
			}
		}

		// Awkward special cases
		if (2 == $level && 'DATE' == $type && in_array($level1type, $date_and_time) && !in_array('TIME', $subtags)) {
			add_simple_tag('3 TIME'); // TIME is NOT a valid 5.5.1 tag
		}
		if (2 == $level && 'STAT' == $type && KT_Gedcom_Code_Temp::isTagLDS($level1type) && !in_array('DATE', $subtags)) {
			add_simple_tag('3 DATE', '', KT_Gedcom_Tag::getLabel('STAT:DATE'));
		}

		$i++;
		if (isset($gedlines[$i])) {
			$fields = explode(' ', $gedlines[$i]);
			$level = $fields[0];
			if (isset($fields[1])) {
				$type = trim($fields[1]);
			} else {
				$level = 0;
			}
		} else {
			$level = 0;
		}
		if ($level <= $glevel) {
			break;
		}
	}

	if ('_PRIM' != $level1type) {
		insert_missing_subtags($level1type, $add_date);
	}

	return $level1type;
}

/**
 * Populates the global $tags array with any missing sub-tags.
 *
 * @param string $level1tag the type of the level 1 gedcom record
 * @param mixed  $add_date
 */
function insert_missing_subtags($level1tag, $add_date = false)
{
	global $tags, $date_and_time, $level2_tags, $ADVANCED_PLAC_FACTS, $ADVANCED_NAME_FACTS;
	global $nondatefacts, $nonplacfacts;

	// handle  MARRiage TYPE
	$type_val = '';
	if ('MARR_' == substr($level1tag, 0, 5)) {
		$type_val = substr($level1tag, 5);
		$level1tag = 'MARR';
	}

	foreach ($level2_tags as $key => $value) {
		if ('DATE' == $key && in_array($level1tag, $nondatefacts) || 'PLAC' == $key && in_array($level1tag, $nonplacfacts)) {
			continue;
		}
		if (in_array($level1tag, $value) && !in_array($key, $tags)) {
			if ('TYPE' == $key) {
				add_simple_tag('2 TYPE ' . $type_val, $level1tag);
			} elseif ('_TODO' === $level1tag && 'DATE' === $key) {
				add_simple_tag('2 ' . $key . ' ' . strtoupper(date('d M Y')), $level1tag);
			} elseif ('_TODO' === $level1tag && '_KT_USER' === $key) {
				add_simple_tag('2 ' . $key . ' ' . KT_USER_NAME, $level1tag);
			} elseif ('TITL' === $level1tag && false !== strstr($ADVANCED_NAME_FACTS, $key)) {
				add_simple_tag('2 ' . $key, $level1tag);
			} elseif ('NAME' === $level1tag && false !== strstr($ADVANCED_NAME_FACTS, $key)) {
				add_simple_tag('2 ' . $key, $level1tag);
			} elseif ('NAME' !== $level1tag) {
				add_simple_tag('2 ' . $key, $level1tag);
			}

			switch ($key) { // Add level 3/4 tags as appropriate
				case 'PLAC':
					if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_PLAC_FACTS, $match)) {
						foreach ($match[1] as $tag) {
							add_simple_tag("3 {$tag}", '', KT_Gedcom_Tag::getLabel("{$level1tag}:PLAC:{$tag}"));
						}
					}
					add_simple_tag('3 MAP');
					add_simple_tag('4 LATI');
					add_simple_tag('4 LONG');

					break;

				case 'FILE':
					add_simple_tag('3 FORM');

					break;

				case 'EVEN':
					add_simple_tag('3 DATE');
					add_simple_tag('3 PLAC');

					break;

				case 'STAT':
					if (KT_Gedcom_Code_Temp::isTagLDS($level1tag)) {
						add_simple_tag('3 DATE', '', KT_Gedcom_Tag::getLabel('STAT:DATE'));
					}

					break;

				case 'DATE':
					if (in_array($level1tag, $date_and_time)) {
						add_simple_tag('3 TIME');
					} // TIME is NOT a valid 5.5.1 tag

					break;

				case 'HUSB':
				case 'WIFE':
					add_simple_tag('3 AGE');

					break;

				case 'FAMC':
					if ('ADOP' == $level1tag) {
						add_simple_tag('3 ADOP BOTH');
					}

					break;
			}
		} elseif ('DATE' == $key && $add_date) {
			add_simple_tag('2 DATE', $level1tag, KT_Gedcom_Tag::getLabel("{$level1tag}:DATE"));
		}
	}
	// Do something (anything!) with unrecognised custom tags
	if ('_' == substr($level1tag, 0, 1) && '_UID' != $level1tag && '_TODO' != $level1tag) {
		foreach (['DATE', 'PLAC', 'ADDR', 'AGNC', 'TYPE', 'AGE'] as $tag) {
			if (!in_array($tag, $tags)) {
				add_simple_tag("2 {$tag}");
				if ('PLAC' == $tag) {
					if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $ADVANCED_PLAC_FACTS, $match)) {
						foreach ($match[1] as $tag) {
							add_simple_tag("3 {$tag}", '', KT_Gedcom_Tag::getLabel("{$level1tag}:PLAC:{$tag}"));
						}
					}
					add_simple_tag('3 MAP');
					add_simple_tag('4 LATI');
					add_simple_tag('4 LONG');
				}
			}
		}
	}
}

/**
 * Checkboxes to apply '1 SOUR' to BIRT/MARR/DEAT as '2 SOUR'
 *
 * @param string $fact
 * @param string $value
 *
**/
function sourceLinks($bdm)
{

	$PREFER_LEVEL2_SOURCES   = get_gedcom_setting(KT_GED_ID, 'PREFER_LEVEL2_SOURCES');
	$QUICK_REQUIRED_FACTS    = get_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FACTS');
	$QUICK_REQUIRED_FAMFACTS = get_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FAMFACTS');


	if ($PREFER_LEVEL2_SOURCES === '0') {
		$level1_checked = '';
		$level2_checked = '';
	} else if ($PREFER_LEVEL2_SOURCES === '1' || $PREFER_LEVEL2_SOURCES === true) {
		$level1_checked = '';
		$level2_checked = ' checked';
	} else {
		$level1_checked = ' checked';
		$level2_checked = '';

	}

	if ($bdm && strpos($bdm, 'B') !== false) { ?>
		<p>
			<input type="checkbox" name="SOUR_INDI" <?php echo $level1_checked; ?> value="Y">
			<label><?php echo KT_I18N::translate('Individual'); ?></label>
		</p>
		<?php if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FACTS, $matches)) {
			foreach ($matches[1] as $match) {
				if (!in_array($match, explode('|', KT_EVENTS_DEAT))) { ?>
					<p>
						<input type="checkbox" name="SOUR_<?php echo $match; ?>" <?php echo $level2_checked; ?> value="Y">
						<label><?php echo KT_Gedcom_Tag::getLabel($match); ?></label>
					</p>
				<?php }
			}
		}
	}

	if ($bdm && strpos($bdm, 'D') !== false) {
		if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FACTS, $matches)) {
			foreach ($matches[1] as $match) {
				if (in_array($match, explode('|', KT_EVENTS_DEAT))) { ?>
					<p>
						<input type="checkbox" name="SOUR_<?php echo $match; ?>" <?php echo $level2_checked; ?> value="Y">
						<label><?php echo KT_Gedcom_Tag::getLabel($match); ?></label>
					</p>
				<?php }
			}
		}
	}

	if ($bdm && strpos($bdm, 'M') !== false) { ?>
		<p>
			<input type="checkbox" name="SOUR_FAM" <?php echo $level1_checked; ?> value="Y">
			<label><?php echo KT_I18N::translate('Family'); ?></label>
		</p>
		<?php if (preg_match_all('/(' . KT_REGEX_TAG . ')/', $QUICK_REQUIRED_FAMFACTS, $matches)) {
			foreach ($matches[1] as $match) { ?>
				<p>
					<input type="checkbox" name="SOUR_<?php echo $match; ?>" <?php echo $level2_checked; ?> value="Y">
					<label><?php echo KT_Gedcom_Tag::getLabel($match); ?></label>
				</p>
			<?php }
		}
	}

}
