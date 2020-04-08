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
global $DB, $COURSE, $CFG, $USER;

$resource1 = $DB->get_record('pastel_resource', array('id'=>'28'));
$lien1 = $resource1->url;
$titre1 = $resource1->title;
$domaine1 = $resource1->source;
$description1 = $resource1->description;

$resource2 = $DB->get_record('pastel_resource', array('id'=>'29'));
$lien2 = $resource2->url;
$titre2 = $resource2->title;
$domaine2 = $resource2->source;
$description2 = $resource2->description;

$resource3 = $DB->get_record('pastel_resource', array('id'=>'30'));
$lien3 = $resource3->url;
$titre3 = $resource3->title;
$domaine3 = $resource3->source;
$description3 = $resource3->description;

$cours = $DB->get_record('pastel', array('id'=>'2'));
$totalDiapo = $cours->intro;
$adresseStream = $cours->stream;

$beginning=1516113002;

$slides = $DB->get_records_sql('SELECT * FROM {pastel_slide} WHERE activity = 263 AND timecreated > '.$beginning);

$slide1 = $DB->get_record_sql('SELECT MAX(id) AS maxid FROM {pastel_slide} WHERE timecreated < 1508851917'); // S à records, MIN(id) ; code 15h33
$slide1bis = $slide1->maxid;

$transcription = $DB->get_records_sql('SELECT * FROM {pastel_transcription} WHERE activity = 263 AND timecreated > '.$beginning);

$notesImportees = $DB->get_records_sql('SELECT * FROM {pastel_user_event} WHERE user_id = '.$USER->id.' AND activity = 263 AND object = "notesEditor" AND timecreated >'.$beginning);
$notes = $notesImportees->data;

// $stockTranscription = $DB->get_records_sql('SELECT * FROM {pastel_transcription} WHERE activity = 263 AND timecreated > 1512165790');


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

$changements = $DB->get_records_sql('SELECT timecreated,page FROM {pastel_slide} WHERE course = 20 AND activity = 263');

$url_diapo = "http://la-pastel.univ-lemans.fr/mod/pastel_/pix/page/SLIDES-CM-2017-page-";
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

$PAGE->set_url('/mod/pastel/config1.php', array('id' => $cm->id));
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

print('
  <script src="ckeditor/ckeditor.js"></script>

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="jquery.scrollTo-2.1.2\jquery.scrollTo.js"></script>

  <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseId . '">Retour</a>
  <a target="_blank" href="' . $CFG->wwwroot . '/mod/pastel/view_export.php?id=' . $id. '" style="float:right;" style="float:right;" onclick="notifierAlerte("liens","export",0,"")">Export des notes</a>

  <div id = "progressbar">
    <div class = "contenant" id="progressbarContenant">
      <!--  <div class="tick" style="left:100px;"></div> -->
    </div>
  </div>
  <hr>

  <div class="clearfix hauteur_max">
    <div class="zone_tiers">
      <div id="webcam_view">
        <iframe width="320" height="200" 
          src="'.$adresseStream.'?autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0" 
          frameborder="0" allowfullscreen>
        </iframe>
      </div>
      <form id="open_question_form" style="margin-top:5px;">
        <input type="text" name="question_ouverte" id="open_question_field" placeholder="Entrez votre question ou formulez votre besoin" style="width:100%;">
      </form>

      <div id="slides_panel" style="margin-top:5px;">
        <div class="">
          <img id="slides_view" src="'.$url_diapo.sprintf("%'.03d\n", $nbDiapo).'.jpg" class="diapo" onclick="zoomImage(true)">
        </div>
        <div style="margin-top:5px;">
          <button id="slides_previous" class="uiBtn">⇦</button>
          <button id="slides_next" class="uiBtn">⇨</button>
          <button id="slides_direct" style="float:right;" class="uiBtn">⬤</button>
        </div>
      </div>
      <div id="alert_panel" class="">
        <br />
        <button id="alert_speed" class="uiBtn">Le cours va trop vite</button>
      </div>
    </div>

    <div class="zone_elargie tscrpt">
      <div id="transcription" class="leftCentered interligneDouble">
        <div class="">
          <div class="anchor" style="margin:1em;">
            Bienvenue dans ce cours, vous en retrouverez ici la transcription en direct.
          </div>
        </div>');
        $keys = array_keys($slides);
        foreach(array_keys($keys) AS $k ){
        //for ($i=0; $i < count($slides); $i++) { 
        //foreach ($slides as $x) {
          if ($slides[$keys[$k]]->page != $slides[$keys[$k-1]]->page) {
            print('<div class="transcriptWrapper"> <div class="blocTranscript" onclick="gestionPopup(this)">DIAPO ');
            $y=$slides[$keys[$k]]->page;
            print_r($y);
            print(' : ');
            foreach ($transcription as $z) {
              if(($z->timecreated >= $slides[$keys[$k]]->timecreated and $z->timecreated < $slides[$keys[$k+1]]->timecreated)) {
                print_r($z->text);
              }
            }
            print('</div></div>');
          }
        } 
        print('
        <div class="transcriptWrapper">
          <div id="slide1" class="blocTranscript" onclick="gestionPopup(this)"> DIAPO '.$nbDiapo.' : 
          </div>
        </div>
      </div>
    </div>

    <div class="zone_ressources">
      <div id="resources_view">
        <div class="field_resource">
          <span style="position: absolute;right:2px;top:-4px;color:lightGray;font-weight: bold;z-index=-1;">RESSOURCES EXTERNES</span>
          <!-- <a target="_blank" href="'.$lien1.'">'.$titre1.'</a>
          <span style="float:right;">'.$domaine1.'</span></br>
          '.$description1.' -->
          <button id="like1" class="like" onclick="like(this,0)">👍</button><button id="dislike1" class="like" onclick="dislike(this,0)">👎</button><a id="url1" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",1,"")>T.E.L. </a><br />
          <p id="description1" class="description_ressource"> </p>

          <button id="like2" class="like" onclick="like(this,1)">👍</button><button id="dislike2" class="like" onclick="dislike(this,1)">👎</button><a id="url2" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",2,"")>Speech Transcription </a><br />
          <p id="description2" class="description_ressource"> </p>

          <button id="like3" class="like" onclick="like(this,2)">👍</button><button id="dislike3" class="like" onclick="dislike(this,2)">👎</button><a id="url3" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",3,"")>Expectation </a><br />
          <p id="description3" class="description_ressource"> </p>

          <button id="like4" class="like" onclick="like(this,3)">👍</button><button id="dislike4" class="like" onclick="dislike(this,3)">👎</button><a id="url4" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",4,"")>EM Models </a><br />
          <p id="description4" class="description_ressource"> </p>

          <button id="like5" class="like" onclick="like(this,4)">👍</button><button id="dislike5" class="like" onclick="dislike(this,4)">👎</button><a id="url5" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",5,"")>Algorithm </a><br />
          <p id="description5" class="description_ressource"> </p>

        </div>

        <div class="field_resource">
          <span style="position: absolute;right:2px;top:-4px;color:lightGray;font-weight: bold;z-index=-1;">QUESTIONS DE L\'AUDIENCE</span>
          <button id="like6" class="like" onclick="like(this,5)">👍</button><button id="dislike6" class="like" onclick="dislike(this,5)">👎</button><a id="url6" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",6,"")>Convergence </a><br />
          <p id="description6" class="description_ressource"> </p>

          <button id="like7" class="like" onclick="like(this,6)">👍</button><button id="dislike7" class="like" onclick="dislike(this,6)">👎</button><a id="url7" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",7,"")>Maximization </a><br />
          <p id="description7" class="description_ressource"> </p>

          <button id="like8" class="like" onclick="like(this,7)">👍</button><button id="dislike8" class="like" onclick="dislike(this,7)">👎</button><a id="url8" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",8,"")>Perplexity </a><br />
          <p id="description8" class="description_ressource"> </p>

        </div>

      </div>
    </div>

  </div>

  <div class="popup">
      <form>
        <textarea name="popupNotes" id="popupNotes"></textarea>
      </form>
      <br />
      <button id="alert_difficulty" class="uiBtn" onclick="notifierAlerte(\'alert\',\'difficulty\', paragrapheClique ,\'\')">Besoin de plus d\'informations</button>
  </div>

  <div id="overlay" class="overlay" onclick="zoomImage(false)">
    <div class="zoomContainer">
      <img id="slides_view_big" src="'.$url_diapo.sprintf("%'.03d\n", $nbDiapo).'.jpg" class="imageZoomee" onclick="zoomImage(false)">
    </div>
  </div>

  <div id="info" style="float:none;">
  <!-- <button onclick="showPopup()">
       Show popup
     </button> -->
   ');

//print("#####");
//print_r($slides);
//print_r($transcription);

print('
    </div>
    <script>
    //_____________________________________________________________________________________________________________

    var beginning = '.$beginning.' ;
    var momentClic = Date.now()/1000 - beginning;

    CKEDITOR.replace( "popupNotes" );

    CKEDITOR.instances.popupNotes.on( "save", function( evt ) {
      notifierAlerte("popup", "notesEditor", paragrapheClique, evt.editor.getData());
      stockNotes[paragrapheClique] = evt.editor.getData();
      console.log(stockNotes);
      return false;
    });

    //var editeur = FCKeditorAPI.GetInstance("popupNotes");

    var surFrise = false ;
    var xFrise ;
    var pourcentage ;
    var offset ;

    

    var diapoCourante = '.$nbDiapo.';
    var diapoVisualisee = diapoCourante;

    var wsUri = "ws://la-pastel.univ-lemans.fr:8000/" ;
    var output ;

    var user_id ;
    var course_id ;
    var activity_id ;

    var state_direct = true ;

    var stockTemps = [] ;
    var stockNotes = [""] ;
    var evalRessources = [0,0,0,0,0,0,0,0];
    var stockRessource = [{},{},{},{},{}];
    var stockQuestions = [{},{},{}];

    var paragrapheClique = 0 ;
    var objParagraphe;
    ');

foreach ($changements as $item){
  print('
    if ('.$item->timecreated.' >= beginning){
    stockTemps.push(
      {stockTimestamp : '.$item->timecreated.', stockPage : '.$item->page.'}
    );}
  ');
}

foreach ($notesImportees as $item){
  print('
    stockNotes['.$item->page.'] = "'.$item->data.'";
  ');
}

    print(' 
    $("#transcription").scrollTo("100%");

    $( "#progressbar" ).height(15);

    //setInterval(function(){ console.log("Event fired"); }, 3000);

    $( function() {
      $( "#progressbar" ).progressbar({
          value: 100
      });
    } );

    function numeroDiapo (n) {
      if (n !== undefined){
        if (n<10){
          return "00".concat(n) ;
        } else if (n>9 && n<100) {
          return "0".concat(n) ;
        } else {
          return n ;
        }
      } else {
        return "001";
      }
    }

    var barreConf = document.getElementById("progressbar");

    // Création des ticks sur la frise
    stockTemps.forEach( function(item) {
      ajouterTick( Math.round((barreConf.offsetWidth*(item.stockTimestamp-beginning) / (Date.now()/1000-beginning))) );
    });

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

    // Clic sur la frise
      document.getElementById("progressbar").addEventListener("click", function() {
        notifierAlerte("barre","barre",0,"");
        // Changement vers état différé
        setDirect(false);
        // Clic va chercher le taux de progression et scrolle de ce taux
        $("#progressbar").progressbar("value", pourcentage); // offset d origine inconnue
        $("#transcription").scrollTo(String(pourcentage).concat("%"));
        // timestamp du moment cliqué
        momentClic = (Date.now()/1000-beginning) * ($("#progressbar").progressbar("value")/100) + beginning ;
        var pageMoment ;
        stockTemps.forEach(function(item){
          if (item.stockTimestamp < momentClic ) {
            pageMoment = item.stockPage;
          }
        });
        var number = numeroDiapo(pageMoment)  ;
        document.getElementById("slides_view").src="'.$url_diapo.'" + number + ".jpg";
        document.getElementById("slides_view_big").src="'.$url_diapo.'" + number + ".jpg";
      });

      // Quand il y a un scroll de la transcription : A rajouter la gestion de state_direct
      $("#transcription").scroll(function(evenement){
        updateTimeline();
        if (evenement.originalEvent === undefined) {
          // c est l ordi
        } else {
          // c est l user
        }
      });

    } else {
      console.log( "Barre non detectee" );
    }

    document.getElementById("alert_speed").addEventListener("click", function() {
      notifierAlerte("alert","speed",diapoVisualisee,"");
    });

    // A SUPPRIMER
    // document.getElementById("alert_difficulty_2").addEventListener("click", function() {
    //   notifierAlerte("alert","difficulty","");
    //   document.getElementById("alert_difficulty_2").style.background = "red";
    // });

    document.getElementById("slides_previous").addEventListener("click", function() {
      if (diapoCourante>1) {
        pageArriere();
        diapoVisualisee -=1;
        notifierAlerte(\'alert\',\'precedent\', diapoVisualisee ,"");
        setDirect(false);
      }
    });

    document.getElementById("slides_next").addEventListener("click", function() {
      if (diapoVisualisee<diapoCourante) {
        pageAvant();
        diapoVisualisee +=1;
        notifierAlerte(\'alert\',\'suivant\', diapoVisualisee ,"");
        setDirect(false);
      }
    });

    document.getElementById("slides_direct").addEventListener("click", function() {
      setDirect(!state_direct);
    });

    function showPopup(element){
      $(".popup").show();
      var coordParent = $(element).offset();
      var widthParent = $(element).width()
      $(".popup").css("left", coordParent.left + widthParent); //-30
      var haut = $("#resources_view").offset();
      $(".popup").css("top", haut.top);

      //var coordTranscript = $("#transcription").offset();
      // if (coordParent.top > coordTranscript.top ) {
      //   $(".popup").css("top", coordParent.top - 50);
      // } else {
      //   $(".popup").css("top", coordTranscript.top - 25);
      // }
      // if (coordParent.bottom > coordTranscript.bottom ) {
      //   $(".popup").css("bottom", coordParent.bottom - 50);
      // } else {
      //   $(".popup").css("bottom", coordTranscript.bottom - 25);
      // }
      $(".popup").addClass("selected");
    }

    function hidePopup(){
      $(".popup").hide();
      $(".popup").removeClass("selected");
    }

    function gestionPopup (element){
      if ($(".popup").hasClass("selected")){
        objParagraphe.parent().css("background-color", "transparent" );
        notifierAlerte("popup", "notesEditor", paragrapheClique, CKEDITOR.instances.popupNotes.getData());
        stockNotes[paragrapheClique] = CKEDITOR.instances.popupNotes.getData();
        hidePopup();
      } else {
        $(element).parent().css("background-color", "#e6e6e6" );
        objParagraphe = $(element);
        showPopup(element);
        paragrapheClique = element.innerHTML;
        var souchaine = paragrapheClique.substring(0,20);
        var sousouchaine = souchaine.replace(/[^0-9]/g, "");
        paragrapheClique = parseInt(sousouchaine);
        CKEDITOR.instances.popupNotes.setData(stockNotes[paragrapheClique]);
      }
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
    authentifie('.$USER->id.', "etudiant", 20, 263);
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

  function transcription(message) {
    //output = document.getElementById("transcription");
    cible = document.getElementsByClassName("blocTranscript");
    bloc = cible[cible.length - 1];
    bloc.innerHTML += message.text ;
    if (state_direct){
      $("#transcription").scrollTo("100%");
    } else {
      updateTimeline();
    }

    //var pre = document.createElement("p");
    //pre.style.wordWrap = "break-word";
    //pre.innerHTML = message.text;
    //bloc.appendChild(pre);
  }

  // RÉCEPTION D UN CHANGEMENT DE PAGE PAR WEBSOCKET
  function page(message) {
    // writeToScreen(\'<span>PAGE reçue </span> \' + JSON.stringify(message, null, 4));
    console.log("PAGE reçue"+message);
    if (state_direct){
    actualiserDiapo(message["page"]);
    diapoVisualisee = message["page"];
    }
    diapoCourante = message["page"];
    nouveauParagraphe();
  }

  // RÉCEPTION DE LA RESSOURCE
  function ressource(message) {
    //writeToScreen(\'<span>RESSOURCE reçue </span> \' + JSON.stringify(message, null, 4));
    if (message["mime"]=="manuel"){
      stockRessource.push(message);
      stockRessource.splice(0,1);

      console.log(stockRessource);

      $("#url5").attr("href", stockRessource[0]["url"]);
      $("#url5").html(stockRessource[0]["title"]);
      $("#description5").html(stockRessource[0]["description"]);
      var couleurLike = $("like4").css( "background-color" );
      $("#like5").css( "background-color", couleurLike );
      var couleurDislike = $("dislike4").css( "background-color" );
      $("#dislike5").css( "background-color", "couleurDislike" );

      $("#url4").attr("href", stockRessource[1]["url"]);
      $("#url4").html(stockRessource[1]["title"]);
      $("#description4").html(stockRessource[1]["description"]);
      var couleurLike = $("like3").css( "background-color" );
      $("#like4").css( "background-color", couleurLike );
      var couleurDislike = $("dislike3").css( "background-color" );
      $("#dislike4").css( "background-color", "couleurDislike" );

      $("#url3").attr("href", stockRessource[2]["url"]);
      $("#url3").html(stockRessource[2]["title"]);
      $("#description3").html(stockRessource[2]["description"]);
      var couleurLike = $("like2").css( "background-color" );
      $("#like3").css( "background-color", couleurLike );
      var couleurDislike = $("dislike2").css( "background-color" );
      $("#dislike3").css( "background-color", "couleurDislike" );

      $("#url2").attr("href", stockRessource[3]["url"]);
      $("#url2").html(stockRessource[3]["title"]);
      $("#description2").html(stockRessource[3]["description"]);
      var couleurLike = $("like1").css( "background-color" );
      $("#like2").css( "background-color", couleurLike );
      var couleurDislike = $("dislike1").css( "background-color" );
      $("#dislike2").css( "background-color", "couleurDislike" );

      $("#url1").attr("href", stockRessource[4]["url"]);
      $("#url1").html(stockRessource[4]["title"]);
      $("#description1").html(stockRessource[4]["description"]);
      $("#like1").css( "background-color", "LightGray" );
      $("#dislike1").css( "background-color", "LightGray" );
    }

    if (message["mime"]=="auto"){
      stockQuestions.push(message);
      stockQuestions.splice(0,1);

      $("#url8").attr("href", stockQuestions[0]["url"]);
      $("#url8").html(stockQuestions[0]["title"]);
      $("#description8").html(stockQuestions[0]["description"]);
      var couleurLike = $("like7").css( "background-color" );
      $("#like8").css( "background-color", couleurLike );
      var couleurDislike = $("dislike7").css( "background-color" );
      $("#dislike8").css( "background-color", "couleurDislike" );

      $("#url7").attr("href", stockQuestions[1]["url"]);
      $("#url7").html(stockQuestions[1]["title"]);
      $("#description7").html(stockQuestions[1]["description"]);
      var couleurLike = $("like6").css( "background-color" );
      $("#like7").css( "background-color", couleurLike );
      var couleurDislike = $("dislike6").css( "background-color" );
      $("#dislike7").css( "background-color", "couleurDislike" );

      $("#url6").attr("href", stockQuestions[2]["url"]);
      $("#url6").html(stockQuestions[2]["title"]);
      $("#description6").html(stockQuestions[2]["description"]);
      $("#like6").css( "background-color", "LightGray" );
      $("#dislike3").css( "background-color", "LightGray" );
    }
  }

  function indicator(message) {
    output = document.getElementById("info");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    output.appendChild(pre);
  }

  // Infos en cas derreur
  function onError(evt)
  {
    writeToScreen(\'<span style="color: red;">ERROR:</span> \' + evt.data);
  }

  // Envoi websocket
  function doSend(message)
  {
    // writeToScreen("SENT: " + message);
    websocket.send(message);
  }

  // Print des infos de connection/sent/reçu
  function writeToScreen(message)
  {
    output = document.getElementById("info");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    output.appendChild(pre);
  }

  // Authentifier la personne sur le serveur moodle
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

  // Envoyer une alerte par websocket (dif./vitesse)
  function notifierAlerte(conteneur, obj, page, donnee) {
    var data = {
      "action" : "alerte",
      "params" : { "user_id" : user_id, "container" : conteneur, "object":obj, "activity":activity_id, "course" : course_id, "data" : donnee, "page" : page }
    };

    doSend(JSON.stringify(data));
  }

  // Affichage diapo précédente
  function pageArriere(){
    if (diapoVisualisee>=2){
      actualiserDiapo(diapoVisualisee-1);
    }
  }

  // Affichage diapo suivante
  function pageAvant(){
    // ajouter controleur max à partir de diapo courante
    actualiserDiapo(diapoVisualisee+1);
  }

  function nouveauParagraphe(){
    output = document.getElementById("transcription");
    var pre = document.createElement("div");
    pre.className = "transcriptWrapper";
    // avant sur le onclick : gestionPopup(pre)
    var bloc = document.createElement("div");
    bloc.onclick = function() {
      gestionPopup(this);
    }
    bloc.className = "blocTranscript";
    bloc.innerHTML = " DIAPO " + diapoCourante.toString() + " : ";

    // On aère si il y a un point, code à refaire pour l\'adapter à cette fonction
    // if (message.indexOf(".") >= 0) {
    //   console.log("Point détecté");
    //   message = message + "<br />";
    // }

    pre.appendChild(bloc);
    output.appendChild(pre);
  }

  function updateTimeline(){
    $("#progressbar").progressbar("value", Math.round(100*$("#transcription").scrollTop() / (document.getElementById("transcription").scrollHeight - $("#transcription").height())));
  }

  $("#open_question_field").keypress(function(event) {
    if (event.which == 13 && $(this).val()!= "") {
        event.preventDefault();
        notifierAlerte("alert", "open_question_field",diapoVisualisee, $(this).val(),0);
        $(this).val("");
        return false;
    }else if (event.which == 13 && $(this).val()== "") {
      event.preventDefault();
      return false;
    }
  });

  function ajouterTick(coord){
    var div = document.getElementById("progressbarContenant");
    var tick = document.createElement("div");
    tick.className += " tick";
    tick.style.left = coord.toString() + "px";
    div.appendChild(tick);
  }

  function actualiserDiapo(num){
    document.getElementById("slides_view").src="'.$url_diapo.'".concat(numeroDiapo(num)).concat(".jpg");
    document.getElementById("slides_view_big").src="'.$url_diapo.'".concat(numeroDiapo(num)).concat(".jpg");
  }


  // REFRESH
  setInterval(function(){ 
    for (i=0;i<stockTemps.length;i++){
      $(".tick").eq(i).css("left", Math.round((barreConf.offsetWidth*(stockTemps[i]["stockTimestamp"]-beginning) / (Date.now()/1000-beginning)))+"px");
    }
    // recalculer la largeur beginning<>momentClic
    if (state_direct==false){
      $("#progressbar").progressbar("value", 100*( (momentClic - beginning) / (Date.now()/1000-beginning) ) );
    }
  }, 3000);

  function setDirect(b){
    state_direct = b;
    //notifierAlerte("alert", "direct",0, b);
    console.log(state_direct);
    if(b===true){
      $("#slides_direct").css( "background-color", "red" );
      $("#progressbar").progressbar("value",100);
      $("#transcription").scrollTo("100%");
      notifierAlerte("alert","repriseDirect", diapoVisualisee ,"");
      diapoVisualisee = diapoCourante;
      var n = numeroDiapo(diapoCourante);
      document.getElementById("slides_view").src="'.$url_diapo.'" + n + ".jpg";
      document.getElementById("slides_view_big").src="'.$url_diapo.'" + n + ".jpg";
      //notifierAlerte("alerte", "direct", 0, true);
    } else {
      $("#slides_direct").css( "background-color", "gray" );
      notifierAlerte("alerte", "direct", 0, "");
    }
  }

  function like(element, numero){
    if (evalRessources[numero]==1){
      evalRessources[numero]=0;
      $(element).css( "background-color", "LightGray" );
    } else if (evalRessources[numero]==0){
      evalRessources[numero]=1;
      $(element).css( "background-color", "#00b4e7" );
      notifierAlerte("alert","like", diapoVisualisee ,numero);
    } else if (evalRessources[numero]==-1){
      evalRessources[numero]=1;
      $(element).css( "background-color", "#00b4e7" );
      $(element).next().css( "background-color", "LightGray" );
      notifierAlerte("alert","like", diapoVisualisee ,numero);
    }
  }

  function dislike(element, numero){
    if (evalRessources[numero]==-1){
      evalRessources[numero]=0;
      $(element).css( "background-color", "LightGray" );
    } else if (evalRessources[numero]==0){
      evalRessources[numero]=-1;
      $(element).css( "background-color", "#4d4d4d" );
      notifierAlerte("alert","dislike", diapoVisualisee ,numero);
    } else if (evalRessources[numero]==1){
      evalRessources[numero]=-1;
      $(element).css( "background-color", "#4d4d4d" );
      $(element).prev().css( "background-color", "LightGray" );
      notifierAlerte("alert","dislike", diapoVisualisee ,numero);
    }
  }

  function zoomImage(bool){
    if (bool){
      $(".overlay").show();
      $(".zoomContainer").show();
      $(".imageZoomee").show();
    } else {
      $(".overlay").hide();
      $(".zoomContainer").hide();
      $(".imageZoomee").hide();
    }
  }

  // $("#slides_view").click(notifierZoom("zoom"));
  // $("#slides_view_big").click(notifierZoom("dezoom"));
  // $("#overlay").click(notifierZoom("dezoom"));

  // function notifierZoom(stri){
  //   notifierAlerte("diapo",stri,0,"");
  // }

  </script>



  ');


// Finish the page.
echo $OUTPUT->footer();