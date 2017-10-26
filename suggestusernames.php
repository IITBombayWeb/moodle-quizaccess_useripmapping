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
 * Suggest list of usernames based on a particular string for the quizaccess_useripmapping plugin.
 *
 * @package    quizaccess_useripmapping
 * @author     Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright  2017 Indian Institute Of Technology,Bombay,India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
require_login();
global $DB;

if (!empty($_POST["keyword"])) {
    $name        = $_POST["keyword"];
    $usersql = "SELECT * FROM (SELECT CONCAT(firstname, ' ', lastname) as fullname FROM mdl_user) base
                WHERE fullname LIKE '%" . $name . "%' ";
    $usernames   = $DB->get_fieldset_sql($usersql);
    if (count($usernames) > 0) {
        foreach ($usernames as $username) {
            echo "<li>$username</li>";
        }
    }
}
