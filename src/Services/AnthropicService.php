<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\ServiceInterface;
use Codechap\Aiwrapper\Abstract\AbstractAIService;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Traits\PropertyAccessTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class AnthropicService extends AbstractAIService 
{
    private const DEFAULT_API_URL = 'https://api.anthropic.com/v1/';
    private const API_VERSION = '2023-06-01';
    private const CHAT_ENDPOINT = 'messages';

    /**
     * API Configuration
     */
    protected string $systemPrompt = 'You are Claude, a helpful AI assistant.';

    /**
     * Model Configuration
     */
    protected string $model = 'claude-3-5-sonnet-20241022';
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
            'user'        => $this->user
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
        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
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
        return $response['content'][0]['text'] ?? '';
    }

    protected function extractAllResponses(array $response): array
    {
        return $response['content'] ?? [];
    }
}
