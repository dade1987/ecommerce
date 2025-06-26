<?php
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if ($status == AUTH_LOGGED) {
    menu();
    ?>
    <html>
        <head>
            <link rel="stylesheet" type="text/css" href="../../css/stile.css"> 
            <!-- Inclusione jQuery -->
            <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
            <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
            <script type="text/javascript" src="chat_jquery.js"></script>


        </head>
        <body>
            <h1>Chat</h1>

            <?php
            if (license_has($user, "sede_centrale")) {
                echo '<div style="width:25%;float:left;" id="negozi">';

                $query = "SELECT * FROM " . $_CONFIG['table_utenti'] . " WHERE 1;";
                $risultato = $db_magazzino->query($query);

                while ($cliente = $risultato->fetch_assoc()) {
                    if ($cliente['nome_negozio'] != casa_madre) {
                        echo "<input style='width:90%;' type='button' id='" . $cliente['nome_negozio'] . "' value='" . $cliente['nome_negozio'] . "'><br/><br/>";
                    }
                }
                echo '</div>';
                echo '<div style="width:75%;float:right;">';
            }
            else echo '<div style="width:100%;float:right;">';
            ?>
            <form method="POST">
                <input type="hidden" name="io" value="<?php echo $user['nome_negozio']; ?>">
                <input type="hidden" name="lui" value="">
                <textarea name="messaggi_ricevuti" readonly="readonly" style="width:100%;clear:both;height:500px;overflow:auto;margin-bottom:20px;"></textarea>
                <input type="text" name="messaggio" placeholder="Scrivi qui il tuo messaggio"  style="width:100%;clear:both;margin-bottom:20px;" >
                <input type="button" name="invia" value="Invia"> 
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>