<?php

function get_users_list(){
	global $conn, $_CONFIG;
	$query="
	SELECT *
	FROM ".$_CONFIG['table_utenti']."
	";
	//echo $query;
	$result = $conn->query($query);
	$data = array();
	while($tmp = $result->fetch_assoc()){
		array_push($data, $tmp);
	};
	
	return $data;
}

function user_get_id($user){
	global $conn, $_CONFIG;
	$query="
	SELECT id
	FROM ".$_CONFIG['table_utenti']."
	WHERE username='".$user['username']."' and password='".$user['password']."'
	";
	$conn->query($query);
	return mysqli_result($conn->query($query), 0, 'id');
}

function mysqli_result($ris, $riga, $field=0) {

	    $riga=$ris->fetch_assoc();
	    return $riga[$field];
} 

?>
