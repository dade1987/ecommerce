<?php 
session_start();
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
?>
<html>
<head>
</head>
<body>
<?php
#echo htmlentities(var_export($_SESSION,true));

list($status, $user) = auth_get_status();


if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){

//preleva numerazione fattura
/*$query_identificativo=$db_fatture->query("SELECT * FROM db_fatture WHERE negozio='".$user['nome_negozio']."' ORDER BY id DESC LIMIT 1");
$ultimo_identificativo=$query_identificativo->fetch_assoc();
$identificativo=$ultimo_identificativo['numero_fattura']+1;*/
//var_dump($_SESSION);

$sql="INSERT INTO db_fatture (totale_fattura,fattura,negozio,intestatario,data)
VALUES ('-".$_SESSION['totale_fattura']."','".addslashes($_SESSION['tabella_fattura'])."','".$user['nome_negozio']."','".$_SESSION['dati_fattura']['intestatario']."','".date('Y-m-d H:i:s',strtotime('now'))."')";

//var_dump($_SESSION);
//$sql = "INSERT INTO 'db_fatture' ('id','fattura') VALUES (NULL, '".$_SESSION['fattura']."')";

$risultato=$db_fatture->query($sql);

$numero_fattura=$db_fatture->insert_id;

$sql="INSERT INTO numerazione_ddt (fattura_corrispondente)
VALUES (".$numero_fattura.")";

$risultato=$db_fatture->query($sql);


unset($_SESSION['numero_ddt']);
unset($_SESSION['tabella_fattura']);
unset($_SESSION['dati_fattura']);
unset($_SESSION['totale_fattura']);



if($risultato!=FALSE)
echo "NOTA MEMORIZZATA!";
else
echo "ERRORE DEL DATABASE!";


}
else
echo "ERRORE DI AUTENTICAZIONE!";
?>



</body>
</html>
