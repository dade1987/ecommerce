<?php
   session_start();

   // store session data
   $_SESSION['punti_fidelity_card'] = $_POST['punti'];

   echo "assegnati ".$_POST['punti']." punti";
?>