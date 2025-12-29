<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class GroqServiceTest extends TestCase {
    public function testGroqServiceQuery() {
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
        $groq = new Ai('groq', 'dummy-key');
        
        // Inject Mock
        $groq->setCurl($mockCurl);

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
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}