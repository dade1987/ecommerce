<?php
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED && license_has($user, "sede_centrale")) {


    if (isset($_POST['submit']) && !isset($_GET['id'])) {
        $query = "INSERT INTO " . $_CONFIG['table_utenti'] . "(`nome_sede_legale`, `indirizzo_sede_legale`, `citta_sede_legale`, `provincia_sede_legale`, `partita_iva_sede_legale`,`codice_fiscale_sede_legale`, `nome_negozio`, `indirizzo_negozio`, `citta_negozio`, `provincia_negozio`,`partita_iva_negozio`,`codice_fiscale_negozio`, `username`, `password`) VALUES ('" . $_POST['nome_sede_legale'] . "','" . $_POST['indirizzo_sede_legale'] . "','" . $_POST['citta_sede_legale'] . "','" . $_POST['provincia_sede_legale'] . "','" . $_POST['partita_iva_sede_legale'] . "','" . $_POST['codice_fiscale_sede_legale'] . "','" . $_POST['nome_negozio'] . "','" . $_POST['indirizzo_negozio'] . "','" . $_POST['citta_negozio'] . "','" . $_POST['provincia_negozio'] . "','" . $_POST['partita_iva_negozio'] . "','" . $_POST['codice_fiscale_negozio'] . "','" . $_POST['username'] . "','" . md5($_POST['password']) . "');";
        echo "Cliente aggiunto con successo.<br/><br/>";
        $db_magazzino->query($query);
    } elseif (isset($_POST['submit']) && isset($_GET['id'])) {
        $query = "UPDATE `utenti3` SET `nome_sede_legale`='" . $_POST['nome_sede_legale'] . "',`indirizzo_sede_legale`='" . $_POST['indirizzo_sede_legale'] . "',`citta_sede_legale`='" . $_POST['citta_sede_legale'] . "',`provincia_sede_legale`='" . $_POST['provincia_sede_legale'] . "',`partita_iva_sede_legale`='" . $_POST['partita_iva_sede_legale'] . "',`codice_fiscale_sede_legale`='" . $_POST['codice_fiscale_sede_legale'] . "',`nome_negozio`='" . $_POST['nome_negozio'] . "',`indirizzo_negozio`='" . $_POST['indirizzo_negozio'] . "',`citta_negozio`='" . $_POST['citta_negozio'] . "',`provincia_negozio`='" . $_POST['provincia_negozio'] . "',`partita_iva_negozio`='" . $_POST['partita_iva_negozio'] . "',`codice_fiscale_negozio`='" . $_POST['codice_fiscale_negozio'] . "', `username`='" . $_POST['username'] . "', `password`='" . md5($_POST['password']) . "' WHERE id='" . $_GET['id'] . "';";
        echo "Cliente modificato con successo.<br/><br/>";
#echo $query;
        $db_magazzino->query($query);
    } elseif (!isset($_POST['submit']) && isset($_GET['id'])) {
        $query = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE id='" . $_GET['id'] . "';";
        $risultato = $db_magazzino->query($query);
        $cliente = $risultato->fetch_assoc();
    }
    ?>

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

            <?php menu(); ?>

            <form method="POST" action="">
                
                <div class="container-fluid form-group">
                    <h3>Sede legale<h3>
                            <div style="clear:left;float:left;width:20%;"><label>Nome</label></div><div style="clear:right;float:left;"><input class="form-control" type="text" name="nome_sede_legale" value="<?php echo $cliente['nome_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"><label>Indirizzo</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="indirizzo_sede_legale" value="<?php echo $cliente['indirizzo_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"><label>Citta</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="citta_sede_legale" value="<?php echo $cliente['citta_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"><label>Provincia</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="provincia_sede_legale" value="<?php echo $cliente['provincia_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"><label>Codice fiscale</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="codice_fiscale_sede_legale" value="<?php echo $cliente['codice_fiscale_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"><label>Partita IVA</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="partita_iva_sede_legale" value="<?php echo $cliente['partita_iva_sede_legale']; ?>"/></div>
                            <div style="clear:left;float:left;width:20%;"></div>

                            <div style="clear:left;float:left;width:20%;"><h3>Sede negozio<h3></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Nome</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="nome_negozio" value="<?php echo $cliente['nome_negozio']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Indirizzo</label></div><div style="clear:right;float:left;"><input  class="form-control" type="text" name="indirizzo_negozio" value="<?php echo $cliente['indirizzo_negozio']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Citta</label></div><div style="clear:right;float:left;"><input  class="form-control" type="text" name="citta_negozio" value="<?php echo $cliente['citta_negozio']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Provincia</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="provincia_negozio" value="<?php echo $cliente['provincia_negozio']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Codice fiscale</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="codice_fiscale_negozio" value="<?php echo $cliente['codice_fiscale_negozio']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Partita IVA</label></div><div style="clear:right;float:left;"><input  class="form-control" type="text" name="partita_iva_negozio" value="<?php echo $cliente['partita_iva_negozio']; ?>"/></div>
                                        <br>
                                        <div style="clear:left;float:left;width:20%;"><label>Username</label></div><div style="clear:right;float:left;"><input  class="form-control" type="text" name="username" value="<?php echo $cliente['username']; ?>"/></div>
                                        <div style="clear:left;float:left;width:20%;"><label>Password</label></div><div style="clear:right;float:left;"><input class="form-control"  type="password" name="password" value="default"/></div>
                                        <div style="clear:left;float:left;width:20%;"></div>
                                        <div style="clear:left;float:left;width:20%;"><button class="btn btn-default"  name="submit" type="submit">Ok</button></div>


                                        </div>
                                        </form>

                                        </body>
                                        </html>

                                        <?php
                                    }
                                    ?>
