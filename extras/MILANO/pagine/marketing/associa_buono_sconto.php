<html>
    <head>
        <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
    </head>
    <body>

        <?php
        include_once("../../classi/utils.lib.php");
        include_once("../../classi/license.lib.php");
        include_once("../../classi/funzioni.php");
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");
        list($status, $user) = auth_get_status();
        ?>

        <div class="container-fluid">

            <?php
            if ($status == AUTH_LOGGED) {
                menu();

                if (isset($_POST['submit'])) {
                    $query = "UPDATE elenco_movimenti SET attivo=1 WHERE barcode='" . $_POST['barcode_sconto'] . "' ORDER BY id DESC LIMIT 1;";
                    $db_magazzino->query($query);
                    //echo $query;
                    $query = "UPDATE db_fidelity_card SET punti=punti-40 WHERE numero='" . $_GET['numero_fidelity'] . "' LIMIT 1;";
                    //echo $query;
                    $db_fidelity_card->query($query);

                    echo "<h1>Buono " . $_POST['barcode_sconto'] . " attivo. Sono stati scalati 40 punti dalla carta numero " . $_GET['numero_fidelity'] . " </h1>";
                } elseif (isset($_GET['numero_fidelity'])) {


                    $query = "SELECT * FROM db_fidelity_card WHERE numero=" . $_GET['numero_fidelity'] . " LIMIT 1;";
                    $fidelity = $db_fidelity_card->query($query)->fetch_assoc();

                    if ($fidelity['punti'] >= 40) {
                        echo "<h3>ASSOCIA IL BUONO SCONTO</h3>";
                        echo "<form method=\"POST\">";
                        echo "<table><tr><td><label>Barcode Fidelity Card</label></td><td><input type=\"text\" name=\"barcode_fidelity\" value=\"" . $fidelity['numero_vecchio'] . "\" disabled></td></tr>";
                        echo "<tr><td><label>Barcode Buono Sconto</label></td><td><input type=\"text\" name=\"barcode_sconto\" placeholder=\"BS0000000001\"></td></tr></table>";
                        echo "<button type=\"submit\" name=\"submit\">Invia i dati</button>";
                        echo "</form>";
                    } else
                        non_autorizzato();
                } else
                    non_autorizzato();
            }
            ?>
    </body>