<?php
function license_get_list(){
	global $conn;
	$result = $conn->query("
	SELECT *
	FROM permessi
	");
	$data = array();
	while($tmp = $result->fetch_assoc()){
		array_push($data, $tmp);
	};
	
	return $data;
}

function license_change($userid, $perms){
	global $conn;
	$result = $conn->query("
	UPDATE ".$_CONFIG['table_utenti']."
	SET permessi='".$perms."' WHERE id='".$userid."'
	");
}

function license_new_id(){
	global $conn;
	$result = $conn->query("
	SELECT (id*2) as next_id
	FROM permessi
	ORDER BY id DESC
	LIMIT 0,1
	");
	if($result->num_rows != 0){
		return mysqli_result($result, 0, "next_id");
	}else{
		return 1;
	}
}

function license_add($name, $desc){
	global $conn;
	$result = $conn->query("
	INSERT INTO permessi
	VALUES ('".license_new_id()."','".$name."','".$desc."')
	");
}

function license_user_get_perms($id){
	global $conn,$_CONFIG;
	return intval(mysqli_result($conn->query("
	SELECT permessi
	FROM ".$_CONFIG['table_utenti']."
	WHERE id = '".$id."'
	"), 0 ,'permessi'));
}

function license_has($user, $perm){
	global $conn;
	$permessi = license_user_get_perms(user_get_id($user));
	$perm = mysqli_result($conn->query("
	SELECT id
	FROM permessi
	WHERE nome = '".$perm."'
	"), 0 ,'id');
	return intval($permessi) & intval($perm);
}

function license_get($user){
	$permessi = license_user_get_perms(user_get_id($user));
	$perm_list = array();
	foreach(license_get_list() as $perm){
		if($permessi & intval($perm['id'])){
			$perm_list[] = $perm;
		}
	}	
	return $perm_list;
}
?>
