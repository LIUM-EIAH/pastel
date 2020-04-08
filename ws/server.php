<?php
/**
 * Pastel server class implementation
 *
 * @package    mod_pastel
 * @copyright  2017 Marc Leconte
 */

// Require HashMap
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
	function __destruct() {
		echo date("F j, Y, g:i a") . ' ARRET du serveur Pastel WS '  . PHP_EOL;
	}
	public function onOpen(ConnectionInterface $conn) {
	}
	
	
	public function onMessage(ConnectionInterface $conn, $data) {
		// Parse data received.
		echo date("F j, Y, g:i a") . $data . PHP_EOL;
		
		if (! $this->isValid ( $conn, $data )) {
			return;
		}
		$data = json_decode ( $data );
		
		if (isset ( $this->connections [$conn] )) {
			$this->userid = $this->connections [$conn];
			// le timestamp Moodle
			$time = new DateTime ( "now", core_date::get_user_timezone_object () );
			$data->params->timecreated = $time->getTimestamp ();
		}
		
		//echo $data . PHP_EOL;
		
		if (isset ( $data->action ) && isset ( $data->params )) {
			switch ($data->action) {
				
				// When a user becomes online
				case 'update_status' :
					$this->update_user_status ( $conn, $data->params );
					break;
				
				// a la reception d'une transcription
				case 'transcription' :
					if ($this->infos [$this->userid]->role != ROLE_TRANSCRIPTEUR) {
						$this->unauthorisedAction($conn, $data->action);
						break;
					}
					$idUser = $this->transcription ( $conn, $data->params );
					break;
				
				// a chaque changement de page
				case 'page' :
					if ($this->infos [$this->userid]->role != ROLE_ENSEIGNANT) {
						$this->unauthorisedAction($conn, $data->action);
						break;
					}
					$this->chgtPage ($conn, $data->params );
					break;
				
				// qd un etudiant clic sur un bouton alerte
				case 'alerte' :
					if ($this->infos [$this->userid]->role != ROLE_ETUDIANT) {
						$this->unauthorisedAction($conn, $data->action);
						break;
					}
					$this->notifyAlerte ( $data->params );
					break;
				case 'indicator' :
					if ($this->infos [$this->userid]->role != ROLE_ANALYSE && $this->infos [$this->userid]->role != ROLE_RESSOURCE) {
						$this->unauthorisedAction($conn, $data->action);
						break;
					}
					$this->indicator ( $data->params );
					break;
				// quand l'equipe ressource envoi une ressource
				case 'ressource' :
					if ($this->infos [$this->userid]->role != ROLE_RESSOURCE) {
						$this->unauthorisedAction($conn, $data->action);
						break;
					}
					$this->addRessource ( $data->params );
					break;
                // a la reception d'une trace Trello ou Etherpad
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
		echo "An error has occurred: {$e->getMessage()}" . PHP_EOL ;	
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
		// When a user becomes offline
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
	 * Envoi le message a l'ensemble des cible qui porte le role demand�.
	 */
	private function send_data_role($action, $params, $role, $filtre) {
		$data = array (
				'action' => $action,
				'params' => $params 
		);
		$json = json_encode ( $data );
		
		foreach ( $this->infos as $key => $value ) {
			$envoi = !$filtre;
			if ($filtre && $this->infos [$key]->course == $params->course && $this->infos [$key]->activity == $params->activity ) {
				$envoi = true;
			}
			//echo ' indice ' . $key;
			if ($envoi && $this->infos [$key]->role == $role) {
				//echo ' envoi ';
				foreach ( $this->clients [$key] as $conn ) {
					$conn->send ( $json );
				}
			}
		}
	}
	
	/**
	 * Updates a user status to online or offline.
	 *
	 * @param object $conn
	 *        	- the client connection.
	 * @param object $params
	 *        	- the data received from the client.
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
			
			// If Online
			if ($params->status == 'online') {
				// Store Connection
				$this->connections [$conn] = $params->user_id;
				if (! isset ( $this->clients [$params->user_id] )) {
					$this->clients [$params->user_id] = array ();
				}
				$this->infos [$params->user_id] = $info;
				array_push ( $this->clients [$params->user_id], $conn );
			} else if ($params->status == 'offline') {
				// Close Connection
				unset ( $this->connections [$conn] );
				$key = array_search ( $conn, $this->clients [$params->user_id] );
				$this->clients [$params->user_id] [$key]->close ();
				unset ( $this->clients [$params->user_id] [$key] );
				unset ( $this->infos [$params->user_id] );
			}
		}
	}
	
	/**
	 * Traitement a r�aliser a chaque transcription recu
	 *
	 * @param object $params
	 *        	- the data received from the client.
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
		// recuperation user_id
		if (mod_pastel_transcription ( $this->userid, $params ) == false) {
			return;
		}
		
		// aiguillage sur les cibles
		$this->send_data_role ( "transcription", $params, ROLE_ETUDIANT, true );
		$this->send_data_role ( "transcription", $params, ROLE_ENSEIGNANT, true );
		$this->send_data_role ( "transcription", $params, ROLE_RESSOURCE, false );
		$this->send_data_role ( "transcription", $params, ROLE_ANALYSE, false );
	}
	
	/**
	 * A chaque changement de page (msg de l'enseignant)
	 *
	 * @param object $params
	 *        	- the data received from the client.
	 */
	private function chgtPage(ConnectionInterface $conn, $params) {
		
		// le numero de page du message est le numero de page courante
		if (mod_pastel_chgtPage ( $this->userid, $params) == false) {
			return;
		}
		// on recupere $numeroPageMax dans le chap intro de l'activite
		
		$numero = intval($params->page);
		if ($params->navigation == 'forward') {
			$numero = $numero + 1;
		}
		if ($params->navigation == 'backward') {
			$numero = $numero - 1;
		}
		$maxpage = mod_pastel_get_maxPage($params);
		if ($maxpage < $numero || $numero < 0) {
			$response = array (
					'action' => 'error',
					'params' => array ('message' => 'Page inexistante !')
			);
			$this->response ( $conn, $response );
			return;
		}
		$params->page = $numero;
		// aiguillage sur les cibles
		$this->send_data_role ( "page", $params, ROLE_ETUDIANT, true );
		$this->send_data_role ( "page", $params, ROLE_ENSEIGNANT, true );
		$this->send_data_role ( "page", $params, ROLE_RESSOURCE,false );
		$this->send_data_role ( "page", $params, ROLE_ANALYSE, false );
	}
	
	/**
	 * Quand un etudiant emet une alerte.
	 *
	 * @param object $params
	 *        	- the data received from the client.
	 */
	private function notifyAlerte($params) {
		//enregistrement en bdd dans la table pastel_user_event
		if (mod_pastel_userEvent ( $this->userid, $params) == false) {
			return;
		}
		$params->user_id = $this->userid;
		// aiguillage sur les cibles
		//$this->send_data_role ( "alerte", $params, ROLE_ANALYSE, false );
		if (strcmp($params->container,"alert") === 0) {
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
		
		// aiguillage sur les cibles
		//$this->send_data_role ( "indicator", $params, ROLE_RESSOURCE, false );
		$this->send_data_role ( "indicator", $params, ROLE_ENSEIGNANT, true );
	}
	
	/**
	 * Posts a message into a chat session and sends it to the other user in
	 * that session.
	 *
	 * @param object $params
	 *        	- the data received from the client.
	 */
	private function addRessource($params) {
		if (mod_pastel_addRessource ( $this->userid, $params) == false) {
			return;
		}
		
		// aiguillage sur les cibles
		$this->send_data_role ( "ressource", $params, ROLE_ETUDIANT, true );
		$this->send_data_role ( "ressource", $params, ROLE_ENSEIGNANT, true );
		$this->send_data_role ( "ressource", $params, ROLE_ANALYSE, false );
	}
	private function isValid(ConnectionInterface $conn, $message) {
		try {
			// format valid ?
			$data = json_decode ( $message );
			if (isset ( $data->action ) == false || isset ( $data->params ) == false) {
				$response = array ('action' => 'error',
						'params' => array ('message' => 'Format message incorrect')	);
				$this->response ( $conn, $response );
				return false;
			}
			// first message must be update_status
			if (! isset ( $this->connections [$conn] ) && $data->action != 'update_status') {
				$response = array (
						'action' => 'error',
						'params' => array (
								'message' => 'Identification requise' 
						) 
				);
				$this->response ( $conn, $response );
				return false;
			}
			return true;
		} catch ( Exception $err ) {
			$response = array (
					'action' => 'error',
					'params' => array (
							'message' => $err->getMessage () 
					) 
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