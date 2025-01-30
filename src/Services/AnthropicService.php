<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class AnthropicService implements AIServiceInterface 
{
    use AIServiceTrait;
    use HeadersTrait;

    private string $apiKey;
    private string $baseUrl;

    private string $systemPrompt = 'You are Claude, a helpful AI assistant.';

    protected string $model            = 'claude-3-5-sonnet-20241022';
    protected ?float $temperature      = null;
    protected ?int $maxTokens          = 1024;
    protected ?array $stop             = null;
    protected ?bool $stream            = false;
    protected ?array $metadata         = null;
    protected ?float $topP             = null;
    protected ?float $topK             = null;
    protected ?string $user            = null;

    private $curl;

    public function __construct(string $apiKey, string $url = 'https://api.anthropic.com/v1/')
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

    public function query(string|array $prompts): self
    {
        if (is_string($prompts) && empty(trim($prompts))) {
            throw new \InvalidArgumentException("Prompt cannot be empty");
        }
        if (is_array($prompts) && empty(array_filter($prompts))) {
            throw new \InvalidArgumentException("Prompts array cannot be empty");
        }

        // Convert prompts to messages format without system message
        $messages = is_array($prompts) 
            ? array_map(fn($prompt) => ['role' => 'user', 'content' => $prompt], $prompts)
            : [['role' => 'user', 'content' => $prompts]];

        $data = array_filter([
            'messages'    => $messages,
            'system'      => $this->systemPrompt,  // System prompt as top-level parameter
            'model'       => $this->model,
            'max_tokens'  => $this->maxTokens,
            'metadata'    => $this->metadata,
            'stream'      => $this->stream,
            'temperature' => $this->temperature,
            'top_p'       => $this->topP,
            'top_k'       => $this->topK,
            'stop'        => $this->stop,
            'user'        => $this->user
        ], function($value) {
            return !is_null($value);
        });

        $headers = $this->getHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ]);

        $url = $this->baseUrl . 'messages';

        $this->curl = new Curl();
        $this->curl->post($data, $headers, $url);
        return $this;
    }

    public function one() : string
    {
        $response = $this->curl->getResponse();
        return $response['content'][0]['text'];
    }

    public function all() : array
    {
        return $this->curl->getResponse()['content'];
    }
}
