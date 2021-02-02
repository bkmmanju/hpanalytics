<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Handles uploading files
 *
 * @package    local_trainer_analysis
 * @copyright  Manjunath<manjunath@elearn10.com>
 * @copyright  Dhruv Infoline Pvt Ltd <lmsofindia.com>
 * @license    http://www.lmsofindia.com 2017 or later
 */





require_once('../../config.php');
require_once('lib.php'); 
require_once($CFG->dirroot.'/local/hpanalytics/csslinks.php');
global $OUTPUT, $CFG, $USER;
require_login(true);
$context = context_system::instance();
$PAGE->set_context($context);
$capmanger = has_capability('local/hpanalytics:manager',$context);


$PAGE->set_url($CFG->wwwroot . '/local/hpanalytics/ebscorpt.php');
$title = get_string('title_ebsco', 'local_hpanalytics');
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
include_once('jslinktable.php');
echo $OUTPUT->header();
$jslink='';
$type = optional_param('type', '', PARAM_TEXT);

//student sql last two days
if ($type == 'student') {
	$sql = "SELECT u.id,username,email,firstname,lastname,ui.data as zone, ml.action as action,
	FROM_UNIXTIME(ml.timecreated) AS days
	FROM {logstore_standard_log} as ml
	JOIN {user} as u ON u.id = ml.userid
	JOIN {user_info_data} AS ui ON ml.userid = ui.userid
	WHERE DATEDIFF( NOW(),FROM_UNIXTIME(ml.timecreated) ) < 2
	AND ui.fieldid = 16
	AND ui.data != ''
	AND (action = 'loggedin' OR action = 'loggedout')
	ORDER BY username,days";

	$result = $DB->get_records_sql($sql);
	//for datatable.
	$table  = new \html_table();
	$table->id = 'studenttable';
	$table->head = array(get_string('username'),
		get_string('firstname'),
		get_string('lastname'),
		get_string('email'),
		get_string('zone'),
		get_string('action'),
		get_string('days'),
	);
	$result = $DB->get_records_sql($sql);
	foreach ($result as $row) {
		$table->data[] = array($row->username,$row->firstname,$row->lastname,$row->email,$row->zone,$row->action,$row->days);
	}
	$datatable='';
	$datatable .= html_writer::start_div('container pt-5');
	$datatable .= html_writer::start_div('row');
	$datatable .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$datatable .= html_writer::table($table);
	$datatable .= html_writer::end_div();
	$datatable .= html_writer::end_div();//end row
	$datatable .= html_writer::end_div();//end container
	echo $datatable;

} else if ($type == 'trainer') {
	//teachers
$sql = "SELECT u.id,username,email,firstname,lastname,u.icq, ml.action as action,
	FROM_UNIXTIME(ml.timecreated) AS days
	FROM {logstore_standard_log} as ml
	JOIN {user} as u ON u.id = ml.userid
	WHERE DATEDIFF( NOW(),FROM_UNIXTIME(ml.timecreated) ) < 2
	AND u.icq = 'Trainer'
	AND (action = 'loggedin' OR action = 'loggedout')
	ORDER BY username,days";

	//for datatable.
	$table  = new \html_table();
	$table->id = 'trainertable';
	$table->head = array(get_string('username'),
		get_string('firstname'),
		get_string('lastname'),
		get_string('email'),
		get_string('role'),
		get_string('action'),
		get_string('days'),
	);
	$result = $DB->get_records_sql($sql);
	foreach ($result as $row) {
		$table->data[] = array($row->username,$row->firstname,$row->lastname,$row->email,$row->icq,$row->action,$row->days);
	}
	$datatable='';
	$datatable .= html_writer::start_div('container pt-5');
	$datatable .= html_writer::start_div('row');
	$datatable .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$datatable .= html_writer::table($table);
	$datatable .= html_writer::end_div();
	$datatable .= html_writer::end_div();//end row
	$datatable .= html_writer::end_div();//end container
	echo $datatable;
} else if ($type == 'ebsco') {
	//teachers
$sql = "SELECT us.id as slid,username,u.id,email,firstname,lastname,u.department, us.searchterm, us.searchtime,
FROM_UNIXTIME(us.searchtime) AS days
FROM {user_searchterms} us
JOIN {user} u ON u.id = us.userid
ORDER BY us.searchtime,days";
	

	//for datatable.
	$table  = new \html_table();
	$table->id = 'trainertable';
	$table->head = array(
		get_string('username'),
		get_string('fullname'),
		get_string('email'),
		get_string('department'),
		get_string('ebsco_search_term','local_hpanalytics'),
		get_string('ebsco_search_time','local_hpanalytics'),
	);
	$result = $DB->get_records_sql($sql);
	//$result = $DB->get_records_sql($sql10nov);
	//echo count($result);
	foreach ($result as $row) {
		
		//get user details 
		
	//	$rptuser = $DB->get_record('user', array('id' => $row->id));
		
		$formate = '%m/%d/%Y';
		$date2 = userdate($row->searchtime,$formate);
	
			//$table->data[] = array($rptuser->username,$rptuser->firstname.' '.$rptuser->lastname,$rptuser->email,
			
		$table->data[] = array($row->username,$row->firstname.' '.$row->lastname,$row->email,
		$rptuser->department,
		$row->searchterm,
		$date2
	);
	}
	$datatable='';
	$datatable .= html_writer::start_div('container pt-5');
	$datatable .= html_writer::start_div('row');
	$datatable .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$datatable .= html_writer::table($table);
	$datatable .= html_writer::end_div();
	$datatable .= html_writer::end_div();//end row
	$datatable .= html_writer::end_div();//end container
	echo $datatable;
}



echo $OUTPUT->footer();
