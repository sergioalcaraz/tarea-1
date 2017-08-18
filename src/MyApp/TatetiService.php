<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Servidor WebSocket para el juego Tateti.
 *
 * @property array $jugadores
 */
class TatetiService implements MessageComponentInterface
{
    private $jugadores = [];
    /**
     * Juego tateti
     * @var \MyApp\TatetiJuego
     */
    private $tateti;


    public function onOpen(ConnectionInterface $conexion)
    {
        if (count($this->jugadores) < 2) {
            if (count($this->jugadores) === 0) {
                $this->jugadores[$conexion->resourceId] = ['turno' => 'x', 'conexion' => $conexion];
                $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => true]));
            } else {
                $jugador = $this->jugadores;
                $jugador = array_pop($jugador);
                $this->jugadores[$conexion->resourceId] = ['turno' => $jugador['turno'] === 'x' ? 'o' : 'x', 'conexion' => $conexion];
                $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => true]));
            }

            return;
        } else if (count($this->jugadores) === 2) {
            $conexion->send(json_encode(['accion' => 'noPermitido']));
            return;
        } else {
            $this->jugadores[$conexion->resourceId] = ['turno' => 'o', 'conexion' => $conexion];
            $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => true]));
            return;
        }
        $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => false]));
    }

    public function onMessage(ConnectionInterface $conexion, $mensaje)
    {
        $mensaje = json_decode($mensaje);
        switch ($mensaje->accion) {
            case 'iniciar':
                $respuesta = ['accion' => 'turnoJugador', 'turnoJugador' => $this->jugadores[$conexion->resourceId]['turno']];
                $conexion->send(json_encode($respuesta));
                if (count($this->jugadores) === 2) {
                    $this->tateti = new TatetiJuego();
                    $respuesta = ['accion' => 'empezar', 'sgteTurno' => $this->tateti->getTurno()];
                    // empezar el juego
                    foreach ($this->jugadores as $jugador) {
                        $jugador['conexion']->send(json_encode($respuesta));
                    }
                }
                break;
            case 'marcar':
                if ($this->tateti->marcar($mensaje->celda)) {
                    $checkGanador = $this->tateti->checkGanador();
                    $respuesta['accion'] = 'sgteTurno';
                    $respuesta['sgteTurno'] = $this->tateti->getTurno();
                    $respuesta['celda'] = $mensaje->celda;
                    $respuesta['marcadoPor'] = $this->jugadores[$conexion->resourceId]['turno'];
                    $respuesta['ganador'] = $checkGanador['ganador'];
                    if ($checkGanador['ganador'] === true || $checkGanador['ganador'] === null) {
                        $respuesta['turnoGanador'] = $checkGanador['turno'];
                    } else {

                    }
                    foreach ($this->jugadores as $jugador) {
                        $jugador['conexion']->send(json_encode($respuesta));
                    }
                }
                break;
            case 'reiniciar':
                $this->tateti = new TatetiJuego();
                $respuesta = ['accion' => 'empezar', 'sgteTurno' => $this->tateti->getTurno()];
                // empezar el juego
                foreach ($this->jugadores as $jugador) {
                    $jugador['conexion']->send(json_encode($respuesta));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conexion)
    {
        unset($this->jugadores[$conexion->resourceId]);
        foreach ($this->jugadores as $jugador) {
            $jugador['conexion']->send(json_encode(['accion' => 'rivalDesconectado']));
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Un error inesperado ha ocurrido: {$e->getMessage()}\n";
        $conn->close();
    }
}
