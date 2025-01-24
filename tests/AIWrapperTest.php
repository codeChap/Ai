<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Tests;

use Codechap\Aiwrapper\AIWrapper;
use PHPUnit\Framework\TestCase;

class AIWrapperTest extends TestCase
{
    private const DUMMY_API_KEY = 'test-api-key-123';

    public function test_can_initialize_existing_service(): void
    {
        // Create a mock service file
        $serviceDir = __DIR__ . '/../src/Services';
        if (!is_dir($serviceDir)) {
            mkdir($serviceDir, 0777, true);
        }

        $mockServicePath = $serviceDir . '/TestService.php';
        file_put_contents($mockServicePath, '<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Services;

readonly class TestService {
    public function __construct(private string $apiKey) {}
    
    public function query(string $prompt): string {
        return "Test response from " . $this->apiKey;
    }
}
');

        try {
            // Test service initialization
            $wrapper = new AIWrapper('test', self::DUMMY_API_KEY);
            $response = $wrapper->query('Hello');

            $this->assertStringContainsString(self::DUMMY_API_KEY, $response);
        } finally {
            // Cleanup
            if (file_exists($mockServicePath)) {
                unlink($mockServicePath);
            }
        }
    }

    public function test_throws_exception_for_invalid_service(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AIWrapper('non_existent_service', self::DUMMY_API_KEY);
    }
}
