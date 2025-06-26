<?php

session_start();

include_once("../classi/utils.lib.php");
include_once("../classi/license.lib.php");
include_once("../classi/funzioni.php");
include_once("../classi/config.php");
include_once("../classi/auth.lib.php");

set_time_limit(3);

$data = date("d-m-Y_h-i-s");

list($status, $user) = auth_get_status();

$query = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $user['nome_negozio'] . "' LIMIT 1";
#echo $query;
$risultato = $conn->query($query);
$risultato = $risultato->fetch_assoc();

if ($user['nome_negozio'] === "BLACK FASHION VENEZIA") {

$nome_file = "scontrino.txt";
}
else {
$nome_file = "scontrino_" . $risultato['username'] . "_" . $data . ".txt";
}
#header("refresh:5;url='".$nome_file."'");
$file = fopen($nome_file, 'w');


if ($user['nome_negozio'] === "BLACK FASHION VENEZIA") {
    if (isset($_GET['apertura_cassetto'])) {
        fwrite($file, "912 ; 1");
    } else {

        fwrite($file, "1322");
        fwrite($file, "\r\n");

        for ($i = 0; isset($_SESSION['articolo'][$i]); $i++) {
            if (substr($_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], 0, 1) == "-") {
                $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] = substr($_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], 1);
                linea_prodotto($user['nome_negozio'],$file, $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], $_SESSION['articolo'][$i]['descrizione'], $_SESSION['articolo'][$i]['quantita_acquistata'], 3); #3 identifica il reso
            } else
                linea_prodotto($user['nome_negozio'],$file, $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], $_SESSION['articolo'][$i]['descrizione'], $_SESSION['articolo'][$i]['quantita_acquistata']);
        }

        $fidelity = $_SESSION['fidelity_card'];

        $importo = $_SESSION['sconto_in_cassa'];

        if ($importo > 0) {
            fwrite($file, "1325 ; -$importo ; SCONTO IN CASSA ; 1");
            fwrite($file, "\r\n");
        }


        /*if (!empty($fidelity)) {
            $punti = $_SESSION['punti_fidelity_card'];
        } else
            $punti = 0;

        fwrite($file, "112;Punti totali: $punti;0;1;1");

        fwrite($file, "\n");*/

        fwrite($file, "1332\r\n1329\r\n1323\r\n912 ; 1");

        registra_log();
    }
} else {
    if (isset($_GET['apertura_cassetto'])) {
        fwrite($file, "13");
    } else {

        fwrite($file, "0 0 0 ");
        fwrite($file, "\n");

        for ($i = 0; isset($_SESSION['articolo'][$i]); $i++) {
            if (substr($_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], 0, 1) == "-") {
                $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] = substr($_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], 1);
                linea_prodotto($user['nome_negozio'],$file, $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], $_SESSION['articolo'][$i]['descrizione'], $_SESSION['articolo'][$i]['quantita_acquistata'], 3); #3 identifica il reso
            } else
                linea_prodotto($user['nome_negozio'],$file, $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'], $_SESSION['articolo'][$i]['descrizione'], $_SESSION['articolo'][$i]['quantita_acquistata']);
        }

        $fidelity = $_SESSION['fidelity_card'];

        $importo = $_SESSION['sconto_in_cassa'];

        if ($importo > 0) {
            fwrite($file, "4 SCONTO IN CASSA                           1  1  -$importo");
            fwrite($file, "\n");
        }


        if (!empty($fidelity)) {
            $punti = $_SESSION['punti_fidelity_card'];
        } else
            $punti = 0;

        fwrite($file, "5 0 Punti totali: $punti");

        fwrite($file, "\n");

        fwrite($file, "6                                                0  0");
        fwrite($file, "\n");

        fwrite($file, "55%S%");


        registra_log();
    }
}

function linea_prodotto($negozio,$file, $pre, $des, $qua, $reso = 1) {
    global $user;
    
    if($user['nome_negozio']==="BLACK FASHION VENEZIA")
    {
    $pre=  str_replace(".", ",", $pre);
    fwrite($file,"1325 ; $pre ; $des ; 1 ; Articolo\r\n");
    }else
    {
    $npre = strlen($pre);
    $ndes = strlen($des);
    $nqua = strlen($qua);

    punta($file, 0); //0
    fwrite($file, $reso);

    punta($file, 28 - 1); //28-1=27
    fwrite($file, $pre);

    punta($file, 38 - 28 - $npre + 1); //38-4(pre)-24
    fwrite($file, "1");

    punta($file, 41 - 39 + 1); //41-1-38
    fwrite($file, $des);

    punta($file, 84 - 41 - $ndes);
    fwrite($file, $qua);

    fwrite($file, "\n");
    }
}

function punta($file, $puntatore) {
    //se devo fare 10 caratteri
    //$num=10 se $i<$num allora $i++
    //sposta puntatore
    //(1 per riga)
    $pos = ftell($file); #7 posizione corrente
    #la posizione corrente deve corrispondere a 0
    #e da la comincia il ciclo
    #echo "<br>".ftell($file);

    for ($i = 0; ftell($file) - $pos != $puntatore; $i++) {
        #echo ftell($file)."-";
        fseek($file, $pos + $i, SEEK_SET);
        fwrite($file, ' ');
    }
}

fclose($file);

#echo "<script>window.close();</script>";

ob_clean();
flush();
readfile($nome_file);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($nome_file));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($nome_file));

registra_log();
?>
