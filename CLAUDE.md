# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A PHP library providing a unified interface for multiple AI services (OpenAI, Anthropic, Mistral, Groq, xAI, Google). The library standardizes interactions across providers while maintaining service-specific features.

## Development Commands

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test:coverage

# Run a single test file
./vendor/bin/phpunit tests/OpenAiServiceTest.php

# Run a specific test method
./vendor/bin/phpunit --filter testMethodName
```

### Manual Testing
- `run.php` - Contains examples for testing each service (requires API keys in parent directory)

## Architecture

### Request Flow

```
User Code → Ai (factory) → Service → Curl → AI Provider API
                ↓              ↓        ↓
          Auto-discover   Format     HTTP
          from /Services  Messages   Request
```

### Core Components

**Main Entry Point (`src/Ai.php`)**
- Factory class that instantiates the appropriate service based on service type
- Automatic service discovery: scans `src/Services/` directory for `*Service.php` files
- Service registry pattern with static cache for extensibility
- Service types are case-insensitive (automatically converted to lowercase with E_USER_WARNING if capitals used)
- Acts as a proxy: `set()`, `get()`, and `query()` calls are forwarded to the underlying service

**Service Base (`src/Abstracts/AbstractAiService.php`)**
- All service implementations extend this abstract class
- Defines common API configuration properties: `apiKey`, `baseUrl`, `systemPrompt`
- Model configuration: `model`, `temperature`, `maxTokens`, `stop`, `stream`
- Two response methods: `one()` returns first response, `all()` returns array of all responses

**HTTP Layer (`src/Curl.php`)**
- Handles all HTTP requests to AI service APIs
- Supports both standard and streaming responses
- Streaming: uses `CURLOPT_WRITEFUNCTION` callback to handle chunks and prints directly to output
- Response handling: automatically decodes JSON and throws `ResponseException` on errors

**Traits**
- `AiServiceTrait`: Message formatting logic for converting prompts to API message arrays
- `HeadersTrait`: HTTP header management and formatting
- `PropertyAccessTrait`: Generic getter/setter methods using `property_exists()` checks

### Service Implementations

Each service in `src/Services/` follows this pattern:
1. Extends `AbstractAiService`
2. Uses `AiServiceTrait`, `HeadersTrait`, `PropertyAccessTrait`
3. Defines service-specific properties (model parameters, API options)
4. Implements `query()` method: validates input, formats messages, builds request payload
5. Overrides `extractFirstResponse()`/`extractAllResponses()` if custom logic needed

**Service-Specific Features:**
- **XaiService**: Tool calls, citations, live search via `searchParameters`
- **OpenAiService**: Native JSON mode via `response_format`, `reasoningEffort` parameter
- **GroqService**: Vision support
- **AnthropicService/MistralService/GoogleService**: Standard chat completions

### JSON Handling (`src/Helpers/JsonExtractor.php`)

Non-OpenAI services use post-processing for JSON responses:
- Extracts outermost JSON structure from text (handles markdown code blocks)
- Uses stack-based bracket matching algorithm
- Services set `$this->json = true` to enable automatic extraction

## Adding New AI Services

1. **Create**: `src/Services/YourServiceNameService.php`

2. **Implement**:
   ```php
   <?php
   namespace codechap\ai\Services;
   
   use codechap\ai\Abstracts\AbstractAiService;
   use codechap\ai\Traits\AiServiceTrait;
   use codechap\ai\Traits\PropertyAccessTrait;
   use codechap\ai\Traits\HeadersTrait;
   use codechap\ai\Curl;
   
   class YourServiceNameService extends AbstractAiService {
       use AiServiceTrait, HeadersTrait, PropertyAccessTrait;
       
       protected string $model = 'default-model-name';
       protected ?bool $json = false;
       // Add other service-specific properties...
       
       public function __construct(string $apiKey, string $url = 'https://api.provider.com/v1/') {
           parent::__construct($apiKey, $url);
       }
       
       public function query(string|array $prompts): self {
           $this->validatePrompts($prompts);
           $messages = $this->formatMessages($prompts, $this->systemPrompt);
           
           $data = array_filter([
               'messages' => $messages,
               'model' => $this->model,
               'temperature' => $this->temperature,
           ], fn($value) => !is_null($value));
           
           $headers = $this->getHeaders([
               'Authorization' => "Bearer " . trim($this->apiKey)
           ]);
           
           $this->curl = new Curl();
           $this->curl->post($data, $headers, $this->baseUrl . 'chat/completions');
           return $this;
       }
       
       public function models($column = false): array {
           return []; // @todo Implement
       }
   }
   ```

3. **Auto-discovery**: Service is automatically available via `new Ai('yourservicename', $key)`

## Key Implementation Details

**Property Access Pattern:**
```php
$ai->set('temperature', 0)
   ->set('model', 'grok-4')
   ->set('json', true)
   ->query("Your prompt")
   ->one();
```

The `set()` method throws `Exception` if property doesn't exist. All properties must be defined in the service class.

**Response Extraction:**
- Default path: `$response['choices'][0]['message']['content']`
- XaiService handles tool_calls and citations differently
- JSON mode (non-OpenAI): extracts and validates JSON from text response

**Error Handling:**
- `InvalidArgumentException`: Empty/invalid service type, API key, or prompts
- `ResponseException`: HTTP errors or cURL failures (from `Curl.php`)
- `RuntimeException`: JSON extraction/encoding failures
- `Exception`: Property access errors from `PropertyAccessTrait`

## Common Pitfalls

1. **Service Type Case**: Always use lowercase service types to avoid E_USER_WARNING.

2. **JSON Mode Differences**: 
   - OpenAI: Native support via `response_format` parameter
   - Others: Post-processing with `JsonExtractor` (may fail if AI doesn't return valid JSON)

3. **Streaming Side Effect**: Streaming prints output directly to stdout during request execution.

4. **Response Type Variations**: 
   - Most services return `string` from `one()`
   - XaiService can return `array` for tool calls or citations

5. **Vision Message Format**: Vision requires nested array structure:
   ```php
   [[
       'role' => 'user',
       'content' => [
           ['type' => 'image_url', 'image_url' => ['url' => $url]],
           ['type' => 'text', 'text' => $prompt]
       ]
   ]]
   ```

## Requirements

- PHP 8.2+
- Service registry is static and persists across instances
- `models()` method returns empty array on all services (marked as @todo)
