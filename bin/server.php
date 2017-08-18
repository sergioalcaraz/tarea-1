<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\TatetiService;

// Crear una instancia del servidor para WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TatetiService()
        )
    ),
    8080,
    '127.0.0.1'
);

// Corre el servidor
$server->run();
