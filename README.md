⚠️ **CRITICAL WARNING - DO NOT USE IN PRODUCTION** ⚠️


# AIWrapper Project Outline

## Overview
A flexible PHP wrapper for integrating multiple AI services (OpenAI, Anthropic, xAI, etc.).

## Requirements
- PHP 8.3+
- Composer

## Project Structure

## Features
1. **Automatic Service Discovery**
   - Services are automatically discovered from the Services directory
   - No manual registration required
   - Follow naming convention: `{Name}Service.php`

2. **Flexible Integration**
   - Easy to add new AI services
   - Consistent interface across different providers
   - Type-safe implementation

3. **Modern PHP Features**
   - Strict typing
   - Readonly classes
   - Constructor property promotion
   - PHP 8.3 features

## Service Implementation
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

## Usage Examples

### Basic Usage

```php
use Codechap\Aiwrapper\AIWrapper;

// Initialize services with API keys
$openai = new AIWrapper('openai', 'your-openai-api-key');
$anthropic = new AIWrapper('anthropic', 'your-anthropic-api-key');
$xai = new AIWrapper('xai', 'your-xai-api-key');
$groq = new AIWrapper('groq', 'your-groq-api-key');

// Simple query with string prompt
$response = $xai->query('What is the meaning of life?')->content();

// Using array of messages
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'Tell me about PHP 8.3'],
];
$response = $xai->query($messages)->content();

// Configuring model parameters
$xai->set('temperature', 0.7)
    ->set('maxTokens', 1000)
    ->set('model', 'grok-2-latest');

$response = $xai->query('Write a poem about coding')->content();
```

### Adding a New Service

To add a new AI service:

1. Create a new file in `src/Services/` following the naming convention:

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
    