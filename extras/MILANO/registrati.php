<?php
include_once("./classi/config.php");
include_once("./classi/auth.lib.php");
include_once("./classi/utils.lib.php");
include_once("./classi/license.lib.php");


list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED && license_has($user, "sede_centrale")){
?>
<html>
<head>
<title>Registra nuovo negozio</title>
</head>
<body>
<div align="center">
<h1>Registra nuovo negozio</h1>
<form action="./register.php" method="post">

<table border="0" width="300">
	<tr>
		<td>Nome negozio:</td>
		<td><input type="text" name="nome_negozio"></td>
	</tr>
	<tr>
		<td>Partita IVA</td>
		<td><input type="text" name="partita_iva"></td>
	</tr>
	<tr>
		<td>Indirizzo:</td>
		<td><input type="text" name="indirizzo"></td>
	</tr>
	<tr>
		<td>Username:</td>
		<td><input type="text" name="username"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password"></td>
	</tr>
	<tr>
		<td>Mail:</td>
		<td><input type="text" name="email"></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="action" value="Invia"></td>
	</tr>
</table>
</div>
</form>
</body>
</html>
<?php } else {
header('HTTP/1.0 401 Unauthorized');
echo "Impossibile visualizzare la pagina";
}
?>
