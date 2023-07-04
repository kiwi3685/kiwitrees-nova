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

/**
 * print the information for an individual chart box.
 *
 * find and print a given individuals information for a pedigree chart
 *
 * @param string $pid         the Gedcom Xref ID of the   to print
 * @param int    $style       the style to print the box in, 1 for smaller boxes, 2 for larger boxes, 3 for vertical template
 * @param int    $count       on some charts it is important to keep a count of how many boxes were printed
 * @param mixed  $person
 * @param mixed  $personcount
 * @param mixed  $favNote
 */
function print_pedigree_person($person, $style = 1, $count = 0, $personcount = '1', $favNote = '')
{
	global $HIDE_LIVE_PEOPLE, $SHOW_LIVING_NAMES, $GEDCOM;
	global $SHOW_HIGHLIGHT_IMAGES, $bwidth, $bheight, $PEDIGREE_FULL_DETAILS, $SHOW_PEDIGREE_PLACES;
	global $TEXT_DIRECTION, $DEFAULT_PEDIGREE_GENERATIONS, $OLD_PGENS, $talloffset, $PEDIGREE_LAYOUT;
	global $ABBREVIATE_CHART_LABELS;
	global $chart_style, $box_width, $generations, $show_spouse, $show_full, $iconStyle;
	global $CHART_BOX_TAGS, $SHOW_LDS_AT_GLANCE, $PEDIGREE_SHOW_GENDER;
	global $SEARCH_SPIDER;

	$pid = '';

	if (empty($show_full)) {
		$show_full = 0;
	}
	if (3 == $style || 4 == $style) {
		$show_full = 1;
	}
	if (empty($PEDIGREE_FULL_DETAILS)) {
		$PEDIGREE_FULL_DETAILS = 0;
	}

	if (!isset($OLD_PGENS)) {
		$OLD_PGENS = $DEFAULT_PEDIGREE_GENERATIONS;
	}
	if (!isset($talloffset)) {
		$talloffset = $PEDIGREE_LAYOUT;
	}

	// NOTE: format for missing ancestor
	if ($person) {
		$missing = true;

		$pid = $person->getXref();

		if (0 == $count) {
			$count = rand();
		}

		$lbwidth = $bwidth * .75;
		if ($lbwidth < 150) {
			$lbwidth = 150;
		}

		$tmp           = ['M' => 'M', 'F' => 'F', 'U' => 'U'];
		$isF           = $tmp[$person->getSex()];
		$icons         = '';
		$classfacts    = '';
		$genderImage   = '';
		$BirthDeath    = '';
		$birthplace    = '';
		$deathplace    = '';
		$outBoxAdd     = '';
		$showid        = '';
		$personlinks   = '';
		$iconsStyleAdd = 'float:right;';

		if ('rtl' == $TEXT_DIRECTION) {
			$iconsStyleAdd = 'float:left;';
		}

		$disp = $person->canDisplayDetails();
		$uniqueID = (int) (microtime(true) * 1000000);
		$boxID = $pid . '.' . $personcount . '.' . $count . '.' . $uniqueID;
		$dataToggle = $pid . '-' . $uniqueID;
		$mouseAction4 = ' onclick="expandbox(\'' . $boxID . '\', ' . $style . '); return false;"';
		$displayNote = $favNote;

		if ($person->canDisplayName()) {
			if (empty($SEARCH_SPIDER)) {
				$personlinks = getPersonLinks($person);
			} else {
				if (1 == $style) {
					$outBoxAdd .= 'person_box_template' . $isF . '" style="width: ' . $bwidth . 'px; height: ' . $bheight . 'px; overflow: hidden;"';
				} else {
					$outBoxAdd .= 'person_box_template' . $isF . '" style="overflow: hidden;"';
				}
				// NOTE: Zoom
				if (!$SEARCH_SPIDER) {
					$outBoxAdd .= $mouseAction4;
				}
			}
		} else {
			if (1 == $style) {
				$outBoxAdd .= 'person_box_template' . $isF . 'style1" style="width: ' . $bwidth . 'px; height: ' . $bheight . 'px;"';
			} elseif (3 == $style) {
				$outBoxAdd .= 'vertical_box_template' . $isF . 'style3"';
			} else {
				$outBoxAdd .= 'person_box_template' . $isF . 'style0"';
			}
		}
		// -- find the name
		$name = $person->getFullName(); // standard display of full name
		$shortname = $person->getShortName(); // abbreviated version of name for small spaces
		$addname = $person->getAddName(); // -- find additional name, e.g. Hebrew

		if ($SHOW_HIGHLIGHT_IMAGES) {
			$thumbnail = $person->displayImage();
		} else {
			$thumbnail = '';
		}

		// add optional CSS style for each fact
		$indirec = $person->getGedcomRecord();
		$cssfacts = ['BIRT', 'CHR', 'DEAT', 'BURI', 'CREM', 'ADOP', 'BAPM', 'BARM', 'BASM', 'BLES', 'CHRA', 'CONF', 'FCOM', 'ORDN', 'NATU', 'EMIG', 'IMMI', 'CENS', 'PROB', 'WILL', 'GRAD', 'RETI', 'CAST', 'DSCR', 'EDUC', 'IDNO',
			'NATI', 'NCHI', 'NMR', 'OCCU', 'PROP', 'RELI', 'RESI', 'SSN', 'TITL', 'BAPL', 'CONL', 'ENDL', 'SLGC', '_MILI', ];
		foreach ($cssfacts as $indexval => $fact) {
			if (false !== strpos($indirec, "1 {$fact}")) {
				$classfacts .= " {$fact}";
			}
		}

		if ($PEDIGREE_SHOW_GENDER && $show_full) {
			$genderImage = ' ' . $person->getSexImage('small', "box-{$boxID}-gender");
		}

		// Here for alternate name2
		if ($addname) {
			$addname = '<br><span id="addnamedef-' . $boxID . '" class="name1">' . $addname . '</span>';
		}

		if ($SHOW_LDS_AT_GLANCE && $show_full) {
			$addname = ' <span class="details$style">' . get_lds_glance($indirec) . '</span>' . $addname;
		}

		// Show BIRT or equivalent event
		$opt_tags = preg_split('/\W/', $CHART_BOX_TAGS, 0, PREG_SPLIT_NO_EMPTY);
		if ($show_full) {
			foreach (explode('|', KT_EVENTS_BIRT) as $birttag) {
				if (!in_array($birttag, $opt_tags)) {
					$event = $person->getFactByType($birttag);
					if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace()) && $event->canShow()) {
						$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';

						break;
					}
				}
			}
		}
		// Show optional events (before death)
		foreach ($opt_tags as $key => $tag) {
			if (!preg_match('/^(' . KT_EVENTS_DEAT . ')$/', $tag)) {
				$event = $person->getFactByType($tag);
				if (!is_null($event) && $event->canShow()) {
					$BirthDeath .= '<p>' . $event->print_simple_fact(true, true);
					unset($opt_tags[$key]);
				}
			}
		}
		// Find the short death place
		$opt_tags = preg_split('/\W/', $CHART_BOX_TAGS, 0, PREG_SPLIT_NO_EMPTY);
		if (!in_array('DEAT', $opt_tags)) {
			$event = $person->getFactByType('DEAT');
			if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace()) && $event->canShow()) {
				$tmp = new KT_Place($event->getPlace(), KT_GED_ID);
				$deathplace = $tmp->getShortName();
			}
		}

		// Show DEAT or equivalent event
		if ($show_full) {
			foreach (explode('|', KT_EVENTS_DEAT) as $deattag) {
				$event = $person->getFactByType($deattag);
				if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace() || 'Y' == $event->getDetail()) && $event->canShow()) {
					$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';
					if (in_array($deattag, $opt_tags)) {
						unset($opt_tags[array_search($deattag, $opt_tags)]);
					}

					break;
				}
			}
		}
		// Show remaining optional events (after death)
		foreach ($opt_tags as $tag) {
			$event = $person->getFactByType($tag);
			if (!is_null($event) && $event->canShow()) {
				$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';
			}
		}
		// Find the short birth place
		$opt_tags = preg_split('/\W/', $CHART_BOX_TAGS, 0, PREG_SPLIT_NO_EMPTY);
		foreach (explode('|', KT_EVENTS_BIRT) as $birttag) {
			if (!in_array($birttag, $opt_tags)) {
				$event = $person->getFactByType($birttag);
				if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace()) && $event->canShow()) {
					$tmp = new KT_Place($event->getPlace(), KT_GED_ID);
					$birthplace .= $tmp->getShortName();

					break;
				}
			}
		}
	} else {
		$missing = false;
	}

	// Create detailed view
	if ($pid) {
		$detailedView = detailedView($pid);
	}

	// Output to template
	switch ($style) {
		case '1':
			require KT_THEME_DIR . 'templates/compactbox_template.php';

			break;

		case '2':
		default:
			require KT_THEME_DIR . 'templates/personbox_template.php';

			break;

		case '3':
			require KT_THEME_DIR . 'templates/verticalbox_template.php';

			break;

		case '4':
			require KT_THEME_DIR . 'templates/person_card_template.php';

			break;
	}
}

/**
 * Get detailed gedcom tags for an individual.
 *
 * Used as popup box on charts
 *
 * @param mixed $pid
 */
function detailedView($pid)
{
	$person = KT_Person::getInstance($pid);

	if (!$person || !$person->canDisplayDetails()) {
		return KT_I18N::translate('Private');
	}

	$person->add_family_facts(false);
	$events = $person->getIndiFacts();
	sort_facts($events);

	$content = '';

	foreach ($events as $event) {
		if ($event->canShow()) {
			switch ($event->getTag()) {
				case 'SEX':
				case 'FAMS':
				case 'FAMC':
				case 'NAME':
				case 'TITL':
				case 'NOTE':
				case 'SOUR':
				case 'SSN':
				case 'OBJE':
				case 'HUSB':
				case 'WIFE':
				case 'CHIL':
				case 'ALIA':
				case 'ADDR':
				case 'PHON':
				case 'SUBM':
				case '_EMAIL':
				case 'CHAN':
				case 'URL':
				case 'EMAIL':
				case 'WWW':
				case 'RESI':
				case 'RESN':
				case '_UID':
				case '_TODO':
				case '_KT_OBJE_SORT':
					// Do not show these
					break;

				case 'ASSO':
					// Associates
					$content .= '
					<div>
						<span class="details_label">' . $event->getLabel() . '</span>' .
							print_asso_rela_record($event, $person) . '
					</div>';

					break;

				default:
					// Simple version of print_fact()
					$content .= '
					<div>
						<span class="details_label">' . $event->getLabel() . '</span> ';
					$details = $event->getDetail();
					if ('Y' != $details && 'N' != $details) {
						$content .= '<span dir="auto">' . $details . '</span>';
					}
					$content .= format_fact_date($event, $person, false, false);
					// Show spouse/family for family events
					$spouse = $event->getSpouse();
					if ($spouse) {
						$content .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a> - ';
					}
					if ($event->getParentObject() instanceof KT_Family) {
						$content .= '<a href="' . $event->getParentObject()->getHtmlUrl() . '">' .
							KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family') . ' -
							</a>';
					}
					$content .= ' ' . format_fact_place($event, true, true);
					$content .= '</div>';

					break;
			}
		}
	}

	return $content;
}

/**
 * Print HTML header meta links.
 *
 * Adds meta tags to header common to all themes
 *
 * @param mixed $META_DESCRIPTION
 * @param mixed $META_ROBOTS
 * @param mixed $META_GENERATOR
 * @param mixed $LINK_CANONICAL
 */
function header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL)
{
	global $KT_TREE, $view;
	$header_links = '';
	if (!empty($LINK_CANONICAL)) {
		$header_links .= '<link rel="canonical" href="' . $LINK_CANONICAL . '">';
	}
	if (!empty($META_DESCRIPTION)) {
		global $controller, $ctype;

		switch ($ctype) {
			case '':
				if ('simple' != $view) {
					if ($KT_TREE) {
						$header_links .= '<meta name="description" content="' . htmlspecialchars(strip_tags($controller->getPageTitle() . ' - ' . $KT_TREE->tree_title_html)) . '">';
					} else {
						$header_links .= '<meta name="description" content="' . htmlspecialchars(strip_tags($controller->getPageTitle())) . '">';
					}
				}

				break;

			case 'gedcom':
			default:
				$header_links .= '<meta name="description" content="' . htmlspecialchars((string) $META_DESCRIPTION) . '">';

				break;
		}
	}
	if (!empty($META_ROBOTS)) {
		$header_links .= '<meta name="robots" content="' . $META_ROBOTS . '">';
	}
	if (!empty($META_GENERATOR)) {
		$header_links .= '<meta name="generator" content="' . $META_GENERATOR . '">';
	}
	$header_links .= '<meta name="viewport" content="width=device-width, initial-scale=1">';

	return $header_links;
}

// Generate a login link
function login_link($mobile = false)
{
	global $SEARCH_SPIDER, $iconStyle;

	if ($SEARCH_SPIDER) {
		return '';
	}

	if ($mobile) {
		return
			'<a href="' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()) . '" >
				<i class="' . $iconStyle . ' fa-lock"></i>
			</a>';
	} else {
		return
			'<a href="' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()) . '">'
				. (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login')) . '
			</a>';
	}
}

// Generate a logout link
function logout_link($icon = true, $mobile = false)
{
	global $SEARCH_SPIDER, $iconStyle;

	if ($SEARCH_SPIDER) {
		return '';
	}
	$icon ? $icon = '<i class="' . $iconStyle . ' fa-lock-open"></i>' : $icon = '';

	if ($mobile == 'mobile') {
		return '
			<li>
				<a href="index.php?logout=1">' .
					$icon . '
				</a>
			</li>
		';
	} else {
		return '
			<a href="index.php?logout=1">' .
				$icon . KT_I18N::translate('Logout') . '
			</a>
		';
	}



}

// generate Who is online list
function whoisonline()
{
	$NumAnonymous = 0;
	$loggedusers = [];
	$content = '';

	foreach (get_logged_in_users() as $user_id => $user_name) {
		if (KT_USER_IS_ADMIN || get_user_setting($user_id, 'visibleonline')) {
			$loggedusers[$user_id] = $user_name;
		} else {
			$NumAnonymous++;
		}
	}

	$LoginUsers = count($loggedusers);
	$content .= '<div class="logged_in_count">';
	if ($NumAnonymous) {
		$content .= KT_I18N::plural('%d anonymous logged-in user', '%d anonymous logged-in users', $NumAnonymous, $NumAnonymous);
		if ($LoginUsers) {
			$content .= '&nbsp;|&nbsp;';
		}
	}
	if ($LoginUsers) {
		$content .= KT_I18N::plural('%d logged-in user', '%d logged-in users', $LoginUsers, $LoginUsers);
	}
	$content .= '</div>';
	$content .= '<div class="logged_in_list">';
	if (KT_USER_ID) {
		$i = 0;
		foreach ($loggedusers as $user_id => $user_name) {
			$content .= '<div class="logged_in_name">';

			$individual = KT_Person::getInstance(KT_USER_GEDCOM_ID);
			if ($individual) {
				$content .= '<a href="individual.php?pid=' . KT_USER_GEDCOM_ID . '&amp;ged=' . KT_GEDURL . '">' . htmlspecialchars(getUserFullName($user_id)) . '</a>';
			} else {
				$content .= htmlspecialchars(getUserFullName($user_id));
			}
			$content .= ' - ' . htmlspecialchars((string) $user_name);

			if (KT_USER_ID != $user_id && 'none' != get_user_setting($user_id, 'contactmethod')) {
				$content .= '<a class="fa-envelope-o" href="message.php?to=' . $user_name . '&amp;url=' . addslashes(urlencode(get_query_url())) . '"  title="' . KT_I18N::translate('Send Message') . '"></a>';
			}

			$i++;

			$content .= '</div>';
		}
	}
	$content .= '</div>';

	return $content;
}

// Print a link to allow email/messaging contact with a user
// Optionally specify a method (used for webmaster/genealogy contacts)
function user_contact_link($user_id)
{
	global $iconStyle;
	$method = get_user_setting($user_id, 'contactmethod');

	switch ($method) {
		case 'none':
			return '';

		case 'mailto':
			return '<a href="mailto:' . KT_Filter::escapeHtml(getUserEmail($user_id)) . '"><i class="' . $iconStyle . ' fa-envelope"></i>' . getUserFullName($user_id) . '</a>';

		default:
			return '<a href="#" onclick="window.open(\'message.php?to=' . KT_Filter::escapeHtml(get_user_name($user_id)) . '&amp;url=' . addslashes(urlencode(get_query_url())) . '\', \'_blank\')" rel="noopener noreferrer" title="' . KT_I18N::translate('Send Message') . '">' . getUserFullName($user_id) . '<i class="' . $iconStyle . ' fa-envelope"></i></a>';
	}
}

// print links for genealogy and technical contacts
//
// this function will print appropriate links based on the preferred contact methods for the genealogy
// contact user and the technical support contact user
function contact_links($ged_id = KT_GED_ID)
{
	$contact_user_id = get_gedcom_setting($ged_id, 'CONTACT_USER_ID');
	$webmaster_user_id = get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID');
	$supportLink = user_contact_link($webmaster_user_id);
	if ($webmaster_user_id == $contact_user_id) {
		$contactLink = $supportLink;
	} else {
		$contactLink = user_contact_link($contact_user_id);
	}

	if (!$contact_user_id && !$webmaster_user_id) {
		return '';
	}

	if (!$supportLink && !$contactLink) {
		return '';
	}

	if ($supportLink == $contactLink) {
		return '<div class="contact_links">' . $supportLink . '</div>';
	}
	if ($webmaster_user_id || $contact_user_id) {
		$returnText = '<div class="contact_links">';
		if ($supportLink && $webmaster_user_id) {
			$returnText .= KT_I18N::translate('For technical support and information contact') . ' ' . $supportLink;
			if ($contactLink) {
				$returnText .= '<br>';
			}
		}
		if ($contactLink && $contact_user_id) {
			$returnText .= KT_I18N::translate('For help with genealogy questions contact') . ' ' . $contactLink;
		}
		$returnText .= '</div>';

		return $returnText;
	}

	return '';
}

/**
 * print a note record.
 *
 * @param string $text
 * @param int    $nlevel   the level of the note record
 * @param string $nrec     the note record to print
 * @param bool   $textOnly Don't print the "Note: " introduction
 *
 * @return string
 */
function print_note_record($text, $nlevel, $nrec, $textOnly = false)
{
	global $KT_TREE, $EXPAND_NOTES, $iconStyle;
	$element_id = '';
	$first_line = '';
	$text_cont = get_cont($nlevel, $nrec);
	$revealText = '';
	$noteType = '';

	// Check if shared note (we have already checked that it exists)
	preg_match('/^0 @(' . KT_REGEX_XREF . ')@ NOTE/', $nrec, $match);
	if ($match) {
		$element_id = $match[1] . '-' . (int) (microtime(true) * 1000000);
		$note = KT_Note::getInstance($match[1], $KT_TREE);
		$label = 'SHARED_NOTE';
		// If Census assistant installed, allow it to format the note
		if (array_key_exists('census_assistant', KT_Module::getActiveModules())) {
			$html = census_assistant_KT_Module::formatCensusNote($note);
		} else {
			$html = KT_Filter::formatText($note->getNote());
		}
	} else {
		$element_id = 'N-' . (int) (microtime(true) * 1000000);
		$note = null;
		$label = 'NOTE';
		$html = KT_Filter::formatText($text . $text_cont);
	}

	if ($textOnly) {
		return strip_tags($html);
	}

	if (false === strpos($text . $text_cont, "\n")) {
		// A one-line note? strip the block-level tags, so it displays inline
		return KT_Gedcom_Tag::getLabelValue($label, strip_tags($html, '<a><strong><em>'));
	}
	// A multi-line note, with an expand/collapse option
	if ($note) {
		if (KT_SCRIPT_NAME === 'note.php') {
			$first_line = $note->getFullName();
		} else {
			KT_USER_CAN_EDIT ? $editIcon = '<i class="' . $iconStyle . ' fa-pen-to-square"></i>' : $editIcon = '';
			$first_line = '
					<a href="' . $note->getHtmlUrl() . '">' .
					$note->getFullName() .
					$editIcon . '
					</a>';
			$revealText = $note->getFullName();
		}

		// special case required to display title for census shared notes when is-shown by default
		if (preg_match('/<span id="title">.*<\/span>/', $html, $match)) {
			if (KT_SCRIPT_NAME === 'note.php') {
				$first_line = $match[0];
			} else {
				$first_line = '
						<a href="' . $note->getHtmlUrl() . '">' .
						$match[0] . '
						</a>';
			}
			$html = preg_replace('/<span id="title">.*<\/span>/', '', $html);
		}
	} else {
		$noteType = 'standard_expandable';
		if (strlen($text) > 100) {
			$first_line = mb_substr($text, 0, 100) . KT_I18N::translate('…');
		} else {
			$first_line = KT_Filter::formatText($text);
			$html = KT_Filter::formatText($text_cont);
		}
	}

	if (KT_SCRIPT_NAME === 'note.php') {
		$noteDisplay = '
				<div class="fact_NOTE">
					<span>
						' . KT_Gedcom_Tag::getLabel($label) . ':
					</span>
					<span id="' . $element_id . '">' .
					$first_line . '
					</span>
					<div id="' . $element_id . '">
					  ' . $html . '
					</div>
				</div>
			';
	} else {
		if ('standard_expandable' === $noteType) {
			// togle display
			$noteDisplay = '
					<div class="fact_NOTE standard_expandable">
						<span>
							' . KT_Gedcom_Tag::getLabel($label) . ':
							<a data-toggle="' . $element_id . '">
								<i class="' . $iconStyle . ' fa-maximize"></i>
							</a>
							<span class="first-line">
								' . $first_line . '
							</span>
						</span>
						<div id="' . $element_id . '" class="first-line is-shown" data-toggler=".is-shown">
							' . $html . '
						</div>
					</div>
					<br>
				';
		} else {
			// reveal in modal
			$noteDisplay = '
					<div class="fact_NOTE modal">
						<span>
							' . KT_Gedcom_Tag::getLabel($label) . ':
						</span>
						<a data-open="' . $element_id . '">
							' . $revealText . '
							<i class="' . $iconStyle . ' fa-maximize"></i>
						</a>
						<div class="reveal" id="' . $element_id . '" data-reveal>
							' . $first_line . '<br>' . $html . '
							<button class="close-button" data-close aria-label="' . KT_I18N::translate('Close') . '" type="button">
								<span aria-hidden="true">
									<i class="' . $iconStyle . ' fa-xmark"></i>
								</span>
							</button>
						</div>
					</div>
				';
		}
	}

	return $noteDisplay;
}

/**
 * Print all of the notes in this fact record.
 *
 * @param string $factrec  the factrecord to print the notes from
 * @param int    $level    The level of the factrecord
 * @param bool   $textOnly Don't print the "Note: " introduction
 * @param mixed  $return
 */
function print_fact_notes($factrec, $level, $textOnly = false, $return = false)
{
	global $KT_TREE;

	$data = '';
	$previous_spos = 0;
	$nlevel = $level + 1;
	$ct = preg_match_all("/{$level} NOTE (.*)/", $factrec, $match, PREG_SET_ORDER);
	for ($j = 0; $j < $ct; $j++) {
		$spos1 = strpos($factrec, $match[$j][0], $previous_spos);
		$spos2 = strpos($factrec . "\n{$level}", "\n{$level}", $spos1 + 1);
		if (!$spos2) {
			$spos2 = strlen($factrec);
		}
		$previous_spos = $spos2;
		$nrec = substr($factrec, $spos1, $spos2 - $spos1);
		if (!isset($match[$j][1])) {
			$match[$j][1] = '';
		}
		if (!preg_match('/@(.*)@/', $match[$j][1], $nmatch)) {
			$data .= print_note_record($match[$j][1], $nlevel, $nrec, $textOnly);
		} else {
			$note = KT_Note::getInstance($nmatch[1], $KT_TREE);
			if ($note) {
				if ($note->canDisplayDetails()) {
					$noterec = $note->getGedcomRecord();
					$nt = preg_match("/0 @{$nmatch[1]}@ NOTE (.*)/", $noterec, $n1match);
					$data .= print_note_record(($nt > 0) ? $n1match[1] : '', 1, $noterec, $textOnly);
					if (!$textOnly) {
						if (false !== strpos($noterec, '1 SOUR')) {
							$data .= print_fact_sources($noterec, 1);
						}
					}
				}
			} else {
				$data = '<div class="fact_NOTE">
					<span>' . KT_I18N::translate('Note') . '</span>:
					<span class="field error">' . $nmatch[1] . '</span>
				</div>';
			}
		}

		if (!$textOnly) {
			if (false !== strpos($factrec, "{$nlevel} SOUR")) {
				$data .= '
					<div class="indent">' .
						print_fact_sources($nrec, $nlevel, true) . '
					</div>
				';
			}
		}
	}

	if ($return) {
		return $data;
	}
	echo $data;
}

// -- function to print a privacy error with contact method
function print_privacy_error()
{
	$user_id = get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
	$method = get_user_setting($user_id, 'contactmethod');
	$fullname = getUserFullName($user_id);

	echo '<div class="error">' . KT_I18N::translate('This information is private and cannot be shown.') . '</div>';

	switch ($method) {
		case 'none':
			break;

		case 'mailto':
			$email = getUserEmail($user_id);
			echo '<div class="error">' . KT_I18N::translate('For more information contact') . ' <a href="mailto:' . htmlspecialchars((string) $email) . '">' . htmlspecialchars((string) $fullname) . '</a></div>';

			break;

		default:
			echo '<div class="error">' . KT_I18N::translate('For more information contact') . ' <a class="fa-envelope-o" href="message.php?to=' . $user_id . '&amp;url=' . addslashes(urlencode(get_query_url())) . '"  title="' . KT_I18N::translate('Send Message') . '">' . $fullname . '</a></div>';

			break;
	}
}

// Print a link for a popup help window
function help_link($help_topic, $module = '')
{
	return '<span class="icon-help" onclick="helpDialog(\'' . $help_topic . '\',\'' . $module . '\'); return false;">&nbsp;</span>';
}

// Print help as a foundation dropdown
function helpDropdown($help_topic, $module = false)
{
	global $controller, $iconStyle;
	if ($module) {
		$controller->addInlineJavascript('
			jQuery("#help-' . $help_topic . '").load("help_text.php?help=' . $help_topic . '&title=true&mod=' . $module . '");
		');
	} else {
		$controller->addInlineJavascript('
			jQuery("#help-' . $help_topic . '").load("help_text.php?help=' . $help_topic . '&title=true");
		');
	}

	return '
		<span class="show-for-medium" data-open="' . $help_topic . '" title="' . KT_I18N::translate('Help') . '"  data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-question-circle alert"></i>
		</span>
		<div class="help-text reveal" id="' . $help_topic . '" data-reveal data-overlay="false">
			<button class="close-button" data-close aria-label="Close modal" type="button">
				<span aria-hidden="true"><i class="' . $iconStyle . ' fa-xmark alert"></i></span>
			</button>
			<div id="help-' . $help_topic . '"></div>
		</div>
	';
}

// Help dropdown in an input group
function helpInputLabel($help_topic, $module = false)
{
	return '
		<span class="input-group-label">' .
			helpDropdown($help_topic, $module) . '
		</span>
	';
}

// When a user has searched for text, highlight any matches in
// the displayed string.
function highlight_search_hits($string)
{
	global $controller;
	if ($controller instanceof KT_Controller_Search && $controller->query) {
		// TODO: when a search contains multiple words, we search independently.
		// e.g. searching for "FOO BAR" will find records containing both FOO and BAR.
		// However, we only highlight the original search string, not the search terms.
		// The controller needs to provide its "query_terms" array.
		$regex = [];
		foreach ([$controller->query] as $search_term) {
			$regex[] = preg_quote($search_term, '/');
		}
		// Match these strings, provided they do not occur inside HTML tags
		$regex = '(' . implode('|', $regex) . ')(?![^<]*>)';

		return preg_replace('/' . $regex . '/i', '<span class="search_hit">$1</span>', $string);
	}

	return $string;
}

// Print the associations from the associated individuals in $event to the individuals in $record
function print_asso_rela_record(KT_Event $event, KT_GedcomRecord $record)
{
	global $SEARCH_SPIDER;

	// To whom is this record an assocate?
	if ($record instanceof KT_Person) {
		// On an individual page, we just show links to the person
		$associates = [$record];
	} elseif ($record instanceof KT_Family) {
		// On a family page, we show links to both spouses
		$associates = $record->getSpouses();
	} else {
		// On other pages, it does not make sense to show associates
		return;
	}

	preg_match_all('/^1 ASSO @(' . KT_REGEX_XREF . ')@((\n[2-9].*)*)/', $event->getGedcomRecord(), $amatches1, PREG_SET_ORDER);
	preg_match_all('/\n2 _?ASSO @(' . KT_REGEX_XREF . ')@((\n[3-9].*)*)/', $event->getGedcomRecord(), $amatches2, PREG_SET_ORDER);
	// For each ASSO record
	foreach (array_merge($amatches1, $amatches2) as $amatch) {
		$person = KT_Person::getInstance($amatch[1]);
		if (!$person) {
			// If the target of the ASSO does not exist, create a dummy person, so
			// the user can see that something is present.
			$person = new KT_Person('');
		}
		if (preg_match('/\n[23] RELA (.+)/', $amatch[2], $rmatch)) {
			$rela = $rmatch[1];
		} else {
			$rela = '';
		}
		if (preg_match('/\n[23] NOTE (.+)/', $amatch[2], $nmatch)) {
			$label_3 = KT_I18N::translate('Note');
			$note = $nmatch[1];
			if (false !== strpos($note, '@') && false !== strrpos($note, '@')) {
				$label_3 = KT_I18N::translate('Shared note');
				$nid = substr($note, 1, -1);
				$snote = KT_Note::getInstance($nid);
				if ($snote) {
					$noterec = $snote->getGedcomRecord();
					$nt = preg_match('/^0 @[^@]+@ NOTE (.*)/', $noterec, $n1match);
					$line1 = $n1match[1];
					$text = get_cont(1, $noterec);
					// If Census assistant installed,
					if (array_key_exists('census_assistant', KT_Module::getActiveModules())) {
						$note = census_assistant_KT_Module::formatCensusNote($note);
					} else {
						$note = KT_Filter::formatText($note->getNote(), $KT_TREE);
					}
				} else {
					$note = '<span class="error">' . htmlspecialchars((string) $nid) . '</span>';
				}
			}
		} else {
			$note = '';
		}
		$html = [];
		foreach ($associates as $associate) {
			if ($associate) {
				if ($rela) {
					$label = '<span class="rela_type">' . KT_Gedcom_Code_Rela::getValue($rela, $person) . ':&nbsp;</span>';
					$label_2 = '<span class="rela_name">' . get_relationship_name(get_relationship($associate, $person, true, 4)) . '</span>';
				} else {
					// Generate an automatic RELA
					$label = '';
					$label_2 = '<span class="rela_name">' . get_relationship_name(get_relationship($associate, $person, true, 4)) . '</span>';
				}
				if (!$label && !$label_2) {
					$label = KT_I18N::translate('Relationships');
					$label_2 = '';
				}
				// For family records (e.g. MARR), identify the spouse with a sex icon
				if ($record instanceof KT_Family) {
					$label_2 = $associate->getSexImage() . $label_2;
				}

				if ($SEARCH_SPIDER) {
					$html[] = $label_2; // Search engines cannot use the relationship chart.
				} else {
					$html[] = '<a href="relationship.php?pid1=' . $associate->getXref() . '&amp;pid2=' . $person->getXref() . '&amp;ged=' . KT_GEDURL . '">' . $label_2 . '</a>';
				}
			}
		}
		$html = array_unique($html);
		?>
		<div class="fact_ASSO">
			<?php echo $label . implode(KT_I18N::$list_separator, $html); ?>
			 -
			<a href="<?php echo $person->getHtmlUrl(); ?>">
				<?php echo $person->getFullName(); ?>
			</a>
			<!-- find notes for each fact -->
			<?php if ($note) { ?>
				<div class="indent">
					<span><?php echo $label_3; ?>:</span>
					<span><?php echo $note; ?></span>
				</div>
			<?php } ?>
		</div>
	<?php }
	}

/**
 * Format age of parents in HTML.
 *
 * @param string     $pid        child ID
 * @param null|mixed $birth_date
 */
function format_parents_age($pid, $birth_date = null)
{
	global $SHOW_PARENTS_AGE;

	$html = '';
	if ($SHOW_PARENTS_AGE) {
		$person = KT_Person::getInstance($pid);
		$families = $person->getChildFamilies();
		// Where an indi has multiple birth records, we need to know the
		// date of it.  For person boxes, etc., use the default birth date.
		if (is_null($birth_date)) {
			$birth_date = $person->getBirthDate();
		}
		// Multiple sets of parents (e.g. adoption) cause complications, so ignore.
		if ($birth_date->isOK() && 1 == count($families)) {
			$family = current($families);
			foreach ($family->getSpouses() as $parent) {
				if ($parent->getBirthDate()->isOK()) {
					$sex = $parent->getSexImage();
					$age = KT_Date::getAge($parent->getBirthDate(), $birth_date, 2);
					$deatdate = $parent->getDeathDate();

					switch ($parent->getSex()) {
						case 'F':
							// Highlight mothers who die in childbirth or shortly afterwards
							if ($deatdate->isOK() && $deatdate->MinJD() < $birth_date->MinJD() + 90) {
								$html .= ' <span title="' . KT_Gedcom_Tag::getLabel('_DEAT_PARE', $parent) . '" class="parentdeath">' . $sex . $age . '</span>';
							} else {
								$html .= ' <span title="' . KT_I18N::translate('Mother\'s age') . '" class="female">' . $sex . $age . '</span>';
							}

							break;

						case 'M':
							// Highlight fathers who die before the birth
							if ($deatdate->isOK() && $deatdate->MinJD() < $birth_date->MinJD()) {
								$html .= ' <span title="' . KT_Gedcom_Tag::getLabel('_DEAT_PARE', $parent) . '" class="parentdeath">' . $sex . $age . '</span>';
							} else {
								$html .= ' <span title="' . KT_I18N::translate('Father\'s age') . '" class="male">' . $sex . $age . '</span>';
							}

							break;

						default:
							$html .= ' <span title="' . KT_I18N::translate('Parent\'s age') . '">' . $sex . $age . '</span>';

							break;
					}
				}
			}
			if ($html) {
				$html = '<span class="age">' . $html . '</span>';
			}
		}
	}

	return $html;
}

// print fact DATE TIME
//
// $event - event containing the date/age
// $record - the person (or couple) whose ages should be printed
// $anchor option to print a link to calendar
// $time option to print TIME value
function format_fact_date(KT_Event $event, KT_GedcomRecord $record, $anchor = false, $time = false, $show_age = true)
{
	global $pid, $SEARCH_SPIDER;
	global $GEDCOM, $iconStyle;
	$ged_id = get_id_from_gedcom($GEDCOM);

	$factrec = $event->getGedcomRecord();
	$html = '';
	// Recorded age
	$fact_age = get_gedcom_value('AGE', 2, $factrec);
	if ('' == $fact_age) {
		$fact_age = get_gedcom_value('DATE:AGE', 2, $factrec);
	}
	$husb_age = get_gedcom_value('HUSB:AGE', 2, $factrec);
	$wife_age = get_gedcom_value('WIFE:AGE', 2, $factrec);

	// Calculated age
	if (preg_match('/2 DATE (.+)/', $factrec, $match)) {
		$date = new KT_Date($match[1]);
		$html .= ' ' . $date->Display($anchor && !$SEARCH_SPIDER);
		// time
		if ($time) {
			$timerec = get_sub_record(2, '2 TIME', $factrec);
			if ('' == $timerec) {
				$timerec = get_sub_record(2, '2 DATE', $factrec);
			}
			if (preg_match('/[2-3] TIME (.*)/', $timerec, $tmatch)) {
				$html .= '<span class="date"> - ' . $tmatch[1] . '</span>';
			}
		}
		$fact = $event->getTag();
		if ($record instanceof KT_Person) {
			// Can't use getBirthDate(), as this also gives BAPM/CHR events, which
			// wouldn't give the correct "days after birth" result for people with
			// no BIRT.
			$birth_event = $record->getFactByType('BIRT');
			if ($birth_event) {
				$birth_date = $birth_event->getDate();
			} else {
				$birth_date = new KT_Date('');
			}
			// age of parents at child birth
			$parents_age = false;
			if (($birth_date->isOK() && 'BIRT' === $fact) || (!$birth_date->isOK() && in_array($fact, ['CHR', 'BAPM'])) && $show_age) {
				$html .= format_parents_age($record->getXref(), $date);
				$parents_age = true;
			}
			// age at event
			elseif (!$parents_age && ('BIRT' !== $fact && 'CHAN' !== $fact && '_TODO' !== $fact)) {
				// Can't use getDeathDate(), as this also gives BURI/CREM events, which
				// wouldn't give the correct "days after death" result for people with
				// no DEAT.
				$death_event = $record->getFactByType('DEAT');
				if ($death_event) {
					$death_date = $death_event->getDate();
				} else {
					$death_date = new KT_Date('');
				}
				$ageText = '';
				if ((KT_Date::Compare($date, $death_date) <= 0 || !$record->isDead()) || 'DEAT' == $fact) {
					// Before death, print age
					$age = KT_Date::GetAgeGedcom($birth_date, $date);
					// Only show calculated age if it differs from recorded age
					if ('' != $age) {
						if (
							'' != $fact_age && $fact_age != $age
							|| '' == $fact_age && '' == $husb_age && '' == $wife_age
							|| '' != $husb_age && 'M' == $record->getSex() && $husb_age != $age
							|| '' != $wife_age && 'F' == $record->getSex() && $wife_age != $age
						) {
							if ('0d' != $age) {
								$ageText = '(' . KT_I18N::translate('Age') . ' ' . get_age_at_event($age, false) . ')';
							}
						}
					}
				}
				if ('DEAT' != $fact && KT_Date::Compare($date, $death_date) >= 0) {
					// After death, print time since death
					$age = get_age_at_event(KT_Date::GetAgeGedcom($death_date, $date), true);
					if ('' != $age) {
						if ('0d' == KT_Date::GetAgeGedcom($death_date, $date)) {
							$ageText = '(' . KT_I18N::translate('on the date of death') . ')';
						} else {
							$ageText = '(' . $age . ' ' . KT_I18N::translate('after death') . ')';
							// Family events which occur after death are probably errors
							if ($event->getParentObject() instanceof KT_Family) {
								$ageText .= '<i class="' . $iconStyle . ' fa-exclamation-triangle warning"></i>';
							}
						}
					}
				}
				if ($ageText && $show_age) {
					$html .= ' <span class="age" title="' . KT_I18N::translate('Calculated age') . '">' . $ageText . '</span>';
				}
			}
		} elseif ($record instanceof KT_Family) {
			$indirec = find_person_record($pid, $ged_id);
			$indi = new KT_Person($indirec);
			$birth_date = $indi->getBirthDate();
			$death_date = $indi->getDeathDate();
			$ageText = '';
			if (KT_Date::Compare($date, $death_date) <= 0) {
				$age = KT_Date::GetAgeGedcom($birth_date, $date);
				// Only show calculated age if it differs from recorded age
				if ('' != $age && $age > 0) {
					if (
						'' != $fact_age && $fact_age != $age
						|| '' == $fact_age && '' == $husb_age && '' == $wife_age
						|| '' != $husb_age && 'M' == $indi->getSex() && $husb_age != $age
						|| '' != $wife_age && 'F' == $indi->getSex() && $wife_age != $age
					) {
						$ageText = '(' . KT_I18N::translate('Age') . ' ' . get_age_at_event($age, false) . ')';
					}
				}
			}
			if ($ageText && $show_age) {
				$html .= ' <span class="age">' . $ageText . '</span>';
			}
		}
	} else {
		// 1 DEAT Y with no DATE => print YES
		// 1 BIRT 2 SOUR @S1@ => print YES
		// 1 DEAT N is not allowed
		// It is not proper GEDCOM form to use a N(o) value with an event tag to infer that it did not happen.
		$factdetail = explode(' ', trim($factrec));
		if (isset($factdetail) && (3 == count($factdetail) && 'Y' == strtoupper($factdetail[2])) || (4 == count($factdetail) && 'SOUR' == $factdetail[2])) {
			$html .= KT_I18N::translate('Yes');
		}
	}
	// print gedcom ages
	foreach ([KT_Gedcom_Tag::getLabel('AGE') => $fact_age, KT_Gedcom_Tag::getLabel('HUSB') => $husb_age, KT_Gedcom_Tag::getLabel('WIFE') => $wife_age] as $label => $age) {
		if ('' != $age && $show_age) {
			$html .= ' <span class="age"><span>' . $label . ':  '. get_age_at_event($age, false) . '</span></span>';
		}
	}

	return $html;
}

/**
 * print fact PLACe TEMPle STATus.
 *
 * @param Event $event  gedcom fact record
 * @param bool  $anchor option to print a link to placelist
 * @param bool  $sub    option to print place subrecords
 * @param bool  $lds    option to print LDS TEMPle and STATus
 */
function format_fact_place(KT_Event $event, $anchor = false, $sub = false, $lds = false)
{
	global $SHOW_PEDIGREE_PLACES, $SHOW_PEDIGREE_PLACES_SUFFIX, $SEARCH_SPIDER;

	$factrec = $event->getGedcomRecord();
	$name_parts = explode(', ', (string) $event->getPlace());
	$ct = count($name_parts);
	$kt_place = new KT_Place($event->getPlace(), KT_GED_ID);

	if ($anchor) {
		// Show the full place name, for facts/events tab
		if ($SEARCH_SPIDER) {
			$html = $kt_place->getFullName();
		} else {
			$html = '<a href="' . $kt_place->getURL() . '">' . $kt_place->getFullName() . '</a>';
		}
	} else {
		// Abbreviate the place name, for chart boxes
		return $kt_place->getShortName();
	}

	$ctn = 0;
	if ($sub) {
		$placerec = get_sub_record(2, '2 PLAC', $factrec);
		if (!empty($placerec)) {
			if (preg_match_all('/\n3 (?:_HEB|ROMN) (.+)/', $placerec, $matches)) {
				foreach ($matches[1] as $match) {
					$kt_place = new KT_Place($match, KT_GED_ID);
					$html .= '&nbsp;' . $kt_place->getFullName();
				}
			}
			$map_lati = '';
			$cts = preg_match('/\d LATI (.*)/', $placerec, $match);
			if ($cts > 0) {
				$map_lati = $match[1];
				$html .= '<br><span>' . KT_Gedcom_Tag::getLabel('LATI') . ': </span>' . $map_lati;
			}
			$map_long = '';
			$cts = preg_match('/\d LONG (.*)/', $placerec, $match);
			if ($cts > 0) {
				$map_long = $match[1];
				$html .= ' <span>' . KT_Gedcom_Tag::getLabel('LONG') . ': </span>' . $map_long;
			}
			if ($map_lati && $map_long && empty($SEARCH_SPIDER)) {
				$map_lati = trim(strtr($map_lati, 'NSEW,�', ' - -. ')); // S5,6789 ==> -5.6789
				$map_long = trim(strtr($map_long, 'NSEW,�', ' - -. ')); // E3.456� ==> 3.456
				if ($name_parts) {
					$place = $name_parts[0];
				} else {
					$place = '';
				}
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://maps.google.com/maps?q=' . $map_lati . ',' . $map_long . '" class="icon-googlemaps" title="' . KT_I18N::translate('Google Maps™') . '"></a>';
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://www.bing.com/maps/?lvl=15&cp=' . $map_lati . '~' . $map_long . '" class="icon-bing" title="' . KT_I18N::translate('Bing Maps™') . '"></a>';
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://www.openstreetmap.org/#map=15/' . $map_lati . '/' . $map_long . '" class="icon-osm" title="' . KT_I18N::translate('OpenStreetMap™') . '"></a>';
			}
			if (preg_match('/\d NOTE (.*)/', $placerec, $match)) {
				ob_start();
				print_fact_notes($placerec, 3);
				$html .= '<br>' . ob_get_contents();
				ob_end_clean();
			}
		}
	}
	if ($lds) {
		if (preg_match('/2 TEMP (.*)/', $factrec, $match)) {
			$tcode = trim($match[1]);
			$html .= '<br>' . KT_I18N::translate('LDS Temple') . ': ' . KT_Gedcom_Code_Temp::templeName($match[1]);
		}
		if (preg_match('/2 STAT (.*)/', $factrec, $match)) {
			$html .= '<br>' . KT_I18N::translate('Status') . ': ' . KT_Gedcom_Code_Stat::statusName($match[1]);
			if (preg_match('/3 DATE (.*)/', $factrec, $match)) {
				$date = new KT_Date($match[1]);
				$html .= ', ' . KT_Gedcom_Tag::getLabel('STAT:DATE') . ': ' . $date->Display(false);
			}
		}
	}

	return $html;
}

/**
 * Check for facts that may exist only once for a certain record type.
 * If the fact already exists in the second array, delete it from the first one.
 *
 * @param mixed $uniquefacts
 * @param mixed $recfacts
 * @param mixed $type
 */
function CheckFactUnique($uniquefacts, $recfacts, $type)
{
	foreach ($recfacts as $indexval => $factarray) {
		$fact = false;
		if (is_object($factarray)) {
			// @var $factarray Event
			$fact = $factarray->getTag();
		} else {
			if (('SOUR' == $type) || ('REPO' == $type)) {
				$factrec = $factarray[0];
			}
			if (('FAM' == $type) || ('INDI' == $type)) {
				$factrec = $factarray[1];
			}

			$ft = preg_match('/1 (\\w+)(.*)/', $factrec, $match);
			if ($ft > 0) {
				$fact = trim($match[1]);
			}
		}
		if (false !== $fact) {
			$key = array_search($fact, $uniquefacts);
			if (false !== $key) {
				unset($uniquefacts[$key]);
			}
		}
	}

	return $uniquefacts;
}

/**
 * Print a new fact box on details pages.
 *
 * @param string $id        the id of the person, family, source etc the fact will be added to
 * @param array  $usedfacts an array of facts already used in this record
 * @param string $type      the type of record INDI, FAM, SOUR etc
 *
 */
function print_add_new_fact($id, $usedfacts, $type)
{
	global $KT_SESSION;

	switch ($type) {
		case 'SB_ATTRIB':
			$classes1 = 'cell fact-title';
			$classes2 = 'cell fact-detail';
			$label = KT_I18N::translate('Add attribute');
			break;
		case 'INDI_ATTRIB':
			$classes1 = 'cell medium-3 fact-title';
			$classes2 = 'cell medium-7 fact-detail';
			$label = KT_I18N::translate('Add attribute');
			break;
		default:
			$classes1 = 'cell medium-3 fact-title';
			$classes2 = 'cell medium-7 fact-detail';
			$label = KT_I18N::translate('Add event');
			break;
	} ?>

	<div class="cell indiFact famFact">
		<div class="grid-x grid-padding-x grid-padding-y">
			<!-- Add from clipboard -->
			<?php if ($KT_SESSION->clipboard) {
				$newRow = true;
				foreach (array_reverse($KT_SESSION->clipboard, true) as $key => $fact) {
					if ($fact['type'] == $type || 'all' == $fact['type']) {
						if ($newRow) {
							$newRow = false; ?>
							<div class="<?php echo $classes1; ?>">
								<label><?php echo KT_I18N::translate('Add from clipboard'); ?></label>
							</div>
							<div class="<?php echo $classes2; ?>">
								<form method="get" name="newFromClipboard" action="" onsubmit="return false;">
									<div class="input-group">
										<div class="input-group-button">
											<input type="button" class="button" value="<?php echo KT_I18N::translate('Add'); ?>" onclick="addClipboardRecord(<?php echo $id; ?>, 'newClipboardFact');">
										</div>
										<select class="input-group-field" id="newClipboardFact" name="newClipboardFact">
						<?php }
											$fact_type = KT_Gedcom_Tag::getLabel($fact['fact']); ?>
											<option value="clipboard_<?php echo $key; ?>">
												<?php echo $fact_type;
												if (preg_match('/^2 DATE (.+)/m', $fact['factrec'], $match)) {
													$tmp = new KT_Date($match[1]);
													echo '; ' . $tmp->minDate()->Format('%Y');
												}
												if (preg_match('/^2 PLAC ([^,\n]+)/m', $fact['factrec'], $match)) {
													echo '; ' . $match[1];
												} ?>
											</option>
					<?php }
				}
				if (!$newRow) { ?>
					</select>
					</div>
					</form>
					</div>
				<?php }
			} ?>

			<!-- Add from pick list -->
			<?php switch ($type) {
				case 'INDI':
					$addfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'INDI_ATTRIB':
					$facts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					foreach ($facts as $fact) {
						if (KT_Gedcom_Tag::isTagAttribute($fact)) {
							$addfacts[] = $fact;
						}
					}
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'FAM':
					$addfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'SOUR':
					$addfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'NOTE':
					$addfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'REPO':
					$addfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
					$uniquefacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
					$quickfacts = preg_split('/[, ;:]+/', get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK'), -1, PREG_SPLIT_NO_EMPTY);

					break;

				default:
					return;
			}
			$addfacts = array_merge(CheckFactUnique($uniquefacts, $usedfacts, $type), $addfacts);
			$quickfacts = array_intersect($quickfacts, $addfacts);
			$translated_addfacts = [];
			foreach ($addfacts as $addfact) {
				$translated_addfacts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
			}
			uasort($translated_addfacts, 'factsort'); ?>

			<div class="<?php echo $classes1; ?>">
				<label class="h6"><?php echo $label; ?></label>
			</div>
			<div class="<?php echo $classes2; ?>">
				<form method="get" name="newfactform" action="" onsubmit="return false;">
					<div class="input-group">
						<div class="input-group-button">
							<input type="button" class="button" value="<?php echo KT_I18N::translate('Add'); ?>" onclick="add_record('<?php echo $id; ?>', 'newfact');">
						</div>
						<select id="newfact" class="input-group-field" name="newfact">
							<option value="" disabled selected><?php echo KT_I18N::translate('Select'); ?></option>
							<?php foreach ($translated_addfacts as $fact => $fact_name) {
								if ('EVEN' !== $fact && 'FACT' !== $fact) { ?>
									<option value="<?php echo $fact; ?>"><?php echo $fact_name; ?></option>
								<?php }
								}
							if ('INDI' == $type || 'FAM' == $type) { ?>
								<option value="EVEN"><?php echo KT_I18N::translate('Custom event'); ?></option>
								<option value="FACT"><?php echo KT_I18N::translate('Custom Fact'); ?></option>
							<?php } ?>
						</select>
					</div>
					<?php if ($quickfacts) { ?>
						<span class="quickfacts">
							<?php foreach ($quickfacts as $fact) { ?>
								<a href="edit_interface.php?action=add&pid=<?php echo $id; ?>&fact=<?php echo $fact; ?>&accesstime=<?php echo KT_TIMESTAMP; ?>&ged=<?php echo KT_GEDCOM; ?>" target="_blank">
									<?php echo KT_Gedcom_Tag::getLabel($fact); ?>
								</a>
							<?php } ?>
						</span>
					<?php } ?>
				</form>
			</div>
		</div>
	</div>
<?php }

/**
 * javascript declaration for calendar popup.
 *
 * @param none
 */
function init_calendar_popup()
{
	global $WEEK_START, $controller;

	$controller
		->addExternalJavascript(KT_DATEPICKER_JS)
		->addExternalJavascript(KT_DATEPICKER_JS_LOCALE)
		->addInlineJavascript('
			jQuery(".fdatepicker").fdatepicker({
				language: "' . KT_LOCALE . '"
			});

			cal_setMonthNames(
				"' . KT_I18N::translate_c('NOMINATIVE', 'January') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'February') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'March') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'April') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'May') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'June') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'July') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'August') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'September') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'October') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'November') . '",
				"' . KT_I18N::translate_c('NOMINATIVE', 'December') . '"
			)
			cal_setDayHeaders(
				"' . KT_I18N::translate('Sun') . '",
				"' . KT_I18N::translate('Mon') . '",
				"' . KT_I18N::translate('Tue') . '",
				"' . KT_I18N::translate('Wed') . '",
				"' . KT_I18N::translate('Thu') . '",
				"' . KT_I18N::translate('Fri') . '",
				"' . KT_I18N::translate('Sat') . '"
			)
			cal_setWeekStart(' . $WEEK_START . ');
	')
	;
}

function print_findindi_link($element_id, $indiname = '', $ged = KT_GEDCOM)
{
	return '<a href="#" onclick="findIndi(document.getElementById(\'' . $element_id . '\'), document.getElementById(\'' . $indiname . '\'), \'' . KT_Filter::escapeHtml($ged) . '\'); return false;" class="icon-button_indi" title="' . KT_I18N::translate('Find an individual') . '"></a>';
}

function print_findplace_link($element_id)
{
	return '<a href="#" onclick="findPlace(document.getElementById(\'' . $element_id . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_place" title="' . KT_I18N::translate('Find a place') . '"></a>';
}

function print_findfamily_link($element_id)
{
	return '<a href="#" onclick="findFamily(document.getElementById(\'' . $element_id . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_family" title="' . KT_I18N::translate('Find a family') . '"></a>';
}

function print_specialchar_link($element_id)
{
	global $iconStyle;

	return '
		<span onclick="findSpecialChar(document.getElementById(\'' . $element_id . '\')); if (window.updatewholename) { updatewholename(); } return false;" title="' . KT_I18N::translate('Find a special character') . '" data-tooltip data-position="top" data-alignment="center">
			<i class="' . $iconStyle . ' fa-keyboard fa-fw"></i>
		</span>
	';

}

function print_specialcharacters($element_id)
{
	global $iconStyle;

	$revealID = 'specChar-' . $element_id;
	$appendTo = 'appendTo-' . $element_id; ?>

		<span class="input-group-label" data-open="<?php echo $revealID; ?>" id="<?php echo $appendTo; ?>" title="<?php echo KT_I18N::translate('Find a special character'); ?> '" style="cursor:pointer;">
				<i class="<?php echo $iconStyle; ?> fa-keyboard"></i>
		</span>

		<div class="tiny reveal" id="<?php echo $revealID; ?>" data-reveal data-overlay="false" data-v-offset=0>

			<?php include KT_ROOT . 'includes/reveal/specialcharacters.php'; ?>

			<button class="close-button" data-close aria-label="<?php echo KT_I18N::translate('Close'); ?>" type="button">
				<span aria-hidden="true">
					<i class="<?php echo $iconStyle; ?> fa-xmark"></i>
				</span>
			</button>
		</div>

	<?php

}

function print_autopaste_link($element_id, $choices)
{
	echo '<small>';
	foreach ($choices as $indexval => $choice) {
		echo '<span onclick="document.getElementById(\'', $element_id, '\').value=';
		echo '\'', $choice, '\';';
		echo ' return false;">', $choice, '</span> ';
	}
	echo '</small>';
}

function print_findsource_link($element_id, $sourcename = '')
{
	return '<a href="#" onclick="findSource(document.getElementById(\'' . $element_id . '\'), document.getElementById(\'' . $sourcename . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_source" title="' . KT_I18N::translate('Find a source') . '"></a>';
}

function print_findnote_link($element_id, $notename = '')
{
	return '<a href="#" onclick="findnote(document.getElementById(\'' . $element_id . '\'), document.getElementById(\'' . $notename . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_findnote" title="' . KT_I18N::translate('Find a note') . '"></a>';
}

function print_findrepository_link($element_id)
{
	return '<a href="#" onclick="findRepository(document.getElementById(\'' . $element_id . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_repository" title="' . KT_I18N::translate('Find a repository') . '"></a>';
}

function print_findmedia_link($element_id, $choose = '')
{
	return '<a href="#" onclick="findMedia(document.getElementById(\'' . $element_id . '\'), \'' . $choose . '\', \'' . KT_GEDURL . '\'); return false;" class="icon-button_media" title="' . KT_I18N::translate('Find a media object') . '"></a>';
}

function print_findfact_link($element_id)
{
	return '<a href="#" onclick="findFact(document.getElementById(\'' . $element_id . '\'), \'' . KT_GEDURL . '\'); return false;" class="icon-button_find_facts" title="' . KT_I18N::translate('Find a fact or event') . '"></a>';
}

function print_findfact_edit_link($element_id)
{
	return '<a href="#" onclick="findFact(document.getElementById(\'' . $element_id . '\'), \'' . KT_GEDURL . '\'); return false;" title="' . KT_I18N::translate('Find a fact or event') . '">
				<i class="' . $iconStyle . ' fa-pen-to-square"></i>' . KT_I18N::translate('Edit') . '
			</a>';
}

/**
 * get a quick-glance view of current LDS ordinances.
 *
 * @param string $indirec
 *
 * @return string
 */
function get_lds_glance($indirec)
{
	global $GEDCOM;
	$ged_id = get_id_from_gedcom($GEDCOM);
	$text = '';

	$ord = get_sub_record(1, '1 BAPL', $indirec);
	if ($ord) {
		$text .= 'B';
	} else {
		$text .= '_';
	}
	$ord = get_sub_record(1, '1 ENDL', $indirec);
	if ($ord) {
		$text .= 'E';
	} else {
		$text .= '_';
	}
	$found = false;
	$ct = preg_match_all('/1 FAMS @(.*)@/', $indirec, $match, PREG_SET_ORDER);
	for ($i = 0; $i < $ct; $i++) {
		$famrec = find_family_record($match[$i][1], $ged_id);
		if ($famrec) {
			$ord = get_sub_record(1, '1 SLGS', $famrec);
			if ($ord) {
				$found = true;

				break;
			}
		}
	}
	if ($found) {
		$text .= 'S';
	} else {
		$text .= '_';
	}
	$ord = get_sub_record(1, '1 SLGC', $indirec);
	if ($ord) {
		$text .= 'P';
	} else {
		$text .= '_';
	}

	return $text;
}

function getPersonLinks($person)
{
	global $PEDIGREE_FULL_DETAILS, $OLD_PGENS, $GEDCOM;
	global $box_width, $chart_style, $generations, $show_spouse, $talloffset;

	$pid = $person->getXref();
//	$tmp = array('M'=>'', 'F'=>'F', 'U'=>'U');
//	$isF = $tmp[$person->getSex()];

	$personlinks = '<ul class="person_box_template ' . $person->getSex() . ' links">
		<li>
			<a href="pedigree.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;talloffset=' . $talloffset . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Pedigree') . '</b>
			</a>
		</li>';
	if (array_key_exists('googlemap', KT_Module::getActiveModules())) {
		$personlinks .= '
				<li>
					<a href="module.php?mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=' . $pid . '&amp;ged=' . KT_GEDURL . '">
						<b>' . KT_I18N::translate('Pedigree map') . '</b>
					</a>
				</li>
			';
	}
	if (KT_USER_GEDCOM_ID && KT_USER_GEDCOM_ID != $pid) {
		$personlinks .= '
				<li>
					<a href="relationship.php?show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;pid1=' . KT_USER_GEDCOM_ID . '&amp;pid2=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;pretty=2&amp;followspouse=1&amp;ged=' . KT_GEDURL . '">
						<b>' . KT_I18N::translate('Relationship to me') . '</b>
					</a>
				</li>
			';
	}
	$personlinks .= '<li>
			<a href="descendancy.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;generations=' . $generations . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Descendants') . '</b>
			</a>
		</li>
		<li>
			<a href="ancestry.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;chart_style=' . $chart_style . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Ancestors') . '</b>
			</a>
		</li>
		<li>
			<a href="compact.php?rootid=' . $pid . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Compact tree') . '</b>
			</a>
			</li>
		<li>
			<a href="module.php?mod=chart_fanchart&mod_action=show&rootid=' . $pid . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Fanchart') . '</b>
			</a>
		</li>
		<li>
			<a href="hourglass.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;chart_style=' . $chart_style . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '&amp;show_spouse=' . $show_spouse . '">
				<b>' . KT_I18N::translate('Hourglass chart') . '</b>
			</a>
		</li>';
	if (array_key_exists('tree', KT_Module::getActiveModules())) {
		$personlinks .= '
				<li>
					<a href="module.php?mod=tree&amp;mod_action=treeview&amp;ged=' . KT_GEDURL . '&amp;rootid=' . $pid . '">
						<b>' . KT_I18N::translate('Interactive tree') . '</b>
					</a>
				</li>
			';
	}
	foreach ($person->getSpouseFamilies() as $family) {
		$spouse = $family->getSpouse($person);
		$children = $family->getChildren();
		$num = count($children);
		$personlinks .= '<li>';
		if ((!empty($spouse)) || ($num > 0)) {
			$personlinks .= '
						<a href="' . $family->getHtmlUrl() . '">
							<b>' . KT_I18N::translate('Family with spouse') . '</b>
						</a>
					';
			if (!empty($spouse)) {
				$personlinks .= '
							<a href="' . $spouse->getHtmlUrl() . '">' .
						$spouse->getFullName() . '
							</a>
						';
			}
		}
		$personlinks .= '
				<ul>';
		foreach ($children as $child) {
			$personlinks .= '
							<li>
								<a href="' . $child->getHtmlUrl() . '">' .
						$child->getFullName() . '
								</a>
							</li>
						';
		}
		$personlinks .= '</ul>';
	}
	$personlinks .= '</li></ul>';

	return $personlinks;
}
