<?php
session_start();

//Includo le classi necessarie
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");

//Memorizzo le variabili per verificare se l'utente è collegato e per il nome utente
list($status, $user) = auth_get_status();
?>
<html>
    <head>	
        <meta charset="UTF-8">

        <!-- Inclusione jQuery -->
        <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

        <script src="/bimbo/js/brainextension.js" type="text/javascript"></script>
        <script src="/bimbo/jQuery/jquery.ui.touch-punch.min.js" type="text/javascript"></script>
        
        <script>
            function stampa(){
                $('#stampa').printArea();
            }
        </script>
        
    </head>

    <body>


        <div class="container-fluid"> 
            <div class="row clearfix">
                <div class="col-md-12">
                    <form class="form" method="post">
                         <div class="col-md-2">
                            <input class="form-control" type="text" placeholder="Negozio" name="negozio">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" placeholder="Data" name="data">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="text" placeholder="Totale" name="totale">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success" name="submit" value="submit">Yeah!</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row clearfix" id="stampa">
                <div class="col-md-12">
                    <?php
                    //Se un utente è collegato
                    if ($status == AUTH_LOGGED || license_has($user, "sede_centrale")) {

                        if ($_POST["submit"] === "submit") {
                            $query = "select barcode, descrizione, costo_aziendale from elenco_movimenti where barcode!='' and costo_aziendale!='' and escluso_fattura='0' group by barcode;";

                            //echo $query;

                            $risultato = $db_magazzino->query($query);


                            echo "<h1>INVENTARIO " . $_POST['negozio'] . "<br/>DATA: " . $_POST['data'] . "</h1>";


                            echo "<table class='table striped'>";
                            echo "<tr><th>Barcode</th><th>Descrizione</th><th>Quantita</th><th>Costo aziendale</th><th>Totale</th></tr>";

                            for ($prezzo_totale = 0.00; ($prezzo_totale <= $_POST['totale']) && ($row = $risultato->fetch_assoc()); $prezzo_totale+=$int_quantita * $row["costo_aziendale"]) {

                                //var_dump($row["barcode"]);
                                //var_dump($_POST['totale']);

                                $int_quantita = rand(100, 200);

                                echo "<tr><td>" . $row["barcode"] . "</td><td>" . $row["descrizione"] . "</td><td>" . $int_quantita . "</td><td>" . number_format($row["costo_aziendale"], 2) . "</td><td>" . number_format(($int_quantita * $row["costo_aziendale"]), 2) . "</td></tr>";
                            }

                            echo "</table>";

                            echo "<h1>TOTALE: " . number_format($prezzo_totale, 2) . "</h1>";
                        }
                        ?>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-md-12">
                        <button class="btn btn-info" onclick="stampa();">Stampa</button>
                    </div>
                </div>

            </div>

            <?php
        }
        ?>
    </body>
</html>
