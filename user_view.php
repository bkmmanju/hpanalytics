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
require_once('../../config.php');
require_once('user_view_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_once('user_lib.php'); 
require_once($CFG->dirroot.'/local/hpanalytics/csslinks.php');
global $OUTPUT, $CFG, $USER;
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
if(isloggedin()){
	$id = optional_param('cid','',PARAM_INT);//this is the test battery id
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
		$mform = new local_hpanalytics_form($CFG->wwwroot . '/local/hpanalytics/user_view.php',array('id'=>$id));
	}else{
		$mform = new local_hpanalytics_form($CFG->wwwroot . '/local/hpanalytics/user_view.php',array('id'=>0));
	}
	$data = $mform->get_data();
	echo $OUTPUT->header();   
	//here we are creating the page heading and other page link button.
	$pagetitlebox = html_writer::start_tag('div',  array('class' => 'row'));
	$pagetitlebox .= html_writer::start_tag('div',  array('class' => 'col-md-12'));
	$pagetitlebox .= html_writer::start_tag('h4',  array());
	$pagetitlebox .= get_string('managetest','local_hpanalytics');
	$pagetitlebox .= html_writer::end_tag('h4');
	$pagetitlebox .= html_writer::end_tag('div');
	$pagetitlebox .= html_writer::end_tag('div');
	$pagetitlebox .= get_string('guideline', 'local_hpanalytics');
	echo $pagetitlebox.'<hr>';
	$mform->display();

//manjunath: Getting data from the form
	$data = $mform->get_data();
	if ($mform->is_cancelled()) {
		redirect(moodle_url($CFG->wwwroot.'/my'));
	} else if($data){
		$updatedata = new \stdClass;
		$updatedata->reportstart = $data->reportstart;
		$updatedata->reportend = $data->reportend;
		if(!empty($data->userid)){
			$userid = $data->userid;
		}
		else{
			$userid = $USER->id;
		}
		$start_date = $data->reportstart;
		$end_date = $data->reportend;
//manjunath: getting the user enrollment count 
		$enrollment_data = get_user_enrollment_data($userid, $start_date, $end_date);
		if(!empty($enrollment_data)){
			$realvalue = $enrollment_data;
		}
//manjunath: getting the certificate count
		$certificate_data = user_certificate($userid, $start_date, $end_date);
		if(!empty($certificate_data))
		{
			$certificatecount = $certificate_data;

		}
//manjunath: getting  course completion,inprogress and not started counts
		$course_stats = user_course_stats($userid, $start_date, $end_date);
		$totalcount = $course_stats['0'];
		$completedcount = $course_stats['1'];
		$inprogresscount = $course_stats['2'];
		$notstartedcount = $course_stats['3'];
//manjunath: calculating badge count here
		$badge_data = user_badge_data($userid, $start_date, $end_date);
		
		if(!empty($badge_data)){
			$goldbadgecount = $badge_data['0'];
			$silverbadgecount = $badge_data['1'];
			$bronzebadgecount = $badge_data['2'];
			$completionbadgecount = $badge_data['3'];	
		}else{
			$goldbadgecount = 0;
			$silverbadgecount = 0;
			$bronzebadgecount = 0;
			$completionbadgecount = 0;
		}
//manjunath: getting graph data from these functions
		$graphdata = user_graphdata_enrollment($userid, $start_date, $end_date);
		$completiongraph = user_graphdata_completions($userid, $start_date, $end_date);

//manjunath: getting user image
		global $CFG, $OUTPUT, $COURSE;
		$user = $DB->get_record('user', array('id' => $userid));
		$params = array('size' => '100', 'imagealt' => 'aaa', 'class' => 'someclass');
		if(!empty($user)){
			$userpicture = $OUTPUT->user_picture($user,$params);
		}
		$userlink = html_writer::link('', $userpicture.'<br/>'. fullname($user));

//manjunath: Dashboard display based on search results
	$data = html_writer::start_div('container dash');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');//course completion block
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('coursecompletion', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body first-graph');
	$data .= html_writer::tag('h2',get_string('nodata','local_hpanalytics'));
	$data .= html_writer::start_div('firstchart',array('id' => 'donutchart'));
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column

	$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
	$data .= html_writer::start_div('card enrollments ');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('coursedetails', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center text-white1 enroll');
	if(!empty($realvalue)){
		$data .= '<h2>'.$realvalue.'</h2>'.get_string('enrollments','local_hpanalytics');
	}else if(empty($realvalue)){
		$data .= '0'.get_string('enrollments','local_hpanalytics');
	}
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-5');
	$data .= '<br/>'.$userlink;
	$profilelink = '<a class="btn btn-primary" href="'.$CFG->wwwroot.'/user/view.php?id='.$userid.'">'.'View'.'</a>';
	$dashboardlink = '<a class="btn btn-info" href="'.$CFG->wwwroot.'/my">'.'Dashboard'.'</a>';
	$data .= '<br/>'.$profilelink.'&nbsp;';
	$data .= $dashboardlink;
	$data .= html_writer::end_div();
	$data .= html_writer::start_div('col-md-7 text-left pt-3');
	$sql = "SELECT email,phone1,institution,department,city
	FROM {user}
	WHERE id = $userid";
	$userdata = $DB->get_record_sql($sql);
	$data .= html_writer::tag('p',get_string('email','local_hpanalytics').': '.$userdata->email);
	$data .= html_writer::tag('p',get_string('phone','local_hpanalytics').': '.$userdata->phone1);
	$data .= html_writer::tag('p',get_string('citys','local_hpanalytics').': '.$userdata->city);
	$data .= html_writer::tag('p',get_string('institutions','local_hpanalytics').': '.$userdata->institution);
	$data .= html_writer::tag('p',get_string('department','local_hpanalytics').': '.$userdata->department);
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end inner row
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end card
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row

	$data .= html_writer::start_div('row pt-3 pb-3');
	$data .= html_writer::start_div('col-md-2 certificate');//Certificates Display
	$data .= html_writer::start_div('card text-white1 bg-info enrollments pb-4');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('certificates', 'local_hpanalytics');
	$data .= html_writer::end_div();//end column
	$data .= html_writer::start_div('card-body pt-5 text-center');
	$data .= '<i class="fa fa-certificate" aria-hidden="true" style="font-size:50px"></i>';
	if(!empty($certificatecount) && $certificatecount == 1 ){
		$data .= '<h1>'.$certificatecount.'</h1>' . get_string('earnedcertificate','local_hpanalytics');
	}elseif(!empty($certificatecount) && $certificatecount > 1){
		$data .= '<h1>'.$certificatecount.'</h1>' . get_string('earnedcertificates','local_hpanalytics');
	}
	else{
		$data .= '<h1>No </h1><br/>'. get_string('earnedcertificate','local_hpanalytics');
	}
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end column

//manjunath: four badges count will display here..
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

	$data .= html_writer::start_div('col-md-2');//Average time spent
	$data .= html_writer::start_div('card text-white1 bg-success enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('averagetimespent', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= '<i class="fa fa-clock-o" style="font-size:50px"></i> &nbsp;<h1>175</h1></br>'.get_string('timespent','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row

	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6');//Business insights graph
	$data .= html_writer::start_div('card  bg-default enrollments');
	$data .= html_writer::start_div('card-header bg-transparent text-center');
	$data .= get_string('businessinsights', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_div('business p-3',array('id' => 'chart_div'));
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column

	$data .= html_writer::start_div('col-md-6');//Course enrollments graph 
	$data .= html_writer::start_div('card  bg-default1 enrollments');
	$data .= html_writer::start_div('card-header bg-transparent text-center');
	$data .= get_string('courseenrollments', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_div('chart_wrap');
	$data .= html_writer::start_div('p-3 enrollmentchart',array('id' => 'line_top_x'));
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row
	$data .= html_writer::end_div();//end container
	echo $data;
}
//Dashboard display based on filter results ends
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
			pieHole: 0.5,
			backgroundColor: { fill:'transparent' },
			legend: 'none',
			'height':230
		};


		var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
		chart.draw(data, options);
	}
</script>
<!--manjunath: badge display js code -->
<!-- 1st -->
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
          // ['Completion Badges',     <?php echo json_encode($completionbadgecount, JSON_NUMERIC_CHECK); ?>],
          ['Completion Badges',     1],

          ]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
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




<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawVisualization);

	function drawVisualization() {
		var data = google.visualization.arrayToDataTable([
			['Course', 'Course Completion', 'Average'],
			<?php 
			$i =0;
			foreach ($completiongraph as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo "['".$key."',  ".$value.", ".$value."]";
				$i++;
			}
			?>
			]);
		var options = {
			legend: 'none',
			height: 300,
			backgroundColor: { fill:'transparent' },
			vAxis: {title: 'Completion'},
			hAxis: {title: 'Courses'},
			seriesType: 'bars',
			series: {1: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>

<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Month');
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
			height:300,

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

