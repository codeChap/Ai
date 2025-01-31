# AI Wrapper

## Overview
A flexible PHP wrapper for integrating multiple AI services.

| Service   | Chat | Streaming | Functions/Tools | Computer Vision | Prompt Caching | PDF support  |
|-----------|------|-----------|-----------------|-----------------|----------------|--------------|
| Anthropic | ✓    | ✕         | ✕               | ✕               | ✕              | ✕            |
| Groq      | ✓    | ✕         | ✕               | ✕               | ✕              | ✕            |
| Mistral   | ✓    | ✕         | ✕               | ✕               | ✕              | ✕            |
| OpenAI    | ✓    | ✕         | ✕               | ✕               | ✕              | ✕            |
| xAI       | ✓    | ✕         | ✕               | ✕               | ✕              | ✕            |


## Requirements
- PHP 8.3+
- Composer

## Installation
```bash
composer require codechap/aiwrapper
```

## Basic Usage

```php
// Mistral
$mistral = new AI('mistral', $mistralKey);
print $mistral
    ->set('temperature', 0)
    ->set('model', 'mistral-large-latest')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

```php
// Groq
$groq = new AI('groq', $groqKey);
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
$openai = new AI('openai', $openaiKey);
print $openai
    ->set('temperature', 0)
    ->set('model', 'gpt-4o-mini')
    ->set('systemPrompt', 'You are a helpful assistant from planet earth.')
    ->set('stream', false)
    ->query("What is the capital of South Africa?")
    ->one()
    ;
```

```php
// Anthropic
$anthropic = new AI('anthropic', $anthropicKey);
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
$xai = new AI('xai', $xaiKey);
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

### Project Structure

```
src/
├── Abstract/
│   └── AbstractAIService.php (Base abstract class for AI services)
├── Interfaces/
│   └── ServiceInterface.php (Core interface for AI services)
├── Services/
│   ├── AnthropicService.php (Anthropic implementation)
│   ├── GroqService.php (Groq implementation)
│   ├── MistralService.php (Mistral implementation)
│   ├── OpenAiService.php (OpenAI implementation)
│   ├── XaiService.php (xAI implementation)
│   └── ExampleService.php (Template for new services)
├── Traits/
│   ├── AIServiceTrait.php (Common methods for AI services)
│   └── HeadersTrait.php (Common methods for headers)
├── AIWrapper.php (Main wrapper class)
└── Curl.php (HTTP client implementation)
```

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