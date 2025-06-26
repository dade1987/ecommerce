<?php
session_start(); //Inizio la sessione

if(count($_POST)===0)
{
    unset($_SESSION['articolo']);
}

include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();

//echo $status." ".$user; 

if ($status == AUTH_LOGGED && license_has($user, "affiliato") && ($user['nome_negozio'] === 'BLACK FASHION (Esse Erre)' || $user['nome_negozio'] === 'BLACK FASHION THIENE' || $user['nome_negozio'] === 'BLACK FASHION ODERZO' || $user['nome_negozio'] === 'BLACK FASHION VICENZA' || $user['nome_negozio'] === 'BF NICOSIA' || $user['nome_negozio'] === 'BLACK FASHION CASTELFRANCO' || $user['nome_negozio'] === 'BLACK FASHION MONTEBELLUNA')
) {
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

            <script type="text/javascript">
                function Articolo() {
                    this.gruppo = null;
                    this.colore = null;
                    this.prezzo = null;
                }

                var articolo = new Articolo();

                //stampa bool
                function stampa_scontrino(stampa) {
                    
                    console.log("stampato");

                    var gruppo = [];
                    var colore = [];
                    var prezzo = [];
                    var barcode= [];
                    var codice = [];

                    $('#cassa table tbody').find('[id^="art_"]').each(
                            function () {
                                gruppo.push($(this).find('td:nth-child(1)').html());
                                colore.push($(this).find('td:nth-child(2)').html());
                                prezzo.push($(this).find('td:nth-child(3)').html());
                                barcode.push($(this).find('.barcode').val());
                                codice.push($(this).find('.codice').val());
                            });

                    $.ajax({
                        type: "POST",
                        url: 'cassa_extra_ajax.lib.php',
                        data: {
                            action: "crea_sessione",
                            gruppo: gruppo,
                            colore: colore,
                            prezzo: prezzo,
                            barcode: barcode,
                            codice: codice
                        },
                        success: function () {
                            if (stampa === true)
                            {
                                window.open('../../scontrini/scontrino.php', '_blank');
                            }
                            $('#cassa tbody>tr').remove();
                            calcola_totale();
                        }
                    });
                }

                function calcola_totale() {

                    var totale = 0.0;

                    $('#cassa table tbody').find('[id^="art_"]').each(
                            function ()
                            {
                                //Prezzo x quantità se è un prezzo
                                totale += parseFloat($(this).find('td:nth-child(3)').html());
                            });
                    $('#totale_conto>tr>th:nth-child(3)').html(totale.toFixed(2));
                }



                function elimina_articolo(id) {
                    $('#cassa table tbody tr#art_' + id).remove();
                    calcola_totale();
                }


                $(document).ready(function () {

                    $('#input_barcode').change(
                            function () {
                                $.ajax({
                                    beforeSend: function () {
                                        $("body").addClass("loading")
                                    },
                                    complete: function () {
                                        $("body").removeClass("loading")
                                    },
                                    type: "POST",
                                    url: 'cassa_extra_ajax.lib.php',
                                    data: {
                                        action: "estrai_da_barcode",
                                        barcode: $('#input_barcode').val()},
                                    success: function (response) {
                                        eval(response);
                                        calcola_totale();
                                    },
                                    complete: function () {
                                        $('#input_barcode').val('');
                                    }
                                });
                            });


                    $('#btn_prezzo_alternativo,#selettore_gruppi li, #selettore_colori li, #selettore_prezzi li').click(function () {
                        $(this).parent().find("li.colorato").removeClass('colorato');
                        //COLORA LE SCELTE
                        $(this).addClass('colorato');

                        //CREA L OGGETTO
                        switch ($(this).closest('div').attr('id')) {
                            case "selettore_gruppi":
                                articolo.gruppo = $(this).html();
                                break;
                            case "selettore_colori":
                                articolo.colore = $(this).html();
                                break;
                            case "selettore_prezzi":
                                articolo.prezzo = $(this).html();
                                break;
                            case "prezzo_libero":
                                $('#prezzo_alternativo').addClass('colorato');
                                articolo.prezzo = $('#prezzo_alternativo').val();
                                break;
                        }

                        //SE L OGGETTO E' PIENO LO BUTTA NELLA TABELLA
                        if (articolo.gruppo !== null && articolo.colore !== null && articolo.prezzo !== null)
                        {

                            setTimeout(function () {
                                //rimuove i colori
                                $(".colorato").removeClass("colorato");

                                //butta in tab
                                var progressivo = $('#cassa table tbody tr').length;

                                $('#cassa table tbody').prepend('<tr id="art_' + progressivo + '"><td>' + articolo.gruppo + '</td><td>' + articolo.colore + '</td><td>' + articolo.prezzo + '</td><td onclick="elimina_articolo(' + progressivo + ');" style="color:red;cursor:pointer;">X</td></tr>');

                                //azzera l'input del prezzo alternativo
                                $('#prezzo_alternativo').val('');

                                //azzera l'oggetto
                                articolo.gruppo = null;
                                articolo.colore = null;
                                articolo.prezzo = null;

                                calcola_totale();
                            }, 200);
                        }

                    });
                });
            </script>


            <style>
                ul {
                    padding:0;
                }

                #pulsanti_cassa, #barcode {
                    padding-top:25px;
                    padding-bottom:25px;
                }

                .colorato {
                    background-color: cadetblue !important;
                }

                #selettore_gruppi, #selettore_colori {
                    font-size:2em;
                    background-color:white;
                } 

                #selettore_prezzi li {
                    font-size:2em;
                    text-align:center;
                    float:left;
                    width:100px;
                    height:80px;
                    background-color: white;
                }

                #selettore_gruppi li, #selettore_colori li, #selettore_prezzi li{
                    cursor:pointer;
                    border:2px solid grey;
                    list-style-type: none;
                }

                #prezzo_libero {
                    padding-top:50px;
                }

                table {
                    font-size:1.5em;
                }
            </style>

        </head>

        <body>

    <?php menu(); ?>


            <div class="container-fluid">
                <div class="row" id="barcode">

                    <div class="col-md-8" >
                        <input type="text"  class="form-control" id="input_barcode" name="barcode" >
                    </div>
                    <!--
                    <div class="col-md-4">
                        <button class="btn btn-default" id="btn_invia_barcode">Invia barcode!</button>
                    </div>
                    -->
                </div>                   
                <div class="row">
                    <div class="col-md-2">
                        <div id="selettore_gruppi">
                            <ul>
                                <li>VARIE</li>
                                <li>COLLANE</li>
                                <li>BRACCIALI</li>
                                <li>ORECCHINI</li>
                                <li>BORSE</li>
                                <li>POCHETTE</li>
                                <li>PINZE LISCIE</li>
                                <li>PINZE STRASSA</li>
                                <li>BAULETTI</li>
                                <li>FOULARD</li>
                                <li>ABBIGLIAMENTO</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div id="selettore_colori">
                            <ul>
                                <li>AI 2015-16</li>
                                <li>ARGENTO</li>
                                <li>BIANCO</li>
                                <li>BLU</li>
                                <li>GRIGIO</li>
                                <li>MARRONE</li>
                                <li>NERO</li>
                                <li>ORO</li>
                                <li>ROSA/VIOLA</li>
                                <li>ROSSO</li>
                                <li>VERDE</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-8">                        
                        <div class="row">
                            <div class="col-m-12" id="selettore_prezzi">
                                <ul>
                                    <li>1.90</li>
                                    <li>2.90</li>
                                    <li>3.90</li>
                                    <li>4.90</li>
                                    <li>5.90</li>
                                    <li>6.90</li>
                                    <li>7.90</li>
                                    <li>8.90</li>
                                    <li>9.90</li>
                                    <li>10.90</li>
                                    <li>11.90</li>
                                    <li>12.90</li>
                                    <li>13.90</li>
                                    <li>14.90</li>
                                    <li>15.90</li>
                                    <li>16.90</li>
                                    <li>17.90</li>
                                    <li>18.90</li>
                                    <li>19.90</li>
                                    <li>20.90</li>
                                    <li>21.90</li>
                                    <li>22.90</li>
                                    <li>23.90</li>
                                    <li>24.90</li>
                                    <li>25.90</li>
                                    <li>26.90</li>
                                    <li>27.90</li>
                                    <li>28.90</li>
                                    <li>29.90</li>
                                    <li>30.90</li>
                                    <li>31.90</li>
                                    <li>32.90</li>
                                    <li>33.90</li>
                                    <li>34.90</li>
                                    <li>39.90</li>
                                    <li>49.90</li>
                                    <li>59.90</li>
                                    <li>69.90</li>
                                    <li>79.90</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10" id="prezzo_libero">
                                <label>Inserisci qui i prezzi che non sono nella tabella (CON IL PUNTO, NON LA VIRGOLA es. 11.56 NON 11,56)</label>
                                <input id="prezzo_alternativo" type = "text" placeholder = "Inserisci qui i prezzi che non sono nella tabella" class= "form-control" >
                                <br/>
                                <button class="btn btn-default" id="btn_prezzo_alternativo">Conferma prezzo</button>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="row" id="pulsanti_cassa">   
                    <div class="col-md-2">
                        <button class="btn btn-default" onclick="stampa_scontrino(true);">Stampa scontrino</button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-default" onclick="stampa_scontrino(false);">Registra senza stampare</button>
                    </div>
                    <div class="col-md-4">
                    </div>
                </div>   
                <div class="row">
                    <div id="cassa">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr class="warning"><th>Gruppo</th><th>Colore</th><th>Prezzo</th><th>Elimina</th></tr>
                            </thead>
                            <thead id="totale_conto">
                                <tr class="danger"><th>Totale</th><th></th><th></th><th></th></tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
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
        </body>
    </html>
    <?php
}
?>

