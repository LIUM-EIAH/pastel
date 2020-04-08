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

$resource1 = $DB->get_record('pastel_resource', array('id'=>'3'));
$lien1 = $resource1->url;
$titre1 = $resource1->title;
$domaine1 = $resource1->source;
$description1 = $resource1->description;

$resource2 = $DB->get_record('pastel_resource', array('id'=>'4'));
$lien2 = $resource2->url;
$titre2 = $resource2->title;
$domaine2 = $resource2->source;
$description2 = $resource2->description;

$resource3 = $DB->get_record('pastel_resource', array('id'=>'5'));
$lien3 = $resource3->url;
$titre3 = $resource3->title;
$domaine3 = $resource3->source;
$description3 = $resource3->description;

$cours = $DB->get_record('pastel', array('id'=>'2'));
$totalDiapo = $cours->intro;
$adresseStream = $cours->stream;

$indicateurs = $DB->get_records('pastel_user_event', array('course'=>'20', 'activity'=>'263', 'nature'=>'vitesse'));
$relevantTime = time() - 600;
$indicateurs2 = $DB->get_records_sql('SELECT * FROM {pastel_user_event} WHERE course = 20 AND activity = 263 AND object = "speed" AND timecreated >= '.$relevantTime.' GROUP BY user_id');
$compteIndicateurs = count((array)$indicateurs2);
$usersConnectes = $DB->get_records_sql('SELECT t1.* FROM mdl_pastel_connection t1
  JOIN (SELECT user_id, MAX(timecreated) timecreated FROM mdl_pastel_connection WHERE timecreated>1510000000 AND role="etudiant" GROUP BY user_id) t2
    ON t1.user_id = t2.user_id AND t1.timecreated = t2.timecreated;');
$compteConnectes = count((array)$usersConnectes);

$connectes = $DB->get_records_sql('SELECT * FROM {pastel_connection} WHERE course = 20 AND activity = 263 GROUP BY user_id');
$nbConnectes = count((array)$connectes);


$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$courseId = optional_param('courseid', 0, PARAM_INT);
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

$PAGE->set_pagelayout('popup');

$PAGE->set_url('/mod/pastel/config2-demo.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('mod_pastel/pastel_scripts', 'init');


/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('pastel-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header(

);

// Conditions to show the intro can change to look for own settings or whatever.
// if ($pastel->intro) {
//   echo $OUTPUT->box(format_module_intro('pastel', $pastel, $cm->id), 'generalbox mod_introbox', 'pastelintro');
// }

// Replace the following lines with you own code.
// echo $OUTPUT->heading('Test de heading');

$lastChange = $DB->get_record_sql('SELECT * FROM mdl_pastel_slide WHERE course=20 AND activity=263 ORDER BY id DESC limit 1');
$nbDiapo = $lastChange->page ?: 1;

$parameters = array('instanceid' => $cm->instance, 'courseid' => $cm->course, 'id' => $cm->id ,'sesskey' => sesskey());
$url = new moodle_url('/mod/pastel/slides_window.php', $parameters);


print('
  <script src="ckeditor/ckeditor.js"></script>

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="jquery.scrollTo-2.1.2\jquery.scrollTo.js"></script>

  <a href="' . $url . '" target="_blank">Ouvrir la fenêtre des diapositives</a>
  <br />
  <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseId . '">Retour</a>

  <div id="progressbar" class="invisible"></div>
  <hr>

  <div class="clearfix">
    <div class="zone_tiers">
      <div id="webcam_view">
        <iframe width="320" height="280" 
          src="'.$adresseStream.'?autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0" 
          frameborder="0" allowfullscreen>
        </iframe>
      </div>
      
    </div>

    <div class="zone_tiers tscrpt">

      <table border=1 style="width:100%">
        <tr>
          <td><a id="url1" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">T.E.L.</a></td>
          <td><a id="url5" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Algorithm </a></td>
        </tr>
        <tr>
          <td><a id="url2" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Sppech Transcription </a></td>
          <td><a id="url6" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Convergence </a></td>
        </tr>
        <tr>
          <td><a id="url3" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Expectation </a></td>
          <td><a id="url7" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Maximization</a></td>
        </tr>
        <tr>
          <td><a id="url4" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">EM Models </a></td>
          <td><a id="url8" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px">Perplexity </a></td>
        </tr>
      </table>

      <br />
      <hr />
      <h5>Étudiants pour qui le cours va trop vite :</h5>
      <div class="indicateur_fond">
        <div class="indicateur_25 indicBarre"></div>
        <div class="indicateur_barre" id="barreVitesse"></div>
      </div>
      <br />
      <hr />
      <h5>Étudiants consultant un point antérieur du cours :</h5>
      <div class="indicateur_fond">
        <div class="indicateur_25 indicBarre"></div>
        <div class="indicateur_barre" id="barreDirect"></div>
      </div>
      <br />
      <hr />
      <h5>Étudiants exprimant une difficulté :</h5>
      <table border=1 style="width:100%">
        <tr>
          <th>Diapositive concernée</th>
          <th>Étudiants</th>
        </tr>
        <tr>
          <td id="diff1">47 </td>
          <td id="nb1">3 </td>
        </tr>
        <tr>
          <td id="diff1">45 </td>
          <td id="nb1">1 </td>
        </tr>
        <tr>
          <td id="diff1">44 </td>
          <td id="nb1">1 </td>
        </tr>
      </table>
    </div>

    <div class="zone_tiers marges">
      <h5>Retours des étudiants</h5>
      <div id="questions" class="retours">
      </div>
    </div>

  </div>

  <div id="info"> </div>
  
    <script>
    //_____________________________________________________________________________________________________________
    
    var surFrise = false;
    var xFrise ;
    var pourcentage ;
    var offset;

    var diapoCourante = '.$nbDiapo.' ;

    var wsUri = "ws://la-pastel.univ-lemans.fr:8000/";
    var output ;

    var user_id;
    var course_id;
    var activity_id;

    var nombreEtudiants = 18;

    var tableauVitesse = [] // Stockage des alertes de cours trop rapide
    var tableauDirect = [] // Stockage des id de ceux qui sont hors du direct

    var tableauDifficulte = [];
    var stockDifficulte=[];
    var tempsDifficulte=[];

    var stockTemps = [] ;
    var stockNotes = [""] ;
    var evalRessources = [0,0,0,0,0,0,0,0];
    var stockRessource = [{},{},{},{},{}];
    var stockQuestions = [{},{},{}];

    $( function() {
      $( "#progressbar" ).progressbar({
          value: 0
      });
      $( ".indic1" ).each(function(){
        $(this).width("50%");
      });
      var posXIndicBarre = 0.25*$( ".indicBarre" ).parent().width();
      $( ".indicBarre" ).each(function() {
        $(this).css("left", posXIndicBarre);
      });
    } );

    function numeroDiapo (n) {
      if (n<10){
        return "00".concat(n) ;
      } else if (n>9 && n<100) {
        return "0".concat(n) ;
      } else {
        return n ;
      }
    }

    var barreConf = document.getElementById("progressbar");

    if (barreConf){
      barreConf.addEventListener("mouseover", function() {
        surFrise = true;
      });

      barreConf.addEventListener("mouseout", function() {
        surFrise = false;
      });

      document.addEventListener("mousemove", function(e) {
         if (surFrise){
           xFrise = e.clientX;
           offset = $("#progressbar").offset();
           pourcentage = Math.round(100*(xFrise-offset.left) / barreConf.offsetWidth);
        }
      }, false);


      document.getElementById("progressbar").addEventListener("click", function() {
        $("#progressbar").progressbar("value", pourcentage);
        // $("#transcription").scrollTo(String(pourcentage).concat("%"));
      });

      // document.getElementById("transcription").addEventListener("scroll", function() {
      //   $("#progressbar").progressbar("value", Math.round(100*$("#transcription").scrollTop() / (document.getElementById("transcription").scrollHeight - $("#transcription").height())));
      // });

    } else {
      console.log( "Barre non detectee" );
    }


  //_____________________________________________________________________________________________________________

    function testWebSocket()
  {
    websocket = new WebSocket(wsUri);
    websocket.onopen = function(evt) { onOpen(evt) };
    websocket.onclose = function(evt) { onClose(evt) };
    websocket.onmessage = function(evt) { onMessage(evt) };
    websocket.onerror = function(evt) { onError(evt) };
  }

  function onOpen(evt)
  {
    writeToScreen("CONNECTED");
    authentifie(5, "enseignant", 20, 263);
  }

  function onClose(evt)
  {
    writeToScreen("DISCONNECTED");
  }

  // function onMessage(evt)
  // {
  //   var message = JSON.parse(evt.data);
  //   switch (message.action) {
  //     case "transcription" : 
  //     transcription(message.params);
  //     break;
  //     case "page" :
  //     page(message.params);
  //     break;
  //     case "ressource" :
  //     ressource(message.params);
  //     break;
  //     case "indicator" :
  //     indicator(message.params);
  //     break;
  //     default :
  //     console.log(message);
  //     break;
  //   }
  // }

  // AFFICHER LE NUMERO DE PAGE
  function page(message) {
    // output = document.getElementById("page");
    
    // var pre = document.createElement("p");
    // pre.style.wordWrap = "break-word";
    // pre.innerHTML = message.page;
    // output.appendChild(pre);
  }

  function ressource(message) {
    output = document.getElementById("ressource");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message.url;
    output.appendChild(pre);
  }

  function indicator(message) {
    // output = document.getElementById("info");  
    // var pre = document.createElement("p");
    // pre.style.wordWrap = "break-word";
    // pre.innerHTML = message;
    // output.appendChild(pre);

    if (message["object"]=="speed") {
      var index = tableauVitesse.length - 1 ;
      while (index >= 0) {
        if (tableauVitesse[index]["user_id"]==message["user_id"]){
          tableauVitesse.splice(index, 1);
        }
        index -= 1 ;
      };
      tableauVitesse.push(message);

    } else if (message["object"]==="direct") {
      if (message["data"]===false){
        //on check si on a déjà l\id dans la liste
        if ( $.inArray(message["user_id"],tableauDirect) == -1 && message["user_id"] != 0){
          // ajouter à la liste
          tableauDirect.push(message["user_id"]);
        }
      } else {
        // retirer de la liste
        tableauDirect = jQuery.grep(tableauDirect, function(value) {
          return value != message["user_id"];
        });
      }
      console.log(message);
      console.log(tableauDirect);
    } else if (message["object"]=="difficulty"){
      if ( $.inArray(message["user_id"],tableauDifficulte) == -1 && message["user_id"] != 0){
        tableauDifficulte.push(message["user_id"]);
        stockDifficulte.push(message["page"]); //timecreated
        tempsDifficulte.push(message["timecreated"]);
        console.log("difficulté recue");
        podium(stockDifficulte);
      }
    }

    if (message["object"]=="open_question_field"){
      console.log(message);
      $("#questions").prepend("<hr><p>"+message["data"]+"</p>");
    }
  }

  // function podium(tablo){
  //   var count = 0;
  //   var indexPage;
  //   tablo.forEach(function (element){
  //     var count2  = $.grep(tablo, function (elem) { //Compter le nombre doccurences
  //       return elem === element;
  //     }).length;;
  //     if (count2>count){
  //       count = count2;
  //       indexPage = element;
  //     }
  //   });
  //   console.log(count+" "+indexPage);
  //   $("#diff1").html(indexPage);
  //   $("#nb1").html( count);
  // }

  function onError(evt)
  {
    writeToScreen(\'<span style="color: red;">ERROR:</span> \' + evt.data);
  }

  function doSend(message)
  {
    //writeToScreen("SENT: " + message);
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
      "params" : { "user_id" : user_id, "container" : conteneur, "object":obj, "activity":activity_id, "course" : course_id, "data" : donnee }
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

  function ressource(message) {
    // writeToScreen(\'<span>RESSOURCE reçue </span> \' + JSON.stringify(message, null, 4));
    if (message["mime"]=="manuel"){
      stockRessource.push(message);
      stockRessource.splice(0,1);

      console.log(stockRessource);

      $("#url5").attr("href", stockRessource[0]["url"]);
      $("#url5").html(stockRessource[0]["title"]);

      $("#url4").attr("href", stockRessource[1]["url"]);
      $("#url4").html(stockRessource[1]["title"]);

      $("#url3").attr("href", stockRessource[2]["url"]);
      $("#url3").html(stockRessource[2]["title"]);

      $("#url2").attr("href", stockRessource[3]["url"]);
      $("#url2").html(stockRessource[3]["title"]);

      $("#url1").attr("href", stockRessource[4]["url"]);
      $("#url1").html(stockRessource[4]["title"]);
    }

    if (message["mime"]=="auto"){
      stockQuestions.push(message);
      stockQuestions.splice(0,1);

      $("#url8").attr("href", stockQuestions[0]["url"]);
      $("#url8").html(stockQuestions[0]["title"]);

      $("#url7").attr("href", stockQuestions[1]["url"]);
      $("#url7").html(stockQuestions[1]["title"]);

      $("#url6").attr("href", stockQuestions[2]["url"]);
      $("#url6").html(stockQuestions[2]["title"]);
    }
  }

  // Fonction qui enlève les alertes vitesse qui datent de plus de 10 minutes et update la barre
  // + Qui indique combien d\etudiants sont a un point anterieur du cours
  setInterval(function(){
    var index = tableauVitesse.length - 1 ;
    while (index >= 0) {
      if ( (Date.now()/1000 - 600) > tableauVitesse[index]["timecreated"] ){
        tableauVitesse.splice(index, 1);
      }
      index -= 1 ;
    };
    var ratioVitesse = 35; //tableauVitesse.length/nombreEtudiants *100;
    $("#barreVitesse").width( ratioVitesse + "%");
    if (ratioVitesse<25){
      $("#barreVitesse").css( "background-color", "green" );
    } else {
      $("#barreVitesse").css( "background-color", "red" );
    }

    var ratioDirect = 10; //tableauDirect.length/nombreEtudiants *100;
    $("#barreDirect").width( ratioDirect + "%");

  }, 60000);

  setInterval(function(){
    var tableauDifficulte = [];
    var stockDifficulte= [];
    var tempsDifficulte= [];
  }, 1800000);

  </script>



  ');


// Finish the page.
echo $OUTPUT->footer();