//SELECT * FROM elenco_movimenti WHERE 1 AND (data >= '2015-10-24' AND data <='2015-10-25') AND fornitore LIKE 'BLACK FASHION VENEZIA'BLACK FASHION VENEZIA

SELEZIONA TUTTE LE COLONNE DA ELENCO MOVIMENTI
DOVE

BLOCCO 1
DATA >= 2015-10-24
ED E' <= 2015-10-25

E

BLOCCO2
FORNITORE E' BLACK FASHION VENEZIA (CHI HA VENDUTO E' CONSIDERATO FORNITORE)

E PRESUPPONE CHE DEBBANO ESSERE ENTRAMBE VERE


<?php

//Query è quella sopra
//Negozio è BLACK FASHION VENEZIA (quello a cui fare la fattura)

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

    $ultimafattura = $db_fatture->query("SHOW TABLE STATUS LIKE 'db_fatture'");
    $ultima_fattura = $ultimafattura->fetch_assoc();
    
    //riprende il numero fattura da un altra funzione (rivedere)
    $numero_fattura = $ultima_fattura['Auto_increment'];
    
    //tabella fattura è quella conservata nel server che viene salvata quando faccio memorizza
    $_SESSION['tabella_fattura'] = "";

    //questo è per vedere (dopo ti dico)
    list($status, $user) = auth_get_status();

    //il numero di righe della fattura per non uscire di pagina (cioè 44)
    define("INTESTAZIONE", 44); #43 originale
    //Aggiunge ai dati della query presa in input questa stringa

    //alla query che gli ho passato aggiunge limite di 1000????
    $query.=" ORDER BY data DESC LIMIT 1000;";
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

                //METTE I TITOLI SE E' UNO SCARICO O UNA NOTA DI CREDITO
                if ($switch === "Scarichi") {
                    $_SESSION['tabella_fattura'].= "<div class=fattura>"
                            . "<h2>FATTURA N. " . $numero_fattura . " DEL " . $data . " - Pag. " . $ccc . "</h2>"
                            . "</div>";
                } elseif ($switch === "NotaCredito") {
                    $_SESSION['tabella_fattura'].= "<div class=fattura>"
                            . "<h2>NOTA CREDITO N. " . $numero_fattura . " DEL " . $data . " - Pag. " . $ccc . "</h2>"
                            . "</div>";
                }

                $_SESSION['tabella_fattura'].= "<br/><br/>";


                //Crea l'intestazione della fattura
                $_SESSION['tabella_fattura'].= "
                <div class='col-md-4'>
		<table  class='table table-striped table-hover '>
		
		<tr id=\"intestazione\"><td  class=\"ddt fattura\" colspan=\"3\">Emittente</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">" . $riga['nome_sede_legale'] . "</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">" . $riga['indirizzo_sede_legale'] . " - " . $riga['citta_sede_legale'] .
                        "(" . $riga['provincia_sede_legale'] . ")</td></tr>

		<tr id=\"bianco\"><td  class=\"ddt fattura\" colspan=\"3\">P.IVA " . $riga['partita_iva_sede_legale'] . " - C.F. " . $riga['codice_fiscale_sede_legale'] . "</td></tr>	

		</table>
                </div>
                <div class='col-md-4'>
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



            //SE E' IN SALDO L'ARTICOLO 
            //RIVEDERE
            if ($row[34] === 1) {
                $row[22] = 50;
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
                } else {
                    //ALTRIMENTI

                    if ($row[2] === "Reso - Reso per difetto" || $row[2] === "Reso - Reso per rettifica") {
                        //SE E' UNA RESTITUZIONE
                        //NON SOTTRAE LA QUANTITA DAL TOTALE DEI PEZZI VENDUTI 
                        //$totale_pezzi_venduti-=$row[9];
                        // PREZZO PUBBLICO E AZIENDALE TOTALI DIVENTANO 0
                        $prezzo_pubblico_tot = 0;
                        $prezzo_azienda_tot = 0;
                        $row[9]=$row[9];

                        // QUINDI NON SI AGGIUNGE NIENTE AL TOTALE
                        $fattura += $prezzo_azienda_tot;
                        $incassi += $prezzo_pubblico_tot;
                    } else {
                        //SE INVECE E' UN RESO IN CASSA

                        $row[9]=$row[9]*-1;
                        $totale_pezzi_venduti+=$row[9];

                        //SEMPRE SOTTRAE LA QUANTITA
                        //IL PREZZO E' QUELLO DELL' ARTICOLO (QUINDI PER - FA MENO)
                        $prezzo_pubblico_tot = $prezzo_pubblico_uni * $row[9];
                        $prezzo_azienda_tot = $prezzo_azienda_uni * $row[9];

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
                $_SESSION['tabella_fattura'].="<td><a class='btn btn-link' href=\"./rettifica_movimento.php?id=$row[0]&barcode=$row[4]&codice=$row[3]&prezzo=" . $prezzo_pubblico_uni . "\">Rettifica</a></td>";
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
	<tr id=\"intestazione\"><td class=\"ddt\" >Numero DDT</td><td class=\"fattura\" >Numero fattura</td><td  class=\"fattura\">Imponibile</td><td class=\"fattura\">Aliquota IVA %</td><td class=\"fattura\">Imposta</td><td class=\"fattura\">Totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td><td rowspan=\"2\" colspan=\"3\"><a  class='btn btn-link' onClick=\"print_fattura()\">STAMPA FATTURA</a></td><td rowspan=\"2\" colspan=\"3\"><a class='btn btn-link' onClick=\"mem_fattura_popup()\">MEMORIZZA FATTURA</a></td></tr>
	<tr id=\"bianco\"><td class=\"ddt\">" . $numero_ddt . "</td><td class=\"fattura\">" . $numero_fattura . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format($fattura, 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">" . IVA . "</td><td class=\"fattura\">&euro; " . number_format(($fattura / 100 * IVA), 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . $_SESSION['totale_fattura'] . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr></table>";
            elseif ($switch === "NotaCredito")
                $_SESSION['tabella_fattura'].=
                        "<table  class='table table-striped table-hover  '><td colspan=\"15\"></td>
	<tr id=\"intestazione\"><td class=\"ddt\" >Numero DDT</td><td class=\"fattura\" >Numero fattura</td><td  class=\"fattura\">Imponibile</td><td class=\"fattura\">Aliquota IVA %</td><td class=\"fattura\">Imposta</td><td class=\"fattura\">Totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td><td rowspan=\"2\" colspan=\"3\"><a  onClick=\"print_nota()\">STAMPA NOTA</a></td><td rowspan=\"2\" colspan=\"3\"><a onClick=\"mem_nota_popup()\">MEMORIZZA NOTA</a></td></tr>
	<tr id=\"bianco\"><td class=\"ddt\">" . $numero_ddt . "</td><td class=\"fattura\">" . $numero_fattura . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format($fattura, 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">" . IVA . "</td><td class=\"fattura\">&euro; " . number_format(($fattura / 100 * IVA), 2) . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . $_SESSION['totale_fattura'] . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr></table>";
        } elseif (license_has($user, "sede_centrale")) { #se invece sei solo casa madre  
            $_SESSION['tabella_fattura'].= "
        <table class='table table-striped table-hover  '>
	<td colspan=\"15\"></td>
	<tr id=\"intestazione\"><td class=\"fattura\" >Numero fattura</td><td  class=\"fattura\">Imponibile parziale</td></tr>
        <tr id=\"bianco\"><td class=\"fattura\">" . $numero_fattura . "</td><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format($fattura, 2) . "</td></tr></table>";
        } else { #altrimenti se non sei neanche casa madre ma solo negozio
            $_SESSION['totale_fattura'] = number_format(iva($fattura), 2);
            $_SESSION['tabella_fattura'].=
                    "<table   class='table table-striped table-hover  '><td colspan=\"15\"></td>
	<tr id=\"intestazione\"><td class=\"fattura\">Costo totale</td><td >Incasso effettivo</td><td>Totale pezzi venduti</td></tr>
        <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">&euro; " . number_format(iva($fattura), 2) . "</td><td style=\"font-weight:bold;\">&euro; " . number_format($incassi, 2) . "</td><td>" . $totale_pezzi_venduti . "</td></tr></table>";
        }
        $_SESSION['tabella_fattura'].=
                "<table class='table table-striped table-hover  '><td colspan=\"15\"></td>
	<tr id=\"intestazione\"><td class=\"fattura\">Intestatario</td><td class=\"fattura\">Banca d'appoggio</td><td class=\"fattura\">IBAN</td></tr>
        <tr id=\"bianco\"><td class=\"fattura\" style=\"font-weight:bold;\">" . casa_madre . "</td><td  class=\"fattura\" style=\"font-weight:bold;\">Banca Popolare Volksbank</td><td class=\"fattura\" style=\"font-weight:bold;\">IT77 J058 5661 5611 7957 1040 889</td></tr></table>";


        $_SESSION['tabella_fattura'].="</div>";
    }//FINE CICLO FOR INIZIALE
    #apre una nuova tabella con i dati finali della fattura e la funzione di stampa
    //echo htmlentities(utf8_encode(var_export($_SESSION['tabella_fattura'],true)));
    echo $_SESSION['tabella_fattura'];

    //Elimino tutte le righe
    $risultato->close();
}
