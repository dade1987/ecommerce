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

    $bla = [];
    
    $barcode = substr($_POST['barcode'], 0, -1);
    $data_da = $_POST['data_da'];
    $data_a = $_POST['data_a'];

    $query = "SELECT gruppo, colore FROM elenco_movimenti WHERE barcode LIKE  '$barcode%' LIMIT 1;";
    $result = $db_magazzino->query($query);
    $row = $result->fetch_assoc();
    
    //echo $query."\n";

    $gruppo = $row['gruppo'];
    $colore = $row['colore'];

    $query2 = "SELECT DISTINCT fornitore from elenco_movimenti where fornitore!='ESSE ERRE SAS' AND fornitore!='BLACK FASHION BIELLA' AND fornitore!='BLACK FASHION VENEZIA' AND fornitore!='Articoli'  AND  causale!='RESO' AND gruppo='$gruppo' AND colore='$colore' AND data >= '$data_da' AND data <= '$data_a';";
        //echo $query2."\n";

    $result2 = $db_magazzino->query($query2);
    

    while ($row2 = $result2->fetch_assoc()) {
        $rimanenze = quantita_venduta($row2['fornitore'], $gruppo, $colore, $data_da, $data_a);

        if ($rimanenze > 0) {

            $bla[] = array('barcode' => $barcode, 'gruppo' => $gruppo, 'colore' => $colore, 'negozio' => $row2['fornitore'], 'mancanza' => $rimanenze);
        } 
    }
   
    usort($bla, 'sortByOption_2');

    echo json_encode($bla);
}