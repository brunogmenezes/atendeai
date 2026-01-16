<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/api-print/autoload.php';

define( 'MYSQL_HOST', 'HOST' );
define( 'MYSQL_USER', 'USER' );
define( 'MYSQL_PASSWORD', 'PASSWORD' );
define( 'MYSQL_DB_NAME', 'db_estacionamento' );

$PDO = new PDO( 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB_NAME, MYSQL_USER, MYSQL_PASSWORD );
try
{
    $PDO = new PDO( 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB_NAME, MYSQL_USER, MYSQL_PASSWORD );
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}

$PLACA=$_GET['placa'];
$PDO->exec("set names utf8");
$sql = "SELECT * FROM tbl_veiculos where placa= '$PLACA'";
$result = $PDO->query( $sql );
$rows = $result->fetchAll();
 
use Mike42\Escpos\Printer;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
 
 
try {
 
$connector = new WindowsPrintConnector("smb://192.168.15.148/TM-T20");
 $printer = new Printer($connector);


					$ENTRADA= date("m/d/Y 10:i:s");
					$SAIDA= date("m/d/Y H:i:s");
					$PLACA=$rows[0]['placa'];
					$TEMPO="00:41";
					$VALOR="3,00";
					$numero=$rows[0]['idveiculos'];

	
		
try {
    $tux = EscposImage::load("resources/Logo_x.png", false);

    //$printer -> text("These example images are printed with the older\nbit image print command. You should only use\n\$p -> bitImage() if \$p -> graphics() does not\nwork on your printer.\n\n");
   $printer -> setJustification(Printer::JUSTIFY_CENTER);
    $printer -> bitImage($tux, Printer::IMG_DOUBLE_WIDTH | Printer::IMG_DOUBLE_HEIGHT);
    //$printer -> text("Large Tux in correct proportion (bit image).\n");
} catch (Exception $e) {
    /* Images not supported on your PHP, or image file not found */
 //   $printer -> text($e -> getMessage() . "\n");
}
	
	
	
	
	
				/* TITULO SISTEMA */

$printer -> setEmphasis(true);
$printer -> setJustification(Printer::JUSTIFY_CENTER);
//$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
$printer -> text("SISTEMA DE ESTACIONAMENTO\n");
//$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
$printer -> text("ZAPBRASIL TELECOM\n");
$printer -> initialize();
$printer -> text("INTEM  CODIGO  DESC       QTD  R$   TOTAL\n");
$printer -> text("01     0005    PAO DOCE   10  3,00 30,00 \n");
$printer -> text("02     0005    PAO SAL    10  3,00 30,00 \n");
$printer -> text("03     0005    AÇUCAR     10  3,00 30,00 \n");
$printer -> text("04     0005    ALFACE     10  3,00 30,00 \n");
$printer -> text("05     0005    TOMATE     10  3,00 30,00 \n");
$printer -> text("06     0005    SAL        10  3,00 30,00 \n");
//$printer -> text("----------------------------\n");
//$printer -> text("Entrada: $ENTRADA\n");
//$printer -> text("----------------------------\n");
//$printer -> text("Saida: $SAIDA\n");
//$printer -> text("----------------------------\n"); 
		
///////////////placa do veiculo//////////////////////
$printer -> setEmphasis(true);
//$printer -> setTextSize(8,3);
$printer -> setJustification(Printer::JUSTIFY_CENTER);
//$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
//$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_EMPHASIZED);


$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH |Printer::MODE_DOUBLE_HEIGHT);

//$printer -> text("Placa: ".$PLACA."\n");
$printer -> selectPrintMode();
$printer -> setEmphasis(false);
///////////////placa do veiculo//////////////////////
  $printer -> initialize();

///////////////valores//////////////////////																
//$printer -> text("----------------------------\n");
//$printer -> text("Tempo: $TEMPO\n");
//$printer -> text("___________________________\n");
//$printer -> text("Valor: R$ ".$VALOR."\n");
//$printer -> text("----------------------------\n\n\n");
///////////////valores//////////////////////	
			   

 


/* Barcodes - see barcode.php for more detail */
$printer -> setJustification(Printer::JUSTIFY_CENTER);
$printer->setBarcodeHeight(80);
$printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
$printer->barcode($numero);
$printer->feed();
 

 
 
    $printer -> cut();
    
    /* Close printer */
    $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}

 //$var = "<script>javascript:history.back(-2)</script>";
 //echo $var;









