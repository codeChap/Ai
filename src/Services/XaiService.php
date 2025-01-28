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
    private string $baseUrl = 'https://api.x.ai/v1/';
    
    public function __construct(string $apiKey) 
    {
        $this->apiKey = $apiKey;
    }
    
    public function query(string|array $prompts, ?string $systemMessage = null): string 
    {
        $data = [
            'messages' => $this->formatMessages($prompts, $systemMessage),
            'model' => 'grok-2-latest',
            'stream' => false,
            'temperature' => 0
        ];

        $headers = $this->getHeaders($this->apiKey);
        $url = $this->baseUrl . 'chat/completions';

        $curl = new Curl();
        $result = $curl->post($data, $headers, $url);
        
        return $result['choices'][0]['message']['content'] ?? '';
    }
}