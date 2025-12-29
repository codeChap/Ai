<?php

namespace codechap\ai\Interfaces;

interface CurlInterface {
    public function post(array $headers, string $url, array $data = []): self;
    public function get(array $headers, string $url): self;
    public function getResponse(): array;
}
