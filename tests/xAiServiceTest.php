<?php

use PHPUnit\Framework\TestCase;
use codechap\ai\Ai;
use codechap\ai\Interfaces\CurlInterface;

class xAiServiceTest extends TestCase {
    public function testxAiServiceQuery() {
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
        $xai = new Ai('xai', 'dummy-key');
        
        // Inject Mock
        $xai->setCurl($mockCurl);

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
        $this->assertArrayHasKey('capital', $jsonResult);
    }
}