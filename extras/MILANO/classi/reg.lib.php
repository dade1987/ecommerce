<?php
function reg_register($data){
	//registro l'utente
	global $_CONFIG;
	global $conn;
	
	$id = reg_get_unique_id();

	$query="INSERT INTO `utenti` (`id`, `nome_negozio`, `username`, `password`, `partita_iva`, `indirizzo`, `email`, `regdata`, `uid`) VALUES (NULL, '".$data['nome_negozio']."', '".$data['username']."', MD5('".$data['password']."'), '".$data['partita_iva']."', '".$data['indirizzo']."', '".$data['email']."', '".time()."', '".$id."');";
	echo $data['username'];
	echo "<br>";
	echo $data['password'];
	echo "<br>";
	$conn->real_query($query);
	
	//Decommentate la riga seguente per testare lo script in locale
	//echo "<a href=\"http://localhost/Articoli/autenticazione/2/scripts/confirm.php?id=".$id."\">Conferma</a>";
	if($conn->insert_id){
		echo "Registrazione effettuata";
	}else return REG_FAILED;
}



function reg_get_unique_id(){
	//restituisce un ID univoco per gestire la registrazione
	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));
	return md5(uniqid(mt_rand(), true));
}

function reg_check_data(&$data){
	global $_CONFIG;
	
	$errors = array();
	
	foreach($data as $field_name => $value){
		$func = $_CONFIG['check_table'][$field_name];
		if(!is_null($func)){
			$ret = $func($value);
			if($ret !== true)
				$errors[] = array($field_name, $ret);
		}
	}
	
	return count($errors) > 0 ? $errors : true;
}

function reg_confirm($id){
	global $_CONFIG;
	global $conn;
	
	$query = $conn->query("
	UPDATE ".$_CONFIG['table_utenti']."
	SET temp='0'
	WHERE uid='".$id."'");
	
	return ($query->affected_rows () != 0) ? REG_SUCCESS : REG_FAILED;
}
?>
