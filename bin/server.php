<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\TatetiService;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TatetiService()
        )
    ),
    8080,
    '127.0.0.1'
);

$server->run();
