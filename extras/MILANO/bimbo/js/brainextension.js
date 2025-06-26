/*Bimbo 1.0 (r)
 Copyright (c) 2015 Davide Cavallini
 All rights reserved */


/* Bimbo 1.0 © ®
 * Davide Cavallini
 * +39 39 11 35 25 26 
 * davidecavallini1987@gmail.com */

var db = openDatabase('BrainExtension', '1.0', 'Database client side', 2 * 1024 * 1024);

function addZero(str)
{
    return str < 10 ? ('0' + str) : str;
}

function invia_email(stringa) {

    //invia email a davide@davide.it ciao come stai?

    stringa = stringa.substr(14);

    var email = stringa.match(/\S+@\S+/g);

    stringa = stringa.replace(email, '');

    window.open('mailto:' + email + '?subject=Richiesta di informazioni&body=' + stringa);

    return "Apertura email in corso...";

}

function clicca(stringa)
{
    stringa = stringa.substr(7);

    $('input[value="OK"]').trigger('click');
    $('button[value="OK"]').trigger('click');

    return stringa;
}

function menu(stringa) {
    stringa = stringa.substr(6);
    stringa[0]=stringa[0].toUpperCase();

    var link = $('[role="menu"]').find('li>a:contains('+stringa+')').attr('href');

    window.open(link, '_blank');

    return "Menu " + stringa;

}

function stampa_tutte_fatture() {

    var negozi = new Array();

    $('form[name="form1"]').attr("target", "_blank");

    var i = 0;
    $('[name="utenti3"] option').each(function () {
        switch ($(this).attr('value')) {
            case "BLACK FASHION CASTELFRANCO":
            //case "BLACK FASHION POTENZA":
            case "BLACK FASHION (Esse Erre)":
            case "BLACK FASHION MONTEBELLUNA":
            case "BLACK FASHION ODERZO":
            case "BLACK FASHION VICENZA":
            case "BLACK FASHION VENEZIA":
            case "BLACK FASHION BIELLA":
            case "ESSE ERRE SAS":
                break;
            default:
                negozi[i] = $(this).attr('value');
                i++;
                break;
        }
    });

    console.log(negozi);

    for (var i = 0, loadDelay = 1000; i < negozi.length; ++ i, loadDelay += 5000)
        setTimeout((function (num) {
            return function () {
                console.log(negozi[num]);
                fattura('              ' + negozi[num]);
            };
        })(i), loadDelay);

    return "Apertura di tutte le fatture in corso";
}

function fattura(stringa)
{

    $('form[name="form1"]').attr("action", "http://www.b-fashion.it/pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi&sfb=bfs#bottom");

    stringa = stringa.substr(14);
    $('[name="utenti3"] option:contains("' + stringa + '")').attr('selected', true);

    stringa = stringa.toUpperCase();
    $('[name="utenti3"] option:contains("' + stringa + '")').attr('selected', true);


    console.log("Fattura di " + stringa);

    var curr = new Date; // get current date
    var first = curr.getDate() - 7; // First day is the day of the month - the day of the week

    var curr2 = new Date; // get current date

    var last = curr2.getDate(); // last day is the first day + 6

    var firstday = new Date(curr.setDate(first));
    var lastday = new Date(curr2.setDate(last));

    var partenza = addZero(firstday.getFullYear()) + '-' + addZero(firstday.getMonth() + 1) + '-' + addZero(firstday.getDate());

    var fine = addZero(lastday.getFullYear()) + '-' + addZero(lastday.getMonth() + 1) + '-' + addZero(lastday.getDate());

    $('[name="data"]').attr('value', partenza);
    $('[name="data2"]').attr('value', fine);


    $('[name="submit"]').trigger("click");

    $('form[name="form1"]').attr("action", "");

    return "Fattura di " + stringa + " in fase di elaborazione.";
}

function stampa_fattura() {
    print_fattura();//funzione esterna allo script
    return "Complimenti.";
}

function media(array) {

    var numeri = array.length;
    console.log(numeri);

    var media = 0;

    for (var i = 0; i < numeri; i++)
    {
        media += array[i];
        //console.log(array[i]);
    }

    media = media / numeri;


    console.log(media);

    return media;
}

function cerca(stringa) {
    var stringa = stringa.substr(6);

    var cerca = "https://www.google.it/?gws_rd=ssl#safe=off&q=" + stringa;
    console.log(cerca);

    window.open(cerca, "_blank");

    return "Ricerca della frase " + stringa + " in corso...!";
}

function wiki(stringa) {
    var stringa = stringa.substr(5);

    var cerca = "https://it.wikipedia.org/wiki/" + stringa;
    console.log(cerca);

    window.open(cerca, "_blank");

    return "Ricerca nell'enciclopedia di " + stringa + " in corso!";
}

function vai_a(stringa) {
    var stringa = stringa.substr(6);

    var cerca = "https://www.google.it/maps/place/" + stringa;
    console.log(cerca);

    window.open(cerca, "_blank");

    return "Ricerca di " + stringa + " nella mappa in corso!";
}

function portami_a(stringa) {
    var stringa = stringa.substr(10);

    var cerca = "https://www.airbnb.it/s/" + stringa + "?guests=&ss_id=5ojf1axm&source=bb";
    //console.log(cerca);

    window.open(cerca, "_blank");

    return "Ricerca di appartamenti e hotel a " + stringa + " in corso";
}


function memorizza(stringa) {

    stringa = stringa.substr(10);

    var testo_query = 'INSERT INTO annotazioni (testo) VALUES ("' + stringa + '");';

    //toglie il primo memorizza
    //inserisce in tabella

    query(testo_query, function () {

    });

    return "nota memorizzata";
}
;

function cancella(stringa) {
    //prende 3 parole (tipo tag)
    //confronta con db
    stringa = stringa.substr(14);

    stringa = stringa.match(/\w+/gi);

    if (stringa[0] === undefined) {
        stringa[0] = ' ';
    }

    if (stringa[1] === undefined) {
        stringa[1] = ' ';
    }

    var testo_query = 'SELECT testo FROM annotazioni WHERE testo LIKE "%' + stringa[0] + '%" AND testo LIKE "%' + stringa[1] + '%";';

    //toglie il primo memorizza
    //inserisce in tabella

    query(testo_query, function (result) {

        var output;

        if (result[0] !== undefined)
        {
            if (result.length > 1) {
                output = 'Ho trovato piu di una frase: ';
                result.forEach(function (obj) {
                    output += obj.testo.substring(0, 20) + '...,';
                });
            }
            else
            {
                var testo_query = "DELETE FROM annotazioni WHERE testo='" + result[0].testo + "';";
                query(testo_query, function (result) {
                });
                output = 'Ho cancellato questa annotazione: ' + result[0].testo;

            }
        }
        else
        {
            output = "Non ho trovato nulla di corrispondente in memoria.";
        }

        $('#risposta_vocale').val(output);
        //----------------------------//
        var u = new SpeechSynthesisUtterance();
        u.text = output;
        u.lang = 'it-IT';
        u.rate = 1.2;
        speechSynthesis.speak(u);
        //----------------------------//
    });

}
;

function riprendi(stringa) {
    //prende 3 parole (tipo tag)
    //confronta con db
    stringa = stringa.substr(9);

    stringa = stringa.match(/\w+/gi);

    if (stringa[0] === undefined) {
        stringa[0] = ' ';
    }

    if (stringa[1] === undefined) {
        stringa[1] = ' ';
    }

    var testo_query = 'SELECT testo FROM annotazioni WHERE testo LIKE "%' + stringa[0] + '%" AND testo LIKE "%' + stringa[1] + '%";';

    //toglie il primo memorizza
    //inserisce in tabella

    query(testo_query, function (result) {

        var output;

        if (result[0] !== undefined)
        {
            if (result.length > 1) {
                output = 'Ho trovato piu di una frase: '
                result.forEach(function (obj) {
                    output += obj.testo.substring(0, 20) + '...,';
                });
            }
            else
            {
                output = result[0].testo;
            }
        }
        else
        {
            output = "Non ho trovato nulla di corrispondente in memoria.";
        }

        $('#risposta_vocale').val(output);
        //----------------------------//
        var u = new SpeechSynthesisUtterance();
        u.text = output;
        u.lang = 'it-IT';
        u.rate = 1.2;
        speechSynthesis.speak(u);
        //----------------------------//
    });

}
;


function invia_richiesta(stringa) {

    var parse_math = function (stringa)
    {
        //parsestr matematico
        stringa = stringa.replace(/diviso/gi, '/');
        stringa = stringa.replace(/fratto/gi, '/');
        stringa = stringa.replace(/x/gi, '*');
        stringa = stringa.replace(/per/gi, '*');
        stringa = stringa.replace(/più/gi, '+');
        stringa = stringa.replace(/meno/gi, '-');
        stringa = stringa.replace(/=/gi, ' ');

        return stringa;
    };

    var parla = function (sorgente) {

        $('#risposta_vocale').val(output);

        var u = new SpeechSynthesisUtterance();

        if (output.substr(-3) === '.00')
        {
            u.text = parseInt(output).toString();
        }
        else if (!isNaN(output))
        {
            output = output.split('.');

            output = output[0] + ' e ' + output[1];
            u.text = output;
        }
        else if (typeof (output) === "string") {

            u.text = output;
        }

        u.lang = 'it-IT';
        u.rate = 1.2;
        //u.onend = function (event) { };

        speechSynthesis.speak(u);
        console.log("u.text: " + u.text);
    };

    var output;

    var funzione = new Array();

    //MANCANO LE FUNZIONI DI MEMORIZZAZIONE


    stringa = stringa.replace(/ punto /gi, '.');
    stringa = stringa.replace(/,/gi, '.');
    stringa = stringa.replace(/ D/g, ' di ');



    var testo_query = "SELECT * FROM istruzioni order by length(nome) desc;";

    //console.log(testo_query);

    query(testo_query, function (result) {
        //console.log(result);
        var i = 0;
        result.forEach(function (obj) {
            funzione[i] = new Array();
            funzione[i][0] = new Array();
            funzione[i][0] = obj.nome;
            funzione[i][1] = new Array();
            funzione[i][1] = obj.funzione;
            i++;
        });

        //console.log(funzione);

        try {
            output = eval(parse_math(stringa));
            output = parseFloat(output).toFixed(2);

            parla();

            return output;
        }
        catch (e) {
            try {

                funzione.forEach(function (array) {

                    var frase = parse_math(stringa);
                    frase = stringa.toLowerCase();
                    array[0] = array[0].toLowerCase()

                    if (frase.indexOf(array[0]) !== -1) {

                        //console.log(stringa);

                        var numeri = frase.match(/(\d+\.\d+)|(\d+)/gi);
                        console.log(numeri);

                        //converte i numeri dell'array da stringa a numero
                        for (var i = 0; numeri && i < numeri.length; i++)
                        {
                            numeri[i] = parseFloat(numeri[i]);
                        }
                        /*
                         console.log(stringa);
                         console.log(array[0]);
                         console.log(numeri);
                         */
                        console.log(array[1]);

                        output = eval(array[1]);

                        console.log(output);

                        if (output !== undefined) {

                            if (typeof (output) === "number") {
                                output = parseFloat(output).toFixed(2);
                            }

                            //console.log(output);



                            parla();

                            return output;
                        }
                    }


                });

            }
            catch (e) {

                return e;
            }
        }
    });


}

//Solo su Chrome
var recognition = new webkitSpeechRecognition();
recognition.onresult = function (event) {
    if (event.results.length > 0) {
        richiesta_vocale.value = event.results[0][0].transcript;
        $('#richiesta_vocale').trigger("change");
    }
};

var iniz_strutt_db = function (callback) {


    var testo_query = 'CREATE TABLE IF NOT EXISTS istruzioni (nome,funzione);';
    query(testo_query, function () {
        testo_query = 'CREATE TABLE IF NOT EXISTS annotazioni (testo);';
        query(testo_query, function () {
            console.log("struttura database creata con successo");
            testo_query = 'SELECT * FROM istruzioni LIMIT 1;';
            query(testo_query, function (result) {
                if (!result[0]) {
                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("iva di ","numeri[0]/100*22");';
                    query(testo_query, function () {
                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("+ iva","numeri[0]/100*122");';
                        query(testo_query, function () {
                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("ritenuta di ","numeri[0]/100*20");';
                            query(testo_query, function () {
                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("+ ritenuta","numeri[0]/100*120");';
                                query(testo_query, function () {
                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("- iva","numeri[0] - (numeri[0]/100*22)");';
                                    query(testo_query, function () {
                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("- ritenuta","numeri[0] - (numeri[0]/100*20)");';
                                        query(testo_query, function () {
                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("media","media(numeri)");';
                                            query(testo_query, function () {
                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("memorizza","memorizza(stringa)");';
                                                query(testo_query, function () {
                                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("riprendi","riprendi(stringa)");';
                                                    query(testo_query, function () {
                                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("cerca","cerca(stringa)");';
                                                        query(testo_query, function () {
                                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("wiki","wiki(stringa)");';
                                                            query(testo_query, function () {
                                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("vai a ","vai_a (stringa)");';
                                                                query(testo_query, function () {
                                                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("portami a ","portami_a (stringa)");';
                                                                    query(testo_query, function () {
                                                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("radice quadrata","Math.sqrt(numeri[0])");';
                                                                        query(testo_query, function () {
                                                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("fai fattura a","fattura(stringa)");';
                                                                            query(testo_query, function () {
                                                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("stampa tutte le fatture","stampa_tutte_fatture()");';
                                                                                query(testo_query, function () {
                                                                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("stampa fattura","stampa_fattura()");';
                                                                                    query(testo_query, function () {
                                                                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("invia email a ","invia_email(stringa)");';
                                                                                        query(testo_query, function () {
                                                                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("%","numeri[0]/100*numeri[1]");';
                                                                                            query(testo_query, function () {
                                                                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("elenco dei comandi","legenda_comandi()");';
                                                                                                query(testo_query, function () {
                                                                                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("cancella nota","cancella(stringa)");';
                                                                                                    query(testo_query, function () {
                                                                                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("lista","menu(stringa)");';
                                                                                                        query(testo_query, function () {
                                                                                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("clicca","clicca(stringa)");';
                                                                                                            query(testo_query, function () {
                                                                                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("alla seconda","numeri[0]*numeri[0]");';
                                                                                                                query(testo_query, function () {
                                                                                                                    testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("alla terza","numeri[0]*numeri[0]*numeri[0]");';
                                                                                                                    query(testo_query, function () {
                                                                                                                        testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("elevato","Math.pow(numeri[0],numeri[1])");';
                                                                                                                        query(testo_query, function () {
                                                                                                                            testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("dimezza","numeri[0]/2");';
                                                                                                                            query(testo_query, function () {
                                                                                                                                testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("raddoppia","numeri[0]*2");';
                                                                                                                                query(testo_query, function () {

                                                                                                                                    console.log("DB_NEW");
                                                                                                                                    callback("DB_NEW");
                                                                                                                                });
                                                                                                                            });
                                                                                                                        });
                                                                                                                    });
                                                                                                                });
                                                                                                            });
                                                                                                        });
                                                                                                    });
                                                                                                });
                                                                                            });
                                                                                        });
                                                                                    });
                                                                                });
                                                                            });
                                                                        });
                                                                    });
                                                                });
                                                            });
                                                        });
                                                    });
                                                });
                                            });
                                        });
                                    });
                                });
                            });
                        });
                    });
                }
            });
        });
    });
};

var legenda_comandi = function () {
    var stringa = '';

    var testo_query = 'SELECT * FROM istruzioni order by length(nome) desc;';
    query(testo_query, function (result) {
        if (result[0] !== undefined) {
            result.forEach(function (obj) {
                obj.nome = obj.nome[0].toUpperCase() + obj.nome.substr(1);
                stringa += obj.nome + "\n";
            });
        }
        alert("Legenda comandi:\n\nCalcoli matematici normali\n\n" + stringa);
        return "Apertura dell'elenco dei comandi.";
    });
};


var impara_funzione = function () {
    var nome_formula = $('#nome_formula').val();
    var funzione_formula = $('#funzione_formula').val();
    if (nome_formula.length > 0 && funzione_formula.length > 0) {
        var testo_query = 'INSERT INTO istruzioni (nome,funzione) VALUES ("' + nome_formula + '","' + funzione_formula + '");';
        query(testo_query, function () {

            var output = "Formula imparata con successo.";
            $('#risposta_vocale').val(output);
            //----------------------------//
            var u = new SpeechSynthesisUtterance();
            u.text = output;
            u.lang = 'it-IT';
            u.rate = 1.2;
            speechSynthesis.speak(u);
            //----------------------------//

            $('#nome_formula').val('');
            $('#funzione_formula').val('');
        });
    }
};
var query = function (string, callBack) {
    var risultato = new Array();
    db.transaction(function (tx, results) {
        tx.executeSql(string, [], function (tx, rs)
        {
            for (var i = 0; i < rs.rows.length; i++) {
                // Each row is a standard JavaScript array indexed by
                // column names.
                var row = rs.rows.item(i);
                risultato[i] = row;
            }
            callBack(risultato);
        }, errorHandler);
        function errorHandler(transaction, error) {
            console.log("Error : " + error.message + " in " + string);
        }

        //console.log(risultato);
    });
    //console.log(risultato);
};
$(document).ready(
        function () {

            iniz_strutt_db(function () {
            });

            if (!!window.chrome === false)
            {
                alert("Attenzione: il software è compatibile solo con il browser Google Chrome");
            }


            $('#registra_voce').click(function () {
                recognition.start();
            });
            //quando avro risolto il consenti microfono
            /*$('#registra_voce').mousedown(function(){
             recognition.start();
             $('#registra_voce').mouseup(function(){
             recognition.stop();
             });
             });*/

            $('#impara_funzione').click(function () {
                impara_funzione();
            });
            $('#richiesta_vocale').change(function () {

                var stringa = $(this).val();
                invia_richiesta(stringa);
            });
        });  