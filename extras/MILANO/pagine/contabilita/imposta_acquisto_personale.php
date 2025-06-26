<?php

session_start();



if (isset($_POST['numero_articolo'])) {
    $num = $_POST['numero_articolo'];

    $_SESSION['articolo'][$num]["acquisto_personale"] = 1; 
    
}

