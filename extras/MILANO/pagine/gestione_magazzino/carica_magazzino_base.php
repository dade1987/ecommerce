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

        <script>
            $(document).ready(function () {

                $('#btn_conferma_modifiche').click(
                        function () {

                            var array_pannello = new Array();
                            var array_negozio = new Array();
                            var array_opzione = new Array();
                            var array_tutto = new Array();


                            $('.nome_pannello').each(
                                    function () {
                                        array_pannello.push($(this).html());
                                        array_negozio.push($(this).closest('.negozio').find('.nome_negozio').html());
                                        array_opzione.push($(this).parent().parent().find('input[type="checkbox"]:checked').val());
                                    });

                            array_tutto[0] = array_pannello;
                            array_tutto[1] = array_negozio;
                            array_tutto[2] = array_opzione;

                            console.log(array_tutto);

                            $.ajax({
                                type: 'POST',
                                url: 'carica_magazzino_base_ajax.php',
                                data: {array: array_tutto},
                                dataType: 'json',
                                cache: false,
                                timeout: 7000,
                                success: function (data) {
                                    // se ha successo
                                },
                                error: function (XMLHttpRequest, textStatus, errorThrown) {
                                    //se ci sono errori
                                },
                                complete: function (XMLHttpRequest, status) {
                                    //in ogni caso
                                }
                            });
                        });
            });
        </script>
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
                    <div class="col-md-10">
                        <h1>CARICA MAGAZZINO BASE</h1>
                    </div>    
                    <div class="col-md-2">
                        <button class="form-control btn btn-success" id="btn_conferma_modifiche">Conferma modifiche</button>
                    </div>
                </div>

                <!-- riempimento php con crocette già inserite --> 

                <?php
                //NEGOZI
                $query = "SELECT nome_negozio FROM utenti3 order by nome_negozio asc;";
                //echo $query;
                $risultato = $db_magazzino->query($query);
                while ($row = $risultato->fetch_assoc()) {
                    //echo var_export($row, true);

                    echo '  <div class="negozio">';
                    echo '  <div class="row clearfix">                
                                <div class="col-md-10">
                                    <h3 class="nome_negozio">' . $row['nome_negozio'] . '</h3>
                                </div>
                            </div>';

                    $query2 = "SELECT pannelli.nome,magazzino_base.bool FROM pannelli LEFT JOIN magazzino_base ON pannelli.nome=magazzino_base.pannello and magazzino_base.negozio='".$row['nome_negozio']."' group by pannelli.nome ; ";
                    //echo $query2;
                    $risultato2 = $db_magazzino->query($query2);
                    while ($row2 = $risultato2->fetch_assoc()) {
                        //echo var_export($row2, true);
                        //LISTA PANNELLI (SI PUO PRENDERE ANCHE UNA SOLA VOLTA LA VARIABILE PER OTTIMIZZARE)                        
                        echo '  <div class="row clearfix" >
                                    <div class="col-md-12" style="background-color:silver;">
                                        <div class="col-md-10">
                                            <h4 class="nome_pannello">' . $row2['nome'] . '</h4>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="checkbox" value="checked" class="form-control"' . $row2['bool'] . '>
                                        </div>                                                    
                                    </div> 
                                </div>';
                    }
                    echo '  </div>';
                }
                ?>


            </div>

        <?php } ?>

    </body>

</html>
