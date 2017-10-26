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
 * Imports User-IP Mapping for the quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/useripmapping/useripmapping_form.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('locallib.php');
global $CFG, $PAGE, $DB;
$iid          = optional_param('iid', '', PARAM_INT);
$previewrows  = optional_param('previewrows', 10, PARAM_INT);
$quizid       = required_param('quizid', PARAM_INT);
$courseid     = required_param('courseid', PARAM_INT);
$cmid         = required_param('cmid', PARAM_INT);
$coursemodule = get_coursemodule_from_id('quiz', $cmid);
$context      = context_module::instance($cmid);
$quizname     = $coursemodule->name;
require_login($courseid, false, $coursemodule);
require_capability('mod/quiz:manage', $context);
core_php_time_limit::raise(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);
$returntomanageurl = new moodle_url('/mod/quiz/accessrule/useripmapping/managemappings.php', array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$returnurl         = new moodle_url('/mod/quiz/accessrule/useripmapping/importmappings.php', array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$PAGE->set_title(get_string('importmapping', 'quizaccess_useripmapping'));
$PAGE->set_heading(get_string('importmapping', 'quizaccess_useripmapping'));
$PAGE->set_url($CFG->wwwroot . '/mod/quiz/accessrule/useripmapping/importmappings.php', array(
    'quizid' => $quizid,
    'courseid' => $courseid,
    'cmid' => $cmid
));
$PAGE->navbar->add(get_string('accessrules', 'quizaccess_useripmapping'), null);
$PAGE->navbar->add(get_string('useripmapping', 'quizaccess_useripmapping'), $returntomanageurl);
$PAGE->navbar->add(get_string('importmapping', 'quizaccess_useripmapping'), $returnurl);
$stdfields       = array(
    'username',
    'ip'
);
$quizdata         = array(
    'quizid' => $quizid
);
$coursedata       = array(
    'courseid' => $courseid
);
$coursemoduledata = array(
    'cmid' => $cmid
);
if (empty($iid)) {
    $mform = new quizaccess_upload_useripmapping_list();
    $mform->set_data($quizdata);
    $mform->set_data($coursedata);
    $mform->set_data($coursemoduledata);
    if ($formdata = $mform->is_cancelled()) {
        redirect($returntomanageurl);
    } else if ($formdata = $mform->get_data()) {
        $iid          = csv_import_reader::get_new_iid('uploadmappings');
        $cir          = new csv_import_reader($iid, 'uploadmappings');
        $content      = $mform->get_file_content('file');
        $readcount    = $cir->load_csv_content($content, $formdata->encoding, $formdata->csvdelimiter);
        $csvloaderror = $cir->get_error();
        unset($content);
        if (!is_null($csvloaderror)) {
            print_error('csvloaderror', '', $returnurl, $csvloaderror);
        }
        $filecolumns = um_validate_user_mapping_columns($cir, $stdfields, $returnurl);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading_with_help(get_string('uploadmappings', 'quizaccess_useripmapping'), 'uploadmappings',
             'quizaccess_useripmapping');
        $mform->display();
        echo $OUTPUT->footer();
        die();
    }
} else {
    $cir         = new csv_import_reader($iid, 'uploadmappings');
    $filecolumns = um_validate_user_mapping_columns($cir, $stdfields, $returnurl);
}
$selectenrol = 'courseid = ' . $courseid;
$enrolids    = $DB->get_fieldset_select('enrol', 'id', $selectenrol);
$selectuser  = 'enrolid IN (' . implode(",", $enrolids) . ')';
$userids     = $DB->get_fieldset_select('user_enrolments', 'userid', $selectuser);
$mform2      = new quizaccess_store_useripmapping_list(null, array(
    'columns' => $filecolumns,
    'data' => array(
        'iid' => $iid,
        'previewrows' => $previewrows
    )
));
$mform2->set_data($quizdata);
$mform2->set_data($coursedata);
$mform2->set_data($coursemoduledata);
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    echo $OUTPUT->header();
    $cir->init();
    $linenum         = 1; // Column header is first line.
    $mappingsskipped = 0;
    $mappingsadded   = 0;
    while ($line = $cir->next()) {
        $selectusername = "username = '$line[0]'";
        $userid         = $DB->get_field_select('user', 'id', $selectusername);
        if (in_array($userid, $userids)) {
            $enrolled = true;
        } else {
            $enrolled = false;
        }
        $registered = $DB->record_exists('user', array(
            'username' => $line[0]
        ));
        if ($registered && $enrolled && !empty($line[0]) && !empty($line[1])) {
            $record         = new stdClass();
            $record->quizid = $quizid;
            foreach ($line as $key => $field) {
                $colname          = $filecolumns[$key];
                $record->$colname = trim($field);
            }
            $record->timecreated = time();
            $DB->insert_record('quizaccess_useripmappings', $record, $returnid = true, $bulk = false);
            $mappingsadded++;
        } else {
            $mappingsskipped++;
            continue;
        }
        $linenum++;
    }
    $cir->close();
    $cir->cleanup();
    if (!($mappingsskipped)) {
        echo "<div class='alert alert-success'><b>Well Done !<b><br>
All IP Mappings were successfuly added.</div>";
    } else if (!($mappingsadded)) {
        echo "<div class='alert alert-danger'><b>Error><b><br>
None of the IP Mappings were added.Please try again</div>";
    } else {
        echo "<div class='alert alert-warning'><b>Note</b><ol>
<li>No. of mappings skipped : $mappingsskipped</li>
<li>Rest of the IP Mappings were successfuly added.</li></ol></div>";
    }
    echo $OUTPUT->continue_button($returntomanageurl);
    echo $OUTPUT->footer();
    die();
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadmappingspreview', 'quizaccess_useripmapping'));
$data = array();
$cir->init();
$linenum              = 1; // Column header is first line.
$countofnotregistered = 0;
$countofenrolled      = 0;
$countofnotenrolled   = 0;
$missingusernames     = 0;
$missingips           = 0;
$bothfieldsmissing    = 0;
$bothfieldspresent    = 0;
while ($fields = $cir->next()) {
    $selectusername = "username = '$fields[0]'";
    $userid         = $DB->get_field_select('user', 'id', $selectusername);
    if (in_array($userid, $userids)) {
        $enrolled = true;
    } else {
        $enrolled = false;
    }
    $registered = $DB->record_exists('user', array(
        'username' => $fields[0]
    ));
    $rowcols    = array();
    if (empty($fields[0]) && empty($fields[1])) {
        $bothfieldsmissing++;
    } else if (empty($fields[0]) && !empty($fields[1])) {
        $missingusernames++;
    } else if (!empty($fields[0]) && empty($fields[1])) {
        $missingips++;
    } else {
        $bothfieldspresent++;
    }
    foreach ($fields as $key => $field) {
        $rowcols[$filecolumns[$key]] = s(trim($field));
    }
    if ($enrolled) {
        $rowcols['status'] = 'Enrolled';
        if ($linenum <= $previewrows) {
            $data[] = $rowcols;
        }
        $countofenrolled++;
    } else if (!($registered)) {
        $rowcols['status'] = 'Not Registered';
        if ($linenum <= $previewrows) {
            $data[] = $rowcols;
        }
        $countofnotregistered++;
    } else {
        $rowcols['status'] = 'Not Enrolled';
        if ($linenum <= $previewrows) {
            $data[] = $rowcols;
        }
        $countofnotenrolled++;
    }
    $linenum++;
}
if ($previewrows <= $linenum) {
    $data[] = array_fill(0, count($fields) + 5, '...');
}
$table                      = new html_table();
$table->id                  = "useriplistpreview";
$table->attributes['class'] = 'generaltable';
$table->head                = array(
    'Username',
    'IP address',
    'User status'
);
$table->data                = $data;
echo html_writer::tag('div', html_writer::table($table), array(
    'class' => 'flexible-wrap'
));
echo "<div class='alert alert-info'>
<b>File Summary</b>
<br>
Total no. of entries in the file : " . ($readcount - 1) . "</div>";
if ($countofnotenrolled > 0 || $countofnotregistered > 0) {
    echo "<div class='alert alert-warning'>
<b>Warning</b><br>Following mappings will be skipped<ol>";
    if ($countofnotenrolled > 0) {
        echo "<li>Number of users who are not enrolled in this course : $countofnotenrolled</li>";
    }
    if ($countofnotregistered > 0) {
        echo "<li>Number of users who are not registered on this moodle instance : $countofnotregistered</li>";
    }
    echo "</ol></div>";
}
if ($missingusernames > 0 || $missingips > 0 || $bothfieldsmissing > 0) {
    echo "<div class='alert alert-danger'>
<b>Error</b><ol>";
    if ($missingusernames > 0) {
        echo "<li>No. of entries with only Username Missing : $missingusernames</li>";
    }
    if ($missingips > 0) {
        echo "<li>No. of entries with only IP Address Missing : $missingips</li>";
    }
    if ($bothfieldsmissing > 0) {
        echo "<li>No. of entries with both Username and IP Address Missing : $bothfieldsmissing</li>";
    }
    echo "</ol></div>";
}
if ($missingusernames == 0 && $missingips == 0 && $bothfieldsmissing == 0 &&
    $countofnotenrolled == 0 && $countofnotregistered == 0) {
    echo "<div class='alert alert-success'><b>Perfect !</b><br>All entries found to be in accepted format</div>";
}
$missingusernamesdata  = array(
    'missingusernames' => $missingusernames
);
$missingipsdata        = array(
    'missingips' => $missingips
);
$bothfieldsmissingdata = array(
    'bothfieldsmissing' => $bothfieldsmissing
);
$mform2->set_data($missingusernamesdata);
$mform2->set_data($missingipsdata);
$mform2->set_data($bothfieldsmissingdata);
$mform2->display();
echo $OUTPUT->footer();
die();