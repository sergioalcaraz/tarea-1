<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Ta-Te-Ti</title>
        <link rel="stylesheet" href="css/estilo.css" charset="utf-8">
    </head>
    <body>
        <header>
            <h1>Ta-Te-Ti</h1>
        </header>
        <div id="info-jugador"></div>
        <div id="info-turno"></div>
        <ul id="tablero"></ul>
        <div class="resultado"></div>
        <div id="botonera">
            <button id="btn-iniciar" class="btn-juego">Jugar</button>
            <button id="btn-reiniciar" class="btn-juego" style="display: none;">Volver a jugar</button>
        </div>
        <script src="plugins/jquery-3.1.0.min.js"></script>
        <script src="js/tateti.js"></script>
        <script src="js/tablero.js"></script>
        <script>
            new Tablero().eventoBtnIniciar()
        </script>
    </body>
</html>
