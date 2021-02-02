<?php
// this inserts data in ebsco search table 
require_once('../../config.php');
global $DB,$USER;
$keyword = optional_param('keyword','',PARAM_RAW);
$insert  = new stdClass();
$insert->userid = $USER->id;
$insert->searchterm = $keyword;
$insert->searchtime = time();
$insert->extra1 = '';
$insert->extra2 = '';
$insert->extra3 = '';
$DB->insert_record('user_searchterms',$insert);

