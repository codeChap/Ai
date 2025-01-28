<?php

namespace Codechap\Aiwrapper\Traits;

trait AIServiceTrait
{
    protected function formatMessages(string|array $prompt, string|bool $systemMessage = false): array
    {
        $messages = [];

        if($systemMessage) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemMessage
            ];
        }

        // If prompt is a string, treat it as a single user message
        if (is_string($prompt)) {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt
            ];
            return $messages;
        }

        // If prompt is an array, append all messages
        foreach ($prompt as $message) {

            if(!isset($message['role']) || !isset($message['content'])) {
                throw new \Exception("Invalid message format: 'role' and 'content' are required.");
            }

            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        return $messages;
    }
} 