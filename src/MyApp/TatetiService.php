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
    /**
     * Lista de jugadores
     * @var array
     */
    private $jugadores = [];
    /**
     * Juego tateti
     * @var \MyApp\TatetiJuego
     */
    private $tateti;

    /**
     * Abrir la conexion WebSocket
     * @param  ConnectionInterface $conexion
     */
    public function onOpen(ConnectionInterface $conexion)
    {
        if (count($this->jugadores) < 2) { // Si hay lugar disponible
            if (count($this->jugadores) === 0) { // Si es el primero en entrar
                $this->jugadores[$conexion->resourceId] = ['turno' => 'x', 'conexion' => $conexion];
                $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => true]));
            } else { // Jugadores luego del primero
                $jugador = $this->jugadores;
                $jugador = array_pop($jugador);
                $this->jugadores[$conexion->resourceId] = ['turno' => $jugador['turno'] === 'x' ? 'o' : 'x', 'conexion' => $conexion];
                $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => true]));
            }
            return;
        } else if (count($this->jugadores) === 2) { // No hay mas lugar
            $conexion->send(json_encode(['accion' => 'noPermitido']));
            return;
        }
        $conexion->send(json_encode(['accion' => 'onOpen', 'estado' => false]));
    }

    public function onMessage(ConnectionInterface $conexion, $mensaje)
    {
        $mensaje = json_decode($mensaje);
        switch ($mensaje->accion) { // tipo de accion
            case 'iniciar': // Accion llamado luego de onOpen, inicializa datos para el jugador, como el turno del mismo
                $respuesta = ['accion' => 'turnoJugador', 'turnoJugador' => $this->jugadores[$conexion->resourceId]['turno']];
                $conexion->send(json_encode($respuesta));
                if (count($this->jugadores) === 2) { // Una vez que un par de jugadores estan listos se empieza el juego
                    $this->empezarJuego();
                }
                break;
            case 'marcar': // Accion marcar una celda
                if ($this->tateti->marcar($mensaje->celda)) { // Si se puedo marcar
                    $checkGanador = $this->tateti->checkGanador(); // Se verica si hay ganador
                    // Se construye la respuesta
                    $respuesta['accion'] = 'sgteTurno';
                    $respuesta['sgteTurno'] = $this->tateti->getTurno();
                    $respuesta['celda'] = $mensaje->celda;
                    $respuesta['marcadoPor'] = $this->jugadores[$conexion->resourceId]['turno'];
                    $respuesta['ganador'] = $checkGanador['ganador'];
                    if ($checkGanador['ganador'] === true || $checkGanador['ganador'] === null) {
                        $respuesta['turnoGanador'] = $checkGanador['turno']; // Si hay un ganador se estable el turno ganador
                    }
                    // Se emite la respuesta a los jugadores
                    foreach ($this->jugadores as $jugador) {
                        $jugador['conexion']->send(json_encode($respuesta));
                    }
                }
                break;
            case 'reiniciar': // Reiniciar la partida
                $this->empezarJuego();
                break;
        }
    }

    /**
     * Cerrar la conexion
     * @param  ConnectionInterface $conexion
     * @return null
     */
    public function onClose(ConnectionInterface $conexion)
    {
        unset($this->jugadores[$conexion->resourceId]); // Al cerrar la conexion liberar el lugar del jugador
        foreach ($this->jugadores as $jugador) { // Avisar a los jugadores restantes, en este caso solo a 1
            $jugador['conexion']->send(json_encode(['accion' => 'rivalDesconectado']));
        }
    }

    /**
     * Emitir un mensaje de error al log
     * @param  ConnectionInterface $conexion
     * @param  \Exception $e
     * @return
     */
    public function onError(ConnectionInterface $conexion, \Exception $e)
    {
        echo "Un error inesperado ha ocurrido: {$e->getMessage()}\n";
        $conexion->close(); // Al ocurrir el error cerrar la conexion
    }

    /**
     * Inicar el juego.
     * Emitir un mensaje de sgteTurno, que iniciar la interaccion con el juego
     */
    private function empezarJuego() {
        $this->tateti = new TatetiJuego(); // Se crea el juego
        $respuesta = ['accion' => 'empezar', 'sgteTurno' => $this->tateti->getTurno()];
        // se emite el mensaje a cada jugador
        foreach ($this->jugadores as $jugador) {
            $jugador['conexion']->send(json_encode($respuesta));
        }
    }
}
