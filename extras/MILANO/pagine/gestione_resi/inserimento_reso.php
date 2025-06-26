<?php
session_start(); //Inizio la sessione
include_once("../../classi/utils.lib.php");
include_once("../../classi/license.lib.php");
include_once("../../classi/funzioni.php");
include_once("../../classi/config.php");
include_once("../../classi/auth.lib.php");

#error_reporting(E_ALL);

list($status, $user) = auth_get_status();

//&& license_has($user, "affiliato")
if($status == AUTH_LOGGED){
?>
<html>
<head>
       <script src="../../classi/printarea/jquery-1.10.2.js" type="text/JavaScript" language="javascript"></script>
        <script src="../../classi/printarea/jquery-ui-1.10.4.custom.js"></script>
        <script src="../../classi/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

        <script src="../../bootstrap/js/bootstrap.min.js" type="text/JavaScript" language="javascript"></script>

        <link href="../../bootstrap/css/flatly.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/docs.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
 
<script>
function stampa_bolla(){
$('.menu').hide();
$('div.no_stampa').hide();
window.print();
$('.menu').show();
$('div.no_stampa').show();
}
</script>

</head>
<body>
<?php menu(); ?>
<h1 class="no_stampa">INSERIMENTO RESI</h1>

<form name="form1" method="post" action="./inserimento_reso.php">
<div class="no_stampa">
    <label for="barcode"><b>Codice a barre</b></label>
<input type="text" name="barcode" >
<label for="causale"><b>Causale</b></label>
<select name="causale" >
    <option>Reso per difetto</option>
    <option>Reso per rettifica</option>
</select>
<input type="submit" name="submit" value="Elenca prodotto">
<button name="button">Cancella tutto</button>
<button name="carica" onclick="stampa_bolla();">Invia lista resi</button></div>


<?php
//fai la query dei prodotti in carico in magazzino basati su barcode
//in ogni offset di sessione es.$_SESSION[0][etc],$_SESSION[1][etc] memorizzi i dati delle tabelle
//fai un while per mostrarli tutti nella pagina
//al via, fai una query per ogni offset, sottrai i prodotti dal carico e metti in carico all'altro negozio (definito in un menu a tendina) e metti come causale Vendita ad affiliato


$query="SELECT numero from ddt WHERE mittente='".$user['nome_negozio']."' ORDER BY numero DESC LIMIT 1;";
#echo $query;                        

         $numero=$db_magazzino->query($query);
                        
                        if($numero->num_rows>0){
                        
                        $numero=$numero->fetch_assoc();
                        
                        $numero_ddt=$numero['numero']+1;
                        }
                            else
                            {
                                 $numero_ddt=1;
                        }
                        

	
	if(isset($_POST['carica'])&&strlen($_POST['causale'])>=10&&strlen($_POST['causale'])<=50)
		{
		$i=0;
		while(isset($_POST[$i]))
			{
			if($_POST[$i]>=0)
				{
				$query="INSERT INTO elenco_movimenti (numero_ddt,data, causale, codice,sconto_affiliato, sconto_pubblico, quantita, prezzo_pubblico_unitario,descrizione,gruppo,colore,barcode,fornitore,cliente,reso) VALUES ('".$numero_ddt."','".date('Y-m-d H:i:s',strtotime('now'))."','Reso - ".$_POST['causale']."','".$_SESSION['articolo'][$i]['codice']."','".$_SESSION['articolo'][$i]['sconto_azienda']."','".$_SESSION['articolo'][$i]['sconto_pubblico']."','".$_SESSION['articolo'][$i]['quantita_acquistata']."','".$_SESSION['articolo'][$i]['prezzo_pubblico_unitario']."','".$_SESSION['articolo'][$i]['descrizione']."','".$_SESSION['articolo'][$i]['gruppo']."','".$_SESSION['articolo'][$i]['colore']."','".$_SESSION['articolo'][$i]['barcode']."','".$user['nome_negozio']."','".casa_madre."',1)"; 
				//echo $query;
                                

			$db_magazzino->query($query);
				}
			$i++;			
			}	
                       
                        
                            
                        $query="INSERT INTO ddt (tipo,numero,mittente,ddt,negozio,data) VALUES ('RESTITUZIONE','".$numero_ddt."','".$user['nome_negozio']."','".utf8_encode($_SESSION['table'])."','".$user['nome_negozio']."','".date('Y-m-d H:i:s',strtotime('now'))."');";
                               #echo htmlentities($query);
                                $ddt=$db_magazzino->query($query);
                                
                        unset($_SESSION['table']);
			session_unset();
			session_destroy();
			echo "<br><br>Dati caricati correttamente.<br>";				
		}

	$rimanenze=array();
	if(isset($_POST['button']))
		{
		session_unset();
		session_destroy();
		}

	if(isset($_SESSION['articolo']))
		$i=count($_SESSION['articolo']);	
		else
		$i=0;		
	
//echo var_dump($_SESSION['articolo']);
	if(isset($_POST['submit']))
			{
            $_POST['barcode']=substr($_POST['barcode'],0,-1);

		if(isset($_POST['barcode']))
			{
                    	
                    	if(verifica_esistenza_valore($_POST['barcode'],$_SESSION['articolo'])===false )
                        {    $rimanenze=rimanenze($user['nome_negozio'], $_POST['barcode'], NULL, NULL, NULL); 
                                if(!empty($rimanenze[0])) {$_SESSION['articolo'][$i]=$rimanenze[0];}

                        }

			//echo "prova";			
				//var_dump($rimanenze);	
                                $s=count($_SESSION['articolo']);
                                for($i=0;$i<$s;$i++)
                                {
                                    $_SESSION['articolo'][$i]['quantita_acquistata']=$_POST[$i];
                                    if(empty($_SESSION['articolo'][$i]['quantita_acquistata'])) { $_SESSION['articolo'][$i]['quantita_acquistata']=1; }
                                    elseif($_POST['barcode']==$_SESSION['articolo'][$i]['barcode']) { $_SESSION['articolo'][$i]['quantita_acquistata']++; }    
                                   # var_dump($_SESSION);
                                }
                          
			//echo var_dump($_SESSION['articolo']);
			}
			}
	if(isset($_SESSION['articolo']))
		$i=count($_SESSION['articolo']);	
		else
		$i=0;

			$c1=0;
                        $destinatario=$db_magazzino->query("SELECT * FROM ".$_CONFIG['table_utenti']." WHERE nome_negozio='".casa_madre."' LIMIT 1;")->fetch_assoc();
                        $mittente=$db_magazzino->query("SELECT * FROM ".$_CONFIG['table_utenti']." WHERE nome_negozio='".$user['nome_negozio']."' LIMIT 1;")->fetch_assoc();
                        #$destinatario=$destinatario[0]; $mittente=$mittente[0];
                                            
                        #$numero_ddt=$db_fatture->query("SHOW TABLE STATUS LIKE 'ddt';")->fetch_assoc();
                    	#$numero_ddt=$numero_ddt['Auto_increment'];
                        
                        /*var_dump($destinatario);  var_dump($mittente); 
                        echo "SELECT * FROM ".$_CONFIG['table_utenti']." WHERE nome_negozio='".casa_madre."' LIMIT 1;";
                        echo "SELECT * FROM ".$_CONFIG['table_utenti']." WHERE nome_negozio='".$user['nome_negozio']."' LIMIT 1;";*/
                                
                        $data=date("d-m-Y H:i:s");
                        
                        $_SESSION['table']="<table border=\"1\" bgcolor=\"white\" class=\"stampa\">";
                        $_SESSION['table'].="<tr><td><strong>RAGIONE SOCIALE DEL CLIENTE</strong></td><td><strong>DESTINAZIONE MERCE</strong></td></tr>";
                        $_SESSION['table'].="<tr><td><strong>SPETT.LE</strong></td><td><strong>SPETT.LE</strong></td></tr>";                       
                        $_SESSION['table'].="<tr><td>".$mittente['nome_negozio']."</td><td>".$destinatario['nome_negozio']."</td></tr>";
                        $_SESSION['table'].="<tr><td>".$mittente['indirizzo_negozio']."</td><td>".$destinatario['indirizzo_negozio']."</td></tr>";
                        $_SESSION['table'].="<tr><td>".$mittente['citta_negozio']." ".$mittente['provincia_negozio']."</td><td>".$destinatario['citta_negozio']." ".$destinatario['provincia_negozio']."</td></tr>";
                        $_SESSION['table'].="<tr><td colspan=\"2\"></td></tr>";
                        $_SESSION['table'].="<tr><td><strong>Tipo di documento</strong></td><td><strong>Numero DDT</strong></td></tr>";
                        $_SESSION['table'].="<tr><td>Documento di trasporto</td><td>".$numero_ddt."</td></tr>";
                        $_SESSION['table'].="<tr><td><strong>Data</strong></td><td><strong>Causale</strong></td></tr>";
                        $_SESSION['table'].="<tr><td>".$data."</td><td>RESO</td></tr>";
                        $_SESSION['table'].="<tr><td colspan=\"2\"><strong>Contributo CONAI assolto ove dovuto</strong></td></tr>";
                        $_SESSION['table'].="</table>";
                                
			$_SESSION['table'].="<br><table style=\"width:90%;\" class=\"stampa\"><tr id=\"intestazione\"><td>id</td><td>Barcode</td><td>Codice</td><td>Descrizione</td>
						<td>Gruppo</td><td>Colore</td><td>Disponibilita max</td><td>Quantita da inviare</td></tr>";
			while($i>0&&$c1<$i&&$i!=NULL&&$_SESSION['articolo'])
				{
				$_SESSION['table'].= "<tr id=\"bianco\">";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['id']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['barcode']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['codice']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['descrizione']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['gruppo']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['colore']."</td>";
				$_SESSION['table'].= "<td>".$_SESSION['articolo'][$c1]['quantita']."</td>";
				$_SESSION['table'].= "<td><input type=\"text\" readonly name=\"$c1\" value=\"".$_SESSION['articolo'][$c1]['quantita_acquistata']."\"></td>";
				$_SESSION['table'].= "</tr>";
		
				$c1++;
				}
	$_SESSION['table'].="</table>";
	echo $_SESSION['table'];
        
        $pezzi=count($_SESSION['articolo']);
        
        $totale_pezzi=0;
        for($i=0;$i<$pezzi;$i++) { $totale_pezzi+=$_SESSION['articolo'][$i]['quantita_acquistata']; }
        
        echo "<input nome=\"totale_pezzi\" value=\"Totale pezzi: $totale_pezzi\">";
} else non_autorizzato();
	

//-----------------------//
//echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/>";
//var_dump($_SESSION['table']);
//-----------------------//

?>
<script>
$('input[name="barcode"]').focus();
</script>
</form>
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
