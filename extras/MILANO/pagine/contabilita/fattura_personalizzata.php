<?php
session_start();
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");


list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
    if (isset($_POST['memorizza'])) {
        $sql = "INSERT INTO db_fatture (anno,numero_fattura,fattura,negozio,data,intestatario,totale_fattura)
            VALUES ('".date('Y')."','".numero_fattura()."','" . addslashes($_POST['html']) . "','" . $user['nome_negozio'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','" . $_POST['intestatario'] . "','" . $_POST['totale_totale'] . "')";

        #echo "<pre>Sql query: $sql</pre>";

        $db_fatture->query($sql);
        echo "<h2>Fattura memorizzata. Non ricaricare o aggiornare la pagina.</h2>";
    }

    menu();
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
            <script>
                $(document).ready(function () {
                    $(document).change(function () {
                        $("input").each(function () {
                            $(this).attr("value", $(this).val());
                        });
                        $("textarea").each(function () {
                            $(this).html($(this).val());
                        });

                        $('input[name="html"]').val($('#area_stampa').html() +
                                "<button name=\"stampa\" onclick=\"$('.menu,button').hide();window.print();$('.menu,button').show();\">Stampa fattura</button>");
                    });
                });
                function sconto() {
                }

                function totale() {
                    var costo = $('#costo').val().split("\n");
                    var quantita = $('#quantita').val().split("\n");
                    var counter = costo.length;
                    //console.log(costo);
                    var totale_stringa = "", numero_articoli = 0, totale = 0.0;


                    for (i = 0; i < counter; i++) {
                        totale_stringa += ((costo[i] * quantita[i]).toFixed(2)) + "\n";
                        numero_articoli += parseInt(quantita[i]);
                        totale += parseFloat((costo[i] * quantita[i]).toFixed(2));
                    }

                    $('#totale').html(totale_stringa);
                    $('#totale_totale').val((totale / 100 * 122).toFixed(2));
                    $('#imponibile').val((totale).toFixed(2));
                    $('#imposta').val((totale / 100 * 22).toFixed(2));
                    $('#numero_articoli').val(numero_articoli);


                }

                $(document).ready(function () {
                    $('select[name="utenti3"]').change(
                            function () {
                                $('form').submit();
                            });

                    $('#quantita').change(function (e) {
                        totale();
                    });

                    $('#costo').change(function (e) {
                        totale();
                    });
                });
            </script>
        </head>
        <body>
            <h1>FATTURA PERSONALIZZATA</h1>
            <form method="POST">
                <?php
                $emittente = $db_magazzino->query("SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . casa_madre . "'");
                $emittente = $emittente->fetch_assoc();

                $intestatario = $db_magazzino->query("SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $_POST[$_CONFIG['table_utenti']] . "'");
                $intestatario = $intestatario->fetch_assoc();
                select($_CONFIG['table_utenti']);

                $numero_fattura = numero_fattura();
                ?>
                <br/><br/>

                <div id="area_stampa">
                    <div style="background-color:white;width:100%;clear:both;  min-height:75px;padding:10px;">
                        <h2>FATTURA NUMERO: <?php echo $numero_fattura; ?> DEL <?php echo date("d-m-Y"); ?></h2>
                    </div>
                    <br/>
                    <div style="background-color:white;width:45%;float:left;min-height:200px;margin-bottom:20px;padding:10px;clear:both;">
                        <h2>EMITTENTE</h2>
                        <h4><?php echo $emittente['nome_sede_legale']; ?></h4>
                        <p><?php echo $emittente['indirizzo_sede_legale'] . "<br/>" . $emittente['citta_sede_legale'] . " (" . $emittente['provincia_sede_legale'] . ")"; ?></p>
                        <p><?php echo "P.IVA " . $emittente['partita_iva_sede_legale'] . " C.F. " . $emittente['codice_fiscale_sede_legale']; ?></p>
                    </div>
                    <div style="background-color:white;width:45%;float:right;min-height:200px;margin-bottom:20px;padding:10px;">
                        <h4><input type="text" placeholder="Intestatario" name="intestatario" style="width:100%;"  value="<?php echo $intestatario['nome_sede_legale']; ?>"></input></h4>
                        <p><?php
            echo "<input type='text' placeholder='Indirizzo' style='width:100%;' value='" . $intestatario['indirizzo_sede_legale'] . "'></input><br/>"
            . "<br/><input type='text' placeholder='Citt� (PR)' style='width:100%;'";
            if (!empty($intestatario['citta_sede_legale']))
                echo "value='" . $intestatario['citta_sede_legale'] . " " . $intestatario['provincia_sede_legale'] . "'";
            echo "></input>";
                ?></input></p>
                        <p><?php
                            echo "<input type='text' placeholder='P.I. - C.F.' style='width:100%;' ";
                            if (!empty($intestatario['partita_iva_sede_legale']))
                                echo "value='" . $intestatario['partita_iva_sede_legale'] . ' ' . $intestatario['codice_fiscale_sede_legale'] . "'";
                            echo "></input>";
                            ?></p>
                    </div>
                    <div style="background-color:white;width:100%;clear:both; margin-bottom:20px; overflow:auto;padding:10px;">
                        <p>Note<input  style='width:100%;' type='text'></input></p>
                        <div style="width:24%;float:left;"><b>Descrizione</b><br/><textarea style="min-height:170px;"></textarea></div>
                        <div style="width:24%;float:left;"><b>Costo / pz.</b><br/><textarea id="costo" style="min-height:170px;"></textarea></div>
                        <div style="width:24%;float:left;"><b>Quantit�</b><br/><textarea id="quantita" style="min-height:170px;"></textarea></div>
                        <div style="width:24%;float:left;"><b>Totale</b><br/><textarea id="totale" style="min-height:170px;" readonly ></textarea></div>
                    </div>

                    <div style="background-color:white;width:100%;clear:both;  min-height:75px;padding:10px;">
                        <h4>Numero fattura: <?php echo $numero_fattura; ?> </h4>
                        <p>Numero articoli: <input type="text" id="numero_articoli" readonly /> Imponibile: <input type="text" id="imponibile" readonly /> 
                            <br/>Aliquota: <input type="text" value="22%" id="aliquota" readonly /> Imposta: <input type="text" id="imposta" readonly /> 
                            <br/>Totale: <input type="text" id="totale_totale" name="totale_totale" readonly /> </p>
                    </div>
                </div>

                <br/>
                <button class="btn btn-default" name="memorizza" onclick="$('form').submit();">Memorizza fattura</button>
                <input type="hidden" name="html">

            </form>
            <button class="btn btn-default" name="stampa" onclick="$('#area_stampa').printArea();">Stampa fattura</button>




            <?php
        } else {
            non_autorizzato();
        }
        ?>
    </body>