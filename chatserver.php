<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\Controllers\EventosController;

require  'vendor/autoload.php';


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new EventosController()
        )
    ),
    8080
);

$server->run();
?>