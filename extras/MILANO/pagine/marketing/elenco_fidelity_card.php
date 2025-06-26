<html>
<head>
       <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
 </head>
<body>

<?php

include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");
list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED)
	{
	menu();

	echo "<h1>LISTA DELLE FIDELITY CARD</h1>";
        echo "<h2 style=\"color:darkblue;\">Premere CTRL+F per cercare per nome, cognome, o barcode.</h2>";

        if(license_has($user, "sede_centrale"))
	$query="SELECT * FROM db_fidelity_card ORDER BY cognome ASC;";
        else
        $query="SELECT * FROM db_fidelity_card WHERE negozio_riferimento='".$user['nome_negozio']."' ORDER BY cognome ASC;";

        
	$risultato=$db_fidelity_card->query($query);

	echo "<table style=\"width:90%;\">";
	echo "<tr id=\"intestazione\">";
	echo "<td>ID</td>";
        echo "<td>Barcode</td>";
	echo "<td>Nome</td>";
	echo "<td>Cognome</td>";
        echo "<td>Luogo</td>";
	echo "<td>Telefono</td>";
	echo "<td>Email</td>";
        echo "<td>Punti</td>";
	echo "<td>Rettifica</td>";
        echo "<td>Extra</td>";
	echo "</tr>";

	$i=0;
	while($colonna=$risultato->fetch_assoc())
		{

		//Funzione per fare le righe della tabella di colori diversi
		//se il valore di $i è un numero pari
		if($i % 2==0) 
			//la variabile colore diventa bianca altrimenti azzurrina
			$colore='bianco'; else $colore='azzurrino';

		echo "<tr id=\"".$colore."\">";
		echo "<td>".sprintf("%09d",$colonna['numero'])."</td>";
                echo "<td>".$colonna['numero_vecchio']."</td>";
		echo "<td>".$colonna['nome']."</td>";
		echo "<td>".$colonna['cognome']."</td>";
                echo "<td>".$colonna['negozio_riferimento']."</td>";
		echo "<td>".$colonna['telefono']."</td>";
		echo "<td>".$colonna['email']."</td>";
                echo "<td>".$colonna['punti']."</td>";
                echo "<td><a href=\"ins_mod_fidelity_card.php?numero=".$colonna['numero']."\">Rettifica</a></td>";
                if($colonna['punti']>=40)
                echo "<td><a href='./associa_buono_sconto.php?numero_fidelity=".$colonna['numero']."'>Associa buono sconto</a></td>";
                else echo "<td></td>";
		echo "</tr>";
		$i++;
		}
	echo "</table>";
	}
	else
		non_autorizzato();

?>
    <!--Start of Tawk.to Script-->
<script type="text/javascript">
var $_Tawk_API={},$_Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/559ba55c04c33fb6400d686d/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>