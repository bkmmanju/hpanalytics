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
    die($errormsg ); /// It must be included from a Moodle page

}else{

	$id = optional_param('cid','',PARAM_INT);//this is the test battery id
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
	//Manju:
	$mform = new local_hpanalytics_engage_form();
	$data = $mform->get_data();
	echo $OUTPUT->header();
	$mform->display();
	$gettopvideocourse = get_top_video_course();
	if(!empty($gettopvideocourse)){
			$courseobj = $DB->get_record('course',array('id'=>$gettopvideocourse));
				$courseimage = course_image_cd($courseobj);
				$courseurl = $CFG->wwwroot.'/course/view.php?id='.$gettopvideocourse;
	}
	if ($mform->is_cancelled()) {
		redirect(moodle_url($CFG->wwwroot.'/local/hpanalytics/engageview.php'));
	} else if($data){
		$alldata = get_all_categories_enrol_and_completion_count($data);
		$alltechcourses = get_technical_cat_course_stats($data);
		$result = loginuser_details_analytics($a='sofarlogin',$b='uniqlogin', $c='perdaylogin',$data);

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
		$alltechcourses = get_technical_cat_course_stats($alldate);
		$result = loginuser_details_analytics($a='sofarlogin',$b='uniqlogin', $c='perdaylogin',$alldate);
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
		$contentlogintoday = $result['perdaylogin'];
		$contentmagzter = $magzcounter;
	}
	$data = html_writer::start_div('container');
	$data .= html_writer::start_div('row');
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
	$data .= html_writer::start_div('card text-white1  engageenrollments');
	$data .= html_writer::start_div('card-header text-center');
	$data .= get_string('engageheading3', 'local_hpanalytics');
	$data .= html_writer::end_div();//end header
	$data .= html_writer::start_div('card-body bg-warning text-center');
	$data .= '<i class="fa fa-flag flagstyle"></i> &nbsp;<h1>'.$contentlogintoday.'</h1></br>'.get_string('engageone','local_hpanalytics');
	$data .= html_writer::end_div();
	$data .= html_writer::end_div();//end card-body
	$data .= html_writer::end_div();//end column

	$data .= html_writer::start_div('col-md-3');//Average time spent
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

window.onload = function() {
  var ctx = document.getElementById("canvas").getContext("2d");
  var ctx1 = document.getElementById("canvas-tech").getContext("2d");
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
};

</script>

<!-- <script type="text/javascript">
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

window.onload = function() {
  var ctx1 = document.getElementById("canvas-tech").getContext("2d");
  window.myBar = new Chart(ctx1, {
    type: "bar",
    data: barChartData1,
    options: chartOptions1
  });
};

</script> -->




	