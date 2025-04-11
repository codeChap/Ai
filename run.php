<?php

require __DIR__ . '/vendor/autoload.php';

use codechap\ai\Ai;

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

// Groq Test
print "### Groq Test ### \n";
$groq = new Ai('groq', $groqKey);
$result = $groq
    ->set('temperature', 0)
    ->set('model', 'meta-llama/llama-4-scout-17b-16e-instruct')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', false)
    ->query(
    [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'https://upload.wikimedia.org/wikipedia/commons/f/f2/LPU-v1-die.jpg'
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'What is this image about?'
                ]
            ]
        ]
    ])
    ->all()
    ;
print_r($result);
print "\n\n";

die();

// Anthropic Test
print "### Anthropic Test ### \n";
$anthropic = new Ai('anthropic', $anthropicKey);
$result = $anthropic
    ->set('temperature', 0)
    ->set('model', 'claude-3-7-sonnet-20250219')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->all()
    ;
print_r($result);
print "\n\n";

// xAI Test
print "### xAI Test ### \n";
$xai = new Ai('xai', $xaiKey);
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




// Mistral Test
print "### Mistral Test ### \n";
$mistral = new Ai('mistral', $mistralKey);
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
$openai = new Ai('openai', $openaiKey);
print $openai
    ->set('temperature', 0)
    ->set('model', 'gpt-4.5-preview-2025-02-27')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('json', true)
    //->set('reasoningEffort', 'low')
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->one()
    ;
print "\n\n";

// Mistral Test
print "### Mistral Test ### \n";
$mistral = new Ai('mistral', $mistralKey);
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
