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
 * Web Socket Server Pastel run server script.
 *
 * Must be run from Command Line, or the chat won't work.
 *
 * @package    mod_pastel
 * @copyright  2020 Marc Leconte
 */

define('CLI_SCRIPT', true);

// Require Moodle and Block Libs.
require_once('../../../config.php');
require_once('../lib.php');

// Require Ratchet Libs and Server Class.
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/server.php');

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

// Create Server.
$server = IoServer::factory(
    new WsServer(new mod_pastel_server()), mod_pastel_get_server_port()
);

// Run Server.
$server->run();
