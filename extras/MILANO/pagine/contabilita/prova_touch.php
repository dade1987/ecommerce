<?php
session_start(); //Inizio la sessione

include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();

//echo $status." ".$user; 

if ($status == AUTH_LOGGED && license_has($user, "affiliato") && ($user['nome_negozio'] === 'BLACK FASHION THIENE' || $user['nome_negozio'] === 'BLACK FASHION ODERZO' || $user['nome_negozio'] === 'BLACK FASHION VICENZA' || $user['nome_negozio'] === 'BF NICOSIA' || $user['nome_negozio'] === 'BLACK FASHION CASTELFRANCO' || $user['nome_negozio'] === 'BLACK FASHION MONTEBELLUNA')
) {
    ?>
    <html>
        <head>
            <!-- Inclusione jQuery -->
            <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
            <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
            <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

            <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

            <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
            <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
            <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

            <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>

            <script type="text/javascript">
                function prova_touch() {
                    console.log("touch");
                }
            </script>


        </head>

        <body>

            <div class="container-fluid">
                <div class="row" id="barcode">

                    <div class="col-md-8" >

                        <button class="btn btn-default" onclick="prova_touch();">Stampa scontrino</button>
                    </div>

                </div>
            </div>



        </body>
    </html>
    <?php
}
?>

