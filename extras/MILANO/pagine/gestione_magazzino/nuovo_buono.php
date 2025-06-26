<?php
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED) {
    menu();


    if (isset($_POST['submit']) && !isset($_GET['id'])) {

        $query = "UPDATE elenco_movimenti SET attivo=1, prezzo_pubblico_unitario='-" . $_POST['prezzo'] . "' WHERE barcode='" . $_POST['barcode'] . "' ORDER BY id DESC LIMIT 1;";
        $db_magazzino->query($query);
//echo $query;
        echo "Il buono sconto è stato aggiunto al database. Per usufruirne, bisogna che sia speso almeno per intero.<br/>Ricordati di ritirarlo quando sarà stato speso.<br/><br/>";
    }

    $db_magazzino->query($query);
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
            <form method="POST">
                <div class="container-fluid">
                    <h3>Inserisci il barcode della CouponCard</h3>
                    <div style="clear:left;float:left;width:20%;"><label>Prezzo</label></div><div style="clear:right;float:left;"><input class="form-control"  type="text" name="prezzo" placeholder="es. 1.34"  value=""/></div>
                    <div style="clear:left;float:left;width:20%;"><label>Barcode</label></div><div  style="clear:right;float:left;"><input class="form-control" type="text" name="barcode" placeholder="es. 1000003424234" value="" /></div>

                    <div style="clear:left;float:left;width:20%;"></div>
                    <div style="clear:left;float:left;width:20%;"><button class="btn btn-default"  name="submit" type="submit">Ok</button></div>
                </div>
                </form>

        </body>
    </html>
    <?php
}
?>