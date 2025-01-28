<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Tests\Services;

use Codechap\Aiwrapper\Services\XaiService;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class XaiServiceTest extends TestCase
{
    private const DUMMY_API_KEY = 'test-api-key-123';

    public function test_can_initialize_xai_service(): void
    {
        $service = new XaiService(self::DUMMY_API_KEY);
        $this->assertInstanceOf(XaiService::class, $service);
    }

    public function test_throws_exception_for_empty_api_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key cannot be empty');
        new XaiService('');
    }

    public function test_query_with_empty_prompt(): void
    {
        $service = new XaiService(self::DUMMY_API_KEY);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prompt cannot be empty');
        $service->query('');
    }

    public function test_query_with_empty_prompts_array(): void
    {
        $service = new XaiService(self::DUMMY_API_KEY);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prompts array cannot be empty');
        $service->query([]);
    }
} 