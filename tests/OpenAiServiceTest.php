<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\ai;

class OpenAiServiceTest extends TestCase {
    public function testOpenAiServiceQuery() {
        // Load the OpenAI API key from file. Adjust the path if necessary.
        $openaiKeyPath = realpath(__DIR__ . '/../../../') . '/OPENAI-API-KEY.txt';
        if (!file_exists($openaiKeyPath)) {
            $this->markTestSkipped("OPENAI-API-KEY not provided.");
        }
        $openaiKey = trim(file_get_contents($openaiKeyPath));

        $openai = new ai('openai', $openaiKey);

        $result = $openai
            ->set('temperature', 0)
            ->set('model', 'o3-mini-2025-01-31')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('json', true)
            ->set('reasoningEffort', 'low')
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->one();

        $jsonResult = json_decode($result, true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from openai response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
    }
} 