<?php
require_once('../../classi/barcode/BCGFontFile.php');
require_once('../../classi/barcode/BCGColor.php');
require_once('../../classi/barcode/BCGDrawing.php');
require_once('../../classi/barcode/BCGean13.barcode.php');


//creo il codice a barre
$colorFont = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);
$font = new BCGFontFile('../../classi/font/Arial.ttf', 18);
$code = new BCGean13();
$code->setScale(2); // Resolution
$code->setThickness(30); // Thickness
$code->setForegroundColor($colorFont); // Color of bars
$code->setBackgroundColor($colorBack); // Color of spaces
$code->setFont($font); // Font (or 0)
$code->parse($_GET['barcode']); // Text

$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);
$drawing->draw();

header('Content-Type: image/png');
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

?>

?>
