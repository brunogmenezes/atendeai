<?php
require_once 'libs/phpqrcode.php';

/**
 * Gera o payload PIX padrão Bacen
 */
function gerarPayloadPix($chave, $valor, $nomeRecebedor = "MINHA EMPRESA", $cidade = "SAO PAULO")
{
    $valor = number_format($valor, 2, '.', '');

    $payload = "000201" .
        "26" . str_pad(strlen("0014BR.GOV.BCB.PIX01" . strlen($chave) . $chave), 2, "0", STR_PAD_LEFT) .
        "0014BR.GOV.BCB.PIX01" . strlen($chave) . $chave .
        "52040000" . // Merchant Category Code
        "5303986" .  // Moeda BRL
        "54" . str_pad(strlen($valor), 2, "0", STR_PAD_LEFT) . $valor .
        "5802BR" .
        "59" . str_pad(strlen($nomeRecebedor), 2, "0", STR_PAD_LEFT) . $nomeRecebedor .
        "60" . str_pad(strlen($cidade), 2, "0", STR_PAD_LEFT) . $cidade .
        "62070503***"; // txid

    // Adiciona CRC16
    $payload .= "6304";
    $payload .= crcChecksum($payload);

    return $payload;
}

function crcChecksum($str)
{
    $crc = 0xFFFF;
    for ($c = 0; $c < strlen($str); $c++) {
        $crc ^= ord($str[$c]) << 8;
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
            $crc &= 0xFFFF;
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

/**
 * Exibe QR Code diretamente na tela
 */
function exibirQrCode($payload)
{
    QRcode::png($payload, false, QR_ECLEVEL_M, 5);
}
