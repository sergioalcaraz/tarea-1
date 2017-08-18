/**
 * Manejar el tablero y flujo del juego
 */
function Tablero() {
  'use strict'
  var _tateti = new TaTeTi()
  var conexion
  var self = this
  var turnoJugador
  var sgteTurno

  /**
   * Genera el tablero vacio
   */
  this.generarTablero = function() {
    $('#tablero').empty()
    for (var fila = 0; fila < 3; fila++) {
      for (var columna = 0; columna < 3; columna++) {
        $('#tablero').append('<li><div id="' + fila + '-' + columna + '" class="celda marcable"></div></li>')
      }
    }
    return self
  }

  this.enviarMensaje = function(mensaje) {
    if (typeof mensaje === 'object') {
      mensaje = JSON.stringify(mensaje)
    }
    self.conexion.send(mensaje)
  }

  this.jugadorActual = function() {
    $('#info-turno').html('<p>Es el turno de <span id="jugador" class="jugador-' + self.sgteTurno + '"></span></p>')
    return self
  }

  this.marcable = function() {
    if (self.turnoJugador === self.sgteTurno) {
      $('.marcable').on('click', function (event) {
        $('.marcable').off('click')
        var celda = $(this).attr('id').split('-')
        self.enviarMensaje({
          accion: 'marcar',
          celda: celda,
        })
      })
    } else {
      $('.marcable').off('click')
    }

    return self
  }

  this.marcar = function(celda, marcadoPor) {
    var marcado = $('#' + celda[0] + '-' + celda[1])
    marcado.toggleClass('celda-marcado-' + marcadoPor)
    marcado.toggleClass('marcable')
    marcado.off('click') // Desvincular el evento de esta celda
    return self
  }

  this.iniciar = function() {
    // Asegurar que exista una sola conexión
    if (self.conexion === undefined || self.conexion.readyState === self.conexion.CLOSED) {
      self.conexion = new WebSocket('ws://localhost:8080')

      // Vincular los eventos
      self.conexion.onopen = function(event) {

      }

      self.onerror = function(event) {
        console.log('Ocurrió un error')
      }

      self.conexion.onmessage = function(event) {
        var mensaje = JSON.parse(event.data)

        switch (mensaje.accion) {
          case 'onOpen':
            if (mensaje.estado) {
              self.enviarMensaje({
                'accion': 'iniciar'
              })
            } else {
              alert('No hay conexiones disponibles')
            }
            break
          case 'turnoJugador':
            self.turnoJugador = mensaje.turnoJugador
            $('#info-jugador').html('<p>Tu color es <span class="jugador-' + self.turnoJugador + '"></span></p>')
            break
          case 'empezar':
            self.sgteTurno = mensaje.sgteTurno
            $('.resultado').empty()
            $('#btn-reiniciar').hide()
            self.generarTablero().jugadorActual().marcable()
            break
          case 'sgteTurno':
            self.sgteTurno = mensaje.sgteTurno
            self.marcable().jugadorActual().marcar(mensaje.celda, mensaje.marcadoPor)
            if (mensaje.ganador === true) {
              $('.resultado').html('El ganador es <span class="jugador-' + mensaje.turnoGanador + '"></span>')
              $('.marcable').off('click').toggleClass('marcable') // Desvincular el evento click a todas las celdas y quitar la clase marcable
              $('#info-turno').empty()
              $('#btn-reiniciar').show()
            } else if (mensaje.ganador === null) {
              $('.resultado').html('Empate')
              $('#info-turno').empty()
              $('#btn-reiniciar').show()
            }
            break
          case 'noPermitido':
            alert('Ya se esta jugando')
            break
          case 'rivalDesconectado':
            alert('El rival se ha desconectado')
            $('#tablero').empty()
            $('#info-turno').html('Esperando al rival...')

        }
      }
    }

    $('.resultado').empty()
    return this
  }

  this.eventoBtnIniciar = function() {
    $('#btn-iniciar').on('click', function(event) {
      self.iniciar()
      $(this).hide()
      $('#info-turno').html('Esperando al rival...')
    })

    $('#btn-reiniciar').on('click', function(event) {
      self.enviarMensaje({accion: 'reiniciar'})
    })
  }
}
