# Juego Tateti
## Electiva II - Programación front end

### Tarea 2 - Juego multijugador (WebSocket)

El juego implementado para la tarea 1, modificarlo de forma que permita jugar al mismo tiempo a 2 jugadores conectados desde 2 navegadores distintos.

El juego debe soportar solo 1 juego a la vez, por solo 2 jugadores.

El sistema puede asumir que el primer jugador que se "conecta" al web socket es el "Jugador 1", y el siguiente en conectarse es el "Jugador ", y el juego ya puede empezar.

### Requisitos
- Apache web server.
- PHP.
- [Composer](http://getcomposer.org).

### Instrucciones
- Instalar las dependencias del proyecto: `composer install`.
- Correr el servidor web Apache
- Correr el servidor para WebSocket `php /directorio/del/proyecto/bin/server.php`
