<?php

namespace codechap\ai\Services;

use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Curl;

class GroqService extends AbstractAiService
{
    use AiServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model       = 'meta-llama/llama-4-scout-17b-16e-instruct';
    protected ?int $maxTokens     = null;
    protected ?array $stop        = null;
    protected ?bool $stream       = false;
    protected ?float $temperature = null;
    protected ?float $topP        = null;
    protected ?string $user       = null;
    protected ?bool $json         = false;

    public function __construct(string $apiKey, string $url = 'https://api.groq.com/openai/v1/')
    {
        parent::__construct($apiKey, $url);
    }

    public function query(string|array $prompts): self
    {
        $this->validatePrompts($prompts);

        $messages = $this->formatMessages($prompts, $this->systemPrompt);

        $data = array_filter([
            'messages'          => $messages,
            'model'             => $this->model,
            'max_tokens'        => $this->maxTokens,
            'stop'              => $this->stop,
            'stream'            => $this->stream,
            'temperature'       => $this->temperature,
            'top_p'             => $this->topP,
            'user'              => $this->user
        ], fn($value) => !is_null($value));

        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey)
        ]);

        $url = $this->baseUrl . 'chat/completions';

        $this->curl = new Curl();
        $this->curl->post($headers, $url, $data);

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

    public function one(): string
    {
        $response = $this->curl->getResponse();
        return $this->extractFirstResponse($response);
    }

    public function all(): array
    {
        $response = $this->curl->getResponse();
        return $this->extractAllResponses($response);
    }

    protected function extractFirstResponse(array $response): string
    {
        $text = $response['choices'][0]['message']['content'] ?? '';
        if ($this->json) {
            $extracted = \codechap\ai\Helpers\JsonExtractor::extract($text);
            if ($extracted === null) {
                // If extraction fails, try to find JSON between ```json and ``` markers
                if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
                    $extracted = \codechap\ai\Helpers\JsonExtractor::extract($matches[1]);
                    if ($extracted === null) {
                        throw new \RuntimeException('Response does not contain valid JSON.');
                    }
                } else {
                    throw new \RuntimeException('Response does not contain valid JSON.');
                }
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

            foreach ($response['choices'] ?? [] as $choice) {
                $text = $choice['message']['content'] ?? '';
                $extracted = \codechap\ai\Helpers\JsonExtractor::extract($text);
                if ($extracted === null) {
                    // If extraction fails, try to find JSON between ```json and ``` markers
                    if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
                        $extracted = \codechap\ai\Helpers\JsonExtractor::extract($matches[1]);
                        if ($extracted === null) {
                            throw new \RuntimeException('One of the responses does not contain valid JSON.');
                        }
                    } else {
                        throw new \RuntimeException('One of the responses does not contain valid JSON.');
                    }
                }
                try {
                    $results[] = json_encode($extracted, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \RuntimeException('Failed to encode JSON: ' . $e->getMessage());
                }
            }
            return $results;
        }
        return array_map(fn($choice) => $choice['message']['content'] ?? '', $response['choices'] ?? []);
    }
}
