$(document).ready(function (event) {
    $('[name="saldo[]"]').change(function () {
        num_art = $(this).val();

        if (this.checked)
        {
            $('[name="prezzo_' + num_art + '"]').val(parseFloat($('[name="prezzo_' + num_art + '"]').val()) / 100 * 50);
            funz_totale();
        }
        else
        {
            $('[name="prezzo_' + num_art + '"]').val(parseFloat($('[name="prezzo_' + num_art + '"]').val()) / 50 * 100);
            funz_totale();
        }
    });

    $('#barcode').focus();
    $(document).keyup(function (e)
    {
        if (e.keyCode == 17)
        {
            //e.preventDefault()
            //event.preventDefault();
            $('#barcode').focus();
        }
        //if (e.keyCode == 16) 
        //    {
        //e.preventDefault()
        //event.preventDefault();
        //    $("button[name='stampa']").click();
        //    $("button[name='stampa']").remove();
        //    }
    });
});


function stampa_scontrino2() {
    $('body').children().hide();
    $('form').show();
    $('div.menu_cassa').hide();
    window.print();
    $('*').show();
}

function stampa_fattura() {
    $('body').children().hide();
    $('form').show();
    $('div.menu_cassa').hide();
    $('.hide').hide();
    window.print();
    $('*').show();
}

function funz_resto() {
    var saldo = $('[name="saldo"]').val();
    var totale = $('[name="totale"]').val();
    var fine = saldo - totale;
    $('[name="resto"]').val(fine.toFixed(2));
}

function funz_totale() {
    if ($("input[name='imponibile']").val() != undefined) {
        var totale = 0;
        for (i = 0; document.getElementsByName('prezzo_' + i).length != 0; i++) {
            var saldo = document.getElementsByName('prezzo_' + i);
            var quantita = document.getElementsByName('quantita_' + i);

            totale = saldo[0].value * quantita[0].value + totale;
            imposta = totale / 100 * 22;
            imponibile = totale - imposta;

        }
        document.getElementsByName('totale')[0].value = totale.toFixed(2);
        document.getElementsByName('totale')[0].value = totale.toFixed(2);
        document.getElementsByName('imposta')[0].value = imposta.toFixed(2);
        document.getElementsByName('imponibile')[0].value = imponibile.toFixed(2);



        //$('input[name="punti_fidelity_card"]').val('');
        //$('input[name="punti_fidelity_card"]').val(parseInt(parseInt($('input[name="punti_fidelity_card"]').val())+parseInt($('input[name="totale"]').val())/10));
        //$('input[name="punti_fidelity_card_nuovi"]').val(Math.floor(parseInt($('input[name="totale"]').val())/10));
    }
}
