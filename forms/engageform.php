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
 * You may have settings in your plugin
 *
 * @package    local_hpanalytics
 * @copyright  Manjunath B K <manjunathbk@elearn10.com>
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); 
}
global $OUTPUT, $CFG;
require_once($CFG->libdir.'/formslib.php');
class local_hpanalytics_engage_form extends moodleform {
    function definition() {
        global $CFG,$DB,$USER,$PAGE,$OUTPUT;
        $id = $USER->id;
        $context = context_system::instance();
        $mform =& $this->_form;
        $date_options = array(
            'startyear' => 2010, 
            'stopyear'  => 2050,
            'timezone'  => 99,
            'optional'  => false
        );
        $mform->addElement('date_selector', 'reportstart', get_string('fromdate','local_hpanalytics'), $date_options);
        $mform->addElement('date_selector', 'reportend', get_string('todate','local_hpanalytics'), $date_options);

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = $mform->createElement('submit', 'downloadexceleng', get_string('downloadexcel','local_hpanalytics'));
        $buttonarray[] = $mform->createElement('submit', 'magzterreport', get_string('magzterreport','local_hpanalytics'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
}
