/**
 * Controla el juego
 */
function TaTeTi() {
  var _dimension
  var _tablero
  var _turno
  var _cantJugado
  var _minJuegos
  var that = this

  this.marcar = function (fila, columna) {
    if ((fila >= 0 && fila < _dimension) && (columna >= 0 && columna < _dimension)) {
      if (_tablero[fila][columna] === null) {
        _tablero[fila][columna] = _turno
        _turno = _turno === 'x' ? 'o' : 'x' // pasar de un turno a otro
        _cantJugado++
        return true
      }
    }
    return false
  }

  this.iniciar = function (turno, dimension) {
    if (turno !== undefined) {
      if (turno === 'x' || turno === 'o') {
        _turno = turno
      } else {
        throw Error('Turno distinto a \'x\' y \'o\'')
      }
    } else {
      _turno = 'x'
    }

    if (dimension !== undefined) {
      if (dimension >= 3 && dimension <= 10) {
        _dimension = dimension
      } else {
        throw Error('Dimensión es incorrecto')
      }
    } else {
      _dimension = 3
    }

    _tablero = []
    for (var fila = 0; fila < _dimension; fila++) {
      _tablero.push([])
      for (var columna = 0; columna < _dimension; columna++) {
        _tablero[fila].push(null)
      }
    }
    _cantJugado = 0
    _minJuegos = _dimension * 2 - 1
  }

  this.ganador = function () {
    var repeticiones, simbolo, fila, columna

    if (_cantJugado < _minJuegos) {
      return false
    }

    // por cada fila
    for (fila = 0; fila < _dimension; fila++) {
      repeticiones = 1
      simbolo = _tablero[fila][0]
      if (simbolo === null) {
        continue
      }
      for (columna = 1; columna < _dimension; columna++) {
        if (_tablero[fila][columna] === simbolo) {
          repeticiones++
        } else {
          break
        }
      }
      if (repeticiones === _dimension) {
        return simbolo
      }
    }

    // por cada columna
    for (columna = 0; columna < _dimension; columna++) {
      repeticiones = 1
      simbolo = _tablero[0][columna]
      if (simbolo === null) {
        continue
      }
      for (fila = 1; fila < _dimension; fila++) {
        if (_tablero[fila][columna] === simbolo) {
          repeticiones++
        } else {
          break
        }
      }
      if (repeticiones === _dimension) {
        return simbolo
      }
    }

    // por diagonal principal
    simbolo = _tablero[0][0]
    if (simbolo !== null) {
      fila = 1
      repeticiones = 1
      while (fila < _dimension) {
        if (simbolo === _tablero[fila][fila]) {
          repeticiones++
        } else {
          break
        }
        fila++
      }
      if (repeticiones === _dimension) {
        return simbolo
      }
    }

    // por diagonal secundaria
    simbolo = _tablero[0][_dimension - 1]
    if (simbolo !== null) {
      fila = 1
      columna = _dimension - 2
      repeticiones = 1
      while (fila < _dimension) {
        if (simbolo === _tablero[fila][columna]) {
          repeticiones++
        } else {
          break
        }
        fila++
        columna--
      }
      if (repeticiones === _dimension) {
        return simbolo
      }
    }

    if (_cantJugado === _dimension * _dimension) {
      return null
    } else {
      return false
    }
  }

  this.getTurno = function () {
    return _turno
  }

  this.getDimension = function () {
    return _dimension
  }

  this.getCantJugado = function () {
    return _cantJugado
  }
}
