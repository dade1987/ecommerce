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
            $(document).ready(function () {
                $('.attenzione').hide();
            });

            function stampa_bolla() {
                $('.menu').hide();
                $('div.no_stampa').hide();
                $('input').css("padding-top", "0");
                $('input').css("padding-bottom", "0");
                $('table td').css("padding-top", "0");
                $('table td').css("padding-bottom", "0");
                $('table').css("font-size", "10px");
                $('#trasportatore_ddt').hide();
                $('#codice_tracciatura').hide();
                window.print();
                $('.menu').show();
                $('div.no_stampa').show();
            }
            function stampa() {
                $(".menu").hide();
                $(".attenzione").hide();
                $("button").hide();
                $('input').css("padding-top", "0");
                $('input').css("padding-bottom", "0");
                
                $('input').css("height", "initial");
                $('input').css("font-size", "initial");
                $('input').css("border", "initial");
                $('#logo').css("margin-right", "20px");
                
                $('table td').css("padding-top", "0");
                $('table td').css("padding-bottom", "0");
                $('table').css("font-size", "10px");
                $('#intestatario_fattura').hide();
                
                $('#trasportatore_ddt').hide();
                $('#codice_tracciatura').hide();
                window.print();
                $(".attenzione").show();
                $(".menu").show();
                $("button").show();
            }
            ;

        </script>

    </head>

    <body>

        <?php
        include_once("../../classi/utils.lib.php");

        include_once("../../classi/license.lib.php");

        include_once("../../classi/funzioni.php");

        include_once("../../classi/config.php");

        include_once("../../classi/auth.lib.php");



        menu();



        list($status, $user) = auth_get_status();



        if ($status == AUTH_LOGGED) {

            if (!license_has($user, "sede_centrale")) {
                $fattura = $db_fatture->query("UPDATE ddt SET visualizzato=1 WHERE id='" . htmlentities($_GET['id']) . "'");
            }

            $fattura = $db_fatture->query("SELECT * FROM ddt WHERE id='" . htmlentities($_GET['id']) . "'");

            $fattura = $fattura->fetch_assoc();

            $fattura = $fattura['ddt'];

            echo "<div class='stampa'>";
            echo $fattura;
            echo "</div>";
            echo "<br><br><button class='btn btn-success' value='Stampa' onClick='stampa();'>STAMPA DOCUMENTO</button>";
        } else
        //Pagina per chi non è autorizzato	
            non_autorizzato();
        ?>

    </body>