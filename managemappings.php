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
 * Manage User-IP Mapping for the quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
$quizid       = required_param('quizid', PARAM_INT);
$courseid     = required_param('courseid', PARAM_INT);
$cmid         = required_param('cmid', PARAM_INT);
$coursemodule = get_coursemodule_from_id('quiz', $cmid);
$context      = context_module::instance($cmid);
require_login($courseid, false, $coursemodule);
require_capability('mod/quiz:manage', $context);
$returnurl = new moodle_url('/mod/quiz/accessrule/useripmapping/managemappings.php', array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$PAGE->set_title('Manage user-IP Mappings');
$PAGE->set_heading('Manage user-IP Mappings');
$PAGE->set_url($CFG->wwwroot . '/mod/quiz/accessrule/useripmapping/managemappings.php', array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$PAGE->requires->jquery();
echo $OUTPUT->header();
$importmappingurl = new moodle_url("/mod/quiz/accessrule/useripmapping/importmappings.php", array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$editmappingurl   = new moodle_url("/mod/quiz/accessrule/useripmapping/editmapping.php", array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
echo html_writer::tag('a', get_string('importmapping', 'quizaccess_useripmapping'), array(
    'href' => $importmappingurl
));
echo html_writer::tag('br', '');
echo html_writer::tag('a', get_string('editmapping', 'quizaccess_useripmapping'), array(
    'href' => $editmappingurl
));
echo $OUTPUT->footer();