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
 * @copyright  2019 Manjunath B K
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */
require_once('../../config.php');
require_once('viewresult_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once('lib.php'); 
require_once($CFG->dirroot.'/local/hpanalytics/csslinks.php');
global $OUTPUT, $CFG;
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$capmanger = has_capability('local/hpanalytics:manager',$context);
if(!($capmanger || is_siteadmin())){
	$errormsg = get_string('noaccess','local_hpanalytics');
    die($errormsg ); 
    //It must be included from a Moodle page

}else{
	$id = optional_param('cid','',PARAM_INT);
	$PAGE->set_pagelayout('admin');
	$PAGE->set_url($CFG->wwwroot . '/local/hpanalytics/view.php');
	$title = get_string('managetest', 'local_hpanalytics');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add($title);
	$PAGE->requires->jquery();
	include_once('jslink.php');
	if($id){
		$mform = new local_hpanalytics_form($CFG->wwwroot . '/local/hpanalytics/view.php',array('cid'=>$id));
	}else{
		$mform = new local_hpanalytics_form($CFG->wwwroot . '/local/hpanalytics/view.php',array('cid'=>0));
	}
	$data = $mform->get_data();
	if(!empty($data)){
		$course_id = $data->courseid;
		$start_date = $data->reportstart;
		$end_date = $data->reportend;
		$city = $data->city;
		$institution = $data->institution;
		$department = $data->department;
		//Manju:For downloading excel.[30/01/2020].
		$arraydata = (array)$data;
		if (array_key_exists('downloadexcel', $arraydata)) {
			$redirecturl = $CFG->wwwroot.'/local/hpanalytics/hpcl_excel.php?courses='.'all'.'&dataformat=csv&courseid='.$course_id.'&reportstart='.$start_date.'&reportend='.$end_date.'&city='.$city.'&institution='.$institution.'&department='.$department.'';
			redirect($redirecturl);
		}
	}

	echo $OUTPUT->header();   
	//here we are creating the page heading and other page link button.
	echo'<br>';
	$headingtext = get_string('managetest','local_hpanalytics');
	$url = $CFG->wwwroot.'/local/hpanalytics/hpcl_excel.php?courses='.'all'.'&dataformat=csv';
	$heading = get_heading($headingtext,'','','',$url);
	echo $heading;
	$mform->display();
	if ($mform->is_cancelled()) {
		redirect(moodle_url($CFG->wwwroot.'/my'));
	} else if($data){
		$course_id = $data->courseid;
		$start_date = $data->reportstart;
		$end_date = $data->reportend;
		$city = $data->city;
		$institution = $data->institution;
		$department = $data->department;
		//Sangita : Jan 16 2020 : check courseid.....
		$cid = $data->courseid;
		$completioncount_gsb='';
		//Manju: fo all courses.[30/01/2020].
		if($cid == 0){
			$allcourseavgtimespent_total = 0;
			$videocat = $DB->get_field('course_categories','id',array('idnumber'=>'Video'));
			$sql = 'SELECT id  FROM  {course} 
			WHERE visible = 1 AND category != "'.$videocat.'"';
			$allcourses = $DB->get_records_sql($sql);
			foreach ($allcourses as $key => $allcourse) {
				$allcourseids[] = $allcourse->id;
			}
			//find all enrollment
			$allenrollmentcount = get_enrollment_count($allcourseids,$data);
			$htmldisplay = create_html_for_display_enrollment_records($allenrollmentcount);
			echo $htmldisplay; 
			$allcoursecompletion = get_course_completion_count($allcourseids,$data);
			$htmlcoursecompletion = create_html_for_display_completion_records($allcoursecompletion);
			echo $htmlcoursecompletion;

		//Manju: For highest video course tables section.[30/01/2020]
			$videoquery1 ='SELECT c.id  FROM  {course} c 
			INNER JOIN {course_categories} ct ON ct.id = c.category
			WHERE ct.idnumber LIKE "Video"';
			$videocourses = $DB->get_records_sql($videoquery1);
			$videocourseid =[];
			foreach ($videocourses as $videocourse) {
				$videocourseid[]=$videocourse->id;
			}
			$videocrsenrollcount = get_enrollment_count($videocourseid,$data);
			$videocompletioncount = get_course_completion_count($videocourseid,$data);
			$highestvideocourses = create_html_for_highest_video_courses($videocrsenrollcount,$videocompletioncount);
			echo $highestvideocourses;

			$sqlall = 'SELECT id  FROM  {course} 
			WHERE visible = 1';
			$alcourseids=[];
			$alcourses = $DB->get_records_sql($sqlall);
			foreach ($alcourses as $key => $alcourse) {
				$alcourseids[] = $alcourse->id;
			}

			//donut chart
			$allusercoursecompletionstatus = all_course_completion_statss($alcourseids,$data);
			$totalcount = $allusercoursecompletionstatus['allcoursecompletion'];
			$completedcount = $allusercoursecompletionstatus['allcoursecpmleted'];
			$inprogresscount = $allusercoursecompletionstatus['allinprogresscourse'];
			$notstartedcount = $allusercoursecompletionstatus['allcoursenotstarted'];
			// $realvalue = $totalcount;
			//all enrolmentcount
			$allcourseenrollmentcount = get_enrollment_count($alcourseids,$data);
			//Manju:Average time spent on course by all users.[31/01/2020]
			$allcoursemean=[];
			foreach ($alcourses as $key => $crs) {
				$coursemean = get_mean_dedication_time($crs->id,$start_date, $end_date);
				$allcoursemean[]=$coursemean;
			}
			if(!empty($allcoursemean)){
				$allcourseavgtime = round((array_sum($allcoursemean))/count($allcoursemean),4);
			}else{
				$allcourseavgtime=0;
			}
			//all badges
			$allbadgescount = get_all_course_badges($alcourseids,$data);
			$goldbadgecount = $allbadgescount['gold'];
			$silverbadgecount = $allbadgescount['silver'];
			$bronzebadgecount = $allbadgescount['bronz'];
			$completionbadgecount = $allbadgescount['completionbadgecount'];

			//Manju: certificate count.
			$completioncount_gsb = $goldbadgecount+$silverbadgecount+$bronzebadgecount+$completionbadgecount;

			if(!empty($completioncount_gsb)){
				$completedcount=$completioncount_gsb;
				$notstartedcount = $totalcount-($completedcount+$inprogresscount);
			}

		}
		//Sangita: Jan 16 2020
		//Manju: for individual courses.[30/01/2020].
		if($cid != 0){
		//manjunath: getting  course completion,inprogress and not started counts
			$course_stats = course_completion_stats($course_id, $start_date, $end_date, $city, $institution, $department );
			$indtotalcount = $course_stats['0'];
			$completedcount = $course_stats['1'];
			$inprogresscount = $course_stats['2'];
			$notstartedcount = $course_stats['3'];
		//Manju: mean time spent by an user in course.[04/02/2020]
			$meandedication = get_mean_dedication_time($cid,$start_date, $end_date);
		//manju: calculating badge count here.
			$coursename = get_course_name($course_id);
			$badge_data = get_badge_data($course_id, $coursename, $start_date, $end_date, $city, $institution, $department);
			if(!empty($badge_data)){
				$goldbadgecount = $badge_data['0'];
				$silverbadgecount = $badge_data['1'];
				$bronzebadgecount = $badge_data['2'];
				$completionbadgecount = $badge_data['3'];	
			}
		//manju:adding gold, silver and bronze badge count to get completion number.[29/01/2020].
			$completioncount_gsb = $goldbadgecount+$silverbadgecount+$bronzebadgecount;
			if(empty($completioncount_gsb)){
				$completionbadgecount=$completedcount;
			}
			if(!empty($completioncount_gsb)){
				$completedcount=$completioncount_gsb;
				$inprogresscount = $indtotalcount-($completedcount+$course_stats['3']);
				$remainingcount = $indtotalcount-$completedcount;
				if($remainingcount < $course_stats['3']){
					$notstartedcount = $remainingcount;
					$inprogresscount = 0;
				}

			}
		//manjunath: getting graph data from these functions
			$graphdata = graphdata_enrollment($course_id, $start_date, $end_date, $city, $institution, $department);
			$completiongraph = graphdata_completions($course_id, $start_date, $end_date, $city, $institution, $department);
		//Manju:Get Year wise graph data.
			$yeargraphdata = get_yearwisegraph($course_id, $start_date, $end_date, $city, $institution, $department);
			$yearcompletion=0;
			$yearenroll=0;
			foreach ($yeargraphdata as $key => $value) {
				if($key === 'enrolldata'){
					$yearenroll =$value;
				}elseif($key === 'completiondata'){
					$yearcompletion =$value;
				}
			}

		//manjunath: getting course image here.
			global $CFG, $OUTPUT;
			$fs = get_file_storage(); 
			$course = $DB->get_record('course',array('id'=>$data->courseid));
			$context = context_course::instance($data->courseid); 
			$files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false); 
			$courseimage = $CFG->wwwroot . '/theme/boost/pix/dummy.png'; 
			foreach ($files as $file) { 
				$isimage = $file->is_valid_image(); 
				if ($isimage) { 
					$courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'. $file->get_contextid(). '/'. $file->get_component(). '/'. $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage); 
				} 
			}
			$course_image = '<br/><img src="'.$courseimage.'" width="200px" height="180px">';
			
		}		


		$data = html_writer::start_div('container');
		$data .= html_writer::start_div('row');
	//Manju:For course completion, not-started and in-progress donut chart.
		$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
		$data .= html_writer::start_div('card enrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('coursecompletion', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body first-graph');
		$data .= html_writer::start_div('firstchart',array('id' => 'donutchart'));
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//card ends
		$data .= html_writer::end_div();//end column
	//Manju:For total enrollment section.
		$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
		$data .= html_writer::start_div('card enrollments ');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('coursedetails', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center text-white1 enroll');
		if($cid !=0){
			if(!empty($indtotalcount)){
				$data .= '<h1>'.$indtotalcount.'</h1>'.get_string('enrollments','local_hpanalytics');
				$data .= $course_image;
			}else if(empty($indtotalcount) && !empty($course_id)){
				$data .= '<h1>0</h1>'.get_string('enrollments','local_hpanalytics');
				$data .= $course_image;
			}
		} else {
			$data .= '<h1>'.$totalcount.'</h1>'.get_string('enrollments','local_hpanalytics');
		}
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end card
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
		$data .= html_writer::start_div('row pt-3 pb-3');
	//Manju: For certificate count section.[30/01/2020].
		$data .= html_writer::start_div('col-md-2 certificate');
		$data .= html_writer::start_div('card text-white1 bg-info enrollments pb-5');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('certificates', 'local_hpanalytics');
		$data .= html_writer::end_div();//end column
		$data .= html_writer::start_div('card-body pt-5 text-center');
		$data .= '<i class="fa fa-certificate" aria-hidden="true" style="font-size:50px"></i>';
		if($completioncount_gsb === 0){
			$data .= '<h1>'.$completedcount.'</h1>' . get_string('earnedcertificate','local_hpanalytics');
		}elseif($completioncount_gsb > 0){
			$data .= '<h1>'.$completioncount_gsb.'</h1>' . get_string('earnedcertificates','local_hpanalytics');
		}else{
			$data .= '<h1>0</h1><br/>'. get_string('earnedcertificate','local_hpanalytics');
		}
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end column
//Manju: four badges count will display here.
		$data .= html_writer::start_div('col-md-8 badges');
		$data .= html_writer::start_div('card enrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('badgedistribution', 'local_hpanalytics');
		$data .= html_writer::end_div();
		$data .= html_writer::start_div('card-body badge-card');
		$data .= html_writer::start_div('row pb-4');
		if(!empty($goldbadgecount))
		{
			$data .= html_writer::start_div('col-md-3 text-center gold');
			$data .= html_writer::tag('h4',get_string('goldbadges', 'local_hpanalytics'));
			$data .= html_writer::start_div('p-2',array('id' => 'donut_single1'));
			$data .= html_writer::end_div();
			$data .= html_writer::end_div();
		}else{
			$data .= html_writer::start_div('col-md-3 text-center gold');
			$data .= html_writer::tag('h4',get_string('goldbadges', 'local_hpanalytics'));
			$data .= html_writer::start_div('pt-5');
			$data .= html_writer::tag('h2',get_string('nodata', 'local_hpanalytics'));
			$data .= html_writer::end_div();
			$data .= html_writer::end_div();
		}
		if(!empty($silverbadgecount))
		{
			$data .= html_writer::start_div('col-md-3 text-center silver');
			$data .= html_writer::tag('h4',get_string('silverbadges', 'local_hpanalytics'));
			$data .= html_writer::start_div('p-2',array('id' => 'donut_single2'));
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();
		}else{
		$data .= html_writer::start_div('col-md-3 text-center silver');
		$data .= html_writer::tag('h4',get_string('silverbadges', 'local_hpanalytics'));
		$data .= html_writer::start_div('pt-5');
		$data .= html_writer::tag('h2 pt-5',get_string('nodata', 'local_hpanalytics'));
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();
		}
		if(!empty($bronzebadgecount))
		{
		$data .= html_writer::start_div('col-md-3 text-center bronze');
		$data .= html_writer::tag('h4',get_string('bronzebadges', 'local_hpanalytics'));
		$data .= html_writer::start_div('p-2',array('id' => 'donut_single3'));
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();
		}else{
		$data .= html_writer::start_div('col-md-3  text-center bronze');
		$data .= html_writer::tag('h4',get_string('bronzebadges', 'local_hpanalytics'));
		$data .= html_writer::start_div('pt-5');
		$data .= html_writer::tag('h2 pt-5',get_string('nodata', 'local_hpanalytics'));
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();
		}
		if(!empty($completionbadgecount))
		{
		$data .= html_writer::start_div('col-md-3 text-center completion');
		$data .= html_writer::tag('h4',get_string('completionbadges', 'local_hpanalytics'));
		$data .= html_writer::start_div('p-2',array('id' => 'donut_single4'));
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end column
		}else{
		$data .= html_writer::start_div('col-md-3 text-center completion');
		$data .= html_writer::tag('h4',get_string('completionbadges', 'local_hpanalytics'));
		$data .= html_writer::start_div('pt-5');
		$data .= html_writer::tag('h2 ',get_string('nodata', 'local_hpanalytics'));
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();
		}
		$data .= html_writer::end_div();//inner row ends
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end card-body

	//for all courses no need to show this  chart
		if($cid == 0){
			$meandedication = $allcourseavgtime.get_string('seconds','local_hpanalytics');
		}
	//Manju:Average time spent on course by all users.[31/01/2020]
		$data .= html_writer::start_div('col-md-2');
		$data .= html_writer::start_div('card text-white1 bg-success enrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('averagetimespent', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center');
		if($meandedication >= 1){
			$data .= '<i class="fa fa-clock-o" style="font-size:50px"></i> &nbsp;<h1>'.$meandedication.'</h1></br>'.get_string('timespentinmin','local_hpanalytics');
		}else{
			$data .= '<i class="fa fa-clock-o" style="font-size:50px"></i> &nbsp;<h1>'.$meandedication.'</h1></br>'.get_string('timespentinsec','local_hpanalytics');
		}
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
	//for all courses no need to show this  chart
	if($cid != 0){
	//Manju:Business insights graph.
		$data .= html_writer::start_div('row');
		$data .= html_writer::start_div('col-md-6');
		$data .= html_writer::start_div('card  bg-default enrollments');
		$data .= html_writer::start_div('card-header bg-transparent text-center');
		$data .= get_string('businessinsights', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center');
		$data .= html_writer::start_div('p-3',array('id' => 'chart_div'));
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//card ends
		$data .= html_writer::end_div();//end column
	//Manju:Course enrollments graph. 
		$data .= html_writer::start_div('col-md-6');
		$data .= html_writer::start_div('card  bg-default1 enrollments');
		$data .= html_writer::start_div('card-header bg-transparent text-center');
		$data .= get_string('courseenrollments', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center');
		$data .= html_writer::start_div('p-3',array('id' => 'line_top_x'));
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//card ends
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
		$data .= html_writer::end_div();//end container

		$data .= html_writer::start_div('container');
		$data .= html_writer::start_div('row');
		$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
		$data .= html_writer::start_div('card enrollments allmaxenrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('yearlyenrollments', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body first-graph');
		$data .= html_writer::start_div('p-3',array('id' => 'year_enrolment'));
		$data .= html_writer::end_div();//end column

		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end card
		$data .= html_writer::end_div();//end column


		$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
		$data .= html_writer::start_div('card enrollments allmaxenrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('yearlycompletion', 'local_hpanalytics');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body first-graph');
		$data .= html_writer::start_div('p-3',array('id' => 'year_completion'));
		$data .= html_writer::end_div();//end column


		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end card
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
		$data .= html_writer::end_div();//end container


	}
	echo $data;

		
	
}
echo $OUTPUT->footer();

}
?>
<script type="text/javascript">
	google.charts.load("current", {packages:["corechart"]});
	google.charts.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Task', 'Hours per Day'],
			['Completed',     <?php echo json_encode($completedcount, JSON_NUMERIC_CHECK); ?>],
			['Inprogress',      <?php echo json_encode($inprogresscount, JSON_NUMERIC_CHECK); ?>],
			['Not Started',  <?php echo json_encode($notstartedcount, JSON_NUMERIC_CHECK); ?>]
			]);

		var options = {
			pieHole: 0.4,
			backgroundColor: { fill:'transparent' },
			legend: 'none',
			'height':300

		};

		var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
		chart.draw(data, options);
	}
</script>
<!--manjunath: badges display js code -->
<!-- 1st badge -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Gold Badges',   <?php echo json_encode($goldbadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'font-size':16,
			'height':150,
			colors: ['#D4AF37'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single1'));
		chart.draw(data, options);
	}
</script>
<!-- 2th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Silver Badges',     <?php echo json_encode($silverbadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			colors: ['#C0C0C0'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single2'));
		chart.draw(data, options);
	}
</script>
<!-- 3th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Bronze Badges',     <?php echo json_encode($bronzebadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			colors: ['#cd7f32'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single3'));
		chart.draw(data, options);
	}
</script>
<!-- 4th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		
		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
          ['Completion Badges',     <?php echo json_encode($completionbadgecount, JSON_NUMERIC_CHECK); ?>],
          ]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single4'));
		chart.draw(data, options);
	}
</script>

<!--manjunath: course completion js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawVisualization);

	function drawVisualization() {
		var data = google.visualization.arrayToDataTable([
			['Course', 'Course Completion'],
			<?php 
			$i =0;
			foreach ($completiongraph as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo "['".$key."',  ".$value."]";
				$i++;
			}
			?>
			]);
		var options = {
			legend: 'none',
			height: 300,
			backgroundColor: { fill:'transparent' },
			vAxis: {title: 'Completion'},
			hAxis: {title: 'Months'},
			seriesType: 'bars',
			series: {1: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>

<!--manjunath: course enrollments js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Months');
		data.addColumn('number', 'Course enrollments');
		data.addRows([
			<?php 
			$i =0;
			foreach ($graphdata as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo "['".$key."',  ".$value."]";
				$i++;
			}
			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('line_top_x'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
<!--Manju: course enrollments year wise js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Year');
		data.addColumn('number', 'Course enrollments');
		data.addRows([
			<?php 
			$i =0;
			if(!empty($yearenroll)){
				foreach ($yearenroll as $key => $value) {
					if($i!=0){
						echo ',';
					}
					echo "['".$key."',  ".$value."]";
					$i++;
				}
			}else{
				echo "[0,  0]";
			}

			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('year_enrolment'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
<!--Manju: course completions year wise js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Year');
		data.addColumn('number', 'Course Completions');
		data.addRows([
			<?php 
			$i =0;
			if(!empty($yearcompletion)){
				foreach ($yearcompletion as $gkey => $gvalue) {
					if($i!=0){
						echo ',';
					}
					echo "['".$gkey."',  ".$gvalue."]";
					$i++;
				}
			}else{
				echo "[0,  0]";

			}

			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('year_completion'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
