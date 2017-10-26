<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Implementaton of the quizaccess_useripmapping plugin.
 *
 * @package   quizaccess_useripmapping
 * @author    Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright 2017 Indian Institute Of Technology,Bombay,India
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
global $DB;
/**
 * A rule implementing the user ip mapping check in order to restrict user to attempt
 * quiz from mapped/assigned IP Address
 *
 * @author    Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright 2017 Indian Institute Of Technology,Bombay,India
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_useripmapping extends quiz_access_rule_base
{
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->useripmappingrequired)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        $username = $_SESSION['USER']->username;
        global $DB;
        $quizid               = $this->quiz->id;
        $allowifunassignedsql = "SELECT allowifunassigned FROM mdl_quizaccess_enable_mappings WHERE quizid=$quizid";
        $allowifunassigned    = $DB->get_field_sql($allowifunassignedsql);
        $ipsql                = "SELECT ip FROM mdl_quizaccess_useripmappings WHERE username='$username' and quizid=$quizid
                                 order by timecreated DESC limit 1";
        $mappedipaddress    = $DB->get_field_sql($ipsql);
        $remoteaddr           = getremoteaddr();
        $ipmismatchmessage1   = get_string('$ipmismatchmessage1', 'quizaccess_useripmapping');
        $ipmismatchmessage2   = get_string('$ipmismatchmessage2', 'quizaccess_useripmapping');
        $ipnotassignedmessage = get_string('ipnotassignedmessage', 'quizaccess_useripmapping');
        if ($allowifunassigned) {
            if (empty($mappedipaddress)) {
                return false;
            } else if (address_in_subnet(getremoteaddr(), $mappedipaddress)) {
                 return false;
            } else {
                 return "$ipmismatchmessage1.$mappedipaddress.$ipmismatchmessage2";
            }
        } else {
            if (address_in_subnet(getremoteaddr(), $mappedipaddress)) {
                return false;
            } else {
                return $ipnotassignedmessage;
            }
        }
    }
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $useripmappingarray   = array();
        $useripmappingarray[] = $mform->createElement('select', 'useripmappingrequired',
            get_string('useripmappingrequired', 'quizaccess_useripmapping'), array(
            0 => get_string('notrequired', 'quizaccess_useripmapping'),
            1 => get_string('useripmappingrequiredoption', 'quizaccess_useripmapping')
        ));
        $useripmappingarray[] = $mform->createElement('advcheckbox', 'allowifunassigned', '', 'Allow Unmapped', '', array(
            0,
            1
        ));
        $mform->disabledIf('allowifunassigned', 'useripmappingrequired', 'neq', 1);
        $mform->addGroup($useripmappingarray, 'enableuseripmapping',
            get_string('useripmappingrequired', 'quizaccess_useripmapping'), array(' '), false);
        $mform->addHelpButton('enableuseripmapping', 'useripmappingrequired', 'quizaccess_useripmapping');
        $mform->setAdvanced('enableuseripmapping', true);
    }
    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->useripmappingrequired)) {
            $DB->delete_records('quizaccess_enable_mappings', array(
                'quizid' => $quiz->id
            ));
        } else {
            if (!$DB->record_exists('quizaccess_enable_mappings', array(
                'quizid' => $quiz->id
            ))) {
                $record                        = new stdClass();
                $record->quizid                = $quiz->id;
                $record->useripmappingrequired = 1;
                $record->allowifunassigned     = $quiz->allowifunassigned;
                $DB->insert_record('quizaccess_enable_mappings', $record);
            } else {
                $select                    = "quizid=$quiz->id";
                $id                        = $DB->get_field_select('quizaccess_enable_mappings', 'id', $select);
                $record                    = new stdClass();
                $record->id                = $id;
                $record->allowifunassigned = $quiz->allowifunassigned;
                $DB->update_record('quizaccess_enable_mappings', $record);
            }
        }
    }
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_enable_mappings', array(
            'quizid' => $quiz->id
        ));
    }
    public static function get_settings_sql($quizid) {
        return array(
            'useripmappingrequired',
            'LEFT JOIN {quizaccess_enable_mappings} enable_mappings ON enable_mappings.quizid = quiz.id',
            array()
        );
    }
}