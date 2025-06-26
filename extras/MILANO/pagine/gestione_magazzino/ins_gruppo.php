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

if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){
	menu(); 
        
        
if(isset($_POST['submit']) && !isset($_GET['id'])){
	$query="INSERT INTO gruppi (`nome`, `sconto_azienda`) VALUES ('".$_POST['gruppo']."','".$_POST['sconto']."');";
echo "Il gruppo è stato aggiunto con successo.<br/><br/>";
}
elseif(isset($_POST['submit']) && isset($_GET['id'])){
    	$query="UPDATE gruppi SET nome='".$_POST['gruppo']."',sconto_azienda='".$_POST['sconto']."' WHERE id='".$_GET['id']."';";
echo "Il gruppo è stato modificato con successo.<br/><br/>";
}
$db_magazzino->query($query);


?>

<form method="POST">   
    <div class="container-fluid">
<div style="clear:left;float:left;width:20%;"><label>Gruppo</label></div><div style="clear:right;float:left;"><input type="text"  class="form-control"  name="gruppo" value=""/></div>
<div style="clear:left;float:left;width:20%;"><label>Sconto</label></div><div style="clear:right;float:left;"><input type="text"  class="form-control" name="sconto" value=""/></div>

<div style="clear:left;float:left;width:20%;"></div>
<div style="clear:left;float:left;width:20%;"><button class="btn btn-default"  name="submit" type="submit">Ok</button></div>
    </div>
</form>
</body>
</html>
<?php
}
?>