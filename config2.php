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


// Des importantions et variables globales requises par moodle
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(__DIR__.'/tool_demo/output/index_page.php');
global $DB, $CFG, $COURSE;

// Configure les numeros de cours, de plugins, etc. d'apres les arguments passes en URL
$instanceId = optional_param('instanceid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$courseId = optional_param('courseid', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);  // ... pastel instance ID - it should be named as the first character of the module.

// Gestion des erreurs dans les arguments
if ($id) {
  $cm         = get_coursemodule_from_id('pastel', $id, 0, false, MUST_EXIST);
  $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
  $pastel  = $DB->get_record('pastel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
  $pastel  = $DB->get_record('pastel', array('id' => $n), '*', MUST_EXIST);
  $course     = $DB->get_record('course', array('id' => $pastel->course), '*', MUST_EXIST);
  $cm         = get_coursemodule_from_instance('pastel', $pastel->id, $course->id, false, MUST_EXIST);
}

// Fetch des ressources precedemments envoyees par le role des ressources, avec leurs differentes caracteristiques
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

// Parametres du cours permettant de controler les diapos
$cours = $DB->get_record('pastel', array('id'=>$instanceId));
$totalDiapo = $cours->intro;
$adresseStream = $cours->stream;

// Fetch les alertes de vitesse deja envoyees
$indicateurs = $DB->get_records('pastel_user_event', array('course'=>$courseId, 'activity'=>$id, 'nature'=>'vitesse'));
$relevantTime = time() - 600;
$indicateurs2 = $DB->get_records_sql('SELECT * FROM {pastel_user_event} WHERE course = '.$courseId.' AND activity = '.$id.' AND object = "speed" AND timecreated >= '.$relevantTime.' GROUP BY user_id');
$compteIndicateurs = count((array)$indicateurs2);
// La jointure est faite pour pouvoir retrouver l'ID d'un etudiant connecte et pouvoir l'identifier
$usersConnectes = $DB->get_records_sql('SELECT t1.* FROM mdl_pastel_connection t1
  JOIN (SELECT user_id, MAX(timecreated) timecreated FROM mdl_pastel_connection WHERE timecreated>1510000000 AND role="etudiant" GROUP BY user_id) t2
    ON t1.user_id = t2.user_id AND t1.timecreated = t2.timecreated;');
$compteConnectes = count((array)$usersConnectes);


// Fetch le nombre de personnes connectees
$connectes = $DB->get_records_sql('SELECT * FROM {pastel_connection} WHERE course = '.$courseId.' AND activity = '.$id.' GROUP BY user_id');
$nbConnectes = count((array)$connectes);



// require_login($course, true, $cm);

// $event = \mod_pastel\event\course_module_viewed::create(array(
//     'objectid' => $PAGE->cm->instance,
//     'context' => $PAGE->context,
// ));
// $event->add_record_snapshot('course', $PAGE->course);
// $event->add_record_snapshot($PAGE->cm->modname, $pastel);
// $event->trigger();

// Print the page header.


// Definit le format de la page Moodle, POPUP a ete choisi pour ne pas avoir de marge ou de hearder instrusif
$PAGE->set_pagelayout('popup');

// Definit les differentes caracteristiques de la page web
$PAGE->set_url('/mod/pastel/config1.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pastel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('mod_pastel/pastel_scripts', 'init');

// Output starts here.
echo $OUTPUT->header(

);

// Chercher l'entree BDD concernant le dernier changement de page
$lastChange = $DB->get_record_sql('SELECT * FROM mdl_pastel_slide WHERE course='.$courseId.' AND activity='.$id.' ORDER BY id DESC limit 1');
// Chercher le numero de la derniere diapo affichee (si aucune n'est enregistree on prend le numero 1)
$nbDiapo = $lastChange->page ?: 1;

// Recuperer, dans le dossier qui contient toutes les images de slides, les slides avec le nom qui correspond au cours
$parameters = array('instanceid' => $cm->instance, 'courseid' => $cm->course, 'id' => $cm->id ,'sesskey' => sesskey());
$url = new moodle_url('/mod/pastel/slides_window.php', $parameters);

// La page web et le JS
print('
  <script src="ckeditor/ckeditor.js"></script>
  <script>
    var votesA = 0;
    var votesB = 0;
    var votesC = 0;
    var votesIDs = [];
  </script>

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="jquery.scrollTo-2.1.2\jquery.scrollTo.js"></script>

  <a href="' . $url . '" target="_blank">Ouvrir la fenêtre des diapositives</a>
  <br />
  <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseId . '">Retour</a>

  <!-- La progressbar etait un element d UI de la premiere version -->
  <div id="progressbar" class="invisible"></div>
  <hr>

  <!-- Cadre de la vidéo en streaming -->
  <div class="clearfix">
    <div class="zone_tiers">
      <div id="webcam_view">
        <iframe width="320" height="280" 
          src="'.$adresseStream.'?autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0" 
          frameborder="0" allowfullscreen>
        </iframe>
      </div>
      
     <!-- Cadre avec les resultats des QCMs -->
  <div class="hidden">
    <h4>Réponses aux questions</h4>
      A - <span id="repA">0</span>
      <div class="indicateur_fond">
        <div class="vote_barre" id="gaugeA"></div>
      </div>
      B - <span id="repB">0</span>
      <div class="indicateur_fond">
        <div class="vote_barre" id="gaugeB"></div>
      </div>
      C - <span id="repC">0</span>
      <div class="indicateur_fond">
        <div class="vote_barre" id="gaugeC"></div>
      </div>
  </div>
      <br />
      <br />
      <br />

      <!-- Bouton pour remettre les votes a zero -->
      <button onclick="resetVotes()">Effacer les votes</button>

    </div>

    

    <div class="zone_tiers tscrpt">


      <div class="apercu_ressources">

      </div>
      <!-- La table d affichage des ressources. Certains enseignants n en veulent pas, elle est mise en commentaire dans cette version
      <table border=1 style="width:100%">
        <tr>
          <td><a id="url1" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
          <td><a id="url5" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
        </tr>
        <tr>
          <td><a id="url2" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
          <td><a id="url6" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
        </tr>
        <tr>
          <td><a id="url3" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
          <td><a id="url7" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
        </tr>
        <tr>
          <td><a id="url4" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
          <td><a id="url8" class="nom_ressource" target="_blank" href="https://fr.wikipedia.org/wiki/Traduction_automatique#Le_processus_de_traduction" style="font-weight: bold;margin-left:10px"> </a></td>
        </tr>
      </table>
      -->

      <br />
      <hr />
      <h5>Étudiants pour qui le cours va trop vite : <span id="chiffreVitesse">0</span></h5>
      <div class="indicateur_fond">
        <div class="indicateur_25 indicBarre"></div>
        <div class="indicateur_barre" id="barreVitesse"></div>
      </div>
      <br />
      <hr />
      <h5>Étudiants consultant un point antérieur du cours : <span id="chiffreAnterieur">0</span></h5>
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
          <td id="diff1"> </td>  <!-- Le numero de la diapositive liee a l alerte --> 
          <td id="nb1"> </td>   <!-- Le nombre d etudiants qui l ont envoyee -->
        </tr>
      </table>
    </div>

    <div class="zone_tiers marges">
      <h5>Retours des étudiants</h5>
      <!-- la DIV suivante est un flux qui affiche les questions ouvertes envoyees par les etudiants -->
      <div id="questions" class="retours">
      </div>
    </div>

  </div>

  <div id="info"> </div>
  
    <script>
    //___________________________________________LE JAVASCRIPT__________________________________________________________________
    
    // Les variables qui servaient a la frise chronologique
    var surFrise = false;
    var xFrise ;
    var pourcentage ;
    var offset;

    // On retrouve le numero de la diapo affichee a cet instant
    var diapoCourante = '.$nbDiapo.' ;

    // Variables de connexion au websocket
    var wsUri = "ws://la-pastel.univ-lemans.fr:8000/";
    var output ;

    // Le contexte du cours et l ID de l enseignant
    var user_id;
    var course_id;
    var activity_id;

    // Le nombre total d etudiants pour determiner la largeur des jauges
    var nombreEtudiants = 18;

    var tableauVitesse = [] // Stockage des alertes de cours trop rapide
    var tableauDirect = [] // Stockage des id de ceux qui sont hors du direct

    // Stockage des alertes d incomprehension, differents tableaux pour les differents types de donnees
    var tableauDifficulte = [];
    var stockDifficulte=[];
    var tempsDifficulte=[];

    //
    var stockTemps = [] ;
    // Stockage des notes pour les envoyer au server et les retrieve
    var stockNotes = [""] ;
    // Stockage des likes (repris direct du code JS de la page etudiant, non necessaire)
    var evalRessources = [0,0,0,0,0,0,0,0];
    var stockRessource = [{},{},{},{},{}];
    var stockQuestions = [{},{},{}];

    // Une fonction qui initialise le look du tableau de bord
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

    // Fonction qui retourne les numeros de diapos normes, avec trois caracteres pour retrouver le nom (comme diapo_001)
    function numeroDiapo (n) {
      if (n<10){
        return "00".concat(n) ;
      } else if (n>9 && n<100) {
        return "0".concat(n) ;
      } else {
        return n ;
      }
    }

    // Le code qui servait a configurer et faire fonctionner la frise chronologique dans la V1 (now deprecated)
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


  //______________________________________CODE DE LA CONNEXION WEBSOCKET__________________________________________________

    websocket = new WebSocket(wsUri);
    websocket.onopen = function(evt) { onOpen(evt) };
    websocket.onclose = function(evt) { onClose(evt) };
    websocket.onmessage = function(evt) { onMessage(evt) };
    websocket.onerror = function(evt) { onError(evt) };

    // Si le socket est ouvert avec succes
  function onOpen(evt)
  {
    writeToScreen("(connecté au système)");
    authentifie(5, "enseignant", '.$courseId.', '.$cm->id.');
  }

  // A la fermeture du canal
  function onClose(evt)
  {
    writeToScreen("(déconnecté du système)");
  }

  // A la reception d un package de donnees, on aiguille vers la bonne fonction
  function onMessage(evt)
  {
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

  // AFFICHER LE NUMERO DE PAGE
  function page(message) {
    // output = document.getElementById("page");
    
    // var pre = document.createElement("p");
    // pre.style.wordWrap = "break-word";
    // pre.innerHTML = message.page;
    // output.appendChild(pre);
  }

  // Ajoute un element HTML p qui contient le titre de la ressource, dans la liste des apercus de ressource
  function ressource(message) {
    output = document.getElementById("apercu_ressources");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message.description;
    output.appendChild(pre);
  }

  // Reception d un message d alerte de la part des etudiants
  function indicator(message) {
    // Si c est une alerte qui dit que le cours va trop vite
    if (message["object"]=="speed") {
      // On verifie que la personne qui l envoie n est pas deja dans la liste des alertes recues, pour eviter les doublons
      var index = tableauVitesse.length - 1 ;
      while (index >= 0) {
        // On fait cela en comparant les ID utilisteurs
        if (tableauVitesse[index]["user_id"]==message["user_id"]){
          tableauVitesse.splice(index, 1);
        }
        index -= 1 ;
      };
      // On l ajoute a la liste
      tableauVitesse.push(message);

    } else if (message["object"]==="direct") {
      // Si c est un message disant que l etudiant regarde un point anterieur du cours ou revient a la diapo courante
      if (message["data"]===false){
        // Dans le cas ou l etudiant regarde un point anterieur
        //on check si on a déjà l\id dans la liste
        if ( $.inArray(message["user_id"],tableauDirect) == -1 && message["user_id"] != 0){
          // ajouter à la liste
          tableauDirect.push(message["user_id"]);
        }
      } else {
        // Si l etudiant revient a la diapo courante
        // retirer de la liste
        tableauDirect = jQuery.grep(tableauDirect, function(value) {
          return value != message["user_id"];
        });
      }
      console.log(message);
      console.log(tableauDirect);

    } else if (message["object"]=="difficulty"){
      // Si c est un message disant que l etudiant a besoin de plus d explications
      if ( $.inArray(message["user_id"],tableauDifficulte) == -1 && message["user_id"] != 0){
        tableauDifficulte.push(message["user_id"]);
        // On garde le numero de slide qui pose probleme
        stockDifficulte.push(message["page"]); //timecreated
        // Ainsi que le moment ou cela a ete envoye
        tempsDifficulte.push(message["timecreated"]);
        console.log("difficulté recue");
        // Et on recalcule l affichage sur la fenetre du prof
        podium(stockDifficulte);
      }
    }

    // Si c est une question ouverte
    if (message["object"]=="open_question_field"){
      console.log(message);
      // On ajoute le corps du message en haut dans la liste des questions
      $("#questions").prepend("<hr><p>"+message["data"]+"</p>");
    }

    // Si c est une reponse a un QCM
    if (message["object"]=="Answer"){
      var reponse = JSON.parse(message["data"]);
      // On prend les differentes donnees
      console.log(reponse);
      if(!votesIDs.includes(reponse[reponse.length-1])){
        // On verifie que l ID du votant ne se trouve pas deja dans la liste des votants
        votesIDs.push(reponse[reponse.length-1]);
        // Puis on stocke l ID dans la liste des votants, et on compte son vote
        if(reponse.includes("A")){
          votesA+=1;
        }
        if(reponse.includes("B")){
          votesB+=1;
        }
        if(reponse.includes("C")){
          votesC+=1;
        }
      }
    }

  }

  // Cette fonction est baptisee podium car elle fait un top 3 des diapositives les moins comprises
  function podium(tablo){
    var count = 0;
    var indexPage;
    tablo.forEach(function (element){
      var count2  = $.grep(tablo, function (elem) { //Compter le nombre doccurences
        return elem === element;
      }).length;;
      if (count2>count){
        count = count2;
        indexPage = element;
      }
    });
    console.log(count+" "+indexPage);
    $("#diff1").html(indexPage);
    $("#nb1").html( count);
  }

  // Fonction qui se declenche s il y a un probleme dans l aiguillage
  function onError(evt)
  {
    writeToScreen(\'<span style="color: red;">ERROR:</span> \' + evt.data);
  }

  // Envoyer un message au server
  function doSend(message)
  {
    //writeToScreen("SENT: " + message);
    console.log("SENT: " + message);
    websocket.send(message);
  }

  // Affiche des messages a l ecran
  function writeToScreen(message)
  {
    output = document.getElementById("info");
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    output.appendChild(pre);
  }

  // Authentifie l utilisateur avec son role dans le contexte
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

  // Envoie un message au serveur, avec la structure HTML qui le contient, l objet HTML clique, et la donnee texte a l interieur
  function notifierAlerte(conteneur, obj, donnee) {
    var data = {
      "action" : "alerte",
      "params" : { "user_id" : user_id, "container" : conteneur, "object":obj, "activity":activity_id, "course" : course_id, "data" : donnee }
    };

    doSend(JSON.stringify(data));
  }

  // Notifie le serveur quand l enseignant revient sur une diapositive precedente, avec son numero de diapositive
  function pageArriere(){
    if (diapoCourante>=2){
        var data = {
          "action" : "page",
          "params" : { "activity":activity_id, "course" : course_id, "navigation" : "backward", "page" : diapoCourante }
        };

        doSend(JSON.stringify(data));
    }
  }

  // Notifie le serveur quand l enseignant passe a une diapositive suivante, avec son numero de diapositive
  function pageAvant(){
    var data = {
      "action" : "page",
      "params" : { "activity":activity_id, "course" : course_id, "navigation" : "forward", "page" : diapoCourante }
    };
      doSend(JSON.stringify(data));
  }

  // A la reception d une ressource
  function ressource(message) {
    console.log(message);
    // Auparavant les ressources etaient redirigees vers des emplacements precis dans l interface, cela a ete repense
    // Aujourd hui on ajoute juste le lien qui mene vers cette ressource dans la liste prevue a cet effet
    if(message.mime == "ressources_externes") {
      $( ".apercu_ressources" ).append( \'<a class="nom_ressource" target="_blank" href="\' + message.source + \'" style="font-weight: bold;margin-left:10px" onclick="notifierAlerte("ressource","lien",1,"")>\' + message.title + \'</a><br /> \' );
      $(".apercu_ressources").scrollTo("100%");
    }
  }


  // Fonction qui enleve les alertes vitesse qui datent de plus de 10 minutes et update la barre
  // + Qui indique combien d\etudiants sont a un point anterieur du cours
  // + Qui met a jour les resultats de vote

  setInterval(function(){
    var index = tableauVitesse.length - 1 ;
    while (index >= 0) { // Dans cette boucle on parcourt le tableau en partant de la fin
      if ( (Date.now()/1000 - 600) > tableauVitesse[index]["timecreated"] ){ // On check si les alertes datent d il y a plus de 10 min
        tableauVitesse.splice(index, 1); // Si c est le cas on les supprime
      }
      index -= 1 ;
    };
    $("#chiffreVitesse").text(tableauVitesse.length); // On affiche le nombre d alertes qui trouvent le cours trop rapide
    var ratioVitesse = tableauVitesse.length/nombreEtudiants *100;
    $("#barreVitesse").width( ratioVitesse + "%"); // On redimensionne la barre en pourcentage calcule sur le total d etudiants
    if (ratioVitesse<25){ // Cette condition gere la couleur de la barre qui passe au rouge sous le seuil critique de 25%
      $("#barreVitesse").css( "background-color", "green" );
    } else {
      $("#barreVitesse").css( "background-color", "red" );
    }

    $("#chiffreAnterieur").text(tableauDirect.length); // On update le texte...
    var ratioDirect = tableauDirect.length/nombreEtudiants *100; // ...et la jauge des etudiants qui consultent un point anterieur du cours
    $("#barreDirect").width( ratioDirect + "%"); 

    $("#repA").text(votesA); // On update le texte...
    $("#gaugeA").width( votesA/nombreEtudiants *100 + "%"); // ...et la jauge des etudiants qui ont repondu A au questionnaire
    $("#repB").text(votesB);
    $("#gaugeB").width( votesB/nombreEtudiants *100 + "%");
    $("#repC").text(votesC);
    $("#gaugeC").width( votesC/nombreEtudiants *100 + "%");

  }, 3000);

  // Remettre les alertes de difficulte a zero une fois plusieurs minutes passees - le delai est calcule en millisecondes ici 1800000
  setInterval(function(){
    var tableauDifficulte = [];
    var stockDifficulte= [];
    var tempsDifficulte= [];
  }, 1800000);

  // Remettre les votes a zero (se declenche quand on clique sur le bouton dedie de l interface)
  function resetVotes(){
    votesA=0;
    votesB=0;
    votesC=0;
    votesIDs=[]
  }

  </script>

  ');


// Finish the page.
echo $OUTPUT->footer();