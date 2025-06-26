<html>
<head>
</head>
<body>
<?php
include_once("./classi/config.php");
include_once("./classi/auth.lib.php");
include_once("./classi/utils.lib.php");
include_once("./classi/license.lib.php");


list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){

if(isset($_POST['action']) and $_POST['action'] == 'Invia'){
	$ret = reg_check_data($_POST);
	$status = ($ret === true) ? reg_register($_POST) : REG_ERRORS;
	
	switch($status){
		case REG_ERRORS:
			?>
			<span class="style1">Sono stati rilevati i seguenti errori:</span><br>
			<?php
			foreach($ret as $error)
				printf("<b>%s</b>: %s<br>", $error[0], $error[1]);
			?>
			<br>Premere "indietro" per modificare i dati
			<?php
		break;
		case REG_FAILED:
			echo "Registrazione Fallita a causa di un errore interno.";
		break;
		case REG_SUCCESS:
			echo "Registrazione avvenuta con successo.<br>
			Vi è stata inviata una email contente le istruzioni per confermare la registrazione.";
		break;
		}
	}
} else {
header('HTTP/1.0 401 Unauthorized');
	echo "non hai i permessi per visualizzare questa pagina";
}
?>
</body>
</html>
