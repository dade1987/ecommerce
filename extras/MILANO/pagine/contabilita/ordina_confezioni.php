<?php
error_reporting(E_ALL);
session_start();
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");


list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED && license_has($user, "affiliato")) {
    if (isset($_POST['memorizza'])) {
        $sql = "INSERT INTO db_ordini (codice,negozio,data,intestatario,totale)
            VALUES ('" . addslashes($_POST['html']) . "','" . $user['nome_negozio'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','ESSE ERRE SAS','" . $_POST['totale_totale'] . "')";

        #echo "<pre>Sql query: $sql</pre>";

        $db_fatture->query($sql);
        echo "<h2>Ordine memorizzato. Non ricaricare o aggiornare la pagina.</h2>";
    }
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



            <style>
                @media print {
                    [class*="col-md-"] {
                        float: left;
                    }
                }

                #tabella_ordine_confezioni td {
                    border: 1px solid silver;
                }
            </style>

            <script>
                $(document).ready(function () {
                    $('#tabella_ordine_confezioni > tbody > tr > td:nth-child(3)').attr('contenteditable', 'true');
                    $(document).change(function () {

                        $("input").each(function () {
                            $(this).attr("value", $(this).val());
                        });
                        $("textarea").each(function () {
                            $(this).html($(this).val());
                        });
                    });
                });
                function sconto() {
                }

                function totale() {
                    /*var costo = $('#costo').val().split("\n");
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
                     $('#numero_articoli').val(numero_articoli);*/




                }

                $(document).ready(function () {
                    $('select[name="utenti3"]').change(
                            function () {
                                $('form').submit();
                            });
                    $('#tabella_ordine_confezioni > tbody > tr > td:nth-child(3)').blur(
                            function () {
                                var quantita = $(this).html();
                                var prezzo_un = $(this).parent().find('td:nth-child(2)').html();
                                var multiplo = $(this).parent().find('td:nth-child(4)').html();

                                if (quantita % multiplo !== 0)
                                {
                                    $(this).html($(this).parent().find('td:nth-child(4)').html());
                                }

                                if (quantita > 0 && quantita !== '' && quantita !== null && quantita !== undefined) {
                                    $(this).parent().find('td:nth-child(5)').html(parseFloat(prezzo_un * quantita).toFixed(2));
                                    var n_articoli = 0;
                                    var tot = 0.00;

                                    $('#tabella_ordine_confezioni > tbody > tr > td:nth-child(3)').each(function () {

                                        if (!isNaN(parseInt($(this).html())) && !isNaN(parseFloat($(this).parent().find('td:nth-child(5)').html()) !== undefined))
                                        {
                                            n_articoli += parseInt($(this).html());
                                            tot += parseFloat($(this).parent().find('td:nth-child(5)').html());
                                        }

                                    });
                                    $('#numero_articoli').attr('value', n_articoli);
                                    $('#totale_totale').attr('value', tot.toFixed(2));

                                    $('input[name="html"]').val($('#area_stampa').html());
                                }

                            });

                });
            </script>
        </head>
        <body>
            <?php menu(); ?>
            <form method="POST">
                <?php
                $emittente = $db_magazzino->query("SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . casa_madre . "'");
                $emittente = $emittente->fetch_assoc();

                $intestatario = $db_magazzino->query("SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $user['nome_negozio'] . "'");
                $intestatario = $intestatario->fetch_assoc();

                $numero_fattura = numero_fattura();
                ?>
                <div class="container-fluid" id="area_stampa">

                    <div class="row">

                        <div class="col-md-12 col-xs-12" >
                            <div style="background-color:white;width:100%;clear:both;  min-height:75px;padding:10px;">
                                <h2>ORDINE DI MATERIALI DEL <?php echo date("d-m-Y"); ?> DA <?php echo $user['nome_negozio']; ?> </h2>
                            </div>
                            <br/>
                            <br/>

                        </div>

                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="col-md-5 col-xs-5">
                                    <h2>RICHIEDENTE</h2>
                                    <h4><?php echo $intestatario['nome_sede_legale']; ?></h4>
                                    <p><?php
                                        echo $intestatario['indirizzo_sede_legale'] . "<br/>";
                                        echo $intestatario['citta_sede_legale'] . "(";
                                        echo $intestatario['provincia_sede_legale'] . ")<br/><br/>";
                                        echo "P.IVA " . $intestatario['partita_iva_sede_legale'] . "<br/>";
                                        echo "C.F. " . $intestatario['codice_fiscale_sede_legale'];
                                        ?></p>
                                </div>

                                <div class="col-md-5 col-xs-5 col-md-offset-1 col-xs-offset-1">
                                    <h2>DESTINATARIO</h2>
                                    <h4><?php echo $emittente['nome_sede_legale']; ?></h4>
                                    <p><?php echo $emittente['indirizzo_sede_legale'] . "<br/>" . $emittente['citta_sede_legale'] . " (" . $emittente['provincia_sede_legale'] . ")"; ?></p>
                                    <p><?php echo "P.IVA " . $emittente['partita_iva_sede_legale'] . " C.F. " . $emittente['codice_fiscale_sede_legale']; ?></p>
                                </div>
                            </div>
                        </div>
                        <br/><br/>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="col-md-12 col-xs-12"><label>Note</label><input class="form-control" type='text'/><br/><br/></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <table class="table table-responsive" id="tabella_ordine_confezioni">
                                    <tr><th>Articolo</th><th>Costo / pz. (iva escl.)</th><th>Quantita' ordinata</th><th>Multipli quantita'</th><th>Totale</th></tr>

                                    <?php
                                    $nome_articolo = elenco_articoli("nome");
                                    $costo_un_articolo = elenco_articoli("costo_unitario");
                                    $multipli_articolo = elenco_articoli("quantita_minima");

                                    $iter1 = count($nome_articolo);

                                    for ($i = 0; $i < $iter1; $i++) {
                                        echo '<tr><td>' . $nome_articolo[$i] . '</td><td>' . $costo_un_articolo[$i] . '</td><td></td><td>' . $multipli_articolo[$i] . '</td><td></td></tr>';
                                    }
                                    ?>
                                </table>

                            </div>
                        </div>
                        <br/><br/>
                        <div class="row col-md-12 col-xs-12">
                            <div class="col-md-3 col-xs-3">
                                <p>Articoli: <input type="text" id="numero_articoli" readonly />
                            </div>
                            <div class="col-md-3 col-xs-3 col-md-offset-1 col-xs-offset-1">

                                Totale: <input type="text" id="totale_totale" name="totale_totale" readonly /> </p>
                            </div>
                            <div class="col-md-12 col-xs-12">

                                <p>* I prezzi si riferiscono all'eventuale disponibilita' totale della merce.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <br/>
            <input type="hidden" name="html">
            <button class="btn btn-default"  name="memorizza" onclick="$('form').submit();">Memorizza ordine</button>

        </form>

        <button class="btn btn-default" name="stampa" onclick="$('#area_stampa').printArea();">Stampa ordine</button>




        <?php
    } else {
        non_autorizzato();
    }
    ?>
</body>

<?php

function elenco_articoli($nome_dato) {
    global $db_fatture;
    $sql = "SELECT * FROM voci_confezionamento;";
    $risultato = $db_fatture->query($sql);


    for ($i = 0; !empty($dato = $risultato->fetch_assoc()); $i++) {
        $colonna[$i] = $dato[$nome_dato];
    }

    return $colonna;
}
?>