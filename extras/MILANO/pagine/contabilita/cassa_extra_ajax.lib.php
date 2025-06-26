<?php

include_once("../../classi/config.php");
include_once("../../classi/funzioni.php");

list($status, $user) = auth_get_status();

session_start();

switch ($_POST['action']) {

    case "estrai_da_barcode":
        $barcode = substr($_POST['barcode'], 0, -1);

        $rimanenze = rimanenze($user['nome_negozio'], $barcode, NULL, NULL, NULL);

        #var_dump($user['nome_negozio']);

        $rimanenze = $rimanenze[0];

        if (!empty($rimanenze)) {
            $num_sess = count($_SESSION['articolo']) + 1;

            echo "var progressivo = $('#cassa table tbody tr').length;";
            echo "$('#cassa table tbody').prepend('<tr id=\"art_' + progressivo + '\"><td>" . $rimanenze['gruppo'] . "</td><td>" . $rimanenze['colore'] . "</td><td>" . $rimanenze['prezzo_pubblico_unitario'] . "</td><td onclick=\"elimina_articolo(' + progressivo + ');\" style=\"color:red;cursor:pointer;\">X<input type=\"hidden\" class=\"barcode\" value=\"" . $rimanenze['barcode'] . "\"><input type=\"hidden\" class=\"codice\" value=\"" . $rimanenze['codice'] . "\"></td></tr>');";
        }
        break;

    case "crea_sessione":
        unset($_SESSION['articolo']);

        $numero_prezzi = count($_POST['prezzo']);

        for ($i = 0; $i <= $numero_prezzi; $i++) {
            $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] = $_POST['prezzo'][$i];
            $_SESSION['articolo'][$i]['gruppo'] = $_POST['gruppo'][$i];
            $_SESSION['articolo'][$i]['colore'] = $_POST['colore'][$i];
            $_SESSION['articolo'][$i]['descrizione'] = $_POST['gruppo'][$i];

            if (count(barcode) > 0) {
                $_SESSION['articolo'][$i]['barcode'] = $_POST['barcode'][$i];
            }

            if (count(barcode) > 0) {
                $_SESSION['articolo'][$i]['codice'] = $_POST['codice'][$i];
            } else {
                $_SESSION['articolo'][$i]['codice'] = 'VECCHIO';
            }
        }

        var_dump($_SESSION['articolo']);

        $numero_sessioni = count($_SESSION['articolo']) - 1;

        for ($i = 0; $i < $numero_sessioni; $i++) {
            $query = "INSERT INTO elenco_movimenti (costo_aziendale,sconto_saldo,data, causale, codice,sconto_affiliato, sconto_pubblico, 
                    quantita,prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,
                    fornitore,cliente,pagamento,totale,saldo,resto,identificativo,cognome,nome,indirizzo,citta,cap,piva) VALUES 
                    ('0','0','" . date('Y-m-d H:i:s', strtotime('now')) . "','Vendita al dettaglio','" . $_SESSION['articolo'][$i]['codice'] . "',
                    60,0,'1','" . $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] . "','"
                    . $_SESSION['articolo'][$i]['descrizione'] . "','" . $_SESSION['articolo'][$i]['gruppo'] . "','"
                    . $_SESSION['articolo'][$i]['colore'] . "','" . $_SESSION['articolo'][$i]['barcode'] . "','" . $user['nome_negozio'] . "',
                    'Vendita al dettaglio','CONTANTI','" . $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] . "','0',
                    '0','','','','','','','')";

            $db_magazzino->query($query);

            echo "inserimento_magazzino - ";
        }

        break;
}

