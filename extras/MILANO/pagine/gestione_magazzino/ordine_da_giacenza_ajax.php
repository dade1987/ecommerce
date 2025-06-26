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

function sortByOption_2($a, $b) {
    return strcmp($a['mancanza'], $b['mancanza']);
}

//Se un utente è collegato
if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {

    $barcode = substr($_POST['barcode'], 0, -1);

    $query = "SELECT gruppo, colore FROM elenco_movimenti WHERE barcode LIKE  '$barcode%' LIMIT 1;";
    $result = $db_magazzino->query($query);
    $row = $result->fetch_assoc();

    $gruppo = $row['gruppo'];
    $colore = $row['colore'];

    $query1 = "SELECT gruppo,colore,nome,quantita from pannelli where gruppo='$gruppo' and colore='$colore';";
    $result1 = $db_magazzino->query($query1);

    $bla = array();

    while ($row1 = $result1->fetch_assoc()) {
        $pannello = $row1['nome'];

        $query2 = "SELECT negozio from magazzino_base where pannello='$pannello' and bool='checked';";
        $result2 = $db_magazzino->query($query2);

        while ($row2 = $result2->fetch_assoc()) {

            $query3 = "SELECT SUM( quantita ) AS quantita FROM ( SELECT cliente, fornitore, gruppo, colore, SUM( quantita ) AS quantita FROM elenco_movimenti WHERE cliente =  '" . $row2['negozio'] . "' AND gruppo =  '" . $row['gruppo'] . "' AND colore =  '" . $row['colore'] . "' UNION ALL SELECT cliente, fornitore, gruppo, colore, SUM( - quantita ) AS quantita FROM elenco_movimenti WHERE fornitore =  '" . $row2['negozio'] . "' AND gruppo =  '" . $row['gruppo'] . "' AND colore =  '" . $row['colore'] . "' GROUP BY gruppo ) x GROUP BY gruppo";
            $result3 = $db_magazzino->query($query3);

            $row3 = $result3->fetch_assoc();

            $rimanenze = rimanenze($row2['negozio'], $barcode, null, null, null, null);

            $mancanza = $row1['quantita'] - $row3['quantita'];

            if ($mancanza > 0) {

                #var_dump($row2['negozio'],$mancanza);
                #echo "<br/>";

                $bla[] = array('barcode' => $barcode, 'gruppo' => $row['gruppo'], 'colore' => $row['colore'], 'negozio' => $row2['negozio'], 'mancanza' => $mancanza);
                #DEVE RIORDINARE GLI ARRAY IN BASE A DOVE MANCA DI PIU
            } else {
                #echo("NON MANCANTE A ".$row2['negozio']."<br/>");
            }
        }
        //var_dump(array('barcode' => $barcode, 'gruppo' => $row['gruppo'], 'colore' => $row['colore'], 'negozio' => $row2['negozio'], 'mancanza' => $mancanza));


        #echo json_encode($bla);
    }
    usort($bla, 'sortByOption_2');

    echo json_encode($bla);
}