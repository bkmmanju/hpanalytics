<?php
require_once("../../config.php");
require_once("lib.php");
global $PAGE, $DB, $CFG, $OUTPUT;
$type = optional_param('type','',PARAM_RAW);
$start_date = optional_param('reportstart','',PARAM_RAW);
$end_date = optional_param('reportend','',PARAM_RAW);

if($type == 'engage'){
	$delimiter = ",";
	$filename = "engage_report" . date('Ymd').'.csv';
	$fp = fopen('php://memory', 'w');
	$fields = (array)get_strings(array('sno', 'eusername', 'ename', 'engageemail','number','filterfrom','filterto'), 'local_hpanalytics');
	fputcsv($fp, $fields, $delimiter);
//get all the login details and login counts.
	if($start_date == $end_date){
		$sql='SELECT u.id,count(l.id) as cnt,u.firstname,u.lastname,u.username,u.email FROM {user} u
		JOIN {local_loginevent} l on l.userid = u.id 
		WHERE l.logintime >= '.$start_date.' GROUP BY l.userid';
		$results=$DB->get_records_sql($sql);

	}else{
		$sql='SELECT u.id,count(l.id) as cnt,u.firstname,u.lastname,u.username,u.email FROM {user} u
		JOIN {local_loginevent} l on l.userid = u.id 
		WHERE l.logintime BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY l.userid';
		$results=$DB->get_records_sql($sql);
	}
	$count=1;
	$documentdata=[];
	foreach ($results as $result) {
		$dataarray=array($count,$result->firstname.' '.$result->lastname,$result->username,$result->email,$result->cnt,date('d-m-Y',$start_date),date('d-m-Y',$end_date));
		$documentdata[]=$dataarray;
		$count++;
	}
	foreach ($documentdata as $hvpdetail) {
		if(!empty($hvpdetail)){
			fputcsv($fp, $hvpdetail, $delimiter);
		}    
	}
	fseek($fp, 0);
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="' . $filename . '";');
	fpassthru($fp);
	exit;

}elseif ($type == 'magz') {
	$delimiter = ",";
	$filename = "Magzter_report" . date('Ymd').'.csv';
	$fp = fopen('php://memory', 'w');
	$fields = (array)get_strings(array('sno', 'username', 'firstname','lastname','engageemail','dateofclick','timeofclick','filterfrom','filterto'), 'local_hpanalytics');
	fputcsv($fp, $fields, $delimiter);
	//check if the start date and end date are same.
	if($start_date == $end_date){
		$magzsql="SELECT mg.id,mg.userid,mg.date,mg.timecreated,u.username,u.firstname,u.lastname,u.email FROM {user} u JOIN {magzter_user} mg ON mg.userid = u.id
		WHERE mg.timecreated >= ".$start_date."";
		$results=$DB->get_records_sql($magzsql);

	}else{
		$magzsql="SELECT mg.id,mg.userid,mg.date,mg.timecreated,u.username,u.firstname,u.lastname,u.email FROM {user} u JOIN {magzter_user} mg ON mg.userid = u.id
		WHERE mg.timecreated BETWEEN ".$start_date." AND ".$end_date."";
		$results=$DB->get_records_sql($magzsql);

	}
	$count=1;
	$documentdata=[];
	foreach ($results as $result) {
		$dataarray=array($count,
			$result->username,
			$result->firstname,
			$result->lastname,
			$result->email,
			$result->date,
			date('H:i:s',$result->timecreated),
			date('d-m-Y',$start_date),
			date('d-m-Y',$end_date));
		$documentdata[]=$dataarray;
		$count++;
	}
	foreach ($documentdata as $hvpdetail) {
		if(!empty($hvpdetail)){
			fputcsv($fp, $hvpdetail, $delimiter);
		}    
	}
	fseek($fp, 0);
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="' . $filename . '";');
	fpassthru($fp);
	exit;

}
?>