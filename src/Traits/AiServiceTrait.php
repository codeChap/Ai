<?php

namespace codechap\ai\Traits;

trait AiServiceTrait
{
    /**
     * Format messages for the AI service.
     *
     * @param string|array $prompt The prompt to format
     * @param string|bool $systemMessage The system message to include
     * @return array The formatted messages
     */
    protected function formatMessages(string|array $prompt, string|bool $systemMessage = false): array
    {
        $messages = [];

        if(is_string($systemMessage)) {
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
            $messages[] = $message;
        }

        return $prompt;
    }
}
