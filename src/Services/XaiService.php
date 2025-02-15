<?php

namespace codechap\ai\Services;

use codechap\ai\Interfaces\ServiceInterface;
use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Curl;
use codechap\ai\Traits\HeadersTrait;

class XaiService extends AbstractAiService 
{
    use AiServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model            = 'grok-2-latest';
    protected ?bool $deferred          = null;
    protected ?float $frequencyPenalty = null;
    protected ?array $logitBias        = null;
    protected ?bool $logprobs          = null;
    protected ?int $maxTokens          = null;
    protected ?int $n                  = null;
    protected ?float $presencePenalty  = null;
    protected ?array $responseFormat   = null;
    protected ?int $seed               = null;
    protected ?array $stop             = null;
    protected ?bool $stream            = false;
    protected ?array $streamOptions    = null;
    protected ?float $temperature      = null;
    protected mixed $toolChoice        = null;
    protected ?array $tools            = null;
    protected ?int $topLogprobs        = null;
    protected ?float $topP             = null;
    protected ?string $user            = null;
    protected ?bool $json             = false;

    protected $curl;

    public function __construct(string $apiKey, string $url = 'https://api.x.ai/v1/')
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
            'deferred'          => $this->deferred,
            'frequency_penalty' => $this->frequencyPenalty,
            'logit_bias'        => $this->logitBias,
            'logprobs'          => $this->logprobs,
            'max_tokens'        => $this->maxTokens,
            'n'                 => $this->n,
            'presence_penalty'  => $this->presencePenalty,
            'response_format'   => $this->responseFormat,
            'seed'              => $this->seed,
            'stop'              => $this->stop,
            'stream'            => $this->stream,
            'stream_options'    => $this->streamOptions,
            'temperature'       => $this->temperature,
            'tool_choice'       => $this->toolChoice,
            'tools'             => $this->tools,
            'top_logprobs'      => $this->topLogprobs,
            'top_p'             => $this->topP,
            'user'              => $this->user
        ], function($value) {
            return !is_null($value);
        });

        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey),
        ]);
        $url = $this->baseUrl . 'chat/completions';

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
    }

    public function one(): array | string
    {
        $response = $this->curl->getResponse();
        return $this->extractFirstResponse($response);
    }

    public function all(): array
    {
        $response = $this->curl->getResponse();
        return $this->extractAllResponses($response);
    }

    protected function extractFirstResponse(array $response): string|array
    {
        if (isset($response['choices'][0]['message']['tool_calls'])) {
            return $this->handleToolCalls($response['choices'][0]['message']['tool_calls'])[0];
        }

        $text = $response['choices'][0]['message']['content'] ?? '';
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
        if (isset($response['choices'][0]['message']['tool_calls'])) {
            // For now, we'll only handle tool calls from the first choice
            return [$this->handleToolCalls($response['choices'][0]['message']['tool_calls'])];
        }

        if ($this->json) {
            $results = [];
            foreach ($response['choices'] ?? [] as $choice) {
                $text = $choice['message']['content'] ?? '';
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
        return array_map(function($choice) {
            return $choice['message']['content'] ?? '';
        }, $response['choices'] ?? []);
    }

    protected function handleToolCalls(array $toolCalls): array
    {
        $results = [];
        foreach ($toolCalls as $call) {
            $results[] = [
                'id' => $call['id'],
                'type' => $call['type'],
                'function' => [
                    'name' => $call['function']['name'],
                    'arguments' => json_decode($call['function']['arguments'], true)
                ]
            ];
        }
        return $results;
    }
}
