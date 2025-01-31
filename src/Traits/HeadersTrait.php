<?php

namespace codechap\ai\Traits;

trait HeadersTrait
{
    /**
     * Get the default headers for API requests
     *
     * @param array $headers Additional headers to add
     * @return array
     */
    protected function getHeaders(array $headers = []): array
    {
        return array_merge([
            'Content-Type'  => 'application/json',
        ], $headers);
    }

    /**
     * Get headers specifically for streaming responses
     *
     * @param string $apiKey The API key for authentication
     * @return array
     */
    protected function getStreamHeaders(string $apiKey): array
    {
        return array_merge($this->getHeaders($apiKey), [
            'Accept'        => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
        ]);
    }

    /**
     * Add custom headers to the existing headers
     *
     * @param array $existingHeaders The existing headers
     * @param array $customHeaders Additional headers to add
     * @return array
     */
    protected function addCustomHeaders(array $existingHeaders, array $customHeaders): array
    {
        return array_merge($existingHeaders, $customHeaders);
    }

    /**
     * Convert associative array headers to proper cURL header format
     */
    protected function formatHeaders(array $headers): array
    {
        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "$key: $value";
        }
        return $curlHeaders;
    }
} 