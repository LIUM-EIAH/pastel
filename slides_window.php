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
global $DB, $CFG, $COURSE;

$instanceId = optional_param('instanceid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT); // Course_module ID.
$courseId = optional_param('courseid', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);  // ... pastel instance ID - it should be named as the first character of the module.

$cours = $DB->get_record('pastel', array('id' => $instanceId));
$totalDiapo = $cours->intro;;

if ($id) {
    $cm         = get_coursemodule_from_id('pastel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pastel  = $DB->get_record('pastel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $pastel  = $DB->get_record('pastel', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $pastel->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('pastel', $pastel->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

// Print the page header.

$PAGE->set_pagelayout('popup');

$PAGE->set_url('/mod/pastel/config1.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('mod_pastel/pastel_scripts', 'init');

$lastChange = $DB->get_record_sql('SELECT * FROM mdl_pastel_slide
                                    WHERE course='.$courseId.' AND activity='.$id.' ORDER BY id DESC limit 1');
$nbDiapo = $lastChange->page ?: 1;

$url_subname = $cours->nomdiapo;
$url_diapo = "http://la-pastel.univ-lemans.fr/mod/pastel_/pix/page/".$url_subname."-page-";

// Output starts here.
echo $OUTPUT->header(

);

print('
  <script src="ckeditor/ckeditor.js"></script>

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="jquery.scrollTo-2.1.2\jquery.scrollTo.js"></script>

  <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseId . '">Retour</a>

  <div id="progressbar"></div>
  <hr>

  <div class="clearfix">
    <div id="slides_panel">
      <div class="diapoEnseignant">
        <img id="slides_view" src="'.$url_diapo.sprintf("%'.03d\n", $nbDiapo).'.jpg" class="diapo3">
      </div>
      <div>
        <button id="slides_previous" class="uiBtn">⇦</button>
        <button id="slides_next" style="float:right;" class="uiBtn">⇨</button>
      </div>
    </div>
  <div id="info"></div>

    <script>

    var surFrise = false;
    var xFrise ;
    var pourcentage ;
    var offset;

    var diapoCourante = '.$nbDiapo.';

    var wsUri = "ws://la-pastel.univ-lemans.fr:8000/";
    var output ;

    var user_id;
    var course_id;
    var activity_id;

    function numeroDiapo (n) {
      if (n<10){
        return "00".concat(n) ;
      } else if (n>9 && n<100) {
        return "0".concat(n) ;
      } else {
        return n ;
      }
    }

    document.getElementById("slides_previous").addEventListener("click", function() {
        previousSlide();
    });

    function previousSlide(){
      console.log("mark previous slide inside");
      if (diapoCourante>1) {
        pageArriere();
        diapoCourante -=1;
        document.getElementById("slides_view").src="'.$url_diapo.'".concat(numeroDiapo(diapoCourante)).concat(".jpg");
        console.log("mark previous slide outside");
      }
    }

    $(document).keydown(function(event){
      console.log(event.keyCode);
      if (event.keyCode == 39 || event.keyCode == 34){
        event.preventDefault();
        nextSlide();
      } else if (event.keyCode == 37 || event.keyCode == 33) {
        event.preventDefault();
        previousSlide();
      }
    });

    function nextSlide(){
      console.log("mark next sliden inside");
      if (diapoCourante<'.intval($totalDiapo).') {
        console.log("mark next slide avant pageAvant");
        pageAvant();
        diapoCourante +=1;
        document.getElementById("slides_view").src="'.$url_diapo.'".concat(numeroDiapo(diapoCourante)).concat(".jpg");
        console.log("mark next slide outside");
      }
    }

    document.getElementById("slides_next").addEventListener("click", function() {
        nextSlide();
    });

    //_____________________________________________________________________________________________________________

  //   function testWebSocket()
  // {
    websocket = new WebSocket(wsUri);
    websocket.onopen = function(evt) { onOpen(evt) };
    websocket.onclose = function(evt) { onClose(evt) };
    websocket.onmessage = function(evt) { onMessage(evt) };
    websocket.onerror = function(evt) { onError(evt) };
  // }

  function onOpen(evt)
  {
    console.log("CONNECTED interne");
    authentifie(-5, "enseignant", '.$courseId.', '.$cm->id.');
  }

  function onClose(evt)
  {
    console.log("DISCONNECTED interne");
  }

  function onMessage(evt)
  {
    var message = JSON.parse(evt.data);
    switch (message.action) {
      case "transcription" :
      transcription(message.params);
      break;
      // case "page" :
      // page(message.params);
      // break;
      case "ressource" :
      ressource(message.params);
      break;
      case "indicator" :
      indicator(message.params);
      break;
      default :
      console.log(message);
      break;
    }
  }

  function ressource(message) {
    output = document.getElementById("ressource");

    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message.url;
    output.appendChild(pre);
  }

  function indicator(message) {
    output = document.getElementById("indicator");

    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    output.appendChild(pre);
  }

  function onError(evt)
  {
    writeToScreen(\'<span style="color: red;">ERROR:</span> \' + evt.data);
  }

  function doSend(message)
  {
    // writeToScreen("SENT: " + message);
    console.log("SENT: " + message);
    websocket.send(message);
  }

  function writeToScreen(message)
  {
    output = document.getElementById("info");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    output.appendChild(pre);
  }

  function authentifie(userid, role, course, activity)
  {
    user_id= userid;
    course_id = course;
    activity_id = activity;
    var data = {
      "action" : "update_status",
      "params" : { "user_id" : userid, "status" : "online", "role":role, "course":course, "activity" : activity }
    };

    doSend(JSON.stringify(data));
  }

  function notifierAlerte(conteneur, obj, donnee) {
    var data = {
      "action" : "alerte",
      "params" : {
          "user_id" : user_id,
          "container" : conteneur,
          "object":obj,
          "activity":activity_id,
          "course" : course_id,
          "data" : donnee }
    };

    doSend(JSON.stringify(data));
  }

  function pageArriere(){
    if (diapoCourante>=2){
      var data = {
        "action" : "page",
        "params" : { "activity":activity_id, "course" : course_id, "navigation" : "backward", "page" : diapoCourante }
      };

      doSend(JSON.stringify(data));
    }
  }

  function pageAvant(){
    var data = {
      "action" : "page",
      "params" : { "activity":activity_id, "course" : course_id, "navigation" : "forward", "page" : diapoCourante }
    };
      doSend(JSON.stringify(data));
  }

  </script>
');


// Finish the page.
echo $OUTPUT->footer();