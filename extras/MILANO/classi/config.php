<?php

error_reporting(0); //E_ALL

date_default_timezone_set('Europe/Rome');

//Definisco la costante IVA al 22%

define('IVA', 22);
define('url','http://b-fashion.it');
define('casa_madre','ESSE ERRE SAS');
define('trasportatore','BRT');

$_CONFIG['host'] = "62.149.150.200";
$_CONFIG['user'] = "Sql840866";
$_CONFIG['pass'] = "277e46txnw"; //DKhp9rQIyF 
$_CONFIG['dbname'] = "Sql840866_1";

$_CONFIG['table_sessioni'] = "sessioni";
$_CONFIG['table_utenti'] = "utenti3";

$_CONFIG['expire'] = 216000; 
$_CONFIG['regexpire'] = 60; //in ore

$_CONFIG['check_table'] = array(
	"username" => "check_username",
	"password" => "check_global",
	"nome_negozio" => "check_global",
	"indirizzo" => "check_global",
	"partita_iva" => "check_global",
	"email" => "check_global",
	"action" => "check_global",
	"permessi" => "check_global"
);

function check_username($value){
	global $_CONFIG;
	global $conn;
	
	$value = trim($value);
	if($value == "")
		return "Il campo non pu� essere lasciato vuoto";
	$query = $conn->query("
	SELECT id
	FROM ".$_CONFIG['table_utenti']."
	WHERE username='".$value."'");
	if($query->num_rows != 0)
		return "Nome utente gi� utilizzato";
	
	return true;
}

function check_global($value){
	global $_CONFIG;
	
	$value = trim($value);
	if($value == "")
		return "Il campo non pu� essere lasciato vuoto";
	
	return true;
}


//--------------
define('AUTH_LOGGED', 99);
define('AUTH_NOT_LOGGED', 100);

define('AUTH_USE_COOKIE', 101);
define('AUTH_USE_LINK', 103);
define('AUTH_INVALID_PARAMS', 104);
define('AUTH_LOGEDD_IN', 105);
define('AUTH_FAILED', 106);

define('REG_ERRORS', 107);
define('REG_SUCCESS', 108);
define('REG_FAILED', 109);

//Connessione ai database

//Database dei movimenti in magazzino
$db_magazzino=$db_clienti = $db_fornitori=$db_fatture=$db_note_accredito=$db_fidelity_card=$db_giacenze=new mysqli($_CONFIG['host'], $_CONFIG['user'],$_CONFIG['pass'],$_CONFIG['dbname']);

if (mysqli_connect_error()) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
}

//Database degli utenti
$conn = new mysqli($_CONFIG['host'], $_CONFIG['user'], $_CONFIG['pass'], $_CONFIG['dbname']);

if (mysqli_connect_error()) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
}
?>
