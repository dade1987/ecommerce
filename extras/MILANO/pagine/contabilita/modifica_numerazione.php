<html>
<head>
       <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
 
<meta charset="utf-8">
</head>

<body>
<?php
include_once("../../classi/utils.lib.php");

include_once("../../classi/license.lib.php");

include_once("../../classi/funzioni.php");

include_once("../../classi/config.php");

include_once("../../classi/auth.lib.php");

menu(); 
list($status, $user) = auth_get_status();
if($status == AUTH_LOGGED && license_has($user, "sede_centrale"))
	{
        if(isset($_POST['elimina_ultima_fattura']))
        {
            $db_fatture->query("DELETE FROM db_fatture WHERE numero_fattura=".((int)$_POST['numero_fattura']-1)." LIMIT 1;");
        }
        
        if(isset($_POST['elimina_ultimo_ddt']))
        {
            $db_fatture->query("DELETE FROM ddt WHERE numero=".((int)$_POST['numero_ddt']-1)." LIMIT 1;");
            echo "DELETE FROM ddt WHERE numero='".((int)$_POST['numero_ddt']-1)."' LIMIT 1;";
        }
            
	if(isset($_POST['submit'])){
		$db_fatture->query("INSERT INTO db_fatture (negozio,anno,numero_fattura) VALUES ('".$user['nome_negozio']."','".date('Y')."',".htmlentities($_POST['numero_fattura']).");");
		$db_fatture->query("INSERT INTO ddt (mittente,anno,numero) VALUES  ('".$user['nome_negozio']."','".date('Y')."',".htmlentities($_POST['numero_ddt']).");");
		}
	
	$ultimo_ddt=numero_ddt();
	$ultima_fattura=numero_fattura();
	
    echo "<form id=\"form1\" name=\"form1\" method=\"post\">
  <p>
    <div style=\"width:30%\">
	<label for=\"numero_ddt\">Prossimo numero DDT:</label>
	</div>  
	  <div style=\"width:70%\">
	  <input class=\"form-control\"  type=\"number\" name=\"numero_ddt\" id=\"number\" min=\"$ultimo_ddt\" value=\"$ultimo_ddt\">
	  
  (non puoi scendere sotto il numero $ultimo_ddt in quanto quel ddt è già presente nel database)</div></p>
  <p>
    <div style=\"width:30%\"><label for=\"numero_fattura\">Prossimo numero fattura:</label></div>
    <div style=\"width:70%\"><input class=\"form-control\" type=\"number\" name=\"numero_fattura\" id=\"number2\" min=\"$ultima_fattura\" value=\"$ultima_fattura\">
    (non puoi scendere sotto il numero $ultima_fattura in quanto quella fattura è già presente nel database)</div></p>
  <p>
    <input class=\"btn btn-default\" type=\"submit\" name=\"submit\" id=\"submit\" value=\"Modifica numerazioni\">
    <input class=\"btn btn-default\" type=\"submit\" name=\"elimina_ultima_fattura\" value=\"Elimina ultima fattura\">
    <input class=\"btn btn-default\" type=\"submit\" name=\"elimina_ultimo_ddt\" value=\"Elimina ultimo DDT\">


  </p>
</form>";
    } 
		else
		//Pagina per chi non è autorizzato	
		non_autorizzato();
?>

</body>
