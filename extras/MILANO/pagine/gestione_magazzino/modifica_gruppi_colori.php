<?php
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){
	menu(); 
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
$query="SELECT * FROM colori ORDER BY nome ASC;";
$risultato=$db_magazzino->query($query);
echo "<div style='width:45%;float:left;background-color:white;border:1px solid grey;padding:10px;'>";
echo "<h2>COLORI</h2>";

while($colore=$risultato->fetch_assoc())
	{
	echo "<a href='".url."/pagine/gestione_magazzino/ins_colore.php?id=".$colore['id']."'>Modifica</a> ".$colore['nome']."<br/>";
	}
echo "</div>";

        
$query="SELECT * FROM gruppi ORDER BY nome ASC;";
$risultato=$db_magazzino->query($query);
echo "<div style='width:45%;float:right;background-color:white;border:1px solid grey;padding:10px;'>";

echo "<h2>GRUPPI</h2>";

while($gruppo=$risultato->fetch_assoc())
	{
	echo "<a href='".url."/pagine/gestione_magazzino/ins_gruppo.php?id=".$gruppo['id']."'>Modifica</a> ".$gruppo['nome']."<br/>";
	}
echo "</div>";


$query="SELECT * FROM etichette_materiali ORDER BY materiale ASC;";
$risultato=$db_magazzino->query($query);
echo "<div style='width:45%;float:left;background-color:white;border:1px solid grey;padding:10px;'>";

echo "<h2>MATERIALI</h2>";

while($gruppo=$risultato->fetch_assoc())
	{
	echo "<a href='".url."/pagine/gestione_magazzino/ins_materiale.php?id=".$gruppo['id']."'>Modifica</a> ".$gruppo['materiale']."<br/>";
	}
echo "</div>";
?>

</body>
</form>
</body>
</html>

<?php
}
?>
