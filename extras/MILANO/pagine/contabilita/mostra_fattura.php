<html>
    <head>
        <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

        <script>
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

            /*function print_fattura() {
                $('#tawkchat-iframe-container').hide();
                $("nav").hide();
                $("td.fattura").css("font-size", "11px");
                $("tr#reso.restituzione").html('&nbsp;');
                $(".a4").css("margin-bottom", "55px");
                var child = $(".a4").length;
                $(".a4:nth-child(" + child + ")").css("margin-bottom", "0");
                $('.table>tbody>tr>td').css("padding-top", "0");
                $('.table>tbody>tr>td').css("padding-bottom", "0");
                $(".menu").hide();
                //$(".ddt").not('.fattura').remove();
                $("p").hide();
                $("h2").hide();
                $("form").hide();
                $("input").height(10);
                $('#dati_ddt').children().not('#mittente_ddt, #intestatario_fattura').hide();
                $("table tr td").hide();
                $("table tr td.fattura").show();
                $("table tr th").hide();
                $("table tr th.fattura").show();
                $("div.fattura").children().show();
                $('button').hide();

                window.print();
            }
            */
        </script>
        <style>
            tr#reso{
                background-color:red;
            }

            @media print {

                [class*="col-md-"] {
                    float: left;
                }        
            }
        </style>
    </head>
    <body>
        <?php
        include_once("../../classi/utils.lib.php");
        include_once("../../classi/license.lib.php");
        include_once("../../classi/funzioni.php");
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED) {
            menu();
            
            $fattura = $db_fatture->query("UPDATE db_fatture SET visualizzato=1 WHERE id='" . htmlentities($_GET['id']) . "'");



            $fattura = $db_fatture->query("SELECT * FROM db_fatture WHERE id='" . htmlentities($_GET['id']) . "'");

            $fattura = $fattura->fetch_assoc();

            $fattura = $fattura['fattura'];

            echo $fattura;
            echo '<br/><button class="btn btn-default" name="stampa" onclick="print_fattura();">Stampa fattura</button>';
        } else
        //Pagina per chi non è autorizzato	
            non_autorizzato();
        ?>

    </body>