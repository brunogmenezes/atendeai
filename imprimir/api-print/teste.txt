<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/api-print/autoload.php';

 
 
use Mike42\Escpos\Printer;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
 
 
try {
 
$connector = new WindowsPrintConnector("smb://192.168.15.148/TM-T20");
 
    
    /* Print a "Hello world" receipt" */
    $printer = new Printer($connector);
    $printer -> text("Hello World!\n");
    $printer -> cut();
    
    /* Close printer */
    $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
