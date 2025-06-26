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
        list($status, $user) = auth_get_status();

        if ($status == AUTH_LOGGED) {
            menu();

            if (isset($_POST['submit'])) {
                if (!empty($_POST['numero'])) {
                    $query = "UPDATE db_fidelity_card SET nome='" . $_POST['nome'] . "',cognome='" . $_POST['cognome'] . "',telefono='" . $_POST['telefono'] . "', 
				email='" . $_POST['email'] . "',numero_vecchio='" . $_POST['numero_vecchio'] . "' WHERE numero='" . $_POST['numero'] . "'";
                    //echo $query;

                    $risultato = $db_fidelity_card->query($query);
                    if (!$risultato)
                        die("Errore nella connessione con il database: " . mysqli_connect_error());
                    else
                        echo "<br><br>Dati memorizzati correttamente.
					<br><br><a href =\"./elenco_movimenti.php\">FATTO</a>";
                }
                else {
                    $query = "INSERT INTO db_fidelity_card (nome,cognome,telefono,email,numero_vecchio,negozio_riferimento) VALUES ('" . $_POST['nome'] . "','" . $_POST['cognome'] . "',
				'" . $_POST['telefono'] . "','" . $_POST['email'] . "','" . $_POST['numero_vecchio'] . "','" . $user['nome_negozio'] . "')";

                    //echo $query;
                    $risultato = $db_fidelity_card->query($query);
                    if (!$risultato)
                        die("Errore nella connessione con il database: " . mysqli_connect_error());
                    else
                        echo "<br><br>Dati memorizzati correttamente.
					<br><br><a href =\"../gestione_magazzino/elenco_movimenti.php\">FATTO</a>";
                }
            }
            if (isset($_GET['numero'])) {
                $risultato = $db_fidelity_card->query("SELECT * FROM db_fidelity_card WHERE numero=" . $_GET['numero'] . " ORDER BY numero ASC");
                $colonna = $risultato->fetch_assoc();
            }
            ?>
            <form name="form1" method="post" action="./ins_mod_fidelity_card.php">
                <div class="container-fluid">

                    <h1>INSERIMENTO CLIENTE FIDELITY CARD</h1>

                    <table><tr><td>

                                <label for="nome">
                                    Nome:</label></td><td>
                                <input class="form-control" name="nome" type="text" value="<?php if (isset($colonna['nome'])) echo $colonna['nome']; ?>"size="50"></td></tr>
                        <tr><td>

                                <label for="cognome">
                                    Cognome:</label></td><td>
                                <input class="form-control"  name="cognome" type="text" value="<?php if (isset($colonna['cognome'])) echo $colonna['cognome']; ?>"size="50"></td></tr>
                        <tr><td>

                                <label for="telefono">
                                    Telefono:</label></td><td>
                                <input class="form-control"  name="telefono" type="text" value="<?php if (isset($colonna['telefono'])) echo $colonna['telefono']; ?>"size="50"></td></tr>

                        <tr><td>

                                <label for="email">
                                    Email:</label></td><td>
                                <input class="form-control"  name="email" type="text" value="<?php if (isset($colonna['email'])) echo $colonna['email']; ?>"size="50"></td></tr>

                        <tr><td>
                                <label for="numero_vecchio">
                                    Barcode:</label></td><td>
                                <input class="form-control"  name="numero_vecchio" type="text" value="<?php if (isset($colonna['numero_vecchio'])) echo $colonna['numero_vecchio']; ?>"size="50"></td></tr>

                        <tr><td>

                                <input type="hidden" name="numero" value="<?php if (isset($colonna['numero'])) echo $colonna['numero']; ?>">

                            <td colspan="2"><input class="btn btn-default"  type="submit" name="submit" value="OK"></td></tr></table>
                </div>
            </form> 

    <?php
}//Fine della funzione per vedere se l'utente è autorizzato
else
    non_autorizzato();
?>
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
            var $_Tawk_API = {}, $_Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/559ba55c04c33fb6400d686d/default';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
    </body>
</html>