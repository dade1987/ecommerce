<?php
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){
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
<?php
menu(); 

$query="SELECT * FROM ".$_CONFIG['table_utenti']." WHERE 1;";
$risultato=$db_magazzino->query($query);

while($cliente=$risultato->fetch_assoc())
	{
	echo "<a href='".url."/pagine/gestione_magazzino/ins_mod_cliente.php?id=".$cliente['id']."'>Modifica</a> ".$cliente['username']."<br/>";
	}

?>

</body>
</form>
</body>
</html>

<?php
}
?>
