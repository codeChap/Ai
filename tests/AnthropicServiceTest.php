<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\ai;

class AnthropicServiceTest extends TestCase {
    public function testAnthropicServiceQuery() {
        // Load the Anthropic API key from file. Adjust the path if necessary.
        $anthropicKeyPath = realpath(__DIR__ . '/../../../') . '/ANTHROPIC-API-KEY.txt';
        if (!file_exists($anthropicKeyPath)) {
            $this->markTestSkipped("ANTHROPIC-API-KEY not provided.");
        }
        $anthropicKey = trim(file_get_contents($anthropicKeyPath));

        $anthropic = new ai('anthropic', $anthropicKey);

        $result = $anthropic
            ->set('temperature', 0)
            ->set('model', 'claude-3-5-sonnet-20241022')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('stream', false)
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from anthropic response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
    }
} 