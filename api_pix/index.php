<?php

require __DIR__.'/vendor/autoload.php';

use \App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

$chavepix = '+5599981075765';
//INSTANCIA PRINCIPAL DO PAYLOAD PIX
$obPayload = (new Payload)->setPixKey($chavepix)
                            ->setDescription('Pagamento do pedido 12345')
                            ->setMerchantName('BIJU20')
                            ->setMerchantCity('IMPERATRIZ')
                            ->setAmount(100.00)
                            ->setTxid('BIJU20');

//CODIGO DE PAGAMENTO PIX
$payloadQrCode = $obPayload->getPayload();

//QR CODE
$obQrCode = new QrCode($payloadQrCode);

//IMAGEM DO QRCODE
$image = (new Output\Png)->output($obQrCode,100);
?>
<h1>QR CODE PIX</h1>
<br>
<img src="data:image/png;base64, <?=base64_encode($image)?>">
<br><br>
Código pix:<br>
<strong><?=$payloadQrCode?></strong>