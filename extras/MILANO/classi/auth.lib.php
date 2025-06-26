<?php

$_AUTH = array(
	"TRANSICTION METHOD" => AUTH_USE_COOKIE
);

function auth_set_option($opt_name, $opt_value){
	global $_AUTH;
	
	$_AUTH[$opt_name] = $opt_value;
}

function auth_get_option($opt_name){
	global $_AUTH;
	
	return is_null($_AUTH[$opt_name])
		? NULL
		: $_AUTH[$opt_name];
}

function auth_clean_expired(){
	global $_CONFIG;
	global $conn;
	$result = $conn->query("SELECT creation_date FROM ".$_CONFIG['table_sessioni']." WHERE uid='".auth_get_uid()."'");
	if($result){
		$data = $result->fetch_array(MYSQLI_BOTH);
		if($data['creation_date']){
			if($data['creation_date'] + $_CONFIG['expire'] <= time()){
				switch(auth_get_option("TRANSICTION METHOD")){
					case AUTH_USE_COOKIE:
						setcookie('uid','',time()+1,'/');
					break;
					case AUTH_USE_LINK:
						global $_GET;
						$_GET['uid'] = NULL;
					break;
				}
			}
		}
	}
	
	$conn->query("
	DELETE FROM ".$_CONFIG['table_sessioni']."
	WHERE creation_date + ".$_CONFIG['expire']." <= ".time()
	);
}

function auth_get_uid(){
	
	$uid = NULL;
	
	switch(auth_get_option("TRANSICTION METHOD")){
		case AUTH_USE_COOKIE:
			global $_COOKIE;
			$uid = isset($_COOKIE['uid']) ? $_COOKIE['uid'] : NULL;
		break;
		case AUTH_USE_LINK:
			global $_GET;
			$uid = isset($_GET['uid']) ? $_GET['uid'] : NULL;
		break;
	}

	return $uid ? $uid : NULL;
}

function auth_get_status(){
	global $_CONFIG;
	global $conn;

	auth_clean_expired();
	$uid = auth_get_uid();
	if(is_null($uid))
		return array(100, NULL);
	
	$query="SELECT U.nome_negozio, U.nome_sede_legale, U.username as username, U.password as password
	FROM ".$_CONFIG['table_sessioni']." S,".$_CONFIG['table_utenti']." U
	WHERE S.user_id = U.id and S.uid = '".$uid."'";
	$result = $conn->query($query);
	
	if($result->num_rows != 1)
		return array(100, NULL);
	else{
		$user_data = $result->fetch_assoc();
		return array(99, array_merge($user_data, array('uid' => $uid)));
	}
}

function auth_login($uname, $passw){
	global $_CONFIG;
	global $conn;

	$result = $conn->query("
	SELECT *
	FROM ".$_CONFIG['table_utenti']."
	WHERE username='".$uname."' and password=MD5('".$passw."')"
	);
	
	if($result->num_rows != 1){
		return array(AUTH_INVALID_PARAMS, NULL);
	}else{
		$data = $result->fetch_array(MYSQLI_BOTH);
		return array(AUTH_LOGEDD_IN, $data);
	}
}

function auth_generate_uid(){

	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));
	return md5(uniqid(mt_rand(), true));
}

function auth_register_session($udata){
	global $_CONFIG;
	global $conn;

	$uid = auth_generate_uid();
	$query="
	INSERT INTO ".$_CONFIG['table_sessioni']."

	(uid, user_id, creation_date)

	VALUES

	('".$uid."', '".$udata['id']."', ".time().")

	";

	$conn->query($query);
	if(!$conn->insert_id){
		return array(AUTH_LOGEDD_IN, $uid);
	}else{
		return array(AUTH_FAILED, NULL);
	}
}

function auth_logout(){
	global $_CONFIG;
	global $conn;

	$uid = auth_get_uid();
	
	if(is_null($uid)){
		return false;
	}else{
		$conn->query("
		DELETE FROM ".$_CONFIG['table_sessioni']."
		WHERE uid = '".$uid."'"
		);
		return true;
	}
}
?>
