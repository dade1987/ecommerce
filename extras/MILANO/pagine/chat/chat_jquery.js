$(document).ready(
        function () {
            var form = $('form');

            var contatore = setInterval(function () {
                ricevi_ajax(form);
            }, 1000);

            $('#negozi>input').click(
                    function (e) {
                        $('input[name="lui"]').val($(this).val());
                    }
            );

            $('[name="invia"]').click(
                    function () {
                        invia_ajax(form);
                    }
            );
        }
);

function invia_ajax(form) {
    var valore=form.serializeArray();
    valore.push({name:'invia', value:'si'});
    $('input[name="messaggio"]').val('');

    $.ajax({
        type: "POST",
        url: 'chat_ajax.php',
        data: valore,
        success: function (response) {
            $("[name='messaggi_ricevuti']").text(response);
        }
    });
}

function ricevi_ajax(form) {
var valore=form.serializeArray();
valore.push({name:'invia', value:'no'});

    $.ajax({
        type: "POST",
        url: 'chat_ajax.php',
        data: valore,
        success: function (response) {
            $("[name='messaggi_ricevuti']").text(response);
        }
    });

}