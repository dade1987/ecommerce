<?php
//Includo le classi necessarie
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
?>

<html>
    <head>
        <meta charset="UTF-8">

        <!-- Inclusione jQuery -->
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
        //Memorizzo le variabili per verificare se l'utente è collegato e per il nome utente
        list($status, $user) = auth_get_status();

        //Se un utente è collegato
        if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {



            menu();
            ?>


            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <h1>ORDINE DA GIACENZA</h1>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-md-10">
                        <input class="form-control" type="text" id="barcode" placeholder="Barcode"></input>
                    </div>
                    <div class="col-md-2">
                        <button class="form-control btn btn-success" id="submit">Stampa DDT</button>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="col-md-12" style="margin-top:20px;background-color:white;border: 1px solid magenta;padding:10px;">

                        <div class="col-md-2">
                            <h4><b>BARCODE</b></h4>
                        </div>
                        <div class="col-md-2">
                            <h4><b>GRUPPO</b></h4>
                        </div>
                        <div class="col-md-2">
                            <h4><b>COLORE</b></h4>
                        </div>
                        <div class="col-md-5">
                            <h4><b>OPZIONE</b></h4>
                        </div>
                        <div class="col-md-1">
                        </div>
                    </div>
                </div>
                <div id="ordine">
                    <!-- RIEMPITO VIA AJAX -->
                </div>
            </div>

            <script>

                //DUE POSSIBIITA DI LISTENER
                $('#submit').click(function () {

                    var ordine_raggruppato = new Object();

                    $('div#ordine>.row.clearfix').each(function () {

                        var barcode = $(this).find('.ord_barcode>h4').html();
                        var gruppo = $(this).find('.ord_gruppo>h4').html();
                        var colore = $(this).find('.ord_colore>h4').html();
                        var negozio = $(this).find('.ord_negozio select option:selected').val();
                        var quantita = parseInt($(this).find('.quantita').val());

                        if (ordine_raggruppato[negozio] === undefined)
                            ordine_raggruppato[negozio] = new Object();

                        if (ordine_raggruppato[negozio][barcode] === undefined)
                            ordine_raggruppato[negozio][barcode] = new Object();

                        if (ordine_raggruppato[negozio][barcode][gruppo] === undefined)
                            ordine_raggruppato[negozio][barcode][gruppo] = new Object();

                        if (ordine_raggruppato[negozio][barcode][gruppo][colore] === undefined)
                            ordine_raggruppato[negozio][barcode][gruppo][colore] = new Object();

                        if (ordine_raggruppato[negozio][barcode][gruppo][colore].quantita === undefined)
                            ordine_raggruppato[negozio][barcode][gruppo][colore].quantita = 0;

                        //OGGETTO FINALE
                        ordine_raggruppato[negozio][barcode][gruppo][colore].negozio = negozio;
                        ordine_raggruppato[negozio][barcode][gruppo][colore].barcode = barcode;
                        ordine_raggruppato[negozio][barcode][gruppo][colore].gruppo = gruppo;
                        ordine_raggruppato[negozio][barcode][gruppo][colore].colore = colore;
                        ordine_raggruppato[negozio][barcode][gruppo][colore].quantita += quantita;

                    });

                    //console.log(ordine_raggruppato);

                    var posting = $.post('./ddt_ordine_da_giacenza_ajax.php', {ordine_raggruppato: ordine_raggruppato},
                    function (data) {
                        $('div#ordine').html(data);
                    });
                });


                document.getElementById('barcode').onkeypress = function (e) {
                    if (!e)
                        e = window.event;
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) {
                        // Enter pressed
                        inserisci_articolo();
                        return false;
                    }
                };

                function stampa() {

                    var tableRow = new Array();

                    $("#ordine .row.clearfix .ord_negozio select option:selected").filter(function () {
                        tableRow.push($(this).val());
                    });


                    //COSI HO L'ELENCO UNICO DEI NEGOZI
                    var negozi = jQuery.unique(tableRow);


                    negozi.forEach(function (negozio) {


                        var tab1 = $("#ordine .row.clearfix").filter(function () {
                            return $(this).find('.ord_negozio select option:selected').val() === negozio;
                        });

                        var table = '';
                        tab1.each(function () {
                            console.log($(this));
                            table += $(this)[0].outerHTML;
                        });


                        console.log(table);
                        //console.log(table[0].outerHTML,table);

                        //TEMPORANEO
                        $('.elimina_div,.ord_negozio').hide();

                        $('#ordine').prepend('\
                        <div class="row clearfix" >\n\
                        <div class="col-xs-12 col-md-12">\n\
                        <h4>' + negozio + '</h4>\n\
                        </div>\n\
                        </div>' + table);

                        //console.log(table, negozio);
                        tab1.remove();

                    });



                    //$('#ordine').printArea();



                }

                function inserisci_articolo() {
                    var barcode = $('#barcode').val();
                    // Send the data using post
                    var posting = $.post('./ordine_da_giacenza_ajax.php',
                            {barcode: barcode},
                    function (data) {
                        //console.log(data);

                        //RIEMPIE LE OPZIONI PER IL GRUPPO/COLORE
                        //Altrimenti come prima opzione mette undefined
                        var options = '';
                        data.forEach(function (obj) {

                            var quantita = 0;


                            var tableRow = $("#ordine .row.clearfix").filter(function () {

                                return $(this).find(".ord_gruppo>h4").html() === data[0].gruppo &&
                                        $(this).find(".ord_colore>h4").html() === data[0].colore &&
                                        $(this).find(".ord_negozio select option:selected").val() === obj.negozio;

                            });


                            tableRow.each(function () {

                                quantita += parseInt($(this).find(".quantita").val());

                            });

                            //console.log(quantita);

                            var differenza = obj.mancanza - quantita;

                            //console.log(obj.negozio, obj.mancanza, quantita, differenza);


                            options += '<option value="' + obj.negozio + '">' + obj.negozio + ' (Mancanti: ' + differenza + ')</option>';
                        });

                        $('#ordine').prepend('\n\
                        <div class="row clearfix" >\n\
                        <div class="col-xs-12 col-md-12" style="background-color:silver;border:1px solid black;padding:10px;">\n\
                            \n\
                            <div class="ord_barcode col-xs-3 col-md-2">\n\
                                <h4>' + data[0].barcode + '</h4>\n\
                            </div>\n\
                            <div class="ord_gruppo col-xs-3  col-md-2">\n\
                                <h4>' + data[0].gruppo + '</h4>\n\
                            </div>\n\
                            <div class="ord_colore col-xs-3  col-md-2">\n\
                                <h4>' + data[0].colore + '</h4>\n\
                            </div>\n\
                            <div class="ord_negozio col-md-4">\n\
                                <select class="form-control">' + options + '</select>\n\
                            </div>\n\
                            <div class="ord_quantita col-xs-3 col-md-1">\n\
                                <input type="number" class="form-control quantita" value="1">\n\
                            </div>\n\
                            <div class="col-md-1 elimina_div">\n\
                                <h4><a class="elimina_riga">ELIMINA</a></h4>\n\
                            </div>\n\
                        </div>\n\
                    </div>');

                    },
                            'json');

                    $('#barcode').val('');
                }



                $(document).on('click', '.elimina_riga', function () {
                    $(this).closest('.row.clearfix').remove();
                });


            </script>

        <?php } ?>


    </body>
</html>

