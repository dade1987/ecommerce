<?php

include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();
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
        <script>
            function print() {
                $("div.page-break").printArea();
            }
            ;

            function sconto() {
                var sel = document.getElementsByName('gruppi')[0];
                var opt = sel.options[sel.selectedIndex];
                var n = opt.value.length;
                var sconto = opt.value.substring(n - 2, n);
                var s = document.getElementsByName('sconto_azienda')[0].value = sconto;
            }
            ;

            $(document).ready(function (e) {
                $('select[name="gruppi"]').change(function () {
                    $('input[name="nome"]').val($('select[name="gruppi"] option:selected').text());
                })

                $('input').change(function (e) {
                    var IVA = 22;
                    var prezzo_con_iva = $('input[name="prezzo_unitario_ivato"]').val();
                    var sconto_pubblico = $('input[name="sconto_pubblico"]').val();
                    var sconto_azienda = $('input[name="sconto_azienda"]').val();
                    var quantita = $('input[name="quantita"]').val();

                    $('input[name="prezzo_pubblico_unitario"]').val((prezzo_con_iva / 100 * (100 - IVA)).toFixed(2));
                    $('input[name="calcola_prezzo_quantita"]').val((prezzo_con_iva * quantita).toFixed(2));
                    $('input[name="calcola_sconto_pubblico"]').val((prezzo_con_iva / 100 * (100 - sconto_pubblico)).toFixed(2));
                    $('input[name="calcola_sconto_azienda"]').val((prezzo_con_iva / 100 * (100 - sconto_azienda)).toFixed(2));
                });
            });
        </script>



    </head>
    <body>
        <div class="container-fluid"> 
            <?php
            if ($user['nome_negozio'] === "BLACK FASHION PORTOFERRAIO") {
                menu();
                echo "<h1>INSERIMENTO ARTICOLO PERSONALE DI PORTOFERRAIO</h1>";


                if ($_POST['submit']) {
                    $data = date('Y-m-d H:i:s', strtotime('now'));
                    $causale = "Vendita ad affiliato";
                    $codice = date('m', strtotime('now'));
                    $codice.=substr(date('Y', strtotime('now')), 2);
                    $codice.=$_POST['codice_fornitore'];

                    $codice.=$_POST['colori'][0] . $_POST['colori'][2] . $_POST['colori'][4];

                    $risultato = $db_magazzino->query("SELECT * FROM elenco_movimenti ORDER BY id DESC LIMIT 1;");

                    $progressivo = $risultato->fetch_assoc();
                    $codice.=sprintf('%04d', $progressivo['id'] + 1);

                    $id = $db_magazzino->query("SHOW TABLE STATUS LIKE 'elenco_movimenti'")->fetch_assoc();
                    $id = $id['Auto_increment'];

                    $risultato->close();

                    if (strlen($_POST['quantita']) >= 1 && strlen($_POST['nome']) >= 3 && strlen($_POST['gruppi']) && strlen($_POST['colori']) >= 3 && strlen($_POST['sconto_azienda']) >= 1) {
                        $_POST['gruppi'] = substr($_POST['gruppi'], 0, -2);
                    }

                    $_POST['prezzo_unitario'] = $_POST['calcola_sconto_pubblico'];

                    $query = "INSERT INTO elenco_movimenti (escluso_fattura,materiale,attivo,codice_fornitore,costo_aziendale,data, causale, codice,sconto_affiliato, sconto_pubblico, quantita, prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,fornitore,cliente) VALUES (1,'" . $_POST['materiale'] . "','1','ME','" . $_POST['costo_aziendale'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','PROPRIO ARTICOLO','" . $codice . "','0','0','" . $_POST['quantita'] . "','" . $_POST['prezzo_pubblico_unitario'] . "','" . $_POST['nome'] . "','". $_POST['categoria_merceologica'] ."','" . $_POST['colori'] . "','" . $_POST['barcode'] . "','".$user['nome_negozio']."','" . $user['nome_negozio'] . "')";

                    $risultato = $db_magazzino->query($query);

                    if ($risultato != FALSE) {
                        echo "ARTICOLO MEMORIZZATO!";
                    } else
                        echo "ARTICOLO NON MEMORIZZATO!";
                }

                    //$risultato->close();
                
                    //echo '<br><br>'.$query;
                ?>

                <div class="form-group">
                    <form name="form1" method="post" action="./articoli_portoferraio.php">

                        <table>

                            <tr><td><label for="barcode">Codice a barre:</label></td><td><input class="form-control"  name="barcode" type="text" size="30"></td></tr>

                            <tr><td>

                                    <label for="nome">
                                        Descrizione:</label></td><td>
                                    <input class="form-control"  name="nome" type="text" size="50"></td></tr>


                            <tr><td><label for="categoria_merceologica">Categoria merceologica:</label></td><td>
                                    <select class="form-control" name="categoria_merceologica">
                                        <option></option>
                                        <option>A</option>
                                        <option>B</option>
                                        <option>C</option>
                                        <option>D</option>
                                        <option>E</option>
                                        <option>F</option>
                                        <option>G</option>
                                        <option>H</option>
                                        <option>I</option>
                                        <option>L</option>
                                        <option>M</option>
                                        <option>N</option>
                                        <option>O</option>
                                        <option>P</option>
                                        <option>Q</option>
                                        <option>R</option>
                                        <option>S</option>
                                        <option>T</option>
                                        <option>U</option>
                                        <option>V</option>
                                        <option>Z</option>
                                    </select>
                                </td></tr>

                            
                            <tr><td><label for="colori">Colore:</label></td><td><?php select("colori"); ?></td></tr>

                            <tr><td>
                                    <label for="quantita">Quantit&agrave;:</label></td><td>
                                    <input class="form-control"  name="quantita" type="text" size="50" style="background-color: lightyellow;"></td></tr>
                            <tr><td>

                                    <label for="prezzo_unitario_ivato">Prezzo pubblico unitario (IVA inclusa- Sconti esclusi):</label></td><td>
                                    <input class="form-control"  name="prezzo_unitario_ivato" type="text" size="30" style="background-color: lightyellow;"></td></tr>

                            <tr><td> <label for="calcola_prezzo_quantita">Prezzo x quantita:</label></td><td><input class="form-control"  name="calcola_prezzo_quantita" type="text" size="30"></td></tr>

                            <tr><td><label for="prezzo_pubblico_unitario">Prezzo unitario IVA esclusa:</label></td><td><input class="form-control"  name="prezzo_pubblico_unitario" type="text" size="30"></td></tr>

                            <tr><td>  
                                    <label for="costo_aziendale">Costo dell'azienda fornitrice:</label></td><td>
                                    <input class="form-control"  name="costo_aziendale" type="text" size="50" style="background-color: lightyellow;"></td></tr>

                            

                            <input type="hidden" value="" name="Hidden_Gruppo" />

                            <td colspan="2"><input class="btn btn-success" type="submit" name="submit" value="OK"></td></tr></table>
                    </form>
                </div>
            </div>
    </html>
    <?php
} else
    non_autorizzato();
?>
