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
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace pastel with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(__DIR__.'/tool_demo/output/index_page.php');

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
// } else {
//     error('You must specify a course_module ID or an instance ID');
}


// require_login($course, true, $cm);

// $event = \mod_pastel\event\course_module_viewed::create(array(
//     'objectid' => $PAGE->cm->instance,
//     'context' => $PAGE->context,
// ));
// $event->add_record_snapshot('course', $PAGE->course);
// $event->add_record_snapshot($PAGE->cm->modname, $pastel);
// $event->trigger();

// Print the page header.

$PAGE->set_pagelayout('frametop');

$PAGE->set_url('/mod/pastel/pages/config1.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('pastel-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($pastel->intro) {
    echo $OUTPUT->box(format_module_intro('pastel', $pastel, $cm->id), 'generalbox mod_introbox', 'pastelintro');
}

// Replace the following lines with you own code.
echo $OUTPUT->heading('Test de heading');

print('
    <script src="ckeditor/ckeditor.js"></script>

    <div class="module_moitie vertically_centered">
        <img src="person_placeholder.jpg" class="bigPicture">
        <button>test</button>
        <button>test</button>
    </div>

    <div class="module_moitie tscrpt">
        <div id="transcript" class="leftCentered interligneDouble">
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:25  Ceci est un test de trancription.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:28  La transcription est étiquetée avec lheure. Bla bla bla, bla bla, bla bla bla bla. Bla bla bla, bla bla, bla bla bla bla.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:29  Un test plus long pour tester quand un paragraphe est plus long, bla bla bla, bla bla, bla bla bla bla. Bla bla bla, bla bla, bla bla bla bla. Bla bla bla, bla bla, bla bla bla bla.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:30  Généralement, on utilise un texte en faux latin (le texte ne veut rien dire, il a été modifié), le Lorem ipsum ou Lipsum, qui permet donc de faire office de texte dattente. Lavantage de le mettre en latin est que lopérateur sait au premier coup doeil que la page contenant ces lignes nest pas valide, et surtout lattention du client nest pas dérangée par le contenu, il demeure concentré seulement sur laspect graphique. Bla bla bla, bla bla, bla bla bla bla. Bla bla bla, bla bla, bla bla bla bla.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:35 Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:40 Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. <span class="correctif">Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</span>
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:50 Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. <span class="correctif">Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</span>
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
                <div class="transcriptWrapper">
                    <div class="blocTranscript"> 10:55 Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        <span class="tooltiptext">Diapo n° X</span>
                    </div>
                </div>
            </div>
    </div>
    ');

// Finish the page.
echo $OUTPUT->footer();