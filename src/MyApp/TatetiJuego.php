<?php
namespace MyApp;

/**
 * Juego tateti
 */
class TatetiJuego
{
    /**
     *
     * @var array[]
     */
    public $tablero;

    private $dimension;

    private $turno;

    private $cantJugado;

    private $minJuegos;

    public function __construct($dimension = 3)
    {
        // $this->tablero = [[null, null, null], [null, null, null], [null, null, null]];
        $this->dimension = $dimension;
        $this->generarTablero();
        $this->cantJugado = 0;
        $this->minJuegos = $dimension * 2 - 1;
        $this->turno = 'x';
    }

    private function generarTablero()
    {
        $tablero = [];
        for($i = 0; $i < $this->dimension; $i++) {
            $filas = [];
            for($j = 0; $j < $this->dimension; $j++) {
                $filas[] = null;
            }
            $tablero[] = $filas;
        }
        $this->tablero = $tablero;
    }

    public function marcar($celda)
    {
        list($fila, $columna) = $celda;
        if ($this->tablero[$fila][$columna] === null) {
            $this->tablero[$fila][$columna] = $this->turno;
            $this->turno = $this->turno === 'x' ? 'o' : 'x'; // pasar de un turno a otro
            $this->cantJugado++;
            return true;
        }
        return false;


    }

    public function checkGanador() {
        if ($this->cantJugado < $this->minJuegos) {
            return ['ganador' => false];
        }

        // verficar por cada fila
        for ($fila = 0; $fila < $this->dimension; $fila++) {
            $repeticiones = 1;
            $simbolo = $this->tablero[$fila][0];
            if ($simbolo === null) {
                continue;
            }
            // recorrer la fila
            for ($columna = 1; $columna < $this->dimension; $columna++) {
                if ($this->tablero[$fila][$columna] === $simbolo) {
                    $repeticiones++;
                } else {
                    break;
                }
            }
            if ($repeticiones === $this->dimension) {
                return ['ganador' => true, 'turno' => $simbolo];
            }
        }


        // verficar por cada columna
        for ($columna = 0; $columna < $this->dimension; $columna++) {
            $repeticiones = 1;
            $simbolo = $this->tablero[0][$columna];
            if ($simbolo === null) {
                continue;
            }
            // recorrer la columna
            for ($fila = 1; $fila < $this->dimension; $fila++) {
                if ($this->tablero[$fila][$columna] === $simbolo) {
                    $repeticiones++;
                } else {
                    break;
                }
            }
            if ($repeticiones === $this->dimension) {
                return ['ganador' => true, 'turno' => $simbolo];
            }
        }

        // por diagonal principal
        $simbolo = $this->tablero[0][0];
        if ($simbolo !== null) {
            $fila = 1;
            $repeticiones = 1;
            while ($fila < $this->dimension) {
                if ($simbolo === $this->tablero[$fila][$fila]) {
                    $repeticiones++;
                } else {
                    break;
                }
                $fila++;
            }
            if ($repeticiones === $this->dimension) {
                return ['ganador' => true, 'turno' => $simbolo];
            }
        }

        // por diagonal secundaria
        $simbolo = $this->tablero[0][$this->dimension - 1];
        if ($simbolo !== null) {
            $fila = 1;
            $columna = $this->dimension - 2;
            $repeticiones = 1;
            while ($fila < $this->dimension) {
                if ($simbolo === $this->tablero[$fila][$columna]) {
                    $repeticiones++;
                } else {
                    break;
                }
                $fila++;
                $columna--;
            }
            if ($repeticiones === $this->dimension) {
                return ['ganador' => true, 'turno' => $simbolo];
            }
        }

        if ($this->cantJugado === $this->dimension * $this->dimension) {
            return ['ganador' => null, 'turno' => null];
        } else {
            return ['ganador' => false];
        }
    }

    public function getTurno()
    {
        return $this->turno;
    }

    public function getDimension()
    {
        return $this->dimension;
    }

    public function getCantJugado()
    {
        return $this->cantJugado;
    }
}
