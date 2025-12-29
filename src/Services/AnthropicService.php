<?php

namespace codechap\ai\Services;

use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Curl;

class AnthropicService extends AbstractAiService
{
    private const DEFAULT_API_URL = 'https://api.anthropic.com/v1/';
    private const API_VERSION     = '2023-06-01';
    private const CHAT_ENDPOINT   = 'messages';

    use AiServiceTrait;
    use PropertyAccessTrait;
    use HeadersTrait;

    /**
     * API Configuration
     */
    protected string $systemPrompt = 'You are Claude, a helpful AI assistant.';

    /**
     * Model Configuration
     */
    protected string $model = 'claude-sonnet-4-5-20250929';
    protected ?float $temperature = null;
    protected ?int $maxTokens = 1024;
    protected ?array $stop = null;
    protected ?bool $stream = false;

    /**
     * Additional Parameters
     */
    protected ?array $metadata = null;
    protected ?float $topP = null;
    protected ?float $topK = null;
    protected ?string $user = null;
    protected ?bool $json = false;
    /**
     * Constructor
     *
     * @param string $apiKey API key for authentication
     * @param string $url Base URL for the API
     * @throws \InvalidArgumentException if API key is empty
     */
    public function __construct(string $apiKey, string $url = self::DEFAULT_API_URL)
    {
        parent::__construct($apiKey, $url);
    }

    /**
     * Send a query to the Anthropic API
     *
     * @param string|array $prompts Single prompt or array of prompts
     * @return self
     * @throws \InvalidArgumentException if prompts are empty
     */
    public function query(string|array $prompts): self
    {
        $this->validatePrompts($prompts);

        $messages = $this->formatMessages($prompts);
        $data = $this->prepareRequestData($messages);
        $headers = $this->prepareHeaders();

        $this->sendRequest($data, $headers);

        return $this;
    }

    /**
     * Get a list of models.
     *
     * @todo Implement this method.
     * @param string|null $column The column to sort by.
     * @return array
     */
    public function models(?string $column = null) : array
    {
        return [];
    }

    /**
     * Prepare the request data
     *
     * @param array $messages
     * @return array
     */
    private function prepareRequestData(array $messages): array
    {
        return array_filter([
            'messages'    => $messages,
            'system'      => $this->systemPrompt,
            'model'       => $this->model,
            'max_tokens'  => $this->maxTokens,
            'metadata'    => $this->metadata,
            'stream'      => $this->stream,
            'temperature' => $this->temperature,
            'top_p'       => $this->topP,
            'top_k'       => $this->topK,
            'stop'        => $this->stop,
            'user'        => $this->user,
        ], fn($value) => !is_null($value));
    }

    /**
     * Prepare headers for the API request
     *
     * @return array
     */
    private function prepareHeaders(): array
    {
        return $this->getHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
        ]);
    }

    /**
     * Send the request to the API
     *
     * @param array $data
     * @param array $headers
     */
    private function sendRequest(array $data, array $headers): void
    {
        $url = $this->baseUrl . self::CHAT_ENDPOINT;
        if ($this->curl === null) {
            $this->curl = new Curl();
        }
        $this->curl->post($headers, $url, $data);
    }

    /**
     * Get the first response from the API
     *
     * @return string
     */
    public function one(): string
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

    protected function extractFirstResponse(array $response): string
    {
        $text = $response['content'][0]['text'] ?? '';
        if ($this->json) {
            $extracted = \codechap\ai\Helpers\JsonExtractor::extract($text);
            if ($extracted === null) {
                throw new \RuntimeException('Response does not contain valid JSON.');
            }
            try {
                return json_encode($extracted, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \RuntimeException('Failed to encode JSON: ' . $e->getMessage());
            }
        }
        return $text;
    }

    protected function extractAllResponses(array $response): array
    {
        if ($this->json) {
            $results = [];
            foreach ($response['content'] ?? [] as $content) {
                $text = $content['text'] ?? '';
                $extracted = \codechap\ai\Helpers\JsonExtractor::extract($text);
                if ($extracted === null) {
                    throw new \RuntimeException('One of the responses does not contain valid JSON.');
                }
                try {
                    $results[] = json_encode($extracted, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \RuntimeException('Failed to encode JSON: ' . $e->getMessage());
                }
            }
            return $results;
        }
        return $response['content'] ?? [];
    }
}
