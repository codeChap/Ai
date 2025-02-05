<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\ai;

class GroqServiceTest extends TestCase {
    public function testGroqServiceQuery() {
        // Load the Groq API key from file. Adjust the path if necessary.
        $groqKeyPath = realpath(__DIR__ . '/../../../') . '/GROQ-API-KEY.txt';
        if (!file_exists($groqKeyPath)) {
            $this->markTestSkipped("GROQ-API-KEY not provided.");
        }
        $groqKey = trim(file_get_contents($groqKeyPath));

        $groq = new ai('groq', $groqKey);

        $result = $groq
            ->set('temperature', 0)
            ->set('model', 'deepseek-r1-distill-llama-70b')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('stream', false)
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from groq response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
    }
} 