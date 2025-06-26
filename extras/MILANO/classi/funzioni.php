<?php

//Includo le classi necessarie

require_once("config.php");

require_once("auth.lib.php");

require_once("utils.lib.php");

require_once("license.lib.php");

//MENU

function menu() {
    global $db_fatture;

    $menu_centrale = array(
        "Articolo nuovo" => url . "/pagine/gestione_magazzino/ins_articolo.php",
        "Buono spesa" => url . "/pagine/gestione_magazzino/nuovo_buono.php",
        "Carica magazzino base" => url . "/pagine/gestione_magazzino/carica_magazzino_base.php",
        "Carica pannelli" => url . "/pagine/gestione_magazzino/carica_pannelli.php",
        "Carichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Carichi",
        "DDT" => url . "/pagine/contabilita/elenco_ddt.php",
        "Elenco clienti" => url . "/pagine/gestione_magazzino/elenco_clienti.php",
        "Elenco fidelity" => url . "/pagine/marketing/elenco_fidelity_card.php",
        "Email promozionali" => url . "/pagine/marketing/invia_email_promozionali.php",
        "Fattura vuota" => url . "/pagine/contabilita/fattura_personalizzata.php",
        "Fatture" => url . "/pagine/contabilita/elenco_fatture_emesse.php",
        "Impostazioni countdown" => url . "/pagine/contabilita/impostazioni_countdown.php",
        "Modifica tabelle" => url . "/pagine/gestione_magazzino/modifica_gruppi_colori.php",
        "Nota di credito" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=NotaCredito",
        "Nota di credito vuota" => url . "/pagine/contabilita/nc_personalizzata.php",
        "Numerazione documenti" => url . "/pagine/contabilita/modifica_numerazione.php",
        "Nuova fidelity" => url . "/pagine/marketing/ins_mod_fidelity_card.php",
        "Nuovo cliente" => url . "/pagine/gestione_magazzino/ins_mod_cliente.php",
        "Nuovo colore" => url . "/pagine/gestione_magazzino/ins_colore.php",
        "Nuovo gruppo" => url . "/pagine/gestione_magazzino/ins_gruppo.php",
        "Nuovo materiale" => url . "/pagine/gestione_magazzino/ins_materiale.php",
        "Ordine cliente" => url . "/pagine/gestione_magazzino/ordine_da_cliente.php",
        "Ordini confezionamento" => url . "/pagine/contabilita/elenco_ordini_confezionamento.php",
        "Ordine da giacenza" => url . "/pagine/gestione_magazzino/ordine_da_giacenza.php",
        "Ordine da venduto" => url . "/pagine/gestione_magazzino/ordine_da_venduto.php",
        "Pagamenti" => url . "/pagine/contabilita/pagamenti_ricevuti.php",
        "Restituzioni" => url . "/pagine/contabilita/elenco_restituzioni.php",
        "Rimanenze" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Rimanenze",
        "Scarichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi",
        "Statistiche" => url . "/pagine/statistiche/statistiche.php",
        "Statistiche Gruppi Colori" => url . "/pagine/statistiche/venduti_gruppi_colori.php",
        "Tutte le vendite" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Tutti_Scarichi",
        "Tutti i carichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Tutti_Carichi",
        "Logout" => url . "/logout.php"
    );


    $menu_affiliato = array(
        "Buono spesa" => url . "/pagine/gestione_magazzino/nuovo_buono.php",
        "Carichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Carichi",
        "Cassa" => url . "/pagine/contabilita/cassa.php",
        "Cassa EXXTRA" => url . "/pagine/contabilita/cassa_exxtra.php",
        "DDT" => url . "/pagine/contabilita/elenco_ddt.php",
        "Elenco fidelity" => url . "/pagine/marketing/elenco_fidelity_card.php",
        "Fattura" => url . "/pagine/contabilita/fattura_affiliato.php",
        "Fatture" => url . "/pagine/contabilita/elenco_fatture_emesse.php",
        "Inserimento reso" => url . "/pagine/gestione_resi/inserimento_reso.php",
        "Nuova fidelity" => url . "/pagine/marketing/ins_mod_fidelity_card.php",
        "Ordina confezioni" => url . "/pagine/contabilita/ordina_confezioni.php",
        "Ordini confezionamento" => url . "/pagine/contabilita/elenco_ordini_confezionamento.php",
        "Pagamenti" => url . "/pagine/contabilita/pagamenti_ricevuti.php",
        "Restituzioni" => url . "/pagine/contabilita/elenco_restituzioni.php",
        "Rimanenze" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Rimanenze",
        "Scarichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi",
        "Statistiche" => url . "/pagine/statistiche/statistiche.php",
        "Logout" => url . "/logout.php"
    );

    $menu_portoferraio = array(
        "Articoli PortoFerraio" => url . "/pagine/gestione_magazzino/articoli_portoferraio.php",
        "Buono spesa" => url . "/pagine/gestione_magazzino/nuovo_buono.php",
        "Carichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Carichi",
        "Cassa" => url . "/pagine/contabilita/cassa.php",
        "Cassa EXXTRA" => url . "/pagine/contabilita/cassa_exxtra.php",
        "DDT" => url . "/pagine/contabilita/elenco_ddt.php",
        "Elenco fidelity" => url . "/pagine/marketing/elenco_fidelity_card.php",
        "Fattura" => url . "/pagine/contabilita/fattura_affiliato.php",
        "Fatture" => url . "/pagine/contabilita/elenco_fatture_emesse.php",
        "Inserimento reso" => url . "/pagine/gestione_resi/inserimento_reso.php",
        "Nuova fidelity" => url . "/pagine/marketing/ins_mod_fidelity_card.php",
        "Ordina confezioni" => url . "/pagine/contabilita/ordina_confezioni.php",
        "Ordini confezionamento" => url . "/pagine/contabilita/elenco_ordini_confezionamento.php",
        "Pagamenti" => url . "/pagine/contabilita/pagamenti_ricevuti.php",
        "Restituzioni" => url . "/pagine/contabilita/elenco_restituzioni.php",
        "Rimanenze" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Rimanenze",
        "Scarichi" => url . "/pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi",
        "Statistiche" => url . "/pagine/statistiche/statistiche.php",
        "Logout" => url . "/logout.php"
    );

    list($status, $user) = auth_get_status();

    if (license_has($user, "sede_centrale")) {

        $array_menu = $menu_centrale;
    }


    if (license_has($user, "affiliato")) {
        if ($user['nome_negozio'] === "BLACK FASHION PORTOFERRAIO") {
            $array_menu = $menu_portoferraio;
        } else {
            $array_menu = $menu_affiliato;
        }
    }

    //sort($array_menu);

    echo '<div class="row">
                <nav class="navbar navbar-default">
                    <div class="container-fluid">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Menu<span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">';

    foreach ($array_menu as $testo => $link) {

        if ($testo === "DDT") {
            $estrazione_d = $db_fatture->query("SELECT visualizzato FROM ddt WHERE visualizzato=0 AND negozio='" . $user['nome_negozio'] . "' LIMIT 1;");
            $estrazione_d = $estrazione_d->fetch_assoc();


            if ($estrazione_d['visualizzato'] === "0") {
                echo "<li><a style='background-color:red;' href='$link'>$testo</a></li>";
            } else {
                echo "<li><a href='$link'>$testo</a></li>";
            }
        } else if ($testo === "Fatture") {

            $estrazione_f = $db_fatture->query("SELECT visualizzato FROM db_fatture WHERE visualizzato=0 AND (intestatario = '" . $user['nome_negozio'] . "' OR intestatario = '" . $user['nome_sede_legale'] . "') LIMIT 1;");
            $estrazione_f = $estrazione_f->fetch_assoc();


            if ($estrazione_f['visualizzato'] === "0") {
                echo "<li><a style='background-color:red;' href='$link'>$testo</a></li>";
            } else {
                echo "<li><a href='$link'>$testo</a></li>";
            }
        } elseif ($testo === "Ordini confezionamento") {

            $estrazione_o = $db_fatture->query("SELECT visualizzato FROM db_ordini WHERE visualizzato=0 AND intestatario = '" . $user['nome_negozio'] . "'  LIMIT 1;");
            $estrazione_o = $estrazione_o->fetch_assoc();

            if ($estrazione_o['visualizzato'] === "0") {
                echo "<li><a style='background-color:red;' href='$link'>$testo</a></li>";
            } else {
                echo "<li><a href='$link'>$testo</a></li>";
            }
        } else {
            echo "<li><a href='$link'>$testo</a></li>";
        }
    }

    echo '</ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>';
}

//GESTIONE MAGAZZINO
//Prende in input il costo totale e la percentuale da scontare, e calcola il prezzo finale scontato

function sconto($prezzo, $sconto) {

    $valore = $prezzo / 100 * $sconto;

    return $valore = $prezzo - $valore;
}

//Prende il prezzo imponibile in input, e genera la fattura

function iva($fattura) {

    $valore = $fattura / 100 * IVA;

    return $valore = $fattura + $valore;
}

//Creazione della tabella delle rimanenze

function tab_rimanenze($negozio, $barcode, $decrizione, $codice, $gruppo, $colore) {

    /* echo '<pre>';
      var_export($barcode, true);
      echo '</pre>'; */

//Memorizza lo status (AUTH_LOGGED) e il nome utente nelle variabili $status, $user 
//per mezzo della funzione auth_get_status();

    list($status, $user) = auth_get_status();



//memorizza un array con il contenuto della tabella delle rimanenze nella var $rimanenze

    $rimanenze = rimanenze($negozio, $barcode, $decrizione, $codice, $gruppo, $colore);



//Crea l'intestazione della tabella

    echo "<table class='table table-striped table-hover '>

			<tr id=\"intestazione\">

			<td>Descrizione</td>

			<td>Gruppo</td>

			<td>Colore</td>

			<td>Codice</td>

			<td>Quantita</td>

			<td>Barcode</td>

			<td>Prezzo azienda unitario</td>

			<td>Prezzo pubblico unitario</td>

			<td>Costo azienda tot.</td>

			<td>Prezzo pubblico tot.</td>

			<td>Sconto azienda tot.</td>

			<td>Sconto pubblico tot.</td>

			<td>Giacenza minima</td>

			</tr>";



//L'interatore i parte da 0, e finchè non raggiunge il numero degli elementi dell'array rimanenze 
//(-1 perchè il primo dell'array è 0) continua ad aumentare il valore di i e ri-esegue le istruzioni

    for ($i = 0; $i <= count($rimanenze) - 1; $i++) {

        //Funzione per fare le righe della tabella di colori diversi
        //se il valore di $i è un numero pari

        if ($i % 2 == 0)

        //la variabile colore diventa bianca altrimenti azzurrina
            $colore = 'bianco';
        else
            $colore = 'azzurrino';





        //Se il prodotto è minore della giacenza minima, e la giacenza minima è superiore a zero, evidenzia la riga di giallo

        if ($rimanenze[$i]['quantita'] < $rimanenze[$i]['giacenza_minima'] && $rimanenze[$i]['giacenza_minima'] > 0)
            echo "<tr id=\"sottogiacenza\">";
        else
            echo "<tr id=\"$colore\">";

        //Creo il resto della tabella

        echo

        "<td>" . $rimanenze[$i]['descrizione'] . "</td>" .
        "<td>" . $rimanenze[$i]['gruppo'] . "</td>" .
        "<td>" . $rimanenze[$i]['colore'] . "</td>" .
        "<td>" . $rimanenze[$i]['codice'] . "</td>" .
        "<td>" . $rimanenze[$i]['quantita'] . "</td>" .
        "<td>" . $rimanenze[$i]['barcode'] . "</td>" .
        "<td>" . $rimanenze[$i]['prezzo_azienda_uni'] . "&euro;</td>" .
        //"<td>".number_format($rimanenze[$i]['prezzo_pubblico_unitario'],2)."&euro;</td>".

        "<td>" . $rimanenze[$i]['prezzo_pubblico_unitario'] . "&euro;</td>" .
        "<td>" . $rimanenze[$i]['prezzo_azienda_tot'] . "&euro;</td>" .
        "<td>" . $rimanenze[$i]['prezzo_pubblico_tot'] . "&euro;</td>" .
        "<td>" . $rimanenze[$i]['sconto_azienda'] . "</td>" .
        "<td>" . $rimanenze[$i]['sconto_pubblico'] . "</td>" .
        "<td>" . $rimanenze[$i]['giacenza_minima'] . "</td>" .
        "</tr>";
    }



    //Chiude la tabella e la pagina

    echo

    "<tr id=\"intestazione\">";

    if (license_has($user, "sede_centrale"))
        echo "<td colspan=\"2\">Costo azienda</td>";

    echo "<td colspan=\"2\">Costo affiliato</td><td colspan=\"2\">Costo pubblico</td><td colspan=\"2\">Pezzi totali</td></tr><tr id=\"bianco\">";

    if (license_has($user, "sede_centrale"))
        echo "<td colspan=\"2\">" . $rimanenze[$i - 1]['costo_azienda'] . "</td>";
    echo "<td colspan=\"2\">" . $rimanenze[$i - 1]['costo_affiliato'] . "</td><td colspan=\"2\">" . $rimanenze[$i - 1]['incasso_effettivo'] . "</td><td colspan=\"2\">" . $rimanenze[$i - 1]['pezzi_totali'] . "</td></tr>
        
    </table>

	</body>

	</html>";
}

//Funzione per il calcolo delle rimanenze;

function rimanenze($negozio, $barcode, $descrizione, $codice, $gruppo, $colore) {

    global $db_magazzino, $array, $array2, $user;

    $array = $array2 = array();

    $i = 0;

    // Funzione che estrae tutti i barcode univocamente presenti in magazzino (senza sdoppiarli)

    $query2 = "SELECT DISTINCT (barcode) from elenco_movimenti WHERE 1  AND (cliente = '" . $negozio . "' OR fornitore LIKE '" . $negozio . "')";
    if (!empty($descrizione))
        $query2.=" AND descrizione LIKE'%" . $descrizione . "%'";

    if (!empty($codice))
        $query2.=" AND codice='" . $codice . "'";

    if (!empty($gruppo))
        $query2.=" AND gruppo='" . $gruppo . "'";

    if (!empty($colore))
        $query2.=" AND colore='" . $colore . "'";

    if (!empty($barcode))
        $query2.=" AND barcode LIKE '%" . $barcode . "%'";
    $query2.=" ORDER BY id DESC LIMIT 100;";

    #echo $query2."<br/>";

    $risultato2 = $db_magazzino->query($query2);

    for ($nb = 0; $prodotto = $risultato2->fetch_assoc(); $nb++) {
        $array2[$nb]['barcode'] = $prodotto['barcode'];
    }

    //var_dump($array2);
    $risultato2->close();

    //Il numero di codici presenti nell'array2 (conta i valori di array2)

    $valori_array = count($array2);

    #echo $valori_array;
    //Funzione per memorizzare le rimanenze all'interno di un array
    //Esegue questa funzione per ogni codice presente nell'array2

    for ($i = 0; $i < $valori_array; $i++) {

        //Definisco il contenuto della query

        $query2 = "SELECT * from elenco_movimenti WHERE 1  AND (cliente = '" . $negozio . "' OR fornitore LIKE '" . $negozio . "') AND barcode LIKE '%" . $array2[$i]['barcode'] . "%'";

        //La query non considera i resi, dato che non fanno parte delle rimanenze

        $query2.=" ORDER BY data ASC;";

        #echo $query2."<br/>";
        //Eseguo la query

        $risultato2 = $db_magazzino->query($query2);



        //Inizializzo l'interatore b senza inserirlo nel ciclo successivo (altrimenti diventerebbe 0 ad ogni ciclo)

        $b = 0;

        //Numero di chiavi di $array 

        $c2 = count($array);
        #echo $c2;
        //Finchè ci sono righe da leggere nella tabella, eseguo il ciclo
        //Le medesime righe verranno memorizzate nell'array prodotto2

        while ($prodotto2 = $risultato2->fetch_row()) {
            #echo "x"  ;
            //Riempio l'array con i dati della tabella

            $array[$c2]['id'] = $prodotto2[0];

            $array[$c2]['barcode'] = $prodotto2[4];

            $array[$c2]['codice'] = $prodotto2[3];

            $array[$c2]['descrizione'] = $prodotto2[5];

            $array[$c2]['gruppo'] = $prodotto2[6];

            $array[$c2]['colore'] = $prodotto2[7];

            $array[$c2]['prezzo_pubblico_unitario'] = number_format(sconto($prodotto2[8], $prodotto2[13]), 2);
            if ($array[$c2]['prezzo_pubblico_unitario'] < 0)
                $array[$c2]['prezzo_pubblico_unitario'] = $array[$c2]['prezzo_pubblico_unitario'] * -1;

            $array[$c2]['prezzo_azienda_uni'] = number_format(sconto($prodotto2[8], $prodotto2[12]), 2);
            if ($array[$c2]['prezzo_azienda_uni'] < 0)
                $array[$c2]['prezzo_azienda_uni'] = $array[$c2]['prezzo_azienda_uni'] * -1;

            $array[$c2]['sconto_azienda'] = $prodotto2[12];

            $array[$c2]['costo_aziendale'] = $prodotto2[27];

            $array[$c2]['sconto_pubblico'] = $prodotto2[13];

            $array[$c2]['giacenza_minima'] = $prodotto2[15];



            //Se l'iteratore $b=0 (ovvero è il primo ciclo che eseguo)

            if ($b == 0) {

                //memorizzo il valore della quantità inserita precedentemente

                $array[$c2]['quantita'] = $prodotto2[9];
            } else {

                //altrimenti sottraggo i valori seguenti
                //Se il cliente presente nella riga corrisponde al negozio dell'utente corrente
                #$prodotto2[11] == $negozio && $negozio == casa_madre  vecchia funzione
                if ($prodotto2[14] == '1' && $prodotto2[11] !== casa_madre && $user['nome_negozio'] !== casa_madre) { //RESO RIMANENZE && $prodotto2[14] != 1 cio� reso
                    $array[$c2]['negozio'] = $prodotto2[11];
                    $array[$c2]['quantita'] = $array[$c2]['quantita'] + $prodotto2[9];
                    #echo '+';
                } elseif ($prodotto2[14] == '1' && $prodotto2[11] === casa_madre) { //RESO RIMANENZE && $prodotto2[14] != 1 cio� reso
                    if ($user['nome_negozio'] == casa_madre) {
                        $array[$c2]['negozio'] = $prodotto2[11];
                        $array[$c2]['quantita'] = $array[$c2]['quantita'] + $prodotto2[9];
                    } else {
                        $array[$c2]['negozio'] = $prodotto2[11];
                        $array[$c2]['quantita'] = $array[$c2]['quantita'] - $prodotto2[9];
                    }
                    #echo '+';
                } elseif ($prodotto2[2] == "Vendita ad affiliato") {
                    $array[$c2]['quantita'] = $array[$c2]['quantita'] + $prodotto2[9];
                } else {
                    #echo '-';
                    $array[$c2]['quantita'] = $array[$c2]['quantita'] - $prodotto2[9];
                }
                #echo $prodotto2[2]."-".$array[$c2]['quantita']."<br/>";
            }


            $array[$c2]['attivo'] = $prodotto2[30];


            $array[$c2]['prezzo_pubblico_tot'] = number_format(sconto($array[$c2]['prezzo_pubblico_unitario'] * $array[$c2]['quantita'], $prodotto2[13]), 2);
            $array[$c2]['prezzo_azienda_tot'] = number_format(iva(sconto($array[$c2]['prezzo_azienda_uni'] * $array[$c2]['quantita'], $prodotto2[12])), 2);


            if ($c2 > 0) {
                $array[$c2]['valorizzazione_magazzino'] = number_format($array[$c2]['prezzo_azienda_tot'] + $array[$c2 - 1]['prezzo_azienda_tot'], 2);
            }

            //else
            //	$array[$c2]['valorizzazione_magazzino']=number_format($array[$c2]['valorizzazione_magazzino']
            //						+$array[$c2]['prezzo_azienda_tot'],2);
            //Aggiunge un unità all'iteratore b (quindi le variabili successive allo zero
            //verranno sommate o sottratte a seconda dei casi	

            $b++;
        }
        #echo $array[$c2]['prezzo_pubblico_tot']."<br/>";
        if ($c2 > 0) {
            $array[$c2]['pezzi_totali'] = $array[$c2 - 1]['pezzi_totali'] + $array[$c2]['quantita'];
            $array[$c2]['incasso_effettivo'] = $array[$c2 - 1]['incasso_effettivo'] + ($array[$c2]['prezzo_pubblico_unitario'] * $array[$c2]['quantita']);
            $array[$c2]['costo_affiliato'] = $array[$c2 - 1]['costo_affiliato'] + $array[$c2]['prezzo_azienda_tot'];
            $array[$c2]['costo_azienda'] = $array[$c2 - 1]['costo_azienda'] + ($array[$c2]['costo_aziendale'] * $array[$c2]['quantita']);
        }
        #var_dump($array[$c2]['prezzo_azienda_tot']);
        #var_dump($array[$c2]['prezzo_pubblico_tot']);
    }

    //Ritorna un array ordinato progressivamente per numero
    //contenente tutti i dati delle rimanenze (uno per numero di chiave)
    //consolelog($array);

    return $array;
}

function quantita_venduta($negozio_venditore, $gruppo, $colore, $data_da, $data_a) {

    global $db_magazzino;

    $query_venduto = "SELECT sum(quantita) as quantita FROM elenco_movimenti WHERE fornitore='$negozio_venditore' AND causale!='RESO' AND gruppo='$gruppo' AND colore='$colore' AND data >= '$data_da' AND data <= '$data_a' GROUP BY gruppo,colore;";
    //echo $query_venduto."\n";

    $risultato_venduto = $db_magazzino->query($query_venduto);

    $risultato_quantita_venduta = $risultato_venduto->fetch_assoc();

    $quantita_venduta = $risultato_quantita_venduta['quantita'];

    return $quantita_venduta;
}

function consolelog($qualsiasi) {
    echo "<script>$(document).ready(function(){console.log(" . json_encode($qualsiasi) . "); });</script>";
}

function numero_fattura() {
//numera la fattura in base all'ultimo numero inserito (da inserire in una funzione)

    global $db_fatture;

    list($status, $user) = auth_get_status();

    $ultimafattura = $db_fatture->query("SELECT numero_fattura FROM db_fatture WHERE negozio='" . $user['nome_negozio'] . "' AND anno= '" . date("Y") . "' ORDER BY numero_fattura DESC LIMIT 1;");
    $ultima_fattura = $ultimafattura->fetch_assoc();
    $numero_fattura = $ultima_fattura['numero_fattura'];

    if ($numero_fattura > 0) {
        $numero_fattura++;
    } else {
        $numero_fattura = 1;
    }

    return $numero_fattura;
}

function numero_ddt() {
//numera la fattura in base all'ultimo numero inserito (da inserire in una funzione)

    global $db_fatture, $db_magazzino;

    list($status, $user) = auth_get_status();

    $query = "SELECT id,numero from ddt WHERE mittente='" . $user['nome_negozio'] . "' and anno='" . date('Y') . "' ORDER BY numero DESC LIMIT 1;";
    $numero = $db_magazzino->query($query);


    $numero = $numero->fetch_assoc();
    $numero_ddt = $numero['numero'] + 1;

    return $numero_ddt;
}

function tab($query, $negozio) {
    global $db_magazzino, $db_fatture, $conn, $_CONFIG;

    //in questo caso è Scarichi
    $switch = $_GET['switch'];

    //Data è la data cioè 24-10-2015
    $data = date("d-m-Y");

    //fattura è l'imponibile che sarà calcolato dopo ma parte da 0
    $fattura = 0;

    //numero fattura è il numero fattura che si collega a una funzione esterna
    $numero_fattura = numero_fattura();


    //tabella fattura è quella conservata nel server che viene salvata quando faccio memorizza
    $_SESSION['tabella_fattura'] = "";

    //questo è per vedere (dopo ti dico)
    list($status, $user) = auth_get_status();

    //il numero di righe della fattura per non uscire di pagina (cioè 44)
    define("INTESTAZIONE", 46); #43 originale
    //se devo fare la fattura elenca solo gli articoli non personali dei negozi
    if (license_has($user, "sede_centrale")) {
        $query.=" AND escluso_fattura=0 ";
    }

    //Aggiunge ai dati della query presa in input questa stringa
    //alla query che gli ho passato aggiunge limite di 1000????
    $query.=" ORDER BY data DESC LIMIT 1500;";

    #echo $query;
    //quindi alla fine può elencare al massimo 1000 articoli
    //fa la richiesta al database con quella query
    $risultato = $db_magazzino->query($query);

    //vede quante righe ha estratto dal database
    $numero_righe = $risultato->num_rows;

    //calcola il numero di pagine (numero righe totali / 44 arrotondato per eccesso
    $numero_pagine = ceil($numero_righe / INTESTAZIONE);

    //linea è un array che contiene poi ogni riga che viene stampata
    $linea = array();

    //riempie tutte le linee con le linee del database uguali
    for ($i = 1; $linea[$i] = $risultato->fetch_row(); $i++)
        ;

    //calcola il numero dell'ultimo ddt (perchè? bho)
    $ultimoddt = $db_fatture->query("SHOW TABLE STATUS LIKE 'numerazione_ddt'");
    $ultimo_ddt = $ultimoddt->fetch_assoc();
    $numero_ddt = $ultimo_ddt['Auto_increment'];


    //per ogni pagina
    for ($ccc = 1; $ccc <= $numero_pagine; $ccc++) {

        //se sei la sede centrale come permessi
        if (license_has($user, "sede_centrale")) {

            //estrae i dati fiscali dell'intestatario
            $query = "SELECT nome_sede_legale, codice_fiscale_sede_legale, partita_iva_sede_legale, provincia_sede_legale, indirizzo_sede_legale, citta_sede_legale FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $negozio . "'";
            $dato = $conn->query($query);
            $dato = $dato->fetch_assoc();

            $p_iva = $dato['partita_iva_sede_legale'];
            $cf_sede_legale = $dato['codice_fiscale_sede_legale'];
            $nome_sede_legale = $dato['nome_sede_legale'];
            $indirizzo = $dato['indirizzo_sede_legale'];
            $citta = $dato['citta_sede_legale'];
            $provincia = $dato['provincia_sede_legale'];

            //Estrai i dati fiscali dell'emittente
            $query = "SELECT nome_sede_legale, codice_fiscale_sede_legale, partita_iva_sede_legale, provincia_sede_legale, indirizzo_sede_legale, citta_sede_legale FROM " . $_CONFIG['table_utenti'] . " WHERE nome_negozio='" . $user['nome_negozio'] . "'";

            $emittente = $conn->query($query);

            $riga = $emittente->fetch_assoc();

            //L'intestatario della fattura diventa BLACK FASHION VENEZIA
            $_SESSION['dati_fattura']['intestatario'] = $negozio;

            //SE E' UNA NOTA DI CREDITO O UNO SCARICO
            if ($switch === "NotaCredito" || $switch === "Scarichi") {

                //IMPOSTA LA PAGINA IN A4
                $_SESSION['tabella_fattura'].="<div class=\"fattura A4\">";


                //Crea l'intestazione della fattura
                $_SESSION['tabella_fattura'].= "
                    <div class='col-md-2 col-xs-4'>
                    <img src='logo_bf.png' style='height:100px;width:auto;' />
                    </div>
                    
                <div class='col-md-4 col-xs-4'>
		<table  class='table table-striped table-hover '>
		
		<tr id=\"intestazione\"><td  class=\"ddt fattura\" colspan=\"3\">Emittente</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">" . $riga['nome_sede_legale'] . "</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">" . $riga['indirizzo_sede_legale'] . " - " . $riga['citta_sede_legale'] .
                        "(" . $riga['provincia_sede_legale'] . ")</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" style=\"height:50px;\" colspan=\"3\">P.IVA " . $riga['partita_iva_sede_legale'] . " - C.F. " . $riga['codice_fiscale_sede_legale'] . "</td></tr>	

		</table>
                </div>
                <div class='col-md-4 col-xs-4'>
		<table    class='table table-striped table-hover  '>

		<tr  id=\"intestazione\"><td  class=\"ddt fattura\" colspan=\"3\">Intestatario</td></tr>

		<tr  id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">$nome_sede_legale</td></tr>

		<tr  id=\"azzurrino\"><td  class=\"ddt fattura\" colspan=\"3\">" . $indirizzo . " - " . $citta . "(" . $provincia . ")</td></tr>

		<tr  id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">P.IVA $p_iva - C.F. $cf_sede_legale</td></tr>

		</tr>
                

		";
            }
        }
        $_SESSION['tabella_fattura'].= "</table> </div>";


        //METTE I TITOLI SE E' UNO SCARICO O UNA NOTA DI CREDITO
        if (license_has($user, "sede_centrale")) {
            if ($switch === "Scarichi") {
                $_SESSION['tabella_fattura'].= "<div class='row fattura' style='padding-left:25px; margin-bottom:20px; clear:both;'>"
                        . "<h4><div class='col-md-3 col-md-offset-2 col-xs-3'>Fattura n. " . $numero_fattura . " </div><div class='col-md-3 col-xs-4'>Data:  " . $data . "</div><div class='col-md-3 col-xs-3'>Pag. " . $ccc . "/" . $numero_pagine . "</div></h4>"
                        . "</div>";
            } elseif ($switch === "NotaCredito") {
                $_SESSION['tabella_fattura'].= "<div class='row fattura' style='padding-left:25px; margin-bottom:20px;  clear:both;'>"
                        . "<h4><div class='col-md-3 col-md-offset-2 col-xs-3'>Nota di credito n. " . $numero_fattura . " </div><div class='col-md-3 col-xs-4'>Data:  " . $data . "</div><div class='col-md-3 col-xs-3'>Pag. " . $ccc . "/" . $numero_pagine . "</div></h4>"
                        . "</div>";
            }
        }


        //COMINCIA LA TABELLA DEGLI ARTICOLI VENDUTI IN STO CASO DA BLACK FASHION VENEZIA
        $_SESSION['tabella_fattura'].= "<div class='col-md-12 table-responsive'>"
                . "<table  class='table table-striped table-hover'>";

        #prima riga della tabella articoli
        $_SESSION['tabella_fattura'].= "
	<tr id=\"intestazione\">
	<td class=\"ddt\">Data</td>
	<td class=\"ddt\">Causale</td>
	<td class=\"ddt\" >Codice</td>
	<td class=\"fattura\" >Barcode</td>
	<td class=\"fattura\" >Descrizione completa</td>
	<td class=\"fattura\" >Gruppo</td>
	<td class=\"ddt\" >Colore</td>
	<td class=\"fattura\">Costo/pz</td>
	<td class=\"ddt\">Pr.pub.uni.</td>
	<td class=\"fattura\" >Quantit&agrave;</td>
	<td class=\"fattura\">Costo totale</td>
	<td class=\"ddt\">Prezzo pubblico to.</td>
	<td class=\"ddt\" >Fornitore</td>
	<td class=\"ddt\" >Destinazione</td>
	<td class=\"fattura\">IVA</td>
	<td>% az.</td>
	<td>% pub.</td>
        <td>N.Fisc.</td>";

        #se e' la sede centrale mette la voce rettifica su tutto
        if (license_has($user, "sede_centrale")) {

            //Crea il campo "rettifica" nella tabella

            $_SESSION['tabella_fattura'].="<td>Ret.</td>";
        }

        $_SESSION['tabella_fattura'].= "</tr>";

        #serve a identificare i colori della tabella
        $i = 0;

        #finche' estrae le righe bbb continua a salire di uno
        for ($bbb = 1; $bbb <= INTESTAZIONE and isset($linea[$bbb + (($ccc - 1) * INTESTAZIONE)]); $bbb++) {

            #quando i e' pari il colore e' bianco, altrimenti azzurrino
            if ($i % 2 == 0) {
                $colore = 'bianco';
            } else {
                $colore = 'azzurrino';
            }

            $num = $bbb + (($ccc - 1) * INTESTAZIONE);
            //Calcolo dei costi (da creare successivamente come unica funzione)

            $row = $linea[$num];
            //crea la riga
            //SE SONO QUESTI I NEGOZI DA FATTURARE


            if ($row[32] == 1 && $row[0] > 121916) {
                $row[12] = 50;
            }



            if ($negozio === "BLACK FASHION CASTELFRANCO" || $negozio === "BLACK FASHION MONTEBELLUNA" ||
                    $negozio === "BLACK FASHION VICENZA" || $negozio === "BLACK FASHION VENEZIA" || $negozio === "BLACK FASHION ODERZO") {

                //SE HAI MESSO IL COSTO AZIENDALE
                if (!empty($row[27])) {
                    //PREZZO AZIENDA UNITARIO E' QUELLO CHE HAI MESSO E PREZZO PUBBLICO UNI QUELLO CHE HAI INSERITO
                    $prezzo_pubblico_uni = $row[8];
                    $prezzo_azienda_uni = $row[27];
                } else {
                    //SENNO PREZZO PUBBLICO UNI E' SEMPRE QUELLO E PREZZO AZIENDA E' -80%
                    $prezzo_pubblico_uni = $row[8];
                    $prezzo_azienda_uni = $row[8] / 100 * (100 - 80);
                }
            } else {
                //ALTRIMENTI (NEL SENSO CHE NON SONO QUEI NEGOZI)
                //PREZZO PUBBLICO UNI - SCONTO PUBBLICO
                $prezzo_pubblico_uni = $row[8] / 100 * (100 - $row[13]);
                //PREZZO AZIENDA - SCONTO AFFILIATO
                $prezzo_azienda_uni = $row[8] / 100 * (100 - $row[12]);
            }

            //echo '<pre>'.$user['nome_negozio'].'</pre>';

            if ($row[33] == 1) {
                if ($user['nome_negozio'] === "BLACK FASHION PORTOFERRAIO" || $negozio === "BLACK FASHION PORTOFERRAIO") {
                    $prezzo_pubblico_uni = 0;
                    $prezzo_azienda_uni = 0;
                } else {
                    $prezzo_pubblico_uni = 0;
                }
            }


            //SE NON E' UN RESO
            if ($row[14] === "0") {
                //TOTALE PEZZO VENDUTI E' QUELLO DI PARTENZA + QUANTITA
                $totale_pezzi_venduti+=$row[9];

                //PREZZO PUBBLICO TOTALE = UNITARIO X QUANTITA
                $prezzo_pubblico_tot = $prezzo_pubblico_uni * $row[9];

                //PREZZO AFFILIATO PREZZO AZIENDA UNITARIO X QUANTITA
                $prezzo_azienda_tot = $prezzo_azienda_uni * $row[9];

                //PREZZO DELL AFFILIATO AGGIUNGE IL PREZZO AZIENDA TOT
                $fattura += $prezzo_azienda_tot;
                //AL PUBBLICO AGGIUNGE PREZZO PUBBLICO TOT
                $incassi += $prezzo_pubblico_tot;
            } else {
                if ($row[11] == casa_madre) {
                    //SE IL CLIENTE E' ESSE ERRE SAS
                    // NON FA NIENTE
                    if ($row[2] === "Reso - Reso per difetto" || $row[2] === "Reso - Reso per rettifica") {
                        //SE E' UNA RESTITUZIONE
                        //NON SOTTRAE LA QUANTITA DAL TOTALE DEI PEZZI VENDUTI 
                        //$totale_pezzi_venduti-=$row[9];
                        // PREZZO PUBBLICO E AZIENDALE TOTALI DIVENTANO 0
                        $prezzo_pubblico_tot = 0;
                        $prezzo_azienda_tot = 0;

                        // QUINDI NON SI AGGIUNGE NIENTE AL TOTALE
                        $fattura += $prezzo_azienda_tot;
                        $incassi += $prezzo_pubblico_tot;
                    }
                } else {
                    //ALTRIMENTI

                    if ($row[2] === "Reso - Reso per difetto" || $row[2] === "Reso - Reso per rettifica") {
                        //SE E' UNA RESTITUZIONE
                        //NON SOTTRAE LA QUANTITA DAL TOTALE DEI PEZZI VENDUTI 
                        //$totale_pezzi_venduti-=$row[9];
                        // PREZZO PUBBLICO E AZIENDALE TOTALI DIVENTANO 0
                        $prezzo_pubblico_tot = 0;
                        $prezzo_azienda_tot = 0;

                        // QUINDI NON SI AGGIUNGE NIENTE AL TOTALE
                        $fattura += $prezzo_azienda_tot;
                        $incassi += $prezzo_pubblico_tot;
                    } else {
                        //SE INVECE E' UN RESO IN CASSA


                        $totale_pezzi_venduti-=$row[9];

                        //SEMPRE SOTTRAE LA QUANTITA
                        //IL PREZZO E' QUELLO DELL' ARTICOLO (QUINDI PER - FA MENO)
                        $prezzo_pubblico_tot = $prezzo_pubblico_uni * $row[9];
                        $prezzo_azienda_tot = $prezzo_azienda_uni * $row[9];

                        if ($prezzo_azienda_tot < 0) {
                            $prezzo_azienda_tot = $prezzo_azienda_tot;
                        } else {
                            $prezzo_azienda_tot*=-1;
                        }

                        //QUINDI NELLA FATTURA VA A SOTTRARSI
                        $fattura += $prezzo_azienda_tot;
                        $incassi += $prezzo_pubblico_tot;
                    }
                }
            }


            #se è un reso o restituzione la riga diventa rossa altrimenti normale
            if ($row[14] == 1) {
                $_SESSION['tabella_fattura'].= "<tr id=\"reso\"";
                if ((strpos($row[2], "Reso -") !== false)) {
                    $_SESSION['tabella_fattura'].=" class='restituzione' ";
                }
                $_SESSION['tabella_fattura'].=">";
            } else {
                $_SESSION['tabella_fattura'].= "<tr id=\"$colore\">";
            }


            #crea le righe degli articoli
            $_SESSION['tabella_fattura'].=

                    "<td class=\"ddt\">$row[1]</td>
		<td class=\"ddt\">$row[2]</td>
		<td class=\"ddt\" >$row[3]</td>
		<td class=\"fattura\" >$row[4]</td>
		<td class=\"fattura\" >" . substr($row[5], 0, 15) . "</td>
		<td class=\"fattura\" >$row[6]</td>
		<td class=\"ddt\" >$row[7]</td>
		<td class=\"fattura\">" . number_format($prezzo_azienda_uni, 2) . "&euro;</td>
		<td class=\"ddt\">" . number_format($prezzo_pubblico_uni, 2) . "&euro;</td>
		<td class=\"fattura\" >$row[9]</td>
		<td class=\"fattura\">" . number_format($prezzo_azienda_tot, 2) . "&euro;</td>
		<td class=\"ddt\">" . number_format($prezzo_pubblico_tot, 2) . "&euro;</td>
		<td class=\"ddt\" >$row[10]</td>
		<td class=\"ddt\" >$row[11]</td>
		<td class=\"fattura\">" . IVA . "%</td>
		<td>" . $row[12] . "%</td>
		<td>" . $row[13] . "%</td>
                <td>" . $row[20] . "</td>";

            #se sei una casa madre ed è la tabella di scarico mette il testo rettifica
            if (license_has($user, "sede_centrale") && $switch === "Scarichi" || $switch === "NotaCredito" || $switch === "Carichi") {
                $_SESSION['tabella_fattura'].="<td><a target='_blank' class='btn btn-link' href=\"./rettifica_movimento.php?id=$row[0]&barcode=$row[4]&codice=$row[3]&prezzo=" . $prezzo_pubblico_uni . "\">Rettifica</a></td>";
            }

            $_SESSION['tabella_fattura'].= "</tr>";

            $i++;
        }
        #chiusura della tabella
        $_SESSION['tabella_fattura'].= "</table></div>";



        #echo "<p>intestazione".$ccc."<br/></p>";
        //crea la tabella con i dati di fatturazione
        if ($numero_pagine == $ccc && license_has($user, "sede_centrale")) { #se numero righe � totale e sei casa madre
            $_SESSION['totale_fattura'] = number_format(iva($fattura), 2);
            if ($switch === "Scarichi")
                $_SESSION['tabella_fattura'].=
                        "<span id=\"bottom\"></span>
                            <table   class='table table-striped table-hover  '><td colspan=\"15\"></td>
                                <tr id=\"intestazione\"><td  class=\"fattura\">Imponibile</td><td class=\"fattura\">Aliquota IVA %</td><td class=\"fattura\">Imposta</td><td class=\"fattura\">Totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td><td rowspan=\"2\" colspan=\"3\"><a  class='btn btn-link' onClick=\"print_fattura()\">STAMPA FATTURA</a></td><td rowspan=\"2\" colspan=\"3\"><a class='btn btn-link' onClick=\"mem_fattura_popup()\">MEMORIZZA FATTURA</a></td></tr>
                                    <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format($fattura, 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">" . IVA . "</td><td class=\"fattura\">&euro; " . number_format(($fattura / 100 * IVA), 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . $_SESSION['totale_fattura'] . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr>";
            elseif ($switch === "NotaCredito")
                $_SESSION['tabella_fattura'].=
                        "<table  class='table table-striped table-hover  '><td colspan=\"15\"></td>
                            <tr id=\"intestazione\"><td  class=\"fattura\">Imponibile</td><td class=\"fattura\">Aliquota IVA %</td><td class=\"fattura\">Imposta</td><td class=\"fattura\">Totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td><td rowspan=\"2\" colspan=\"3\"><a  onClick=\"print_nota()\">STAMPA NOTA</a></td><td rowspan=\"2\" colspan=\"3\"><a onClick=\"mem_nota_popup()\">MEMORIZZA NOTA</a></td></tr>
                                <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format($fattura, 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">" . IVA . "</td><td class=\"fattura\">&euro; " . number_format(($fattura / 100 * IVA), 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . $_SESSION['totale_fattura'] . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr>";
            $_SESSION['tabella_fattura'].=
                    "<tr id=\"intestazione\"><td class=\"fattura\">Intestatario</td><td class=\"fattura\">Banca d'appoggio</td><td class=\"fattura\">IBAN</td></tr>
                        <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">" . casa_madre . "</td><td  class=\"fattura\" style=\"font-weight:bold;\">Banca Popolare Volksbank</td><td class=\"fattura\" style=\"font-weight:bold;\">IT77 J058 5661 5611 7957 1040 889</td></tr></table>";
        } elseif ($numero_pagine == $ccc) {

            $_SESSION['tabella_fattura'].=
                    "<table   class='table table-striped table-hover  '>
          <tr id=\"intestazione\"><td class=\"fattura\">Costo totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td></tr>
          <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format(iva($fattura), 2) . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr></table>";
        }


        $_SESSION['tabella_fattura'].="</div>";
    }//FINE CICLO FOR INIZIALE
    #apre una nuova tabella con i dati finali della fattura e la funzione di stampa
    //echo htmlentities(utf8_encode(var_export($_SESSION['tabella_fattura'],true)));
    echo $_SESSION['tabella_fattura'];

    //Elimino tutte le righe
    $risultato->close();
}

//Funzione per mostrare la pagina a chi non è autorizzato

function non_autorizzato() {

    header('HTTP/1.0 401 Unauthorized');

    echo "Impossibile visualizzare la pagina.";
}

//Funzione per verificare l'esistenza di un valore in un array multidimensionale "ricorsivamente"

function verifica_esistenza_valore($valore, $array) {
    $art_id = false;

    for ($i = 0; $i < count($array); $i++)
        if ($array[$i]['barcode'] == $valore)
            $art_id = $i;

    return $art_id;
}

//FUNZIONE PER POPOLARE I CAMPI SELECT DEI GRUPPI, COLORI E NEGOZI

function select($nome_campo, $opz = 1) {

    global $db_magazzino, $_CONFIG, $user;

    if ($nome_campo === "gruppi")
        $query = "SELECT * FROM " . $nome_campo . " ORDER BY nome ASC;";
    else
        $query = "SELECT * FROM " . $nome_campo . " ORDER BY id ASC;";

    $dati = $db_magazzino->query($query);

    echo "<select class=\"form-control\" style=\"background-color: lightyellow;\" name=\"" . $nome_campo . "\" onchange=\"sconto();\">";

    if ($nome_campo == "gruppi" || $nome_campo == "colori")
        echo "<option value=\"\">";


    while ($riga = $dati->fetch_row()) {

        if ($nome_campo == $_CONFIG['table_utenti']) {
            echo "<option value=\"" . $riga[6];
            echo "\">" . $riga[6] . "</option>";
        } elseif ($nome_campo == "gruppi" && $opz == 1) {
            echo "<option value=\"" . $riga[2];
            echo "\">" . $riga[1] . "</option>";
        } elseif ($nome_campo == "gruppi" && $opz == 2) {
            echo "<option value=\"" . $riga[1];
            echo "\">" . $riga[1] . "</option>";
        } elseif ($nome_campo == "colori") {
            echo "<option value=\"" . $riga[1];
            echo "\">" . $riga[1] . "</option>";
        }
    }

    #if($nome_campo==$_CONFIG['table_utenti'] && license_has($user, "sede_centrale")) { echo "<option value=\"%%\">Tutti i negozi</option>"; }

    echo "</select>";
}

function select_materiali() {

    global $db_magazzino, $_CONFIG, $user;

    $query = "SELECT * FROM etichette_materiali ORDER BY materiale ASC;";
    $dati = $db_magazzino->query($query);

    echo "<select  class=\"form-control\"  name=\"materiale\" onchange=\"sconto();\">";
    echo "<option value=\"\"></option>";

    while ($riga = $dati->fetch_assoc()) {

        echo "<option value=\"" . $riga['materiale'] . "\">" . $riga['materiale'] . "</option>";
    }

    echo "</select>";
}

function corrispondenza_id($id) {
    global $db_magazzino, $db_fatture, $conn, $_CONFIG;

    $query = "SELECT * FROM '" . $_CONFIG['table_utenti'] . "' WHERE id='" . $id . "' LIMIT 1;";

    $dato = $conn->query($query);
    $dato = $dato->fetch_assoc();
    return $dato;
}

function registra_log($debug_info) {
    global $user;
    $file = fopen('/web/htdocs/www.b-fashion.it/home/testing/log/log.txt', 'a');

    fwrite($file, "\n");
    $data = date('d-m-Y H.i:s:u');
    fwrite($file, $data);
    fwrite($file, "\n");
    $contenuto = "NEGOZIO: ";
    $contenuto.=utf8_encode($user['nome_negozio']);
    $contenuto.="\n";
    $contenuto.="DEBUG: ";
    $contenuto.=utf8_encode(var_export(debug_backtrace(), true));
    $contenuto.="\n";
    $contenuto.="POST: ";
    $contenuto.=utf8_encode(var_export($_POST, true));
    $contenuto.="\n";
    $contenuto.="GET: ";
    $contenuto.=utf8_encode(var_export($_GET, true));
    $contenuto.="\n";
    $contenuto.="SESSION['articolo']: ";
    $contenuto.=utf8_encode(var_export($_SESSION['articolo'], true));
    fwrite($file, $contenuto);
    fwrite($file, "\n\n");

    fclose($file);
}

function memorizza_sessione() {
    global $db_magazzino;

    /*
     * formo l'array in stringa 
     * Per evitare errori di sql codifico la stringa in base64
     */
    $var_array = $_SESSION['articolo'];
    $array_to_save = base64_encode(serialize($var_array));

    /* Salvataggio nel database */
    $sql = "INSERT INTO sessioni_salvate (negozio,sessione) VALUES ('" . $_POST['utenti3'] . "','" . $array_to_save . "')";
    #echo "<br><br>".$sql."<br><br>";
    $db_magazzino->query($sql);

#var_dump($_SESSION['articolo']);
}

function recupera_sessione() {
    global $db_magazzino;
    /* Recupero dell'array dal database */
    $sql = "SELECT sessione FROM sessioni_salvate WHERE negozio='" . $_POST['utenti3'] . "' ORDER BY id DESC LIMIT 1;";
    $result = $db_magazzino->query($sql);
    #$sql = "DELETE FROM sessioni_salvate WHERE negozio='" . $_POST['utenti3'] . "' ORDER BY ID DESC LIMIT 1;";
    #$db_magazzino->query($sql);

    $row = $result->fetch_assoc();
    $var_array = unserialize(base64_decode($row['sessione']));
    $_SESSION['articolo'] = $var_array;
#var_dump ($_SESSION['articolo']);
}
?>

