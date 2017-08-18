/**
 * Manejar el tablero y flujo del juego
 */
function Tablero() {
  'use strict'
  var conexion // Conexion WebSocket
  var self = this // para tener acceso en las funciones, (function scope)
  var turnoJugador // turno del jugador
  var sgteTurno // el siguiente turno a jugarse

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

  /**
   * Enviar mensaje al servidor
   * @param  String|Object mensaje mensaje a ser enviado
   */
  this.enviarMensaje = function(mensaje) {
    if (typeof mensaje === 'object') {
      // Si el mensaje es un objeto, parsear a un string JSON
      mensaje = JSON.stringify(mensaje)
    }
    self.conexion.send(mensaje)
  }

  /**
   * Establecer el turno actual en el tablero de juego
   * @return Tablero
   */
  this.jugadorActual = function() {
    $('#info-turno').html('<p>Es el turno de <span id="jugador" class="jugador-' + self.sgteTurno + '"></span></p>')
    return self
  }

  /**
   * Registrar un el evento click en las celdas "marcables"
   * @return Tablero
   */
  this.marcable = function() {
    // Si no es el turno para el jugador no se registra el evento
    if (self.turnoJugador === self.sgteTurno) {
      $('.marcable').on('click', function (event) { // Se registra el evento
        $('.marcable').off('click') // Una vez hecho el click se desvincula el evento click en todos los marcables
        var celda = $(this).attr('id').split('-') // se obtiene las coordenadas de la celda
        // Se envia el mensaje para "marcar" una celda
        self.enviarMensaje({
          accion: 'marcar',
          celda: celda,
        })
      })
    } else { // Si no es el turno del jugador se desvincula los eventos click
      $('.marcable').off('click')
    }

    return self
  }

  /**
   * Marcar en el tablero la celda seleccionada con el color del turno especifico
   * @param  Array celda
   * @param  String marcadoPor
   * @return Tablero
   */
  this.marcar = function(celda, marcadoPor) {
    var marcado = $('#' + celda[0] + '-' + celda[1])
    marcado.toggleClass('celda-marcado-' + marcadoPor) // Se marca la celda
    marcado.toggleClass('marcable') // A partir de ahora ya no es marcable
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
        // Se espera un tipo de accion
        switch (mensaje.accion) {
          case 'onOpen':
            // Si pudo ingresar
            if (mensaje.estado) {
              self.enviarMensaje({
                'accion': 'iniciar'
              })
            } else {
              alert('No hay conexiones disponibles')
            }
            break
          case 'turnoJugador':
            // Establecer el turno del jugador
            self.turnoJugador = mensaje.turnoJugador
            $('#info-jugador').html('<p>Tu color es <span class="jugador-' + self.turnoJugador + '"></span></p>')
            break
          case 'empezar':
            // Empieza el juego
            self.sgteTurno = mensaje.sgteTurno
            $('.resultado').empty()
            $('#btn-reiniciar').hide()
            self.generarTablero().jugadorActual().marcable()
            break
          case 'sgteTurno':
            // Accion para jugar el siguiente turno
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

  /**
   * Registrar evento click para jugar
   */
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
