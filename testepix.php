<?php
class PixBRCodeGenerator {
    private $pixKey;
    private $merchantName;
    private $merchantCity;
    private $transactionAmount;
    private $merchantCategoryCode;
    private $referenceLabel;
    private $pointOfInitiation;
    
    public function __construct($pixKey, $merchantName, $merchantCity = 'SAO PAULO') {
        $this->pixKey = $pixKey;
        $this->merchantName = substr($merchantName, 0, 25);
        $this->merchantCity = substr($merchantCity, 0, 15);
        $this->merchantCategoryCode = '0000';
        $this->pointOfInitiation = '12'; // 12 = uso único, 11 = estático
    }
    
    public function setAmount($amount) {
        $this->transactionAmount = number_format($amount, 2, '.', '');
    }
    
    public function setReferenceLabel($label) {
        $this->referenceLabel = substr($label, 0, 25);
    }
    
    public function setStaticQRCode() {
        $this->pointOfInitiation = '11'; // QR Code estático (pode ser reutilizado)
    }
    
    private function buildEMVField($id, $value) {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $length . $value;
    }
    
    private function buildEMVTemplate($id, $fields) {
        $content = '';
        foreach ($fields as $subId => $value) {
            $content .= $this->buildEMVField($subId, $value);
        }
        return $this->buildEMVField($id, $content);
    }
    
    public function generatePayload() {
        $payload = '';
        
        // 00 - Payload Format Indicator (M)
        $payload .= $this->buildEMVField('00', '01');
        
        // 01 - Point of Initiation Method (O)
        if ($this->pointOfInitiation) {
            $payload .= $this->buildEMVField('01', $this->pointOfInitiation);
        }
        
        // 26 - Merchant Account Information (M)
        $merchantAccountInfo = [
            '00' => 'br.gov.bcb.pix', // GUI
            '01' => $this->pixKey     // Chave PIX
        ];
        $payload .= $this->buildEMVTemplate('26', $merchantAccountInfo);
        
        // 52 - Merchant Category Code (M)
        $payload .= $this->buildEMVField('52', $this->merchantCategoryCode);
        
        // 53 - Transaction Currency (M)
        $payload .= $this->buildEMVField('53', '986'); // BRL
        
        // 54 - Transaction Amount (O)
        if (isset($this->transactionAmount)) {
            $payload .= $this->buildEMVField('54', $this->transactionAmount);
        }
        
        // 58 - Country Code (M)
        $payload .= $this->buildEMVField('58', 'BR');
        
        // 59 - Merchant Name (M)
        $payload .= $this->buildEMVField('59', $this->merchantName);
        
        // 60 - Merchant City (M)
        $payload .= $this->buildEMVField('60', $this->merchantCity);
        
        // 62 - Additional Data Field Template (M)
        $additionalData = [];
        
        // 05 - Reference Label (M dentro do template 62)
        if ($this->referenceLabel) {
            $additionalData['05'] = $this->referenceLabel;
        } else {
            $additionalData['05'] = '***'; // Valor padrão conforme tabela
        }
        
        // Payment System Specific Template (opcional dentro do 62)
        // $paymentSystemTemplate = [
        //     '00' => 'BR.GOV.BCB.BRCODE' // GUI do sistema de pagamento
        // ];
        // $additionalData['50'] = $this->buildEMVTemplate('00', $paymentSystemTemplate);
        
        $payload .= $this->buildEMVTemplate('62', $additionalData);
        
        // CRC16 (M)
        $crc = $this->calculateCRC16($payload . '6304');
        $payload .= '6304' . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
        
        return $payload;
    }
    
    private function calculateCRC16($data) {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }
        return $crc & 0xFFFF;
    }
    
    public function generateQRCodeImage($size = 300) {
        $payload = $this->generatePayload();
        $encodedPayload = urlencode($payload);
        
        // Usando Google Charts API para gerar QR Code
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedPayload}";
    }
}

// Exemplo de uso para R$ 5,00
$pixKey = '99981075765';
$amount = 5.00;

$pixGenerator = new PixBRCodeGenerator($pixKey, 'Recebedor PIX', 'SAO PAULO');
$pixGenerator->setAmount($amount);
$pixGenerator->setReferenceLabel('PAGAMENTO5REAIS');
$pixGenerator->setStaticQRCode(); // Remove esta linha para QR Code de uso único

$payload = $pixGenerator->generatePayload();
$qrCodeUrl = $pixGenerator->generateQRCodeImage();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code PIX - Padrão BR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #008000;
            margin: 15px 0;
        }
        .qrcode-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 2px dashed #ddd;
            border-radius: 10px;
        }
        .pix-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .instructions {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Pagamento via PIX</h1>
            <div class="amount">R$ <?= number_format($amount, 2, ',', '.') ?></div>
        </div>
        
        <div class="pix-info">
            <p><strong>Chave PIX:</strong> <?= $pixKey ?></p>
            <p><strong>Beneficiário:</strong> Recebedor PIX</p>
            <p><strong>Cidade:</strong> São Paulo</p>
        </div>
        
        <div class="qrcode-container">
            <img src="<?= $qrCodeUrl ?>" alt="QR Code PIX" width="300" height="300">
            <p style="color: #666; font-size: 14px; margin-top: 10px;">
                Escaneie este QR Code com seu app bancário
            </p>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <button onclick="copyToClipboard('<?= $pixKey ?>')">📋 Copiar Chave PIX</button>
            <button onclick="showPayload()">🔍 Ver Payload</button>
        </div>
        
        <div class="instructions">
            <h3>📱 Como pagar:</h3>
            <ol>
                <li>Abra o app do seu banco</li>
                <li>Selecione a opção "Pagar com PIX"</li>
                <li>Escaneie o QR Code acima</li>
                <li>Confirme o pagamento de <strong>R$ 5,00</strong></li>
            </ol>
        </div>
        
        <div id="payloadInfo" style="display: none; background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h4>Payload BR Code:</h4>
            <code style="word-break: break-all; font-size: 12px;"><?= $payload ?></code>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text)
                .then(() => alert('Chave PIX copiada com sucesso!'))
                .catch(err => console.error('Erro ao copiar:', err));
        }
        
        function showPayload() {
            const payloadInfo = document.getElementById('payloadInfo');
            payloadInfo.style.display = payloadInfo.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>