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
            //void
            var sconto = function () {
            };
            //Genera la select gruppi da utilizzare poi in js
            var gruppi = '<?php select("gruppi", 2); ?>';
            //Genera la select colori da utilizzare poi in js
            var colori = '<?php select("colori"); ?>';
            //IMPOSTA IL CONTENUTO DEL PANNELLO DI BASE
            var riga_schema = '<div class="row clearfix" >\n\
                                                    <div class="col-md-12" style="background-color:silver;padding:10px;">\n\
                                                        <div class="col-md-4">\n\
                                                            ' + gruppi + '\n\
                                                        </div>\n\
                                                        <div class="col-md-4">\n\
                                                            ' + colori + '\n\
                                                        </div>\n\
                                                        <div class="col-md-2">\n\
                                                            <input name="quantita" class="form-control quantita" value="0" type="number" placeholder="Quantità"></input>\n\
                                                        </div>\n\
                                                        <div class="col-md-2">\n\
                                                            <h4><a class="elimina_riga_gruppo"><b>X</b></a></h4>\n\
                                                        </div>\n\
                                                    </div>\n\
                                                </div>';
            //Permette di catturare l'evento, anche dopo averlo generato dinamicamente
            $(document).on('click', '.elimina_pannello', function () {
                $(this).closest('.pannello').remove();
                console.log($(this).closest('.pannello'));
            });
            $(document).on('click', '.elimina_riga_gruppo', function () {
                $(this).closest('.row.clearfix').remove();
            });
            $(document).on('change', '.quantita', function () {

                //imposta tutti i val come value (non so cosa ci sia di diverso però è così)
                $('.quantita').each(
                        function () {
                            $(this).attr('value', $(this).val());
                        });
                //testa sia la quantità appena cambiata, che tutte le altre inserite
                if ($(this).val() > 0 && $('.quantita[value="0"]').attr('value') === undefined)
                {
                    //AGGIUNGE IL PANNELLO                      
                    $(this).closest('.row.clearfix').parent().append(riga_schema);
                }
            }
            );
            //FUNZIONI DOPO IL CARICAMENTO DEL DOCUMENTO
            $(document).ready(function () {

                $('#btn_conferma_modifiche').click(
                        function () {

                            var array_gruppi = new Array();
                            var array_colori = new Array();
                            var array_quantita = new Array();
                            var array_pannello = new Array();
                            var array_tutto = new Array();


                            $('#lista_pannelli .row.clearfix').not('#lista_pannelli .row.clearfix:first-child').each(
                                    function () {
                                        array_gruppi.push($(this).find('[name="gruppi"]').val());
                                        array_colori.push($(this).find('[name="colori"]').val());
                                        array_quantita.push($(this).find('[name="quantita"]').val());
                                        array_pannello.push($(this).parent().find('.titolo_pannello').html());
                                        console.log($(this).parent().find('.titolo_pannello').html());
                                    });

                            array_tutto[0] = array_gruppi;
                            array_tutto[1] = array_colori;
                            array_tutto[2] = array_quantita;
                            array_tutto[3] = array_pannello;

                            console.log(array_tutto);

                            $.ajax({
                                type: 'POST',
                                url: 'carica_pannelli_ajax.php',
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
                $('#btn_nuovo_pannello').click(
                        function () {
                            if ($('#nome_nuovo_pannello').val().length > 0) {
                                var pannello_schema = '<div class="row clearfix" >\n\
                                                        <div class="col-md-10">\n\
                                                            <h3 class="titolo_pannello">' + $('#nome_nuovo_pannello').val() + '</h3>\n\
                                                        </div>\n\
                                                        <div class="col-md-2">\n\
                                                            <h3><a class="elimina_pannello">ELIMINA</a></h3>\n\
                                                        </div>\n\
                                                    </div>';
                                //AGGIUNGE IL PANNELLO                      
                                $('#lista_pannelli').prepend('<div class="pannello">' + pannello_schema + riga_schema + '</div>');
                            }
                        }
                );
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

            <div class="row clearfix" id="form_pannelli">
                <div class="col-md-12">
                    <h1>GESTIONE PANNELLI</h1>
                </div>                   
            </div>
            <div class="row clearfix">
                <div class="col-md-3">
                    <input class="form-control" type="text" id="nome_nuovo_pannello" placeholder="Nome nuovo pannello"></input>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-info form-control" id="btn_nuovo_pannello">Crea nuovo</button>
                </div>    
                <div class="col-md-3">
                    <button class="btn btn-success form-control" id="btn_conferma_modifiche">Conferma modifiche</button>
                </div>    
            </div>   

            <form class="form-group" id="dati_post_pannelli" method="POST">
                <div id="lista_pannelli">
                    <!--RIEMPIMENTO CON PHP/JS -->


                    <?php
                    $query = "SELECT nome FROM pannelli WHERE 1 group by nome";

                    $risultato = $db_magazzino->query($query);

                    while ($row = $risultato->fetch_assoc()) {

                    echo '<div class="pannello"> 
                            <div class="row clearfix" >
                                <div class="col-md-10">
                                    <h3 class="titolo_pannello">' . $row['nome'] . '</h3>
                                </div>
                            <div class="col-md-2">
                                <h3><a class="elimina_pannello">ELIMINA</a></h3>
                            </div>
                        </div>';


                    $query2 = "SELECT * FROM pannelli WHERE nome='".$row['nome']."' order by gruppo";

                    $risultato2 = $db_magazzino->query($query2);

                    while ($row2 = $risultato2->fetch_assoc()) {

                    //echo var_export($row2, true) . "<br/><br/>";

                    echo '<div class="row clearfix" >
                                                    <div class="col-md-12" style="background-color:silver;padding:10px;">
                                                        <div class="col-md-4">
                                                            <input type="text" readonly="readonly" class="form-control" name="gruppi" value="' .$row2['gruppo'] . '">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" readonly="readonly" class="form-control" name="colori" value="' .$row2['colore'] . '">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input name="quantita" class="form-control quantita" value="' .$row2['quantita'] . '" type="number" placeholder="Quantità"></input>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h4><a class="elimina_riga_gruppo"><b>X</b></a></h4>
                                                        </div>
                                                    </div>
                                                </div>';
                    }

                    //echo "FINE PANNELLO<br/><br/>";
                    echo '</div>';
                    }

                    //echo "FINE PAGINA<br/><br/>";
                    ?>


                </div>
            </form>
        </div>
        <?php 
        }
        ?>
    </body>

</html>
