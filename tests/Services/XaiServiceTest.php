<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Tests\Services;

use Codechap\Aiwrapper\Services\XaiService;
use Codechap\Aiwrapper\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class XaiServiceTest extends TestCase
{
    private const DUMMY_API_KEY = 'xai-xVbhhCJQXBxC0g7cUWvdNWycCUii70tUz6mXoKWUpm0GPiJAPFqXmkume75ER1fjbvwobXGbRoeZtpFr';
    private MockHttpClient $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = new MockHttpClient();
    }

    public function test_can_initialize_xai_service(): void
    {
        $service = new XaiService(self::DUMMY_API_KEY, $this->mockClient);
        $this->assertInstanceOf(XaiService::class, $service);
    }

    public function test_can_query_xai_service(): void
    {
        $expectedResponse = json_encode([
            'response' => 'Hello! I am doing well, thank you for asking.'
        ]);
        
        $this->mockClient = new MockHttpClient($expectedResponse);
        $service = new XaiService(self::DUMMY_API_KEY, $this->mockClient);
        
        $response = $service->query('Hello, how are you?');
        
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
        $this->assertEquals('Hello! I am doing well, thank you for asking.', $response);
    }

    public function test_handles_api_error(): void
    {
        $this->mockClient->setThrowError(true);
        $service = new XaiService(self::DUMMY_API_KEY, $this->mockClient);
        
        $this->expectException(RuntimeException::class);
        $service->query('Hello, how are you?');
    }
} 