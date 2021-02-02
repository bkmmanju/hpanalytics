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
require_once($CFG->libdir.'/formslib.php');
class local_hpanalytics_form extends moodleform {
    function definition() {
        global $CFG,$DB,$USER,$PAGE,$OUTPUT;
        $mform =& $this->_form;

        $cid = $this->_customdata['cid']; // this contains the data of this form to get the course id
        
        $mform->addElement('header', 'headername', get_string('select', 'local_hpanalytics'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT); 
        //manju: courses from category 2 are removed from dropdown.
        $coursequery ="SELECT id,fullname from {course}
                        WHERE visible = 1 
                        AND category != 2";
        // $courses = $DB->get_records('course',array('visible' => 1,'category'=>),'id,fullname');
        $courses = $DB->get_records_sql($coursequery);
      

        
        $course_options = array('' => get_string('selectcourse','local_hpanalytics'));
        //Sangita : jan 16 2020 : add all course option in select box
        $course_options[0] =  get_string('selectall','local_hpanalytics');
        foreach($courses as $course){
            $course_options[$course->id] = $course->fullname;
        }
       
        $select = $mform->addElement('select', 'courseid', get_string('course'), $course_options,'required');
        $mform->addRule('courseid', get_string('missingcourse','local_hpanalytics'), 'required','server');

        $date_options = array(
         'startyear' => 2010, 
         'stopyear'  => 2050,
         'timezone'  => 99,
         'optional'  => false
     );

        $mform->addElement('date_selector', 'reportstart', get_string('from'), $date_options);
        $mform->addElement('date_selector', 'reportend', get_string('to'), $date_options);

//manju:commented on 19/11/2019
        // $mform->addElement('header', 'headername', get_string('selectothers', 'local_hpanalytics'));
        // $zones = $DB->get_records('course',array('visible' => 1),'id,fullname');
        // $zones_options = array('' => get_string('selectcourse','local_hpanalytics'));
        // foreach($zones as $zone){
        //     $zones_options[$zone->id] = $zone->fullname;
        // }
        // $select = $mform->addElement('select', 'zoneid', get_string('zone','local_hpanalytics'), $zones_options);

        //manjunath: for institution dropdown
        
       
        $mform->addElement('header', 'institutionzone', get_string('selectzone', 'local_hpanalytics'));
        $sql = "SELECT DISTINCT institution from {user}";
        $institution = $DB->get_records_sql($sql);

        $institution_options = array('' => get_string('selectzone','local_hpanalytics'),'10103'=> 'Hindustan Bhavan','10501' => 'Petroleum House',  '11100' =>'North Zone',  '11120'=>'North Central Zone', '11350'=> 'West Zone','11370'=> 'North West Zone','11600'=>'East Zone', '11750'=>'South Zone','11770'=>'South Central Zone','12620'=> 'South Central LPG Zone', '46000'=>'   Visakha Refinery','48000'=>'Mumbai Refinery');

        // $institution_options = array('' => get_string('institution','local_hpanalytics'));
        // foreach($institution as $institutes){
        //     $institution_options[$institutes->institution] = $institutes->institution;
        // }
        $select = $mform->addElement('select', 'institution', get_string('zone','local_hpanalytics'), $institution_options);
     

        //manjunath: for city dropdown
        $mform->addElement('header', 'headername', get_string('selectcity', 'local_hpanalytics'));
        $sql = "SELECT DISTINCT city from {user}";
        $cities = $DB->get_records_sql($sql);
        $city_options = array('' => get_string('city','local_hpanalytics'));
        foreach($cities as $city){
            $city_options[$city->city] = $city->city;
        }
        $select = $mform->addElement('select', 'city', get_string('selectcity','local_hpanalytics'), $city_options);



//manjunath: for departments dropdown
        $mform->addElement('header', 'headername', get_string('selectdepartments', 'local_hpanalytics'));
        $sql = "SELECT DISTINCT department from {user}";
        $department = $DB->get_records_sql($sql);
        $department_options = array('' => get_string('departments','local_hpanalytics'));
        foreach($department as $departments){
            $department_options[$departments->department] = $departments->department;
        }
        $select = $mform->addElement('select', 'department', get_string('departments','local_hpanalytics'), $department_options);
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = $mform->createElement('submit', 'downloadexcel', get_string('downloadexcel','local_hpanalytics'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }
}
