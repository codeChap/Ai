# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A PHP library providing a unified interface for multiple AI services (OpenAI, Anthropic, Mistral, Groq, xAI, Google). The library standardizes interactions across providers while maintaining service-specific features.

## Development Commands

### Testing
```bash
# Run all tests
composer test

# Run tests with coverage report
composer test:coverage

# Run tests using shell script
./run-tests.sh
```

### Manual Testing
- `run.php` - Contains examples for testing each service
- `judge.php` - Additional testing script

## Architecture

### Core Components

**Main Entry Point (`src/Ai.php`)**
- Factory class that instantiates the appropriate service based on service type
- Automatic service discovery: scans `src/Services/` directory for `*Service.php` files
- Service registry pattern for extensibility
- **Important**: Service types are case-insensitive and automatically converted to lowercase

**Service Base (`src/Abstracts/AbstractAiService.php`)**
- All service implementations extend this abstract class
- Defines common API configuration properties: `apiKey`, `baseUrl`, `systemPrompt`
- Model configuration: `model`, `temperature`, `maxTokens`, `stop`, `stream`
- Two response methods: `one()` returns first response, `all()` returns array of all responses
- Template methods: `query()`, `extractFirstResponse()`, `extractAllResponses()`

**HTTP Layer (`src/Curl.php`)**
- Handles all HTTP requests to AI service APIs
- Supports both standard and streaming responses
- Streaming: uses `CURLOPT_WRITEFUNCTION` callback to handle chunks
- Response handling: automatically decodes JSON and throws `ResponseException` on errors

**Traits**
- `AiServiceTrait`: Message formatting logic for converting prompts to API message arrays
- `HeadersTrait`: HTTP header management and formatting
- `PropertyAccessTrait`: Generic getter/setter methods for service properties

### Service Implementations

Each service in `src/Services/` follows this pattern:
1. Extends `AbstractAiService`
2. Uses `AiServiceTrait`, `HeadersTrait`, `PropertyAccessTrait`
3. Defines service-specific properties (model parameters, API options)
4. Implements `query()` method: validates input, formats messages, builds request payload
5. Overrides `extractFirstResponse()`/`extractAllResponses()` if custom logic needed

**Service-Specific Features:**
- **XaiService**: Supports tool calls, citations, live search parameters, search_parameters
- **OpenAiService**: Native JSON mode via `response_format`, reasoning_effort parameter
- **GroqService**: Vision support
- **AnthropicService/MistralService/GoogleService**: Standard chat completions

### JSON Handling (`src/Helpers/JsonExtractor.php`)

Non-OpenAI services use post-processing for JSON responses:
- Extracts outermost JSON structure from text (handles markdown code blocks)
- Uses stack-based bracket matching algorithm
- Handles escaped characters and string boundaries correctly
- Services set `$this->json = true` to enable automatic extraction

### Service Discovery

When instantiating `new Ai('servicetype', $apiKey)`:
1. Checks service registry cache
2. If empty, scans `src/Services/` for files matching `*Service.php`
3. Registers each service with lowercase name (e.g., `XaiService` → `xai`)
4. Validates service exists, instantiates service class
5. Returns `Ai` instance with service injected

## Adding New AI Services

To add a new service:

1. Create `src/Services/YourServiceNameService.php`
2. Extend `AbstractAiService` and use the three traits
3. Define service-specific properties with defaults
4. Implement `query(string|array $prompts): self` method:
   - Call `$this->validatePrompts($prompts)`
   - Use `$this->formatMessages($prompts, $this->systemPrompt)`
   - Build `$data` array with API parameters using `array_filter()`
   - Set headers with `$this->getHeaders()`
   - Create Curl instance and call `post()`
5. Override `extractFirstResponse()` / `extractAllResponses()` if needed
6. Service is auto-discovered on next instantiation

## Key Implementation Details

**Message Format Handling:**
- Single string prompt → converted to `[['role' => 'user', 'content' => $prompt]]`
- Array of messages → validated for required `role` and `content` keys
- System prompt added as first message if provided

**Property Access Pattern:**
```php
$ai->set('temperature', 0)
   ->set('model', 'grok-4')
   ->set('json', true)
   ->query("Your prompt")
   ->one();
```

**Response Extraction:**
- Default path: `$response['choices'][0]['message']['content']`
- XaiService handles tool_calls and citations differently
- JSON mode (non-OpenAI): extracts and validates JSON from text response

**Error Handling:**
- `InvalidArgumentException`: Empty/invalid service type or API key
- `ResponseException`: HTTP errors or cURL failures
- `RuntimeException`: JSON extraction/encoding failures

## Testing Structure

Tests in `tests/` directory:
- One test file per service (e.g., `OpenAiServiceTest.php`)
- Tests service instantiation and basic functionality
- Uses PHPUnit with colors enabled and warning details
- Coverage reports generated to `coverage/` directory

## Important Notes

- PHP 8.2+ required (uses typed properties, array_filter with callbacks)
- All service types must be lowercase (automatic conversion with warning)
- Service registry is static and persists across instances
- Streaming responses print content directly and rebuild full response
- Vision support requires specific message structure with `image_url` type
- Tool calls (xAI) return structured arrays instead of text content
