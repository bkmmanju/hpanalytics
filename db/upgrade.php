<?php
// This file keeps track of upgrades to
// the assignment module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die();

 function xmldb_local_hpanalytics_upgrade($oldversion) {
     global $CFG,$DB;

    $dbman = $DB->get_manager();
         if ($oldversion < 2014061102) {

        // Define field patient_id to be added to patient_complete_details.
            $table = new xmldb_table('user_searchterms');
        //organization Address
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, 
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null); 
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10',
                null, null,null, null, null);
            $table->add_field('searchterm', XMLDB_TYPE_TEXT, '250',
                null, null,null, null, null);
            $table->add_field('searchtime', XMLDB_TYPE_TEXT, '250',
                null, null,null, null, null);
            $table->add_field('extra1', XMLDB_TYPE_TEXT, '250',
                null, null,null, null, null);
            $table->add_field('extra2', XMLDB_TYPE_TEXT, '250',
                null, null,null, null, null);
            $table->add_field('extra3', XMLDB_TYPE_TEXT, '250',
                null, null,null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch add field organization address.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Patientrecord savepoint reached.
        upgrade_plugin_savepoint(true, 2014061102,'local', 'hpanalytics');
    }



    return true;
}
