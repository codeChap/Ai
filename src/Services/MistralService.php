<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AI\AIServiceInterface;
use Codechap\Aiwrapper\Abstract\AbstractAIService;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Traits\PropertyAccessTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class MistralService extends AbstractAIService 
{
    use AIServiceTrait;
    use HeadersTrait;
    use PropertyAccessTrait;

    protected string $apiKey;
    protected string $baseUrl;

    protected string $systemPrompt = 'You are a helpful assistant.';

    protected string $model       = 'mistral-large-latest';
    protected ?bool $stream       = false;
    protected ?float $temperature = null;
    protected ?float $topP        = null;
    protected ?int $maxTokens     = null;
    protected ?bool $safeMode     = null;
    protected ?array $tools       = null;
    protected ?array $stop        = null;
    protected ?string $randomSeed = null;

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