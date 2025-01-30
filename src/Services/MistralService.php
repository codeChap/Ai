<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Curl;
use Codechap\Aiwrapper\Traits\HeadersTrait;

class MistralService implements AIServiceInterface 
{
    use AIServiceTrait;
    use HeadersTrait;

    private string $apiKey;
    private string $baseUrl;

    private string $systemPrompt = 'You are a helpful assistant.';

    protected string $model       = 'mistral-large-latest';
    protected ?bool $stream       = false;
    protected ?float $temperature = null;
    protected ?float $topP        = null;
    protected ?int $maxTokens     = null;
    protected ?bool $safeMode     = null;
    protected ?array $tools       = null;
    protected ?array $stop        = null;
    protected ?string $randomSeed = null;

    private $curl;

    public function __construct(string $apiKey, string $url = 'https://api.mistral.ai/v1/')
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