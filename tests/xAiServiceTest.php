<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;

class xAiServiceTest extends TestCase {
    public function testxAiServiceQuery() {
        // Load the xAI API key from file. Adjust the path if necessary.
        $xaiKeyPath = realpath(__DIR__ . '/../../../') . '/XAI-API-KEY.txt';
        if (!file_exists($xaiKeyPath)) {
            $this->markTestSkipped("XAI-API-KEY.txt not provided.");
        }
        $xaiKey = trim(file_get_contents($xaiKeyPath));

        $xai = new Ai('xai', $xaiKey);

        $result = $xai
            ->set('temperature', 0)
            ->set('model', 'grok-4-1-fast-non-reasoning')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('stream', false)
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from xai response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
    }
}
