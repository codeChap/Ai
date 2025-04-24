# AI Service Integration Library

## Overview
A PHP library that provides a unified interface for interacting with multiple AI services (OpenAI, Anthropic, Mistral, Groq, xAI). Simplifies integration and standardizes interactions across different AI providers while maintaining service-specific features.

## Implementations

| Service   | Chat | Streaming | Tools | Vision | Caching | PDF | JSON |
|-----------|------|-----------|-------|--------|---------|-----|------|
| Anthropic | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✓    |
| Groq      | ✓    | ✕         | ✕     | ✓      | ✕       | ✕   | ✓    |
| Mistral   | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✓    |
| OpenAI    | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✓    |
| xAI       | ✓    | ✕         | ✓     | ✕      | ✕       | ✕   | ✓    |
| Google    | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✓    |

## Requirements
- PHP 8.2+
- Composer

## Installation
```bash
composer require codechap/ai
```

## Basic Usage

### OpenAI
```php
use codechap\ai\Ai;
$openai = new ai('openai', $openaiKey);
print $openai
    ->set('temperature', 0)
    ->set('model', 'o3-mini-2025-01-31')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('json', true)
    ->set('reasoningEffort', 'low')
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->one()
    ;
```

### Mistral
```php
use codechap\ai\Ai;
$mistral = new Ai('mistral', $mistralKey);
print $mistral
    ->set('temperature', 0)
    ->set('model', 'o3-mini-2025-01-31')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->one()
    ;
```

### Groq (Most open source models)
```php
use codechap\ai\Ai;
$groq = new Ai('groq', $groqKey);
print $groq
    ->set('temperature', 0)
    ->set('model', 'deepseek-r1-distill-llama-70b')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
print "\n\n";
```

### Anthropic (Claude)
```php
use codechap\ai\Ai;
$anthropic = new Ai('anthropic', $anthropicKey);
print $anthropic
    ->set('temperature', 0)
    ->set('model', 'claude-3-5-sonnet-20241022')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

### xAI (Grok)
```php
use codechap\ai\Ai;
$xai = new Ai('xai', $xaiKey);
print $xai
    ->set('temperature', 0)
    ->set('model', 'grok-2-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

### Google (Gemini)
```php
use codechap\ai\Ai;
$google = new Ai('google', $googleKey);
print = $google
    ->set('temperature', 0)
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->all()
    ;
```

## Vision Example
// Groq Vision
$groq = new Ai('groq', $groqKey);
$result = $groq
    ->set('temperature', 0)
    ->set('model', 'meta-llama/llama-4-scout-17b-16e-instruct')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->set('json', false)
    ->query(
    [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'https://upload.wikimedia.org/wikipedia/commons/f/f2/LPU-v1-die.jpg'
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'What is this image about?'
                ]
            ]
        ]
    ])
    ->all()
    ;
print_r($result);

## JSON Response Handling

Different AI services handle JSON responses in different ways:

### OpenAI
- Uses native JSON response formatting via the `response_format` parameter
- Set `json: true` to automatically receive properly formatted JSON responses
- No additional processing needed

### Other Services (Anthropic, Mistral, Groq, xAI)
- JSON responses are handled through post-processing
- Set `json: true` to enable JSON extraction and validation
- Uses the JsonExtractor helper to:
  - Extract JSON from raw responses
  - Handle JSON within markdown code blocks (```json ... ```)
  - Validate JSON structure

Example usage:
```php
// OpenAI (native JSON)
$ai->openai()
   ->set('json', true)
   ->query('Return user data')
   ->one();

// Other services (post-processed JSON)
$ai->anthropic() // or mistral(), groq(), xai()
   ->set('json', true)
   ->query('Return user data')
   ->one();
```

## Contributing

 - Todo

### Testing
- PHPUnit test suite
- Automatic service discovery testing
- Error handling verification

### Contributing by adding a New Service

To add a new AI service:
1. Create a new file in `src/Services/` following the naming convention
2. Implement the required methods:
   - `__construct(string $apiKey)`
   - `query(string $prompt): string`

3. The service will be automatically discovered and available through AIWrapper
4. Run `composer test` to verify your implementation
