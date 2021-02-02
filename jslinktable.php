<?php
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/custom-table.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/jquery.dataTables.min.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/dataTables.buttons.min.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/jszip.min.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/vfs_fonts.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/hpanalytics/js/buttons.html5.min.js'), true);
// https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js
// https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js
// https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js
// https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
// https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js
// https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js
// https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js
// https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/hpanalytics/css/jquery.dataTables.min.css'));
