<?php

namespace codechap\ai;

use codechap\ai\Traits\HeadersTrait;

class Curl {

    use HeadersTrait;

    private array $response;
    private array $content;
    private $curl;

    public function post(array $data, array $headers, string $url): self {
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

    private function prepareHeaders(array $headers): array {
        $headers = array_map('trim', $headers);
        if (!$this->hasContentTypeHeader($headers)) {
            $headers[] = 'Content-Type: application/json';
        }
        return $headers;
    }

    private function prepareData(array $data): string {
        $jsonData = json_encode(array_filter($data));
        if ($jsonData === false) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }
        return $jsonData;
    }

    private function setCurlOptions(string $url, bool $isStreaming, array $headers, string $jsonData): void {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => !$isStreaming,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_POSTFIELDS => $jsonData
        ]);

        if ($isStreaming) {
            curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, [$this, 'handleStreamingData']);
        }
    }

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

    private function handleStreamingResponse(): self {
        $this->executeRequest();
        $this->response = [
            'choices' => [
                ['message' => ['content' => implode('', $this->content)]]
            ]
        ];
        return $this;
    }

    private function handleStandardResponse(): self {
        $response = $this->executeRequest();
        $this->response = json_decode($response, true);
        return $this;
    }

    private function executeRequest(): ?string {
        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            throw new \RuntimeException("cURL error: $error");
        }

        curl_close($this->curl);

        if ($httpCode !== 200) {
            throw new \RuntimeException(
                "HTTP error: $httpCode" . ($response ? ", Response: $response" : '')
            );
        }

        return $response;
    }

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
