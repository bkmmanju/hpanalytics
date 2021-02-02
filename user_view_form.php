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
 * @copyright  2014 Daniel Neis
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page
}
global $OUTPUT, $CFG;
require_once($CFG->libdir.'/formslib.php');
class local_hpanalytics_form extends moodleform {
    function definition() {
        global $CFG,$DB,$USER,$PAGE,$OUTPUT;
        $id = $USER->id;
        $context = context_system::instance();
        $capmanger = has_capability('local/hpanalytics:manager',$context);
        $mform =& $this->_form;
        $mform->addElement('header', 'headername', get_string('select', 'local_hpanalytics'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT); 
        if(is_siteadmin()){

        $usersql = 'SELECT * FROM {user} WHERE deleted!=1 and suspended!=1 GROUP BY firstname ASC';
        $userdetails = $DB->get_records_sql($usersql, array(1));
        //manju:changed array logic here on 19/11/2019
        $user_options[0] = get_string('pleaseselectuser', 'local_hpanalytics');
        foreach($userdetails as $uid => $users){
            $user_options[$users->id] = fullname($users);
        }
        $selectuser = get_string('pleaseselectuser', 'local_hpanalytics');
        $mergervalue = $user_options+$user_options;
        $options = array(                           
            'multiple' => false,                               
            'noselectionstring' => get_string('selectuser', 'local_hpanalytics'),
            'placeholder' => get_string('selectuser', 'local_hpanalytics'),                                                              
        );
        if(!empty($options)) {
            $select = $mform->addElement('autocomplete', 'userid', get_string('selectuser','local_hpanalytics'), $mergervalue,$options);
        } 
        
            }

        $date_options = array(
            'startyear' => 2010, 
            'stopyear'  => 2050,
            'timezone'  => 99,
            'optional'  => false
        );
        $mform->addElement('date_selector', 'reportstart', get_string('from'), $date_options);
        $mform->addElement('date_selector', 'reportend', get_string('to'), $date_options);

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
}
