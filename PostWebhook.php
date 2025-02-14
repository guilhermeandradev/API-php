<?php

require_once "index.php"; 


$webhookUrl = "https://webhook.fiqon.app/webhook/async/9e324339-4955-4a66-9b7d-ccbfac86a9a8";
$webhookToken = "1111111"; 

// Dados a serem enviados
$data = [
    "message" => " olá ",
    "nome" => " gui ",
    "email" => "gui@email.com"
];

$jsonData = json_encode($data);

// Inicializa cURL para enviar os dados ao Webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: ' . $webhookToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Erro ao enviar a requisição: " . curl_error($ch);
} else {
    echo "Código HTTP da resposta: $httpCode\n";
    echo "Resposta do Webhook:\n$response";
}


curl_close($ch);