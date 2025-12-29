<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class GoogleServiceTest extends TestCase {
    public function testGoogleServiceQuery() {
        // Mock Response
        $mockResponse = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode(['capital' => 'Pretoria', 'city2' => 'Cape Town', 'city3' => 'Bloemfontein'])]
                        ]
                    ],
                    'finishReason' => 'STOP'
                ]
            ]
        ];

        // Create Mock Curl
        $mockCurl = $this->createMock(CurlInterface::class);
        $mockCurl->method('post')->willReturnSelf();
        $mockCurl->method('getResponse')->willReturn($mockResponse);

        // Instantiate AI with dummy key
        $google = new Ai('google', 'dummy-key');
        
        // Inject Mock
        $google->setCurl($mockCurl);

        $result = $google
            ->set('temperature', 0)
            ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
            ->set('json', true)
            ->query("What is the capital of South Africa? Only return the three in a JSON response.")
            ->all();

        $jsonResult = json_decode($result[0], true);
        $this->assertNotNull($jsonResult, "Expected valid JSON from google response.");
        $this->assertIsArray($jsonResult, "Expected JSON to be an array.");
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}
