<?php

if (!file_exists(__DIR__)) {
    mkdir(__DIR__, 0777, true);
}

function checkCurl() {
    if (!function_exists('curl_init')) {
        die('
            <div style="text-align: center; padding: 20px; font-family: Arial, sans-serif;">
                <h1>Erreur : cURL n\'est pas installé</h1>
                <p>Pour utiliser cette application, vous devez :</p>
                <ol style="display: inline-block; text-align: left;">
                    <li>Ouvrir le fichier php.ini</li>
                    <li>Rechercher la ligne ";extension=curl"</li>
                    <li>Retirer le point-virgule au début de la ligne</li>
                    <li>Sauvegarder le fichier</li>
                    <li>Redémarrer votre serveur PHP</li>
                </ol>
            </div>
        ');
    }
}

function fetchFromAniList($query, $variables = []) {
    checkCurl();
    
    $url = 'https://graphql.anilist.co';
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $data = [
        'query' => $query,
        'variables' => $variables
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    
    if ($response === false) {
        die('Erreur cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    $decodedResponse = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Erreur de décodage JSON: ' . json_last_error_msg());
    }

    return $decodedResponse;
}
