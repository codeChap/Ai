<?php

namespace codechap\ai\Services;

use codechap\ai\Interfaces\ServiceInterface;
use codechap\ai\Abstracts\AbstractAiService;
use codechap\ai\Traits\AiServiceTrait;
use codechap\ai\Traits\PropertyAccessTrait;
use codechap\ai\Curl;
use codechap\ai\Traits\HeadersTrait;

class OpenAiService extends AbstractAiService 
{
    use AiServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model            = 'o3-mini-2025-01-31';
    protected ?float $frequencyPenalty = null;
    protected ?array $logitBias        = null;
    protected ?int $maxTokens          = null;
    protected ?int $n                  = null;
    protected ?float $presencePenalty  = null;
    protected ?array $responseFormat   = null;
    protected ?int $seed               = null;
    protected ?array $stop             = null;
    protected ?bool $stream            = false;
    protected ?float $temperature      = null;
    protected mixed $toolChoice        = null;
    protected ?array $tools            = null;
    protected ?float $topP             = null;
    protected ?string $user            = null;
    protected ?bool $json               = false;
    protected $curl;

    public function __construct(string $apiKey, string $url = 'https://api.openai.com/v1/')
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
            'frequency_penalty' => $this->frequencyPenalty,
            'logit_bias'        => $this->logitBias,
            'max_tokens'        => $this->maxTokens,
            'n'                 => $this->n,
            'presence_penalty'  => $this->presencePenalty,
            'response_format'   => $this->responseFormat 
                                   ? $this->responseFormat 
                                   : (is_array($this->json) 
                                         ? $this->json 
                                         : ($this->json === true ? ['type' => 'json_object'] : null)
                                     ),
            'seed'              => $this->seed,
            'stop'              => $this->stop,
            'stream'            => $this->stream,
            'temperature'       => $this->temperature,
            'tool_choice'       => $this->toolChoice,
            'tools'             => $this->tools,
            'top_p'             => $this->topP,
            'user'              => $this->user,
        ], function($value) {
            return !is_null($value);
        });

        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey),
            'OpenAI-Beta'   => 'assistants=v1'
        ]);
        
        $url = $this->baseUrl . 'chat/completions';

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
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