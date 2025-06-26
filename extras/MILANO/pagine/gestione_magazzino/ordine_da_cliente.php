<?php
#var_dump($_SESSION);
$time_start = getmicrotime(); //sec iniziali

session_start();

include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
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

            <script src="../../classi/sorttable.js" type="text/javascript" language="javascript"></script>

            <script type="text/javascript" src="beep.js"></script>
            <script>

                var b1 = new Beep(40050);
                var b2 = new Beep(20050);

                function beep() {
                    b1.play(500, 0.05, [Beep.utils.amplify(50000)], function () {
                        console.log('ok');
                    });
                    $('body').css('background-color', 'green');
                    setTimeout(function () {
                        $('body').css('background-color', '');
                    }, 500);
                }

                function beep_errato() {
                    b2.play(500, 0.4, [Beep.utils.amplify(300000)], function () {
                        console.log('error');
                    });
                    $('body').css('background-color', 'red');
                    setTimeout(function () {
                        $('body').css('background-color', '');
                    }, 500);
                }

                $(document).ready(function ()
                {
                    function sconto() {
                    }
                    ;
                    $("input[name='barcode']").focus();
                    $('button[name="carica"]').click(function (e) {
                        var negozio = $('[name="utenti3"] option:selected').val();
                        if (!confirm('Sei sicuro di voler caricare i prodotti nel magazzino di ' + negozio + '?')) {
                            e.preventDefault();
                        }
                    })

                    $('button[name="carica"]').click(function () {
                        $('input').each(function () {
                            $(this).attr("value", $(this).val());
                        });


                        $('.attenzione').remove();
                        $('th.fattura:last-child()').remove();
                        $('tr>td.fattura:last-child()').remove();

                        $('[name="ddt_content"]').val($('#dati_ddt')[0].outerHTML + $('table')[0].outerHTML);

                        $('body').children().children().children().not('table').hide();
                        $('div#dati_ddt').show();
                        $('#intestatario_fattura').remove();


                        $('#dati_ddt input').css("height", "initial");
                        $('#dati_ddt input').css("font-size", "initial");
                        $('#dati_ddt input').css("border", "initial");
                        $('#dati_ddt #logo').css("margin-right", "20px");

                        $('#trasportatore_ddt').hide();
                        $('#codice_tracciatura').hide();

                        window.print();
                    });

                    $('#btn_salva_fattura').click(function (event) {

                        event.preventDefault();

                        $('input').each(function () {
                            $(this).attr("value", $(this).val());
                        });

                        $('#btn_salva_fattura').remove();

                        $.ajax({
                            url: "salva_fattura_da_ordine.lib.php",
                            data: {
                                action: 'salva_fattura_da_ordine',
                                intestatario: $('[name="utenti3"] option:selected').val(),
                                totale_fattura: $('span#totale_fattura').html(),
                                fattura: $('#dati_ddt')[0].outerHTML
                                        + $('table')[0].outerHTML + $('#pie_fattura')[0].innerHTML
                            },
                            type: "POST"
                        });

                    })


                    $('button[name="button"]').click(function (e) {

                        if (!confirm('Sei sicuro di voler annullare l\'intera operazione?')) {
                            e.preventDefault();
                        }
                    })

                    $('select[name="utenti3"]').change(function () {
                        $.ajax({
                            url: "dati_ddt.php",
                            data: {negozio: $('select[name="utenti3"] option:selected').val()},
                            type: "POST",
                            success: function (risposta) {
                                eval(risposta)
                            }

                        });
                    });
                })


            </script>
        </head>
        <body>


            <div class="container-fluid"> 

                <?php menu(); ?>
                <h1>ORDINE DA CLIENTE</h1>

                <form name="form1" method="post" action="./ordine_da_cliente.php">

                    <input type="hidden" name="ddt_content">
                    <input type="hidden" name="fattura_content">

                    <label for="barcode"><b>Codice a barre</b></label>
                    <input class="form-control" type="text" name="barcode" >
                    <label for="negozi">Negozio:</label><?php select($_CONFIG['table_utenti']); ?>
                    <input class="btn btn-info"  type="submit" name="submit" value="Elenca prodotto">
                    <button class="btn btn-danger"   name="button">Cancella tutto</button>
                    <button class="btn btn-success"   name="carica">Carica in magazzino del cliente</button>
                    <button class="btn btn-warning"   name="salva_sessione">Salva sessione</button>
                    <button class="btn btn-success"   name="riprendi_sessione">Riprendi sessione</button>


                    <div id="dati_ddt" style="display: none; width: 100%; overflow:auto;">

                        <div id="logo" style="float:left;clear:both;"><img src='logo_bf.png' style='height:100px;width:auto;' /></div><div id="mittente_ddt" style="float:left;"></div><div id="destinatario_ddt"  style="margin-left:20px;float:left;"></div><div id="intestatario_fattura" style="float:right;"></div>
                        <div id="numero_ddt" style="float:left;clear:both;"></div><div id="data_ddt" style="margin-left:20px;float:left;"></div><div id="trasportatore_ddt" style="margin-left:20px;float:left;"></div><div id="codice_tracciatura" style="margin-left:20px;float:left;"></div>    

                    </div>

                    <?php
//fai la query dei prodotti in carico in magazzino basati su barcode
//in ogni offset di sessione es.$_SESSION[0][etc],$_SESSION[1][etc] memorizzi i dati delle tabelle
//fai un while per mostrarli tutti nella pagina
//al via, fai una query per ogni offset, sottrai i prodotti dal carico e metti in carico all'altro negozio (definito in un menu a tendina) e metti come causale Vendita ad affiliato


                    if (isset($_POST['salva_sessione'])) {
                        memorizza_sessione();
                    }
                    if (isset($_POST['riprendi_sessione'])) {
                        recupera_sessione();
                    }

                    if (isset($_POST['carica'])) {
                        $i = 0;
                        //var_dump($_SESSION['articolo']);


                        if ($_POST[$_CONFIG['table_utenti']] != $user['nome_negozio']) {
                            while (isset($_POST['quantita' . $i])) {
                                if ($_POST['quantita' . $i] >= 0) {
                                    $query = "INSERT INTO elenco_movimenti (attivo,costo_aziendale,numero_ddt,data, causale, codice,sconto_affiliato, sconto_pubblico, quantita,prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,fornitore,cliente,giacenza_minima) VALUES ('" . $_SESSION['articolo'][$i]['attivo'] . "','" . $_SESSION['articolo'][$i]['costo_aziendale'] . "','" . $_SESSION['numero_ddt2'] . "','" . date('Y-m-d H:i:s', strtotime('now')) . "','Vendita ad affiliato','" . $_SESSION['articolo'][$i]['codice'] . "','" . $_SESSION['articolo'][$i]['sconto_azienda'] . "','" . $_SESSION['articolo'][$i]['sconto_pubblico'] . "','" . $_POST['quantita' . $i] . "','" . $_SESSION['articolo'][$i]['prezzo_pubblico_unitario'] . "','" . $_SESSION['articolo'][$i]['descrizione'] . "','" . $_SESSION['articolo'][$i]['gruppo'] . "','" . $_SESSION['articolo'][$i]['colore'] . "','" . $_SESSION['articolo'][$i]['barcode'] . "','" . $user['nome_negozio'] . "','" . $_POST[$_CONFIG['table_utenti']] . "','" . $_POST['giacenza_minima' . $i] . "');";
                                    #echo "<br>".$query;
                                    $db_magazzino->query($query);
                                }
                                $i++;
                            }
                        }


                        //INSERIMENTO ddt
                        $query = "INSERT INTO ddt (anno,tipo,mittente,numero,data,ddt,negozio,codice_tracciatura) VALUES ('".date('Y')."','DDT','" . $user['nome_negozio'] . "','".numero_ddt()."','" . date('Y-m-d H:i:s', strtotime('now')) . "','" . utf8_encode($_POST['ddt_content']) . "','" . $_POST[$_CONFIG['table_utenti']] . "','" . $_POST['codice_tracciatura'] . "');";
                        //echo htmlentities($query);
                        //exit();
                        $db_magazzino->query($query);


                        //INSERIMENTO sessione vuota
                        $query = "INSERT INTO sessioni_salvate (sessione,negozio) VALUES ('','" . $user['nome_negozio'] . "';";
                        #echo "<br>".htmlentities($query);
                        $db_magazzino->query($query);


                        //Se fai 2 carichi nello stesso momento dallo stesso computer quello in sospeso si cancella
                        sleep(3);
                        registra_log();
                        session_unset();
                        session_destroy();
                        echo "<br><br>Dati caricati correttamente.<br>";
                    }

                    $rimanenze = array();
                    if (isset($_POST['button'])) {
                        registra_log();
                        session_unset();
                        session_destroy();
                    }



//echo var_dump($_SESSION['articolo']);
                    if (isset($_POST['submit']) || isset($_POST['ordina_tab'])) {

                        //var_dump($_SESSION['articolo']);
                        for ($i = 0; isset($_POST['quantita' . $i]); $i++) {
                            $_SESSION['articolo'][$i]['quantita_acquistata'] = $_POST['quantita' . $i];
                            $_SESSION['articolo'][$i]['giacenza_minima'] = $_POST['giacenza_minima' . $i];
                            if ($_POST['quantita' . $i] == "X")
                                unset($_SESSION['articolo'][$i]);
                        }

                        $pos;
                        if (isset($_POST['barcode']) && !empty($_POST['barcode'])) {
                            $_POST['barcode'] = substr($_POST['barcode'], 0, 12);

                            //echo "prova";
                            $rimanenze = rimanenze($user['nome_negozio'], $_POST['barcode'], NULL, NULL, NULL);
                            //var_dump($rimanenze[0]);

                            $id_art_uguale = verifica_esistenza_valore($_POST['barcode'], $_SESSION['articolo']);
                            //echo "Articolo uguale=".$id_art_uguale;

                            if ($id_art_uguale === false && strlen($_POST['barcode']) == 12 && strlen($rimanenze[0]['barcode']) == 12) {
                                $i = count($_SESSION['articolo']);
                                $_SESSION['articolo'][$i] = $rimanenze[0];
                                $_SESSION['articolo'][$i]['quantita_acquistata'] = 1;
                                echo "<script>beep();</script>";
                            } elseif ($id_art_uguale === 0 xor $id_art_uguale > 0) {
                                $_SESSION['articolo'][$id_art_uguale]['quantita_acquistata'] ++;
                                $pos = $id_art_uguale + 1;

                                echo "<script>beep();</script>";
                            } else {
                                echo "<script>beep_errato();</script>";
                            }
                        }
                    }

                    //echo "<br><br>Articolo ".$i."<br/><br/>".var_export($_SESSION['articolo'],true);

                    if (isset($_SESSION['articolo']))
                    //----------------//
                        $c1 = count($_SESSION['articolo']);
                    else
                        $c1 = 0;
                    //---------------//
                    $i = 0;

                    $_SESSION['table'] = "<br><table class=\"table table-striped table-hover sortable\">
                        <tr id=\"intestazione\"><th class=\"attenzione\">id</th><th class=\"fattura\">Barcode</th><th>Codice</th><th class=\"fattura\">Descrizione</th>
						<th class=\"fattura\">Gruppo</th><th class=\"fattura\">Colore</th><th class=\"attenzione\">Disponibilita max</th><th class=\"fattura\">Quantita da inviare</th><th class=\"attenzione\">Giacenza minima</th><th class=\"fattura\">Costo uni.</th></tr>";

                    //echo "<p>".$c1."</p>";
                    //var_dump($_SESSION['articolo']);
                    while ($c1 > 0 && $c1 > $i && $c1 != NULL && $_SESSION['articolo']) {
                        $num = $c1 - 1;
                        $_SESSION['table'].= "<tr id=\"bianco\">";
                        $_SESSION['table'].= "<td class=\"attenzione\">" . $_SESSION['articolo'][$num]['id'] . "</td>";
                        $_SESSION['table'].= "<td class=\"fattura\">" . $_SESSION['articolo'][$num]['barcode'] . "</td>";
                        $_SESSION['table'].= "<td>" . $_SESSION['articolo'][$num]['codice'] . "</td>";
                        $_SESSION['table'].= "<td class=\"fattura\">" . $_SESSION['articolo'][$num]['descrizione'] . "</td>";
                        $_SESSION['table'].= "<td class=\"fattura\">" . $_SESSION['articolo'][$num]['gruppo'] . "</td>";
                        $_SESSION['table'].= "<td class=\"fattura\">" . $_SESSION['articolo'][$num]['colore'] . "</td>";
                        $_SESSION['table'].= "<td class=\"attenzione\">" . $_SESSION['articolo'][$num]['quantita'] . "</td>";
                        $_SESSION['table'].= "<td class=\"fattura\"><input class=\"form-control col-md-1\" type=\"text\" name=\"quantita" . $num . "\" value=\"" .
                        $_SESSION['articolo'][$num]['quantita_acquistata'] . "\"></td>";
                        $_SESSION['table'].= "<td class=\"attenzione\"><input class=\"form-control col-md-1\"  type=\"text\" name=\"giacenza_minima" . $num . "\" value=\"" . $_SESSION['articolo'][$num]['giacenza_minima'] . "\"></td>";
                        $_SESSION['table'].= "<td class=\"fattura\">" . number_format($_SESSION['articolo'][$num]['costo_aziendale'], 2) . "</td>";
                        $_SESSION['table'].= "</tr>";

                        $c1--;
                    }
                    $_SESSION['table'].="</table><br>";

                    echo $_SESSION['table'];


                    $i = count($_SESSION['articolo']);
                    for ($a = 0; $a < $i; $a++) {
                        $quantita_totale = $quantita_totale + $_SESSION['articolo'][$a]['quantita_acquistata'];
                        $costo_azienda = $costo_azienda + ($_SESSION['articolo'][$a]['costo_aziendale'] * $_SESSION['articolo'][$a]['quantita_acquistata']);
                        $costo_affiliato = $costo_affiliato + ($_SESSION['articolo'][$a]['prezzo_azienda_uni'] * $_SESSION['articolo'][$a]['quantita_acquistata']);
                    }

                    echo "<div id='pie_fattura'>"
                    . "Numero fattura: " . numero_fattura()
                    . "<br/>Imponibile: " . number_format($costo_azienda, 2) . " Euro"
                    . "<br/>+ IVA (" . IVA . "%): " . number_format($costo_azienda / 100 * IVA, 2) . " Euro"
                    . "<br/>Totale: <span id='totale_fattura'>" . number_format($costo_azienda / 100 * (100 + IVA), 2) . "</span> Euro"
                    . "<br/><br/>"
                    . "<button class='btn btn-danger btn-xs' id='btn_salva_fattura'>Salva fattura</button>"
                    . "<br/><br/>"
                    . "</div>";

                    echo "<script>"
                    . "$(\"#pie_fattura\").hide();"
                    . "</script>";
                    echo "<button class='btn btn-default btn-lg' onclick='event.preventDefault(); $(\"#pie_fattura\").toggle(\"display\");'>Switch fattura</button>";

                    echo "<div id=\"dati-finali\" style=\"position: absolute;
  top: 80px;
  clear: both;
  left: 450px;
  background-color:grey; border-radius:5px; padding:10px;\"><input type=\"text\"  style=\"font-weight:bold;font-size:25px;\" class=\"attenzione\" value=\"Articoli totali: " . $quantita_totale . "\" />"
                    . "<input type=\"text\" class=\"attenzione\"  value=\"Costo azienda: " . $costo_azienda . "\" />"
                    . "<input type=\"text\"  class=\"attenzione\"  value=\"Costo affiliato: " . $costo_affiliato . "\" /></div>";

                    //var_dump($_SESSION['articolo']);
                } else
                    non_autorizzato();

                $time_end = getmicrotime(); //sec finali
                $time = $time_end - $time_start; //differenza in secondi
                echo "<div class=\"attenzione\"><br/></br>Funzione: $time secondi.<br/></div>";

                echo "<script> $('tr:nth-last-child($pos)').css('background-color','magenta');</script>";

                function getmicrotime() {
                    list($usec, $sec) = explode(" ", microtime());
                    return ((float) $usec + (float) $sec);
                }
                ?>
            </form>
        </div>
    </body>
</html>
