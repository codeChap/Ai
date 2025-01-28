<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Curl;

class TestService implements AIServiceInterface
{
    public function __construct(string $apiKey)
    {
        // Test constructor
    }

    public function query(string|array $prompt): Curl
    {
        // Test implementation
        return new Curl();
    }

    public function content(): string
    {
        return '';
    }

    public function get(string $name)
    {
        return null;
    }

    public function set(string $name, $value): self
    {
        return $this;
    }
} 