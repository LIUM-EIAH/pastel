<?php 

class pastel_export_pdf {
	protected $courseID;
	protected $activityID;
	protected $userID;
	protected $url_subname;
	
	public function setContext($cid, $modid, $uid) {
 		$this->courseID = $cid;
 		$this->activityID = $modid;
 		$this->userID = $uid;
 		global $DB;
 		// $cm         = get_coursemodule_from_id('pastel', $this->activityID, 0, false, MUST_EXIST);
 		$cours = $DB->get_record('pastel', array('course'=>$this->courseID));
 		$this->url_subname = $cours->nomdiapo;
 		// $url_diapo = "http://la-pastel.univ-lemans.fr/mod/pastel_/pix/page/".$url_subname."-page-";

		
		//XXX donnée de test a supprimer
		//$this->courseID = 26;
		//$this->activityID = 300;
		//$this->userID = 58;
	}
	/**
	 * Construit le pdf et le place dans le navigateur.
	 */
	public function print_activity() {
		global $CFG;
		require_once($CFG->dirroot.'/lib/pdflib.php');
		
		$doc = new pdf;
		$doc->setPrintHeader(false);
		$doc->setPrintFooter(false);
		
		$TabTempsPage = $this->getNbPage();
		if (count($TabTempsPage) == 0) {
			$msg = "Activite non realise !";
			$doc->AddPage('L', 'A4');
			$doc->writeHTML($msg, true, false, true, false, '');
			$doc->Output('pastel.pdf', 'I');
			exit;
		}
		
		foreach ($TabTempsPage as $key => $val) {
			$numeroPage = $key;
		
			$doc->AddPage('L', 'A4');
	
			$nomImage = $CFG->wwwroot . '/mod/' . sprintf("pastel_/pix/page/".$this->url_subname."-page-%'.03d.jpg" , $numeroPage);
			
			$debugage = '<br/>cours = ' . $this->courseID . ' activite =' . $this->activityID . ' user = '. $this->userID
				. ' timecreated = ' . $val[0]->debut
				. '<br/>Numero slide ' . $numeroPage . ' / Nombre de slide ' . count($TabTempsPage);
			
			$tbl = '<table><tr><td>'
			.'<img src="' . $nomImage . '" alt="test alt attribute" width="400" height="300" border="0"/> </td>'
			// .'<td><b>Notes</b>' 
			// XXX.'<br/>' .$debugage
			//  	.'<br/>' . $this->getNotesPage($numeroPage).'</td>'
				.'</tr></table><br><b>Ressources</b>';
			
			$doc->writeHTML($tbl, true, false, true, false, '');
			
			$this->ecrireRessources($doc, $val);
			$tbl4 = '<br/><b>Transcription</b><br/>'. $this->getTranscriptionPage($val);
			$doc->writeHTML($tbl4, true, false, true, false, '');
		} //fin trt des pages

		$doc->AddPage('L', 'A4');
		$tbl2 = '<b>Notes</b></br>'. $this->getNotesPage($numeroPage);
		$doc->writeHTML($tbl2, true, false, true, false, '');
		
		$doc->Output('pastel.pdf', 'I');
		exit;
	}
	
	//XXX obtention des notes pour une page
	private function getNotesPage($numeroPage) {
		global $DB;
		$ret = "";
		$tabInter = [];
		$req = "select data from {pastel_user_event} where course = ? and activity = ? and container = 'notes' and user_id = ?"; //and page = ? 
		$rs = $DB->get_recordset_sql ( $req, array ($this->courseID, $this->activityID, $this->userID)); //get_recordset_sql   $numeroPage,
		// foreach ( $rs as $result ) {
		// 	$ret = $ret . $result->data;
		// }
		foreach ( $rs as $result ) {
			$tabInter[] = $result->data;
		}
		$ret = end($tabInter);
		return $ret;
	}
	//XXX obtention des ressources d'une page
	private function ecrireRessources($doc, $tabTemps) {
		global $DB;
		$doc->SetFont('times', 'U', 12, '', 'false');
		$doc->SetTextColor(0, 0, 255);
		foreach ($tabTemps as $temps) {
			if (!isset($temps->fin) || !isset($temps->debut)) continue;
			if ($temps->fin === null || $temps->debut === null) continue;
try {
			$req = "select url, title from {pastel_resource} where course = ? and activity = ? and url like 'http%' and timecreated >= ? and timecreated <= ? and mime='auto'";
			$rs = $DB->get_recordset_sql ( $req, array ($this->courseID, $this->activityID, $temps->debut, $temps->fin));
			foreach ( $rs as $result ) {
				$doc->Write(0, $result->title, $result->url, false, 'L', true);
			}
} catch (Exception $err) {
	//err
}
		}
		$doc->SetTextColor(0, 0, 0);
		$doc->SetFont('times', '', 12, '', 'false');
	}
	
	/**
	 * Obtention des textes de transcription de la page.
	 * On recevoir le tableau des temps passés sur la page
	 */
	private function getTranscriptionPage($tabTemps) {
		global $DB;
		$ret = "";
		foreach ($tabTemps as $temps) {
			if (!isset($temps->fin) || !isset($temps->debut)) continue;
			
			$duree = $temps->fin - $temps->debut;
			if ($duree < 1) continue;
			
			$req = "select text from {pastel_transcription} where course = ? and activity = ? and timecreated >= ? and timecreated <= ?";
			$rs = $DB->get_recordset_sql ( $req, array ($this->courseID, $this->activityID, $temps->debut, $temps->fin));

			foreach ( $rs as $result ) {
				$ret = $ret . " " . $result->text;
			}
		}
		return $ret;
	}
	
	/**
	 * Etablit la liste des pages et leur creneau de visualisation.
	 * La derniere page aura une date de fin équivalente a maintenant.
	 */
	private function getNbPage() {
		global $DB;
		$data = array ();
		
		$maxpage = $this->getMaxPage();
		if ($maxpage == -1) {
			return $data;
		}
		
		
		$now = new DateTime ( "now", core_date::get_user_timezone_object () );
				
		$req = "select timecreated, navigation, page from {pastel_slide} where course = ? and activity = ?";
		$rs = $DB->get_recordset_sql ( $req, array ($this->courseID, $this->activityID));
		
		
		foreach ( $rs as $result ) {
			$numPageQuit = $result->page;
			
			$numPageArriv = $numPageQuit + 1;
			if (strcmp($result->navigation, "forward") !== 0 ) {
				$numPageArriv = $numPageQuit - 1;
			}
			if ($numPageArriv > $maxpage || $numPageArriv < 1) continue;
			
			if (isset ($data[$numPageQuit])) {
				$tabTemps = $data[$numPageQuit];
				$count = count($tabTemps);
				$tabTemps[$count - 1]->fin = $result->timecreated;
			} else {
				$temps = new stdClass ();
				$temps->debut = null;
				$temps->fin = $result->timecreated;
				$tabTemps = array();
				$tabTemps[] = $temps;
				$data[$numPageQuit] = $tabTemps;
			}
			
			$temps = new stdClass ();
			$temps->debut = $result->timecreated + 1;
			if  ($numPageArriv == $maxpage) {
				$temps->fin = $now;
			} else {
				$temps->fin = null;
			}

			if (isset ($data[$numPageArriv])) {
				$data[$numPageArriv][] = $temps; 
			} else {
				$tabTemps = array();
				$tabTemps[] = $temps;
				$data[$numPageArriv] = $tabTemps;
			}
		}
		//purge
		foreach ($data as $key => $val) {
			$tempsPage = $data[$key];
			foreach ($tempsPage as $ind => $plage) {
				if (!isset($plage->fin)) {
					unset ($tempsPage[$ind]);
					continue;
				}
				if (!isset($plage->debut)) {
					unset ($tempsPage[$ind]);
				} else if (isset($plage->fin) &&  $plage->debut > $plage->fin ) {
					unset ($tempsPage[$ind]);
				}
			}
			if (count($tempsPage) == 0) {
				unset($data[$key]);
			}
		}
		
		return $data;
	}
	
	
	private function getMaxPage() {
		//TODO test return 137;
		global $DB;
		$maxpage = -1;
		try {
			$reqInstance = "select instance from {course_modules} where id = ". $this->activityID;
			$instance = $DB->get_record_sql($reqInstance, array());
			$req = "select intro from {pastel} where id = " . $instance->instance;
			$description = $DB->get_record_sql($req, array());
			$maxpage = intval(trim($description->intro));
	
		} catch ( Exception $err ) {
			$maxpage = -1;
		}
		return $maxpage;
	}
}