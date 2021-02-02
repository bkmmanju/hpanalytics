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
 * Version details.
 *
 * @package   local_hpanalytics
 * @copyright 2019 Manjunath B K
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $USER,$DB,$COURSE;
defined('MOODLE_INTERNAL') || die();

/**
*@ id is courseid
*@startdate form startdate
*@enddate form enddate
*@Return $enrollcnt will rreturn the count of total course enrollments.
*/

//adding menu Mihir 
function local_hpanalytics_extend_navigation(global_navigation $nav) {

	global $CFG,$USER;
	$systemcontext = context_system::instance();
	$capability = has_capability('local/hpclreport:view',$systemcontext);
	$nav->showinflatnavigation = true;
	if($capability){
		$abc = $nav->add(get_string('analytics','local_hpanalytics'),$CFG->wwwroot.'/local/hpanalytics/view.php'); 
		$abc->showinflatnavigation = true;
		
		$xyz = $nav->add(get_string('engagement','local_hpanalytics'),$CFG->wwwroot.'/local/hpanalytics/engageview.php'); 
		$xyz->showinflatnavigation = true;
		
		$xyz = $nav->add(get_string('title_ebsco','local_hpanalytics'),$CFG->wwwroot.'/local/hpanalytics/ebscorpt.php?type=ebsco'); 
		$xyz->showinflatnavigation = true;
		
		$xyz = $nav->add(get_string('date_report','local_hpanalytics'),$CFG->wwwroot.'/local/course_hours/report.php'); 
		$xyz->showinflatnavigation = true;
	}

	if( ($USER->username == '31969130') || ($USER->username == '31909080')){
		
		$xyz = $nav->add(get_string('title_ebsco','local_hpanalytics'),$CFG->wwwroot.'/local/hpanalytics/ebscorpt.php?type=ebsco'); 
		$xyz->showinflatnavigation = true;
	}
}
//manju: changed logic as per instructions.[29/01/2020].
function get_enrollment_data($id, $startdate, $enddate){
	global $DB;
	$enrollcnt = count($DB->get_records('course_completions',array('course'=>$id)));
	return $enrollcnt;
}

//manjunath: this function returns the course name.
function get_course_name($id){
	global $DB;
	$coursedata = $DB->get_record('course',array('id'=>$id));
	$coursename = $coursedata->fullname;//total enrollment count
	return $coursename;
}
//manjuanth: this function returns the all badge count related to the search results
function get_badge_data($id, $coursename, $startdate, $enddate, $city, $institution, $department)
{
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	$goldbadge = '';
	$silverbadge = '';
	$bronzebadge = '';
	$completionbadge = '';
	$data = $DB->get_records('badge', array('courseid'=>$id));
	if(!empty($data)){
		foreach ($data as $badge) {
			$badgename = $string = str_replace(' ', '', $badge->name);
			$coursename = $string = str_replace(' ', '', $coursename);
//			echo $badgename;

			if(($badgename === 'Gold-'.$coursename))
			{
				$goldbadge = $badge->id;
			}
			if($badgename === 'Silver-'.$coursename)
			{
				$silverbadge = $badge->id;
			}
			if($badgename === 'Bronze-'.$coursename)
			{
				$bronzebadge = $badge->id;
			}
			if($badgename === 'CompletionBadge-'.$coursename)
			{
				$completionbadge = $badge->id;
			}
		}
	}
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$department%'";
	}
	if(!empty($goldbadge)){
		$goldquery = "SELECT c.dateissued,c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
		FROM {badge_issued} c
		INNER JOIN {user} u ON u.id = c.userid
		WHERE (c.dateissued between $startdate and $enddate) 
		AND (c.badgeid = '$goldbadge')
		$instquery
		$cityquery
		$departquery
		";
		$recordsarray = $DB->get_records_sql($goldquery);
		$records = count($DB->get_records_sql($goldquery));
		$goldbadgecount = $records;
	}else{
		$goldbadgecount =0;
	}
	if(!empty($silverbadge)){
		$silverquery = "SELECT c.dateissued,c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
		FROM {badge_issued} c
		INNER JOIN {user} u ON u.id = c.userid
		WHERE (c.dateissued between $startdate and $enddate) 
		AND (c.badgeid = '$silverbadge')
		$instquery
		$cityquery
		$departquery
		";
		$records = count($DB->get_records_sql($silverquery));
		$silverbadgecount = $records;
	}else{
		$silverbadgecount=0;
	}
	if(!empty($bronzebadge)){
		$bronzequery = "SELECT c.dateissued,c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
		FROM {badge_issued} c
		INNER JOIN {user} u ON u.id = c.userid
		WHERE (c.dateissued between $startdate and $enddate) 
		AND (c.badgeid = '$bronzebadge')
		$instquery
		$cityquery
		$departquery
		";
		$records = count($DB->get_records_sql($bronzequery));
		$bronzebadgecount = $records;
	}else{
		$bronzebadgecount=0;
	}
	if(!empty($completionbadge)){
		$completequery = "SELECT c.dateissued,c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
		FROM {badge_issued} c
		INNER JOIN {user} u ON u.id = c.userid
		WHERE (c.dateissued between $startdate and $enddate) 
		AND (c.badgeid = '$completionbadge')
		$instquery
		$cityquery
		$departquery
		";
		$records = count($DB->get_records_sql($completequery));
		if(!empty($records)){
			$completionbadgecount = $records;
		}
	}else{
		$completionbadgecount=0;
	}
	$badgestatistics = array($goldbadgecount, $silverbadgecount, $bronzebadgecount, $completionbadgecount);
	return $badgestatistics;
}
//manjunath: this function returns the certificate count 
function get_certificate($courseid, $start, $end, $city=null, $institution=null, $department=null)
{
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$department%'";
	}
	$certificate_data = $DB->get_record('simplecertificate', array('course'=>$courseid));
	if(!empty($certificate_data)){
		$certid = $certificate_data->id;
	}
	if(!empty($certid)){
		$sql = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
		FROM {simplecertificate_issues} c 
		INNER JOIN {user} u ON u.id = c.userid 
		WHERE c.timecreated between $start and $end and c.certificateid = $certid 
		$instquery
		$cityquery
		$departquery
		";
		$certificate_count = count($DB->get_records_sql($sql));
		return $certificate_count;
	}
}
//manjunath: course completion, in-progress and not started statistics.
function course_completion_stats($courseid, $start, $end ,$city, $institution, $department)
{
	global $DB;
//manjunath: getting total count of users.
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$department%'";
	}
	$totalquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.course = '$courseid')
	$instquery
	$cityquery
	$departquery
	";
	$records = count($DB->get_records_sql($totalquery));
	$totalcount = $records;
//manjunath: coures completed users count.
	$completedquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.course = '$courseid')
	AND (c.timecompleted is not null)
	$instquery
	$cityquery
	$departquery
	";
	$completedrecords = count($DB->get_records_sql($completedquery));
	$completedcount = $completedrecords;

//manjunath: course in progress users count.
	$progressquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted,c.timestarted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.course = '$courseid')
	AND (c.timestarted != 0)
	AND (c.timecompleted is null)
	$instquery
	$cityquery
	$departquery
	";
	$progessrecords = count($DB->get_records_sql($progressquery));
	$progresscount = $progessrecords;

//manjunath: course not started users count
	$notstartedquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted,c.timestarted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.course = '$courseid')
	AND (c.timestarted = 0)
	AND (c.timecompleted is null)
	$instquery
	$cityquery
	$departquery
	";
	$notstartedrecords = count($DB->get_records_sql($notstartedquery));
	$notstartedcount = $notstartedrecords;
	$enrollmentstats = array($totalcount,$completedcount,$progresscount,$notstartedcount);
	return $enrollmentstats;
}

//manjunath: this function returns the enrollment graph data
function graphdata_enrollment($id, $startdate, $enddate, $city, $institution, $department)
{
	global $DB;
//Manju: @$enddate is the first enrollment date to the course.[31/01/2020]
	$allenrolldates = all_enrolled_usersdata($id);
	if(!empty($allenrolldates)){
		$enddate = $allenrolldates['0'];
	}else{
		$coursedetails = $DB->get_record('course',array('id'=>$id));
		$enddate = $coursedetails->timecreated;
	}
	
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$department%'";
	}
	$newstdate = date('Ym',$enddate);
	$sql ="
	SELECT ue.id, u.id as uid, c.id as cid, u.city, u.institution, u.department, FROM_UNIXTIME(ue.timestart, '%Y%m') as timeconvert
	FROM {user} u
	INNER JOIN {user_enrolments} ue ON ue.userid = u.id
	INNER JOIN {enrol} e ON e.id = ue.enrolid
	INNER JOIN {course} c ON e.courseid = c.id
	WHERE ue.timestart >= $enddate and e.courseid = $id
	ORDER BY timeconvert desc
	";
	$array_value = [];
	$graphdata = $DB->get_records_sql($sql);
	
	if(!empty($graphdata)){
		foreach ($graphdata as $key => $value1) {
			$array_value[] =$value1->timeconvert; 
		}
	}
	$returnarray = array();
	for($i=0; $i<=6 ;$i++) {
		if($i == 0){
			$timevalue = $newstdate;
		}else{
			$timevalue = $newstdate + $i ;
		}
		$arraykey = substr($timevalue,4);
		if(in_array($timevalue,$array_value)){
			$uid_counts = array_count_values($array_value);
			$enrolcount = $uid_counts[$timevalue];
		}else{
			$enrolcount = 0;
		}
		$dateObj   = DateTime::createFromFormat('!m', $arraykey);
		$monthName = $dateObj->format('M');
		$returnarray[$monthName] = $enrolcount;
	}
	return $returnarray;
}
//manjunath: this function return the completion graph data
function graphdata_completions($id, $startdate, $enddate, $city, $institution, $department)
{
	global $DB;
//get the year and month from the start date
	$completiondatequery = "SELECT timeenrolled FROM {course_completions} WHERE course = $id AND timecompleted IS NOT NULL ORDER BY timeenrolled ASC LIMIT 1";
	$coursecompledate = $DB->get_record_sql($completiondatequery);
	if(!empty($coursecompledate)){
		$enddate = $coursecompledate->timeenrolled;
	}else{
		$coursedetails = $DB->get_record('course',array('id'=>$id));
		$enddate = $coursedetails->timecreated;
	}
	//filters.
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$department%'";
	}
	$newstdate = date('Ym',$enddate);
	$sql ="SELECT u.*, FROM_UNIXTIME(cc.timeenrolled, '%Y%m') as timeconvert FROM {course_completions} cc
	JOIN {user} u ON u.id = cc.userid WHERE cc.timeenrolled >= $enddate and cc.course = $id and timecompleted is not null 
	$instquery
	$cityquery
	$departquery
	ORDER BY timeconvert desc";
	$completegraphdata = $DB->get_records_sql($sql);
	$array_value1 = [];
	if(!empty($completegraphdata)){
		foreach ($completegraphdata as $key => $value1) {
			$array_value1[] =$value1->timeconvert; 
		}

	}
	$returndata = array();
	for($i=0; $i<=6 ;$i++) {
		if($i==0){
			$timevalue = $newstdate;
		}else{
			$timevalue = $newstdate + $i ;
		}
		$arraykey = substr($timevalue,4);
		if(in_array($timevalue,$array_value1)){
			$uid_counts = array_count_values($array_value1);
			$coursecompletioncount = $uid_counts[$timevalue];
		}else{
			$coursecompletioncount = 0;
		}
		$dateObj   = DateTime::createFromFormat('!m', $arraykey);
		$monthName = $dateObj->format('M');
		$returndata[$monthName] = $coursecompletioncount;
	}
	return $returndata;
}
//Sangita : Jan 16 2020 :This function is used to count total user enrollment in all course
function get_enrollment_count($allcourseids,$data){
	global $DB;
	$institution ='';
	if($data->institution){
		$institution = $data->institution;
	}
	$city ='';
	if($data->city){
		$city = $data->city;
	}
	$department ='';
	if($data->department){	
		$department = $data->department;
	}	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	foreach ($allcourseids as $key => $allcourseid) {
		$allenrolls[] = get_course_enroll($allcourseid,$startdate,$enddate,$institution,$city,$department);
	}
	$courseenrollcount=array();
	foreach ($allenrolls as $key => $allenroll) {
		if(!empty($allenroll)){
			$courseenrollcount[$allenroll->fullname] = $allenroll->enroled;
		}
	}
	$allcourseenrolleduser = array_sum($courseenrollcount);
	$maxvaluaarray = find_dessending_array($courseenrollcount);
	$minvaluaarray = find_assending_array($courseenrollcount);
	$finalrecord = array('maxrecod'=>$maxvaluaarray,'minrecod'=>$minvaluaarray,'totalenrolluser'=>$allcourseenrolleduser);
	return $finalrecord;
} 
//find the maximum value array
function find_dessending_array($courseenrollcountval){
	global $DB;
	arsort($courseenrollcountval);
	array_splice($courseenrollcountval, 5);
	return $courseenrollcountval;
}
//find the minimum value array
function find_assending_array($coursecountval){
	global $DB;
	asort($coursecountval);
	array_splice($coursecountval, 5);
	return $coursecountval;
}
//this function is used for enrollment of user
function get_course_enroll_dedication($cid,$startdate,$enddate,$institution,$city,$department){
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}
	$query = "SELECT ue.id as enid, u.id FROM {course} c 
	JOIN {enrol} en ON en.courseid = c.id 
	JOIN {user_enrolments} ue ON ue.enrolid = en.id
	JOIN {user} u ON ue.userid = u.id 
	Where c.id = ? AND (ue.timecreated>=? AND ue.timecreated<=?) 
	$instquery
	$cityquery
	$departquery
	";
	$record = $DB->get_records_sql($query,array($cid,$startdate,$enddate));
	return $record; 
}
//this function is used for enrollment of user
function get_course_enroll($cid,$startdate,$enddate,$institution,$city,$department){
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}
	$query = "SELECT c.fullname, COUNT(ue.id) AS Enroled FROM {course} c 
	JOIN {enrol} en ON en.courseid = c.id 
	JOIN {user_enrolments} ue ON ue.enrolid = en.id
	JOIN {user} u ON ue.userid = u.id 
	Where c.id = ? AND (ue.timecreated>=? AND ue.timecreated<=?) 
	$instquery
	$cityquery
	$departquery
	GROUP BY c.id ORDER BY c.fullname
	";
	$record = $DB->get_record_sql($query,array($cid,$startdate,$enddate));
	return $record; 
}
//this function use to create html structure to display course enrollments records
function create_html_for_display_enrollment_records($allenrollmentcount){
	$maxarrayvalues = $allenrollmentcount['maxrecod'];
	$minarrayvalues = $allenrollmentcount['minrecod'];
	$data = html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments allmaxenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('highestcourseenroll', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body first-graph');
	if(!empty($maxarrayvalues)){
		$maxtable = new html_table();
		$maxtable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('enrollcount','local_hpanalytics'));
		foreach ($maxarrayvalues as $coursename => $maxarrayvalue) {
			$maxtable->data[] = array($coursename,$maxarrayvalue);
		}
		$data .= html_writer::table($maxtable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
	$data .= html_writer::start_div('card enrollments allminenrollments ');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('mincourseenrollment', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center text-white1 enroll');
	if(!empty($minarrayvalues)){
		$mintable = new html_table();
		$mintable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('enrollcount','local_hpanalytics'));
		foreach ($minarrayvalues as $coursename => $minarrayvalue) {
			$mintable->data[] = array($coursename,$minarrayvalue);
		}		
		$data .= html_writer::table($mintable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row
	$data .= html_writer::end_div();//end container
	return $data;
}
function get_course_completion_count($allcourseids,$data){
	global $DB;
	$institution ='';
	if($data->institution){
		$institution = $data->institution;
	}
	$city ='';
	if($data->city){
		$city = $data->city;
	}
	$department ='';
	if($data->department){	
		$department = $data->department;
	}	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	foreach ($allcourseids as $key => $allcourseid) {
		$allcompletioncounts[] = get_course_completion($allcourseid,$startdate,$enddate,$institution,$city,$department);
	}
	
	foreach ($allcompletioncounts as $allcompletioncount) {
		if(!empty($allcompletioncount)){
			$completionrecords[$allcompletioncount->fullname] = $allcompletioncount->completion;
		}
	}
	$maxvaluaarray = find_dessending_array($completionrecords);
	$minvaluaarray = find_assending_array($completionrecords);
	$finalcompletionrecord = array('maxrecod'=>$maxvaluaarray,'minrecod'=>$minvaluaarray);
	return $finalcompletionrecord;
	
}

function get_course_completion($allcourseid,$startdate,$enddate,$institution,$city,$department){
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}
	//check if start date and enddate are same.
	if($startdate == $enddate){
		$query ="SELECT c.fullname, COUNT(cc.id) AS completion FROM {course} c
		JOIN {course_completions} cc ON cc.course = c.id 
		JOIN {user} u ON cc.userid = u.id 
		Where cc.course = ? AND (cc.timecompleted>=?) AND cc.timecompleted is not null AND c.visible = 1  
		$instquery
		$cityquery
		$departquery
		GROUP BY c.id ORDER BY c.fullname";
		$record = $DB->get_record_sql($query,array($allcourseid,$startdate));
	}else{
		$query ="SELECT c.fullname, COUNT(cc.id) AS completion FROM {course} c
		JOIN {course_completions} cc ON cc.course = c.id 
		JOIN {user} u ON cc.userid = u.id 
		Where cc.course = ? AND (cc.timecompleted BETWEEN ? AND ? AND cc.timecompleted is not null AND c.visible = 1)
		$instquery
		$cityquery
		$departquery
		GROUP BY c.id ORDER BY c.fullname";

		$query111 = "";



		$query123="SELECT cc.id, u.username, u.firstname, u.lastname, u.email, ch.hours as CDuration, ch.hpclcategory,ch.coursecode, c.fullname, cc.timeenrolled as timeenrolled, 
		FROM_UNIXTIME(cc.timecompleted) as timecompleted,ch.facultycode,ch.learnigtype,ch.programtype,ch.vendor,ch.summativeassessment  
		FROM {course} AS c
		JOIN {hpcl_coursehours} as ch ON c.id = ch.course_id
		JOIN {course_completions} AS cc ON cc.course = c.id 
		JOIN {user} AS u ON u.id = cc.userid
		WHERE (cc.timecompleted BETWEEN '$startdate' AND '$enddate')
		ORDER by cc.timeenrolled";
		$record = $DB->get_record_sql($query,array($allcourseid,$startdate,$enddate));
	}
	return $record;
}
//this function use to create html structure to display course enrollments records
function create_html_for_display_completion_records($allenrollmentcount){
	$maxarrayvalues = $allenrollmentcount['maxrecod'];
	$minarrayvalues = $allenrollmentcount['minrecod'];
	$data = html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments allmaxenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('highestcoursecompletion', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body first-graph');
	if(!empty($maxarrayvalues)){
		$maxtable = new html_table();
		$maxtable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('completioncount','local_hpanalytics'));
		foreach ($maxarrayvalues as $coursename => $maxarrayvalue) {
			$maxtable->data[] = array($coursename,$maxarrayvalue);
		}
		$data .= html_writer::table($maxtable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
	$data .= html_writer::start_div('card enrollments allminenrollments ');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('mincoursecompletionb', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center text-white1 enroll');
	if(!empty($minarrayvalues)){
		$mintable = new html_table();
		$mintable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('completioncount','local_hpanalytics'));
		foreach ($minarrayvalues as $coursename => $minarrayvalue) {
			$mintable->data[] = array($coursename,$minarrayvalue);
		}		
		$data .= html_writer::table($mintable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row
	$data .= html_writer::end_div();//end container
	return $data;
}
//Sangita: Jan 17 2020:All  course completion, in-progress and not started statistics.
//$courseid, $start, $end ,$city, $institution, $department
function all_course_completion_statss($allcourseids,$data)
{
	global $DB;
//manjunath: getting total count of users.
	$institution ='';
	if($data->institution){
		$institution = $data->institution;
	}
	$city ='';
	if($data->city){
		$city = $data->city;
	}
	$department ='';
	if($data->department){	
		$department = $data->department;
	}	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	foreach ($allcourseids as $allcourseid) {
		$allrecords[] = find_all_course_records($allcourseid,$institution,$city,$department,$startdate,$enddate);
		$allcoursecomplets[] = find_all_course_completed_records($allcourseid,$institution,$city,$department,$startdate,$enddate);
		$allinprogress[] = find_all_inprogress_course_records($allcourseid,$institution,$city,$department,$startdate,$enddate);
		$allnotstarted[] = find_all_not_started_course_records($allcourseid,$institution,$city,$department,$startdate,$enddate);
	}
	$allcoursecompletion = array_sum($allrecords);
	$allcoursecompleted = array_sum($allcoursecomplets);
	$allcourseinprogress = array_sum($allinprogress);
	$allcoursenotstarted = array_sum($allnotstarted);
	$coursecompletionstatus = array('allcoursecompletion'=>$allcoursecompletion,'allcoursecpmleted'=>$allcoursecompleted,'allinprogresscourse'=>$allcourseinprogress,'allcoursenotstarted'=>$allcoursenotstarted);
	return $coursecompletionstatus;
}
//find all course comepletion record
function find_all_course_records($courseid=14,$institution=null,$city=null,$department=null,$startdate=null,$enddate=null){
	global $DB;

	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}

	$totalquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department
	FROM {course_completions} c
	JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $startdate and $enddate) 
	AND (c.course = '$courseid')
	$instquery
	$cityquery
	$departquery
	";
	$records = $DB->get_records_sql($totalquery);
	
	$totalcount = count($records);
	return $totalcount;

}
//find all couse completed 
function find_all_course_completed_records($courseid,$institution=null,$city=null,$department=null,$startdate=null,$enddate=null){
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}

	$completedquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $startdate and $enddate) 
	AND (c.course = '$courseid')
	AND (c.timecompleted is not null)
	$instquery
	$cityquery
	$departquery
	";
	$completedrecords = $DB->get_records_sql($completedquery);
	$completedcount = count($completedrecords);

	return $completedcount;
}
//this function is used to find all inprogress courses
function find_all_inprogress_course_records($courseid,$institution=null,$city=null,$department=null,$startdate=null,$enddate=null){
	global $DB;

	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}

	$progressquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted,c.timestarted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $startdate and $enddate) 
	AND (c.course = '$courseid')
	AND (c.timestarted != 0)
	AND (c.timecompleted is null)
	$instquery
	$cityquery
	$departquery
	";
	$progessrecords = $DB->get_records_sql($progressquery);
	$progresscount = count($progessrecords);

	return $progresscount;

}

//this function is used to find all couses not started by user
function find_all_not_started_course_records($courseid,$institution=null,$city=null,$department=null,$startdate=null,$enddate=null){
	global $DB;
	$instquery ='';
	$cityquery = '';
	$departquery ='';
	if (!empty($institution)) {
		$instquery = "AND u.institution LIKE '%$institution%'";
	}
	if(!empty($city)){
		$cityquery = "AND u.city LIKE '%$city%'";
	}
	if(!empty($department)){
		$departquery = "AND u.department LIKE '%$departquery%'";
	}

	$notstartedquery = "SELECT c.userid, u.username, u.firstname, u.lastname,u.institution,u.city,u.department,c.timecompleted,c.timestarted
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE (c.timeenrolled between $startdate and $enddate) 
	AND (c.course = '$courseid')
	AND (c.timestarted = 0)
	AND (c.timecompleted is null)
	$instquery
	$cityquery
	$departquery
	";
	$notstartedrecords = $DB->get_records_sql($notstartedquery);
	$notstartedcount = count($notstartedrecords);
	return $notstartedcount;

}


//total course enrollment display
function total_enroll_display_html($enrollmentdetails){
	
	$totalcount = $enrollmentdetails['totalenrolluser'];
	$data = '';
	$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
	$data .= html_writer::start_div('card totalenrollments ');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('totalenrolldetails', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body totalcountm text-white1 enroll');
	
	$data .= '<h1 class = totalcount>'.$totalcount.'</h1>';
	
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column
	
	return $data;
}

//find all course certificate
function get_all_course_certificate($allcourseids,$data){
	global $DB;
	$institution ='';
	if($data->institution){
		$institution = $data->institution;
	}
	$city ='';
	if($data->city){
		$city = $data->city;
	}
	$department ='';
	if($data->department){	
		$department = $data->department;
	}	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	foreach ($allcourseids as $allcourseid) {
		$allcertificates[] = get_certificate($allcourseid, $startdate, $enddate, $city, $institution, $department);
	}
	$totalcertificatecount = array_sum($allcertificates);
	$certificateinfo = array('totalcertificatecount'=>$totalcertificatecount);
	return $certificateinfo;
}
//find all badges from course
function get_all_course_badges($allcourseids,$data){
	global $DB;
	$institution ='';
	if($data->institution){
		$institution = $data->institution;
	}
	$city ='';
	if($data->city){
		$city = $data->city;
	}
	$department ='';
	if($data->department){	
		$department = $data->department;
	}	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;


	foreach ($allcourseids as $allcourseid) {
		$coursename = get_course_name($allcourseid);
		$badge_data = get_badge_data($allcourseid, $coursename, $startdate, $enddate, $city, $institution, $department);
		$goldbadgecount[] = $badge_data['0'];
		$silverbadgecount[] = $badge_data['1'];
		$bronzebadgecount[] = $badge_data['2'];
		$completionbadgecount[] = $badge_data['3'];
	}
	
	$gold = array_sum($goldbadgecount);
	$silver = array_sum($silverbadgecount);
	$bronz = array_sum($bronzebadgecount);
	$completionbadgecount = array_sum($goldbadgecount);
	$badgesdetails = array('gold'=>$gold,'silver'=>$silver,'bronz'=>$bronz,'completionbadgecount'=>$completionbadgecount);
	return $badgesdetails;

}
// Utils functions used by block dedication.
class local_hpanalytics_utils {

	public static $logstores = array('logstore_standard', 'logstore_legacy');

    // Return formatted events from logstores.
	public static function get_events_select($selectwhere, array $params) {
		$return = array();

		static $allreaders = null;

		if (is_null($allreaders)) {
			$allreaders = get_log_manager()->get_readers();
		}

		$processedreaders = 0;

		foreach (self::$logstores as $name) {
			if (isset($allreaders[$name])) {
				$reader = $allreaders[$name];
				$events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);
				foreach ($events as $event) {
                    // Note: see \core\event\base to view base class of event.
					$obj = new stdClass();
					$obj->time = $event->timecreated;
					$obj->ip = $event->get_logextra()['ip'];
					$return[] = $obj;
				}
				if (!empty($events)) {
					$processedreaders++;
				}
			}
		}

        // Sort mixed array by time ascending again only when more of a reader has added events to return array.
		if ($processedreaders > 1) {
			usort($return, function($a, $b) {
				return $a->time > $b->time;
			});
		}

		return $return;
	}

    // Formats time based in Moodle function format_time($totalsecs).
	public static function format_dedication($totalsecs) {
		$totalsecs = abs($totalsecs);

		$str = new stdClass();
		$str->hour = get_string('hour');
		$str->hours = get_string('hours');
		$str->min = get_string('min');
		$str->mins = get_string('mins');
		$str->sec = get_string('sec');
		$str->secs = get_string('secs');

		$hours = floor($totalsecs / HOURSECS);
		$remainder = $totalsecs - ($hours * HOURSECS);
		$mins = floor($remainder / MINSECS);
		$secs = round($remainder - ($mins * MINSECS), 2);

		$ss = ($secs == 1) ? $str->sec : $str->secs;
		$sm = ($mins == 1) ? $str->min : $str->mins;
		$sh = ($hours == 1) ? $str->hour : $str->hours;

		$ohours = '';
		$omins = '';
		$osecs = '';

		if ($hours) {
			$ohours = $hours . ' ' . $sh;
		}
		if ($mins) {
			$omins = $mins . ' ' . $sm;
		}
		if ($secs) {
			$osecs = $secs . ' ' . $ss;
		}

		if ($hours) {
			return trim($ohours . ' ' . $omins);
		}
		if ($mins) {
			return trim($omins . ' ' . $osecs);
		}
		if ($secs) {
			return $osecs;
		}
		return get_string('none');
	}

    // Formats ips.
	public static function format_ips($ips) {
		return implode(', ', array_map('block_dedication_utils::link_ip', $ips));
	}

    // Generates an linkable ip.
	public static function link_ip($ip) {
		return html_writer::link("http://en.utrace.de/?query=$ip", $ip, array('target' => '_blank'));
	}

    // Table styles.
	public static function get_table_styles() {
		global $PAGE;

        // Twitter Bootstrap styling.
		if (in_array('bootstrapbase', $PAGE->theme->parents)) {
			$styles = array(
				'table_class' => 'table table-striped table-bordered table-hover table-condensed table-dedication',
				'header_style' => 'background-color: #333; color: #fff;'
			);
		} else {
			$styles = array(
				'table_class' => 'table-dedication',
				'header_style' => ''
			);
		}

		return $styles;
	}

    // Generates generic Excel file for download.
	public static function generate_download($downloadname, $rows) {
		global $CFG;

		require_once($CFG->libdir . '/excellib.class.php');

		$workbook = new MoodleExcelWorkbook(clean_filename($downloadname));

		$myxls = $workbook->add_worksheet(get_string('pluginname', 'block_dedication'));

		$rowcount = 0;
		foreach ($rows as $row) {
			foreach ($row as $index => $content) {
				$myxls->write($rowcount, $index, $content);
			}
			$rowcount++;
		}

		$workbook->close();

		return $workbook;
	}

}	
//sangita : Jan 23 2020 : 
//this function is used for print the heading of page.
function get_heading($headingtext,$subheadingtext,$buttonlink,$buttontext,$url){
	global $CFG;
	$headingdetails = html_writer::start_tag('div',  array('class' => 'row'));
	$headingdetails .= html_writer::start_tag('div',  array('class' => 'col-md-12 btndownloadexe'));
	$headingdetails .= html_writer::start_tag('h4');
	$headingdetails .= $headingtext;
	$headingdetails .= html_writer::end_tag('h4');
	$headingdetails .= html_writer::start_tag('a',array('href'=>$url));
	$headingdetails .= '<i class="fa fa-arrow-circle-o-down h-25 m-1" aria-hidden="true"></i>';
	$headingdetails .= html_writer::end_tag('a');
	$headingdetails .= html_writer::end_tag('div');
    //$headingdetails .= html_writer::end_tag('div');
	$headingdetails .= html_writer::end_tag('div');
	$headingdetails .= get_string('guideline', 'local_hpanalytics');
	return $headingdetails;
}



//Manju: This function will return html format of highest video courses.[30/01/2020].
function create_html_for_highest_video_courses($allenrollmentcount,$allcoursecompletion){
	global $DB;
	$maxarrayvalues = $allenrollmentcount['maxrecod'];
	$data = html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments allmaxenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('highestvideocourseenrollment', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body first-graph');
	if(!empty($maxarrayvalues)){
		$vidmaxtable = new html_table();
		$vidmaxtable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('enrollcount','local_hpanalytics'));
		foreach ($maxarrayvalues as $coursename => $maxarrayvalue) {
			$vidmaxtable->data[] = array($coursename,$maxarrayvalue);
		}
		$data .= html_writer::table($vidmaxtable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body

	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column

	$maxarrayval = $allcoursecompletion['maxrecod'];
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments allmaxenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('highestvideocoursecompletion', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body first-graph');
	if(!empty($maxarrayval)){
		$vidtable = new html_table();
		$vidtable->head = array(get_string('coursename', 'local_hpanalytics'), get_string('enrollcount','local_hpanalytics'));
		foreach ($maxarrayval as $coursename => $maxarrayvalue) {
			$vidtable->data[] = array($coursename,$maxarrayvalue);
		}
		$data .= html_writer::table($vidtable);
	}else{
		$data .= html_writer::start_tag('p');
		$data .= get_string('norecord','local_hpanalytics');
		$data .= html_writer::end_tag('p');//end card-body
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row
	$data .= html_writer::end_div();//end container
	return $data;
}

function all_enrolled_usersdata($courseid)
{
	global $DB,$CFG;
	$sql = "SELECT ue.id AS ueid, ue.status AS uestatus, ue.enrolid AS ueenrolid, ue.timestart AS uetimestart,
	ue.timeend AS uetimeend, ue.modifierid AS uemodifierid, ue.timecreated AS uetimecreated,
	ue.timemodified AS uetimemodified, e.status AS estatus,
	u.* FROM {user_enrolments} ue
	JOIN {enrol} e ON e.id = ue.enrolid
	JOIN {user} u ON ue.userid = u.id
	WHERE ";
	$params = array();

	if ($courseid) {
		$conditions[] = "e.courseid = :courseid";
		$params['courseid'] = $courseid;
	}

	$allenrolleduser=$DB->get_records_sql($sql . ' ' . implode(' AND ', $conditions), $params);
	$listofusers =[];
	foreach ($allenrolleduser as $user) {
		$listofusers[] = $user->uetimecreated;
	}
	sort($listofusers);
	return  $listofusers;
}

function get_yearwisegraph($course_id, $start_date, $end_date, $city, $institution, $department){
	global $DB;
	//getting yearwise enrollment count.
	$convdate=[];
	$allenrolldates = all_enrolled_usersdata($course_id);
	foreach ($allenrolldates as $condate) {

		$convdate[]=date('Y', $condate);
	}
	$userenrolconvyear = array_count_values($convdate);
	$temp=$userenrolconvyear;
	if(count($temp)==1){
		reset($temp);
		$result = key($temp);
		$userenrolconvyear[$result-1]=0;

	}
	ksort($userenrolconvyear);

//getting yearwise completion count.
	$cmpletiondate=[];
	$completiondatequery = "SELECT id,timecompleted FROM {course_completions} WHERE course = $course_id AND timecompleted IS NOT NULL";
	$coursecompledate = $DB->get_records_sql($completiondatequery);
	foreach ($coursecompledate as $cdate) {
		$cmpletiondate[]=date('Y',$cdate->timecompleted);
	}
	$usercompleconvyear = array_count_values($cmpletiondate);
	$temp1=$usercompleconvyear;
	if(count($temp1)==1){
		reset($temp1);
		$result1 = key($temp1);
		$usercompleconvyear[$result1-1]=0;

	}
	ksort($usercompleconvyear);

	$returnarray = array('enrolldata'=>$userenrolconvyear,
		'completiondata'=>$usercompleconvyear);


	return $returnarray;
}

//Manju: Functions moved from engageview.php to lib.[03/02/2020].
function get_highest_enr_count_video() {
	global $DB;

	$videosql = "SELECT c.id, c.fullname, COUNT(ue.id) AS enroled
	FROM {course} AS c 
	JOIN {course_categories} AS cat ON cat.id = c.category
	JOIN {enrol} AS en ON en.courseid = c.id
	JOIN {user_enrolments} AS ue ON ue.enrolid = en.id
	WHERE cat.id = 2
	GROUP BY c.id
	ORDER BY c.fullname";

	$getresult = $DB->get_records_sql($videosql);
	if (!empty($getresult)){
		foreach($getresult as $key => $value) {
			$newarr[$key] = $value->enroled;
		}
	}
	// print_object($newarr);

}
function loginuser_details_analytics($data){
	global $DB,$CFG,$USER;
	//start date and end date.
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	//check for both start and end dates are same or not.
	$totallogin=0;
	$uniqlogincount=0;
	if($startdate == $enddate){
		$totalq="SELECT COUNT(id) as total FROM {logstore_standard_log} WHERE timecreated >= ".$startdate." AND action = 'loggedin'";
		$tlogin=$DB->get_record_sql($totalq);
		$totallogin=$tlogin->total;

		$uniqsql="SELECT COUNT(DISTINCT(userid)) as uniq FROM {logstore_standard_log} WHERE timecreated >= ".$startdate."";
		$ucount=$DB->get_record_sql($uniqsql);
		$uniqlogincount=$ucount->uniq;

	}else{
		$totalq="SELECT COUNT(id) as total FROM {local_loginevent} WHERE logintime 
		BETWEEN ".$startdate." and ".$enddate."";
		$tlogin=$DB->get_record_sql($totalq);
		$totallogin=$tlogin->total;

		$uniqsql="SELECT COUNT(DISTINCT(userid)) as uniq FROM {local_loginevent} WHERE logintime 
		BETWEEN ".$startdate." and ".$enddate."";
		$ucount=$DB->get_record_sql($uniqsql);
		$uniqlogincount=$ucount->uniq;
	}
	return $resultvalue = array(
		'sofarlogin'=>$totallogin,
		'uniqlogin'=>$uniqlogincount
	);

}
//Functions from engageview.php ends.
//Manju: Function to get top video course based on number of enrollment
function get_top_video_course(){
	global $DB,$CFG;
	$returnarray=[];
	$videocoursearray=[];
	//get the video category id.
	$videocatid = $DB->get_field('course_categories', 'id', array('idnumber'=>'Video'));
	//get all the courses in video category.
	$videocourses = $DB->get_records('course',array('category'=>$videocatid));
	//get count of all enrolled usersdata.
	foreach ($videocourses as $course) {
		$enrollmentcount=count(all_enrolled_usersdata($course->id));
		$videocoursearray[$course->id]=$enrollmentcount;
	}
	arsort($videocoursearray);
	reset($videocoursearray);
	//Returning the courseid of video course having max enrollments. 
	return key($videocoursearray);
}


// Abhijit: function for get course image NEW.........
function course_image_cd($course_detail) {
	global $CFG, $PAGE, $OUTPUT;
	// Get course overview files.
	if (empty($CFG->courseoverviewfileslimit)) {
		return '';
	}
	require_once ($CFG->libdir . '/filestorage/file_storage.php');
	require_once ($CFG->dirroot . '/course/lib.php');
	$fs = get_file_storage();
	$context = context_course::instance($course_detail->id);
	$files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
	if (count($files)) {
		$overviewfilesoptions = course_overviewfiles_options($course_detail->id);
		$acceptedtypes = $overviewfilesoptions['accepted_types'];
		if ($acceptedtypes !== '*') {
	        // Filter only files with allowed extensions.
			require_once ($CFG->libdir . '/filelib.php');
			foreach ($files as $key => $file) {
				if (!file_extension_in_typegroup($file->get_filename() , $acceptedtypes)) {
					unset($files[$key]);
				}
			}
		}
		if (count($files) > $CFG->courseoverviewfileslimit) {
	        // Return no more than $CFG->courseoverviewfileslimit files.
			$files = array_slice($files, 0, $CFG->courseoverviewfileslimit, true);
		}
	}

	// Get course overview files as images - set $courseimage.
	// The loop means that the LAST stored image will be the one displayed if >1 image file.
	$courseimage = '';
	foreach ($files as $file) {
		$isimage = $file->is_valid_image();
		if ($isimage) {
			$courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename() , !$isimage);
		}
	}

	if($courseimage!=''){
		return $courseimage;
	}else{
		return $CFG->wwwroot . '/local/hpanalytics/pix/course-defoult-image.jpg';
	}
	

}

function get_course_completion_count_engage($allcourseids,$data){
	global $DB;	
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	$completionrecords=[];
	foreach ($allcourseids as $key => $allcourseid) {
		$allcompletioncounts[] = get_course_completion($allcourseid,$startdate,$enddate,null,null,null);
	}
	foreach ($allcompletioncounts as $allcompletioncount) {
		if(!empty($allcompletioncount)){
			$completionrecords[$allcompletioncount->fullname] = $allcompletioncount->completion;
		}
	}
	//print_object($completionrecords);die;
	return $completionrecords;
}


//Manju:[04/02/2020].
function get_students_dedication2($students,$courseid,$start_date, $end_date) {
	global $DB;
	$rows = array();
	$where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';
	$params = array(
		'courseid' => $courseid,
		'userid' => 0,
		'mintime' => $start_date,
		'maxtime' => $end_date
	);
	$perioddays = ($end_date - $start_date) / DAYSECS;
	foreach ($students as $user) {
		$daysconnected = array();
		$params['userid'] = $user->id;
		$logs = local_hpanalytics_utils::get_events_select($where, $params);
		if ($logs) {
			$previouslog = array_shift($logs);
			$previouslogtime = $previouslog->time;
			$sessionstart = $previouslog->time;
			$dedication = 0;
			$daysconnected[date('Y-m-d', $previouslog->time)] = 1;
			$limit =60;
			foreach ($logs as $log) {
				if (($log->time - $previouslogtime) > $limit) {
					$dedication += $previouslogtime - $sessionstart;
					$sessionstart = $log->time;
				}
				$previouslogtime = $log->time;
				$daysconnected[date('Y-m-d', $log->time)] = 1;
			}
			$dedication += $previouslogtime - $sessionstart;
		} else {
			$dedication = 0;
		}
		$groups = groups_get_user_groups($courseid, $user->id);
		$group = !empty($groups) && !empty($groups[0]) ? $groups[0][0] : 0;
		$rows[] = (object) array(
			'user' => $user,
			'groupid' => $group,
			'dedicationtime' => $dedication,
			'connectionratio' => round(count($daysconnected) / $perioddays, 2),
		);
	}
	return $rows;
}

//Manju:this function will return mean avg time spent by all users for a course.
function get_mean_dedication_time($courseid,$start_date, $end_date){
	global $DB;
	$meantime =0;
	$students = get_enrolled_users(context_course::instance($courseid));
	if(!empty($students)){
		$rows = get_students_dedication2($students,$courseid,$start_date, $end_date);
		// Table formatting & total count.
		$totaldedication = 0;
		foreach ($rows as $index => $row) {
			$totaldedication += $row->dedicationtime;
			$rows[$index] = array(
				local_hpanalytics_utils::format_dedication($row->dedicationtime)
			);
		}
		$meantime = local_hpanalytics_utils::format_dedication(count($rows) ? $totaldedication / count($rows) : 0);
		$totaldedication = local_hpanalytics_utils::format_dedication($totaldedication);
	}

	return $meantime;
}

//all enaggeview functions


function get_all_courses_cat_subcat($catid){
	global $DB;
	$sqlquery ="SELECT {course}.id as courseid , {course_categories}.id
	FROM {course}, {course_categories}
	WHERE {course}.category = {course_categories}.id
	AND (
	{course_categories}.path LIKE '%/$catid/%'
	OR {course_categories}.path LIKE '%/$catid'
)";

$allcourses = $DB->get_records_sql($sqlquery);
return $allcourses;
}

function get_all_courses_subcat($catid){
	global $DB;
	$sqlquery ="SELECT {course}.id as courseid , {course_categories}.id
	FROM {course}, {course_categories}
	WHERE {course}.category = {course_categories}.id
	AND (
	{course_categories}.path LIKE '%/$catid/%'
)";

$allcourses = $DB->get_records_sql($sqlquery);
return $allcourses;
}

//Manju: get all 4 categories enrollment and completion count.
function get_all_categories_enrol_and_completion_count($data){
	global $DB;
	$videocoursearray=[];
	$techcoursearray=[];
	$behavcoursearray=[];
	$tech_foundacoursearray=[];
	$videoids =[];
	//get all 4 categories category id.
	$videocatid = $DB->get_field('course_categories', 'id', array('idnumber'=>'Video'));
	$techcatid = $DB->get_field('course_categories', 'id', array('idnumber'=>'Technical'));
	$behavcatid = $DB->get_field('course_categories', 'id', array('idnumber'=>'Behavioral'));
	$tech_founda = $DB->get_field('course_categories', 'id', array('idnumber'=>'Foundation'));

	//get all courses in videocategory.
	$videocourses =get_all_courses_cat_subcat($videocatid);
	$videocourseid=[];
	foreach ($videocourses as $vcourse) {
		$videocourseid[]=$vcourse->courseid;
	}
	//get all courses in technical category.
	$techcourses = get_all_courses_cat_subcat($techcatid);

	$techcourseid=[];
	foreach ($techcourses as $tcourse) {
		$techcourseid[]=$tcourse->courseid;
	}
	//get all courses in behavior category.
	$behavecourses = get_all_courses_cat_subcat($behavcatid);
	$behavecourseid=[];
	foreach ($behavecourses as $bcourse) {
		$behavecourseid[]=$bcourse->courseid;
	}
	//get all courses in tech foundation category.
	$tech_foundacourses = get_all_courses_cat_subcat($tech_founda);
	$tech_foundacourseid=[];
	foreach ($tech_foundacourses as $fcourse) {
		$tech_foundacourseid[]=$fcourse->courseid;
	}


	//get count of all video enrolled usersdata.
	foreach ($videocourses as $videocourse) {
		$videoenrollmentcount=count(all_enrolled_usersdata($videocourse->courseid));
		$videocoursearray[$videocourse->courseid]=$videoenrollmentcount;
	}
	$videoenrolcount = array_sum($videocoursearray);
	$videocompletioncount = array_sum(get_course_completion_count_engage($videocourseid,$data));



	//get count of all technical enrolled usersdata.
	foreach ($techcourses as $techcourse) {
		$techenrollmentcount=count(all_enrolled_usersdata($techcourse->courseid));
		$techcoursearray[$techcourse->courseid]=$techenrollmentcount;
	}
	$techenrolcount= array_sum($techcoursearray);
	$techcompletioncount = array_sum(get_course_completion_count_engage($techcourseid,$data));


	//get count of all behavior enrolled usersdata.
	foreach ($behavecourses as $behavcourse) {
		$behaveenrollmentcount=count(all_enrolled_usersdata($behavcourse->courseid));
		$behavcoursearray[$behavcourse->courseid]=$behaveenrollmentcount;
	}
	$behavenrolcount=array_sum($behavcoursearray);
	$behavecompletioncount = array_sum(get_course_completion_count_engage($behavecourseid,$data));


	//get count of all tech foundation enrolled usersdata.
	foreach ($tech_foundacourses as $tech_foundacourse) {
		$tech_foundaenrollmentcount=count(all_enrolled_usersdata($tech_foundacourse->courseid));
		$tech_foundacoursearray[$tech_foundacourse->courseid]=$tech_foundaenrollmentcount;
	}

	$techfoundationenrollcount=array_sum($tech_foundacoursearray);
	$techfoundationcompletioncount = array_sum(get_course_completion_count_engage($tech_foundacourseid,$data));



	$returnarray = array('Video'=>array('enrol'=>$videoenrolcount,
		'complete'=>$videocompletioncount),
	'Technical'=>array('enrol'=>$techenrolcount,
		'complete'=>$techcompletioncount),
	'Behavioral'=>array('enrol'=>$behavenrolcount,
		'complete'=>$behavecompletioncount),
	'Foundation'=>array('enrol'=>$techfoundationenrollcount,
		'complete'=>$techfoundationcompletioncount));
	return $returnarray;
}
function get_technical_cat_course_stats($data){
	global $DB;
	$returnarray=[];
	$techcoursearray=[];
	$techcatid = $DB->get_field('course_categories', 'id', array('idnumber'=>'Technical'));
	$techsubcat= get_all_courses_subcat($techcatid);
	if(!empty($techsubcat)){
		$subcatarray =[];
		foreach ($techsubcat as  $subcat) {
			$subcatarray[]=$subcat->id;
		}
	}
	//Manju: @$uniqsubcats contains all categories ids under technical cat.
	$uniqsubcats = array_unique($subcatarray);
	$techcoursearray=[];
	$returnarray=[];
	foreach ($uniqsubcats as  $sbcat) {
		//cat name
		$catname = $DB->get_field('course_categories', 'name', array('id'=>$sbcat));
		//get all courses.
		$allcourses = $DB->get_records('course',array('category'=>$sbcat));
		if(!empty($allcourses)){
			$courseids=[];
			foreach ($allcourses as $course) {
				$courseids[]=$course->id;
			}
			//get count of all technical enrolled usersdata.
			foreach ($allcourses as $techcourse) {
				$techenrollmentcount=count(all_enrolled_usersdata($techcourse->id));
				$techcoursearray[$techcourse->id]=$techenrollmentcount;
			}
			$techenrolcount= array_sum($techcoursearray);
			$techcompletioncount = array_sum(get_course_completion_count_engage($courseids,$data));
		}
		$returnarray[$catname]=array('enrol'=>$techenrolcount,
			'complete'=>$techcompletioncount);
	}
	return $returnarray;
}

//Replacing functions.
function get_hpcl_excel_report($cid = null,$start =null,$end=null,$city=null,$institution=null,$department=null){
	global $DB;
	$i = 1;
	if(!empty($cid)){
		$instquery ='';
		$cityquery = '';
		$departquery ='';
		if (!empty($institution)) {
			$instquery = "AND u.institution LIKE '%$institution%'";
		}
		if(!empty($city)){
			$cityquery = "AND u.city LIKE '%$city%'";
		}
		if(!empty($department)){
			$departquery = "AND u.department LIKE '%$department%'";
		}
		$totalquery = "SELECT c.*
		FROM {course_completions} c
		INNER JOIN {user} u ON u.id = c.userid
		WHERE (c.timeenrolled between $start and $end) 
		AND (c.course = '$cid')
		$instquery
		$cityquery
		$departquery
		";
		$completiondetials = $DB->get_records_sql($totalquery);
		$finalexcel = array();
		if(!empty($completiondetials)){
			foreach ($completiondetials as $key => $completiondetial) {
				// $exceldatas[] = get_hpcl_excel_record($i,$completiondetial);
				// $i++;

				$exceldatas = get_hpcl_excel_record($i,$completiondetial,$start,$end);

				if(!empty($exceldatas)){
					$finalexcel[] = $exceldatas;
					$i++;
				}
			}

		}
	}	else{
		$completiondetials = $DB->get_records('course_completions');
		$finalexcel = array();
		foreach ($completiondetials as $key => $completiondetial) {
			$exceldatas = get_hpcl_excel_record($i,$completiondetial,$start,$end);
			if(!empty($exceldatas)){
				$finalexcel[] = $exceldatas;
				$i++;
			}

		}
	}
	return $finalexcel;
}
//this function is used to find all completion records
function get_hpcl_excel_record($i,$completiondetial,$start,$end){
	global $DB;
	
	$cid = $completiondetial->course;//course id
	$uid = $completiondetial->userid;
	$sql = "SELECT u.id,u.username,u.auth,CONCAT(u.firstname,' ',u.lastname) AS userfullname,u.institution,u.department,u.city,c.fullname AS coursename 
	FROM {user} u JOIN {course_completions} cc ON u.id = cc.userid 
	JOIN {course} c ON c.id = cc.course
	WHERE cc.course = ? AND u.id =?";
	
	$singlerecord = $DB->get_record_sql($sql,array($cid,$uid));
	$enrolltimeunix = '';
	if(!empty($singlerecord)){
		$coursestart = userdate($completiondetial->timestarted,get_string('strftimedatefullshort','langconfig'));
		if(!empty($completiondetial->timecompleted)){
			$compeletetime = userdate($completiondetial->timecompleted,get_string('strftimedatefullshort','langconfig'));
		}else{
			$compeletetime='-';
		}
		$enrolltime = '-';
		if($completiondetial->timeenrolled != 0){
			$enrolltime = userdate($completiondetial->timeenrolled,get_string('strftimedatefullshort','langconfig'));
			$enrolltimeunix = $completiondetial->timeenrolled; 	
		}else{
			$sql = "SELECT e.timemodified FROM {user_enrolments} ue JOIN {enrol} e ON ue.enrolid = e.id WHERE ue.userid =? AND e.courseid =? ";
			$enrollmentdetails = $DB->get_record_sql($sql,array($uid,$cid));
			if(!empty($enrollmentdetails)){
				$enrolltime = userdate($enrollmentdetails->timemodified,get_string('strftimedatefullshort','langconfig'));
				$enrolltimeunix = $enrollmentdetails->timemodified;
			}	
			
		}
		$userdeatls = $DB->get_record('user',array('id'=>$uid));

		$data = new stdClass(); 
		$data->reportstart = $enrolltimeunix;
		$data->reportend =$completiondetial->timecompleted;
		$timeinmunuts = '';
		$timeinmunuts = get_students_dedication3($userdeatls,$cid,$start, $end);
		if(empty($timeinmunuts)){
			$timeinmunuts = '-';
		}else{
			$timeinmunuts = local_hpanalytics_utils::format_dedication($timeinmunuts);
		}
		$alldetails = get_record_array($i,$completiondetial,$singlerecord,$enrolltime,$coursestart,$compeletetime,$timeinmunuts);
	}
	if(!empty($alldetails)){
		return $alldetails;
	}
}

//this function is used to create array
function get_record_array($i,$completiondetial,$singlerecord,$enrolltime,$coursestart,$compeletetime,$timeinmunuts){
	global $DB;

	if($completiondetial->timecompleted !== null){

		$alldetails = [$i,$singlerecord->username,$singlerecord->userfullname,$singlerecord->city,$singlerecord->coursename,'Completed',$enrolltime,$compeletetime,$timeinmunuts];
	}else{
		$alldetails = [$i,$singlerecord->username,$singlerecord->userfullname,$singlerecord->city,$singlerecord->coursename,'Not completed',$enrolltime,$compeletetime,$timeinmunuts];
	}
	return $alldetails;
}

//New functions.
function get_students_dedication3($students,$courseid,$start_date, $end_date) {
	global $DB;
	$rows = array();
	$where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';
	$params = array(
		'courseid' => $courseid,
		'userid' => $students->id,
		'mintime' => $start_date,
		'maxtime' => $end_date
	);
	$perioddays = ($end_date - $start_date) / DAYSECS;

	$daysconnected = array();
	$logs = local_hpanalytics_utils::get_events_select($where, $params);
	if ($logs) {
		$previouslog = array_shift($logs);
		$previouslogtime = $previouslog->time;
		$sessionstart = $previouslog->time;
		$dedication = 0;
		$daysconnected[date('Y-m-d', $previouslog->time)] = 1;
		$limit =60;
		foreach ($logs as $log) {
			if (($log->time - $previouslogtime) > $limit) {
				$dedication += $previouslogtime - $sessionstart;
				$sessionstart = $log->time;
			}
			$previouslogtime = $log->time;
			$daysconnected[date('Y-m-d', $log->time)] = 1;
		}
		$dedication += $previouslogtime - $sessionstart;
	} else {
		$dedication = 0;
	}
	return $dedication;
}

//Manju:course enrollment and completion data based on zones.[27/02/2020]
function get_chartdata_of_allzones($data){
	global $CFG,$DB;
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	//get all the zones.
	$returnarray=[];
	$institution_options = array('10103'=> 'Hindustan Bhavan',
		'10501' => 'Petroleum House',
		'11100' =>'North Zone',
		'11120'=>'North Central Zone',
		'11350'=> 'West Zone',
		'11370'=> 'North West Zone',
		'11600'=>'East Zone',
		'11750'=>'South Zone',
		'11770'=>'South Central Zone',
		'12620'=> 'South Central LPG Zone',
		'46000'=>'Visakha Refinery',
		'48000'=>'Mumbai Refinery');
	//get all the user under zones.
	foreach ($institution_options as $zoneid => $zonename) {
		if($startdate == $enddate){
			$enrolquery ="SELECT cc.id FROM {course_completions} cc
			INNER JOIN {user} u ON cc.userid = u.id
			WHERE u.institution = $zoneid AND cc.timeenrolled >= ".$startdate."";
			$completionquery="SELECT cc.id FROM {course_completions} cc
			INNER JOIN {user} u ON cc.userid = u.id
			WHERE u.institution = $zoneid AND cc.timecompleted >= ".$startdate." AND cc.timecompleted is not null";
		}else{
			$enrolquery ="SELECT cc.id FROM {course_completions} cc
			INNER JOIN {user} u ON cc.userid = u.id
			WHERE u.institution = $zoneid AND cc.timeenrolled BETWEEN $startdate AND $enddate";
			$completionquery="SELECT cc.id FROM {course_completions} cc
			INNER JOIN {user} u ON cc.userid = u.id
			WHERE u.institution = $zoneid AND cc.timecompleted BETWEEN $startdate AND $enddate AND cc.timecompleted is not null";
		}
		
		$zoneenrolcount = count($DB->get_records_sql($enrolquery));
		$zonecompletioncount = count($DB->get_records_sql($completionquery));
		$returnarray[$zonename] =array('enrol'=>$zoneenrolcount,
			'complete'=>$zonecompletioncount);
	}
	return $returnarray;
}

//Manju: this function will returns yearwise logged in unique users details.[28/02/2020]
function get_loggedin_data_yearwise(){
	global $CFG,$DB;
	$returnarray=[];
	$alllogins = $DB->get_records('logstore_standard_log',array('action'=>'loggedin'));
	$logtimearray=[];
	foreach ($alllogins as  $log) {
		$date= date("Y",$log->timecreated);
		$logtimearray[$date][$log->userid]=$log->userid;
	}
	foreach ($logtimearray as $year => $value) {
		$returnarray[$year]=count($value);
	}
	return $returnarray;
}

//Manju: this function will give all months unique users logged in details.[28/02/2020]
function get_loggedin_data_monthise(){
	global $CFG,$DB;
	$finalarray=[];
	$yeararray=[];
	$yeararryreverse=[];
	$firstlogged=$DB->get_record_sql("SELECT timecreated FROM {logstore_standard_log} WHERE action LIKE '%loggedin%' GROUP BY timecreated ASC LIMIT 1");
	$firstloggedyear= date("Y",$firstlogged->timecreated);
	$currentyear=date("Y");
	$years=$currentyear-$firstloggedyear;
	for ($i=0; $i <=$years; $i++) {
		if($i==0){
			$yeararray[$firstloggedyear]=0;
			$yeararryreverse[]=$firstloggedyear;
		}else{
			$yeararray[$firstloggedyear+1]=0;
			$yeararryreverse[]=$firstloggedyear+1;
			$firstloggedyear=$firstloggedyear+1;
		}
	}
	$montharray =array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$returnarray=[];
	$alllogins =$DB->get_records_sql("SELECT * FROM {logstore_standard_log} WHERE action LIKE '%loggedin%' GROUP BY timecreated ASC");
	foreach ($alllogins as  $log) {
		$date= date("M",$log->timecreated);
		$date1= date("Y",$log->timecreated);
		$logtimearray[$date][$date1][$log->userid]=$log->userid;
	}
	foreach ($montharray as $month) {
		if (array_key_exists($month,$logtimearray)){
			$returnarray[$month]=$logtimearray[$month];
		}else{
			$returnarray[$month]=0;
		}
	}

	foreach ($returnarray as $key => $value) {
		$temparay=[];
		if(!empty($value)){
			foreach ($value as $key1 => $value1) {
				$temparay[$key1]=count($value1);
			}
			$finalarray[$key]=$temparay;
		}else{
			$finalarray[$key]=$yeararray;
		}
		
	}
	$lastarray=[];
	foreach ($finalarray as $mnth => $data) {
		$lastarray[$mnth]=$data+$yeararray;
	}
	$finalreturnarray=[];
	$cnter=0;
	$aaa='';
	$cnt=count($yeararryreverse);
	for($yi=0;$yi<$cnt;$yi++){
		$aaa='';
		$fcnt=0;
		foreach ($lastarray as $lkey => $lvalue) {
			if($fcnt==0){
				$aaa .=$lvalue[$yeararryreverse[$yi]];
			}else{
				$aaa .=','.$lvalue[$yeararryreverse[$yi]];
			}
			$fcnt++;
		}
		$finalreturnarray[$yeararryreverse[$yi]]=$aaa;
	}
	return $finalreturnarray;
}
//getting the completion count for hpcl categories.
function hpcl_categories_completion_count($courseid,$data,$category){
	global $DB;
	$startdate = $data->reportstart;
	$enddate = $data->reportend;
	$counter=1;
	$instring="";
	foreach ($courseid as $cid) {
		if($counter == 1){
			$instring = "'".$cid."'";
		}else{
			$instring =$instring.","."'".$cid."'";
		}
		$counter++;
	}
	$query="SELECT cc.id, u.username, u.firstname, u.lastname, u.email, ch.hours as CDuration, ch.hpclcategory,ch.coursecode, c.fullname, cc.timeenrolled as timeenrolled, 
	FROM_UNIXTIME(cc.timecompleted) as timecompleted,ch.facultycode,ch.learnigtype,ch.programtype,ch.vendor,ch.summativeassessment  
	FROM {course} AS c
	JOIN {hpcl_coursehours} as ch ON c.id = ch.course_id
	JOIN {course_completions} AS cc ON cc.course = c.id 
	JOIN {user} AS u ON u.id = cc.userid
	WHERE (cc.timecompleted BETWEEN '$startdate' AND '$enddate')
	AND cc.course IN (".$instring.") AND ch.hpclcategory = '$category'
	ORDER by cc.timeenrolled";
	$record = $DB->get_records_sql($query);
	return $record;
}


//s
function get_all_courses_enrollment_completiondata($data){
	global $DB;
	//rachita: getting all the distinct categories from the course hours table.
	$categories = $DB->get_records_sql("SELECT  DISTINCT hpclcategory AS category from {hpcl_coursehours}");
	$final_array = [];
	//checking for the value.
	if(!empty($categories)){
		//getting individual categories.
		foreach ($categories as $category) {
			//getting all the courses related to the category.
			$courses = $DB->get_records_sql("SELECT course_id FROM {hpcl_coursehours} WHERE hpclcategory = '$category->category'");
			//counter for course enrollment.
			$enrolcounter = 0;
			//counter for course completion.
			$completioncounter = 0;

			$countarray = [];
			//checkimg for value.
			if(!empty($courses)){
				//getting individual courses.
				$courseid = [];
				foreach ($courses as $course) {
					$courseid[] = $course->course_id;
					//get the enrolcount of this course.
					$enrolcount = count(all_enrolled_usersdata($course->course_id));
					///adding the enrol count to the counter.
					$enrolcounter = $enrolcounter + $enrolcount;
				}
				$completioncount = count(hpcl_categories_completion_count($courseid,$data,$category->category));
				if($category->category != ""){
					$final_array[$category->category]=array('enrol'=>$enrolcounter,'complete'=>$completioncount);
				}
			}
		}
		return $final_array;
	}
}