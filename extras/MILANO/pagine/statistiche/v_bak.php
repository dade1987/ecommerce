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
        <link rel="stylesheet" type="text/css" href="../../css/stile.css">
        <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

    </head>
    <body>

        <?php
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
            menu();
            
            echo "<form method=\"POST\">
                <label for=\"data_partenza\"><b>Data d'inizio</b></label>
		<input type=\"date\" name=\"data_partenza\" >
		<label for=\"data_finale\"><b>Data finale</b></label>
		<input  type=\"date\" name=\"data_finale\" >";
            #select($_CONFIG['table_utenti']);
            echo "<input type=\"submit\" name=\"submit\" value=\"OK\"></form>";
            
            echo "<div class='stampa'><h1>STATISTICHE PER GRUPPI E COLORI</h1>";
            
            $query = "SELECT COUNT(*) as quantita_venduta,gruppo,colore,cliente FROM elenco_movimenti
WHERE causale='Vendita ad affiliato' AND reso=0
AND (DATA BETWEEN '".$_POST['data_partenza']."' AND '".$_POST['data_finale']."')
GROUP BY gruppo,colore
ORDER BY cliente,gruppo;";

            $r1 = $db_magazzino->query($query);
            
            echo "<h2>- GRUPPI E COLORI PER NEGOZIO</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Quantita venduta</td><td>Gruppo</td><td>Colore</td><td>Negozio</td></tr>";

            while($row=$r1->fetch_assoc())
            {
                echo "<tr><td>".$row['quantita_venduta']."</td><td>".$row['gruppo']."</td><td>".$row['colore']."</td><td>".$row['cliente']."</td></tr>";
            }
            echo "</table>";
            
            $totale_negozio = "SELECT COUNT( * ) AS quantita_venduta, cliente
FROM elenco_movimenti
WHERE causale =  'Vendita ad affiliato'
AND reso =0
AND (
DATA
BETWEEN  '".$_POST['data_partenza']."'
AND  '".$_POST['data_finale']."'
)
GROUP BY cliente";

            $r2 = $db_magazzino->query($totale_negozio);
            
            echo "<h2>- TOTALE PER NEGOZIO</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Quantita venduta</td><td>Negozio</td></tr>";

            while($row=$r2->fetch_assoc())
            {
                echo "<tr><td>".$row['quantita_venduta']."</td><td>".$row['cliente']."</td></tr>";                
            }
            echo "</table>";

            $tot_senza_cliente = "SELECT COUNT(*) as quantita_venduta,gruppo,colore FROM elenco_movimenti
WHERE causale='Vendita ad affiliato' AND reso=0
AND (DATA BETWEEN '".$_POST['data_partenza']."' AND '".$_POST['data_finale']."')
GROUP BY gruppo,colore
ORDER BY gruppo;";

            $r3 = $db_magazzino->query($tot_senza_cliente);
            
            echo "<h2>- GRUPPI E COLORI TOTALE</h2>";
            echo "<table style='background-color:white;border:2px solid grey;width:100%;'>";
            echo "<tr style='background-color:lightgreen;font-weight:bold;'><td>Quantita venduta</td><td>Gruppo</td><td>Colore</td></tr>";
            
            while($row=$r3->fetch_assoc())
            {
                echo "<tr><td>".$row['quantita_venduta']."</td><td>".$row['gruppo']."</td><td>".$row['colore']."</td></tr>";
            }
            echo "</table></div>";

        }
        ?>

        <br/><br/><button onclick="$('div.stampa').printArea();">STAMPA</button>