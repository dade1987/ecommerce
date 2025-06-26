<?php
include_once("../classi/config.php");
include_once("../classi/auth.lib.php");
include_once("../classi/utils.lib.php");
include_once("../classi/license.lib.php");
include_once("../classi/funzioni.php");

$db_magazzino->query("DELETE FROM elenco_movimenti WHERE prezzo_pubblico_unitario=0;");

$db_magazzino->query("UPDATE elenco_movimenti SET prezzo_pubblico_unitario=CONCAT('-',prezzo_pubblico_unitario) WHERE reso=\"1\" AND prezzo_pubblico_unitario NOT LIKE \"-%\" AND causale=\"RESO\";");
    
?>