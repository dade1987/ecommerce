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
                })
            });
        </script>



    </head>
    <body>
        <div class="container-fluid"> 
            <?php
            if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
                menu();
                echo "<h1>INSERIMENTO ARTICOLO IN MAGAZZINO</h1>";


                if ($_POST['submit']) {
                    $data = date('Y-m-d H:i:s', strtotime('now'));
                    $causale = "Carico da articoli";
                    $codice = date('m', strtotime('now'));
                    $codice.=substr(date('Y', strtotime('now')), 2);
                    $codice.=$_POST['codice_fornitore'];

                    $codice.=$_POST['colori'][0] . $_POST['colori'][2] . $_POST['colori'][4];

                    $risultato = $db_magazzino->query("SELECT * FROM elenco_movimenti ORDER BY id DESC LIMIT 1;");

                    $progressivo = $risultato->fetch_assoc();
                    $codice.=sprintf('%04d', $progressivo['id'] + 1);

                    $id = $db_magazzino->query("SHOW TABLE STATUS LIKE 'elenco_movimenti'")->fetch_assoc();
                    $id = $id['Auto_increment'];

                    $barcode = date("Y");
                    $barcode .= sprintf("%'.08d", $id);

                    while ($risultato2 = $db_magazzino->query("SELECT * FROM elenco_movimenti WHERE barcode LIKE '" . $barcode . "%' LIMIT 1;")) {

                        $row = $risultato2->fetch_row();

                        //se il barcode corrisponde
                        if ($barcode == $row[4]) {

                            $id++;
                            
                            //ne crea un altro
                            $barcode = date("Y");
                            $barcode .= sprintf("%'.08d", $id);
                        } else {
                            //dovrebbe in teoria fermare il ciclo
                            break;
                        }
                    }

                    $risultato->close();
                    if (strlen($_POST['quantita']) >= 1 && strlen($_POST['nome']) >= 3 && strlen($_POST['gruppi']) && strlen($_POST['colori']) >= 3 && strlen($_POST['sconto_azienda']) >= 1)
                        $_POST['gruppi'] = substr($_POST['gruppi'], 0, -2);
                    $_POST['prezzo_unitario'] = $_POST['calcola_sconto_pubblico'];

                    $query = "INSERT INTO elenco_movimenti (materiale,attivo,codice_fornitore,costo_aziendale,data, causale, codice,sconto_affiliato, sconto_pubblico, quantita, prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,fornitore,cliente) VALUES ('" . $_POST['materiale'] . "','" . $_POST['attivo'] . "','" . $_POST['codice_fornitore'] . "','" . $_POST['costo_aziendale'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','Carico da articoli','" . $codice . "','" . $_POST['sconto_azienda'] . "','" . $_POST['sconto_pubblico'] . "','" . $_POST['quantita'] . "','" . $_POST['prezzo_unitario'] . "','" . $_POST['nome'] . "','" . $_POST['Hidden_Gruppo'] . "','" . $_POST['colori'] . "','" . $barcode . "','Articoli','" . $user['nome_negozio'] . "')";
//echo $query;

                    $risultato = $db_magazzino->query($query);

                    if ($risultato != FALSE) {
                        echo "ARTICOLO MEMORIZZATO!
	<div class=\"page-break\" style=\"padding-top:1.5mm;padding-left:3mm;width:36mm;clear:both; \">
<div class=\"testo\" style=\"	font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
 font-weight:bold; font-size:7px;text-align:center;float:left;\">" . $codice . " &nbsp; </div><div class=\"testo\" style=\"	font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
 font-size:14px;text-align:center;float:right; font-weight:bold;\">" . number_format($_POST['prezzo_unitario'], 2) . " &euro;</div>
	<img  style=\"height:16px;width:33mm;\" id=\"codice\" src=\"barcode.php?barcode=$barcode\"></img><div class=\"testo\" style=\"font-family:arial; font-size:8px;text-align:center;float:left;\">" . $_POST['nome'] . " &nbsp;</div></div>";

                        if (!empty($_POST['materiale'])) {
                            echo "<div class=\"page-break\" style=\"letter-spacing: -0.2px; padding-left:3mm;padding-top:3mm;font-size:6px;line-height:120%;width:36mm;clear:both; \" >Import da LS BIJOUX P.IVA IT02524510407<br/>Non adatto a bambini di et&agrave; inferiore a 3 anni.<br/><b>" . strtoupper($_POST['materiale']) . "</b></div>";
                        }

                        echo "<a onClick=\"$('body').css('margin-bottom','0'); $('div.page-break').printArea();$('body').css('margin-bottom','60px'); \">Stampa etichetta</a>";
                    } else
                        echo "ARTICOLO NON MEMORIZZATO!";
                }

//$risultato->close();
//echo $query;
                ?>

                <div class="form-group">
                    <form name="form1" method="post" action="./ins_articolo.php">

                        <table>
                            <tr><td><label for="materiale">Etichetta Materiale:</label></td><td><?php select_materiali(); ?></td></tr>


                            <tr><td>
                                    <label for="codice_fornitore">Sigla Fornitore (3 lettere):</label></td><td>
                                    <input class="form-control"  name="codice_fornitore" type="text" size="3" maxlenght="3"></td></tr>

                            <tr><td>


                            <tr><td><label for="gruppi">Gruppo:</label></td><td><?php select("gruppi"); ?></td></tr>

                            <script>
                                $('select[name="gruppi"]').change(function () {
                                    $('input[name="Hidden_Gruppo"]').val($('select[name="gruppi"] option:selected').text());
                                });

                            </script>

                            <tr><td>

                                    <label for="nome">
                                        Descrizione:</label></td><td>
                                    <input class="form-control"  name="nome" type="text" size="50"></td></tr>


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

                            <tr><td>
                                    <label for="sconto_azienda">Sconto affiliato %:</label></td><td>
                                    <input class="form-control"  name="sconto_azienda" type="text" size="30"></td></tr>

                            <tr><td><label for="calcola_sconto_azienda">Prezzo che ci paga l'affiliato:</label></td><td><input class="form-control"  name="calcola_sconto_azienda" type="text" size="30"></td></tr>

                            <tr><td>
                                    <label for="sconto_pubblico">Sconto pubblico %:</label></td><td>
                                    <input class="form-control"  name="sconto_pubblico" type="text" size="30"></td></tr>

                            <tr><td><label for="calcola_sconto_pubblico">Prezzo unitario scontato al pubblico:</label> </td><td><input class="form-control"  name="calcola_sconto_pubblico" type="text" size="30"> </td></tr>

                            <tr><td><label for="attivo">Articolo/Buono attivo (1 SI / 0 NO):</label> </td><td><input class="form-control" name="attivo" type="text" value="1" size="30"> </td></tr>

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
