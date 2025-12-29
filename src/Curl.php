<?php

namespace codechap\ai;

use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Exceptions\ResponseException;

class Curl {

    use HeadersTrait;

    private array $response;
    private array $content;
    private $curl;

    /**
     * Execute a POST request to the specified URL
     *
     * @param array $headers The HTTP headers to include
     * @param string $url The URL to send the request to
     * @param array $data The data to send in the request body
     * @return self Returns the current instance for method chaining
     * @throws \RuntimeException If cURL initialization or JSON encoding fails
     * @throws \codechap\ai\Exceptions\ResponseException If the HTTP request fails
     */
    public function post(array $headers, string $url, array $data = []): self {
        $this->initializeCurl();
        $this->content = [];

        $isStreaming = $data['stream'] ?? false;
        $headers = $this->prepareHeaders($headers);

        $this->setCurlOptions('POST', $url, $isStreaming, $headers, $this->prepareData($data));

        return $isStreaming ?
            $this->handleStreamingResponse() :
            $this->handleStandardResponse();
    }

    /**
     * Execute a GET request to the specified URL
     *
     * @param array $headers The HTTP headers to include
     * @param string $url The URL to send the request to
     * @return self Returns the current instance for method chaining
     * @throws \RuntimeException If cURL initialization fails
     * @throws \codechap\ai\Exceptions\ResponseException If the HTTP request fails
     */
    public function get(array $headers, string $url): self {
        $this->initializeCurl();
        $this->content = [];

        $headers = $this->prepareHeaders($headers);

        $this->setCurlOptions('GET', $url, false, $headers);

        return $this->handleStandardResponse();
    }

    private function initializeCurl(): void {
        $this->curl = curl_init();
        if ($this->curl === false) {
            throw new \RuntimeException('Failed to initialize cURL');
        }
    }

    /**
     * Prepare headers for cURL request
     *
     * @param $headers
     * @return array
     */
    private function prepareHeaders(array $headers): array {
        $headers = array_map('trim', $headers);
        if (!$this->hasContentTypeHeader($headers)) {
            $headers[] = 'Content-Type: application/json';
        }
        return $headers;
    }

    /**
     * Prepare data for cURL request
     *
     * @param $data
     * @return string
     */
    private function prepareData(array $data): string | false {
        if(!empty($data)) {
            $jsonData = json_encode(array_filter($data));
            if ($jsonData === false) {
                throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
            }
            return $jsonData;
        }
        return false;
    }

    /**
     * Set cURL options for request
     *
     * @param string $method
     * @param string $url
     * @param bool $isStreaming
     * @param array $headers
     * @param string|false $jsonData
     */
    private function setCurlOptions(string $method, string $url, bool $isStreaming, array $headers, string|false $jsonData = false): void {
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => !$isStreaming,
            CURLOPT_HTTPHEADER     => $this->formatHeaders($headers),
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($jsonData !== false) {
                $options[CURLOPT_POSTFIELDS] = $jsonData;
            }
        } elseif ($method === 'GET') {
            $options[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($this->curl, $options);

        if ($isStreaming) {
            curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, [$this, 'handleStreamingData']);
        }
    }

    /**
     * Handle streaming data from cURL request
     *
     * @param $curl
     * @param string $data
     * @return int
     */
    private function handleStreamingData($curl, string $data): int {
        $cleanData = str_replace('data: ', '', $data);
        if (trim($cleanData)) {
            $jsonData = json_decode($cleanData, true);
            if ($jsonData && isset($jsonData['choices'][0]['delta']['content'])) {
                $content = $jsonData['choices'][0]['delta']['content'];
                $this->content[] = $content;
                print $content;
            }
        }
        return strlen($data);
    }

    /**
     * Handle streaming response from cURL request
     *
     * @return self
     */
    private function handleStreamingResponse(): self {
        $this->executeRequest();
        $this->response = [
            'choices' => [
                ['message' => ['content' => implode('', $this->content)]]
            ]
        ];
        return $this;
    }

    /**
     * Handle standard response from cURL request
     *
     * @return self
     */
    private function handleStandardResponse(): self {
        $response = $this->executeRequest();
        $this->response = json_decode($response, true);
        return $this;
    }

    /**
     * Execute cURL request and handle response
     *
     * @return string
     */
    private function executeRequest(): string {
        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            throw new ResponseException("cURL error: $error");
        }

        curl_close($this->curl);

        if ($httpCode !== 200) {
            // response is string because successful curl_exec with RETURN_TRANSFER is string
            throw new ResponseException(
                "HTTP error: $httpCode" . (is_string($response) ? ", Response: $response" : '')
            );
        }

        // $response is definitely string here
        /** @var string $response */
        return $response;
    }

    /**
     * Check if the response has a Content-Type header
     *
     * @param array $headers The response headers
     * @return bool True if the response has a Content-Type header, false otherwise
     */
    private function hasContentTypeHeader(array $headers): bool
    {
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the response data
     *
     * @return array The response data
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}