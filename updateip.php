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
 * Updates mapped IP Address for the quizaccess_useripmapping plugin.
 *
 * @package quizaccess_useripmapping
 * @author  Amrata Ramchandani <ramchandani.amrata@gmail.com>
 * @copyright 2017 Indian Institute Of Technology,Bombay,India
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
require_login();
global $DB;
$quizid              = $_POST['quizid'];
$username            = $_POST['username'];
$ip                  = $_POST['ip'];



$record              = new stdClass();
$record->quizid      = $quizid;
$record->username    = $username;
$record->ip          = $ip;
$record->timecreated = time();
$DB->insert_record('quizaccess_useripmappings', $record);
