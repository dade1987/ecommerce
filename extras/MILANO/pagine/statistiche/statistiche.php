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
        <div class="container-fluid">

            <?php
            list($status, $user) = auth_get_status();

            if ($status == AUTH_LOGGED) {
                menu();
                echo "<h1>STATISTICHE</h1>";

                echo "<form method=\"POST\">
                <div class=\"col-md-4\">
                <label for=\"data\"><b>Data d'inizio</b></label>
		<input type=\"date\" class=\"form-control\" name=\"data\" value=\"" . date('Y-m-d 00:00:00', strtotime('now-1 day')) . "\" >
		</div>
                <div class=\"col-md-4\">
                <label for=\"data2\"><b>Data finale</b></label>
		<input  type=\"date\" class=\"form-control\" name=\"data2\" value=\"" . date('Y-m-d 23:59:00', strtotime('now')) . "\" ></div>";
                #select($_CONFIG['table_utenti']);
                echo "<div class=\"col-md-2\"><label>Conferma</label><br/>
                    <input class=\"btn btn-default\" type=\"submit\" name=\"submit\" value=\"OK\"></form></div>
                    
                    <div style='clear:both;'></div>";

                if (isset($_POST['submit'])) {
                    if (license_has($user, "sede_centrale")) {
                        $query1 = "SELECT * FROM " . $_CONFIG['table_utenti'] . ";";
                    } else {
                        $query1 = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $user['nome_negozio'] . "';"; /* echo $query1; */
                    }

                    $negozio = $db_magazzino->query($query1);

                    for ($i = 0; $neg = $negozio->fetch_assoc(); $i++) {
                        $query2 = "select distinct DATE_FORMAT(data, '%Y-%m-%d')  from elenco_movimenti WHERE (data >= '" . $_POST['data'] . "' AND data <='" . $_POST['data2'] . "') ORDER BY data DESC;";
                        #echo $query1;
                        $data = $db_magazzino->query($query2);
                        echo "<table style=\"background-color:white;width:100%;\">";
                        echo "<tr style=\"background-color:cyan; font-weight:bold;\"><td>Data</td><td>Negozio</td><td>Prezzo al pubblico (con IVA)</td><td>Prezzo dell'affiliato (con IVA)</td><td>Costo per l'azienda</td><td>Pezzi venduti</td></tr>";

                        echo "<p style='color:white;'><strong>" . $neg['nome_negozio'] . "</strong><br/></p>";

                        while ($data_vendite = $data->fetch_assoc()) {

                            $query3 = "SELECT * FROM elenco_movimenti WHERE fornitore LIKE '" . $neg['nome_negozio'] . "' AND data LIKE '" . $data_vendite['DATE_FORMAT(data, \'%Y-%m-%d\')'] . "%' ;";

                            $dato = $db_magazzino->query($query3);

                            #var_dump($dato);
                            $totale_pubblico = 0;
                            $totale_affiliato = 0;
                            $totale_azienda = 0;
                            $pezzi_venduti = 0;
                            #echo "<pre>";

                            while ($dati = $dato->fetch_assoc()) {


                                #var_dump($dati);
                                #var_dump($dati['cliente']);
                                #var_dump($dati['reso']);
                                if ($dati['reso'] === "0") {

                                    $pezzi_venduti+=$dati['quantita'];
                                    $totale_pubblico+=$dati['prezzo_pubblico_unitario'] * $dati['quantita'] / 100 * (100 - $dati['sconto_pubblico']);
                                    $totale_affiliato+=$dati['prezzo_pubblico_unitario'] * $dati['quantita'] / 100 * (100 - $dati['sconto_affiliato']);
                                    if (license_has($user, 'sede_centrale')) {
                                        $totale_azienda+=$dati['costo_aziendale'] * $dati['quantita'];
                                    } else {
                                        $totale_azienda+=0;
                                    }
                                    #echo "+" . $dati['quantita'] . "=" . $pezzi_venduti . "<br/>";
                                } else {
                                    if ($dati['cliente'] == casa_madre) {
                                        #echo "|" . $dati['quantita'] . "=" . $pezzi_venduti . "<br/>";
                                    } else {
                                        $totale_pubblico+=$dati['prezzo_pubblico_unitario'] * $dati['quantita'] / 100 * (100 - $dati['sconto_pubblico']);
                                        $totale_affiliato+=$dati['prezzo_pubblico_unitario'] * $dati['quantita'] / 100 * (100 - $dati['sconto_affiliato']);
                                        if (license_has($user, 'sede_centrale')) {
                                            $totale_azienda+=$dati['costo_aziendale'] * $dati['quantita'];
                                        } else {
                                            $totale_azienda+=0;
                                        }
                                        $pezzi_venduti-=$dati['quantita'];
                                        #echo "-" . $dati['quantita'] . "=" . $pezzi_venduti . "<br/>";
                                    }
                                }
                            }
                            #echo "</pre>";


                            #var_dump($data_vendite);
                            echo "<tr><td>" . $data_vendite['DATE_FORMAT(data, \'%Y-%m-%d\')'] . "</td><td>" . $neg['nome_negozio'] . "</td><td>" . number_format($totale_pubblico, 2) . "</td><td>" . number_format($totale_affiliato, 2) . "</td><td>" . number_format($totale_azienda, 2) . "</td><td>$pezzi_venduti</td></tr>";
                            echo "<tr><td colspan=\"5\">&nbsp;</td></tr>";

                            $prezzo_pubblico[$i]+=$totale_pubblico;
                            $prezzo_affiliato[$i]+=$totale_affiliato;
                            $costo_azienda[$i]+=$totale_azienda;
                            $articoli_venduti[$i]+=$pezzi_venduti;
                        }
                        echo "</table>";
                    }
                    $negozio->data_seek(0);


                    echo "<p style='color:white;'><strong>TOTALI</strong><br></p>";
                    echo "<table style=\"background-color:white;width:100%;\">";

                    echo "<tr style=\"background-color:cyan; font-weight:bold;\"><td>Negozio</td><td>Prezzo al pubblico (con IVA)</td><td>Prezzo dell'affiliato (con IVA)</td><td>Costo per l'azienda</td><td>Pezzi venduti</td></tr>";
                    for ($a = 0; $neg = $negozio->fetch_assoc(); $a++) {
                        echo "<tr><td>" . $neg['nome_negozio'] . "</td><td>" . $prezzo_pubblico[$a] . "</td><td>" . $prezzo_affiliato[$a] . "</td><td>" . $costo_azienda[$a] . "</td><td>" . $articoli_venduti[$a] . "</td></tr>";
                    }
                    echo "</table><br/><br/><br/><br/>";
                }
                ?>
            </div>
            <?php
        } else
            non_autorizzato();
        ?>
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
            var $_Tawk_API = {}, $_Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/559ba55c04c33fb6400d686d/default';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
    </body>
</html>
