<?php

//Get the API Key
$xaiKey = file_get_contents(realpath(__DIR__ . "/../../") . "/XAI-API-KEY.txt");
$groqKey = file_get_contents(
    realpath(__DIR__ . "/../../") . "/GROQ-API-KEY.txt"
);
$openaiKey = file_get_contents(
    realpath(__DIR__ . "/../../") . "/OPENAI-API-KEY.txt"
);
$anthropicKey = file_get_contents(
    realpath(__DIR__ . "/../../") . "/ANTHROPIC-API-KEY.txt"
);
$mistralKey = file_get_contents(
    realpath(__DIR__ . "/../../") . "/MISTRAL-API-KEY.txt"
);

require __DIR__ . "/vendor/autoload.php";

use codechap\ai\Ai;

$questions = [
    "What version are you?",

    "How many r\'s are there in the word strawberry? Only output the number.",

    "What 8 letter word can have a letter taken away and it still makes a word. Take another letter away and it still makes a word. Keep on doing that until you have one letter left. What is the word? Only ouput the word.",

    "Alice has M sisters and M brothers. How many sisters does Alice's brother have? Only output the answer.",

    "Generate code in Rust to make a 3D spinning tesseract."
];

// Mistral Test
$mistral = new Ai("mistral", $mistralKey);
print strtoupper("Mistral Test | 'Mistral Small latest'") . PHP_EOL . PHP_EOL;
foreach ($questions as $k => $q1) {
    print "#" . ($k + 1) . " " . $q1 . PHP_EOL;
    $result = $mistral
        ->set("temperature", 0)
        ->set("model", "mistral-small-latest")
        ->set("systemPrompt", "You are a helpful assistant from planet earth.")
        ->query($q1)
        ->one();
    print_r($result);
    print "\n\n";
    sleep(1);
}
