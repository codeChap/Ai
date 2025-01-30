<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\ServiceInterface;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Traits\HeadersTrait;
use Codechap\Aiwrapper\Traits\PropertyAccessTrait;
use Codechap\Aiwrapper\Curl;

class ExampleService extends AbstractAIService 
{
    use AIServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    // API Configuration
    private string $apiKey;
    private string $baseUrl;
    private const DEFAULT_API_URL = 'https://api.example.com/v1/';
    private const API_VERSION = '2024-01';
    private const CHAT_ENDPOINT = 'chat/completions';

    // Default Settings
    private string $systemPrompt = 'You are a helpful assistant.';
    protected string $model = 'example-model-v1';

    // Model Parameters
    // @see https://example.com/docs/api-reference
    protected ?float $temperature = null;    // Controls randomness (0.0 to 1.0)
    protected ?int $maxTokens = null;        // Maximum number of tokens to generate
    protected ?array $stop = null;           // Sequences where the API will stop generating
    protected ?bool $stream = false;         // Whether to stream responses
    protected ?float $topP = null;           // Nucleus sampling parameter (0.0 to 1.0)
    protected ?string $user = null;          // End-user identifier for monitoring

    private $curl;

    public function __construct(
        string $apiKey, 
        string $url = self::DEFAULT_API_URL
    ) {
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = $url;
    }

    public function query(string|array $prompts): self
    {
        $this->validatePrompts($prompts);
        $messages = $this->formatMessages($prompts, $this->systemPrompt);
        $data = $this->prepareRequestData($messages);
        $headers = $this->prepareHeaders();
        
        $url = $this->baseUrl . self::CHAT_ENDPOINT;

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
    }

    /**
     * Validates the input prompts
     * @throws \InvalidArgumentException
     */
    private function validatePrompts(string|array $prompts): void
    {
        if (is_string($prompts) && empty(trim($prompts))) {
            throw new \InvalidArgumentException("Prompt cannot be empty");
        }
        if (is_array($prompts) && empty(array_filter($prompts))) {
            throw new \InvalidArgumentException("Prompts array cannot be empty");
        }
    }

    /**
     * Prepares the request data with all necessary parameters
     */
    private function prepareRequestData(array $messages): array
    {
        return array_filter([
            'messages'    => $messages,
            'model'       => $this->model,
            'max_tokens'  => $this->maxTokens,
            'stream'      => $this->stream,
            'temperature' => $this->temperature,
            'top_p'       => $this->topP,
            'stop'        => $this->stop,
            'user'        => $this->user
        ], fn($value) => !is_null($value));
    }

    /**
     * Prepares the headers for the API request
     */
    private function prepareHeaders(): array
    {
        return $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey),
            'X-API-Version' => self::API_VERSION,
            'Content-Type'  => 'application/json'
        ]);
    }

    public function one() : string
    {
        $response = $this->curl->getResponse();
        if(isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return '';
    }

    public function all() : array
    {
        $response = $this->curl->getResponse();
        $content = [];
        if(isset($response['choices'][0]['message']['content'])) {
            foreach($response['choices'] as $choice) {
                $content[] = $choice['message']['content'];
            }
        }
        return $content;
    }
}
