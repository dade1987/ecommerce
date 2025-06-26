<?php
include_once("./classi/config.php");
include_once("./classi/auth.lib.php");

list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED & auth_get_option("TRANSICTION METHOD") == AUTH_USE_LINK) {
    $link = "?uid=" . $_GET['uid'];
} else
    $link = '';
?>
<html>
    <head>
        <title>Sistema gestionale Giada 3.0</title>
        <link href="./bootstrap/css/superhero.min.css" rel="stylesheet">
        <link href="./bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="./bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
    </head>
    <body style="background-image: url('./immagini/gFlorian2016.jpg');background-size: 100%;">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <img src="./immagini/logo.png" class="img-responsive" style="width:100%;" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <?php
                    switch ($status) {
                        case AUTH_LOGGED:
                            header("Refresh: 0;URL=./pagine/gestione_magazzino/elenco_movimenti.php?switch=Scarichi");
                            break;
                        case AUTH_NOT_LOGGED:
                            ?>

                            <div class="form-group">
                                <form action="./login.php<?= $link ?>" method="post">
                                    <br/><br/><input class="form-control text-center" type="text" name="uname" placeholder="Nome Utente" style="font-size:40px;height:auto;">
                                    <br/><br/><input class="form-control text-center" type="password" name="passw" placeholder="Password" style="font-size:40px;height:auto;">
                                    <br/><br/><input class="form-control text-center btn btn-info" type="submit" name="action" value="Log In" style="font-size:40px;height:auto;font-size: 40px;height: auto;background-color: black;border: 1px solid silver;">
                                </form>
                            </div>
                            <?php
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>
