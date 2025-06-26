<?php session_start(); ?>
<html>
    <head>
        <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">

        <script src="https://code.jquery.com/jquery-2.1.1.js" type="text/JavaScript" language="javascript"></script>

        <script>
            function calcola() {
                var v = document.getElementsByName('prezzo_unitario')[0];
                var s = document.getElementsByName('sconto_pubblico')[0];

                var perc_sconto = v.value / 100 * s.value;
                c = v.value - perc_sconto;

                alert("Euro " + c);
            }

            function  aggiungi_quantita1() {
                var quantita = parseInt($("input[name='quantita']").val());
                var surplus = parseInt($("input[name='aggiungi_quantita']").val());
                $("input[name='quantita']").val(quantita + surplus);
            }
        </script>

    </head>
    <body>

        <?php
        include_once("../../classi/funzioni.php");
        include_once("../../classi/config.php");
        include_once("../../classi/auth.lib.php");
        list($status, $user) = auth_get_status();



        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        }

        if (isset($_POST['id'])) {
            $id = $_POST['id'];
        }

        if (isset($_GET['barcode'])) {
            $barcode = $_GET['barcode'];
        }

        if (isset($_POST['barcode'])) {
            $barcode = $_POST['barcode'];
        }
        
        $risultato = $db_magazzino->query("SELECT * FROM elenco_movimenti WHERE id='" . $id . "' LIMIT 1");
            $row = $risultato->fetch_row();

        if ($status == AUTH_LOGGED && (license_has($user, "sede_centrale") or $row[10]===$user['nome_negozio']) && isset($id)) {
            menu();

            echo "<h1>RETTIFICA MOVIMENTO MAGAZZINO</h1>";


            if ($_POST['submit']) {

                $query = "UPDATE elenco_movimenti SET prezzo_pubblico_unitario = '" . $_POST['prezzo_unitario'] . "'  WHERE elenco_movimenti.barcode ='" . $barcode . "' AND elenco_movimenti.id='".$id."'";
                $risultato = $db_magazzino->query($query);

                $query = "UPDATE elenco_movimenti SET quantita='" . $_POST['quantita'] . "',sconto_affiliato='" . $_POST['sconto_azienda'] . "', sconto_pubblico='" . $_POST['sconto_pubblico'] . "'  WHERE elenco_movimenti.id ='" . $id . "'";
                $risultato = $db_magazzino->query($query);
//$risultato->close();
//echo $query;
            }

            
            ?>
        <body>

            <form name="form1" method="post" >
                <table><tr><td>

                            <label for="nome">
                                Oggetto:</label></td><td>
                            <input name="nome" type="text" disabled value="<?php echo $row[5]; ?>"size="50"></td></tr>
                    <tr><td>

                            <label for="quantita">Quantit&agrave;:</label></td><td>
                            <input name="quantita" type="text" value="<?php echo $row[9]; ?>" size="50"></td></tr>
                    <tr><td>

                            <label for="quantita">Aggiungi quantit&agrave;:</label></td><td>
                            <input name="aggiungi_quantita" type="text" value="" size="50" onchange="aggiungi_quantita1();"></td></tr>
                    <tr><td>

                            <label for="prezzo_unitario">Prezzo pubblico unitario (al netto di iva e sconti):</label></td><td>
                            <input name="prezzo_unitario" type="text" size="50" value="<?php echo number_format($row[8], 2); ?>"></td></tr>

                    <tr><td>
                            <label for="sconto_azienda">Sonto azienda %:</label></td><td>
                            <input name="sconto_azienda" type="text" size="50" value="<?php echo $row[12]; ?>"></td></tr>

                    <tr><td>
                            <label for="sconto_pubblico">Sconto pubblico %:</label></td><td>
                            <input name="sconto_pubblico" type="text" size="50" value="<?php echo $row[13]; ?>"></td></tr>

                    <input type="hidden" name="id" value="<?php echo $id; ?>">     
                    <td colspan="2"><input type="submit" name="submit" value="OK"></td></tr></table>
            </form>

            <a onclick="calcola()">Calcola prezzo pubblico unitario con sconto</a>
            <br>

            <?php
            $referer = explode("?", basename($_SERVER['HTTP_REFERER']));
            $referer = $referer[0];

            $phpself = explode("?", basename($_SERVER['PHP_SELF']));
            $phpself = $phpself[0];

            /* echo $referer;
              echo "<br/>";
              echo $phpself; */

            if ($referer != $phpself) {
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
#echo "<br/>sessione settata<br/>";
            }
#echo $_SESSION['referer'];
            ?>
            <br><a href ="<?php echo $_SESSION['referer']; ?>">Torna indietro</a>
            <br>
            <br>
    <?php
    echo "<a href =\"./stampa_barcode.php?descrizione=" . $row[5] . "&barcode=" . sprintf("%'.012d", $row[4]) . "&codice=" . $row[3] . "&prezzo=" . $row[8] . "\" target=\"_blank\">Stampa codice a barre uguale al precedente</a>";
    ?>
    </html>
<?php
} else
    non_autorizzato();
?>
