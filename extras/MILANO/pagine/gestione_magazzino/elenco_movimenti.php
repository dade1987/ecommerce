<?php session_start(); ?>
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

        <script src="/bimbo/js/brainextension.js" type="text/javascript"></script>
        <script src="/bimbo/jQuery/jquery.ui.touch-punch.min.js" type="text/javascript"></script>


        <!--<link href="//cdn.datatables.net/1.10.8/css/jquery.dataTables.min.css" rel="stylesheet">
        <script src="//cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" type="text/javascript"></script>-->
        <style>
            @media print {

                [class*="col-md-"] {
                    float: left;
                }        
            }

            #menu_laterale_dx {
                top:5%;
                right:-1180px;
                position:fixed;
                width:1200px;
                z-index:2000;
                padding:20px;
                display:block;
                border:2px solid silver;
                border-radius:10px;
                background-color:snow;
                cursor:e-resize;
            }

            tr#reso{
                background-color:red;
            }
        </style>
        <!-- Java Script per aprire un popup all'invio del form -->
        <script>

            $(document).keydown(function (e) {
                if (e.keyCode === 120) // w
                {
                    console.log("F9");
                    mem_fattura_popup();
                }
                if (e.keyCode === 121) // w
                {
                    console.log("F10");
                    print_fattura();
                }
            });

            $(document).ready(function (event) {

                var parts = window.location.search.substr(1).split("&");
                var $_GET = {};
                for (var i = 0; i < parts.length; i++) {
                    var temp = parts[i].split("=");
                    $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
                }

                if ($_GET['sfb'] === "bfs")
                {
                    mem_fattura_popup();
                    print_fattura();
                }

                $('#menu_laterale_dx').draggable({
                    axis: "x"
                });

                //$('div.fattura.A4 > div.col-md-12.table-responsive > table').DataTable();


                $("input[name='barcode']").focus();



            });

            function sconto() {
                //Altrimenti dice che � undefinied
            }

            function mem_bolla_popup() {
                window.open('../gestione_resi/memorizza_bolla.php', 'bolla_memorizzata', 'width=400,height=250');
            }

            function mem_fattura_popup() {
                window.open('../contabilita/memorizza_fattura.php', 'fattura_memorizzata', 'width=400,height=250');
            }

            function print_fattura() {
                $('#tawkchat-iframe-container').hide();
                $("nav").hide();
                $("td.fattura").css("font-size", "11px");
                $("tr#reso.restituzione").hide();
                $(".a4").css("margin-bottom", "55px");
                $(".a4:last-child()").css("margin-bottom", "-10px");
                $('.table>tbody>tr>td').css("padding-top", "0");
                $('.table>tbody>tr>td').css("padding-bottom", "0");
                $(".menu").hide();
                //$(".ddt").not('.fattura').remove();
                $("p").hide();
                $("h2").hide();
                $("form").hide();
                $("table tr td").hide();
                $("table tr td.fattura").show();
                $("div.fattura").children().show();
                $('button').hide();
                $('#menu_laterale_dx').hide();

                window.print();

            }
            ;

            function mem_nota_popup() {
                window.open('../contabilita/memorizza_nota.php', 'nota_memorizzata', 'width=400,height=250');
            }

            function print_nota() {
                $('#tawkchat-iframe-container').hide();
                $("nav").hide();
                $("td.fattura").css("font-size", "11px")
                $(".a4").css("margin-bottom", "45px")
                $(".menu").hide();
                $(".ddt").hide();
                $("p").hide();
                $("h2").hide();
                $("form").hide();
                $("table tr td").hide();
                $("table tr td.fattura").show();
                $("div.fattura").children().show();
                window.print();
                $('#tawkchat-iframe-container').show();
                $("nav").show();
                $("td.fattura").css("font-size", "initial")
                $(".a4").css("margin-bottom", "initial")
                $(".menu").show();
                $(".ddt").show();
                $("p").show();
                $("h2").show();
                $("form").show();
                $("table tr td").show();
                $("table tr td.fattura").show();
                $("div.fattura").children().show();
            }
            ;

            function print_ddt() {
                $('#tawkchat-iframe-container').hide();
                $("nav").hide();
                $("td.fattura").css("font-size", "11px")
                $(".a4").css("margin-bottom", "45px");
                $(".menu").hide();
                $(".ddt").hide();
                $("p").hide();
                $("h2").hide();
                $("form").hide();
                $("table tr td").hide();
                $("table tr td.fattura").show();
                $("div.fattura").children().show();
                window.print();
                $('#tawkchat-iframe-container').show();
                $("nav").show();
                $("td.fattura").css("font-size", "initial")
                $(".a4").css("margin-bottom", "initial")
                $(".menu").show();
                $(".ddt").show();
                $("p").show();
                $("h2").show();
                $("form").show();
                $("table tr td").show();
                $("table tr td.fattura").show();
                $("div.fattura").children().show();

            }
            ;

            function print_bolla() {
                $(".menu").hide();
                $(".fattura").hide();
                $("p").hide();
                $("h2").hide();
                $("form").hide();
                $("table tr td").hide();
                $("table tr td.ddt").show();
                window.print();
                $("p").show();
                $("h2").show();
                $("form").show();
                $("table tr td").show();
                $(".fattura").show();
                $(".menu").show();
                $('a').not('.menu a').remove();
                mem_bolla_popup();
            }
            ;
        </script>



    </head>

    <body>
        <?php
        //Includo le classi necessarie
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");
        include_once("../../classi/utils.lib.php");
        include_once("../../classi/license.lib.php");
        include_once("../../classi/funzioni.php");

        //Memorizzo le variabili per verificare se l'utente è collegato e per il nome utente
        list($status, $user) = auth_get_status();

        //Se un utente è collegato
        if ($status == AUTH_LOGGED) {
            ?>
            <?php
            menu(); //Il div finisce in funzioni
            ?>
            <div class="container-large"> 

                <?php if (license_has($user, "sede_centrale")) { ?>
                    <div id="menu_laterale_dx" >

                        <div class="col-md-5">

                            <div class="row">

                                <div class="col-md-9">

                                    <h1>Bimbo</h1>
                                    <h3>Cresce insieme a te! ;)</h3>
                                    <h5><i>L'intelligenza artificiale alla portata di tutti</i></h5>
                                </div>

                                <div class="col-md-3">
                                    <img src="/bimbo/neonato.png" style="float:right;margin-left:20px;margin-right:20px;height:100px;width:auto;" />
                                </div>
                            </div>


                            <div class="row">

                                <div class="col-md-12"><a href="javascript:legenda_comandi();">Legenda dei comandi</a></div>

                            </div>




                            <div class="row">

                                <div class="col-md-12">

                                    <h3>Fai qui la tua domanda</h3>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-1">

                                    <!--<button class="btn btn-success" id="registra_voce">Registra</button>-->
                                    <img id="registra_voce" src='/bimbo/mic.gif' style="cursor:pointer; background-color:white;"/>

                                </div>

                                <div class="col-md-11">

                                    <input type="text" placeholder="Clicca sul microfono e parla, oppure scrivi" id="richiesta_vocale" autocomplete="on" class="form-control">

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">&nbsp;</div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">

                                    <h3>Ricevi qui la risposta</h3>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">

                                    <input type="text" placeholder="Qui verra' fuori la risposta" id="risposta_vocale" class="form-control">

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">

                                    <h3>Impara una nuova formula</h3>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-3">

                                    <input type="text" placeholder="Tipo (es. + iva, ritenuta di , media)" id="nome_formula" autocomplete="on" class="form-control">

                                </div>

                                <div class="col-md-6">

                                    <input type="text" placeholder="Formula (i tipi sono stringa, numeri[0], numeri (array) es. numeri[0]*numeri[1])" id="funzione_formula" autocomplete="on" class="form-control">

                                </div>

                                <div class="col-md-1">

                                    <button class="btn btn-success" id="impara_funzione">Impara</button>

                                </div>

                            </div>

                        </div>

                    </div>
                <?php } ?>


                <div class="row col-md-12" style="padding-bottom:20px;">
                    <form name="form1" method="post" >
                        <h2>Visualizza</h2>

                        <div class="col-md-1">
                            <?php
                            //Se io fossi la sede centrale visualizzerei il menu per selezionare i negozi
                            if (license_has($user, "sede_centrale")) {
                                echo "<h4>Negozio</h4>";
                            }
                            ?>
                            <br/><h4>Codice a barre</h4>

                        </div>
                        <div class="col-md-2">
                            <?php
                            //Se io fossi la sede centrale visualizzerei il menu per selezionare i negozi
                            if (license_has($user, "sede_centrale")) {
                                //var_dump($user);
                                select($_CONFIG['table_utenti']);
                            }
                            ?>
                            <br/><input type="text" class="form-control" name="barcode" >
                        </div>

                        <div class="col-md-1">
                            <h4>Data d'inizio</h4>
                            <br/><h4>Data finale</h4>
                        </div>

                        <div class="col-md-2">
                            <input type="date" class="form-control" name="data" value="<?php echo date('Y-m-d', strtotime('now-7 day')); ?>" >
                            <br/><input type="date" class="form-control" name="data2" value="<?php echo date('Y-m-d', strtotime('now-1 day')); ?>" >                            

                        </div>

                        <div class="col-md-1">
                            <h4>Codice</h4>
                            <br/><h4>Descrizione</h4>                            
                        </div>
                        <div class="col-md-1">
                            <input class="form-control" type="text" name="codice" >
                            <br/><input class="form-control"  type="text" name="descrizione" >                            
                        </div>

                        <div class="col-md-1">                            
                            <h4>Gruppo</h4>
                            <br/><h4>Colore</h4>
                        </div>
                        <div class="col-md-1">
                            <?php select("gruppi"); ?>
                            <br/><?php select("colori"); ?>
                        </div>

                        <div class="col-md-1"></div>

                        <div class="col-md-1">
                            <input type="hidden" value="" name="hidden_gruppi" />
                            <input class="btn btn-default" type="submit" name="submit" value="OK" />                            
                        </div>
                    </form>



                    <script>
                        $('select[name="gruppi"]').change(function () {
                            $('input[name="hidden_gruppi"]').val($('select[name="gruppi"] option:selected').text());
                        });
                    </script>
                </div>

                <!-- FINE FORM -->
                <?php
                if (isset($_GET['switch'])) {
                    switch ($_GET['switch']) {
                        case "NotaCredito":
                            $_POST['RadioGroup1'] = "NotaCredito";
                            break;
                        case "Carichi":
                            $_POST['RadioGroup1'] = "carichi";
                            break;
                        case "Scarichi":
                            $_POST['RadioGroup1'] = "scarichi";
                            break;
                        case "Rimanenze":
                            $_POST['RadioGroup1'] = "rimanenze";
                            break;
                        case "Tutti_Carichi":
                            $_POST['RadioGroup1'] = "tutti_carichi";
                            break;
                        case "Tutti_Scarichi":
                            $_POST['RadioGroup1'] = "tutti_scarichi";
                            break;
                    }
                }
                ?>

                <div class="row col-md-12">
                    <?php
                    //Scritta di benvenuto		
                    echo "<h2>Benvenuto <b>" . $user["nome_negozio"] . "</b> - Tabella " . ucfirst($_POST['RadioGroup1']) . " di " . $_POST[$_CONFIG['table_utenti']] . "</h2>";
                    ?>
                </div>

                <?php
                //Centro i contenuti seguenti
                echo "<div class='row col-md-12'><div>";

                //Imposto il contenuto fisso della query per le tabelle di carico/scarico
                $query = "SELECT * FROM elenco_movimenti WHERE 1";

                //A seconda del contenuto dei form imposto la query	
                /* if(!empty($_POST['barcode'])) echo "<p>!isset barcode</p>";
                  if(!empty($_POST['descrizione'])) echo "<p>!isset descrizione</p>";
                  if(!empty($_POST['codice'])) echo "<p>!isset codice</p>";
                  if(!empty($_POST['gruppi'])) echo "<p>!isset gruppi</p>";
                  if(!empty($_POST['colori'])) echo "<p>!isset colori</p>"; */


                if (empty($_POST['barcode']) && empty($_POST['descrizione']) && empty($_POST['codice']) && empty($_POST['gruppi']) && empty($_POST['colori'])) {
                    #echo "<p>VERA</p>";
                    if ($_POST['data'])
                        $query.=" AND (data >= '" . $_POST['data'] . "'";
                    if ($_POST['data2'])
                        $query.=" AND data <='" . $_POST['data2'] . "')";
                }

                if (strlen($_POST['barcode']) == 13) {
                    $barcode = substr($_POST['barcode'], 0, -1);
                } else {
                    $barcode = $_POST['barcode'];
                }


                if ($_POST['barcode'])
                    $query.=" AND barcode LIKE '%" . $barcode . "%'";
                if ($_POST['descrizione'])
                    $query.=" AND descrizione LIKE '%" . $_POST['descrizione'] . "%'";
                if ($_POST['codice'])
                    $query.=" AND codice LIKE '%" . $_POST['codice'] . "%'";
                if ($_POST['gruppi'])
                    $query.=" AND gruppo='" . $_POST['hidden_gruppi'] . "'";
                if ($_POST['colori'])
                    $query.=" AND colore='" . $_POST['colori'] . "'";


                //echo $_POST[$_CONFIG['table_utenti']]."<br/>";
                //$_POST['gruppi']=substr($_POST['gruppi'],0,-2);
                switch ($_POST['RadioGroup1']) {
                    case NULL:
                        $_POST['RadioGroup1'] = 'scarichi';
                    case 'tutti_scarichi':
                        if (license_has($user, "sede_centrale"))
                            $query.=" AND causale='Vendita al dettaglio' ";
                        tab($query, 0);
                        break;
                    case 'tutti_carichi':
                        if (license_has($user, "sede_centrale"))
                            $query.=" AND causale='Vendita ad affiliato' ";
                        tab($query, 0);
                        break;
                    case 'rimanenze':
                        if (isset($_POST[$_CONFIG['table_utenti']]))
                            $negozio = $_POST[$_CONFIG['table_utenti']];
                        else
                            $negozio = $user['nome_negozio'];
                        tab_rimanenze($negozio, $barcode, $_POST['descrizione'], $_POST['codice'], $_POST['hidden_gruppi'], $_POST['colori']);
                        break;
                    case 'carichi':
                        if (!isset($_POST[$_CONFIG['table_utenti']]))
                            $query.=" AND cliente = '" . $user['nome_negozio'] . "'";
                        else
                            $query.=" AND cliente LIKE '" . $_POST[$_CONFIG['table_utenti']] . "'";
                        tab($query, $_POST[$_CONFIG['table_utenti']]);
                        break;
                    case 'scarichi';
                        if (!isset($_POST[$_CONFIG['table_utenti']]))
                            $query.="  AND fornitore = '" . $user['nome_negozio'] . "'";
                        else
                            $query.="  AND fornitore LIKE '" . $_POST[$_CONFIG['table_utenti']] . "'   AND causale NOT LIKE 'Reso -%'";
                        tab($query, $_POST[$_CONFIG['table_utenti']]);
                        break;
                    case 'NotaCredito';
                        if (!isset($_POST[$_CONFIG['table_utenti']]))
                            $query.="  AND fornitore = '" . $user['nome_negozio'] . "'";
                        else
                            $query.="  AND fornitore LIKE '" . $_POST[$_CONFIG['table_utenti']] . "'";
                        tab($query, $_POST[$_CONFIG['table_utenti']]);
                        break;
                    case 'resi':
                        $query.=" AND reso=1 AND cliente = '" . $_POST[$_CONFIG['table_utenti']] . "'";
                        //echo $query;
                        tab($query, $_POST[$_CONFIG['table_utenti']]);
                        break;
                }

                //echo $query;
                //echo $_POST[$_CONFIG['table_utenti']];

                echo '</div></div>';
            } else
            //Pagina per chi non è autorizzato	
                non_autorizzato();
            ?>
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
