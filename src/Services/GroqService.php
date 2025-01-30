<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\ServiceInterface;
use Codechap\Aiwrapper\Abstract\AbstractAIService;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Traits\PropertyAccessTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class GroqService extends AbstractAIService 
{
    use AIServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model       = 'deepseek-r1-distill-llama-70b';
    protected ?int $maxTokens     = null;
    protected ?array $stop        = null;
    protected ?bool $stream       = false;
    protected ?float $temperature = null;
    protected ?float $topP        = null;
    protected ?string $user       = null;

    protected $curl;

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
}