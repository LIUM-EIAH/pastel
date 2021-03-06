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
 * Prints a particular instance of pastel
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_pastel
 * @copyright  2020 LIUM
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace pastel with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(__DIR__ .'/tool_demo/output/index_page.php');
global $COURSE, $USER;


$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... pastel instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('pastel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pastel  = $DB->get_record('pastel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $pastel  = $DB->get_record('pastel', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $pastel->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('pastel', $pastel->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$id = $cm->id;

require_login($course, true, $cm);

$event = \mod_pastel\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $pastel);
$event->trigger();

$context = context_course::instance($COURSE->id);
$roles = get_user_roles($context, $USER->id, true);
$rolestr = array();
foreach ($roles as $role) {
    $rolestr[] = $role->shortname;
}
$rolestr = implode(', ', $rolestr);

// Page format.
$PAGE->set_pagelayout('popup');
// Print the page header.
$PAGE->set_url('/mod/pastel/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));


// Output starts here.
echo $OUTPUT->header();

echo('Rôle détecté : ' .$rolestr);

$parameters = array('instanceid' => $cm->instance, 'courseid' => $cm->course, 'id' => $cm->id, 'sesskey' => sesskey());

if (stripos($rolestr, "editingteacher") !== false ) {
    $urlens = new moodle_url('/mod/pastel/config2.php', $parameters);
    redirect($urlens);
} else if (stripos($rolestr, "student") !== false ) {
    $urletu = new moodle_url('/mod/pastel/v3_etudiant_diapo.php', $parameters);
    redirect($urletu);
}

$parameters = array('instanceid' => $cm->instance, 'courseid' => $cm->course, 'id' => $cm->id, 'sesskey' => sesskey());
$url = new moodle_url('/mod/pastel/exempleclient.html', $parameters);
$label = get_string('client', 'block_showcase');
$options = array('class' => 'overviewButton');
echo $OUTPUT->single_button($url, $label, 'get', $options);

print('en dessous du lien');

// Finish the page.
echo $OUTPUT->footer();