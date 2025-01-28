<?php

require_once __DIR__ . '/vendor/autoload.php';

use Codechap\Aiwrapper\AIWrapper;

$apiKey = file_get_contents(realpath(__DIR__ . '/..') . '/X-API-KEY.txt');

// Create an instance of AIWrapper
$ai = new AIWrapper('xai', $apiKey);

// Configure parameters and send query
$response = $ai
    ->set('temperature', 0)
    ->set('model', 'grok-2-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;

print_r($response);

?>