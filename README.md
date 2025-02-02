# AI Wrapper

## Overview
A flexible PHP wrapper for integrating multiple AI services.

| Service   | Chat | Streaming | Tools | Vision | Caching | PDF | JSON |
|-----------|------|-----------|-------|--------|---------|-----|------|
| Anthropic | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕    |
| Groq      | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕    |
| Mistral   | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕    |
| OpenAI    | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✓    |
| xAI       | ✓    | ✕         | ✕     | ✕      | ✕       | ✕   | ✕    |


## Requirements
- PHP 8.2+
- Composer

## Installation
```bash
composer require codechap/ai
```

## Basic Usage


```php
use codechap\ai\ai;

// Mistral
$mistral = new ai('mistral', $mistralKey);
print $mistral
    ->set('temperature', 0)
    ->set('model', 'o3-mini-2025-01-31')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('json', true)
    ->query("What is the capital of South Africa? Only return the three in a JSON response.")
    ->one()
    ;
```

```php
// Groq
$groq = new ai('groq', $groqKey);
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

```php
// OpenAI
$openai = new ai('openai', $openaiKey);
print $openai
    ->set('temperature', 0)
    ->set('model', 'o3-mini-2025-01-31')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

```php
// Anthropic
$anthropic = new ai('anthropic', $anthropicKey);
print $anthropic
    ->set('temperature', 0)
    ->set('model', 'claude-3-5-sonnet-20241022')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

```php
// xAI
$xai = new ai('xai', $xaiKey);
print $xai
    ->set('temperature', 0)
    ->set('model', 'grok-2-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
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