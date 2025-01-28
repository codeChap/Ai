<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Tests;

use Codechap\Aiwrapper\AIWrapper;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Curl;

class AIWrapperTest extends TestCase
{
    private const DUMMY_API_KEY = 'test-api-key-123';
    private string $serviceDir;
    private string $mockServicePath;

    protected function setUp(): void
    {
        // Setup test environment
        $this->serviceDir = __DIR__ . '/../src/Services';
        if (!is_dir($this->serviceDir)) {
            mkdir($this->serviceDir, 0777, true);
        }

        $this->mockServicePath = $this->serviceDir . '/TestService.php';
        $this->createMockService();
    }

    protected function tearDown(): void
    {
        // Cleanup after tests
        if (file_exists($this->mockServicePath)) {
            unlink($this->mockServicePath);
        }
    }

    private function createMockService(): void
    {
        $mockServiceContent = '<?php
declare(strict_types=1);
namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Curl;

class TestService implements AIServiceInterface {
    private string $apiKey;
    private string $lastResponse;
    
    public function __construct(string $apiKey) {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }
        $this->apiKey = $apiKey;
    }
    
    public function query(string|array $prompt): Curl {
        if (empty($prompt)) {
            throw new \InvalidArgumentException("Prompt cannot be empty");
        }
        $this->lastResponse = "Test response from " . $this->apiKey;
        return new Curl();
    }

    public function content(): string {
        return $this->lastResponse;
    }

    public function get(string $name) {
        return null;
    }

    public function set(string $name, $value): self {
        return $this;
    }
}';
        file_put_contents($this->mockServicePath, $mockServiceContent);
    }

    public function test_can_initialize_existing_service(): void
    {
        $wrapper = new AIWrapper('test', self::DUMMY_API_KEY);
        $wrapper->query('Hello');
        $response = $wrapper->content();

        $this->assertStringContainsString(self::DUMMY_API_KEY, $response);
    }

    public function test_throws_exception_for_invalid_service(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service non_existent_service not found');
        new AIWrapper('non_existent_service', self::DUMMY_API_KEY);
    }

    public function test_throws_exception_for_empty_service_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service name cannot be empty');
        new AIWrapper('', self::DUMMY_API_KEY);
    }

    public function test_throws_exception_for_empty_api_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key cannot be empty');
        new AIWrapper('test', '');
    }

    public function test_query_with_empty_prompt(): void
    {
        $wrapper = new AIWrapper('test', self::DUMMY_API_KEY);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prompt cannot be empty');
        $wrapper->query('');
    }

    public function test_service_name_is_case_insensitive(): void
    {
        $wrapper = new AIWrapper('TEST', self::DUMMY_API_KEY);
        $wrapper->query('Hello');
        $response = $wrapper->content();

        $this->assertStringContainsString(self::DUMMY_API_KEY, $response);
    }
}
