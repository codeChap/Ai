<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class OpenAiServiceTest extends TestCase {
    public function testOpenAiServiceQuery() {
        // Mock Response
        $mockResponse = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode(['capital' => 'Pretoria', 'city2' => 'Cape Town', 'city3' => 'Bloemfontein'])
                    ]
                ]
            ]
        ];

        // Create Mock Curl
        $mockCurl = $this->createMock(CurlInterface::class);
        $mockCurl->method('post')->willReturnSelf();
        $mockCurl->method('getResponse')->willReturn($mockResponse);

        // Instantiate AI with dummy key
        $openai = new Ai('openai', 'dummy-key');
        
        // Inject Mock
        $openai->setCurl($mockCurl);

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
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}