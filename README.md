⚠️ **NOT PRODUCTION READY** ⚠️

# AI Wrapper

## Overview
A flexible PHP wrapper for integrating multiple AI services.

| Service   | Chat | Streaming | Functions |
|-----------|------|-----------|-----------|
| Anthropic | ✓    | ✕         | ✕         |
| Groq      | ✓    | ✕         | ✕         |
| Mistral   | ✓    | ✕         | ✕         |
| OpenAI    | ✓    | ✕         | ✕         |
| xAI       | ✓    | ✕         | ✕         |

## Requirements
- PHP 8.3+
- Composer

### Basic Usage

```php
// Mistral Test
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
// Groq Test
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
// OpenAI Test
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
// Anthropic Test
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
// xAI Test
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

## Project Structure

```
src
├── Interfaces
│   ├── AIServiceInterface.php (Ensures consistent functionality across  AI services)
├── Traits
│   ├── AIServiceTrait.php (Common methods for AI services)
│   ├── HeadersTrait.php (Common methods for headers)
├── Services
│   ├── OpenAiService.php (OpenAI service implementation)
│   ├── AnthropicService.php (Anthropic service implementation)
│   ├── GroqService.php (Groq service implementation)
│   ├── MistralService.php (Mistral service implementation)
│   └── XaiService.php (XAI service implementation)
```

## Service Contribution
To add a new AI service:
1. Create a new file in `src/Services/` following the naming convention
2. Implement the required methods:
   - `__construct(string $apiKey)`
   - `query(string $prompt): string`

## Testing
- PHPUnit test suite
- Automatic service discovery testing
- Error handling verification

## Future Enhancements
1. Response standardization
2. Rate limiting
3. Caching layer
4. Async support
5. Streaming responses
6. Error handling improvements
7. Logging system


### Contributing by adding a New Service

To add a new AI service:

1. Create a new file in `src/Services/` following the naming convention and copy the contents of the ExampleService.php file.

```php
<?php

namespace Codechap\Aiwrapper\Services;

use Codechap\Aiwrapper\Interfaces\AIServiceInterface;
use Codechap\Aiwrapper\Traits\AIServiceTrait;
use Codechap\Aiwrapper\Curl;

class NewService implements AIServiceInterface 
{
    use AIServiceTrait;

    public function __construct(string $apiKey)
    {
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException("API key cannot be empty");
        }
        $this->apiKey = $apiKey;
    }

    public function query(string|array $prompt): Curl
    {
        // Implement API call logic here
        return new Curl()->post($data, $headers, $url);
    }

    public function content(): string
    {
        // Return the response content
    }

    public function get(string $name)
    {
        // Get property value
    }

    public function set(string $name, $value): self
    {
        // Set property value
        return $this;
    }
}
```

2. The service will be automatically discovered and available through AIWrapper
3. Run `composer test` to verify your implementation

### Available Methods

- `query(string|array $prompt)`: Send a prompt to the AI service
- `content()`: Get the response content from the last query
- `set(string $name, mixed $value)`: Configure service parameters
- `get(string $name)`: Retrieve service parameter values

### Error Handling

The wrapper throws InvalidArgumentException for:
- Empty API keys
- Invalid service names
- Empty prompts
- Invalid configuration parameters
    