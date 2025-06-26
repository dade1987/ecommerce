<?php

session_start();

if (isset($_POST['art_saldo'])) {
    $num = $_POST['art_saldo'];
    $percent = $_POST['percent'];
    $importo = $_POST['importo'];

    if (!empty($percent)) {
        if ($_SESSION['articolo'][$num]['sconto_saldo'] == "1") {
            $_SESSION['articolo'][$num]['sconto_saldo'] = "0";
            $_SESSION['articolo'][$num]['descrizione'] = substr($_SESSION['articolo'][$num]['descrizione'], 0, strlen($_SESSION['articolo'][$num]['descrizione']) - 3);
            $_SESSION['articolo'][$num]["prezzo_pubblico_unitario"] = $_SESSION['articolo'][$num]["prezzo_pubblico_unitario"] / (100 - $percent) * 100;
        } else {
            $_SESSION['articolo'][$num]['sconto_saldo'] = "1";
            $_SESSION['articolo'][$num]['descrizione'] = $_SESSION['articolo'][$num]['descrizione'] . " $percent%";
            $_SESSION['articolo'][$num]["prezzo_pubblico_unitario"] = $_SESSION['articolo'][$num]["prezzo_pubblico_unitario"] / 100 * (100 - $percent);
        }
    } elseif (!empty($importo)) {
        
            $_SESSION['articolo'][$num]['sconto_saldo'] = "1";
            $_SESSION['articolo'][$num]['descrizione'] = $_SESSION['articolo'][$num]['descrizione'] ;
            $_SESSION['articolo'][$num]["prezzo_pubblico_unitario"] = $importo;
        
    }
}
