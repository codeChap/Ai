<?php

namespace Codechap\Aiwrapper;

use Codechap\Aiwrapper\Traits\HeadersTrait;

class Curl {
    use HeadersTrait;

    public static function post(array $data, array $headers, $url): array {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => (new self)->formatHeaders($headers),
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \RuntimeException("HTTP error: $httpCode, Response: $response");
        }

        return json_decode($response, true);
    }
}
