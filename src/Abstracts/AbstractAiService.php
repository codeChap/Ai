<?php

namespace codechap\ai\Abstracts;

use codechap\ai\Interfaces\ServiceInterface;
use codechap\ai\Interfaces\CurlInterface;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Curl;

abstract class AbstractAiService implements ServiceInterface
{
    use AiServiceTrait;
    use HeadersTrait;

    /**
     * HTTP Client
     */
    protected ?CurlInterface $curl = null;

    /**
     * API Configuration
     */
    protected string $apiKey;
    protected string $baseUrl;
    protected string $systemPrompt = 'You are a helpful assistant.';

    /**
     * Model Configuration
     */
    protected string $model;
    protected ?float $temperature = null;
    protected ?int $maxTokens = null;
    protected ?array $stop = null;
    protected ?bool $stream = false;

    /**
     * Constructor
     *
     * @param string $apiKey API key for authentication
     * @param string $url Base URL for the API
     * @throws \InvalidArgumentException if API key is empty
     */
    public function __construct(string $apiKey, string $url)
    {
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = $url;
    }

    /**
     * Set the HTTP Client instance (useful for mocking)
     *
     * @param CurlInterface $curl
     * @return self
     */
    public function setCurl(CurlInterface $curl): self
    {
        $this->curl = $curl;
        return $this;
    }

    /**
     * Validate the input prompts
     *
     * @param string|array $prompts
     * @throws \InvalidArgumentException
     */
    protected function validatePrompts(string|array $prompts): void
    {
        if (is_string($prompts) && empty(trim($prompts))) {
            throw new \InvalidArgumentException("Prompt cannot be empty");
        }
        if (is_array($prompts) && empty(array_filter($prompts))) {
            throw new \InvalidArgumentException("Prompts array cannot be empty");
        }
    }

    /**
     * Get a single response from the API
     *
     * @return array|string The first response from the API
     * @throws \RuntimeException If JSON parsing fails (when JSON mode is enabled)
     */
    public function one(): array | string
    {
        if ($this->curl === null) {
            throw new \RuntimeException("Curl client has not been initialized. Call query() first.");
        }
        $response = $this->curl->getResponse();
        return $this->extractFirstResponse($response);
    }

    /**
     * Get all responses from the API
     *
     * @return array Array of responses from the API
     * @throws \RuntimeException If JSON parsing fails (when JSON mode is enabled)
     */
    public function all(): array
    {
        if ($this->curl === null) {
            throw new \RuntimeException("Curl client has not been initialized. Call query() first.");
        }
        $response = $this->curl->getResponse();
        return $this->extractAllResponses($response);
    }

    /**
     * Extract first response from API response
     * Override this method in specific service implementations if needed
     *
     * @param array $response The API response array
     * @return string|array The extracted response content
     * @throws \RuntimeException If JSON extraction/encoding fails
     */
    protected function extractFirstResponse(array $response): string | array
    {
        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Extract all responses from API response
     * Override this method in specific service implementations if needed
     *
     * @param array $response The API response array
     * @return array Array of extracted response contents
     * @throws \RuntimeException If JSON extraction/encoding fails
     */
    protected function extractAllResponses(array $response): array
    {
        $content = [];
        if (isset($response['choices'])) {
            foreach ($response['choices'] as $choice) {
                if (isset($choice['message']['content'])) {
                    $content[] = $choice['message']['content'];
                }
            }
        }
        return $content;
    }



    /**
     * Send a query to the AI service
     * This method should be implemented by each specific service
     *
     * @param string|array $prompts The prompt(s) to send
     * @return self Returns the current instance for method chaining
     * @throws \InvalidArgumentException If prompts are empty or invalid
     * @throws \codechap\ai\Exceptions\ResponseException If the API request fails
     */
    abstract public function query(string|array $prompts): self;
}