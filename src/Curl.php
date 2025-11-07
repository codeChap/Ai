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
     * @param array $data The data to send in the request body
     * @param array $headers The HTTP headers to include
     * @param string $url The URL to send the request to
     * @return self Returns the current instance for method chaining
     * @throws \RuntimeException If cURL initialization or JSON encoding fails
     * @throws \codechap\ai\Exceptions\ResponseException If the HTTP request fails
     */
    public function post(array $data = [], array $headers, string $url): self {
        $this->initializeCurl();
        $this->content = [];

        $isStreaming = $data['stream'] ?? false;
        $headers = $this->prepareHeaders($headers);

        $this->setCurlOptions($url, $isStreaming, $headers, $this->prepareData($data));

        return $isStreaming ?
            $this->handleStreamingResponse() :
            $this->handleStandardResponse();
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
     * @param $url
     * @param $isStreaming
     * @param $headers
     * @param $jsonData
     */
    private function setCurlOptions(string $url, bool $isStreaming, array $headers, string $jsonData): void {
        curl_setopt_array($this->curl, array_filter([
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => !$isStreaming,
            CURLOPT_POST           => $jsonData ? true : false,
            CURLOPT_HTTPHEADER     => $this->formatHeaders($headers),
            CURLOPT_POSTFIELDS     => $jsonData ? $jsonData : null
        ]));

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
            try {
                $jsonData = json_decode($cleanData, true);
                if ($jsonData && isset($jsonData['choices'][0]['delta']['content'])) {
                    $content = $jsonData['choices'][0]['delta']['content'];
                    $this->content[] = $content;
                    print $content;
                }
            } catch (\Exception $e) {
                // Skip malformed JSON data
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
     * @return self
     */
    private function executeRequest(): ?string {
        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            throw new ResponseException("cURL error: $error");
        }

        curl_close($this->curl);

        if ($httpCode !== 200) {
            throw new ResponseException(
                "HTTP error: $httpCode" . ($response ? ", Response: $response" : '')
            );
        }

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
