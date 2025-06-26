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

            #ORDINI

            if (license_has($user, "sede_centrale")) {
                $query = "SELECT * FROM db_ordini ORDER BY id DESC;";
            } else {
                $query = "SELECT * FROM db_ordini WHERE negozio = '" . $user['nome_negozio'] . "' ORDER BY id DESC;";
            }

            $risultato = $db_fatture->query($query);

            echo "<table style='margin-bottom:15px; border:1px solid black; background-color:white;'>";
            while ($ordine = $risultato->fetch_assoc()) {
                //var_dump($fatture);
                echo "<tr style='border:1px solid black;'><td style='padding:10px;'>";
                
                if ($ordine['visualizzato'] == 0) {
                    echo "(NUOVO!) ";
                }
                echo "<a href='./mostra_ordine.php?id=" . $ordine['id'] . "'>ORDINE DI MATERIALE del " . $ordine['data'] . " da " . $ordine['negozio'] . "</a></td></tr>";
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
