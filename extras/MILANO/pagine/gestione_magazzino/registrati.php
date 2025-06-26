<?php include_once('funzioni.php'); ?>

<html>
<head>
<meta charset='utf-8'>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" type="text/css" href="./style.css"> 
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script src="newslampeggiante.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="reset.css" media="screen" />
<link rel="stylesheet" type="text/css" href="advanced.css" media="screen" />

<!-- Google Analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-57495103-1', 'auto');
  ga('send', 'pageview');

</script>

<style>
    div.form
    {
    width:80%;
    margin-bottom:10px;
    margin-left:auto;
    margin-right:auto;
    }
    
    div.form > input
    {    
    box-shadow: 0px 2px 5px 0px blue; 
    }
    
    div.form>button,div.form>input
    { 
    border:1px solid cyan;
    border-radius:3px;
    width:100%;
    padding:5px;
    text-align:center;
    }
    
    div.form > label
    {
    font-family: Arial;
    font-size: 14px;
    margin-left: auto;
    margin-right: auto;
    display: block;
    width: 45%;
    text-align: center;
    }
    
</style>
</head>
<body>


<div id="nav">
    <?php menu_intestazione(); ?>
<div id="container">
<div id="chisiamo">
<div class="mappa" style="width:99%;overflow:auto;">
<form>
    <center>
        <p><b>Pagina di registrazione</b><br/><br/>
        *** Attenzione: tutti i campi sono obbligatori ***</p><br/></center>
    <form method="POST" action="registrati.php">
    <div class="form"><label for="ragione_sociale">Ragione sociale</label></div>
    <div class="form"><input type="text" name="ragione_sociale" placeholder="es. Azienda S.r.l." required></div>
    <div class="form"><label for="nome">Nome</label></div>
    <div class="form"><input type="text" name="nome" placeholder="es. Gianni" required></div>
    <div class="form"><label for="cognome">Cognome</label></div>
    <div class="form"><input type="text" name="cognome" placeholder="es. Rossi" required></div>
    <div class="form"><label for="qualifica_aziendale">Qualifica professionale</label></div>
    <div class="form"><input type="text" name="qualifica_aziendale" placeholder="es. Operatore dei servizi logistici" required></div>    
    <div class="form"><label for="email">Email</label></div>
    <div class="form"><input type="email" name="email" placeholder="es. esempio@esempio.it" required></div>
    <div class="form"><label for="telefono">Telefono</label></div>
    <div class="form"><input type="tel" name="telefono" placeholder="es. 555-123456"required></div>
    <div class="form"><label for="password">Password</label></div>
    <div class="form"><input type="password" name="password" placeholder="es. Password123!" required></div>
    <div class="form"><button type="submit">Registrati all'Area Clienti</button>    
    </form> 
    <center><p><br/>Dopo aver inviati i propri dati tramite modulo, FIS S.r.l. provveder&aacute; a verificarli e a confermare l'iscrizione.</p></center>

</div>
</div>
<div id="text-container">
	<div id="text-inside">
	<div class="chisiamo">
	<b>FIS S.r.l.</b>	
	<br>
	<br><b>Matteo Da Lio</b>
	<br>Ice cream and Dessert specialist
	<br>324-9297656
	<br>
	<br><b>Matteo Guberti</b>
	<br>Operation Manager
	<br>344-1394186
	<br>
	</div><div class="chisiamo">
	Sede Legale Via G. Tempesta 42/5
	<br>30033 Noale (VE) ITALY
	<br>Sede Operativa Via Pacinotti 30/A
	<br>30033 Noale (VE) ITALY
	<br><br>
	<br>Codice Fisc. e P.IVA 04258250275
	<br>www.fisnet.it
	<br>fis@fisnet.it
	</div></div>
</div>


</div> 
</div>
</body>
</html>
