<?php

namespace Codechap\Aiwrapper\Traits;

trait AIServiceTrait
{
    protected function formatMessages(string|array $prompt, ?string $systemMessage = null): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemMessage ?? 'You are a test assistant.'
            ]
        ];

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
            if (is_string($message)) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $message
                ];
            } elseif (is_array($message) && isset($message['role'], $message['content'])) {
                $messages[] = $message;
            }
        }

        return $messages;
    }
} 