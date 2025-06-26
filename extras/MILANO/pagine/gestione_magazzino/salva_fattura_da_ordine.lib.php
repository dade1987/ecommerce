<?php

include_once("../../classi/config.php");
include_once("../../classi/funzioni.php");

list($status, $user) = auth_get_status();

session_start();

switch ($_POST['action']) {

    case "salva_fattura_da_ordine":
        
        $query='INSERT INTO db_fatture (anno,numero_fattura,fattura,negozio,data,intestatario,totale_fattura) VALUES ("'.date('Y').'","'.numero_fattura().'","'.addslashes($_POST['fattura']).'","'.addslashes($user['nome_negozio']).'","'.  addslashes(date('Y-m-d h:i:s')).'","'.addslashes($_POST['intestatario']).'","'.addslashes($_POST['totale_fattura']).'");';
        
        $db_fatture->query($query);
        
        echo $query;

        break;
}



