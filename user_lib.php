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
 * @copyright 2014 Daniel Neis Araujo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $USER,$DB,$COURSE;
defined('MOODLE_INTERNAL') || die();
/**
*@ id is courseid
*@startdate form startdate
*@enddate form enddate
*/
function get_user_enrollment_data($id, $startdate, $enddate){
	global $DB;
	$query = "SELECT c.id,c.userid
	FROM {course_completions} c
	INNER JOIN {user} u ON u.id = c.userid
	WHERE c.timeenrolled between $startdate and $enddate
	AND c.userid = $id
	AND c.timeenrolled !=0
	";
	$enrollmentcount = count($DB->get_records_sql($query));
	return  $enrollmentcount;
}
//manjunath: this function returns the total certificates earned by the user
function user_certificate($userid, $start, $end)
{
	global $DB;
	$sql = "SELECT *
	FROM {simplecertificate_issues} c
	WHERE c.timecreated between $start and $end and c.userid = $userid
	";
	$certificate_count = count($DB->get_records_sql($sql));
	return $certificate_count;
}

// manjunath: course completion, in-progress and not started statistics.
function user_course_stats($userid, $start, $end)
{
	global $DB;
	$totalquery = "SELECT *
	FROM {course_completions} c
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.userid = '$userid')
	";
	$records = count($DB->get_records_sql($totalquery));
	$totalcount = $records;

//manjunath: coures completed users count.
	$completedquery = "SELECT *
	FROM {course_completions} c
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.userid = '$userid')
	AND (c.timecompleted is not null)
	";
	$completedrecords = count($DB->get_records_sql($completedquery));
	$completedcount = $completedrecords;

//manjunath: course in progress users count.
	$progressquery = "SELECT *
	FROM {course_completions} c
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.userid = '$userid')
	AND (c.timestarted != 0)
	AND (c.timecompleted is null)
	";
	$progessrecords = count($DB->get_records_sql($progressquery));
	$progresscount = $progessrecords;

//manjunath: course not started users count
	$notstartedquery = "SELECT *
	FROM {course_completions} c
	WHERE (c.timeenrolled between $start and $end) 
	AND (c.userid = '$userid')
	AND (c.timestarted = 0)
	AND (c.timecompleted is null)
	";
	$notstartedrecords = count($DB->get_records_sql($notstartedquery));
	$notstartedcount = $notstartedrecords;
	$enrollmentstats = array($totalcount,$completedcount,$progresscount,$notstartedcount);
	return $enrollmentstats;
}

//manjunath: this function returns all badge count earned by the user
function user_badge_data($id, $startdate, $enddate)
{
    global $DB;
	$goldcount = 0;
    	$silvercount = 0;
    	$bronzecount = 0;
    	$completecount = 0;
    $mainquery = "SELECT c.badgeid, c.userid, u.name
    FROM {badge_issued} c
    INNER JOIN {badge} u ON u.id = c.badgeid
    WHERE (c.dateissued between $startdate and $enddate) 
    AND (c.userid = '$id')
    ";
	
    $data = $DB->get_records_sql($mainquery);
	
    foreach ($data as $badge) {
    	

    	$compare = substr($badge->name, 0, 1);
		
        if(($compare === 'G'))
        {
            $goldcount = $goldcount + 1;
        }
        if($compare === 'S')
        {
            $silvercount = $silvercount + 1;
        }
        if($compare === 'B')
        {
            $bronzecount = $bronzecount + 1;
        }
        if($compare === 'C')
        {
            $completecount = $completecount + 1;
        }
    }
    if(!empty($data)){
        $badgestatistics = array($goldcount, $silvercount, $bronzecount, $completecount);
        return $badgestatistics;
    }
    
    

}

//manjunath: this function return the enrollment graph data
function user_graphdata_enrollment($id, $startdate, $enddate)
{
    global $DB;
//get the year and month from the start date
    $newstdate = date('Ym',$enddate);
    $sql ="
    SELECT c.id as cid, FROM_UNIXTIME(ue.timestart, '%Y%m') as timeconvert
    FROM {user} u
    INNER JOIN {user_enrolments} ue ON ue.userid = u.id
    INNER JOIN {enrol} e ON e.id = ue.enrolid
    INNER JOIN {course} c ON e.courseid = c.id
    WHERE ue.timestart <= $enddate and ue.userid = $id
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
  for($i=1; $i<=5 ;$i++) {
    $timevalue = $newstdate - $i ;
    $arraykey = substr($timevalue,4);
    if(in_array($timevalue,$array_value)){
       //$count = array_count_values(array_column($graphdata,'timeconvert'))[$timevalue]; 
	   
	   $uid_counts = array_count_values($array_value);
			$count = $uid_counts[$timevalue];
			
   }else{
     $count = 0;
 }
 $dateObj   = DateTime::createFromFormat('!m', $arraykey);
 $monthName = $dateObj->format('M');
 $returnarray[$monthName] = $count;
}
return $returnarray;
}

//manjunath: this function returns the completion graph data
function user_graphdata_completions($id, $startdate, $enddate)
{
    global $DB;
    $newstdate = date('Ym',$enddate);
    $sql ="
    SELECT timeenrolled as enrolldate, FROM_UNIXTIME(cc.timeenrolled, '%Y%m') as timeconvert
    FROM {course_completions} cc
    WHERE cc.timeenrolled <= $enddate and cc.userid = $id and cc.timecompleted is not null
    ORDER BY timeconvert desc
    ";
    $completegraphdata = $DB->get_records_sql($sql);
    $array_value1 = [];
    if(!empty($completegraphdata)){
        foreach ($completegraphdata as $key => $value1) {
          $array_value1[] =$value1->timeconvert; 
      }

  }
  $returndata = array();
  for($i=1; $i<=5 ;$i++) {
    $timevalue = $newstdate - $i ;
    $arraykey = substr($timevalue,4);

    if(in_array($timevalue,$array_value1)){
        //$count = array_count_values(array_column($completegraphdata,'timeconvert'))[$timevalue];
		
		$uid_counts = array_count_values($array_value1);
			$count = $uid_counts[$timevalue];
    }else{
     $count = 0;
 }
 $dateObj   = DateTime::createFromFormat('!m', $arraykey);
 $monthName = $dateObj->format('M');
 $returndata[$monthName] = $count;
}
return $returndata;
}

