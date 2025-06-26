<html>
    <head>
        <script src="./classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="./classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="./classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="./bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="./bootstrap/css/darkly.min.css" rel="stylesheet">
        <link href="./bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="./bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
        
        <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>


    </head>
    <body>
        <div class="container">
            <div class="row col-md-12">
                <?php
                include_once("./classi/config.php");
                include_once("./classi/auth.lib.php");

                list($status, $user) = auth_get_status();
                header("Refresh: 5;URL=./index.php");

                if ($status == AUTH_LOGGED) {
                    if (auth_logout()) {
                        echo '<div align="center"><h1>Disconnessione effettuata ... attendi il reindirizzamento</h1></div>';
                    } else {
                        echo '<div align="center"><h1>Errore durante la disconnessione ... attendi il reindirizzamento</h1></div>';
                    }
                } else {
                    echo '<div align="center"><h1>Non sei connesso ... attendi il reindirizzamento</h1></div>';
                }
                ?>
            </div>
        </div>
    </body>
</html>
