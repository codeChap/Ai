<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class XaiService implements AIServiceInterface 
{
    use AIServiceTrait;
    use HeadersTrait;

    private string $apiKey;
    private string $baseUrl;

    private string $systemPrompt = 'You are a helpful assistant.';

    protected string $model = 'grok-2-latest';
    protected ?bool $deferred = null;
    protected ?float $frequencyPenalty = null;
    protected ?array $logitBias = null;
    protected ?bool $logprobs = null;
    protected ?int $maxTokens = null;
    protected ?int $n = null;
    protected ?float $presencePenalty = null;
    protected ?array $responseFormat = null;
    protected ?int $seed = null;
    protected ?array $stop = null;
    protected ?bool $stream = false;
    protected ?array $streamOptions = null;
    protected ?float $temperature = null;
    protected mixed $toolChoice = null;
    protected ?array $tools = null;
    protected ?int $topLogprobs = null;
    protected ?float $topP = null;
    protected ?string $user = null;
    
    public function __construct(string $apiKey, string $url = 'https://api.x.ai/v1/')
    {
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }
        
        $this->apiKey = $apiKey;
        $this->baseUrl = $url;
    }

    public function get(string $name)
    {
        if(property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property $name does not exist in " . __CLASS__);
    }

    public function set(string $name, $value): self
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        }
        throw new \Exception("Property $name does not exist in " . __CLASS__);
    }
    
    public function query(string|array $prompts): Curl
    {
        if (is_string($prompts) && empty(trim($prompts))) {
            throw new \InvalidArgumentException("Prompt cannot be empty");
        }
        if (is_array($prompts) && empty(array_filter($prompts))) {
            throw new \InvalidArgumentException("Prompts array cannot be empty");
        }

        $data = array_filter([
            'messages'          => $this->formatMessages($prompts, $this->systemPrompt),
            'model'             => $this->model,
            'stream'            => $this->stream,
            'temperature'       => $this->temperature,
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
            'stream_options'    => $this->streamOptions,
            'tool_choice'       => $this->toolChoice,
            'tools'             => $this->tools,
            'top_logprobs'      => $this->topLogprobs,
            'top_p'             => $this->topP,
            'user'              => $this->user
        ], function($value) {
            return !is_null($value);
        });

        $headers = $this->getHeaders($this->apiKey);
        $url = $this->baseUrl . 'chat/completions';

        return new Curl()->post($data, $headers, $url);
    }
}
