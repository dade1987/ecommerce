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

        define("EMAIL_SEDE_CENTRALE", "promo@b-fashion.it");

        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {
            menu();
            ?>
            <div class="container-fluid">
                <h1>INVIA EMAIL PROMOZIONALI</h1>
                <?php
                if (isset($_POST['submit'])) {

                    $negozio_riferimento = '';
                    $i = 0;

                    foreach ($_POST['negozio'] as $value) {
                        if ($i > 0) {
                            $negozio_riferimento.="OR negozio_riferimento='$value'";
                        } else {
                            $negozio_riferimento.=" negozio_riferimento='$value'";
                        }
                        $i++;
                    }

                    $query = "SELECT email FROM db_fidelity_card WHERE $negozio_riferimento ORDER BY numero ASC;";
                    $risultato = $db_fidelity_card->query($query);
                    $sender = EMAIL_SEDE_CENTRALE; //da definire in config
                    #$to = EMAIL_SEDE_CENTRALE; //da definire in config

                    while ($email = $risultato->fetch_assoc()) {
                        $to = $email['email'];
                        $mail_boundary = "=_NextPart_" . md5(uniqid(time()));
                        $subject = $_POST['oggetto'];

                        $headers = "From: $sender\n";
                        $headers .= "MIME-Version: 1.0\n";
                        $headers .= "Content-Type: multipart/alternative;\n\tboundary=\"$mail_boundary\"\n";
                        $headers .= "X-Mailer: PHP " . phpversion();

                        // Corpi del messaggio nei due formati testo e HTML
                        $text_msg = $_POST['contenuto'];
                        $html_msg = "<b>" . $_POST['contenuto'] . "</b>";

                        // Costruisci il corpo del messaggio da inviare
                        $msg = "This is a multi-part message in MIME format.\n\n";
                        $msg .= "--$mail_boundary\n";
                        $msg .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
                        $msg .= "Content-Transfer-Encoding: 8bit\n\n";
                        $msg .= $_POST['contenuto'];

                        // aggiungi il messaggio in formato text

                        $msg .= "\n--$mail_boundary\n";
                        $msg .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
                        $msg .= "Content-Transfer-Encoding: 8bit\n\n";
                        $msg .= $_POST['contenuto'];

                        // aggiungi il messaggio in formato HTML
                        // Boundary di terminazione multipart/alternative
                        $msg .= "\n--$mail_boundary--\n";

                        // Imposta il Return-Path (funziona solo su hosting Windows)
                        ini_set("sendmail_from", $sender);

                        // Invia il messaggio, il quinto parametro "-f$sender" imposta il Return-Path su hosting Linux
                        if (strlen($to) > 0) {
                            if (mail($to, $subject, $msg, $headers, "-f$sender")) {
                                echo "<br><br>Mail inviata correttamente a $to!</br>";
                            } else {
                                echo "<br><br>Recapito e-Mail fallito!<br/>";
                            }
                        } else {
                            #per ora non fa niente
                        }
                    }
                }
                ?>
                <form name="form1" method="post">
                    <div class="col-md-6"><label for="oggetto">Oggetto:</label></td><td><input class="form-control" name="oggetto" type="text" value=""size="58" placeholder="Braccialetti argento scontati del 50%">

                            <label for="contenuto">Contenuto (HTML):</label></td><td><textarea class="form-control"  name="contenuto" rows="22" cols="60" placeholder="<html><head></head><body></body>"></textarea></div>
                    <div class="col-md-6">A CHI INVIO L'EMAIL?<br/><br/>

                        <?php
                        $query = "SELECT nome_negozio from " . $_CONFIG['table_utenti'] . ";";
                        $query = $db_magazzino->query($query);

                        while ($negozio = $query->fetch_assoc()) {
                            echo "<input class=\"checkbox-inline\" type=\"checkbox\" name=\"negozio[]\" value=\"" . $negozio['nome_negozio'] . "\"/>" . $negozio['nome_negozio'] . "<br/>";
                        }
                        ?>
                        <a onclick="$('input[name=\'negozio[]\']').trigger('click');
                           ">Seleziona/Deseleziona tutti</a>
                        <br/><br/>
                        <input class="btn btn-default" type="submit" name="submit" value="Invia"> </div>
                    </table>
                </form>
            </div>

            <?php
        } else
            non_autorizzato();
        ?>
    </body>
