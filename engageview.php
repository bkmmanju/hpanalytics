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
require_once('forms/engageform.php');
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
}else{
	$id = optional_param('cid','',PARAM_INT);
	$PAGE->set_pagelayout('admin');
	$PAGE->set_url($CFG->wwwroot . '/local/hpanalytics/engageview.php');
	$title = get_string('engageheading', 'local_hpanalytics');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add($title);
	$PAGE->requires->jquery();
	$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/chart.js'), true);
	$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/custom.js'), true);
	include_once('jslink.php');
	$mform = new local_hpanalytics_engage_form();
	$data = $mform->get_data();
	$gettopvideocourse = get_top_video_course();
	if(!empty($gettopvideocourse)){
		$courseobj = $DB->get_record('course',array('id'=>$gettopvideocourse));
		$courseimage = course_image_cd($courseobj);
		$courseurl = $CFG->wwwroot.'/course/view.php?id='.$gettopvideocourse;
	}
	if ($mform->is_cancelled()) {
		redirect(moodle_url($CFG->wwwroot.'/local/hpanalytics/engageview.php'));
	} else if($data){
		$start_date=$data->reportstart;
		$end_date=$data->reportend;
		//Manju:For downloading excel.
		$arraydata = (array)$data;
		if (array_key_exists('downloadexceleng', $arraydata)) {
			$redirecturl = $CFG->wwwroot.'/local/hpanalytics/engageexcel.php?type=engage&reportstart='.$start_date.'&reportend='.$end_date.'';
			redirect($redirecturl);
		}
		if (array_key_exists('magzterreport', $arraydata)) {
			$redirecturl = $CFG->wwwroot.'/local/hpanalytics/engageexcel.php?type=magz&reportstart='.$start_date.'&reportend='.$end_date.'';
			redirect($redirecturl);
		}
		$alldata = get_all_categories_enrol_and_completion_count($data);
		$alltechcourses = get_technical_cat_course_stats($data);
		$result = loginuser_details_analytics($data);
		//for zone
		$allzonedata = get_chartdata_of_allzones($data);
	//get the total magzter count so far
		$magzcounter = 10;
		$magzcount = $DB->get_record_sql("Select sum(counter) as totalmagz from {hpclmagzcounter} WHERE (timecreated between $data->reportstart and $data->reportend)");
		//$magzcount = $DB->get_record_sql("Select sum(counter) as totalmagz from {hpclmagzcounter}");
		if (!empty($magzcount)) {
			$magzcounter = $magzcount->totalmagz;
		}

		$contentsofar = $result['sofarlogin'];
		$contentuniq = $result['uniqlogin'];
		$contentlogintoday = $result['perdaylogin'];
		$contentmagzter = $magzcounter;
	}else{
		//Manju: at the first access of this page without date selection.[05/02/2020] 
		$recips =get_admin();
		$alldate = new \stdClass();
		$alldate->reportstart= $recips->firstaccess;
		$alldate->reportend= time();
		$alldata = get_all_categories_enrol_and_completion_count($alldate);
		
		$allzonedata = get_chartdata_of_allzones($alldate);
		
		$alltechcourses = get_technical_cat_course_stats($alldate);
		$result = loginuser_details_analytics($alldate);
	//get the total magzter count so far
		$magzcounter = 10;
		//$magzcount = $DB->get_record_sql("Select sum(counter) as totalmagz from {hpclmagzcounter}");
		//passing date fields also
		$magzcount = $DB->get_record_sql("Select sum(counter) as totalmagz from {hpclmagzcounter} WHERE (timecreated between $alldate->reportstart and $alldate->reportend)");
		if (!empty($magzcount)) {
			$magzcounter = $magzcount->totalmagz;
		}
		$contentsofar = $result['sofarlogin'];
		$contentuniq = $result['uniqlogin'];
		$contentmagzter = $magzcounter;
	}
	echo $OUTPUT->header();
	$mform->display();
	
	$yearwiseloggs = get_loggedin_data_yearwise();
	$monthwiseloggs = get_loggedin_data_monthise();
	
	$data = html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	
	$data .= html_writer::start_div('col-md-3');//Average time spent
	/*
	$data .= html_writer::start_div('card text-white1  engageenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('engageheading3', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body bg-warning text-center');
	$data .= '<i class="fa fa-flag flagstyle"></i> &nbsp;<h1>'.$contentlogintoday.'</h1></br>'.get_string('engageone','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	*/
	$data .= html_writer::end_div();//end column

	
	$data .= html_writer::start_div('col-md-3');//Average time spent
	$data .= html_writer::start_div('card text-white1  engageenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('engageheading1', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body bg-primary text-center');
	$data .= '<i class="fa fa-flag flagstyle" ></i> &nbsp;<h1>'.$contentsofar.'</h1></br>'.get_string('engageone','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column
	
	$data .= html_writer::start_div('col-md-3');//Average time spent
	$data .= html_writer::start_div('card text-white1  engageenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('engageheading2', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body bg-secondary text-center');
	$data .= '<i class="fa fa-flag flagstyle"></i> &nbsp;<h1>'.$contentuniq.'</h1></br>'.get_string('engageone','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column
	
	
	$data .= html_writer::start_div('col-md-3');//Average time spent
	
	/*
	$data .= html_writer::start_div('card text-white1  engageenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('engageheading4', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body bg-success text-center');
	$data .= '<i class="fa fa-flag flagstyle"></i> &nbsp;<h1>'.$contentmagzter.'</h1></br>'.get_string('engageone','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//end row
	*/
	$data .= html_writer::end_div();//end container
	
	

	$data .= html_writer::start_div('container',array('onload'=>'chartFunction()'));
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('topvideocourse', 'local_hpanalytics').html_writer::start_tag('a',array('href'=>$courseurl));
	$data .= $courseobj->fullname;
	$data .= html_writer::end_tag('a');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_tag('img',array('src'=>$courseimage,'width'=>'90%'));
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column

	$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('coursecompletion', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body');
	$data .= html_writer::start_tag('canvas',array('id'=>'canvas'));
	$data .= html_writer::end_tag('canvas');
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column

	$data .= html_writer::end_div();//row ends
	$data .= html_writer::end_div();//container ends.

	$data .= html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('techcourses', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_tag('canvas',array('id'=>'canvas-tech'));
	$data .= html_writer::end_tag('canvas');
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//row ends
	$data .= html_writer::end_div();//container ends.
	//manju: for zone chart.
	$data .= html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('zonewisecourses', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_tag('canvas',array('id'=>'canvas-zone'));
	$data .= html_writer::end_tag('canvas');
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//row ends
	$data .= html_writer::end_div();//container ends.
	//	//manju: for zone chart.
	$data .= html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('yearlyloggedinusers', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_tag('canvas',array('id'=>'canvas-yearlog'));
	$data .= html_writer::end_tag('canvas');
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//row ends
	$data .= html_writer::end_div();//container ends.

	$data .= html_writer::start_div('container');
	$data .= html_writer::start_div('row');
	$data .= html_writer::start_div('col-md-12 col-sm-12 col-xs-12');
	$data .= html_writer::start_div('card enrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('monthlyloggedinusers', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body text-center');
	$data .= html_writer::start_tag('canvas',array('id'=>'canvas-monthlog'));
	$data .= html_writer::end_tag('canvas');
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//card ends
	$data .= html_writer::end_div();//end column
	$data .= html_writer::end_div();//row ends
	$data .= html_writer::end_div();//container ends.
	echo $data;
	get_highest_enr_count_video();
}
echo $OUTPUT->footer();
?>
<script type="text/javascript">
	var barChartData = {
		labels: [<?php 
			$i =0;
			foreach ($alldata as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo '"'.$key.'"';
				$i++;
			}
			?>
			],
			datasets: [
			{
				label: "Course enrollments",
				backgroundColor: "pink",
				borderColor: "red",
				borderWidth: 1,
				data: [<?php 
					$i =0;
					foreach ($alldata as $key => $value) {
						if($i!=0){
							echo ',';
						}
						echo $value['enrol'];
						$i++;
					}
					?>]
				},
				{
					label: "Course Completions",
					backgroundColor: "lightblue",
					borderColor: "blue",
					borderWidth: 1,
					data: [<?php 
						$i =0;
						foreach ($alldata as $key => $value) {
							if($i!=0){
								echo ',';
							}
							echo $value['complete'];
							$i++;
						}
						?>]
					}
					]
				};

				var barChartData1 = {
					labels: [<?php 
						$i =0;
						foreach ($alltechcourses as $key => $value) {
							if($i!=0){
								echo ',';
							}
							echo '"'.$key.'"';
							$i++;
						}
						?>
						],
						datasets: [
						{
							label: "Course enrollments",
							backgroundColor: "pink",
							borderColor: "red",
							borderWidth: 1,
							data: [<?php 
								$i =0;
								foreach ($alltechcourses as $key => $value) {
									if($i!=0){
										echo ',';
									}
									echo $value['enrol'];
									$i++;
								}
								?>]
							},
							{
								label: "Course Completions",
								backgroundColor: "lightblue",
								borderColor: "blue",
								borderWidth: 1,
								data: [<?php 
									$i =0;
									foreach ($alltechcourses as $key => $value) {
										if($i!=0){
											echo ',';
										}
										echo $value['complete'];
										$i++;
									}
									?>]
								}
								]
							};


//data for zone chart.
var barChartData2 = {
	labels: [<?php 
		$i =0;
		foreach ($allzonedata as $zone => $value) {
			if($i!=0){
				echo ',';
			}
			echo '"'.$zone.'"';
			$i++;
		}
		?>
		],
		datasets: [
		{
			label: "Course enrollments",
			backgroundColor: "pink",
			borderColor: "red",
			borderWidth: 1,
			data: [<?php 
				$i =0;
				foreach ($allzonedata as $zone => $value) {
					if($i!=0){
						echo ',';
					}
					echo $value['enrol'];
					$i++;
				}
				?>]
			},
			{
				label: "Course Completions",
				backgroundColor: "lightblue",
				borderColor: "blue",
				borderWidth: 1,
				data: [<?php 
					$i =0;
					foreach ($allzonedata as $zone => $value) {
						if($i!=0){
							echo ',';
						}
						echo $value['complete'];
						$i++;
					}
					?>]
				}
				]
			};
//Data for year log chart.
var barChartData3 = {
	labels: [<?php 
		$i =0;
		foreach ($yearwiseloggs as $zone => $value) {
			if($i!=0){
				echo ',';
			}
			echo '"'.$zone.'"';
			$i++;
		}
		?>
		],
		datasets: [
		{
			label: "Users Loggedin",
			backgroundColor: "pink",
			borderColor: "red",
			borderWidth: 1,
			data: [<?php 
				$i =0;
				foreach ($yearwiseloggs as $zone => $value) {
					if($i!=0){
						echo ',';
					}
					echo $value;
					$i++;
				}
				?>]
			}]
		};

//
var barChartData4 = {
	labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
	datasets: [
	<?php $colorarray=array('#FFFF00','#008080','#00FFFF','#0000FF','#FF00FF','#FF0000','#808000');
	$colorcount=0;
	foreach ($monthwiseloggs as $yearname => $yeardata) {
		echo '    {
			label: "'.$yearname.'",
			borderColor: "'.$colorarray[$colorcount].'",
			borderWidth: 1,
			data: ['.$yeardata.']
		},';
		$colorcount++;
	}?>
	]
};

//options for chart 1.
var chartOptions = {
	responsive: true,
	legend: {
		position: "top"
	},
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero: true
			}
		}]
	}
}
//options for chart 2.
var chartOptions1 = {
	responsive: true,
	legend: {
		position: "top"
	},
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero: true
			}
		}]
	}
}
//options for chart 3.
var chartOptions2 = {
	responsive: true,
	legend: {
		position: "top"
	},
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero: true
			}
		}]
	}
}

//options for chart 3.
var chartOptions3 = {
	responsive: true,
	legend: {
		position: "top"
	},
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero: true
			}
		}]
	}
}
//
//options for chart 3.
var chartOptions4 = {
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero:true
			},
		}]            
	}  
}

window.onload = function() {
	var ctx = document.getElementById("canvas").getContext("2d");
	var ctx1 = document.getElementById("canvas-tech").getContext("2d");
	var ctx2 = document.getElementById("canvas-zone").getContext("2d");
	var ctx3 = document.getElementById("canvas-yearlog").getContext("2d");
	var ctx4 = document.getElementById("canvas-monthlog").getContext("2d");

	window.myBar = new Chart(ctx, {
		type: "bar",
		data: barChartData,
		options: chartOptions
	});
	window.myBar = new Chart(ctx1, {
		type: "bar",
		data: barChartData1,
		options: chartOptions1
	});
	window.myBar = new Chart(ctx2, {
		type: "bar",
		data: barChartData2,
		options: chartOptions2
	});
	window.myBar = new Chart(ctx3, {
		type: "bar",
		data: barChartData3,
		options: chartOptions3
	});
	window.myBar = new Chart(ctx4, {
		type: "line",
		data: barChartData4,
		options: chartOptions4
	});
};

</script>