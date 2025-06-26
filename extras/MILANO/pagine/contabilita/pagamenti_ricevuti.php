<html>
    <head>
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
        include_once("../../classi/utils.lib.php");
        include_once("../../classi/license.lib.php");
        include_once("../../classi/funzioni.php");
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");


        menu();?>
        
        <div class="container-fluid">
        <?php
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED) {
            if (!empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    #$key è il numero della fattura
                    #$value è il prezzo

                    $query = "UPDATE db_fatture SET importo_pagato='$value' WHERE numero_fattura='$key' LIMIT 1;";
                    #echo $query; 
                    $db_fatture->query($query);
                }
            }

            echo "<h1>PAGAMENTI RICEVUTI</h1>";

            if (license_has($user, "sede_centrale")) {
                $query = "SELECT * FROM " . $_CONFIG['table_utenti'] . ";";
            } else {
                $query = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $user['nome_negozio'] . "';";
            }

            $negozio = $db_magazzino->query($query);

            echo "<form method='POST'>";
            echo "<table style='width:100%;'>";
            echo "<tr style='background-color:aquamarine;font-weight:bold;'><td>Data</td><td>Ft.n.</td><td>Cliente</td><td>Importo</td><td>Pagato</td><td>Residuo</td></tr>";
            foreach ($negozio as $key => $value) {
                $query1 = "SELECT * FROM db_fatture WHERE (intestatario='" . $value['nome_negozio'] . "' OR intestatario='" . $value['nome_sede_legale'] . "')  ORDER BY numero_fattura DESC;";
                $fattura = $db_fatture->query($query1);

                if ($fattura->num_rows > 0) {
                    foreach ($fattura as $key => $value) {
                        echo "<tr style='background-color:white;'><td>" . $value['data'] . "</td><td>" . $value['numero_fattura'] . "</td><td class='cliente'>" . $value['intestatario'] . "</td><td class='importo'>" . str_replace(",", "", $value['totale_fattura']) . "</td><td>";
                        if (license_has($user, "sede_centrale")) {
                            echo "<input class='form-control pagato' name='" . $value['numero_fattura'] . "' type='text' onchange='calcola_residuo();' value='" . @$value['importo_pagato'] . "' />";
                        } else {
                            echo "<input class='form-control pagato'  readonly name='" . $value['numero_fattura'] . "' type='text' onchange='calcola_residuo();' value='" . @$value['importo_pagato'] . "' />";
                        }

                        echo "</td><td class='residuo'></td></tr>";
                    }
                    echo "<tr style='background-color:beige;'><td></td><td></td><td>Totale cliente</td><td class='totale_importi_" . $value['intestatario'] . "'></td><td class='totale_pagati_" . $value['intestatario'] . "'></td><td class='totale_residuo_" . $value['intestatario'] . "'></td></tr>";
                }
            }
            echo "<tr style='background-color:white;'><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>";
            echo "<tr style='background-color:white;'><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>";

            echo "<tr style='background-color:beige;'><td></td><td></td><td>Totale</td><td></td><td></td><td class='totale'></td></tr>";
            echo "</table>";
            echo "<br/><br/>";
            echo "<button type='submit'>Memorizza i pagamenti</button>";
            echo "</form>";?>
        </div> 
        <?php
            
        } else {
            non_autorizzato();
        }
        ?>
        <script>
            $(document).ready(function () {
                calcola_residuo();
            });
            function calcola_residuo()
            {
                var totale = 0.0;
                $('.pagato').each(
                        function () {
                            var pagato = $(this).val();
                            var importo = $(this).parent().parent().find($('td.importo'));
                            var residuo = $(this).parent().parent().find($('td.residuo'));


                            if (importo.html().length !== 0)
                            {
                                residuo.html((parseFloat(importo.html()) - parseFloat(pagato)).toFixed(2));
                            }

                            if (residuo.html().length !== 0)
                            {
                                totale = (parseFloat(totale) + parseFloat(residuo.html())).toFixed(2);
                            }


                        });

                $("[class^='totale_importi_']").each(
                        function () {
                            var negozio = $(this).attr('class').substr(15);

                            var totale_importi = 0.0;
                            $("td:contains('" + negozio + "')").parent().find($('td.importo')).each(function () {
                                if ($(this).html().length !== 0)
                                {
                                    totale_importi = (parseFloat(totale_importi) + parseFloat($(this).html())).toFixed(2);
                                    //console.log(totale_importi);
                                }
                            });
                            $("[class^='totale_importi_" + negozio + "']").html(totale_importi);

                            var totale_residui = 0.0;
                            $("td:contains('" + negozio + "')").parent().find($('td.residuo')).each(function () {
                                if ($(this).html().length !== 0)
                                {
                                    totale_residui = (parseFloat(totale_residui) + parseFloat($(this).html())).toFixed(2);
                                    //console.log("Residuo: "+$(this).html());
                                }
                            });
                            $("[class^='totale_residuo_" + negozio + "']").html(totale_residui);

                            var totale_pagati = 0.0;
                            //console.log(typeof (totale_pagati));
                            $("td:contains('" + negozio + "')").parent().find($('td>input.pagato')).each(function () {
                                if ($(this).val().length !== 0)
                                {
                                    totale_pagati = (parseFloat(totale_pagati) + parseFloat($(this).val())).toFixed(2);
                                }
                            });
                            $("[class^='totale_pagati_" + negozio + "']").html(totale_pagati);
                        });

                $('.totale').html(totale);
            }
        </script>
        <!--Start of Tawk.to Script-->
<script type="text/javascript">
var $_Tawk_API={},$_Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/559ba55c04c33fb6400d686d/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
    </body>
</html>