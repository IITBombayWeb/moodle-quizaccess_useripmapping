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
 * Defines the various forms used by quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
/**
 * Upload a CVS file with user-ip mapping information.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_upload_useripmapping_list extends moodleform
{
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('filepicker', 'file', get_string('file', 'quizaccess_useripmapping'));
        $mform->addRule('file', null, 'required');
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'csvdelimiter', get_string('csvdelimiter', 'quizaccess_useripmapping'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('csvdelimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('csvdelimiter', 'semicolon');
        } else {
            $mform->setDefault('csvdelimiter', 'comma');
        }
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'quizaccess_useripmapping'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $choices = array(
            '10' => 10,
            '20' => 20,
            '100' => 100,
            '1000' => 1000,
            '100000' => 100000
        );
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'quizaccess_useripmapping'), $choices);
        $mform->setType('previewrows', PARAM_INT);
        $mform->addElement('hidden', 'quizid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'cmid');
        $this->add_action_buttons(true, get_string('uploadmappings', 'quizaccess_useripmapping'));
    }
}
/**
 * Form to add the user-ip mappings after pre-checks.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_store_useripmapping_list extends moodleform
{
    public function definition() {
        $mform   = $this->_form;
        $columns = $this->_customdata['columns'];
        $data    = $this->_customdata['data'];
        // Hidden fields.
        $mform->addElement('hidden', 'iid');
        $mform->setType('iid', PARAM_INT);
        $mform->addElement('hidden', 'previewrows');
        $mform->setType('previewrows', PARAM_INT);
        $mform->addElement('hidden', 'quizid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'cmid');
        $mform->addElement('hidden', 'missingusernames');
        $mform->addElement('hidden', 'missingips');
        $mform->addElement('hidden', 'bothfieldsmissing');
        $actionbuttons = array();
        $actionbuttons[] =& $mform->createElement('submit', 'submit', get_string('confirm', 'quizaccess_useripmapping'));
        $actionbuttons[] =& $mform->createElement('cancel', 'cancel', get_string('cancel', 'quizaccess_useripmapping'));
        $mform->addGroup($actionbuttons, 'actionbuttons', '', array(
            ' '
        ), false);
        $mform->disabledIf('submit', 'missingusernames', 'neq', 0);
        $mform->disabledIf('submit', 'missingips', 'neq', 0);
        $mform->disabledIf('submit', 'bothfieldsmissing', 'neq', 0);
        $this->set_data($data);
    }
}
/**
 * Form to edit the user-ip mapping.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_edit_useripmapping_list extends moodleform
{
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('text', 'username', get_string('username', 'quizaccess_useripmapping'));
        $mform->addElement('html', '<div form-group row  fitem id="hide1" >');
        $mform->addElement('html', '<div class="col-md-3" >');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div id="suggestionbox" class="col-md-9" >');
        $mform->addElement('html', '<ul id="userlist">');
        $mform->addElement('html', '</ul>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('text', 'idnumber', get_string('idnumber', 'quizaccess_useripmapping'));
        $mform->addElement('hidden', 'quizid');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'cmid');
        $this->add_action_buttons(true, get_string('viewthelist', 'quizaccess_useripmapping'));
    }
}