<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AI\AIServiceInterface;
use Codechap\Aiwrapper\Abstract\AbstractAIService;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Traits\PropertyAccessTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class XaiService extends AbstractAIService 
{
    use AIServiceTrait;
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

        $headers = $this->getHeaders([
            'Authorization' => "Bearer " . trim($this->apiKey),
        ]);
        $url = $this->baseUrl . 'chat/completions';

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
    }
}
