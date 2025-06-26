<?php

session_start();


require_once('../../classi/config.php');
include_once("../../classi/auth.lib.php");
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
//<div id="dati_ddt" style="display:none;"><div id="numero_ddt"></div>
//<div id="data_ddt"></div><div id="mittente_ddt"></div><div id="destinatario_ddt"></div><div id="trasportatore_ddt"></div></div>

list($status, $user) = auth_get_status();

/* $query='SELECT  numero
  FROM  ddt
  ORDER BY numero  DESC
  LIMIT 1';
  $numero_ddt=$db_fatture->query($query);
  $numero_ddt=$numero_ddt->fetch_assoc();
  $numero_ddt=$numero_ddt['numero']+1; */

$query = "SELECT numero from ddt WHERE mittente='" . $user['nome_negozio'] . "' ORDER BY numero DESC LIMIT 1;";


$numero = $db_magazzino->query($query);

if ($numero->num_rows > 0) {

    $numero = $numero->fetch_assoc();

    $numero_ddt = $numero['numero'] + 1;
} else {
    $numero_ddt = 1;
}
//echo "console.log('".var_export($numero_ddt,true)."');";
//echo $query;


$query = 'SELECT * 
FROM ' . $_CONFIG["table_utenti"] . '
WHERE nome_negozio =  "' . $user["nome_negozio"] . '"
LIMIT 1;';
//echo $query;
$mittente = $conn->query($query);
$mittente = $mittente->fetch_assoc();


$query = 'SELECT * 
FROM  ' . $_CONFIG["table_utenti"] . '
WHERE nome_negozio = "' . $_POST['negozio'] . '"
LIMIT 1;';
$destinatario = $conn->query($query);
$destinatario = $destinatario->fetch_assoc();
//echo $query;

$_SESSION['numero_ddt2'] = $numero_ddt;
$_SESSION['numero_ddt'] = "<b>Numero DDT:</b><br/> " . $numero_ddt . "<br/><br/>";
$_SESSION['data_ddt'] = "<b>Data:</b> <br/>" . date('Y-m-d H:i:s', strtotime('now')) . "<br/><br/>";

$_SESSION['mittente_ddt'] = "<b>Fornitore / Emittente :</b> <br/>" . $mittente['nome_negozio'] . "<br/>" . $mittente['indirizzo_negozio'] . "<br/>" . $mittente['citta_negozio'] . "<br/>" . $mittente['partita_iva_negozio'] . "<br/><br/>";
$_SESSION['destinatario_ddt'] = "<b>Cliente:</b> <br/>" . $destinatario['nome_negozio'] . "<br/>" . $destinatario['indirizzo_negozio'] . "<br/>" . $destinatario['citta_negozio'] . "<br/>" . $destinatario['partita_iva_negozio'] . "<br/><br/>";

$_SESSION['intestatario_fattura'] = "<b>Intestatario:</b> <br/>" . $destinatario['nome_sede_legale'] . "<br/>" . $destinatario['indirizzo_sede_legale'] . "<br/>" . $destinatario['citta_sede_legale'] . "<br/>" . $destinatario['partita_iva_sede_legale'] . "<br/><br/>";

$_SESSION['trasportatore_ddt'] = "<b>Trasportatore:</b> <br/>" . trasportatore . "<br/><br/>";

echo "$('div#numero_ddt').html('" . $_SESSION['numero_ddt'] . "');";
echo "$('div#data_ddt').html('" . $_SESSION['data_ddt'] . "');";
echo "$('div#mittente_ddt').html('" . $_SESSION['mittente_ddt'] . "');";
echo "$('div#destinatario_ddt').html('" . $_SESSION['destinatario_ddt'] . "');";
echo "$('div#intestatario_fattura').html('" . $_SESSION['intestatario_fattura'] . "');";
echo "$('div#trasportatore_ddt').html('" . $_SESSION['trasportatore_ddt'] . "');";
echo "$('div#codice_tracciatura').html('<b>Codice tracciatura:</b> <br/><input name=\"codice_tracciatura\" type=\"text\"></input>');";
echo "$('div#dati_ddt').show();";


$_SESSION['intestazione_ddt'] = '<div id="numero_ddt" style="float:left;">' . $_SESSION['numero_ddt'] . '</div><div id="data_ddt" style="margin-left:20px;float:left;">' . $_SESSION['data_ddt'] . '</div><div id="trasportatore_ddt" style="margin-left:20px;float:left;">' . $_SESSION['trasportatore_ddt'] . '</div><div id="mittente_ddt" style="margin-left:20px;float:left;clear:both;">' . $_SESSION['mittente_ddt'] . '</div><div id="destinatario_ddt"  style="margin-left:20px;float:left;">' . $_SESSION['destinatario_ddt'] . '</div><div id="intestatario_fattura" style="float:right;clear:right;"></div>';
?>
