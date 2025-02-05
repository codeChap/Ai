<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\ai;

class MistralServiceTest extends TestCase {
    public function testMistralServiceQuery() {
        // Load the Mistral API key from file. Adjust the path if necessary.
        $mistralKeyPath = realpath(__DIR__ . '/../../../') . '/MISTRAL-API-KEY.txt';
        if (!file_exists($mistralKeyPath)) {
            $this->markTestSkipped("MISTRAL-API-KEY not provided.");
        }
        $mistralKey = trim(file_get_contents($mistralKeyPath));

        $mistral = new ai('mistral', $mistralKey);

        $result = $mistral
            ->set('temperature', 0)
            ->set('model', 'mistral-large-latest')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('stream', false)
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from mistral response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
    }
} 