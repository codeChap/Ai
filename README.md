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

// Using OpenAI
$openai = new AIWrapper('openai', 'openai-api-key');
$openaiResponse = $openai->query('Hello OpenAI');

// Using Anthropic
$anthropic = new AIWrapper('anthropic', 'anthropic-api-key');
$anthropicResponse = $anthropic->query('Hello Claude');

// Using xAI
$xai = new AIWrapper('xai', 'xai-api-key');
$xaiResponse = $xai->query('Hello Grok');
```

### Adding a New Service

To add a new AI service:
1. Create a new file in `src/Services/` following the naming convention `{Name}Service.php`
2. Implement the required methods:
   - `__construct(string $apiKey)`
   - `query(string $prompt): string`
3. Run `composer test` to ensure your new service is discovered and tested.
    