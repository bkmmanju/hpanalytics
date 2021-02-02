<?php
    require_once("../../config.php");
    require_once("lib.php");
    global $PAGE, $DB, $CFG, $OUTPUT;
 
    $cid = optional_param('courseid','',PARAM_INT);
    $start_date = optional_param('reportstart','',PARAM_RAW);
    $end_date = optional_param('reportend','',PARAM_RAW);
    $city = optional_param('city','',PARAM_RAW);
    $institution = optional_param('institution','',PARAM_RAW);
    $department = optional_param('department','',PARAM_RAW);

    $delimiter = ",";
    $filename = "hpcl_report" . date('Ymd').'.csv';
    $fp = fopen('php://memory', 'w');
    // $fields = (array)get_strings(array('sno', 'eusername', 'ename', 'ronumber', 'roname','sbu','zone','location','cnaame','completionflag','cstartdate','ccompletiontime','timetakenminuts'), 'local_hpanalytics');
     $fields = (array)get_strings(array('sno', 'eusername', 'ename', 'location','cnaame','completionflag','cstartdate','ccompletiontime','timetakenminuts'), 'local_hpanalytics');
    fputcsv($fp, $fields, $delimiter);
    if(!empty($cid)) {
        $hvpdetails = get_hpcl_excel_report($cid,$start_date,$end_date,$city,$institution,$department);
    }else{
        $hvpdetails = get_hpcl_excel_report();
    }

    foreach ($hvpdetails as $hvpdetail) {
        if(!empty($hvpdetail)){
            fputcsv($fp, $hvpdetail, $delimiter);
        }    
    }
    fseek($fp, 0);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    fpassthru($fp);
    exit;

   ?>