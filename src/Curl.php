<?php

namespace Codechap\Aiwrapper;

use Codechap\Aiwrapper\Traits\HeadersTrait;

class Curl {

    use HeadersTrait;

    private array $response;
    private array $content;

    public function post(array $data, array $headers, $url): self {
        $curl = curl_init();
        $this->content = []; // Initialize content array

        $isStreaming = isset($data['stream']) && $data['stream'] === true;

        // Clean the headers - trim whitespace and remove any newlines
        $headers = array_map(function($header) {
            return trim($header);
        }, $headers);

        // Make sure we only have one Content-Type header
        if (!$this->hasContentTypeHeader($headers)) {
            $headers[] = 'Content-Type: application/json';
        }

        $jsonData = json_encode(array_filter($data));
        if ($jsonData === false) {
            throw new \Exception('Failed to encode JSON: ' . json_last_error());
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => !$isStreaming,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => (new self)->formatHeaders($headers),
            CURLOPT_POSTFIELDS => $jsonData
        ]);

        if ($isStreaming) {
            curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($curl, $data) {
                // Strip away "data: " prefix and decode JSON
                $cleanData = str_replace('data: ', '', $data);
                if (trim($cleanData)) {  // Only process non-empty data
                    try {
                        $jsonData = json_decode($cleanData, true);
                        if ($jsonData && isset($jsonData['choices'][0]['delta']['content'])) {
                            $this->content[] = $jsonData['choices'][0]['delta']['content'];
                            print $jsonData['choices'][0]['delta']['content'];
                        }
                    } catch (\Exception $e) {
                        // Skip malformed JSON data
                    }
                }
                return strlen($data);
            });

            curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode !== 200) {
                throw new \RuntimeException("HTTP error: $httpCode");
            }

            $this->response = ['choices' => [['message' => ['content' => implode('', $this->content)]]]];
            return $this;
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \RuntimeException("HTTP error: $httpCode, Response: $response");
        }

        $this->response = json_decode($response, true);

        return $this;
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
