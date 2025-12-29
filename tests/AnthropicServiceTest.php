<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class AnthropicServiceTest extends TestCase {
    public function testAnthropicServiceQuery() {
        // Mock Response
        $mockResponse = [
            'content' => [
                [
                    'text' => json_encode(['capital' => 'Pretoria', 'city2' => 'Cape Town', 'city3' => 'Bloemfontein'])
                ]
            ]
        ];

        // Create Mock Curl
        $mockCurl = $this->createMock(CurlInterface::class);
        $mockCurl->method('post')->willReturnSelf();
        $mockCurl->method('getResponse')->willReturn($mockResponse);

        // Instantiate AI with dummy key
        $anthropic = new Ai('anthropic', 'dummy-key');
        
        // Inject Mock
        $anthropic->setCurl($mockCurl);

        $result = $anthropic
            ->set('temperature', 0)
            ->set('model', 'claude-sonnet-4-5-20250929')
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('stream', false)
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from anthropic response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}