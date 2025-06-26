<?php
session_start(); //Inizio la sessione

if (count($_POST) === 0) {
    unset($_SESSION['articolo']);
}

$_POST['barcode'] = substr($_POST['barcode'], 0, 12);


include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();

//echo $status." ".$user; 

if ($status == AUTH_LOGGED && license_has($user, "affiliato")) {
    ?>
    <html>
        <head>
            <!-- Inclusione jQuery -->
            <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
            <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
            <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

            <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

            <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
            <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
            <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

            <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>
            <script>

                $(document).ready(function (event) {

                    $('#barcode').focus();
                    funz_resto();
                    $(document).keyup(function (e)
                    {
                        if (e.keyCode == 17)
                        {

                            $('#barcode').focus();
                        }

                    });
                });


                function stampa_scontrino2() {
                    $('body').children().hide();
                    $('form').show();
                    $('div.menu_cassa').hide();
                    window.print();
                    $('*').show();
                }

                function stampa_fattura() {
                    $('body').children().hide();
                    $('form').show();
                    $('div.menu_cassa').hide();
                    $('.nascosto').hide();
                    window.print();
                    $('*').show();
                }

                function funz_totale() {
                    if ($("input[name='imponibile']").val() != undefined) {
                        var totale = 0;
                        for (i = 0; document.getElementsByName('prezzo_' + i).length != 0; i++) {
                            var saldo = document.getElementsByName('prezzo_' + i);
                            var quantita = document.getElementsByName('quantita_' + i);

                            totale = saldo[0].value * quantita[0].value + totale;
                            imposta = totale / 100 * 22;
                            imponibile = totale - imposta;

                        }
                        document.getElementsByName('totale')[0].value = totale.toFixed(2);
                        document.getElementsByName('imposta')[0].value = imposta.toFixed(2);
                        document.getElementsByName('imponibile')[0].value = imponibile.toFixed(2);
                    }
                }

                function funz_resto() {
                    var saldo = $('[name="saldo"]').val();
                    var totale = $('[name="totale"]').val();
                    var sconto_in_cassa = $('[name="sconto_in_cassa"]').val();
                    var totale_con_sconto = parseFloat(totale) - parseFloat(sconto_in_cassa);

                    var fine = parseFloat(parseFloat(saldo) - parseFloat(totale) + parseFloat(sconto_in_cassa));
                    $('[name="resto"]').val(fine.toFixed(2));
                    $('[name="totale_con_sconto"]').val(totale_con_sconto.toFixed(2));
                }

                function personale(numero) {
                    $.post("imposta_acquisto_personale.php", {"numero_articolo": numero}).done(
                            function () {
                                $('[type="submit"]').click();
                            });
                }

            </script>
        </head>

        <?php
        $a = count($_SESSION['articolo']);
        for ($b = 0; $b < $a; $b++) {
            if (isset($_POST['reso_' . $b])) {

                $_POST['prezzo_' . $b] = "-" . $_POST['prezzo_' . $b];
                $_SESSION['articolo'][$b]['prezzo_pubblico_unitario'] = $_POST['prezzo_' . $b];

                header("reso:si");
            }
        }
        ?>    
        <body onload="funz_totale();
                funz_resto();">

            <?php menu(); ?>

            <div class="container-fluid">
                <form name="form1" method="post">
                    <div class="row clearfix menu_cassa">
                        <div class="col-md-3">
                            <label for="barcode"><b>Codice a barre</b></label><input class="form-control" type="text" name="barcode" id="barcode" placeholder="Premi INVIO prima dello scontrino" size="30">
                        </div>
                        <div class="col-md-3">
                            <label for="fidelity"><b>Codice Fidelity Card</b></label><input class="form-control"  type="text" name="fidelity_card" placeholder="Inserire dopo il primo articolo" size="30">
                        </div> 
                        <div class="col-md-3">
                            <a class="links" href="<?php echo url . "/pagine/marketing/elenco_fidelity_card.php" ?>" target="_blank">Cerca fidelity per cognome</a>
                        </div> 

                        <div class="col-md-12">                    
                            <input class="btn btn-success" type="submit" name="submit" value="Elenca prodotto" id="barcode">
                            <?php
                            $header = apache_response_headers();

                            if (isset($_POST['submit']) || isset($header['reso']))
#echo "<button name=\"stampa\" onClick=\"stampa_scontrino()\">Stampa scontrino fiscale</button>&nbsp;";
                                echo "<button class=\"btn btn-success\" name=\"stampa\"  onClick=\"window.open('../../scontrini/scontrino.php?cognome=" . $_POST['cognome'] . "&nome=" . $_POST['nome'] . "&indirizzo=" . $_POST['indirizzo'] . "&citta=" . $_POST['citta'] . "&cap=" . $_POST['cap'] . "&piva=" . $_POST['piva'] . "&fidelity_card=" . $_POST['fidelity_card'] . "&punti_fidelity_card=" . $_POST['punti_fidelity_card'] . "&piva=" . $_POST['piva'] . "', '_blank').focus();\">Stampa scontrino fiscale</button>&nbsp;";
                            ?>
                            <button class="btn btn-success"  name="registra_scontrino">Registra senza stampare</button> 
                            <button class="btn btn-success"  name="reset">Reset</button> ||
                            <button class="btn btn-success"  name="ristampa">Ristampa ultimo scontrino</button>
                            <button class="btn btn-success"  name="apertura_cassetto" onClick="window.open('../../scontrini/scontrino.php?apertura_cassetto=1', '_blank').focus();">Apertura cassetto</button>
                        </div>
                    </div>


                    <?php
                    if (!empty($_POST['fidelity_card']) || !empty($_SESSION['fidelity_card'])) {
                        if (!empty($_SESSION['fidelity_card']) && empty($_POST['fidelity_card']))
                            $_POST['fidelity_card'] = $_SESSION['fidelity_card'];
                        //Se trova la fidelity card la aggiunge alla sessione
                        $query = "SELECT * FROM db_fidelity_card WHERE numero='" . $_POST['fidelity_card'] . "' OR numero_vecchio='" . $_POST['fidelity_card'] . "' ORDER BY numero DESC LIMIT 1;";

                        $risultato = $db_fidelity_card->query($query);
                        $risultato = $risultato->fetch_assoc();
                        //var_dump($risultato);
                        //echo $query;
                        if (!is_null($risultato)) {
                            $_SESSION['fidelity_card'] = $risultato['numero'];
                            $punti_iniziali = $risultato['punti'];

                            //echo "Fidelity card riconosciuta: ".$_SESSION['fidelity_card'];
                        } else {
                            $punti_iniziali['punti_fidelity_card'] = 0;
                        }
                    }

                    if (isset($_POST['submit']) || isset($header['reso']) || isset($_POST['elimina'])) {

                        #Setta tutte le quantita e prezzi a seconda delle varibili POST inviate
                        for ($i = 0; isset($_POST['quantita_' . $i]); $i++) {
                            $_SESSION['articolo'][$i]['quantita_acquistata'] = $_POST['quantita_' . $i];
                            #$_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] = $_POST['prezzo_' . $i];
                        }

                        if (isset($_POST['elimina'])) {
                            foreach ($_POST['elimina'] as $key => $value) {
                                echo "Eliminato Articolo " . $value;
                                //var_dump($_SESSION['articolo'][$value]);
                                unset($_SESSION['articolo'][$value]);
                                registra_log();

                                sort($_SESSION['articolo']);
                            }
                        } elseif (strlen($_POST['barcode']) == 12) {

                            //var_dump($user['nome_negozio'], $_POST['barcode'], NULL, NULL, NULL);
                            //controlla che l'articolo sia in magazzino
                            $rimanenze = rimanenze($user['nome_negozio'], $_POST['barcode'], NULL, NULL, NULL);

                            //var_dump($rimanenze);
                            //se non ci sono articoli a=0 (contatore)
                            if (!isset($_SESSION['articolo']))
                                $a = 0;

                            //var_dump($rimanenze);                    
                            //se esiste in magazzino
                            if (isset($rimanenze) && $rimanenze[0]['attivo'] === "1") {
                                //controlla che l'articolo non sia già in lista
                                $id_art_uguale = verifica_esistenza_valore($_POST['barcode'], $_SESSION['articolo']);
                                //echo "<p>ID UGUALE ".$id_art_uguale."</p>";    


                                if ($id_art_uguale === false) {
                                    //controlla che la sessione articolo sia già stata inizializzata, e se lo è già la aumenta di valore
                                    $a = count($_SESSION['articolo']);
                                    $query = "SELECT * FROM elenco_movimenti WHERE barcode='" . $rimanenze[0]['barcode'] . "' AND reso='0' AND sconto_saldo='0' ORDER BY id DESC LIMIT 1;";
                                    //echo $query;
                                    $_SESSION['articolo'][$a] = $db_magazzino->query($query)->fetch_assoc();
                                    $_SESSION['articolo'][$a]['quantita_acquistata'] = 1;
                                    //var_dump($_SESSION['articolo']);
                                } else {
                                    //echo "XXX";


                                    $_SESSION['articolo'][$id_art_uguale]['quantita_acquistata'] ++;
                                }
                            } else
                                echo "<br><br>Articolo non disponibile in magazzino.";
                        }


                        //memorizzo tutta la sessione articolo in una variabile più semplice da richiamare
                        $articolo = $_SESSION['articolo'];

                        //definisco l'area di stampa
                        echo "<div id=\"codice\">";

                        echo "<div style=\"clear:both; width:100%; height:1px;\"></div>";

                        //creo la tabella prodotti

                        echo "<br><br>Modificare le quantita alla fine<br><table class='table table-responsive'>";
                        echo "<tr id=\"intestazione\">";
                        echo "<td>Descrizione</td>";
                        echo "<td>Codice</td>";
                        echo "<td>Gruppo</td>";
                        echo "<td>Colore</td>";
                        echo "<td>Quantita</td>";
                        echo "<td>Prezzo</td>";
                        echo "<td>Giacenza</td>";


                        echo "</tr>";

                        //conta il numero di articoli presenti nella lista
                        $a = count($_SESSION['articolo']);
                        //per ogni array crea una riga nella tabella
                        for ($b = 0; $b < $a; $b++) {


                            //var_dump($articolo[$b]);
                            //calcolo del prezzo
                            $prezzo_iva_sconti_inclusi[$b] = number_format(sconto($articolo[$b]['prezzo_pubblico_unitario'], $articolo[$b]['sconto_pubblico']), 2);

                            //calcolo della quantita
                            //var_dump($_SESSION['articolo'][$b]);

                            $quantita = $_SESSION['articolo'][$b]['quantita_acquistata'];
//echo $_SESSION['articolo'][$i]['quantita_acquistata'];
                            echo "<tr id=\"bianco\"><td><input class='form-control col-md-2' type=\"text\" name=\"descrizione_" . $b . "\" value=\"" . $articolo[$b]['descrizione'] . "\" readonly></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" name=\"codice_" . $b . "\" value=\"" . $articolo[$b]['codice'] . "\" readonly></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" name=\"gruppo_" . $b . "\" value=\"" . $articolo[$b]['gruppo'] . "\" readonly></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" name=\"colore_" . $b . "\" value=\"" . $articolo[$b]['colore'] . "\" readonly></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" class=\"quantita_prodotto\" name=\"quantita_" . $b . "\" value=\"" . $quantita . "\" onchange=\"funz_totale()\"></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" name=\"prezzo_" . $b . "\" value=\"" . $prezzo_iva_sconti_inclusi[$b] . "\" readonly></td>";
                            echo "<td><input class='form-control col-md-2'  type=\"text\" name=\"giacenza_" . $b . "\" value=\"" . $articolo[$b]['quantita'] . "\" readonly></td>";


                            echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"reso_" . $b . "\" value=\"" . $b . "\">Reso</td>";
                            echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"elimina[]\" value=\"" . $b . "\">X</td>";

                            if ($_SESSION['articolo'][$b]["acquisto_personale"] === 1) {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1 btn-danger' name=\"acquisto_personale\" onclick='event.preventDefault();'>Pers.";
                            } else {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"acquisto_personale\" onclick='event.preventDefault();personale(" . $b . ");'>Personale";
                            }

                            $rz_saldi = $db_magazzino->query("SELECT * FROM utenti3 WHERE nome_negozio='" . $user['nome_negozio'] . "' LIMIT 1;");
                            $row_saldi = $rz_saldi->fetch_assoc();

                            if (!empty($row_saldi["qt"])) {
                                $pezzi_massimi_saldo = $row_saldi["qt"];
                            }

                            if ($row_saldi["20_perc"] === "on") {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"saldo_articolo\" onclick='event.preventDefault();saldo_art(" . $b . ",20);'>20%";
                            }
                            if ($row_saldi["50_perc"] === "on") {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"saldo_articolo\" onclick='event.preventDefault();saldo_art(" . $b . ",50);'>50%";
                            }
                            if ($row_saldi["5_euro"] === "on") {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"saldo_euro[]\" onclick='event.preventDefault();saldo_euro(" . $b . ",5);'>5E";
                            }
                            if ($row_saldi["2_euro"] === "on") {
                                echo "<td class=\"nascosto\" ><button class='form-control col-md-1' name=\"saldo_euro[]\" onclick='event.preventDefault();saldo_euro(" . $b . ",2);'>2E";
                            }

                            /* if ($user['nome_negozio'] === "BLACK FASHION PORTOFERRAIO") {
                              echo "<button name=\"saldo_articolo\" onclick='event.preventDefault();saldo_art(" . $b . ",20);'>-20%";
                              echo "<button name=\"saldo_articolo\" onclick='event.preventDefault();saldo_art(" . $b . ",10);'>-10%";
                              } */
                            echo "</td></tr>";
                        }

                        $contatore = "SELECT SUM(quantita) as contatore FROM elenco_movimenti WHERE fornitore='" . $user['nome_negozio'] . "' AND sconto_saldo='1';";
                        $contatore = $db_magazzino->query($contatore);
                        $contatore = $contatore->fetch_assoc();
                        $contatore = $contatore['contatore']; 

                        if ($contatore < $pezzi_massimi_saldo) {
                            echo "<h4>Articoli in saldo rimanenti: " . ($pezzi_massimi_saldo - $contatore) . "</h4>";
                        }
                        //finisco la creazione della tabella dei prodotti
                        echo "</table>";

                        $enumeratore = numerazione();
                        $identificativo = $enumeratore[0];
                        $numerazione = $enumeratore[1];

                        for ($i = 0; $i < $a; $i++) {
                            $quantita_totale = $quantita_totale + $_SESSION['articolo'][$i]['quantita_acquistata'];
                        }

                        if (isset($_POST['sconto_in_cassa'])) {
                            $_SESSION['sconto_in_cassa'] = $_POST['sconto_in_cassa'];
                        }

                        if (!empty($_SESSION['sconto_in_cassa'])) {
                            $sconto_in_cassa = $_SESSION['sconto_in_cassa'];
                        } else {
                            $sconto_in_cassa = 0;
                        }



                        //tabella dei dati finali
                        echo "<br><br><table>
		<tr id=\"intestazione\">
                <td>Imponibile</td><td>Imposta</td><td><strong>Totale</strong></td><td class=\"nascosto\" >Totale con sconto</td><td class=\"nascosto\" >Saldo</td><td class=\"nascosto\" >Resto</td></tr>
		<tr id=\"intestazione\">
                <td><input class='form-control col-md-1' type=\"text\" name=\"imponibile\" readonly value=\"" . ($_POST['totale'] / 100 * (100 - IVA)) . "\"></td>
		<td><input class='form-control col-md-1' type=\"text\" name=\"imposta\" readonly value=\"" . ($_POST['totale'] / 100 * IVA) . "\"></td>
                <td><input class='form-control col-md-1' type=\"text\" style='background-color:#F5CCCC; font-weight:bold;' name=\"totale\" readonly value=\"" . $_POST['totale'] . "\"></td>
                <td class=\"nascosto\" ><input class='form-control col-md-1' style='background-color:lightgreen;' type=\"text\" name=\"totale_con_sconto\" value=\"$totale_con_sconto\" onchange=\"funz_resto();\"></td>									
		<td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"saldo\" value=\"" . $_POST['saldo'] . "\" onchange=\"funz_resto();\"></td>
		<td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"resto\" value=\"" . $_POST['resto'] . "\" onclick=\"funz_resto();\"></td>	
                </tr>
                
                <tr id=\"intestazione\">
                <td class=\"nascosto\">Numerazione scontrino</td><td></td><td>Articoli totali</td><td class=\"nascosto\" >Fidelity Card</td><td class=\"nascosto\" >Punti Fidelity Card</td><td class=\"nascosto\" >Sconto in cassa (&euro;)</td>
		</tr>
                <tr id=\"intestazione\">
                <td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"numerazione\" value=\"" . $numerazione . "\" readonly></td>
		<td></td>
                <td><input type=\"text\" class='form-control col-md-1' name=\"articoli_totali\" value=\"" . $quantita_totale . "\" readonly></td>";
                        $_SESSION['punti_fidelity_card'] = floor($punti_iniziali + ($_POST['totale'] / 10));
                        echo "<td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"fidelity_card_1\" value=\"" . $_SESSION['fidelity_card'] . "\" readonly></td>					
		<td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"punti_fidelity_card\" value=\"" . $_SESSION['punti_fidelity_card'] . "\" readonly></td>	
                <td class=\"nascosto\" ><input class='form-control col-md-1' type=\"text\" name=\"sconto_in_cassa\" value=\"$sconto_in_cassa\" onchange=\"funz_resto();\"></td>									
                </tr>
                </table>";
                        //var_dump($_SESSION['punti_fidelity_card']);
                        //div dell'area di stampa	
                        echo "</div>";
                    } else if (isset($_POST['stampa']) || isset($_POST['stampa_fattura_']) || isset($_POST['registra_scontrino'])) {

                        $enumeratore = numerazione();
                        $identificativo = $enumeratore[0];
                        $numerazione = $enumeratore[1];

                        //aggiungi i suffissi per fatture e scontrini
                        if (strlen($_POST['nome']) > 3)
                            $identificativo = $identificativo . "F";
                        else
                            $identificativo = $numerazione . "S";

                        //echo "<p>".$identificativo."</p>";
                        //conta il numero di articoli presenti nella lista
                        $a = count($_SESSION['articolo']);

                        //var_dump($_SESSION['articolo']);
                        //e successivamente aggiungi al db dell'elenco movimenti
                        for ($b = 0; $b < $a; $b++) {
                            if (substr($_POST['prezzo_' . $b], 0, 1) == "-") {
                                $query = "INSERT INTO elenco_movimenti (escluso_fattura,costo_aziendale,reso, data, causale, codice,sconto_affiliato, sconto_pubblico, 
					quantita,prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,
					fornitore,cliente,pagamento,totale,saldo,resto,identificativo,cognome,nome,indirizzo,citta,cap,piva) VALUES 
					('" . $_SESSION['articolo'][$b]['escluso_fattura'] . "','" . $_SESSION['articolo'][$b]['costo_aziendale'] . "','1','" . date('Y-m-d H:i:s', strtotime('now')) . "','RESO','" . $_SESSION['articolo'][$b]['codice'] . "',
					" . $_SESSION['articolo'][$b]['sconto_affiliato'] . "," . $_SESSION['articolo'][$b]['sconto_pubblico'] . ",'"
                                        . $_POST['quantita_' . $b] . "','" . $_SESSION['articolo'][$b]['prezzo_pubblico_unitario'] . "','"
                                        . $_SESSION['articolo'][$b]['descrizione'] . "','" . $_SESSION['articolo'][$b]['gruppo'] . "','"
                                        . $_SESSION['articolo'][$b]['colore'] . "','" . $_SESSION['articolo'][$b]['barcode'] . "','" . $user['nome_negozio'] . "',
					'Vendita al dettaglio','" . $_POST['pagamento'] . "','" . $_POST['totale'] . "','" . $_POST['saldo'] . "',
					'" . $_POST['resto'] . "','" . $identificativo . "','" . $_POST['cognome'] . "','" . $_POST['nome'] . "'
					,'" . $_POST['indirizzo'] . "','" . $_POST['citta'] . "','" . $_POST['cap'] . "','" . $_POST['piva'] . "')";
                                if (isset($_POST['fidelity_card_1']) && !empty($_POST['fidelity_card_1'])) {
                                    $query2 = "UPDATE db_fidelity_card SET punti='" . $_POST['punti_fidelity_card'] . "' WHERE numero='" . $_POST['fidelity_card_1'] . "'";
                                }
                            } else {
                                $query = "INSERT INTO elenco_movimenti (escluso_fattura,acquisto_personale,costo_aziendale,sconto_saldo,data, causale, codice,sconto_affiliato, sconto_pubblico, 
					quantita,prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,
					fornitore,cliente,pagamento,totale,saldo,resto,identificativo,cognome,nome,indirizzo,citta,cap,piva) VALUES 
					('" . $_SESSION['articolo'][$b]['escluso_fattura'] . "','" . $_SESSION['articolo'][$b]['acquisto_personale'] . "','" . $_SESSION['articolo'][$b]['costo_aziendale'] . "','" . $_SESSION['articolo'][$b]['sconto_saldo'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','Vendita al dettaglio','" . $_SESSION['articolo'][$b]['codice'] . "',
					" . $_SESSION['articolo'][$b]['sconto_affiliato'] . "," . $_SESSION['articolo'][$b]['sconto_pubblico'] . ",'"
                                        . $_POST['quantita_' . $b] . "','" . $_SESSION['articolo'][$b]['prezzo_pubblico_unitario'] . "','"
                                        . $_SESSION['articolo'][$b]['descrizione'] . "','" . $_SESSION['articolo'][$b]['gruppo'] . "','"
                                        . $_SESSION['articolo'][$b]['colore'] . "','" . $_SESSION['articolo'][$b]['barcode'] . "','" . $user['nome_negozio'] . "',
					'Vendita al dettaglio','" . $_POST['pagamento'] . "','" . $_POST['totale'] . "','" . $_POST['saldo'] . "',
					'" . $_POST['resto'] . "','" . $identificativo . "','" . $_POST['cognome'] . "','" . $_POST['nome'] . "'
					,'" . $_POST['indirizzo'] . "','" . $_POST['citta'] . "','" . $_POST['cap'] . "','" . $_POST['piva'] . "')";
                                if (isset($_POST['fidelity_card_1']) && !empty($_POST['fidelity_card_1'])) {
                                    $query2 = "UPDATE db_fidelity_card SET punti='" . $_POST['punti_fidelity_card'] . "' WHERE numero='" . $_POST['fidelity_card_1'] . "'";
                                    $db_fidelity_card->query($query2);
                                }
                            }
                            //echo $query;
                            $db_magazzino->query($query);
                        }
                        //messaggio di conferma
                        echo "<br><br>Dati caricati correttamente nel database aziendale.";

                        sleep(3);
                        registra_log();
                        session_destroy();
                    } else if (isset($_POST['reset'])) {
                        registra_log();
                        session_destroy();
                    } else if (isset($_POST['chiusura_fiscale'])) {

                        $query = "SELECT *
			 FROM elenco_movimenti WHERE (causale='Vendita al dettaglio' OR causale LIKE 'Reso') AND fornitore='" . $user['nome_negozio'] . "'";
                        #echo $query;
                        $risultato = $db_magazzino->query($query);
                        $gran_totale = 0;
                        while ($riga = $risultato->fetch_assoc()) {
                            if ($riga['reso'] == 1)
                                $gran_totale = $gran_totale - ($riga['totale'] * $riga['quantita']);
                            else
                                $gran_totale = $gran_totale + ($riga['totale'] * $riga['quantita']);
                        }

                        $prezzo_totale = 0;
                        $query.="AND data>='" . date('Y-m-d 00:00:00', strtotime('now')) . "'";
                        $risultato = $db_magazzino->query($query);

                        while ($riga = $risultato->fetch_assoc()) {
                            if ($riga['reso'] == 1)
                                $prezzo_totale = $prezzo_totale - ($riga['totale'] * $riga['quantita']);
                            else
                                $prezzo_totale = $prezzo_totale + ($riga['totale'] * $riga['quantita']);
                        }

                        $query = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_sede_legale='" . $user['nome_negozio'] . "'";
                        $risultato = $conn->query($query);
                        $riga = $risultato->fetch_assoc();

                        echo "Chiusura fiscale memorizzata correttamente nel database.<br>Si consiglia di stamparla.<br>";
                        echo "<div id=\"codice\">";
                        $_SESSION['dati_chiusura'] = "<br><br>" . $riga['nome_sede_legale'];
                        $_SESSION['dati_chiusura'].= "<br>" . $riga['indirizzo_sede_legale'];
                        $_SESSION['dati_chiusura'].= "<br>P.IVA " . $riga['partita_iva_sede_legale'];
                        $_SESSION['dati_chiusura'].= "<br><br>Data chiusura: " . date('Y-m-d H:m:s', strtotime('now'));
                        $_SESSION['dati_chiusura'].= "<br>Gran Totale: " . number_format($gran_totale, 2) . " &euro;";
                        $_SESSION['dati_chiusura'].= "<br>Totale: " . number_format($prezzo_totale, 2) . " &euro;";
                        echo $_SESSION['dati_chiusura'];
                        echo "</div>";
                        echo "<br><a onclick=\"stampa_scontrino2()\">Stampa scontrino</a><br>";

                        //db_scontrini
                        $db_fatture->query("INSERT INTO `db_scontrini`(`negozio`, `dati_chiusura`) VALUES ('" . $user['nome_negozio'] . "','" . $_SESSION['dati_chiusura'] . "')");
                        registra_log();
                        session_destroy();
                    }
                    else if (isset($_POST['chiusure_precedenti'])) {
                        //db_scontrini
                        $chiusure = $db_fatture->query("SELECT * FROM db_scontrini WHERE negozio='" . $user['nome_negozio'] . "' ORDER BY id DESC;");
                        while ($dati = $chiusure->fetch_assoc()) {
                            echo $dati['dati_chiusura'];
                            echo "<br><br>";
                        }
                    } else if (isset($_POST['ristampa'])) {
                        echo "<br><br>Scontrino non fiscale (valido solo per garanzia).";

                        //creazione tabella emittente
                        $query = "SELECT nome_sede_legale, indirizzo_sede_legale, partita_iva_sede_legale FROM " . $_CONFIG['table_utenti'] . " WHERE nome_sede_legale='" . $user['nome_negozio'] . "'";
                        $emittente = $conn->query($query);
                        $riga = $emittente->fetch_assoc();

                        echo "<div id=\"codice\"><br><br><table>
		<tr id=\"intestazione\"><td colspan=\"3\">Emittente</td></tr>
		<tr id=\"bianco\"><td>" . $riga['nome_sede_legale'] . "</td></tr>
		<tr id=\"azzurrino\"><td>" . $riga['indirizzo_sede_legale'] . "</td></tr>
		<tr id=\"bianco\"><td>P.IVA " . $riga['partita_iva_sede_legale'] . "</td></tr></table>";

                        $scontrino = $db_magazzino->query("SELECT * FROM elenco_movimenti WHERE fornitore='" . $user['nome_negozio'] . "' 
					AND cliente='Vendita al dettaglio' ORDER BY data DESC LIMIT 1;");
                        $scontrino = $scontrino->fetch_assoc();

                        echo "<br><table width=\"90%\">";
                        echo "<tr id=\"intestazione\"><td>Prodotti</td><td>Prezzo</td></tr>";
                        echo "<tr id=\"bianco\"><td>Articolo</td><td>" . $scontrino['totale'] . "</td></tr>";
                        echo "<tr><td colspan=\"3\"></td></tr>";
                        echo "<tr id=\"azzurrino\"><td>Totale</td><td>Saldo</td><td>Resto</td></tr>";
                        echo "<tr id=\"bianco\"><td>" . $scontrino['totale'] . "</td><td>" . $scontrino['saldo'] . "</td><td>" . $scontrino['resto'] . "</td></tr>";
                        echo "</table></div>";
                        echo "<br><br><a onclick=\"stampa_scontrino2()\">Stampa scontrino</a><br>";
                    } else if (isset($_POST['cancella'])) {
                        $db_magazzino->query("DELETE FROM elenco_movimenti WHERE fornitore='" . $user['nome_negozio'] . "' AND identificativo='" . $_POST['barcode'] . "'
					 ORDER BY data DESC;");
                        echo "<br><br>Scontrino " . $_POST['barcode'] . " eliminato dal database.";
                    }
                } else
                    non_autorizzato();

                function numerazione() {
                    global $db_fatture, $db_magazzino, $user;

                    //echo "<p>".$user['nome_negozio']."</p>";	
                    //preleva numerazione fattura
                    $query = "SELECT * FROM elenco_movimenti WHERE fornitore='" . $user['nome_negozio'] . "' AND cliente='Vendita al dettaglio' AND identificativo LIKE '%F' ORDER BY id DESC LIMIT 1";
                    //echo $query;
                    $query_numerazione = $db_magazzino->query($query);
                    $ultimo_numero = $query_numerazione->fetch_assoc();
                    $numerazione = substr($ultimo_numero['identificativo'], 0, strlen($ultimo_numero['identificativo']) - 1);
                    $numerazione = $numerazione + 1;

                    //preleva identificativo scontrino
                    $query = "SELECT * FROM elenco_movimenti WHERE fornitore='" . $user['nome_negozio'] . "' AND cliente='Vendita al dettaglio' AND identificativo LIKE '%S' ORDER BY id DESC LIMIT 1";
                    //echo $query;
                    $query_identificativo = $db_magazzino->query($query);
                    $ultimo_identificativo = $query_identificativo->fetch_assoc();
                    //echo $ultimo_identificativo['identificativo'];
                    $identificativo = substr($ultimo_identificativo['identificativo'], 0, strlen($ultimo_identificativo['identificativo']) - 1);
                    $identificativo = $identificativo + 1;
                    //echo $identificativo;
                    $array[0] = $numerazione;
                    $array[1] = $identificativo;
                    "- I: " . $identificativo;

                    return $array;
                }

#var_dump($_SESSION['articolo']);
                ?>

                <script>



                    function saldo_art(numero, percent, event) {
                        $.post("imposta_sessione_saldi.php", {"art_saldo": numero, "percent": percent}).done(
                                function () {
                                    $('[type="submit"]').click();
                                });
                    }

                    function saldo_euro(numero, importo, event) {
                        $.post("imposta_sessione_saldi.php", {"art_saldo": numero, "importo": importo}).done(
                                function () {
                                    $('[type="submit"]').click();
                                });
                    }
                </script>


            </form>
        </div>
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

        <script>
            console.log('session sconto: ' +<?php echo json_encode($_SESSION['sconto_in_cassa']); ?>);
            console.log('post sconto ' +<?php echo json_encode($_POST['sconto_in_cassa']); ?>);
        </script>

    </body>
</html>

