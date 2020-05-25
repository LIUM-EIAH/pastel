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
 * Pastel server class implementation
 *
 * @package    mod_pastel
 * @copyright  2017 Marc Leconte
 */

// Require HashMap.
require __DIR__ . '/hash.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

define('ROLE_TRANSCRIPTEUR', 'transcripteur');
define('ROLE_ETUDIANT', 'etudiant');
define('ROLE_ENSEIGNANT', 'enseignant');
define('ROLE_ANALYSE', 'analyste');
define('ROLE_RESSOURCE', 'illustrateur');
define('ROLE_TRACEUR', 'traceur');

class mod_pastel_server implements MessageComponentInterface {
    protected $clients, $connections, $infos;
    protected $userid;
    public function __construct() {
        $this->clients = array ();
        $this->infos = array ();
        $this->connections = new HashMap ();
        echo  date("F j, Y, g:i a") .' DEMARRAGE du serveur Pastel WS ' . PHP_EOL;
    }
    public function __destruct() {
        echo date("F j, Y, g:i a") . ' ARRET du serveur Pastel WS '  . PHP_EOL;
    }
    public function onOpen(ConnectionInterface $conn) {
    }

    public function onMessage(ConnectionInterface $conn, $data) {
        // Parse data received.
        echo date("F j, Y, g:i a") . $data . PHP_EOL;

        if (! $this->isvalid ( $conn, $data )) {
            return;
        }
        $data = json_decode ( $data );

        if (isset ( $this->connections [$conn] )) {
            $this->userid = $this->connections [$conn];
            $time = new DateTime("now", core_date::get_user_timezone_object());
            $data->params->timecreated = $time->getTimestamp();
        }

        if (isset ( $data->action ) && isset ( $data->params )) {
            switch ($data->action) {
                // When a user becomes online.
                case 'update_status' :
                    $this->update_user_status ( $conn, $data->params );
                    break;

                // Upon receipt of a transcript.
                case 'transcription' :
                    if ($this->infos [$this->userid]->role != ROLE_TRANSCRIPTEUR) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $idUser = $this->transcription ( $conn, $data->params );
                    break;

                // At each page change.
                case 'page' :
                    if ($this->infos [$this->userid]->role != ROLE_ENSEIGNANT) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $this->chgtpage ($conn, $data->params );
                    break;

                // When a student clicks on an alert button.
                case 'alerte' :
                    if ($this->infos [$this->userid]->role != ROLE_ETUDIANT) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $this->notifyalerte($data->params);
                    break;
                case 'indicator' :
                    if ($this->infos [$this->userid]->role != ROLE_ANALYSE &&
                        $this->infos [$this->userid]->role != ROLE_RESSOURCE) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $this->indicator ( $data->params );
                    break;
                // When the resource team sends a resource.
                case 'ressource' :
                    if ($this->infos [$this->userid]->role != ROLE_RESSOURCE) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $this->addressource($data->params);
                    break;
                // Upon receiving a Trello or Etherpad trace.
                case 'trace' :
                    if ($this->infos [$this->userid]->role != ROLE_TRACEUR) {
                        $this->unauthorisedAction($conn, $data->action);
                        break;
                    }
                    $this->send_data_role ( "trace", $data->params, ROLE_ETUDIANT, false );
                    $this->send_data_role ( "trace", $data->params, ROLE_ENSEIGNANT, false );
                    break;

                default :
                    $response = array (
                            'action' => 'error',
                            'params' => array (
                                    'message' => 'Action ' . $data->action . ' non implemente'
                            )
                    );
                    $this->response ( $conn, $response );
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}" . PHP_EOL;
        $conn->close ();
    }

    private function unauthorisedAction($conn, $name) {
        $response = array (
                'action' => 'error',
                'params' => array (
                        'message' => 'Action '. $name .' non autorise pour le role ' . $this->infos [$this->userid]->role
                )
        );
        $this->response ( $conn, $response );
    }

    public function onClose(ConnectionInterface $conn) {
        // When a user becomes offline.
        $params = new stdClass ();
        if (isset ( $this->connections [$conn] )) {
            $params->user_id = $this->connections [$conn];
            $params->role = $this->infos [$params->user_id]->role;
            $params->course = $this->infos [$params->user_id]->course;
            $params->activity = $this->infos [$params->user_id]->activity;
            $params->status = 'offline';
        }
        $this->update_user_status ( $conn, $params );
    }

    /**
     * Sends the message to all the targets that carry the requested role.
     */
    private function send_data_role($action, $params, $role, $filtre) {
        $data = array (
                'action' => $action,
                'params' => $params
        );
        $json = json_encode ( $data );

        foreach ($this->infos as $key => $value) {
            $envoi = !$filtre;
            if ($filtre && $this->infos [$key]->course == $params->course && $this->infos [$key]->activity == $params->activity ) {
                $envoi = true;
            }

            if ($envoi && $this->infos [$key]->role == $role) {
                foreach ($this->clients [$key] as $conn) {
                    $conn->send ( $json );
                }
            }
        }
    }

    /**
     * Updates a user status to online or offline.
     *
     * @param object $conn
     *          - the client connection.
     * @param object $params
     *          - the data received from the client.
     */
    private function update_user_status(ConnectionInterface $conn, $params) {
        if (isset ( $params->user_id ) && isset ( $params->status )) {
            $info = new stdClass ();
            $info->role = $params->role;
            $info->course = $params->course;
            $info->activity = $params->activity;

            if (mod_pastel_update_user_status ( $params->user_id, $params->status, $info ) == false) {
                return;
            }

            // If Online.
            if ($params->status == 'online') {
                // Store Connection.
                $this->connections [$conn] = $params->user_id;
                if (! isset ( $this->clients [$params->user_id] )) {
                    $this->clients [$params->user_id] = array ();
                }
                $this->infos [$params->user_id] = $info;
                array_push ( $this->clients [$params->user_id], $conn );
            } else if ($params->status == 'offline') {
                // Close Connection.
                unset ( $this->connections [$conn] );
                $key = array_search ( $conn, $this->clients [$params->user_id] );
                $this->clients [$params->user_id] [$key]->close ();
                unset ( $this->clients [$params->user_id] [$key] );
                unset ( $this->infos [$params->user_id] );
            }
        }
    }

    /**
     * Treatment to be carried out for each transcription received.
     *
     * @param object $params
     *          - the data received from the client.
     */
    private function transcription(ConnectionInterface $conn, $params) {
        if (! isset($params->final)) {
            $response = array (
                    'action' => 'error',
                    'params' => array ('message' => 'Parametre final manquant !')
            );
            $this->response ( $conn, $response );
            return;
        }
        // Get user_id.
        if (mod_pastel_transcription ( $this->userid, $params ) == false) {
            return;
        }

        // Switching to targets.
        $this->send_data_role ( "transcription", $params, ROLE_ETUDIANT, true );
        $this->send_data_role ( "transcription", $params, ROLE_ENSEIGNANT, true );
        $this->send_data_role ( "transcription", $params, ROLE_RESSOURCE, false );
        $this->send_data_role ( "transcription", $params, ROLE_ANALYSE, false );
    }

    /**
     * A chaque changement de page (msg de l'enseignant)
     *
     * @param object $params
     *          - the data received from the client.
     */
    private function chgtpage(ConnectionInterface $conn, $params) {
        // The page number of the message is the current page number.
        if (mod_pastel_chgtpage ( $this->userid, $params) == false) {
            return;
        }

        $numero = intval($params->page);
        if ($params->navigation == 'forward') {
            $numero = $numero + 1;
        }
        if ($params->navigation == 'backward') {
            $numero = $numero - 1;
        }
        $maxpage = mod_pastel_get_maxpage($params);
        if ($maxpage < $numero || $numero < 0) {
            $response = array (
                    'action' => 'error',
                    'params' => array ('message' => 'Page inexistante !')
            );
            $this->response ( $conn, $response );
            return;
        }
        $params->page = $numero;
        // Switching to targets.
        $this->send_data_role ( "page", $params, ROLE_ETUDIANT, true );
        $this->send_data_role ( "page", $params, ROLE_ENSEIGNANT, true );
        $this->send_data_role ( "page", $params, ROLE_RESSOURCE, false );
        $this->send_data_role ( "page", $params, ROLE_ANALYSE, false );
    }

    /**
     * Quand un etudiant emet une alerte.
     *
     * @param object $params
     *          - the data received from the client.
     */
    private function notifyalerte($params) {
        // Database record in the table  table pastel_user_event.
        if (mod_pastel_userevent($this->userid, $params) == false) {
            return;
        }
        $params->user_id = $this->userid;
        // Switching to targets.
        if (strcmp($params->container, "alert") === 0) {
            $parameters = new stdClass ();
            $parameters->user_id = $this->userid;
            $parameters->timecreated = $params->timecreated;
            $parameters->object = $params->object;
            $parameters->course = $params->course;
            $parameters->page = $params->page;
            $parameters->activity = $params->activity;
            $parameters->data = $params->data;
            $this->send_data_role ( "indicator", $parameters, ROLE_ENSEIGNANT, true );
        }
        $this->send_data_role ( "alerte", $params, ROLE_RESSOURCE, false );
    }

    private function indicator($params) {
        if (mod_pastel_indicator ( $this->userid, $params ) == false) {
            return;
        }

        // Switching to targets.
        $this->send_data_role ( "indicator", $params, ROLE_ENSEIGNANT, true );
    }

    /**
     * Posts a message into a chat session and sends it to the other user in
     * that session.
     *
     * @param object $params
     *          - the data received from the client.
     */
    private function addressource($params) {
        if (mod_pastel_addressource ( $this->userid, $params) == false) {
            return;
        }

        // Switching to targets.
        $this->send_data_role ( "ressource", $params, ROLE_ETUDIANT, true );
        $this->send_data_role ( "ressource", $params, ROLE_ENSEIGNANT, true );
        $this->send_data_role ( "ressource", $params, ROLE_ANALYSE, false );
    }
    private function isvalid(ConnectionInterface $conn, $message) {
        try {
            // Format valid ?
            $data = json_decode ( $message );
            if (isset ( $data->action ) == false || isset ( $data->params ) == false) {
                $response = array ('action' => 'error',
                        'params' => array ('message' => 'Format message incorrect') );
                $this->response ( $conn, $response );
                return false;
            }
            // First message must be update_status.
            if (! isset ( $this->connections [$conn] ) && $data->action != 'update_status') {
                $response = array (
                        'action' => 'error',
                        'params' => array ('message' => 'Identification requise')
                );
                $this->response ( $conn, $response );
                return false;
            }
            return true;
        } catch ( Exception $err ) {
            $response = array (
                    'action' => 'error',
                    'params' => array ('message' => $err->getMessage ())
            );
            $this->response ( $conn, $response );
            return false;
        }
    }

    private function response(ConnectionInterface $conn, $message) {
        try {
            $json = json_encode ( $message );
            $conn->send ( $json );
        } catch ( Exception $err ) {
            echo 'probleme envoi reponse ' . $err->getMessage ();
        }
    }
}