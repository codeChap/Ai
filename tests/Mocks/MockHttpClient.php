<?php

declare(strict_types=1);

namespace Codechap\Aiwrapper\Tests\Mocks;

class MockHttpClient
{
    private string $expectedResponse;
    private bool $shouldThrowError = false;

    public function __construct(string $expectedResponse = '')
    {
        $this->expectedResponse = $expectedResponse;
    }

    public function setThrowError(bool $shouldThrow): void
    {
        $this->shouldThrowError = $shouldThrow;
    }

    public function post(string $url, array $options = []): string
    {
        if ($this->shouldThrowError) {
            throw new \RuntimeException('Error communicating with server');
        }
        return $this->expectedResponse;
    }
} 