<?php

namespace codechap\ai\Abstracts;

use codechap\ai\Interfaces\ServiceInterface;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Traits\PropertyAccessTrait;

abstract class AbstractAiService implements ServiceInterface
{
    use AiServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    /**
     * HTTP Client
     */
    protected $curl;

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
     * Format prompts into messages array
     *
     * @param string|array $prompts
     * @return array
     */
    protected function formatMessages(string|array $prompts): array
    {
        return is_array($prompts)
            ? array_map(fn($prompt) => ['role' => 'user', 'content' => $prompt], $prompts)
            : [['role' => 'user', 'content' => $prompts]];
    }

    /**
     * Get a single response from the API
     *
     * @return string
     */
    public function one(): array | string
    {
        $response = $this->curl->getResponse();
        return $this->extractFirstResponse($response);
    }

    /**
     * Get all responses from the API
     *
     * @return array
     */
    public function all(): array
    {
        $response = $this->curl->getResponse();
        return $this->extractAllResponses($response);
    }

    /**
     * Extract first response from API response
     * Override this method in specific service implementations if needed
     *
     * @param array $response
     * @return string
     */
    protected function extractFirstResponse(array $response): string | array
    {
        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Extract all responses from API response
     * Override this method in specific service implementations if needed
     *
     * @param array $response
     * @return array
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
     * Gets the value of a property
     *
     * @param string $name The name of the property
     * @return mixed The value of the property
     * @throws \Exception If the property doesn't exist
     */
    public function get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property $name does not exist");
    }

    /**
     * Sets the value of a property
     *
     * @param string $name The name of the property
     * @param mixed $value The value to set
     * @return self Returns the current instance for method chaining
     * @throws \Exception If the property doesn't exist
     */
    public function set(string $name, mixed $value): self
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        }
        throw new \Exception("Property $name does not exist");
    }

    /**
     * Send a query to the AI service
     * This method should be implemented by each specific service
     *
     * @param string|array $prompts
     * @return self
     */
    abstract public function query(string|array $prompts): self;
}
