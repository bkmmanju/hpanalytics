<?php
require_once('../../config.php');
global $DB;
//find module id
$sql01 = "SELECT id from {modules} where name LIKE '%simplecertificate%'";
$moduledetails  = $DB->get_record_sql($sql01);
$mid = $moduledetails->id;

//find details
$sql = 'SELECT si.timecreated,cm.id as cmid,si.certificateid,si.userid,sim.course,cm.module as moduleid FROM {simplecertificate_issues} si 
	JOIN {simplecertificate} sim ON si.certificateid = sim.id 
	JOIN {course_modules} cm ON cm.course = sim.course AND cm.module = ?';
$simplecertificatedetails = $DB->get_records_sql($sql,array($mid));

$count = 0;

foreach ($simplecertificatedetails as $key => $simplecertificatedetail) {
	
	$cmid = $simplecertificatedetail->cmid;//course module id
	$userid = $simplecertificatedetail->userid;//user id
	
	$sqlquery = 'SELECT * FROM {course_modules_completion} WHERE coursemoduleid = ? AND userid = ?';
	$modulecompletiondetails = $DB->get_record_sql($sqlquery,array($cmid,$userid));
	if(empty($modulecompletiondetails)){
		$cid = $simplecertificatedetail->course;//course id
		$timecreated = $simplecertificatedetail->timecreated;//course id
		$insertdata = new stdClass();
		$insertdata->coursemoduleid = $cmid;
		$insertdata->userid = $userid;
		$insertdata->completionstate = 1;
		$insertdata->viewed = 1;
		$insertdata->timemodified = $timecreated;

		$DB->insert_record('course_modules_completion',$insertdata);

		$count++;

	}
	

}
echo 'Total updated count'.$count ;
?>