<?php error_reporting(E_ALL); ?>
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
            function print_fattura(number) {
                $('ul.menu').hide();
                $('div.fattura_stampabile').hide();
                $('h1').hide();
                $('a').hide();
                $('div.fattura_stampabile:nth(' + (number - 1) + ')').show();
                window.print();
                $('a').show();
                $('h1').show();
                $('div.fattura_stampabile').show();
                $('ul.menu').show();
            }
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

            echo "<h1>ELENCO DEI DOCUMENTI</h1>";

            


            #RESTITUZIONI

            if (license_has($user, "sede_centrale"))
                $risultati_ddt = $db_fatture->query("SELECT * FROM ddt WHERE tipo='RESTITUZIONE' ORDER BY id DESC;");
            else
                $risultati_ddt = $db_fatture->query("SELECT * FROM ddt WHERE tipo='RESTITUZIONE' AND negozio='" . $user['nome_negozio'] . "' ORDER BY id DESC;;");

            echo "<table style='border:1px solid black; margin-top:30px; background-color:white;'>";

            for ($i = 0; $ddt = $risultati_ddt->fetch_assoc(); $i++) {
                echo "<tr style='border:1px solid black;'><td style='padding:10px;'>";

                if ($ddt['visualizzato'] == 0) {
                    echo "(NUOVA!) ";
                }

                echo "<a href=./visualizza_ddt.php?id=" . $ddt['id'] . ">RESTITUZIONE numero " . $ddt['numero'] . " del " . $ddt['data'] . " da " . $ddt['negozio'] . "</a></td></tr>";
            }
            echo "</table>";
        } else
            non_autorizzato();
        ?>
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
