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

defined('MOODLE_INTERNAL') || die;

class pastel_export_pdf {
    protected $courseid;
    protected $activityid;
    protected $userid;
    protected $urlsubname;

    public function setContext($cid, $modid, $uid) {
        $this->courseid = $cid;
        $this->activityid = $modid;
        $this->userid = $uid;
        global $DB;
        $cours = $DB->get_record('pastel', array('course' => $this->courseid));
        $this->urlsubname = $cours->nomdiapo;
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

        $tabtempspage = $this->get_nb_page();
        if (count($tabtempspage) == 0) {
            $msg = "Activite non realise !";
            $doc->AddPage('L', 'A4');
            $doc->writeHTML($msg, true, false, true, false, '');
            $doc->Output('pastel.pdf', 'I');
            exit;
        }

        foreach ($tabtempspage as $key => $val) {
            $numeropage = $key;

            $doc->AddPage('L', 'A4');

            $nomimage = $CFG->wwwroot . '/mod/' . sprintf("pastel_/pix/page/".$this->urlsubname."-page-%'.03d.jpg" , $numeropage);

            $tbl = '<table><tr><td>'
                .'<img src="' . $nomimage . '" alt="test alt attribute" width="400" height="300" border="0"/> </td>'
                .'</tr></table><br><b>Ressources</b>';

            $doc->writeHTML($tbl, true, false, true, false, '');

            $this->ecrire_ressources($doc, $val);
            $tbl4 = '<br/><b>Transcription</b><br/>'. $this->getTranscriptionPage($val);
            $doc->writeHTML($tbl4, true, false, true, false, '');
        }

        $doc->AddPage('L', 'A4');
        $tbl2 = '<b>Notes</b></br>'. $this->get_notes_page($numeropage);
        $doc->writeHTML($tbl2, true, false, true, false, '');

        $doc->Output('pastel.pdf', 'I');
        exit;
    }

    /**
     * Obtention des notes pour une page.
     */
    private function get_notes_page($numeropage) {
        global $DB;
        $ret = "";
        $tabinter = [];
        $req = "select data from {pastel_user_event}
                where course = ? and activity = ? and container = 'notes' and user_id = ? and page = ?";
        $rs = $DB->get_recordset_sql($req, array ($this->courseid, $this->activityid, $this->userid, $numeropage));
        foreach ($rs as $result) {
            $tabinter[] = $result->data;
        }
        $ret = end($tabinter);
        return $ret;
    }
    /**
     * Obtention des ressources d'une page.
     */
    private function ecrire_ressources($doc, $tabtemps) {
        global $DB, $CFG;
        $doc->SetFont('times', 'U', 12, '', 'false');
        $doc->SetTextColor(0, 0, 255);
        foreach ($tabtemps as $temps) {
            if (!isset($temps->fin) || !isset($temps->debut)) {
                continue;
            }
            if ($temps->fin === null || $temps->debut === null) {
              continue;
            }
            try {
                $req = "select url, title from {pastel_resource}
                        where course = ?
                          and activity = ?
                          and url like 'http%'
                          and timecreated >= ? and timecreated <= ?
                          and mime='auto'";
                $rs = $DB->get_recordset_sql ( $req, array ($this->courseid, $this->activityid, $temps->debut, $temps->fin));
                foreach ($rs as $result) {
                    $doc->Write(0, $result->title, $result->url, false, 'L', true);
                }
            } catch (Exception $err) {
                if ($CFG->debugdeveloper) {
                    debugging('Erreur sur requete SQL Pastel_export_pdf.php ligne 11', DEBUG_DEVELOPER);
                }
            }
        }
        $doc->SetTextColor(0, 0, 0);
        $doc->SetFont('times', '', 12, '', 'false');
    }

    /**
     * Obtention des textes de transcription de la page.
     * On recevoir le tableau des temps passes sur la page
     */
    private function getTranscriptionPage($tabtemps) {
        global $DB;
        $ret = "";
        foreach ($tabtemps as $temps) {
            if (!isset($temps->fin) || !isset($temps->debut)) {
                continue;
            }

            $duree = $temps->fin - $temps->debut;
            if ($duree < 1) {
                continue;
            }

            $req = "select text from {pastel_transcription}
                     where course = ? and activity = ? and timecreated >= ? and timecreated <= ?";
            $rs = $DB->get_recordset_sql ( $req, array ($this->courseid, $this->activityid, $temps->debut, $temps->fin));

            foreach ($rs as $result) {
                $ret = $ret . " " . $result->text;
            }
        }
        return $ret;
    }

    /**
     * Etablit la liste des pages et leur creneau de visualisation.
     * La derniere page aura une date de fin equivalente a maintenant.
     */
    private function get_nb_page() {
        global $DB;
        $data = array ();

        $maxpage = $this->get_max_page();
        if ($maxpage == -1) {
            return $data;
        }

        $now = new DateTime ( "now", core_date::get_user_timezone_object () );
        $req = "select timecreated, navigation, page from {pastel_slide} where course = ? and activity = ?";
        $rs = $DB->get_recordset_sql ( $req, array ($this->courseid, $this->activityid));

        foreach ($rs as $result) {
            $numPageQuit = $result->page;
            $numPageArriv = $numPageQuit + 1;
            if (strcmp($result->navigation, "forward") !== 0 ) {
                $numPageArriv = $numPageQuit - 1;
            }
            if ($numPageArriv > $maxpage || $numPageArriv < 1) {
                continue;
            }
            if (isset ($data[$numPageQuit])) {
                $tabtemps = $data[$numPageQuit];
                $count = count($tabtemps);
                $tabtemps[$count - 1]->fin = $result->timecreated;
            } else {
                $temps = new stdClass ();
                $temps->debut = null;
                $temps->fin = $result->timecreated;
                $tabtemps = array();
                $tabtemps[] = $temps;
                $data[$numPageQuit] = $tabtemps;
            }

            $temps = new stdClass ();
            $temps->debut = $result->timecreated + 1;
            if ($numPageArriv == $maxpage) {
                $temps->fin = $now;
            } else {
                $temps->fin = null;
            }

            if (isset ($data[$numPageArriv])) {
                $data[$numPageArriv][] = $temps;
            } else {
                $tabtemps = array();
                $tabtemps[] = $temps;
                $data[$numPageArriv] = $tabtemps;
            }
        }
        // Purge.
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

    private function get_max_page() {
        global $DB;
        $maxpage = -1;
        try {
            $reqInstance = "select instance from {course_modules} where id = ". $this->activityid;
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