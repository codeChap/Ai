<?php

namespace codechap\ai\Services;

use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Traits\HeadersTrait;
use codechap\ai\Curl;

class MistralService extends AbstractAiService
{
    use AiServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model       = 'mistral-small-latest';
    protected ?bool $stream       = false;
    protected ?float $temperature = null;
    protected ?float $topP        = null;
    protected ?int $maxTokens     = null;
    protected ?bool $safeMode     = null;
    protected ?array $tools       = null;
    protected ?array $stop        = null;
    protected ?string $randomSeed = null;
    protected ?bool $json         = false;

    protected $curl;

    public function __construct(string $apiKey, string $url = 'https://api.mistral.ai/v1/')
    {
        parent::__construct($apiKey, $url);
    }

    public function query(string|array $prompts): self
    {
        $this->validatePrompts($prompts);

        $messages = $this->formatMessages($prompts, $this->systemPrompt);

        $data = array_filter([
            'messages'     => $messages,
            'model'        => $this->model,
            'temperature'  => $this->temperature,
            'top_p'        => $this->topP,
            'max_tokens'   => $this->maxTokens,
            'stream'       => $this->stream,
            'safe_mode'    => $this->safeMode,
            'random_seed'  => $this->randomSeed,
            'tools'        => $this->tools,
            'stop'         => $this->stop
        ], function($value) {
            return !is_null($value);
        });

        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey)
        ]);

        $url = $this->baseUrl . 'chat/completions';

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
    }

    /**
     * Get a list of available models.
     * @param string $column The $column name to retrieve
     * @return array
     */
    public function models($column = false) : array
    {
        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey)
        ]);

        $url = $this->baseUrl . 'models';

        $this->curl = new Curl();
        $this->curl->post([], $headers, $url);
        $response = $this->curl->getResponse();

        if(!empty($response['data'])) {
            if($column){
                return array_column($response['data'], $column);
            }
            return $response['data'];
        }

        throw new \Exception('Failed to retrieve models');
    }

    public function one() : string
    {
        $response = $this->curl->getResponse();
        if(isset($response['choices'][0]['message']['content'])) {
            return $this->extractFirstResponse($response);
        }
        return '';
    }

    public function all() : array
    {
        $response = $this->curl->getResponse();
        $content = [];
        if(isset($response['choices'][0]['message']['content'])) {
            $content = $this->extractAllResponses($response);
        }
        return $content;
    }

    protected function extractFirstResponse(array $response): string
    {
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
        if ($this->json) {
            $results = [];
            foreach ($response['choices'] as $choice) {
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
        return $response['choices'] ?? [];
    }
}
