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

class tabi_attributes_KT_Module extends KT_Module implements KT_Module_IndiTab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Attributes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Facts and events” module */ KT_I18N::translate('A tab showing all recorded attributes of an individual');
	}

	// Extend class KT_Module_IndiTab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function defaultTabOrder() {
		return 10;
	}

	// Implement KT_Module_IndiTab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_IndiTab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_IndiTab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_IndiTab
	public function getTabContent() {
		global $controller,$SHOW_COUNTER, $SEARCH_SPIDER;

		ob_start();
			$indifacts = $controller->getIndiFacts();

			$xrefData = array(
				'label' => KT_I18N::translate('Internal reference '),
				'detail'=> '<span>' . $controller->record->getXref() . '</span>',
			);
			if ($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
				require KT_ROOT . 'includes/hitcount.php';
				$hitData = array(
					'label' => KT_I18N::translate('Hit Count:'),
					'detail'=> '<span>' . $hitCount . '</span>',
				);
			}
			if (count($indifacts) == 0) { ?>
				<div class="callout alert">
					<?php echo KT_I18N::translate('There are no attributes for this individual.'); ?>
				</div>
			<?php } else { ?>
				<div class="cell tabHeader"></div>
				<div class="cell show-for-medium indiFactHeader">
					<div class="grid-x">
						<div class="cell medium-3 event">
							<label><?php echo KT_I18N::translate('Attribute'); ?></label>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'medium-8' : 'auto'); ?> detail">
							<label><?php echo KT_I18N::translate('Details'); ?></label>
						</div>
						<?php if (KT_USER_CAN_EDIT) { ?>
							<div class="cell medium-1 edit">
								<label><?php echo KT_I18N::translate('Edit'); ?></label>
							</div>
						<?php } ?>
					</div>
				</div>
				<!-- Xref id -->
				<div class="cell indiFact">
					<div class="grid-x">
						<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
							<span class="h6"><?php echo $xrefData['label']; ?></span>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
							<?php echo $xrefData['detail']; ?>
						</div>
					</div>
				</div>
				<!-- Privacy status -->
				<div class="cell indiFact">
					<div class="grid-x">
						<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
							<span class="h6"><?php echo KT_I18N::translate('Privacy status'); ?></span>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
							<?php echo $this->privacyStatus(); ?>
						</div>
					</div>
				</div>
				<?php
				//- All GEDCOM attribute facts -//
				foreach ($indifacts as $fact) {
					if (KT_Gedcom_Tag::isTagAttribute($fact->getTag())) {
						print_attributes($fact, $controller->record);
					}
				}
				?>
				<!-- Hit count -->
				<div class="cell indiFact">
					<div class="grid-x">
						<div class="cell small-10 medium-3 small-order-1 medium-order-1 event">
							<span class="h6"><?php echo $hitData['label']; ?></span>
						</div>
						<div class="cell <?php echo (KT_USER_CAN_EDIT ? 'small-10 medium-8' : 'auto'); ?> small-order-5 medium-order-4 detail">
							<?php echo $hitData['detail']; ?>
						</div>
					</div>
				</div>
				<?php
				//-- new fact link
				if ($controller->record->canEdit()) {
					print_add_new_fact($controller->record->getXref(), $indifacts, 'INDI_ATTRIB');
				}
			}
			return '
				<div id="' . $this->getName() . '_content" class="grid-x grid-padding-y">' .
					ob_get_clean() . '
				</div>
			';
		}

	// Implement KT_Module_IndiTab
	private function privacyStatus() {
	    // code based on similar in function_print_list.php
	    global $MAX_ALIVE_AGE, $SHOW_EST_LIST_DATES, $SEARCH_SPIDER;
	    $SHOW_EST_LIST_DATES=get_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES');
	    $controller = new KT_Controller_Individual();
	    $html = '<dl id="privacy_status">';
	    if ($death_dates=$controller->record->getAllDeathDates()) {
	        $html .= '<dt>' .KT_I18N::translate('Dead').help_link('privacy_status',$this->getName()). '</dt>';
	        foreach ($death_dates as $num=>$death_date) {
	            if ($num) {
	                $html .= ' | ';
	            }
	            $html .= '<dd>' .KT_I18N::translate('Death recorded as %s', $death_date->Display(!$SEARCH_SPIDER)). '</dd>';
	        }
	    } else {
	        $death_date=$controller->record->getEstimatedDeathDate();
	        if (!$death_date && $SHOW_EST_LIST_DATES) {
	            $html .= '<dt>' . KT_I18N::translate('Presumed dead') . help_link('privacy_status', $this->getName()) . '</dt>';
	            $html .= '<dd>' . KT_I18N::translate('An estimated death date has been calculated as %s', $death_date->Display(!$SEARCH_SPIDER)) . '</dd>';
	        } else if ($controller->record->isDead()) {
	            $html .= '<dt>' . KT_I18N::translate('Presumed dead') . help_link('privacy_status', $this->getName()) . '</dt>';
	            $html .= '<dd>' . $this->isDeadDetail() . '</dd>';
	        } else {
	            $html .= '<dt>' . KT_I18N::translate('Living') . help_link('privacy_status', $this->getName()) . '</dt>';
	            $html .= '<dd>' . $this->isDeadDetail() . '</dd>';
	        }
	        $death_dates[0]=new KT_Date('');
	    }
	    $html .= '</dl>';
	    return $html;

	}

	 // Implement KT_Module_IndiTab
	private function isDeadDetail() {
		// This is a copy, with modifications, of the function isDead() in /library/WT/Person.php (w1.4.2)
		// It is VERY important that the parameters used in both are identical.

	     global $MAX_ALIVE_AGE, $SEARCH_SPIDER, $controller;

	     // "1 DEAT Y" or "1 DEAT/2 DATE" or "1 DEAT/2 PLAC"
	     if (preg_match('/\n1 (?:'.KT_EVENTS_DEAT.')(?: Y|(?:\n[2-9].+)*\n2 (DATE|PLAC) )/', $controller->record->getGedcomRecord())) {
	         return KT_I18N::translate('Death is recorded with an unknown date.');
	     }

	     // If any event occured more than $MAX_ALIVE_AGE years ago, then assume the person is dead
	     if (preg_match_all('/\n2 DATE (.+)/', $controller->record->getGedcomRecord(), $date_matches)) {
	         foreach ($date_matches[1] as $date_match) {
	             $date=new KT_Date($date_match);
	             if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*$MAX_ALIVE_AGE) {
	                 return KT_I18N::translate('An event occurred in this person\'s life more than %s years ago<br> %s', $MAX_ALIVE_AGE, $date->Display(!$SEARCH_SPIDER));
	             }
	         }
	         // The individual has one or more dated events.  All are less than $MAX_ALIVE_AGE years ago.
	         // If one of these is a birth, the person must be alive.
	         if (preg_match('/\n1 BIRT(?:\n[2-9].+)*\n2 DATE /', $controller->record->getGedcomRecord())) {
	             $date=$controller->record->getBirthDate();
	             return KT_I18N::translate('This person\'s birth was less %s years ago<br> %s', $MAX_ALIVE_AGE, $date->Display(!$SEARCH_SPIDER));
	         }
	     }
	     // If we found no dates then check the dates of close relatives.

	     // Check parents (birth and adopted)
	     foreach ($controller->record->getChildFamilies(KT_PRIV_HIDE) as $family) {
	         foreach ($family->getSpouses(KT_PRIV_HIDE) as $parent) {
	             // Assume parents are no more than 45 years older than their children
	             preg_match_all('/\n2 DATE (.+)/', $parent->getGedcomRecord(), $date_matches);
	             foreach ($date_matches[1] as $date_match) {
	                 $date=new KT_Date($date_match);
	                 if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*($MAX_ALIVE_AGE+45)) {
	                     return KT_I18N::translate('A parent with a date of %s is more than 45 years older than this person', $date->Display(!$SEARCH_SPIDER));
	                 }
	             }
	         }
	     }

	     // Check spouses
	     foreach ($controller->record->getSpouseFamilies(KT_PRIV_HIDE) as $family) {
	         preg_match_all('/\n2 DATE (.+)/', $family->getGedcomRecord(), $date_matches);
	         foreach ($date_matches[1] as $date_match) {
	             $date=new KT_Date($date_match);
	             // Assume marriage occurs after age of 10
	             if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*($MAX_ALIVE_AGE-10)) {
	                 return KT_I18N::translate('A marriage with a date of %s suggests they were born at least 10 years earlier than that.', $date->Display(!$SEARCH_SPIDER));
	             }
	         }
	         // Check spouse dates
	         $spouse=$family->getSpouse($controller->record, KT_PRIV_HIDE);
	         if ($spouse) {
	             preg_match_all('/\n2 DATE (.+)/', $spouse->getGedcomRecord(), $date_matches);
	             foreach ($date_matches[1] as $date_match) {
	                 $date=new KT_Date($date_match);
	                 // Assume max age difference between spouses of 40 years
	                 if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*($MAX_ALIVE_AGE+40)) {
	                     return KT_I18N::translate('A spouse with a date of %s is more than 40 years older than this person', $date->Display(!$SEARCH_SPIDER));
	                 }
	             }
	         }
	         // Check child dates
	         foreach ($family->getChildren(KT_PRIV_HIDE) as $child) {
	             preg_match_all('/\n2 DATE (.+)/', $child->getGedcomRecord(), $date_matches);
	             // Assume children born after age of 15
	             foreach ($date_matches[1] as $date_match) {
	                 $date=new KT_Date($date_match);
	                 if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*($MAX_ALIVE_AGE-15)) {
	                     return KT_I18N::translate('A child with a date of %s suggests this person was born at least 15 years earlier than that.', $date->Display(!$SEARCH_SPIDER));
	                 }
	             }
	             // Check grandchildren
	             foreach ($child->getSpouseFamilies(KT_PRIV_HIDE) as $child_family) {
	                 foreach ($child_family->getChildren(KT_PRIV_HIDE) as $grandchild) {
	                     preg_match_all('/\n2 DATE (.+)/', $grandchild->getGedcomRecord(), $date_matches);
	                     // Assume grandchildren born after age of 30
	                     foreach ($date_matches[1] as $date_match) {
	                         $date=new KT_Date($date_match);
	                         if ($date->isOK() && $date->MaxJD() <= KT_CLIENT_JD - 365*($MAX_ALIVE_AGE-30)) {
	                             return KT_I18N::translate('A grandchild with a date of %s suggests this person was born at least 30 years earlier than that.', $date->Display(!$SEARCH_SPIDER));
	                         }
	                     }
	                 }
	             }
	         }
	     }
	     return KT_I18N::translate('There are no records to suggest this person is dead, so they are displayed as living.');
	 }

}
