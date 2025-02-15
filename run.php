<?php

require __DIR__ . '/vendor/autoload.php';

use codechap\ai\ai;

//Get the API Key
$xaiKey = file_get_contents(realpath(__DIR__ . '/../../') . '/XAI-API-KEY.txt');
$groqKey = file_get_contents(realpath(__DIR__ . '/../../') . '/GROQ-API-KEY.txt');
$openaiKey = file_get_contents(realpath(__DIR__ . '/../../') . '/OPENAI-API-KEY.txt');
$anthropicKey = file_get_contents(realpath(__DIR__ . '/../../') . '/ANTHROPIC-API-KEY.txt');
$mistralKey = file_get_contents(realpath(__DIR__ . '/../../') . '/MISTRAL-API-KEY.txt');

$toolsDefinition = [
    [
        "type" => "function",
        "function" => [
            "name" => "get_current_temperature",
            "description" => "Get the current temperature in a given location",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "location" => [
                        "type" => "string",
                        "description" => "The city and state, e.g. San Francisco, CA"
                    ],
                    "unit" => [
                        "type" => "string",
                        "enum" => ["celsius", "fahrenheit"],
                        "default" => "celsius"
                    ]
                ],
                "required" => ["location"]
            ]
        ]
    ],
    [
        "type" => "function",
        "function" => [
            "name" => "get_current_ceiling",
            "description" => "Get the current cloud ceiling in a given location",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "location" => [
                        "type" => "string",
                        "description" => "The city and state, e.g. San Francisco, CA"
                    ]
                ],
                "required" => ["location"]
            ]
        ]
    ]
];

// xAI Test
print "### xAI Test ### \n";
$xai = new ai('xai', $xaiKey);
$result = $xai
    ->set('temperature', 0)
    ->set('model', 'grok-2-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->set('tools', $toolsDefinition)
    ->query("What is the Temperature in Johannesburg, South Africa?")
    ->all()
    ;
print_r($result);
print "\n\n";





// Anthropic Test
print "### Anthropic Test ### \n";
$anthropic = new ai('anthropic', $anthropicKey);
$result = $anthropic
    ->set('temperature', 0)
    ->set('model', 'claude-3-5-sonnet-20241022')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->all()
    ;

// Groq Test
print "### Groq Test ### \n";
$groq = new ai('groq', $groqKey);
$result = $groq
    ->set('temperature', 0)
    ->set('model', 'deepseek-r1-distill-llama-70b')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->all()
    ;
print_r(json_decode($result[0], true));
print "\n\n";




// Mistral Test
print "### Mistral Test ### \n";
$mistral = new ai('mistral', $mistralKey);
$result = $mistral
    ->set('temperature', 0)
    ->set('model', 'mistral-large-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->all()
    ;
print_r(json_decode($result[0], true));
print "\n\n";



// OpenAI Test
print "### OpenAI Test ### \n";
$openai = new ai('openai', $openaiKey);
print $openai
    ->set('temperature', 0)
    ->set('model', 'o3-mini-2025-01-31')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('json', true)
    ->set('reasoningEffort', 'low')
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->one()
    ;
print "\n\n";

// Mistral Test
print "### Mistral Test ### \n";
$mistral = new ai('mistral', $mistralKey);
print $mistral
    ->set('temperature', 0)
    ->set('model', 'mistral-large-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
print "\n\n";

?>
