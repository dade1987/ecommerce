<?php

//Includo le classi necessarie
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");

error_reporting(E_ALL);

//Memorizzo le variabili per verificare se l'utente è collegato e per il nome utente
list($status, $user) = auth_get_status();

//Se un utente è collegato
if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {

    $ordine = $_POST['ordine_raggruppato'];


    $query = "SELECT id,numero from ddt WHERE mittente='" . $user['nome_negozio'] . "' and anno='".date('Y')."' ORDER BY numero DESC LIMIT 1;";
    $numero = $db_magazzino->query($query);

    if ($numero->num_rows > 0) {
        $numero = $numero->fetch_assoc();
        $numero_ddt = $numero['numero'] + 1;
    } else {
        $numero_ddt = numero_ddt();
    }
    
    $id= $numero['id'] + 1;

    $query_dati_mittente_ddt = "SELECT * FROM utenti3 WHERE nome_negozio='" . $user['nome_negozio'] . "' LIMIT 1;";
    $risultato_query_dati_mittente_ddt = $db_magazzino->query($query_dati_mittente_ddt);
    $row_risultato_query_dati_mittente_ddt = $risultato_query_dati_mittente_ddt->fetch_assoc();

    echo "<h1>APRI DDT SALVATI</h1>";

    foreach ($ordine as $nome_negozio => $array_negozio) {

        $ddt = "<div class='row'>\n";
        $ddt .= "<div class='col-md-12 col-xs-12'>\n";

        $ddt .= "<h1>\n";
        $ddt .= "Numero DDT: $numero_ddt\n";
        $ddt .= "</h1>\n";

        $ddt .= "</div>\n";
        $ddt .= "</div>\n";

        $query_dati_ddt = "SELECT * FROM utenti3 WHERE nome_negozio='" . $nome_negozio . "' LIMIT 1;";
        $risultato_query_dati_ddt = $db_magazzino->query($query_dati_ddt);
        $row_risultato_query_dati_ddt = $risultato_query_dati_ddt->fetch_assoc();

        $ddt .= "<div class='row'>\n";
        $ddt .= "<div class='col-md-5 col-xs-5'>\n";

        $ddt .= "<h3>\n";
        $ddt .= "<strong>CLIENTE:</strong><br/>\n";
        $ddt .= $nome_negozio . "\n";
        $ddt .= "</h3>\n";

        $ddt .= "<h4>\n";
        $ddt .= $row_risultato_query_dati_ddt['indirizzo_negozio'] . "<br/>\n";
        $ddt .= $row_risultato_query_dati_ddt['citta_negozio'] . " (";
        $ddt .= $row_risultato_query_dati_ddt['provincia_negozio'] . ")<br/>\n";
        $ddt .= $row_risultato_query_dati_ddt['partita_iva_negozio'] . " \n";
        $ddt .= "</h4>\n";

        $ddt .= "</div>\n";

        $ddt .= "<div class='col-md-5 col-xs-5'>\n";

        $ddt .= "<h3>\n";
        $ddt .= "<strong>MITTENTE:</strong><br/>\n";
        $ddt .= $user['nome_negozio'] . "\n";
        $ddt .= "</h3>\n";

        $ddt .= "<h4>\n";
        $ddt .= $row_risultato_query_dati_mittente_ddt['indirizzo_negozio'] . "<br/>\n";
        $ddt .= $row_risultato_query_dati_mittente_ddt['citta_negozio'] . " (";
        $ddt .= $row_risultato_query_dati_mittente_ddt['provincia_negozio'] . ")<br/>\n";
        $ddt .= $row_risultato_query_dati_mittente_ddt['partita_iva_negozio'] . " \n";
        $ddt .= "</h4>\n";

        $ddt .= "</div>\n";
        $ddt .= "</div>\n";

        $ddt .= "<div class='row'>\n";
        $ddt .= "<div class='col-md-12 col-xs-5'>\n";
        $ddt .= "<h2>\n";
        $ddt .= "ORDINE: \n";
        $ddt .= "</h2>\n";

        $ddt .= "</div>\n";
        $ddt .= "</div>\n";

        $ddt .= "<div class='row'>\n";

        $ddt .= "<div class='col-md-3 col-xs-3'>\n";
        $ddt .= "<h4><strong>\n";
        $ddt .= "BARCODE\n";
        $ddt .= "</h4></strong>\n";

        $ddt .= "</div>\n";

        $ddt .= "<div class='col-md-3 col-xs-3'>\n";
        $ddt .= "<h4><strong>\n";
        $ddt .= "GRUPPO\n";
        $ddt .= "</h4></strong>\n";
        $ddt .= "</div>\n";

        $ddt .= "<div class='col-md-3 col-xs-3'>\n";
        $ddt .= "<h4><strong>\n";
        $ddt .= "COLORE\n";
        $ddt .= "</h4></strong>\n";
        $ddt .= "</div>\n";

        $ddt .= "<div class='col-md-3 col-xs-3'>\n";
        $ddt .= "<h4><strong>\n";
        $ddt .= "QUANTITA\n";
        $ddt .= "</h4></strong>\n";
        $ddt .= "</div>\n";

        $ddt .= "</div>\n";

        foreach ($array_negozio as $numero_barcode => $array_barcode) {


            foreach ($array_barcode as $nome_gruppo => $array_gruppo) {

                foreach ($array_gruppo as $nome_colore => $array_colore) {
                    $ddt .= "<div class='row'>\n";

                    $ddt .= "<div class='col-md-3 col-xs-3'>\n";
                    $ddt .= "<strong>\n";
                    $ddt .= $numero_barcode;
                    $ddt .= "</strong>\n";
                    $ddt .= "</div>\n";

                    $ddt .= "<div class='col-md-3 col-xs-3'>\n";
                    $ddt .= "<strong>\n";
                    $ddt .= $nome_gruppo;
                    $ddt .= "</strong>\n";
                    $ddt .= "</div>\n";

                    $ddt .= "<div class='col-md-3 col-xs-3'>\n";
                    $ddt .= "<strong>\n";
                    $ddt .= $nome_colore;
                    $ddt .= "</strong>\n";
                    $ddt .= "</div>\n";

                    $ddt .= "<div class='col-md-3 col-xs-3'>\n";
                    $ddt .= "<strong>\n";
                    $ddt .= $array_colore['quantita'];
                    $ddt .= "</strong>\n";
                    $ddt .= "</div>\n";

                    $ddt .= "</div>\n\n";
                }
            }
        }

        $query_inserimento_ddt = "INSERT INTO ddt ('".date('Y')."',tipo,mittente,numero,data,ddt,negozio,codice_tracciatura) VALUES (\"DDT\",\"" . $user['nome_negozio'] . "\",\"$numero_ddt\",\"" . date('Y-m-d H:i:s', strtotime('now')) . "\",\"" . utf8_encode($ddt) . "\",\"$nome_negozio\",\"N.D.\");";
        $risultato_query_dati_mittente_ddt = $db_magazzino->query($query_inserimento_ddt);

        //echo htmlentities($query_inserimento_ddt);

        echo "<h1><a href='" . url . "/pagine/contabilita/visualizza_ddt.php?id=$id' target='_blank'>DDT " . $numero_ddt . " DI ". $nome_negozio ."</a></h1>";

        $id++;
        $numero_ddt++;
    }
}
