<?php
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

list($status, $user) = auth_get_status();

if($status == AUTH_LOGGED){
?>
<html>
<head>


<script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
<script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
<script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>
<script>
function print(){
$("div#codice").printArea();
};
</script>
</head>
<body>
   
    
<?php 
echo "<div id=\"codice\"style=\"width:115px; height:30px;\">
<div class=\"testo\" style=\"	font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
 font-weight:bold; font-size:7px;text-align:center;float:left;\">".$_GET['codice']." &nbsp; </div><div class=\"testo\" style=\"	font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
 font-size:10px;text-align:center; float:right; font-weight:bold;\">".number_format($_GET['prezzo'],2)." &euro;</div>
	<img id=\"codice\" src=\"barcode.php?barcode=".$_GET['barcode']."\" height=\"20\" width=\"110\"></img>
        <div class=\"testo\" style=\"font-family:arial; font-size:9px;text-align:center;float:left;\">".$_GET['descrizione']." &nbsp; </div></div></div></div>";
?>
            
<br><a href="javascript:print();">Stampa etichetta</a>
<div style="font-family:arial;font-size:12px;">
<br><br>Nel caso ci siano problemi di stampa:
<br><b>In Chrome:</b>
<br>- Nell'anteprima di stampa, togliere la spunta in "Intestazione e piè di pagina"
<br>
<br><b>In Firefox e Internet Explorer:</b>
<br>- In "File>Impostazioni pagina" impostare tutti i campi di Intestazione e Piè di pagina in "Vuoto"
</div>

<?php } else
	non_autorizzato();
?>
</body>
</html>

