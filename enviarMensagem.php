<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://netconnect.netsolutions.com.br/message/sendText/ZAPNETSOLUTIONS",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'number' => '5599981075765',
    'text' => 'teste'
  ]),
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "apikey: 27B6EF355013-42F7-B838-175DA0DB3057"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}