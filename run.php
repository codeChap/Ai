<?php

require_once __DIR__ . '/vendor/autoload.php';

use Codechap\Aiwrapper\Services\XaiService;

$apiKey = file_get_contents( realpath(__DIR__ . '/..') . '/X-API-KEY.txt');

$xai = new XaiService($apiKey);

// Define your system prompt
$systemPrompt = "You are a helpful AI assistant that provides clear, concise, and accurate responses.";

// Your user prompt
$userPrompt = "What is the capital of South Africa?";


    $response = $xai->query(
        $userPrompt,
        $systemPrompt,    // System message
        'grok-2-latest',  // Model
        0              // Temperature
    );
    echo $response . "\n";

?>