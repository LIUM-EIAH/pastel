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
 * Library of interface functions and constants for module pastel
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the pastel specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package mod_pastel
 * @copyright 2016 Your Name <your@email.address>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined ( 'MOODLE_INTERNAL' ) || die ();


/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature
 *          FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function pastel_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO :
            return true;
        case FEATURE_SHOW_DESCRIPTION :
            return true;
        case FEATURE_GRADE_HAS_GRADE :
            return false;
        case FEATURE_BACKUP_MOODLE2 :
            return true;
        default :
            return null;
    }
}

/**
 * Saves a new instance of the pastel into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $pastel
 *          Submitted data from the form in mod_form.php
 * @param mod_pastel_mod_form $mform
 *          The form instance itself (if needed)
 * @return int The id of the newly inserted pastel record
 */
function pastel_add_instance(stdClass $pastel, mod_pastel_mod_form $mform = null) {
    global $DB;

    $pastel->timecreated = time ();

    // You may have to add extra stuff in here.

    $pastel->id = $DB->insert_record ( 'pastel', $pastel );

    return $pastel->id;
}

/**
 * Updates an instance of the pastel in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $pastel
 *          An object from the form in mod_form.php
 * @param mod_pastel_mod_form $mform
 *          The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function pastel_update_instance(stdClass $pastel, mod_pastel_mod_form $mform = null) {
    global $DB;

    $pastel->timemodified = time ();
    $pastel->id = $pastel->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record ( 'pastel', $pastel );

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every pastel event in the site is checked, else
 * only pastel events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid
 *          Course ID
 * @return bool
 */
function pastel_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (! $pastels = $DB->get_records ( 'pastel' )) {
            return true;
        }
    } else {
        if (! $pastels = $DB->get_records ( 'pastel', array (
                'course' => $courseid
        ) )) {
            return true;
        }
    }

    return true;
}

/**
 * Removes an instance of the pastel from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 *          Id of the module instance
 * @return boolean Success/Failure
 */
function pastel_delete_instance($id) {
    global $DB;
    if (! $pastel = $DB->get_record('pastel', array ('id' => $id))) {
        return false;
    }
    // Delete any dependent records here.
    $DB->delete_records ( 'pastel', array ('id' => $pastel->id));
    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course
 *          The course record
 * @param stdClass $user
 *          The user record
 * @param cm_info|stdClass $mod
 *          The course module info object or record
 * @param stdClass $pastel
 *          The pastel instance record
 * @return stdClass null
 */
function pastel_user_outline($course, $user, $mod, $pastel) {
    $return = new stdClass ();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course
 *          the current course record
 * @param stdClass $user
 *          the record of the user we are generating report for
 * @param cm_info $mod
 *          course module info
 * @param stdClass $pastel
 *          the module instance record
 */
function pastel_user_complete($course, $user, $mod, $pastel) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in pastel activities and print it out.
 *
 * @param stdClass $course
 *          The course record
 * @param bool $viewfullnames
 *          Should we display full names
 * @param int $timestart
 *          Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function pastel_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link pastel_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities
 *          sequentially indexed array of objects with added 'cmid' property
 * @param int $index
 *          the index in the $activities to use for the next record
 * @param int $timestart
 *          append activity since this time
 * @param int $courseid
 *          the id of the course we produce the report for
 * @param int $cmid
 *          course module id
 * @param int $userid
 *          check for a particular user's activity only, defaults to 0 (all
 *          users)
 * @param int $groupid
 *          check for a particular group's activity only, defaults to 0 (all
 *          groups)
 */
function pastel_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@link
 * pastel_get_recent_mod_activity()}
 *
 * @param stdClass $activity
 *          activity record with added 'cmid' property
 * @param int $courseid
 *          the id of the course we produce the report for
 * @param bool $detail
 *          print detailed report
 * @param array $modnames
 *          as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames
 *          display users' full names
 */
function pastel_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function pastel_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function pastel_get_extra_capabilities() {
    return array ();
}


/**
 * Get server name.
 *
 * @return string
 */
function mod_pastel_get_server_name() {
    global $CFG;

    if (! empty ( $CFG->mod_pastel_server_name )) {
        return $CFG->mod_pastel_server_name;
    } else {
        return $_SERVER ['SERVER_NAME'];
    }
}

/**
 * Get server port.
 *
 * @return string
 */
function mod_pastel_get_server_port() {
    global $CFG;

    if (! empty ( $CFG->mod_pastel_server_port )) {
        return $CFG->mod_pastel_server_port;
    } else {
        return 8000;
    }
}
function mod_pastel_update_user_status($userid, $status, $info) {
    global $DB;

    try {
        $record = new stdClass ();
        $time = new DateTime ( "now", core_date::get_user_timezone_object () );
        $record->timecreated = $time->getTimestamp ();
        $record->user_id = $userid;
        $record->role = $info->role;
        $record->course = $info->course;
        $record->activity = $info->activity;
        $record->status = $status;

        $DB->insert_record ( 'pastel_connection', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'user_status:' . $e->getMessage ());
        return false;
    }
}
function mod_pastel_transcription($userid, $params) {
    global $DB;

    try {
        $record = new stdClass ();
        $record->timecreated = $params->timecreated;
        $record->user_id = $userid;
        $record->course = $params->course;
        $record->activity = $params->activity;
        $record->text = $params->text;
        $record->hypothesis = $params->hypothesis;
        if ($params->final == "true") {
            $record->final = 1;
        } else {
            $record->final = 0;
        }
        $record->timesend = $params->timesend;

        $DB->insert_record ( 'pastel_transcription', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'transcription:' . $e->getMessage ());
        return false;
    }
}
function mod_pastel_chgtpage($userid, $params) {
    global $DB;

    try {
        $record = new stdClass ();
        $record->timecreated = $params->timecreated;
        $record->user_id = $userid;
        $record->navigation = $params->navigation;
        $record->page = $params->page;
        $record->course = $params->course;
        $record->activity = $params->activity;

        $DB->insert_record ( 'pastel_slide', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'chgtPage:' . $e->getMessage ());
        return false;
    }
}
function mod_pastel_addressource($userid, $params) {
    global $DB;

    try {
        $record = new stdClass ();
        $record->timecreated = $params->timecreated;
        $record->user_id = $userid;
        $record->course = $params->course;
        $record->activity = $params->activity;

        $record->url = $params->url;
        $record->title = $params->title;
        $record->description = $params->description;
        $record->source = $params->source;
        $record->mime = $params->mime;
        $record->tag = $params->tag;

        $record->timesend = $params->timesend;

        $DB->insert_record ( 'pastel_resource', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'addRessource:' . $e->getMessage ());
        return false;
    }
}

function mod_pastel_indicator($userid, $params) {
    global $DB;
    try {
        $record = new stdClass ();
        $record->timecreated = $params->timecreated;
        $record->user_id = $userid;
        $record->data = $params->data;
        $record->course = $params->course;
        $record->activity = $params->activity;

        $DB->insert_record ( 'pastel_indicator', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'indicator:' . $e->getMessage ());
        return false;
    }
}

function mod_pastel_saveerror($userid, $message) {
    global $DB;
    try {
        $record = new stdClass ();
        $time = new DateTime ( "now", core_date::get_user_timezone_object () );
        $record->timecreated = $time->getTimestamp ();
        $record->user_id = $userid;
        $record->message = $message;

        $DB->insert_record ( 'pastel_error', $record );
    } catch ( Exception $e ) {
        echo 'erreur lors de l ecriture des erreurs en bdd !!';
    }
}

function mod_pastel_userevent($userid, $params) {
    global $DB;

    try {
        $record = new stdClass ();
        $record->timecreated = $params->timecreated;
        $record->user_id = $userid;
        $record->container = $params->container;
        $record->object = $params->object;
        $record->nature = $params->nature;
        $record->page = $params->page;
        $record->course = $params->course;
        $record->activity = $params->activity;
        $record->data = $params->data;

        $DB->insert_record ( 'pastel_user_event', $record );
        return true;
    } catch ( Exception $e ) {
        mod_pastel_saveerror($userid, 'user_event:' . $e->getMessage ());
        return false;
    }
}

function mod_pastel_get_maxpage($params) {
    global $DB;
    $maxpage = -1;
    try {
        $reqinstance = "select instance from {course_modules} where id=".$params->activity;
        $instance = $DB->get_record_sql($reqinstance, array());
        $req = "select intro from {pastel} where id=".$instance->instance;
        $description = $DB->get_record_sql($req, array());
        $maxpage = intval(trim($description->intro));

        echo 'Page max = ' . $maxpage . ' instance = ' . $instance->instance .   PHP_EOL;
    } catch ( Exception $err ) {
        echo 'max page indetermine pour activity = ' . $params->activity .'  ERREUR :' . $err->getMessage () . PHP_EOL;
        $maxpage = -1;
    }
    return $maxpage;
}
