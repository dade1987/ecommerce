<html>
<head>

<link rel="stylesheet" type="text/css" href="../../css/stile.css">

<script src="http://code.jquery.com/jquery-latest.min.js"

        type="text/javascript"></script>

		<script>
	function print_bolla(){
	$(".menu").hide();
	$(".fattura").hide();
	$("p").hide();
	$("h2").hide();
	$("form").hide();
	$("table tr td").hide();
	$("table tr td.ddt").show();
	window.print();
	$("p").show();
	$("h2").show();
	$("form").show();
	$("table tr td").show();
	$(".fattura").show();
	$(".menu").show();
	$('a').not('.menu a').remove();
	};
	</script>

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

		$fattura=$db_fatture->query("SELECT * FROM bolle_resi WHERE id='".htmlentities($_GET['id']).";'");
	//var_dump($fattura);
		$fattura=$fattura->fetch_assoc();
	//var_dump($fattura);

		$fattura=$fattura['bolla'];
	//var_dump($fattura);


		echo $fattura;

	}
	else
		//Pagina per chi non è autorizzato	
		non_autorizzato();


?>

</body>