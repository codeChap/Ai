<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class MistralServiceTest extends TestCase {
    public function testMistralServiceQuery() {
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
        $mistral = new Ai('mistral', 'dummy-key');
        
        // Inject Mock
        $mistral->setCurl($mockCurl);

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
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}