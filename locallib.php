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
 * Library of functions used by the quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Validation callback function - verifies the column line of csv file.
 * Converts standard column names to lowercase.
 *
 * @param csv_import_reader $cir
 * @param array $stdfields
 *            standard user fields
 * @param moodle_url $returnurl
 *            return url in case of any error
 * @return array list of fields
 */
function um_validate_user_mapping_columns(csv_import_reader $cir, $stdfields, moodle_url $returnurl) {
    $columns = $cir->get_columns();
    if (empty($columns)) {
        $cir->close();
        $cir->cleanup();
        print_error('cannotreadtmpfile', 'error', $returnurl);
    }
    if (count($columns) < 2) {
        $cir->close();
        $cir->cleanup();
        print_error('csvfewcolumns', 'error', $returnurl);
    }
    // Test columns.
    $processed = array();
    foreach ($columns as $key => $unused) {
        $field   = $columns[$key];
        $lcfield = core_text::strtolower($field);
        if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
            // Standard fields are only lowercase.
            $newfield = $lcfield;
        } else if (preg_match('/^(sysrole|cohort|course|group|type|role|enrolperiod|enrolstatus)\d+$/', $lcfield)) {
            // Special fields for enrolments.
            $newfield = $lcfield;
        } else {
            $cir->close();
            $cir->cleanup();
            print_error('invalidfieldname', 'error', $returnurl, $field);
        }
        if (in_array($newfield, $processed)) {
            $cir->close();
            $cir->cleanup();
            print_error('duplicatefieldname', 'error', $returnurl, $newfield);
        }
        $processed[$key] = $newfield;
    }
    return $processed;
}
/**
 * This function extends the settings navigation block for the site.
 * It is being called from quiz_extend_settings_navigation function.
 */
function useripmapping_accessrule_extend_navigation($accessrulenode, $cm) {
    $url        = new moodle_url('/mod/quiz/accessrule/useripmapping/managemappings.php', array(
        'quizid' => $cm->instance,
        'courseid' => $cm->course,
        'cmid' => $cm->id
    ));
    $node       = navigation_node::create(get_string('useripmapping', 'quizaccess_useripmapping'), $url,
                  navigation_node::TYPE_SETTING, null, 'quiz_accessrule_useripmapping', new pix_icon('i/item', ''));
    $managenode = $accessrulenode->add_node($node);
}