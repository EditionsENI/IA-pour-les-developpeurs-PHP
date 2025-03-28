<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_PORT => "8003",
  CURLOPT_URL => "http://127.0.0.1:8003/model/predictions",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'samples' => [
        [
                0,
                0
        ]
    ]
  ]),
  CURLOPT_HTTPHEADER => [
    "Accept: */*",
    "Content-Type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  var_dump($response);
}