<?php
session_start();
include_once("../../classi/phpgraphlib-master/phpgraphlib.php"); 

$graph=new PHPGraphLib(1000,500);
$data = unserialize(urldecode(stripslashes($_GET['mydata'])));
$graph->addData($data);
$graph->setTitle("Vendite");
$graph->setTextColor("blue");
$graph->createGraph();

?>
