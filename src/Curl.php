<?php

namespace Codechap\Aiwrapper;

use Codechap\Aiwrapper\Traits\HeadersTrait;

class Curl {

    use HeadersTrait;

    private array $response;
    private array $content;

    public function post(array $data, array $headers, $url): self {
        $curl = curl_init();
        
        $isStreaming = isset($data['stream']) && $data['stream'] === true;

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => !$isStreaming,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => (new self)->formatHeaders($headers),
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        if ($isStreaming) {
            curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($curl, $data) {
                $this->content[] = $data;
                // Strip away "data: " prefix and decode JSON
                $cleanData = str_replace('data: ', '', $data);
                if (trim($cleanData)) {  // Only process non-empty data
                    $jsonData = json_decode($cleanData, true);
                    $this->content[] = $jsonData['choices'][0]['delta']['content'];
                }
                return strlen($data);
            });
            
            curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode !== 200) {
                throw new \RuntimeException("HTTP error: $httpCode");
            }

            return null;
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \RuntimeException("HTTP error: $httpCode, Response: $response");
        }

        $this->response = json_decode($response, true);

        return $this;
    }

    public function response() : array
    {
        return $this->response;
    }

    public function one() : string
    {
        if(isset($this->response['choices'][0]['message']['content'])) {
            return $this->response['choices'][0]['message']['content'];
        }
        return '';
    }

    public function all() : array
    {
        if(isset($this->response['choices'][0]['message']['content'])) {
            foreach($this->response['choices'] as $choice) {
                $content[] = $choice['message']['content'];
            }
            return $content;
        }
        return [];
    }
}