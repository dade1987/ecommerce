<?php
session_start();
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
?>
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
        $i=0;
        $array=array();
        
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
            menu();    

            echo "<div class=\"row clearfix\">
                <form method=\"POST\">
                <div class=\"col-md-2\">
                <label for=\"data_partenza\"><b>Data d'inizio</b></label>
		<input type=\"date\" class=\"form-control\"  name=\"data_partenza\" >
                </div>
                <div class=\"col-md-2\">
		<label for=\"data_finale\"><b>Data finale</b></label>
		<input class=\"form-control \" type=\"date\" name=\"data_finale\" >
                </div>";
            #select($_CONFIG['table_utenti']);
            echo "<div class=\"col-md-2\" style=\"bottom:0;\"><label>Conferma</label><br/><input class=\"btn btn-default\" type=\"submit\" name=\"submit\" value=\"OK\"></div></form></div>";
            
            echo "<h1>STATISTICHE PER GRUPPI E COLORI (".$_POST['data_partenza']." FINO AL ".$_POST['data_finale'].")</h1>";

            $query_negozi = "SELECT DISTINCT fornitore from elenco_movimenti WHERE causale='Vendita al dettaglio' AND fornitore NOT LIKE '%Venezia%' AND fornitore NOT LIKE 'Black Fashion (Esse Erre)'";
            $negozio = $db_magazzino->query($query_negozi);

            while ($row = $negozio->fetch_assoc()) {

                $query_gruppi = "SELECT SUM(quantita) as quantita, gruppo, colore
FROM elenco_movimenti
WHERE fornitore='" . $row['fornitore'] . "' AND causale='Vendita al dettaglio' AND reso=0
AND (DATA BETWEEN '" . $_POST['data_partenza'] . "' AND '" . $_POST['data_finale'] . "')
GROUP BY gruppo,colore
ORDER BY gruppo ASC;";

                $gruppo = $db_magazzino->query($query_gruppi);
$i++;
            echo "<div class='stampa_$i'>";
                echo "<h2 style='color:white;'>" . $row['fornitore'] . " (".$_POST['data_partenza']." FINO AL ".$_POST['data_finale'].")</h2>";
                echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
                echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Gruppo</td><td>Quantita venduta</td><td>Colore</td><td>Negozio</td></tr>";

                while ($row2 = $gruppo->fetch_assoc()) {
                    echo "<tr><td>" . $row2['gruppo'] . "</td><td>" . $row2['quantita'] . "</td><td>" . $row2['colore'] . "</td><td>" . $row2['fornitore'] . "</td></tr>";
                
                    if(!isset($array[$row2['gruppo']][$row2['colore']]) || $row2['quantita']>$array[$row2['gruppo']][$row2['colore']])
                    { $array[$row2['gruppo']][$row2['colore']]=$row2['quantita']; }
                    
                }
                echo "</table>";
                echo "</div>";
                echo "<br/><br/><button class=\"btn btn-default\" onclick=\"$('div.stampa_$i').printArea();\">STAMPA</button>";
            }


            $query_totale = "SELECT SUM(quantita) as quantita, gruppo, colore
FROM elenco_movimenti
WHERE fornitore NOT LIKE '%Venezia%' AND fornitore NOT LIKE 'Black Fashion (Esse Erre)' AND causale='Vendita al dettaglio' AND reso=0
AND (DATA BETWEEN '" . $_POST['data_partenza'] . "' AND '" . $_POST['data_finale'] . "')
GROUP BY gruppo,colore
ORDER BY gruppo ASC;";

            $totale = $db_magazzino->query($query_totale);

            $i++;
            echo "<div class='stampa_$i'>";
            echo "<h2 style='color:white;'>TOTALE (".$_POST['data_partenza']." FINO AL ".$_POST['data_finale'].")</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Gruppo</td><td>Quantita venduta</td><td>Colore</td><td>Negozio</td></tr>";

            while ($row3 = $totale->fetch_assoc()) {
                echo "<tr><td>" . $row3['gruppo'] . "</td><td>" . $row3['quantita'] . " (".$array[$row3['gruppo']][$row3['colore']].")</td><td>" . $row3['colore'] . "</td><td>" . $row3['fornitore'] . "</td></tr>";
            }
            echo "</table>"; 
            echo "</div>";
            echo "<br/><br/><button class=\"btn btn-default\" onclick=\"$('div.stampa_$i').printArea();\">STAMPA</button>";

            $query_maggiori_venditori = "SELECT fornitore,SUM(quantita) as quantita, gruppo, colore
FROM elenco_movimenti
WHERE fornitore NOT LIKE '%Venezia%' AND fornitore NOT LIKE 'Black Fashion (Esse Erre)' AND causale='Vendita al dettaglio' AND reso=0
AND (DATA BETWEEN '" . $_POST['data_partenza'] . "' AND '" . $_POST['data_finale'] . "')
GROUP BY fornitore,gruppo,colore
ORDER BY quantita DESC
LIMIT 10;";

            $venditore = $db_magazzino->query($query_maggiori_venditori);

            $i++;
            echo "<div class='stampa_$i'>";
            echo "<h2 style='color:white;'>LA TOP TEN DEI PIU FIGHI! ;)</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Gruppo</td><td>Quantita venduta</td><td>Colore</td><td>Negozio</td></tr>";

            while ($row4 = $venditore->fetch_assoc()) {
                echo "<tr><td>" . $row4['gruppo'] . "</td><td>" . $row4['quantita'] . "</td><td>" . $row4['colore'] . "</td><td>" . $row4['fornitore'] . "</td></tr>";
            }
            echo "</table>";
            echo "</div>";
            echo "<br/><br/><button class=\"btn btn-default\" onclick=\"$('div.stampa_$i').printArea();\">STAMPA</button>";
            
            $query_peggiori_venditori = "SELECT fornitore,SUM(quantita) as quantita, gruppo, colore
FROM elenco_movimenti
WHERE fornitore NOT LIKE '%Venezia%' AND fornitore NOT LIKE 'Black Fashion (Esse Erre)' AND causale='Vendita al dettaglio' AND reso=0
AND (DATA BETWEEN '" . $_POST['data_partenza'] . "' AND '" . $_POST['data_finale'] . "')
GROUP BY fornitore,gruppo,colore
ORDER BY quantita ASC
LIMIT 10;";

            $venditore_peggiore = $db_magazzino->query($query_peggiori_venditori);

            $i++;
            echo "<div class='stampa_$i'>";
            echo "<h2 style='color:white;'>LA TOP TEN DEI PIU SCARSI! :)</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Gruppo</td><td>Quantita venduta</td><td>Colore</td><td>Negozio</td></tr>";

            while ($row5 = $venditore_peggiore->fetch_assoc()) {
                echo "<tr><td>" . $row5['gruppo'] . "</td><td>" . $row5['quantita'] . "</td><td>" . $row5['colore'] . "</td><td>" . $row5['fornitore'] . "</td></tr>";
            }
            echo "</table>";
            echo "</div>";
            echo "<br/><br/><button class=\"btn btn-default\" onclick=\"$('div.stampa_$i').printArea();\">STAMPA</button>";
        
            }
        ?>

