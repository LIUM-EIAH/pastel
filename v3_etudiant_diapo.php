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
global $DB, $CFG, $USER;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID
$n  = optional_param('n', 0, PARAM_INT);  // Pastel instance ID.
$courseid = optional_param('courseid', 0, PARAM_INT);

if ($id) {
    $cm      = get_coursemodule_from_id('pastel', $id, 0, false, MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pastel  = $DB->get_record('pastel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $pastel  = $DB->get_record('pastel', array('id' => $n), '*', MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $pastel->course), '*', MUST_EXIST);
    $cm      = get_coursemodule_from_instance('pastel', $pastel->id, $course->id, false, MUST_EXIST);
}

$cours = $DB->get_record('pastel', array('id' => $courseid));
$totaldiapo = $cours->intro; // Non utilise !!
$adressestream = $cours->stream;
$beginning = $cours->timedebut;

$slides = $DB->get_records_sql('SELECT * FROM {pastel_slide} WHERE activity = '.$id.' AND timecreated > '.$beginning);
$transcription = $DB->get_records_sql('SELECT * FROM {pastel_transcription}
                                        WHERE activity = '.$id.' AND timecreated > '.$beginning);
$notesimportees = $DB->get_records_sql('SELECT * FROM {pastel_user_event}
                                        WHERE user_id = '.$USER->id.' AND activity = '.$id.
                                        ' AND object = "notesEditor" AND timecreated >'.$beginning);
$notes = $notesimportees->data;
$changements = $DB->get_records_sql('SELECT timecreated, page FROM {pastel_slide}
                                    WHERE course = '.$courseid.' AND activity = '.$id);
$urlsubname = $cours->nomdiapo;
$urldiapo = "http://la-pastel.univ-lemans.fr/mod/pastel_/pix/page/".$urlsubname."-page-";
require_login($course, true, $cm);

// Print the page header.
$PAGE->set_pagelayout('popup');

$PAGE->set_url('/mod/pastel/v3_etudiant_diapo.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('mod_pastel/pastel_scripts', 'init');

// Output starts here.
echo $OUTPUT->header();

$lastchange = $DB->get_record_sql('SELECT * FROM mdl_pastel_slide WHERE course='.$courseid.
        ' AND activity='.$id.' ORDER BY id DESC limit 1');
$nbdiapo = $lastchange->page ?: 1;

print('<script src="ckeditor/ckeditor.js"></script>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="jquery.scrollTo-2.1.2\jquery.scrollTo.js"></script>
  <script>
    var studentAnswers = [];
  </script>

  <div class="PASTELcontainer">
    <div style="display:none;" class="expandGauche">
      <button onclick="afficherGauche()">></button>
    </div>

    <div class="divGauche">
      <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '">Retour</a> <span id="info"> </span>
      <button onclick="cacherGauche()"><</button>
      <iframe class="video" width="100%"
          src="'.$adressestream.'?autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0"
          frameborder="0" allowfullscreen>
      </iframe>

      <div>
        <button id="alert_speed">Le cours va trop vite</button>
      </div>

      <form id="open_question_form" style="margin-top:5px; margin-bottom:5px;">
        <input type="text" name="question_ouverte" id="open_question_field" placeholder="Entrez votre question" style="width:100%;">
      </form>

      <div class="scrollRessourcesQuestions">
        <div style="right:25px;top:-4px;color:lightGray;font-weight: bold;z-index=-1;">RESSOURCES</div>
        <div class="scrollRessourcesInside"> </div>
      </div>

      <div class="hidden">
        <form id="answerQCM">
          <h4>Réponses aux QCM</h4>
          <input type="checkbox" class="reponses" name="reponseA" value="A"> Réponse A <br>
          <input type="checkbox" class="reponses" name="reponseB" value="B"> Réponse B <br>
          <input type="checkbox" class="reponses" name="reponseC" value="C"> Réponse C <br><br>
          <input type="submit" id="submitAnswerQCM" title="Réponse envoyée" value="Valider" onclick="studentAnswers=[];
            $(\'input:checkbox[class=&quot;reponses&quot;]:checked\').each(function(){
              studentAnswers.push($(this).val());
            });
            studentAnswers.push('.$USER->id.');
            var myJSON = JSON.stringify(studentAnswers);
            console.log(myJSON);
            notifierAlerte(\'alert\',\'Answer\', \'\' ,myJSON,0);
            $(\'.reponses\').prop(\'disabled\', true);
            setTimeout(function()
              {
                $(\'.reponses\').prop(\'disabled\', false);
                $(\'.reponses\').prop(\'checked\', false);
              }, 180000);
            ">
        </form>
      </div>
    </div>

    <div class="divCentre flexVertical">
      <img id="slides_view" src="'.$urldiapo.sprintf("%'.03d\n", $nbdiapo).'.jpg" class="diapositive diapoPetite">
      <button class="needInfo" id="alert_difficulty" class="buttonInformation"
            onclick="notifierAlerte(\'alert\',\'difficulty\', diapoVisualisee ,\'\')">
        Besoin de plus d\'information
      </button>

      <div class="navigation">
        <span id="link_beginning" onclick="diapoFirst()" class="survolMain"> << </span>
        <img id="slides_view_prec" class="hidden"
            src="'.$urldiapo.sprintf("%'.03d\n", $nbdiapo - 1).'.jpg" class="PASTELpreview elementFlex">
        <span id="link_previous" onclick="diapoPrevious();" class="survolMain"> < </span>
        <form style="padding-top:20px">
          <input type="text" id="num_Diapo" autocomplete="off" name="num_Diapo"
                value="'.$nbdiapo.'" style="width:30px;"> <span>/</span><span class="max">'.$nbdiapo.'</span>
        </form>
        <span id="link_next" class="survolMain" onclick="diapoNext();"> > </span>
        <img id="slides_view_next" class="hidden"
             src="'.$urldiapo.sprintf("%'.03d\n", $nbdiapo + 1).'.jpg" class="PASTELpreview elementFlex">
        <span id="link_last" class="survolMain" onclick="diapoLast()"> >> </span>
      </div>

      <div id="transcription2" class="transcription transcriptionPetite">
');
$keys = array_keys($slides);
foreach (array_keys($keys) as $k) {
    if ($slides[$keys[$k]]->page != $slides[$keys[$k - 1]]->page) {
        print('<div class="transcriptWrapper"> <div class="blocTranscript" onclick="gestionPopup(this)">DIAPO ');
        $y = $slides[$keys[$k]]->page;
        print($y);
        print(' : ');
        foreach ($transcription as $z) {
            if ($slides[$keys[$k + 1]]->timecreated != null) {
                if ($z->timecreated >= $slides[$keys[$k]]->timecreated and $z->timecreated < $slides[$keys[$k + 1]]->timecreated) {
                    print($z->text);
                }
            } else {
                print($z->text);
            }
        }
        print('</div></div>');
    }
}

print('
      </div>
      <div>  <!-- N EXISTE QUE POUR QUE LES ELEMENTS FILS SOIENT AU MEME NIVEAU -->
        <button class="buttonTranscription" onclick="cacherTranscription()">▼</button>
        <a href="" style="">
            <img id="changeMode" style="height:25px;width:25px" src="expand.svg" onclick="changeLayout(1);return false;">
        </a>
        <button style="display:none" class="expandTranscription" onclick="afficherTranscription()">▲</button>
      </div>
    </div>

    <div style="display:none;" class="expandDroite">
        <button onclick="afficherDroite()"><</button>
    </div>
    <div class="divDroite">
      <button onclick="cacherDroite()">></button>
      <a target="_blank" href="' . $CFG->wwwroot . '/mod/pastel/view_export.php?id=' .$id.
        '" style="float:right;" onclick="notifierAlerte("liens","export",0,"")">Export des notes</a>
      <br />
      <form id="formNotes">
        <textarea name="popupNotes" id="popupNotes"></textarea>
      </form>
    </div>
  </div>');

print('</div>
  <script>
    var beginning = '.$beginning.' ;
    var momentClic = Date.now()/1000 - beginning;
    var paragrapheClique = 0 ;

    CKEDITOR.replace( "popupNotes", {
      height: 525
    });

    CKEDITOR.instances.popupNotes.on( "save", function( evt ) {
      notifierAlerte("notes", "notesEditor", paragrapheClique, evt.editor.getData());
      return false;
    });

    var surFrise = false ;
    var xFrise ;
    var pourcentage ;
    var offset ;

    var diapoCourante = '.$nbdiapo.';
    var diapoVisualisee = diapoCourante;
    var diapoMax = diapoVisualisee;

    var wsUri = "ws://la-pastel.univ-lemans.fr:8000/" ;
    var output ;

    var user_id ;
    var course_id ;
    var activity_id ;

    var state_direct = true ;

    var mode = 0;

    var stockTemps = [] ;
    var stockNotes = [""] ;
    var evalRessources = [0,0,0,0,0,0,0,0];
    var stockRessource = [{},{},{},{},{}];
    var stockQuestions = [{},{},{}];

    var objParagraphe;
');

foreach ($changements as $item) {
    print('
    if ('.$item->timecreated.' >= beginning){
    stockTemps.push(
      {stockTimestamp : '.$item->timecreated.', stockPage : '.$item->page.'}
    );}');
}

foreach ($notesimportees as $item) {
    print('
    stockNotes['.$item->page.'] = "'.$item->data.'";');
}

print('
    console.log(stockNotes[paragrapheClique]);

    CKEDITOR.instances.popupNotes.setData(stockNotes[paragrapheClique]);
    $(".transcription").scrollTo("100%");
    $( "#progressbar" ).height(15);

    setInterval(function(){
      console.log("Une minute");
      console.log(CKEDITOR.instances.popupNotes.getData());
      notifierAlerte("notes", "notesEditor", paragrapheClique, CKEDITOR.instances.popupNotes.getData());
    }, 60000);

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

    $("#num_Diapo").keypress(function(event) {
      if (event.which == 13 && $(this).val()!= "") {
        event.preventDefault();
        console.log(diapoCourante);
        console.log(parseInt($(this).val()));
        if (parseInt($(this).val())>0 && parseInt($(this).val())<=diapoCourante){ // AJOUTER NOTIF SI NOMBRE TROP GRAND
          event.preventDefault();
          diapoVisualisee = parseInt($(this).val());
          actualiserDiapo(diapoVisualisee);
          notifierAlerte("alert", "num_diapo",diapoVisualisee, $(this).val(),0);
          if (diapoVisualisee == diapoCourante){
            setDirect(true);
          }
          return false;
        }
        if (parseInt($(this).val())>diapoCourante){
          diapoVisualisee = diapoCourante;
          actualiserDiapo(diapoVisualisee);
          $("input[name=num_Diapo]").val(diapoCourante);
        }
        return false;
      }else if (event.which == 13 && $(this).val()== "") {
        event.preventDefault();
        return false;
      }
    });

    document.getElementById("alert_speed").addEventListener("click", function() {
      notifierAlerte("alert","speed",diapoVisualisee,"");
    });

    function diapoPrevious(){
      if (diapoCourante>1) {
        pageArriere();
        diapoVisualisee -=1;
        $("input[name=num_Diapo]").val(diapoVisualisee);
        notifierAlerte(\'alert\',\'precedent\', diapoVisualisee ,"");
        setDirect(false);
      }
      console.log(diapoVisualisee);
    }

    function diapoNext(){
      if (diapoVisualisee<diapoCourante) {
        pageAvant();
        diapoVisualisee +=1;
        $("input[name=num_Diapo]").val(diapoVisualisee);
        if (diapoVisualisee == diapoCourante){
          setDirect(true);
        }
        notifierAlerte(\'alert\',\'suivant\', diapoVisualisee ,"");
      }
      console.log(diapoVisualisee);
    }

    function diapoFirst(){
      diapoVisualisee =1;
      actualiserDiapo(1);
      $("input[name=num_Diapo]").val(diapoVisualisee);
      setDirect(false);
    }

    function diapoLast(){
      diapoVisualisee =diapoCourante
      actualiserDiapo(diapoCourante);
      $("input[name=num_Diapo]").val(diapoCourante);
      setDirect(true);
    }

    function showPopup(element){
      $(".popup").show();
      var coordParent = $(element).offset();
      var widthParent = $(element).width()
      $(".popup").css("left", coordParent.left + widthParent); //-30
      var haut = $("#resources_view").offset();
      $(".popup").css("top", haut.top);
      $(".popup").addClass("selected");
    }

    function hidePopup(){
      $(".popup").hide();
      $(".popup").removeClass("selected");
    }

    function gestionPopup (element){
      if ($(element).hasClass("selected")){
        objParagraphe.parent().css("background-color", "transparent" );
        $(element).removeClass("selected");
      } else {
        objParagraphe = $(element);
        paragrapheClique = element.innerHTML;
        var souchaine = paragrapheClique.substring(0,20);
        var sousouchaine = souchaine.replace(/[^0-9]/g, "");
        paragrapheClique = parseInt(sousouchaine);
        diapoVisualisee=paragrapheClique;
        $("input[name=num_Diapo]").val(diapoVisualisee);
        actualiserDiapo(paragrapheClique);
        $(".selected").parent().css("background-color", "transparent" );
        $(".selected").removeClass("selected");
        $(element).parent().css("background-color", "#e6e6e6" );
        $(element).addClass("selected");
        notifierAlerte(\'alert\',"", diapoVisualisee ,"");
      }
    }

    //_____________________________________________________________________________________________________________

    websocket = new WebSocket(wsUri);
    websocket.onopen = function(evt) { onOpen(evt) };
    websocket.onclose = function(evt) { onClose(evt) };
    websocket.onmessage = function(evt) { onMessage(evt) };
    websocket.onerror = function(evt) { onError(evt) };

  function onOpen(evt) {
    writeToScreen("CONNECTED");
    authentifie('.$USER->id.', "etudiant", '.$courseid.', '.$cm->id.');
    console.log("connection etablie");
  }

  function onClose(evt) {
    writeToScreen("DISCONNECTED");
  }

  function onMessage(evt) {
    var message = JSON.parse(evt.data);
    switch (message.action) {
      case "transcription" :
      transcription(message.params);
      break;
      case "page" :
      page(message.params);
      break;
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

  function transcription(message) {
    cible = document.getElementsByClassName("blocTranscript");
    bloc = cible[cible.length - 1];
    bloc.innerHTML += message.text ;
    if (state_direct){
      $(".transcription").scrollTo("100%");
    }
  }

  // RÉCEPTION D UN CHANGEMENT DE PAGE PAR WEBSOCKET
  function page(message) {
    console.log("PAGE reçue"+message);
    if (state_direct){
      actualiserDiapo(message["page"]);
      diapoVisualisee = message["page"];
      $("input[name=num_Diapo]").val(diapoVisualisee);
    }
    diapoCourante = message["page"];
    if (diapoCourante>diapoMax){
      diapoMax = diapoCourante;
      $(".max").text(diapoMax);
    }
    nouveauParagraphe();
  }

  // RÉCEPTION DE LA RESSOURCE
  function ressource(message) {
    console.log(message);
    console.log('.$USER->id.');
    if(message.targetid == '.$USER->id.' || '.$USER->id.' == 60 || '.$USER->id.' == 61 || message.targetid == 0) {
      ajoutBlocRessource(message);
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
  function onError(evt) {
    writeToScreen(\'<span style="color: red;">ERROR:</span> \' + evt.data);
  }

  // Envoi websocket
  function doSend(message) {
    websocket.send(message);
  }

  // Print des infos de connection/sent/reçu
  function writeToScreen(message) {
    console.log(message);
  }

  // Authentifier la personne sur le serveur moodle
  function authentifie(userid, role, course, activity) {
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
      "params" : {
          "user_id" : user_id,
          "container" : conteneur,
          "object":obj,
          "activity":activity_id,
          "course" : course_id,
          "data" : donnee,
          "page" : page
          }
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
    output = document.getElementById("transcription2");
    var pre = document.createElement("div");
    pre.className = "transcriptWrapper";
    // avant sur le onclick : gestionPopup(pre)
    var bloc = document.createElement("div");
    bloc.onclick = function() {
      gestionPopup(this);
    }
    bloc.className = "blocTranscript";
    bloc.innerHTML = " DIAPO " + diapoCourante.toString() + " : ";

    pre.appendChild(bloc);
    output.appendChild(pre);
  }

  function ajouterTick(coord){
    var div = document.getElementById("progressbarContenant");
    var tick = document.createElement("div");
    tick.className += " tick";
    tick.style.left = coord.toString() + "px";
    div.appendChild(tick);
  }

  function actualiserDiapo(num){
    document.getElementById("slides_view").src="'.$urldiapo.'".concat(numeroDiapo(num)).concat(".jpg");
    document.getElementById("slides_view_prec").src="'.$urldiapo.'".concat(numeroDiapo(num-1)).concat(".jpg");
    document.getElementById("slides_view_next").src="'.$urldiapo.'".concat(numeroDiapo(num+1)).concat(".jpg");
  }

  function setDirect(b){
    state_direct = b;
    console.log(state_direct);
    if(b===true){
      notifierAlerte("alert","repriseDirect", diapoVisualisee ,"");
      diapoVisualisee = diapoCourante;
      var n = numeroDiapo(diapoCourante);
      document.getElementById("slides_view").src="'.$urldiapo.'" + n + ".jpg";
    } else {
      notifierAlerte("alerte", "direct", 0, "");
    }
  }

  function like(element, numero, titre){
    if (evalRessources[numero]==1){
      evalRessources[numero]=0;
      $(element).css( "background-color", "LightGray" );
    } else if (evalRessources[numero]==0){
      evalRessources[numero]=1;
      $(element).css( "background-color", "#00b4e7" );
      notifierAlerte("alert","like", diapoVisualisee ,titre);
    } else if (evalRessources[numero]==-1){
      evalRessources[numero]=1;
      $(element).css( "background-color", "#00b4e7" );
      $(element).next().css( "background-color", "LightGray" );
      notifierAlerte("alert","like", diapoVisualisee ,titre);
    }
  }

  function dislike(element, numero, titre){
    if (evalRessources[numero]==-1){
      evalRessources[numero]=0;
      $(element).css( "background-color", "LightGray" );
    } else if (evalRessources[numero]==0){
      evalRessources[numero]=-1;
      $(element).css( "background-color", "#4d4d4d" );
      notifierAlerte("alert","dislike", diapoVisualisee ,titre);
    } else if (evalRessources[numero]==1){
      evalRessources[numero]=-1;
      $(element).css( "background-color", "#4d4d4d" );
      $(element).prev().css( "background-color", "LightGray" );
      notifierAlerte("alert","dislike", diapoVisualisee ,titre);
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

  //________V3
  function cacherGauche(){
    $(".divGauche").hide();
    $(".expandGauche").show();
  }

  function afficherGauche(){
    $(".divGauche").show();
    $(".expandGauche").hide();
  }

  function cacherDroite(){
    $(".divDroite").hide();
    $(".expandDroite").show();
  }

  function afficherDroite(){
    $(".divDroite").show();
    $(".expandDroite").hide();
  }

  function cacherVideo(){
    $(".video").hide();
    $(".buttonVideo").hide();
    $(".expandVideo").show();
  }

  function afficherVideo(){
    $(".video").show();
    $(".buttonVideo").show();
    $(".expandVideo").hide();
  }

  function cacherTranscription(){
    $(".transcription").hide();
    $(".buttonTranscription").hide();
    $(".expandTranscription").show();
    $("#changeMode").hide();
    $(".diapositive").addClass("diapoGrande");
    $(".diapositive").removeClass("diapoPetite");
  }

  function afficherTranscription(){
    $(".transcription").show();
    $(".buttonTranscription").show();
    $(".expandTranscription").hide();
    $("#changeMode").show();
    $(".diapositive").addClass("diapoPetite");
    $(".diapositive").removeClass("diapoGrande");
  }

  function changeLayout(number){
    if(number==1) {
      $("#slides_view").addClass("diapositiveReduite");
      $("#slides_view").removeClass("diapositive");
      $(".navigation").hide();
      $(".needInfo").hide();
      $("#changeMode").attr("src","collapse2.svg");
      $("#changeMode").attr("onclick","changeLayout(0);return false;");
      $(".buttonTranscription").hide();
      $(".transcription").addClass("transcriptionGrande");
      $(".transcription").removeClass("transcriptionPetite");
      mode = 1;
    }
    else if (number==0){
      $("#slides_view").removeClass("diapositiveReduite");
      $("#slides_view").addClass("diapositive");
      $(".navigation").show();
      $(".needInfo").show();
      $("#changeMode").attr("src","expand.svg");
      $("#changeMode").attr("onclick","changeLayout(1);return false;");
      $(".buttonTranscription").show();
      $(".transcription").removeClass("transcriptionGrande");
      $(".transcription").addClass("transcriptionPetite");
      mode = 0;
    }
  }

  function ajoutBlocRessource(message) {
    if (message.mime=="ressources_externes"){
      $( ".scrollRessourcesInside" ).append( \'<a id="url1" class="nom_ressource" target="_blank" href="\'
            + message.source + \'" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("alert","ressource",1,"")>\'
            + message.description + \'</a><br /> \' );
    }
    if (message.mime == "questions"){
      $( ".scrollRessourcesInside" ).append( \'<button id="like1" class="like" onclick="like(this,0,&quot;\'
            + message.description + message.source + \'&quot;)">👍</button><button id="dislike1" class="like"
            onclick="dislike(this,0,&quot;\' + message.description + message.source + \'&quot;)">👎</button>
            <a id="url1" class="nom_ressource" target="_blank" href="\' + message.source + \'"
            style="font-weight: bold;margin-left:10px;color:#3c763d;" onclick="notifierAlerte("alert","ressource",1,"\'
            + message.description + \'")>\' + message.description + \'</a><br /> \' );
      $( ".scrollRessourcesInside" ).append(\'<a id="url1" target="_blank" href="\' + message.source + \'"
            style="font-weight: normal;margin-left:10px;color:black;" onclick="notifierAlerte("alert","ressource",1,"\'
            + message.description + \'")><i>\' + message.title +\' </i></a><br/>\');
      $( ".scrollRessourcesInside" ).append("<br/>");
    }
    $(".scrollRessourcesInside").scrollTo("100%");
  }

  $("#answerQCM").submit(function(e) {
      e.preventDefault();
  });

  $("#submitAnswerQCM").tooltip({
      disabled: true,
      close: function( event, ui ) { $(this).tooltip("disable"); }
  });

  $("#submitAnswerQCM").on("click", function () {
      $(this).tooltip({
        content: "Réponse envoyée"
      });
      $(this).tooltip("enable").tooltip("open");
  });

  </script>
');

// Finish the page.
echo $OUTPUT->footer();